<?php
/**
 * Maketok ProductReviews module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maketok.info@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Maketok ProductReviews to newer
 * versions in the future. If you wish to customize Maketok ProductReviews for your
 * needs please refer to http://www.maketok.com for more information.
 *
 * @category    Maketok
 * @package     Maketok_ProductReviews
 * @copyright   Copyright (c) 2013 Maketok (http://www.maketok.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Maketok_ProductReviews_ProductController extends Mage_Core_Controller_Front_Action
{
    protected $_defaultSort = 'created_at';
    protected $_defaultDirection = 'desc';

    public function indexAction()
    {
        // should be something here?
    }

    /*
     * get reviews action
     */
    public function reviewsAction()
    {
        $return = new Varien_Object();
        $return->setEmpty(0);
        $return->setError(0);
        $return->setErrorMessage('');
        $return->setItems(array());
        $return->setLastPageNumber(null);
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');

        $productId = $this->getRequest()->getParam('product_id');
        $sort = $this->getRequest()->getParam('sort');
        $dir = $this->getRequest()->getParam('dir');
        $page = $this->getRequest()->getParam('p');
        if (!$productId) {
            $return->setError(1);
            $return->setErrorMessage($this->__('Could not load product for reviews.'));
            $this->getResponse()->setBody($return->toJson());
            die();
        }

        $collection = $this->_getCollection($sort, $dir, $page);

        if (!$collection->getSize()) {
            $return->setEmpty(1);
        } else {
            $items = array();
            foreach ($collection->getItems() as $item) {
                /*
                 * format date
                 */
                $item->setCreatedAt($helper->getDate(strtotime($item->getCreatedAt())));
                /*
                 * escape html tags
                 */
                $item->setDetail(str_replace("\n", '<br />', htmlspecialchars($item->getDetail())));
                $items[] = $item->toArray();
            }
            $return->setLastPageNumber($collection->getLastPageNumber());
            $return->setItems($items);
        }
        $this->getResponse()->setBody($return->toJson());
    }

    /**
     * submit review action
     */
    public function submitAction()
    {
        $return = new Varien_Object();
        $return->setError(0);
        $return->setErrorMessage('');
        $return->setSuccessMessage('');
        $return->setItem(null);
        $return->setCanShow(Mage::helper('productreviews')->getAutoApprove());
        /** @var $dateManager Mage_Core_Model_Date */
        $dateManager = Mage::getSingleton('core/date');
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');

        $post = $this->getRequest()->getPost();
        $review = Mage::getModel('productreviews/review');
        $nickName = $this->getRequest()->getParam('nickname');
        $detail = $this->getRequest()->getParam('detail');
        if (empty($nickName) || empty($detail)) {
            $return->setError(1);
            $return->setErrorMessage($this->__('Empty Nickname or Review fields.'));
            $this->getResponse()->setBody($return->toJson());
            return;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $ipLong = ip2long($ip);
        $customerId = 0;
        if (Mage::helper('customer')->isLoggedIn()) {
            $customerId = Mage::helper('customer')->getCustomer()->getId();
        }
        try {
            $review->addData($post)
                ->setCreatedAt($dateManager->date())
                ->setCustomerId($customerId)
                ->setRemoteIp($ip)
                ->setRemoteIpLong($ipLong)
                ->setStatusId(Mage::helper('productreviews')->getAutoApprove())
                ->save();
            $review->setPercent(0);
            if (!empty($post['rating'])) {
                $ratingData = array(
                    'remote_ip' => $ip,
                    'remote_ip_long' => $ipLong,
                    'customer_id' => $customerId,
                    'product_id' => $post['product_id'],
                    'review_id' => $review->getId(),
                    'percent' => $post['rating']*20,
                    'value' => $post['rating'],
                );
                $rating = Mage::getModel('productreviews/rating')
                    ->addData($ratingData)
                    ->save();
                $review->setPercent($rating->getPercent());
            }
            /*
             * escape html tags
             */
            $review->setDetail(str_replace("\n", '<br />', htmlspecialchars($review->getDetail())));
            /*
             * format date
             */
            $review->setCreatedAt($helper->getDate(strtotime($review->getCreatedAt())));
            /*
             * add Thumbs Up count (since it's new there are no Thumbs up possible at this point)
             */
            $review->setThumbsUp(0);
            $review->setExists(1);
            $review->setThumbsDown(0);
            $review->setHideState(0);
            $review->setHasParent(0);
            $review->setParentNickname('');
            if ($review->getParentId()) {
                $review->setHasParent(1);
                $parent = Mage::getModel('productreviews/review')->load($review->getParentId());
                $review->setParentNickname($parent->getNickname());
            }

            $return->setItem($review->toArray());
            if ($return->getCanShow()) {
                $return->setSuccessMessage($this->__('Your review has been created successfully.'));
            } else {
                $return->setSuccessMessage($this->__('Your review has been created successfully and submitted for moderation.'));
            }
        } catch (Mage_Core_Exception $e) {
            $return->setError(1);
            $return->setErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $return->setError(1);
            $return->setErrorMessage($this->__('There was an error during saving your review. Please try again later.'));
            Mage::logException($e);
        }
        $this->getResponse()->setBody($return->toJson());
    }

    /**
     * @param null|string $sort
     * @param null|string $dir
     * @param null|string|int $page
     * @return Maketok_ProductReviews_Model_Resource_Review_Collection
     */
    protected function _getCollection($sort = null, $dir = null, $page = null)
    {
        $productId = $this->getRequest()->getParam('product_id');
        /** @var $collection Maketok_ProductReviews_Model_Resource_Review_Collection */
        $collection = Mage::getResourceModel('productreviews/review_collection');
        $collection->addFieldToFilter('main_table.product_id', $productId)
            ->addFieldToFilter('main_table.status_id', Maketok_ProductReviews_Model_Review::STATUS_APPROVED);
        $order = $sort ? $sort : $this->_defaultSort;
        $order .= ' ';
        $order .= $dir ? $dir : $this->_defaultDirection;
        $collection->getSelect()->order($order);
        $chunk = 10;
        $collection->setPageSize($chunk);
        if (!is_null($page)) {
            $collection->setCurPage($page);
        }
        return $collection;
    }

    /*
     * Add thumbs_up or thumbs_down for a review
     */
    public function thumbsAction()
    {
        $return = new Varien_Object();
        $return->setError(0);
        $return->setErrorMessage('');

        $reviewId = $this->getRequest()->getParam('id');
        $thumb = $this->getRequest()->getParam('thumb'); // "up" or "down"
        if (!$reviewId) {
            $return->setError(1);
            $return->setErrorMessage($this->__('Can not initialize Review.'));
            $this->getResponse()->setBody($return->toJson());
            return;
        }
        /** @var $review Maketok_ProductReviews_Model_Review */
        $review = Mage::getModel('productreviews/review');
        try {
            if ($review->getCountThumbs($reviewId, null, true) > 0) {
                $return->setError(1);
                if ($thumb == 'up') {
                    $return->setErrorMessage($this->__('You have already liked this review.'));
                } else {
                    $return->setErrorMessage($this->__('You have already voted for this review.'));
                }
                $this->getResponse()->setBody($return->toJson());
                return;
            }
            if ($review->getIfVoteNotAllowed($review->load($reviewId))) {
                $return->setError(1);
                $return->setErrorMessage($this->__('You can not vote for your own review.'));
                $this->getResponse()->setBody($return->toJson());
                return;
            }
            $review->thumbs($reviewId, $thumb);
        } catch (Exception $e) {
            $return->setError(1);
            $return->setErrorMessage($this->__('There was an error. Please try again later.'));
            Mage::logException($e);
        }
        $this->getResponse()->setBody($return->toJson());
    }

    /**
     * A call to verify the Captcha entry
     */
    public function verifyCaptchaAction()
    {
        $post = $this->getRequest()->getPost();
        $captcha = $this->_getCaptcha();
        $result = $captcha->verify($post);
        $this->getResponse()->setBody($result->toJson());
    }

    /**
     * @return Maketok_ProductReviews_Model_Captcha_Recaptcha
     */
    public function _getCaptcha()
    {
        return Mage::getModel('productreviews/captcha_recaptcha');
    }

    public function showParentAction()
    {
        $return = new Varien_Object();
        $return->setError(0);
        $return->setErrorMessage('');
        $return->setItem(null);

        $parentId = $this->getRequest()->getParam('parent_id');
        $helper = Mage::helper('productreviews');

        try{
            /** @var $parent Maketok_ProductReviews_Model_Review */
            $parent = Mage::getModel('productreviews/review')->load($parentId);
            if (!$parent->getId()) {
                $return->setError(1);
                $return->setErrorMessage($this->__('Can not find parent Review.'));
                $this->getResponse()->setBody($return->toJson());
                return;
            }
            if ($parent->getStatusId() != Maketok_ProductReviews_Model_Review::STATUS_APPROVED) {
                $return->setError(1);
                $return->setErrorMessage($this->__('The Review has been removed by the administrator.'));
                $this->getResponse()->setBody($return->toJson());
                return;
            }
            $parent->setThumbsUp($parent->getCountThumbs($parent->getId(), 'up'))
                ->setThumbsDown($parent->getCountThumbs($parent->getId(), 'down'));
            $parent->setExists(($parent->getIfVoteNotAllowed($parent)));
            $parent->setHideState(($parent->getThumbsDown() >= $helper->getMaxVotesDown()) ? 1 : 0);
            $parent->setHasParent(0);
            if ($parent->getParentId()) {
                $parent->setHasParent(1);
                $parentOfParent = Mage::getModel('productreviews/review')->load($parent->getParentId());
                $parent->setParentNickname($parentOfParent->getNickname());
            }
            $parent->setDetail(str_replace("\n", '<br />', htmlspecialchars($parent->getDetail())));
            /** @var $ratingModel Maketok_ProductReviews_Model_Rating */
            $ratingModel = Mage::getModel('productreviews/rating');
            $parent->setPercent($ratingModel->getRatingPercent($parent->getId()));
            $return->setItem($parent->toArray());
        } catch (Exception $e) {
            Mage::logException($e);
            $return->setError(1);
            $return->setErrorMessage($this->__('Can not load parent Review.'));
        }
        $this->getResponse()->setBody($return->toJson());
    }

    public function redirectAction()
    {
        $this->_redirectReferer('/');
    }

}