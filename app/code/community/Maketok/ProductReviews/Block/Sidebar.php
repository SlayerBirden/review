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
class Maketok_ProductReviews_Block_Sidebar extends Maketok_ProductReviews_Block_Template
{
    protected $_collection = null;

    protected function _toHtml()
    {
        $name = $this->getNameInLayout();
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');
        if ($name == 'productreviews_sidebar_left' && $helper->getEnableLeftSidebar()) {
            $this->setTemplate('maketok/productreviews/sidebar/default.phtml');
        } elseif ($name == 'productreviews_sidebar_right' && $helper->getEnableRightSidebar()) {
            $this->setTemplate('maketok/productreviews/sidebar/default.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * @return Maketok_ProductReviews_Model_Resource_Review_Collection
     */
    public function getReviewCollection()
    {
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');
        $sort = $helper->getSidebarSort();
        /** @var $model Maketok_ProductReviews_Model_Sidebar */
        $model = Mage::getModel('productreviews/sidebar');

        if (is_null($this->_collection)) {
            /** @var $collection Maketok_ProductReviews_Model_Resource_Review_Collection */
            $collection = $model->getReviewCollection();
            switch ($sort) {
                case 0:
                    $collection->getSelect()->order('RAND()');
                    break;
                case 1:
                    $collection->setOrder('main_table.created_at', 'desc');
                    break;
                case 2:
                    $collection->setOrder('thumbs_up', 'desc');
                    break;
            }
            $collection->load();
            $model->prepareLoadedCollection($collection);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = Mage::getStoreConfig('productreviews/sidebar/title');
        if ($title) {
            return $title;
        }
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');
        $sort = $helper->getSidebarSort();
        $sourceModel = Mage::getModel('productreviews/system_config_sidebar_sort');
        $array = $sourceModel->toOptionArray();
        return $this->__('%s Reviews', $array[$sort]['label']);
    }
}