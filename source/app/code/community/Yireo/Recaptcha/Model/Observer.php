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
    /*
     * Listen to the event controller_action_layout_load_before
     *
     * @access public
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function controllerActionLayoutLoadBefore($observer)
    {
        $overwrites = Mage::helper('recaptcha')->getOverwrites();
        $mode = Mage::helper('recaptcha')->getMode();

        foreach ($overwrites as $layoutUpdate => $postUrl) {
            if ($mode == 'new' && Mage::getStoreConfig('recaptcha/settings/overwrite_' . $layoutUpdate)) {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('recaptcha_' . $layoutUpdate);
            } elseif ($mode == 'legacy' && Mage::getStoreConfig('web/recaptcha/overwrite_' . $layoutUpdate)) {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('recaptcha_' . $layoutUpdate);
            }
        }
    }

    /*
     * Listen to the event core_block_abstract_to_html_before
     *
     * @access public
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function coreBlockAbstractToHtmlBefore($observer)
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

    /*
     * Listen to the event controller_action_predispatch
     *
     * @access public
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function controllerActionPredispatch($observer)
    {
        if (Mage::helper('recaptcha')->useCaptcha() == false) {
            return $this;
        }

        $mode = Mage::helper('recaptcha')->getMode();
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $remoteIp = $request->getServer('REMOTE_ADDR');

        $skip_urls = Mage::helper('recaptcha')->getSkipUrls();
        foreach ($skip_urls as $skip_url) {
            if (stristr($request->getOriginalPathInfo(), $skip_url)) {
                Mage::helper('recaptcha')->debug('Recaptcha skipped for URL', $skip_url);
                return $this;
            }
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $useRecaptcha = false;

            // Whenever a CAPTCHA-field is present in the POST, enable checking
            if (is_array($post)) {
                foreach ($post as $name => $value) {
                    if (stristr($name, 'recaptcha')) {
                        Mage::helper('recaptcha')->debug('Recaptcha enabled because of detected field', $name);
                        $useRecaptcha = true;
                        break;
                    }
                }
            }

            // Check for POST URLs configured in the code, and enable checking
            if ($useRecaptcha == false) {
                $overwrites = Mage::helper('recaptcha')->getOverwrites();
                foreach ($overwrites as $layoutUpdate => $postUrl) {

                    $refererUrl = $this->_getRefererUrl();
                    if ($mode == 'legacy') {
                        $layoutConfig = Mage::getStoreConfig('web/recaptcha/overwrite_' . $layoutUpdate);
                    } else {
                        $layoutConfig = Mage::getStoreConfig('recaptcha/settings/overwrite_' . $layoutUpdate);
                    }

                    if ($layoutConfig && stristr($request->getOriginalPathInfo(), $postUrl)) {

                        if ($layoutUpdate == 'customer_form_login' && stristr($refererUrl, 'checkout/onepage')) {
                            continue;
                        }

                        Mage::helper('recaptcha')->debug('Original path info', $request->getOriginalPathInfo());
                        Mage::helper('recaptcha')->debug('Layout update', $layoutUpdate);
                        Mage::helper('recaptcha')->debug('Layout config', $layoutConfig);
                        Mage::helper('recaptcha')->debug('Referer URL', $refererUrl);
                        Mage::helper('recaptcha')->debug('Recaptcha enabled for POST URL', $postUrl);
                        $useRecaptcha = true;
                        break;
                    }
                }
            }

            // Check for POST URLs configured in the configuration, and enable checking
            if ($useRecaptcha == false) {
                $custom_urls = Mage::helper('recaptcha')->getCustomUrls();
                foreach ($custom_urls as $custom_url) {
                    if (stristr($request->getOriginalPathInfo(), $custom_url)) {
                        Mage::helper('recaptcha')->debug('Recaptcha enabled for custom URL', $custom_url);
                        $useRecaptcha = true;
                        break;
                    }
                }
            }

            // If reCAPTCHA should be applied (and checked here)
            if ($useRecaptcha == true) {

                $recaptchaValid = true;

                // Initialize reCAPTCHA
                Mage::helper('recaptcha')->includeRecaptcha();

                // New mode
                if ($mode == 'new') {

                    $secretKey = Mage::getStoreConfig('recaptcha/settings/secret_key');
                    $recaptcha = new ReCaptcha($secretKey);

                    $response = $recaptcha->verifyResponse(
                        $remoteIp,
                        $request->getPost('g-recaptcha-response')
                    );

                    $recaptchaValid = ($response != null && $response->success) ? true : false;

                    // Legacy mode
                } elseif ($mode == 'legacy') {

                    $privateKey = Mage::getStoreConfig('web/recaptcha/private_key');
                    $recaptcha = recaptcha_check_answer($privateKey,
                        $remoteIp,
                        $request->getPost('recaptcha_challenge_field'),
                        $request->getPost('recaptcha_response_field')
                    );

                    $recaptchaValid = (bool) $recaptcha->is_valid;
                }

                // Recaptcha returned false
                if ($recaptchaValid == false) {

                    // Return AJAX-errors first
                    if ($request->isXmlHttpRequest()) {
                        $result = array('error' => '1', 'message' => Mage::helper('core')->__('Invalid captcha (%s)', $mode));
                        print Mage::helper('core')->jsonEncode($result);
                        exit;
                    }

                    // Set an error
                    Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Invalid captcha (%s)', $mode));

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

                    // Remember POST-values from contact-form (@todo: does not work)
                    if (stristr($request->getOriginalPathInfo(), 'contact')) {
                        Mage::getSingleton('core/session')->setContactFormData($post);
                    }

                    // Remember POST-values from sendfriend-form (@todo: does not work)
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
                        $redirect_url = $request->getRequestString();
                    }

                    // Redirect and exit
                    $response = Mage::app()->getFrontController()->getResponse();
                    $response->setRedirect($redirect_url);
                    $response->sendResponse();
                    exit;
                }
            }
        }

        return $this;
    }

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
}
