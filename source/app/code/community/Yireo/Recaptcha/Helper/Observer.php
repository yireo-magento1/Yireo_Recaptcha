<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * Observer helper
 */
class Yireo_Recaptcha_Helper_Observer extends Mage_Core_Helper_Abstract
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
