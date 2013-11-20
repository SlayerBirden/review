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
class Maketok_ProductReviews_Model_Rating extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('productreviews/rating');
        parent::_construct();
    }

    /**
     * @param int|string $review_id
     * @return string|int
     */
    public function getRatingPercent($review_id)
    {
        $res = $this->getResource()->getRatingPercent($review_id);
        if ($res) {
            return $res;
        } else {
            return 0;
        }
    }

    /**
     * @param int|string $productId
     * @return string|int
     */
    public function getRatingSummary($productId)
    {
        $res = $this->getResource()->getRatingSummary($productId);
        if ($res) {
            return $res;
        } else {
            return 0;
        }
    }

    public function appendSummary(Varien_Data_Collection_Db $collection)
    {
        $this->getResource()->appendSummary($collection);
    }
}