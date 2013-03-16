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
class MageUp_ProductReviews_Model_Ban extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('productreviews/ban');
        parent::_construct();
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function isIpBanned($ip)
    {
        $collection = $this->getCollection()->addFieldToFilter('remote_ip', $ip);
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * @param string|int $customerId
     * @return bool
     */
    public function isCustomerBanned($customerId)
    {
        if ($customerId != 0) {
            $collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);
            if ($collection->getSize()) {
                return true;
            }
        }
        return false;
    }
}