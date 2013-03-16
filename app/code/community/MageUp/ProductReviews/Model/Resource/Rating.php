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
class MageUp_ProductReviews_Model_Resource_Rating extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('productreviews/rating', 'rating_id');
    }

    /**
     * @param int|string $reviewId
     * @return string|bool
     */
    public function getRatingPercent($reviewId)
    {
        $connection = $this->getReadConnection();
        $select = $connection->select()
            ->from($this->getMainTable(),'percent')
            ->where('review_id = ?', $reviewId);
        return $connection->fetchOne($select);
    }

    /**
     * @param $productId
     * @return string
     */
    public function getRatingSummary($productId)
    {
        $connection = $this->getReadConnection();
        $select = $connection->select()
            ->from(array('main_table' => $this->getMainTable()),array())
            ->join(array('review_table' => $this->getTable('productreviews/review')),
                "`main_table`.`review_id` = `review_table`.`review_id`",
                array()
            )
            ->where('main_table.product_id = ?', $productId)
            ->where('review_table.status_id = ?', MageUp_ProductReviews_Model_Review::STATUS_APPROVED)
            ->columns(array('rating_summary' => 'AVG(`main_table`.`percent`)'));
        return $connection->fetchOne($select);
    }

    public function appendSummary(Varien_Data_Collection_Db $collection)
    {
        $collection->getSelect()
            ->joinLeft(array('mageup_review' => $this->getTable('productreviews/review')),
            "mageup_review.product_id = e.entity_id AND mageup_review.status_id = " . MageUp_ProductReviews_Model_Review::STATUS_APPROVED,
            array('rating_summary' => 'SUM(`mageup_review`.`review_id`)')
        )->group('e.entity_id');
    }
}