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
 * Event observer
 */
class Yireo_Recaptcha_Model_Observer
{
    /**
     * @var Yireo_Recaptcha_Helper_Data
     */
    protected $moduleHelper;

    /**
     * Yireo_Recaptcha_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->moduleHelper = $this->getModuleHelper();
    }

    /**
     * Listen to the event controller_action_layout_load_before
     *
     * @event controller_action_layout_load_before
     *
     * @parameter Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function applyHandles(Varien_Event_Observer $observer)
    {
        $overwrites = $this->moduleHelper->getOverwrites();

        /** @var Mage_Core_Model_Layout_Update $layoutUpdate */
        $layoutUpdate = $observer->getEvent()->getLayout()->getUpdate();

        foreach ($overwrites as $layoutUpdateHandle => $postUrl) {
            if ($this->moduleHelper->getStoreConfig('overwrite_' . $layoutUpdateHandle)) {
                $layoutUpdate->addHandle('recaptcha_' . $layoutUpdateHandle);
            }
        }
    }

    /**
     * Listen to the event core_block_abstract_to_html_before
     *
     * @event core_block_abstract_to_html_before
     *
     * @parameter Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function customerAccountForgotpasswordSetEmailValue(Varien_Event_Observer $observer)
    {
        // Get the parameters from the event
        $block = $observer->getEvent()->getBlock();
        if (empty($block) || !is_object($block)) {
            return $this;
        }

        // Re-insert the email-value for the forgotpassword-block
        $blockClass = 'Mage_Customer_Block_Account_Forgotpassword';
        if ($block instanceof $blockClass) {
            $block->setEmailValue($this->getCoreSession()->getEmailValue());
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
    public function checkRecaptchaResponse(Varien_Event_Observer $observer)
    {
        if ($this->moduleHelper->useCaptcha() == false) {
            return $this;
        }

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getEvent()->getControllerAction()->getRequest();

        // Check whether to apply Recaptcha
        $useRecaptcha = $this->useRecaptcha($request);
        if ($useRecaptcha == false) {
            return $this;
        }

        // Check for Recaptcha response
        Mage::app()->getResponse()->setHeader('X-Recaptcha-Checking', 1);
        $recaptchaValid = $this->getRecaptchaValid($request);

        // Recaptcha returned false
        if ($recaptchaValid == true) {
            return $this;
        }

        // Return AJAX-errors first
        if ($request->isXmlHttpRequest()) {
            $result = array('error' => '1', 'message' => Mage::helper('core')->__('Invalid captcha'));
            print Mage::helper('core')->jsonEncode($result);
            exit;
        }

        // Set an error
        $this->getCoreSession()->addError(Mage::helper('core')->__('Invalid captcha'));

        // Set the original data in the session
        $this->setOriginalSessionInformation($request);

        // Determine the redirect URL
        $redirectUrl = Mage::helper('core/http')->getHttpReferer();
        if (empty($redirectUrl)) {
            $redirectUrl = $request->getRequestUri();
        }

        $this->redirectToUrl($redirectUrl);
    }

    /**
     * @param $redirectUrl
     */
    protected function redirectToUrl($redirectUrl)
    {
        // Redirect and exit
        $response = Mage::app()->getFrontController()->getResponse();
        $response->setRedirect($redirectUrl);
        $response->sendResponse();
        exit;
    }

    /**
     * @param $request Mage_Core_Controller_Request_Http
     */
    protected function setOriginalSessionInformation($request)
    {
        $post = $request->getPost();

        // Remember POST-values from login-form
        if (isset($post['login']['username'])) {
            $this->getCoreSession()->setUsername($post['login']['username']);
        }

        // Remember POST-values from create-form
        if (stristr($request->getOriginalPathInfo(), 'customer/account/createpost')) {
            $this->getCoreSession()->setCustomerFormData($post);
        }

        // Remember POST-values from forgotpassword-form
        if (stristr($request->getOriginalPathInfo(), 'customer/account/forgotpassword') && isset($post['email'])) {
            $this->getCoreSession()->setEmailValue($post['email']);
        }

        // Remember POST-values from contact-form
        // @todo: does not work
        if (stristr($request->getOriginalPathInfo(), 'contact')) {
            $this->getCoreSession()->setContactFormData($post);
        }

        // Remember POST-values from sendfriend-form
        // @todo: does not work
        if (stristr($request->getOriginalPathInfo(), 'sendfriend')) {
            $this->getCoreSession()->setSendfriendFormData($post);
        }

        // Remember POST-values from review-form
        if (stristr($request->getOriginalPathInfo(), 'review')) {
            Mage::getSingleton('review/session')->setFormData($post);
        }
    }

    /**
     *
     * @return Mage_Core_Model_Session
     */
    protected function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Check whether Recaptcha should be applied to the request
     *
     * @param $request Mage_Core_Controller_Response_Http
     *
     * @return bool
     */
    protected function useRecaptcha($request)
    {
        $post = $request->getPost();

        if ($request->isPost() == false) {
            return false;
        }

        if (Mage::helper('recaptcha/observer')->matchSkipUrls($request->getOriginalPathInfo())) {
            $this->moduleHelper->debug('Recaptcha skipped for URL', $request->getOriginalPathInfo());
            return false;
        }

        // Whenever a CAPTCHA-field is present in the POST, enable checking
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                if (stristr($name, 'recaptcha')) {
                    $this->moduleHelper->debug('Recaptcha enabled because of detected field', $name);
                    return true;
                }
            }
        }

        // Check for POST URLs configured in the code, and enable checking
        $overwrites = $this->moduleHelper->getOverwrites();
        foreach ($overwrites as $layoutUpdate => $postUrl) {

            $refererUrl = $this->getRefererUrl();
            $layoutConfig = $this->moduleHelper->getStoreConfig('overwrite_' . $layoutUpdate);

            if ($layoutConfig && stristr($request->getOriginalPathInfo(), $postUrl)) {

                if ($layoutUpdate == 'customer_form_login' && stristr($refererUrl, 'checkout/onepage')) {
                    continue;
                }

                $this->moduleHelper->debug('Original path info', $request->getOriginalPathInfo());
                $this->moduleHelper->debug('Layout update', $layoutUpdate);
                $this->moduleHelper->debug('Layout config', $layoutConfig);
                $this->moduleHelper->debug('Referer URL', $refererUrl);
                $this->moduleHelper->debug('Recaptcha enabled for POST URL', $postUrl);
                return true;
            }
        }

        // Check for POST URLs configured in the configuration, and enable checking
        $custom_urls = $this->moduleHelper->getCustomUrls();
        foreach ($custom_urls as $custom_url) {
            if (stristr($request->getOriginalPathInfo(), $custom_url)) {
                $this->moduleHelper->debug('Recaptcha enabled for custom URL', $custom_url);
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
        $this->moduleHelper->includeRecaptcha();

        $secretKey = Mage::getStoreConfig('recaptcha/settings/secret_key');
        $recaptcha = new \ReCaptcha\ReCaptcha($secretKey);

        $response = $recaptcha->verify($request->getPost('g-recaptcha-response'), $remoteIp);

        $recaptchaValid = ($response != null && $response->isSuccess()) ? true : false;
        if ($recaptchaValid) {
            return true;
        }

        if ($response) {
            foreach ($response->getErrorCodes() as $code) {
                $this->moduleHelper->debug('ReCaptcha error: ' . $code);
            }
        }

        return $recaptchaValid;
    }

    /**
     * Return the referer URL
     *
     * @return mixed|string
     */
    protected function getRefererUrl()
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
    protected function getModuleHelper()
    {
        return Mage::helper('recaptcha');
    }
}
