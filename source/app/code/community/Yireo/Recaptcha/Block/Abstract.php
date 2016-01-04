<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * General helper
 */
class Yireo_Recaptcha_Block_Abstract extends Mage_Core_Block_Template
{
    public function _construct()
    {
        // If CAPTCHA is not enabled, return nothing
        if ($this->getModuleHelper()->useCaptcha() == false) {
            return null;
        }

        $rt = parent::_construct();

        $this->setSiteKey(trim(Mage::getStoreConfig('recaptcha/settings/site_key')));
        $this->setSecretKey(trim(Mage::getStoreConfig('recaptcha/settings/secret_key')));
        $this->setTheme(Mage::getStoreConfig('recaptcha/settings/theme'));

        $langCode = preg_replace('/_([a-zA-Z0-9]+)$/', '', Mage::app()->getLocale()->getLocaleCode());
        $this->setLangCode($langCode);

        return $rt;
    }

    /**
     * @return bool
     */
    public function getBasicMode()
    {
        return (bool)Mage::getStoreConfig('recaptcha/settings/basic_mode');
    }

    /**
     * @return Yireo_Recaptcha_Helper_Data
     */
    protected function getModuleHelper()
    {
        return Mage::helper('recaptcha');
    }
}
