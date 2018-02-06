<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
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
     * @return string
     */
    public function getSiteKey()
    {
        return (string)trim($this->getStoreConfig('site_key'));
    }

    /**
     * List of blocks and URLs to override
     *
     * @param null
     *
     * @return string[]
     */
    protected function getConfigurationClasses()
    {
        return [
            Yireo_Recaptcha_Configuration_CustomerLogin::class,
            Yireo_Recaptcha_Configuration_CustomerRegistration::class,
            Yireo_Recaptcha_Configuration_CustomerForgotPassword::class,
            Yireo_Recaptcha_Configuration_ContactForm::class,
            Yireo_Recaptcha_Configuration_SendFriend::class,
            Yireo_Recaptcha_Configuration_Review::class,
            Yireo_Recaptcha_Configuration_OnepageBilling::class,
            Yireo_Recaptcha_Configuration_OnepageLogin::class,
        ];
    }

    /**
     * @return Yireo_Recaptcha_Configuration_Generic[]
     */
    public function getConfigurations()
    {
        $configurations = [];
        foreach ($this->getConfigurationClasses() as $configurationClass) {
            $configurations[] = new $configurationClass;
        }

        return $configurations;
    }

    /**
     * List of blocks and URLs to override
     *
     * @param null
     *
     * @return Yireo_Recaptcha_Configuration_Generic[]
     * @deprecated Use getConfigurations instead
     */
    public function getOverwrites()
    {
        return $this->getConfigurations();
    }

    /**
     * @param string $matchUrl
     * @param string[] $urls
     * @return bool
     */
    public function matchUrls($matchUrl, $urls)
    {
        foreach ($urls as $url) {
            if (stristr($url, $matchUrl)) {
                return true;
            }

            if (stristr($matchUrl, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load the configured custom URLs as simple array
     *
     * @param null
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
     * @param null
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
     * @param null
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
     * @param null
     *
     */
    public function includeRecaptcha()
    {
        if (class_exists(\ReCaptcha\Response::class, false)) {
            return;
        }

        if (class_exists(\ReCaptcha\ReCaptcha::class, false)) {
            return;
        }

        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/ReCaptcha.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Curl.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/CurlPost.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Post.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/Socket.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestMethod/SocketPost.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/RequestParameters.php';
        require BP . '/app/code/community/Yireo/Recaptcha/Lib/ReCaptcha/Response.php';
    }

    /**
     * Check whether CAPTCHA can be loaded or not
     *
     * @param null
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
        $debugging = (bool)$this->getStoreConfig('debugging');
        if ($debugging === false) {
            return;
        }

        if (!empty($variable)) {
            $message .= ' = ' . $variable;
        }

        Mage::log($message, null, 'yireo_recaptcha.log');
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
