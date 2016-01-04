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
 * Event observer
 */
class Yireo_Recaptcha_Model_Observer
{
    /**
     * Listen to the event controller_action_layout_load_before
     *
     * @parameter Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function controllerActionLayoutLoadBefore(Varien_Event_Observer $observer)
    {
        $overwrites = $this->getHelper()->getOverwrites();

        foreach ($overwrites as $layoutUpdate => $postUrl) {
            if (Mage::getStoreConfig('recaptcha/settings/overwrite_' . $layoutUpdate)) {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('recaptcha_' . $layoutUpdate);
            }
        }
    }

    /**
     * Listen to the event core_block_abstract_to_html_before
     *
     * @parameter Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        // Get the parameters from the event
        $transport = $observer->getEvent()->getTransport();
        $block = $observer->getEvent()->getBlock();
        if (empty($block) || !is_object($block)) {
            return $this;
        }

        // Re-insert the email-value for the forgotpassword-block
        $blockClass = 'Mage_Customer_Block_Account_Forgotpassword';
        if ($block instanceof $blockClass) {
            $block->setEmailValue(Mage::getSingleton('core/session')->getEmailValue());
        }

        return $this;
    }

    /**
     * Listen to the event controller_action_predispatch
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function controllerActionPredispatch(Varien_Event_Observer $observer)
    {
        /** @var Yireo_Recaptcha_Helper_Data $helper */
        $helper = $this->getHelper();

        if ($helper->useCaptcha() == false) {
            return $this;
        }

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $post = $request->getPost();

        // Check whether to apply Recaptcha
        $useRecaptcha = $this->useRecaptcha($request);
        if ($useRecaptcha == false) {
            return $this;
        }

        // Check for Recaptcha response
        Mage::app()->getResponse()->setHeader('X-Recaptcha-Checking', 1);
        $recaptchaValid = $this->getRecaptchaValid($request);

        // Recaptcha returned false
        if ($recaptchaValid == false) {

            // Return AJAX-errors first
            if ($request->isXmlHttpRequest()) {
                $result = array('error' => '1', 'message' => Mage::helper('core')->__('Invalid captcha'));
                print Mage::helper('core')->jsonEncode($result);
                exit;
            }

            // Set an error
            Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Invalid captcha'));

            // Remember POST-values from login-form
            if (isset($post['login']['username'])) {
                Mage::getSingleton('core/session')->setUsername($post['login']['username']);
            }

            // Remember POST-values from create-form
            if (stristr($request->getOriginalPathInfo(), 'customer/account/createpost')) {
                Mage::getSingleton('core/session')->setCustomerFormData($post);
            }

            // Remember POST-values from forgotpassword-form
            if (stristr($request->getOriginalPathInfo(), 'customer/account/forgotpassword') && isset($post['email'])) {
                Mage::getSingleton('core/session')->setEmailValue($post['email']);
            }

            // Remember POST-values from contact-form
            // @todo: does not work
            if (stristr($request->getOriginalPathInfo(), 'contact')) {
                Mage::getSingleton('core/session')->setContactFormData($post);
            }

            // Remember POST-values from sendfriend-form
            // @todo: does not work
            if (stristr($request->getOriginalPathInfo(), 'sendfriend')) {
                Mage::getSingleton('core/session')->setSendfriendFormData($post);
            }

            // Remember POST-values from review-form
            if (stristr($request->getOriginalPathInfo(), 'review')) {
                Mage::getSingleton('review/session')->setFormData($post);
            }

            // Determine the redirect URL
            $redirect_url = Mage::helper('core/http')->getHttpReferer();
            if (empty($redirect_url)) {
                $redirect_url = $request->getRequestUri();
            }

            // Redirect and exit
            $response = Mage::app()->getFrontController()->getResponse();
            $response->setRedirect($redirect_url);
            $response->sendResponse();

            exit;
        }

        return $this;
    }

    /**
     * Check whether Recaptcha should be applied to the request
     *
     * @param $request Mage_Core_Controller_Response_Http
     *
     * @return bool
     */
    public function useRecaptcha($request)
    {
        $post = $request->getPost();

        if ($request->isPost() == false) {
            return false;
        }

        if (Mage::helper('recaptcha/observer')->matchSkipUrls($request->getOriginalPathInfo())) {
            $this->getHelper()->debug('Recaptcha skipped for URL', $request->getOriginalPathInfo());
            return false;
        }

        // Whenever a CAPTCHA-field is present in the POST, enable checking
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                if (stristr($name, 'recaptcha')) {
                    $this->getHelper()->debug('Recaptcha enabled because of detected field', $name);
                    return true;
                }
            }
        }

        // Check for POST URLs configured in the code, and enable checking
        $overwrites = $this->getHelper()->getOverwrites();
        foreach ($overwrites as $layoutUpdate => $postUrl) {

            $refererUrl = $this->_getRefererUrl();
            $layoutConfig = Mage::getStoreConfig('recaptcha/settings/overwrite_' . $layoutUpdate);

            if ($layoutConfig && stristr($request->getOriginalPathInfo(), $postUrl)) {

                if ($layoutUpdate == 'customer_form_login' && stristr($refererUrl, 'checkout/onepage')) {
                    continue;
                }

                $this->getHelper()->debug('Original path info', $request->getOriginalPathInfo());
                $this->getHelper()->debug('Layout update', $layoutUpdate);
                $this->getHelper()->debug('Layout config', $layoutConfig);
                $this->getHelper()->debug('Referer URL', $refererUrl);
                $this->getHelper()->debug('Recaptcha enabled for POST URL', $postUrl);
                return true;
            }
        }

        // Check for POST URLs configured in the configuration, and enable checking
        $custom_urls = $this->getHelper()->getCustomUrls();
        foreach ($custom_urls as $custom_url) {
            if (stristr($request->getOriginalPathInfo(), $custom_url)) {
                $this->getHelper()->debug('Recaptcha enabled for custom URL', $custom_url);
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the Recaptcha response is correct
     *
     * @return bool
     */
    protected function getRecaptchaValid($request)
    {
        $remoteIp = $request->getServer('REMOTE_ADDR');

        // Initialize reCAPTCHA
        $this->getHelper()->includeRecaptcha();

        $secretKey = Mage::getStoreConfig('recaptcha/settings/secret_key');
        $recaptcha = new \ReCaptcha\ReCaptcha($secretKey);

        $response = $recaptcha->verify($request->getPost('g-recaptcha-response'), $remoteIp);

        $recaptchaValid = ($response != null && $response->isSuccess()) ? true : false;
        if ($recaptchaValid) {
            return true;
        }

        if ($response) {
            foreach ($response->getErrorCodes() as $code) {
                $this->getHelper()->debug('ReCaptcha error: ' . $code);
            }
        }

        return $recaptchaValid;
    }

    /**
     * Return the referer URL
     *
     * @return mixed|string
     */
    protected function _getRefererUrl()
    {
        $request = Mage::app()->getRequest();
        $refererUrl = $request->getServer('HTTP_REFERER');

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        return $refererUrl;
    }

    /**
     * @return Yireo_Recaptcha_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('recaptcha');
    }
}
