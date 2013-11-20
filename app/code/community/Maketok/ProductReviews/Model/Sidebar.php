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
class Maketok_ProductReviews_Model_Sidebar extends Mage_Core_Model_Abstract
{
    /**
     * @return Maketok_ProductReviews_Model_Resource_Review_Collection
     */
    public function getReviewCollection()
    {
        /** @var $helper Maketok_ProductReviews_Helper_Data */
        $helper = Mage::helper('productreviews');
        $limit = $helper->getSidebarLimit();
        /** @var $collection Maketok_ProductReviews_Model_Resource_Review_Collection */
        $collection = Mage::getResourceModel('productreviews/review_collection');
        $collection->getSelect()->having('COUNT(DISTINCT rt_down.vote_id) < ?', $helper->getMaxVotesDown());
        $collection->addFieldToFilter('main_table.status_id', Maketok_ProductReviews_Model_Review::STATUS_APPROVED);
        $collection->setPageSize($limit);
        return $collection;
    }

    /**
     * prepare collection items
     * @param Maketok_ProductReviews_Model_Resource_Review_Collection $collection
     * @return Maketok_ProductReviews_Model_Resource_Review_Collection
     */
    public function prepareLoadedCollection($collection)
    {
        foreach ($collection as $item)
        {
            /*
             * making multi-byte safe excerpt
             */
            /** @var $helper Maketok_ProductReviews_Helper_Data */
            $helper = Mage::helper('productreviews');
            $detail = $item->getDetail();
            if (mb_strlen($detail, "UTF-8") > $helper->getExcerptLength()) {
                $detail = iconv("UTF-8", "UTF-8//IGNORE", mb_substr($detail, 0, $helper->getExcerptLength(), "utf-8")) . '...';
            }
            $item->setDetail(Mage::helper('core')->escapeHtml($detail));
            $item->setNickname(Mage::helper('core')->escapeHtml($item->getNickname()));
            $item->setParentNickname(Mage::helper('core')->escapeHtml($item->getParentNickname()));
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $item->setProductUrl($product->getProductUrl());
            $item->setProductName($product->getName());
            $timeStamp = strtotime($item->getCreatedAt());
            $date = $helper->getDate($timeStamp);
            $item->setCreatedAt($date);
        }
        return $collection;
    }
}