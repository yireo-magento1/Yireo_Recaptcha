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
 * Event observer
 */
class Yireo_Recaptcha_Observer_CheckRecaptchaResponse
{
    /**
     * @var Yireo_Recaptcha_Helper_Data
     */
    protected $moduleHelper;

    /**
     * @var Yireo_Recaptcha_Helper_Observer
     */
    protected $observerHelper;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $request;

    /**
     * @var Zend_Controller_Response_Http
     */
    protected $response;

    /**
     * @var Mage_Core_Model_Session
     */
    protected $coreSession;

    /**
     * @var Mage_Review_Model_Session
     */
    protected $reviewSession;

    /**
     * @var Mage_Core_Helper_Abstract
     */
    protected $coreHelper;

    /**
     * Yireo_Recaptcha_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->moduleHelper = Mage::helper('recaptcha');
        $this->observerHelper = Mage::helper('recaptcha/observer');
        $this->request = Mage::app()->getRequest();
        $this->response = Mage::app()->getResponse();
        $this->coreSession = Mage::getSingleton('core/session');
        $this->reviewSession = Mage::getSingleton('review/session');
        $this->coreHelper = Mage::helper('core');

        $this->moduleHelper->includeRecaptcha();
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     * @event controller_action_predispatch
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if ($this->moduleHelper->useCaptcha() === false) {
            return $this;
        }

        // Check whether to apply Recaptcha
        if ($this->useRecaptcha() === false) {
            return $this;
        }

        // Set a response header for later checking
        $this->response->setHeader('X-Recaptcha-Checking', 1);

        // Recaptcha returned valid
        if ($this->getRecaptchaValid() === true) {
            return $this;
        }

        // Return AJAX-errors first
        if ($this->request->isXmlHttpRequest()) {
            $this->sendJsonError();
        }

        // Set an error
        $this->coreSession->addError($this->moduleHelper->__('Invalid captcha'));

        // Set the original data in the session
        $this->setOriginalSessionInformation($this->request);

        // Determine the redirect URL
        $redirectUrl = Mage::helper('core/http')->getHttpReferer();
        if (empty($redirectUrl)) {
            $redirectUrl = $this->request->getRequestUri();
        }

        $this->redirectToUrl($redirectUrl);
    }

    /**
     * Send a JSON error response
     */
    protected function sendJsonError()
    {
        $data = array('error' => '1', 'message' => $this->moduleHelper->__('Invalid captcha'));
        $response = $this->response;
        $response->setHeader('Content-type', 'application/json');
        $response->setBody(Mage::helper('core')->jsonEncode($data));
        $response->sendResponse();
        exit;
    }

    /**
     * @param $redirectUrl
     */
    protected function redirectToUrl($redirectUrl)
    {
        $response = $this->response;
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
            $this->coreSession->setUsername($post['login']['username']);
        }

        // Remember POST-values from create-form
        if (stristr($request->getOriginalPathInfo(), 'customer/account/createpost')) {
            $this->coreSession->setCustomerFormData($post);
        }

        // Remember POST-values from forgotpassword-form
        if (stristr($request->getOriginalPathInfo(), 'customer/account/forgotpassword') && isset($post['email'])) {
            $this->coreSession->setEmailValue($post['email']);
        }

        // Remember POST-values from contact-form
        // @todo: does not work
        if (stristr($request->getOriginalPathInfo(), 'contact')) {
            $this->coreSession->setContactFormData($post);
        }

        // Remember POST-values from sendfriend-form
        // @todo: does not work
        if (stristr($request->getOriginalPathInfo(), 'sendfriend')) {
            $this->coreSession->setSendfriendFormData($post);
        }

        // Remember POST-values from review-form
        if (stristr($request->getOriginalPathInfo(), 'review')) {
            $this->reviewSession->setFormData($post);
        }
    }

    /**
     * Check whether Recaptcha should be applied to the request
     *
     * @return bool
     */
    protected function useRecaptcha()
    {
        $post = $this->request->getPost();

        if ($this->request->isPost() == false) {
            return false;
        }

        if ($this->observerHelper->matchSkipUrls($this->request->getOriginalPathInfo())) {
            $this->moduleHelper->debug('ReCaptcha skipped for URL', $this->request->getOriginalPathInfo());
            return false;
        }

        // Whenever a CAPTCHA-field is present in the POST, enable checking
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                if (stristr($name, 'recaptcha')) {
                    $this->moduleHelper->debug('ReCaptcha enabled because of detected field', $name);
                    return true;
                }
            }
        }

        // Check for POST URLs configured in the code, and enable checking
        $configurations = $this->moduleHelper->getConfigurations();
        foreach ($configurations as $configuration) {

            $requestPath = $this->request->getOriginalPathInfo();
            if (!$this->moduleHelper->matchUrls($requestPath, $configuration->getValidationUrls())) {
                continue;
            }

            $refererUrl = $this->getRefererUrl();
        $layoutHandle = $configuration->getLayoutHandle();
            $layoutEnabled = (bool)$this->moduleHelper->getStoreConfig('overwrite_' . $configuration->getId());
            if ($layoutEnabled === false) {
                continue;
            }

            $this->moduleHelper->debug('Request URI', $this->request->getRequestUri());
            $this->moduleHelper->debug('Original path info', $this->request->getOriginalPathInfo());
            $this->moduleHelper->debug('Layout update "'.$layoutHandle.'" enabled');
            $this->moduleHelper->debug('Referer URL', $refererUrl);
            return true;
        }

        // Check for POST URLs configured in the configuration, and enable checking
        $customUrls = $this->moduleHelper->getCustomUrls();
        foreach ($customUrls as $customUrl) {
            if (stristr($this->request->getOriginalPathInfo(), $customUrl)) {
                $this->moduleHelper->debug('Recaptcha enabled for custom URL', $customUrl);
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
    protected function getRecaptchaValid()
    {
        $remoteIp = $this->request->getServer('REMOTE_ADDR');
        $secretKey = Mage::getStoreConfig('recaptcha/settings/secret_key');
        $clientResponse = $this->request->getPost('g-recaptcha-response');

        $reCaptcha = new \ReCaptcha\ReCaptcha($secretKey);
        $response = $reCaptcha->verify($clientResponse, $remoteIp);

        $reCaptchaValid = ($response !== null && $response->isSuccess()) ? true : false;
        if ($reCaptchaValid) {
            return true;
        }

        if ($response) {
            foreach ($response->getErrorCodes() as $code) {
                $this->moduleHelper->debug('ReCaptcha error: ' . $code);
            }
        }

        return false;
    }

    /**
     * Return the referer URL
     *
     * @return string
     */
    protected function getRefererUrl()
    {
        $request = $this->request;

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_REFERER_URL)) {
            return $url;
        }

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_BASE64_URL)) {
            return $this->coreHelper->urlDecode($url);
        }

        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED)) {
            return $this->coreHelper->urlDecode($url);
        }

        return $request->getServer('HTTP_REFERER');
    }
}
