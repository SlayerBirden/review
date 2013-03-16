<?php
/**
 * MageUp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageup.info@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade MageUp ProductReviews to newer
 * versions in the future. If you wish to customize MageUp ProductReviews for your
 * needs please refer to http://www.mageup.com for more information.
 *
 * @category    MageUp
 * @package     MageUp_ProductReviews
 * @copyright   Copyright (c) 2013 MageUp (http://www.mageup.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class MageUp_ProductReviews_Adminhtml_ProductreviewsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display processes grid action
     */
    public function gridAction()
    {
        $this->_title($this->_getHelper()->__('MageUp'))->_title($this->_getHelper()->__('Manage Customer Reviews'));

        $this->loadLayout();
        $this->_setActiveMenu('mageup');
        $this->_addContent($this->getLayout()->createBlock('productreviews/adminhtml_review'));
        $this->renderLayout();
    }

    /**
     * @return MageUp_ProductReviews_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('productreviews');
    }

    /**
     * Initialize review object by request
     *
     * @return MageUp_ProductReviews_Model_Review|bool
     */
    protected function _initReview()
    {
        $reviewId = $this->getRequest()->getParam('id');
        if ($reviewId) {
            $review = Mage::getModel('productreviews/review')->load($reviewId);
            if ($review->getId()) {
                Mage::register('current_productreviews_review', $review);
                return $review;
            }
        }
        return false;
    }

    /**
     * Edit action
     */
    public function editAction()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = $this->_initReview();
        if ($review) {
            $this->_title("Review from {$review->getNickname()}");

            $this->_title($this->_getHelper()->__('MageUp'))
                ->_title($this->_getHelper()->__('Manage Reviews'));

            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('productreviews/adminhtml_review_edit', 'productreviews_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError(
                $this->_getHelper()->__('Could not initialize the MageUp Product Review.')
            );
            $this->_redirect('*/*/grid');
        }
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = $this->_initReview();
        if ($review) {
            try {
                $review->delete();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The MageUp Product Review has been successfully deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('Could not delete MageUp Product Review.')
                );
                Mage::logException($e);
            }
        } else {
            $this->_getSession()->addError(
                $this->_getHelper()->__('Could not initialize the MageUp Product Review.')
            );
        }
        $this->_redirect('*/*/grid');
    }

    /**
     * Approve action
     */
    public function approveAction()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = $this->_initReview();
        if ($review) {
            try {
                $review->setStatusId(MageUp_ProductReviews_Model_Review::STATUS_APPROVED)->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The MageUp Product Review has been successfully approved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('Could not approve MageUp Product Review.')
                );
                Mage::logException($e);
            }
        } else {
            $this->_getSession()->addError(
                $this->_getHelper()->__('Could not initialize the MageUp Product Review.')
            );
        }
        $this->_redirect('*/*/grid');
    }

    /**
     * Decline action
     */
    public function declineAction()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = $this->_initReview();
        if ($review) {
            try {
                $review->setStatusId(MageUp_ProductReviews_Model_Review::STATUS_DECLINED)->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The MageUp Product Review has been successfully declined.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('Could not decline MageUp Product Review.')
                );
                Mage::logException($e);
            }
        } else {
            $this->_getSession()->addError(
                $this->_getHelper()->__('Could not initialize the MageUp Product Review.')
            );
        }
        $this->_redirect('*/*/grid');
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = $this->_initReview();
        $post = $this->getRequest()->getPost();
        if ($review) {
            try {
                $review->setDetail($post['detail'])->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The MageUp Product Review has been successfully saved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('Could not save MageUp Product Review.')
                );
                Mage::logException($e);
            }
        } else {
            $this->_getSession()->addError(
                $this->_getHelper()->__('Could not initialize the MageUp Product Review.')
            );
        }
        $this->_redirect('*/*/grid');
    }

    public function massDeleteAction()
    {
        $reviews = $this->getRequest()->getParam('reviews');
        if(!is_array($reviews)) {
            $this->_getSession()->addError($this->_getHelper()->__('Please select review(s).'));
        } else {
            try {
                foreach ($reviews as $reviewId) {
                    $model = Mage::getModel('productreviews/review')->load($reviewId);
                    $model->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('Total of %d record(s) have been deleted.', count($reviews))
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while deleting the selected review(s).')
                );
            }
        }
        $this->_redirect('*/*/grid');
    }

    public function massUpdateStatusAction()
    {
        $reviews = $this->getRequest()->getParam('reviews');
        $status = $this->getRequest()->getParam('status');
        if(!is_array($reviews)) {
            $this->_getSession()->addError($this->_getHelper()->__('Please select review(s).'));
        } else {
            try {
                foreach ($reviews as $reviewId) {
                    $model = Mage::getModel('productreviews/review')->load($reviewId);
                    $model->setStatusId($status)->save();
                }
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('Total of %d record(s) have been updated.', count($reviews))
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while updating the selected review(s).')
                );
            }
        }
        $this->_redirect('*/*/grid');
    }

    public function banIpAction()
    {
        $reviewId = $this->getRequest()->getParam('id');
        if ($reviewId) {
            $review = Mage::getModel('productreviews/review')->load($reviewId);
            $ban = Mage::getModel('productreviews/ban');
            $ban->setRemoteIp($review->getRemoteIp())
                ->setRemoteIpLong($review->getRemoteIpLong());
            try {
                $ban->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The Ip Address (%s) has been banned.', $review->getRemoteIp())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while banning the Ip Address.')
                );
            }
        }
        $this->_redirect('*/*/edit', array('id' => $reviewId));
    }

    public function banCustomerAction()
    {
        $reviewId = $this->getRequest()->getParam('id');
        if ($reviewId) {
            $review = Mage::getModel('productreviews/review')->load($reviewId);
            $ban = Mage::getModel('productreviews/ban');
            if ($review->getCustomerId()) {
                $ban->setCustomerId($review->getCustomerId());
            } else {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while banning the Customer.')
                );
                $this->_redirect('*/*/edit', array('id' => $reviewId));
            }
            try {
                $ban->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The customer (id: %s) has been banned.', $review->getCustomerId())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while banning the Customer.')
                );
            }
        }
        $this->_redirect('*/*/edit', array('id' => $reviewId));
    }

    public function unbanIpAction()
    {
        $reviewId = $this->getRequest()->getParam('id');
        if ($reviewId) {
            $review = Mage::getModel('productreviews/review')->load($reviewId);
            $banCollection = Mage::getResourceModel('productreviews/ban_collection')
                ->addFieldtoFilter('remote_ip', $review->getRemoteIp());
            try {
                foreach ($banCollection as $item) {
                    $item->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The ban was released from the Ip Address (%s).', $review->getRemoteIp())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while releasing ban from the Ip Address.')
                );
            }
        }
        $this->_redirect('*/*/edit', array('id' => $reviewId));
    }

    public function unbanCustomerAction()
    {
        $reviewId = $this->getRequest()->getParam('id');
        if ($reviewId) {
            $review = Mage::getModel('productreviews/review')->load($reviewId);
            $banCollection = Mage::getResourceModel('productreviews/ban_collection')
                ->addFieldtoFilter('customer_id', $review->getCustomerId());
            try {
                foreach ($banCollection as $item) {
                    $item->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('The a customer (id: %s) has been unbanned.', $review->getCustomerId())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred while releasing ban from the Customer.')
                );
            }
        }
        $this->_redirect('*/*/edit', array('id' => $reviewId));
    }

}