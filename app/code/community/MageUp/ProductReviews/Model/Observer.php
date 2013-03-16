<?php
/**
 * MageUp ProductReviews module
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
class MageUp_ProductReviews_Model_Observer
{
    public function removeMenuItems()
    {
        if (Mage::helper('productreviews')->isEnabled()){
            $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            unset($menu->catalog->children->reviews_ratings);
            unset($menu->report->children->review);
        } else { // remove rewrites
            $global = Mage::getConfig()->getNode('global');
            unset($global->blocks->adminhtml->rewrite->customer_edit_tab_reviews);
            unset($global->blocks->adminhtml->rewrite->catalog_product_edit_tab_reviews);
        }
    }

    public function checkRewrites()
    {
        if (!(Mage::helper('productreviews')->isEnabled())) {
            $global = Mage::getConfig()->getNode('global');
            unset($global->rewrite->productreviews_review);
            unset($global->rewrite->productreviews_customer);
            unset($global->blocks->review->rewrite->helper);
            unset($global->blocks->review->rewrite->customer_recent);
            // unset Layout
            unset(Mage::getConfig()->getNode('frontend')->layout->updates->productreviews);
        }
    }

    public function catalogBlockProductCollectionBeforeToHtml(Varien_Event_Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof Varien_Data_Collection) {
            Mage::getModel('productreviews/rating')->appendSummary($productCollection);
        }

        return $this;
    }
}