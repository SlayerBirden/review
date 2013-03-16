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
class MageUp_ProductReviews_SidebarController extends Mage_Core_Controller_Front_Action
{
    public function updateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        $response->setCollection(0);
        $id = $this->getRequest()->getParam('last_id');
        try{
            /** @var $model MageUp_ProductReviews_Model_Sidebar */
            $model = Mage::getModel('productreviews/sidebar');
            $collection = $model->getReviewCollection();
            $collection->addFieldToFilter('main_table.review_id', array('gt' => $id));
            $collection->setOrder('main_table.review_id');
            $collection->load();
            if ($collection->getSize()) {
                $model->prepareLoadedCollection($collection);
                $response->setCollection($collection->toArray());
            } else {
                $response->setError(1); // we do not need to do anything is collection is empty
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $response->setError(1);
        }
        $this->getResponse()->setBody($response->toJson());
        return;
    }

}