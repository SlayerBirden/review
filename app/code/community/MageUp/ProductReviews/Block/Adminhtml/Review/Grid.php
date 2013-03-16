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
class MageUp_ProductReviews_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_reviewModel;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_reviewModel = Mage::getModel('productreviews/review');
        $this->setId('productreviews_grid');
        $this->setDefaultSort('review_id');
        $this->setDefaultDir('DESC');
//        $this->setSaveParametersInSession(true);
    }
    
    /**
     * Prepare grid collection
     *
     * @return MageUp_ProductReviews_Block_Adminhtml_Review_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection MageUp_ProductReviews_Model_Resource_Review_Collection */
        $collection = Mage::getResourceModel('productreviews/review_collection');
        $expression = new Zend_Db_Expr('IF(main_table.customer_id = 0 OR main_table.customer_id IS NULL, 0, 1)');
        $collection->getSelect()->columns(array('type' => $expression));
        $collection->getSelect()->join(array('cpe' => $collection->getTable('catalog/product')),
            "cpe.entity_id = main_table.product_id",
            array('sku' => 'cpe.sku')
        );
        if ($this->getCustomerId() || $this->getRequest()->getParam('customerId', false)) {
            $this->setCustomerId(($this->getCustomerId() ? $this->getCustomerId() : $this->getRequest()->getParam('customerId')));
            $collection->addFieldToFilter('main_table.customer_id', $this->getCustomerId());
        }
        if ($this->getProductId() || $this->getRequest()->getParam('productId', false)) {
            $this->setProductId(($this->getProductId() ? $this->getProductId() : $this->getRequest()->getParam('productId')));
            $collection->addFieldToFilter('main_table.product_id', $this->getProductId());
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * Prepare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('review_id', array(
            'header'        => Mage::helper('productreviews')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'review_id',
            'filter_index'  => 'main_table.review_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('productreviews')->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'index'         => 'created_at',
            'filter_index'  => 'main_table.created_at',
        ));

        $this->addColumn('nickname', array(
            'header'        => Mage::helper('productreviews')->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'nickname',
            'filter_index'  => 'main_table.nickname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('productreviews')->__('Customer Id'),
            'align'         => 'left',
            'index'         => 'customer_id',
            'filter_index'  => 'main_table.customer_id',
            'type'          => 'text',
            'escape'        => true,
            'frame_callback' => array($this, 'decorateCustomer'),
        ));

        $this->addColumn('ip', array(
            'header'        => Mage::helper('productreviews')->__('Ip Address'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'remote_ip',
            'filter_index'  => 'main_table.remote_ip',
            'type'          => 'text',
            'escape'        => true,
            'frame_callback' => array($this, 'decorateIp'),
        ));

        $this->addColumn('detail', array(
            'header'        => Mage::helper('productreviews')->__('Review'),
            'align'         => 'left',
            'index'         => 'detail',
            'filter_index'  => 'main_table.detail',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('productreviews')->__('Type'),
            'type'      => 'options',
            'index'     => 'type',
            'options'   => array(
                0 => 'Guest',
                1 => 'Customer',
            ),
            'filter_condition_callback' => array($this, '_filterReviewCondition'),
        ));

        $this->addColumn('parent_id', array(
            'header'    => Mage::helper('productreviews')->__('Parent Review Id'),
            'type'      => 'text',
            'index'         => 'parent_id',
            'filter_index'  => 'main_table.parent_id',
            'align'         => 'left',
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('productreviews')->__('Product SKU'),
            'type'      => 'text',
            'index'         => 'sku',
            'filter_index'  => 'cpe.sku',
            'align'         => 'left',
        ));

        $this->addColumn('product_id', array(
            'header'    => Mage::helper('productreviews')->__('Product ID'),
            'type'      => 'text',
            'index'         => 'product_id',
            'filter_index'  => 'main_table.product_id',
            'align'         => 'left',
        ));

        $this->addColumn('rating', array(
            'header'    => Mage::helper('productreviews')->__('Rating %'),
            'width'     => '160',
            'align'     => 'left',
            'index'     => 'percent',
            'filter_index'  => 'rating_tbl.percent',
            'type'      => 'number',
            'frame_callback' => array($this, 'decorateRating')
        ));

        $this->addColumn('status_id', array(
            'header'    => Mage::helper('productreviews')->__('Status'),
            'width'     => '220',
            'align'     => 'left',
            'index'     => 'status_id',
            'filter_index'  => 'main_table.status_id',
            'type'      => 'options',
            'options'   => array(
                MageUp_ProductReviews_Model_Review::STATUS_PENDING => Mage::helper('productreviews')->__('Pending'),
                MageUp_ProductReviews_Model_Review::STATUS_APPROVED => Mage::helper('productreviews')->__('Approved'),
                MageUp_ProductReviews_Model_Review::STATUS_DECLINED => Mage::helper('productreviews')->__('Declined'),
            ),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('review_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('reviews');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('productreviews')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
            'confirm' => Mage::helper('productreviews')->__('Are you sure?')
        ));

        $statuses = array(
            array('label'=>'', 'value'=>''),
            array('label'=>Mage::helper('productreviews')->__('Pending'), 'value'=>MageUp_ProductReviews_Model_Review::STATUS_PENDING),
            array('label'=>Mage::helper('productreviews')->__('Approved'), 'value'=>MageUp_ProductReviews_Model_Review::STATUS_APPROVED),
            array('label'=>Mage::helper('productreviews')->__('Declined'), 'value'=>MageUp_ProductReviews_Model_Review::STATUS_DECLINED),
        );
        $this->getMassactionBlock()->addItem('update_status', array(
            'label'         => Mage::helper('productreviews')->__('Update Status'),
            'url'           => $this->getUrl('*/*/massUpdateStatus'),
            'additional'    => array(
                'status'    => array(
                    'name'      => 'status',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => Mage::helper('productreviews')->__('Status'),
                    'values'    => $statuses
                )
            )
        ));
    }

    /**
     * Decorate status column values
     *
     * @param $value
     * @param $row
     * @return string
     */
    public function decorateStatus($value, $row)
    {
        $class = '';
        switch ($row->getStatusId()) {
            case MageUp_ProductReviews_Model_Review::STATUS_APPROVED :
                $class = 'grid-severity-notice';
                break;
            case MageUp_ProductReviews_Model_Review::STATUS_PENDING :
                $class = 'grid-severity-major';
                break;
            case MageUp_ProductReviews_Model_Review::STATUS_DECLINED :
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    /**
     * Decorate rating column values
     *
     * @param $value
     * @param $row
     * @return string
     */
    public function decorateRating($value, $row)
    {
        return '<div class="rating-box percent-'.$value.'"><div class="rating" style="width:'.$value.'%"></div></div>';
    }

    /**
     * @param $value
     * @param $row
     * @return string
     */
    public function decorateIp($value, $row)
    {
        /** @var $banModel MageUp_ProductReviews_Model_Ban */
        $banModel = Mage::getModel('productreviews/ban');
        if ($banModel->isIpBanned($row->getRemoteIp())) {
            $value .= ' <span style="font-weight:bold; color:red;">(' . $this->__('banned') . ')</span>';
        }
        return $value;
    }

    /**
     * @param $value
     * @param $row
     * @return string
     */
    public function decorateCustomer($value, $row)
    {
        /** @var $banModel MageUp_ProductReviews_Model_Ban */
        $banModel = Mage::getModel('productreviews/ban');
        if ($banModel->isCustomerBanned($row->getCustomerId())) {
            $value .= ' <span style="font-weight:bold; color:red;">(' . $this->__('banned') . ')</span>';
        }
        return $value;
    }

    /**
     * Get row edit url
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/productreviews/edit', array(
            'id'=>$row->getId(),
            'productId' => $this->getProductId(),
            'customerId' => $this->getCustomerId(),
        ));
    }

    /**
     * Filter condition for Type
     * @param $collection
     * @param $column
     * @return mixed
     */
    protected function _filterReviewCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        if ($value == 0) {
            $this->getCollection()->getSelect()->where('`main_table`.`customer_id` = 0');
        } else {
            $this->getCollection()->getSelect()->where('`main_table`.`customer_id` != 0');
        }
    }

}
