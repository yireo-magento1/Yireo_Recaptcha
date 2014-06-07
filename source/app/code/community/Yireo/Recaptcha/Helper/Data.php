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
        if(function_exists('_recaptcha_qsencode')) return;
        if(function_exists('_recaptcha_http_post')) return;

        require_once BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'Recaptcha'.DS.'Lib'.DS.'recaptchalib.php';
    }

    /*
     * Check whether CAPTCHA can be loaded or not
     * 
     * @access public
     * @parameter null
     * @return boolean
     */
    public function forceCaptcha()
    {
        if(Mage::getStoreConfig('web/recaptcha/enabled') == 0) {
            return false;
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn() == true) {
            if(Mage::getStoreConfig('web/recaptcha/captcha_for_loggedin') == 0) {
                return false;
            }
        }

        $public_key = trim(Mage::getStoreConfig('web/recaptcha/public_key'));
        $private_key = trim(Mage::getStoreConfig('web/recaptcha/private_key'));
        if(empty($public_key) || empty($private_key)) {
            return false;
        }

        return true;
    }
}
