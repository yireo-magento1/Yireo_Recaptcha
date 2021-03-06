<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * Observer helper
 */
class Yireo_Recaptcha_Helper_Observer
{
    /**
     * @param $url
     *
     * @return bool
     */
    public function matchSkipUrls($url)
    {
        $skipUrls = Mage::helper('recaptcha')->getSkipUrls();
        foreach ($skipUrls as $skipUrl) {
            if (stristr($url, $skipUrl)) {
                return true;
            }
        }

        return false;
    }
}
