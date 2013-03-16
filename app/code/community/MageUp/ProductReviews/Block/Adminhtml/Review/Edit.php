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
class MageUp_ProductReviews_Block_Adminhtml_Review_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_review';
        $this->_blockGroup = 'productreviews';

        parent::__construct();

        $review = Mage::registry('current_productreviews_review');

        if ($review && $review->getStatusId() == MageUp_ProductReviews_Model_Review::STATUS_PENDING) {
            $this->_addButton('decline', array(
                'label'     => Mage::helper('adminhtml')->__('Decline'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/decline', array($this->_objectId => $this->getRequest()->getParam($this->_objectId))) . '\')',
                'class'     => 'delete',
            ), -1);
        }

        if ($review && $review->getStatusId() != MageUp_ProductReviews_Model_Review::STATUS_APPROVED) {
            $this->_addButton('approve', array(
                'label'     => Mage::helper('adminhtml')->__('Approve'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/approve', array($this->_objectId => $this->getRequest()->getParam($this->_objectId))) . '\')',
                'class'     => 'save',
            ), -1);
        }

        /** @var $banModel MageUp_ProductReviews_Model_Ban */
        $banModel = Mage::getModel('productreviews/ban');
        if ($review->getCustomerId()) {
            if (!$banModel->isCustomerBanned($review->getCustomerId())) {
                $this->_addButton('ban_customer', array(
                    'label'     => Mage::helper('adminhtml')->__('Ban Customer'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/banCustomer',
                        array(
                            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                        )
                    ) . '\')',
                    'class'     => 'delete',
                ), -1);
            } else {
                $this->_addButton('unban_customer', array(
                    'label'     => Mage::helper('adminhtml')->__('Remove Ban for Customer'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/unbanCustomer',
                        array(
                            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                        )
                    ) . '\')',
                    'class'     => 'save',
                ), -1);
            }
        } else {
            if (!$banModel->isIpBanned($review->getRemoteIp())) {
                $this->_addButton('ban_ip', array(
                    'label'     => Mage::helper('adminhtml')->__('Ban Ip Address'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/banIp',
                        array(
                            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                        )
                    ) . '\')',
                    'class'     => 'delete',
                ), -1);
            } else {
                $this->_addButton('unban_ip', array(
                    'label'     => Mage::helper('adminhtml')->__('Remove Ban for Ip Address'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/unbanIp',
                        array(
                            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                        )
                    ) . '\')',
                    'class'     => 'save',
                ), -1);
            }
        }


        if( $this->getRequest()->getParam('productId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/catalog_product/edit', array('id' => $this->getRequest()->getParam('productId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('customerId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/customer/edit', array('id' => $this->getRequest()->getParam('customerId', false))) .'\')' );
        }
    }

    /**
     * Get back button url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var $label MageUp_ProductReviews_Model_Review */
        $review = Mage::registry('current_productreviews_review');
        if ($review && $review->getId()) {
            return Mage::helper('productreviews')->__(
                "%s's Review info",
                $review->getNickname()
            );
        }
        return '';
    }
}