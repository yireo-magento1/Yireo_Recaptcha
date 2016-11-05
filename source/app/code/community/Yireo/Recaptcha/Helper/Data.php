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
 * General helper
 */
class Yireo_Recaptcha_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Switch to determine whether this extension is enabled or not
     *
     * @return bool
     */
    public function enabled()
    {
        if ((bool)$this->getStoreConfig('advanced/modules_disable_output/Yireo_Recaptcha', false)) {
            return false;
        }

        if ((bool)$this->getStoreConfig('enabled') === false) {
            return false;
        }

        return true;
    }

    /**
     * List of blocks and URLs to override
     *
     * @parameter null
     *
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

    /**
     * Load the configured custom URLs as simple array
     *
     * @parameter null
     *
     * @return array
     */
    public function getCustomUrls()
    {
        $value = $this->getStoreConfig('custom_urls');
        return $this->getArrayFromString($value);
    }

    /**
     * Load the configured skip URLs as simple array
     *
     * @parameter null
     *
     * @return array
     */
    public function getSkipUrls()
    {
        $value = $this->getStoreConfig('skip_urls');
        return $this->getArrayFromString($value);
    }

    /**
     * Convert a CSV string into an array
     *
     * @parameter null
     *
     * @return array
     */
    public function getArrayFromString($value)
    {
        $return = array();
        if (!empty($value)) {
            $value = str_replace("\n", ',', $value);
            $values = explode(',', $value);
            foreach ($values as $value) {
                $value = trim($value);
                if (!empty($value)) {
                    $return[] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Include the CAPTCHA library
     *
     * @parameter null
     *
     */
    public function includeRecaptcha()
    {
        if (class_exists('\ReCaptcha\Response', false)) {
            return;
        }

        if (class_exists('\ReCaptcha\ReCaptcha', false)) {
            return;
        }

        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/ReCaptcha.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/Response.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestParameters.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Curl.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Post.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/CurlPost.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Socket.php';
        require_once BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/SocketPost.php';
    }

    /**
     * Check whether CAPTCHA can be loaded or not
     *
     * @parameter null
     *
     * @return boolean
     */
    public function useCaptcha()
    {
        if ($this->enabled() === false) {
            return false;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn() === true) {
            if ($this->getStoreConfig('captcha_for_loggedin') === 0) {
                return false;
            }
        }

        if ($this->isValidKeys() === false) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isValidKeys()
    {
        $siteKey = trim($this->getStoreConfig('site_key'));
        $secretKey = trim($this->getStoreConfig('secret_key'));

        if (empty($siteKey) || empty($secretKey)) {
            return false;
        }

        return true;
    }

    /**
     * @param $message
     * @param null $variable
     */
    public function debug($message, $variable = null)
    {
        $debugging = $this->getStoreConfig('debugging');
        if ($debugging == false) {
            return;
        }

        if (!empty($variable)) {
            $message .= ' = ' . $variable;
        }

        Mage::log($message, 'yireo_recaptcha.log');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getStoreConfig($value, $prefix = true)
    {
        if ($prefix) {
            $value = 'recaptcha/settings/' . $value;
        }

        return Mage::getStoreConfig($value);
    }
}
