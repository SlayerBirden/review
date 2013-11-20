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
class Maketok_ProductReviews_Model_Captcha_Recaptcha extends Maketok_ProductReviews_Model_Captcha_Abstract
{
    protected $_verifyUrl = 'http://www.google.com/recaptcha/api/verify';

    /**
     * @param $data
     * @return Varien_Object
     */
    public function verify($data)
    {
        $ch = curl_init();
        $queryParams = http_build_query($data);
        $curl_opts = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('Expect:'),
            CURLOPT_POSTFIELDS => $queryParams,
            CURLOPT_URL => $this->_verifyUrl,
        );
        curl_setopt_array($ch, $curl_opts);
        $resultCurl = curl_exec($ch);
        $resultArray = explode("\n", $resultCurl);
        $result = new Varien_Object();
        $result->setData(array(
            'result' => $resultArray[0],
            'message' => $resultArray[1]
        ));
        curl_close($ch);
        return $result;
    }
}