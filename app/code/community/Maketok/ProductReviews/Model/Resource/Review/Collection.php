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
class Maketok_ProductReviews_Model_Resource_Review_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('productreviews/review');
    }

    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $thumbsUp = Maketok_ProductReviews_Model_Vote::THUMBS_UP;
        $thumbDown = Maketok_ProductReviews_Model_Vote::THUMB_DOWN;
        $helper = Mage::helper('productreviews');
        $this->getSelect()
            ->joinLeft(array('rt_up' => $this->getTable('productreviews/vote')),
        "rt_up.review_id = main_table.review_id AND rt_up.value = {$thumbsUp}",
        array('thumbs_up' => "COUNT(DISTINCT rt_up.vote_id)"))
            ->joinLeft(array('rt_down' => $this->getTable('productreviews/vote')),
        "rt_down.review_id = main_table.review_id AND rt_down.value = {$thumbDown}",
        array(
            'thumbs_down' => "COUNT(DISTINCT rt_down.vote_id)",
            'hide_state' => "IF(COUNT(DISTINCT rt_down.vote_id) >= '{$helper->getMaxVotesDown()}', 1, 0)",
        ))
            ->joinLeft(array('parent_tbl' => $this->getTable('productreviews/review')),
        "parent_tbl.review_id = main_table.parent_id",
        array(
            'parent_nickname' => "parent_tbl.nickname",
        ))
            ->joinLeft(array('rating_tbl' => $this->getTable('productreviews/rating')),
        "rating_tbl.review_id = main_table.review_id",
        array(
            'percent' => "IF(rating_tbl.percent, rating_tbl.percent, 0)",
        ))
            ->columns(array('has_parent'=>'IF(main_table.parent_id != 0, 1, 0)'))
            ->group('main_table.review_id');
        $customerId = 0;
        $ip = $_SERVER['REMOTE_ADDR'];
        $ipLong = ip2long($ip);
        /** @var $customer Mage_Customer_Model_Customer */
        if ($customer = Mage::helper('customer')->getCustomer()) {
            $customerId = $customer->getId();
        }
        if ($customerId) {
            $this->getSelect()
                ->joinLeft(array('rt_av' => $this->getTable('productreviews/vote')),
                "rt_av.review_id = main_table.review_id AND rt_av.customer_id = {$customerId}",
                array('exists' => "IF(COUNT(rt_av.vote_id) OR main_table.customer_id = {$customerId}, 1, 0)"));
        } else {
            $this->getSelect()
                ->joinLeft(array('rt_av' => $this->getTable('productreviews/vote')),
                "rt_av.review_id = main_table.review_id AND rt_av.remote_ip_long = {$ipLong}",
                array('exists' => "IF(COUNT(rt_av.vote_id) OR main_table.remote_ip_long = {$ipLong}, 1, 0)"));
        }
        return $this;
    }

    /**
     * Get SQL for get record count
     * rewrite to include reset for GROUP part
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->columns('COUNT(*)');

        return $countSelect;
    }
}