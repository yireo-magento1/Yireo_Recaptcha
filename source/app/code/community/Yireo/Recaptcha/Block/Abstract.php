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
 * General helper
 */
class Yireo_Recaptcha_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * @var Yireo_Recaptcha_Helper_Data
     */
    protected $moduleHelper;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->moduleHelper = $this->getModuleHelper();

        // If CAPTCHA is not enabled, return nothing
        if ($this->moduleHelper->useCaptcha() == false) {
            return;
        }

        parent::_construct();

        $this->setSiteKey(trim($this->getConfig('site_key')));
        $this->setSecretKey(trim($this->getConfig('secret_key')));
        $this->setTheme($this->getConfig('theme'));
        $this->setLangCode($this->getLanguageCode());
    }

    /**
     * @return bool
     */
    public function getBasicMode()
    {
        return (bool)$this->getConfig('basic_mode');
    }

    /**
     * @return string
     */
    protected function getLanguageCode()
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        return preg_replace('/_([a-zA-Z0-9]+)$/', '', $localeCode);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function getConfig($value)
    {
        return Mage::getStoreConfig('recaptcha/settings/' . $value);
    }

    /**
     * @return Yireo_Recaptcha_Helper_Data
     */
    protected function getModuleHelper()
    {
        return Mage::helper('recaptcha');
    }
}
