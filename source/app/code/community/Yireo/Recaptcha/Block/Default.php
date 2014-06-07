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
class Yireo_Recaptcha_Block_Default extends Mage_Core_Block_Template
{
    /*
     * Method to return an unique block ID
     * 
     * @access public
     * @parameter null
     * @return integer
     */
    public function getUniqueId()
    {
        static $counter = 0;
        $counter++;
        return $counter;
    }

    /*
     * Overriden method _toHtml() to add CAPTCHA when needed
     * 
     * @access public
     * @parameter null
     * @return string
     */
    public function _toHtml()
    {
        // If CAPTCHA is not enabled, return nothing
        if(Mage::helper('recaptcha')->forceCaptcha() == false) {
            return null;
        }

        // Load variables
        $public_key = trim(Mage::getStoreConfig('web/recaptcha/public_key'));
        $private_key = trim(Mage::getStoreConfig('web/recaptcha/private_key'));
        $theme = Mage::getStoreConfig('web/recaptcha/theme');
        $api = Mage::getStoreConfig('web/recaptcha/api'); // ajax | default
        $lang_code = preg_replace('/_([a-zA-Z0-9]+)$/', '', Mage::app()->getLocale()->getLocaleCode());
        $unique_id = $this->getUniqueId();
        if(empty($theme)) $theme = 'clean';

        // Output the custom template
        if($theme == 'custom') {
            $this->setPublicKey($public_key);
            $this->setLangCode($lang_code);
            $this->setTemplate('recaptcha/custom.phtml');
            return parent::_toHtml();
        }

        // Helper-method to include the CAPTCHA-library
        Mage::helper('recaptcha')->includeRecaptcha();

        // Load the right scheme
        $scheme = Mage::app()->getRequest()->getScheme();
        $ssl = (Mage::app()->getRequest()->getScheme() == 'https') ? true : false ;

        $html = null;
        if($api == 'ajax') {
            $html .= "<script type=\"text/javascript\" src=\"".$scheme."://www.google.com/recaptcha/api/js/recaptcha_ajax.js\"></script>\n"
                . "<script type=\"text/javascript\">\n"
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
            $html .= "<script type=\"text/javascript\">\n"
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
}
