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
class Maketok_ProductReviews_Block_Catalog_Product_Review extends Maketok_ProductReviews_Block_Template
{
    protected $_availableTemplates = array(
        'default' => 'maketok/productreviews/catalog/product/summary.phtml',
        'short'   => 'maketok/productreviews/catalog/product/summary_short.phtml'
    );

    public function getSummaryHtml($product, $templateType, $displayIfNoReviews)
    {
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = 'default';
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);
        $this->setProduct($product);
        return $this->toHtml();
    }

    /**
     * @return int
     */
    public function getRatingSummary()
    {
        return Mage::getModel('productreviews/rating')->getRatingSummary($this->getProduct()->getId());
    }

    /**
     * @return int
     */
    public function getReviewsCount()
    {
        /** @var $collection Maketok_ProductReviews_Model_Resource_Review_Collection */
        $collection = Mage::getResourceModel('productreviews/review_collection')
            ->addFieldToFilter('main_table.product_id', $this->getProduct()->getId())
            ->addFieldToFilter('main_table.status_id', Maketok_ProductReviews_Model_Review::STATUS_APPROVED);
        return $collection->getSize();
    }

    /**
     * @return string
     */
    public function getReviewsUrl()
    {
        return '#';
    }

    /**
     * Add an available template by type
     *
     * It should be called before getSummaryHtml()
     *
     * @param string $type
     * @param string $template
     */
    public function addTemplate($type, $template)
    {
        $this->_availableTemplates[$type] = $template;
    }
}
