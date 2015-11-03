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
class Yireo_Recaptcha_Block_Default extends Yireo_Recaptcha_Block_Abstract
{
    /**
     * Method to return an unique block ID
     *
     * @parameter null
     * @return integer
     */
    public function getUniqueId()
    {
        static $counter = 0;
        $counter++;

        return $counter;
    }

    /**
     * Overriden method _toHtml() to add CAPTCHA when needed
     *
     * @parameter null
     * @return string
     */
    public function _toHtml()
    {
        // If CAPTCHA is not enabled, return nothing
        if(Mage::helper('recaptcha')->useCaptcha() == false) {
            return null;
        }

        $mode = Mage::helper('recaptcha')->getMode();
        if($mode == 'legacy') {
            return $this->addLegacyHtml();
        } else {
            return $this->addHtml();
        }
    }

    /**
     * Add the legacy reCaptcha code
     *
     * @parameter null
     * @return string
     */
    public function addLegacyHtml()
    {
        // Load variables
        $public_key = $this->getPublicKey();
        $private_key = $this->getPrivateKey();
        $theme = $this->getTheme();
        $api = $this->getApi();
        $lang_code = $this->getLangCode();
        $unique_id = $this->getUniqueId();
        if(empty($theme)) $theme = 'clean';

        // Output the custom template
        if($theme == 'custom') {
            $this->setPublicKey($public_key);
            $this->setLangCode($lang_code);
            $this->setTemplate('recaptcha/legacy/custom.phtml');
            return parent::_toHtml();
        }

        // Helper-method to include the CAPTCHA-library
        Mage::helper('recaptcha')->includeRecaptcha();

        // Load the right scheme
        $ssl = (Mage::app()->getRequest()->getScheme() == 'https') ? true : false ;

        $html = null;
        if($api == 'ajax') {
            $html .= "<script>\n"
                . "window.onload = function() {\n"
                . "    Recaptcha.create('".$public_key."', 'recaptcha_div_".$unique_id."', {"
                . "        theme : '".$theme."',"
                . "        lang : '".Mage::app()->getLocale()->getLocaleCode()."',"
                . "        callback: Recaptcha.focus_response_field"
                . " })};"
                . "</script>"
                . "<div id=\"recaptcha_div_".$unique_id."\"></div>"
            ;
        } else {
            $html .= "<script>\n"
                . " var RecaptchaOptions = {\n"
                . "     theme : '".$theme."',\n"
                . "     lang : '".Mage::app()->getLocale()->getLocaleCode()."'\n"
                . " };\n"
                . "</script>"
                . recaptcha_get_html($public_key, null, $ssl)
            ;
        }

        return $html;
    }

    /**
     * Add the current reCaptcha code
     *
     * @parameter null
     * @return string
     */
    public function addHtml()
    {
        // Load variables
        $theme = $this->getTheme();

        // Output the custom template
        if($theme == 'custom') {
            $this->setTemplate('recaptcha/custom.phtml');
            return parent::_toHtml();
        }

        // Helper-method to include the CAPTCHA-library
        Mage::helper('recaptcha')->includeRecaptcha();

        $this->setTemplate('recaptcha/default.phtml');
        return parent::_toHtml();
    }
}
