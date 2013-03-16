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
class MageUp_ProductReviews_Block_Adminhtml_Review_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var MageUp_ProductReviews_Model_Review
     */
    protected $_model;
    
    protected function _prepareForm()
    {
        $this->_model = Mage::registry('current_productreviews_review');
        /** @var $banModel MageUp_ProductReviews_Model_Ban */
        $banModel = Mage::getModel('productreviews/ban');
        $productId = $this->_model->getData('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getActionUrl(),
            'method' => 'post',
        ));
        $form->setHtmlIdPrefix('productreviews_review_');
        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => Mage::helper('productreviews')->__('Settings'),
                'class' => 'fieldset-wide',
            )
        );
        if ($this->_model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
                'value' => $this->_model->getId(),
            ));
        }

        $fieldset->addField('product_name', 'note', array(
            'label' => Mage::helper('productreviews')->__('Product'),
            'text' => '<a href="' . $this->getUrl('*/catalog_product/edit', array('id' => $product->getId())) . '" onclick="this.target=\'blank\'">' . $product->getName() . '</a>'
        ));

        if ($this->_model->getCustomerId()) {
            $customerText = Mage::helper('productreviews')->__('<a href="%1$s" onclick="this.target=\'blank\'">%2$s</a>',
                $this->getUrl('*/customer/edit', array('id' => $this->_model->getCustomerId(), 'active_tab' => 'review')),
                $this->htmlEscape($this->_model->getNickname()));
            if ($banModel->isCustomerBanned($this->_model->getCustomerId())) {
                $customerText .= ' <span style="font-weight:bold; color:red;">(' . Mage::helper('productreviews')->__('banned') . ')</span>';
            }
        } else {
            $customerText = Mage::helper('productreviews')->__('Guest');
        }

        $fieldset->addField('customer', 'note', array(
            'label' => Mage::helper('productreviews')->__('Posted By'),
            'text' => $customerText,
        ));

        $fieldset->addField('nickname', 'note', array(
            'label' => Mage::helper('productreviews')->__('Nickname'),
            'text' => $this->_model->getData('nickname'),
        ));

        $ip = $this->_model->getData('remote_ip');
        if ($banModel->isIpBanned($ip)) {
            $ip .= ' <span style="font-weight:bold; color:red;">(' . Mage::helper('productreviews')->__('banned') . ')</span>';
        }

        $fieldset->addField('ip', 'note', array(
            'label' => Mage::helper('productreviews')->__('Remote Ip'),
            'text' => $ip,
        ));

        $rating = Mage::getModel('productreviews/rating')->getRatingPercent($this->_model->getId());
        $fieldset->addField('rating', 'note', array(
            'label' => Mage::helper('review')->__('Rating'),
            'text' => '<div class="rating-box percent-'.$rating.'"><div class="rating" style="width:'.$rating.'%"></div></div>',
        ));

        $status = '';
        switch ($this->_model->getStatusId()) {
            case MageUp_ProductReviews_Model_Review::STATUS_APPROVED :
                $status = 'Approved';
                break;
            case MageUp_ProductReviews_Model_Review::STATUS_PENDING :
                $status = 'Pending';
                break;
            case MageUp_ProductReviews_Model_Review::STATUS_DECLINED :
                $status = 'Declined';
                break;
        }
        $fieldset->addField('status', 'note', array(
            'label' => Mage::helper('productreviews')->__('Status'),
            'text' => $status,
        ));

        $fieldset->addField('detail', 'textarea', array(
            'label' => Mage::helper('productreviews')->__('Review'),
            'title' => Mage::helper('productreviews')->__('Review'),
            'name'  => 'detail',
            'required'  => true,
            'style'     => 'height:24em;',
            'value' => $this->_model->getData('detail'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getActionUrl()
    {
        return $this->getUrl('*/*/save');
    }

}
