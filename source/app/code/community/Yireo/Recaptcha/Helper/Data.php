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
class Yireo_Recaptcha_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * List of blocks and URLs to override
     * 
     * @access public
     * @parameter null
     * @return array
     */
    public function getOverwrites()
    {
        return array(
            'customer_form_register' => 'customer/account/createPost/',
            'customer_form_login' => 'customer/account/loginPost/',
            'customer_form_forgotpassword' => 'customer/account/forgotPasswordPost/',
            'contacts_form' => 'contacts/index/post/',
            'sendfriend_send' => 'sendfriend/product/send/',
            'review_form' => 'review/product/post/',
            'checkout_onepage_login' => 'customer/account/loginPost/',
            'checkout_onepage_billing' => 'checkout/onepage/saveBilling/',
        );
    }

    /*
     * Load the configured custom URLs as simple array
     * 
     * @access public
     * @parameter null
     * @return array
     */
    public function getCustomUrls()
    {
        $value = Mage::getStoreConfig('web/recaptcha/custom_urls');
        $return = array();
        if(!empty($value)) {
            $value = str_replace("\n", ',', $value);
            $values = explode(',', $value);
            foreach($values as $value) {
                $value = trim($value);
                if(!empty($value)) {
                    $return[] = $value;
                }
            }
        }
        return $return;
    }

    /*
     * Include the CAPTCHA library
     * 
     * @access public
     * @parameter null
     * @return null
     */
    public function includeRecaptcha()
    {
        $mode = $this->getMode();
        if($mode == 'legacy') {
            if(function_exists('_recaptcha_qsencode')) return;
            if(function_exists('_recaptcha_http_post')) return;
            require_once BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'Recaptcha'.DS.'Lib'.DS.'Legacy'.DS.'recaptchalib.php';

        } else {
            if(class_exists('ReCaptchaResponse')) return;
            if(class_exists('ReCaptcha')) return;
            require_once BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'Recaptcha'.DS.'Lib'.DS.'recaptchalib.php';
        }
    }

    /*
     * Check whether CAPTCHA can be loaded or not
     * 
     * @access public
     * @parameter null
     * @return boolean
     */
    public function getMode()
    {
        $mode = false;
        if(Mage::getStoreConfig('recaptcha/settings/enabled') == 1) {
            $mode = 'new';
        } elseif(Mage::getStoreConfig('web/recaptcha/enabled') == 1) {
            $mode = 'legacy';
        }

        return $mode;
    }

    /*
     * Check whether CAPTCHA can be loaded or not
     * 
     * @access public
     * @parameter null
     * @return boolean
     */
    public function useCaptcha()
    {
        $mode = $this->getMode();
        if(empty($mode)) {
            return false;
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn() == true) {
            if($mode == 'new' && Mage::getStoreConfig('recaptcha/settings/captcha_for_loggedin') == 0) {
                return false;
            } elseif($mode == 'legacy' && Mage::getStoreConfig('web/recaptcha/captcha_for_loggedin') == 0) {
                return false;
            }
        }

        $siteKey = trim(Mage::getStoreConfig('recaptcha/settings/site_key'));
        $secretKey = trim(Mage::getStoreConfig('recaptcha/settings/secret_key'));
        $legacyPublicKey = trim(Mage::getStoreConfig('web/recaptcha/public_key'));
        $legacyPrivateKey = trim(Mage::getStoreConfig('web/recaptcha/private_key'));

        if($mode == 'new' && (empty($siteKey) || empty($secretKey))) {
            return false;
        } elseif($mode == 'legacy' && (empty($legacyPublicKey) || empty($legacyPrivateKey))) {
            return false;
        }

        return true;
    }
}
