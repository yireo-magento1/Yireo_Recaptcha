<?php
/**
 * Google Recaptcha for Magento 
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
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
        if(Mage::helper('recaptcha')->useCaptcha() == false) {
            return null;
        }

        $rt = parent::_construct();

        $mode = Mage::helper('recaptcha')->getMode();
        $this->setMode($mode);

        if($mode == 'legacy') {
            $this->setPublicKey(trim(Mage::getStoreConfig('web/recaptcha/public_key')));
            $this->setPrivateKey(trim(Mage::getStoreConfig('web/recaptcha/private_key')));
            $this->setTheme(Mage::getStoreConfig('web/recaptcha/theme'));
            $this->setApi(Mage::getStoreConfig('web/recaptcha/api')); // ajax | default

        } else {
            $this->setSiteKey(trim(Mage::getStoreConfig('recaptcha/settings/site_key')));
            $this->setSecretKey(trim(Mage::getStoreConfig('recaptcha/settings/secret_key')));
            $this->setTheme(Mage::getStoreConfig('recaptcha/settings/theme'));
        }

        $langCode = preg_replace('/_([a-zA-Z0-9]+)$/', '', Mage::app()->getLocale()->getLocaleCode());
        $this->setLangCode($langCode);
    
        return $rt;
    }
}
