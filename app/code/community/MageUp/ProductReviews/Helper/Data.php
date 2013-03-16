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
class MageUp_ProductReviews_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('productreviews/general/enabled');
    }

    /**
     * If new reviews do not need approval
     *
     * @return bool
     */
    public function getAutoApprove()
    {
        return Mage::getStoreConfigFlag('productreviews/settings/auto_approve');
    }

    /**
     * Is rating system enabled
     * @return bool
     */
    public function isRatingEnabled()
    {
        return Mage::getStoreConfigFlag('productreviews/general/rating_enabled');
    }

    /**
     * Is guest allowed to write a review
     * @return bool
     */
    public function isGuestAllowed()
    {
        return Mage::getStoreConfigFlag('productreviews/general/guest_allowed');
    }

    /**
     * Return max character number for review
     * @return int
     */
    public function getLimitReviewChars()
    {
        return (int) Mage::getStoreConfig('productreviews/settings/limit_chars');
    }

    /**
     * @return bool
     */
    public function getCaptchaEnabledForGuests()
    {
        return Mage::getStoreConfig('productreviews/captcha/enable_captcha') > 0;
    }

    /**
     * @return bool
     */
    public function getCaptchaEnabledForCustomers()
    {
        return Mage::getStoreConfig('productreviews/captcha/enable_captcha') == 2;
    }

    /**
     * Returns the number for votes down reaching which the post will be hidden
     * @return int
     */
    public function getMaxVotesDown()
    {
        return (int) Mage::getStoreConfig('productreviews/settings/max_votes_down');
    }

    /**
     * @return bool
     */
    public function getEnableLeftSidebar()
    {
        return Mage::getStoreConfig('productreviews/sidebar/enable_sidebar') == 1 ||
            Mage::getStoreConfig('productreviews/sidebar/enable_sidebar') == 3;
    }

    /**
     * @return bool
     */
    public function getEnableRightSidebar()
    {
        return Mage::getStoreConfig('productreviews/sidebar/enable_sidebar') == 2 ||
            Mage::getStoreConfig('productreviews/sidebar/enable_sidebar') == 3;
    }

    /**
     * @return int
     */
    public function getSidebarSort()
    {
        return (int) Mage::getStoreConfig('productreviews/sidebar/sort');
    }

    /**
     * @return int
     */
    public function getSidebarLimit()
    {
        return (int) Mage::getStoreConfig('productreviews/sidebar/limit');
    }

    /**
     * @return int
     */
    public function getExcerptLength()
    {
        return (int) Mage::getStoreConfig('productreviews/sidebar/excerpt_length');
    }

    /**
     * @return array
     */
    public function getReCaptchaKeys()
    {
        return array (
            'public' => Mage::helper('core')->decrypt(Mage::getStoreConfig('productreviews/captcha/recaptcha_public_key')),
            'private' => Mage::helper('core')->decrypt(Mage::getStoreConfig('productreviews/captcha/recaptcha_private_key')),
        );
    }

    /**
     * Get date in correct format
     * @param  int
     * @return string
     */
    public function getDate($timestamp)
    {
        $date = Mage::app()->getLocale()->date($timestamp);
        $now = Mage::app()->getLocale()->date();
        $yesterday = Mage::app()->getLocale()->date(strtotime('-1 day'));
        if ($date->compare($now, Zend_Date::DATE_FULL) === 0) {
            $date = $this->__('Today') . ' ' . date('H:i', $timestamp);
        } elseif ($date->compare($yesterday, Zend_Date::DATE_FULL) === 0) {
            $date = $this->__('Yesterday') . ' ' . date('H:i', $timestamp);
        } else {
            $date = Mage::helper('core')->formatDate($date, 'short', true);
        }
        return $date;
    }

    public function getSidebarUpdateInterval()
    {
        $return = 0;
        // this block will work only with Latest Review sorting mode
        if ((int) Mage::getStoreConfig('productreviews/sidebar/sort') == 1) {
            switch ((int) Mage::getStoreConfig('productreviews/sidebar/auto_update')) {
                case 1:
                    $return = 30000;
                    break;
                case 2:
                    $return = 60000;
                    break;
                case 3:
                    $return = 300000;
                    break;
                default:
                    $return = 0;
                    break;
            }
        }
        return $return;
    }
}