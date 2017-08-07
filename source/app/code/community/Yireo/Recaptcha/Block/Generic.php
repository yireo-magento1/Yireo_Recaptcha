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
class Yireo_Recaptcha_Block_Generic extends Mage_Core_Block_Template
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
        $this->moduleHelper = Mage::helper('recaptcha');

        if ($this->moduleHelper->useCaptcha() === false) {
            return;
        }

        parent::_construct();

        $this->setSiteKey($this->getConfig('site_key'));
        $this->setSecretKey($this->getConfig('secret_key'));
        $this->setTheme($this->getConfig('theme'));
        $this->setLangCode($this->getLanguageCode());
    }

    /**
     * @param string $siteKey
     */
    public function setSiteKey($siteKey)
    {
        $this->setData('site_key', trim($siteKey));
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->setData('secret_key', trim($secretKey));
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->setData('theme', $theme);
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
     * @param string $configName
     *
     * @return mixed
     */
    protected function getConfig($configName)
    {
        return Mage::getStoreConfig('recaptcha/settings/' . $configName);
    }
}
