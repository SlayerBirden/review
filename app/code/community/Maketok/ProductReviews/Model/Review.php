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
class Maketok_ProductReviews_Model_Review extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DECLINED = 2;

    protected function _construct()
    {
        $this->_init('productreviews/review');
        parent::_construct();
    }

    /**
     * Add Thumbs for a review
     * type is "up" or "down"
     *
     * @param int|string $reviewId
     * @param string $type
     * @return Maketok_ProductReviews_Model_Review
     */
    public function thumbs($reviewId, $type)
    {
        /** @var $voteModel Maketok_ProductReviews_Model_Vote */
        $voteModel = Mage::getModel('productreviews/vote');
        $typeCode = $voteModel->getThumbCode($type);
        $ip = $_SERVER['REMOTE_ADDR'];
        $ipLong = ip2long($ip);
        $customerId = 0;
        if (Mage::helper('customer')->isLoggedIn()) {
            $customerId = Mage::helper('customer')->getCustomer()->getId();
        }
        $data = array(
            'remote_ip' => $ip,
            'remote_ip_long' => $ipLong,
            'customer_id' => $customerId,
            'review_id' => $reviewId,
            'value' => $typeCode,
        );
        $voteModel->addData($data)
            ->save();
        return $this;
    }

    /**
     * Get count Thumbs of Current user for a review
     * type is "up" or "down"
     *
     * @param int|string $reviewId
     * @param string|null $type
     * @param bool $forUser
     * @return string|int
     */
    public function getCountThumbs($reviewId, $type = null, $forUser = false)
    {
        /** @var $voteModel Maketok_ProductReviews_Model_Vote */
        $voteModel = Mage::getModel('productreviews/vote');
        $typeCode = $voteModel->getThumbCode($type);
        /** @var $collection Maketok_ProductReviews_Model_Resource_Vote_Collection */
        $collection = $voteModel->getCollection();
        $collection->addFieldToFilter('review_id', $reviewId);
        if (!is_null($typeCode)) {
            $collection->addFieldToFilter('value', $typeCode);
        }
        if ($forUser) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ipLong = ip2long($ip);
            $customerId = 0;
            if (Mage::helper('customer')->isLoggedIn()) {
                $customerId = Mage::helper('customer')->getCustomer()->getId();
            }
            if ($customerId) {
                $collection->addFieldToFilter('customer_id', $customerId);
            } else {
                $collection->addFieldToFilter('remote_ip_long', $ipLong);
            }
        }
        return $collection->getSize();
    }

    /**
     * Determines if Vote entry is not allowed for current user for given review
     *
     * @param Maketok_ProductReviews_Model_Review $review
     * @return int
     */
    public function getIfVoteNotAllowed($review)
    {
        $result = 0;
        $customerId = 0;
        if (Mage::helper('customer')->isLoggedIn()) {
            $customerId = Mage::helper('customer')->getCustomer()->getId();
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $ipLong = ip2long($ip);
        if ($review->getCountThumbs($review->getId(), null, true) > 0 ||
            ($customerId && $customerId == $review->getCustomerId() ||
                $ipLong == $review->getRemoteIpLong())
        ) {
            $result = 1;
        }
        return $result;
    }

    public function validatePoster()
    {
        /** @var $banModel Maketok_ProductReviews_Model_Ban */
        $banModel = Mage::getModel('productreviews/ban');
        if ($this->getCustomerId()) {
            if ($banModel->isCustomerBanned($this->getCustomerId())) {
                Mage::throwException(Mage::helper('productreviews')->__('Unfortunately you can not leave review on this site. Please contact administrator of the site for clarification.'));
            }
        } else {
            if ($banModel->isIpBanned($this->getRemoteIp())) {
                Mage::throwException(Mage::helper('productreviews')->__('Unfortunately you can not leave review on this site. Please contact administrator of the site for clarification.'));
            }
        }
    }

    protected function _afterSave()
    {
        $this->validatePoster();
        return parent::_afterSave();
    }

}