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
        foreach($overwrites as $layout_update => $post_url) {
            if(Mage::getStoreConfig('web/recaptcha/overwrite_'.$layout_update)) {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('recaptcha_'.$layout_update);
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
        if(empty($block) || !is_object($block)) {
            return $this;
        }

        // Re-insert the email-value for the forgotpassword-block
        if(get_class($block) == 'Mage_Customer_Block_Account_Forgotpassword') {
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
        if(Mage::helper('recaptcha')->forceCaptcha() == false) {
            return $this;
        }

        $request = $observer->getEvent()->getControllerAction()->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();
            $use_recaptcha = false;

            // Whenever a CAPTCHA-field is present in the POST, enable checking
            if(is_array($post)) {
                foreach($post as $name => $value) {
                    if(preg_match('/^recaptcha_/', $name)) {
                        $use_recaptcha = true;
                        break;
                    }
                }
            }

            // Check for POST URLs configured in the code, and enable checking
            if($use_recaptcha == false) {
                $overwrites = Mage::helper('recaptcha')->getOverwrites();
                foreach($overwrites as $layout_update => $post_url) {
                    if(Mage::getStoreConfig('web/recaptcha/overwrite_'.$layout_update) && stristr($request->getOriginalPathInfo(),$post_url)) {
                        $use_recaptcha = true;
                        break;
                    }
                }
            }

            // Check for POST URLs configured in the configuration, and enable checking
            if($use_recaptcha == false) {
                $custom_urls = Mage::helper('recaptcha')->getCustomUrls();
                foreach($custom_urls as $custom_url) {
                    if(stristr($request->getOriginalPathInfo(), $custom_url)) {
                        $use_recaptcha = true;
                        break;
                    }
                }
            }

            // If reCAPTCHA should be applied (and checked here)
            if($use_recaptcha == true) {

                // Initialize reCAPTCHA
                Mage::helper('recaptcha')->includeRecaptcha();
                $private_key = Mage::getStoreConfig('web/recaptcha/private_key');
                $recaptcha = recaptcha_check_answer($private_key,
                    $_SERVER['REMOTE_ADDR'],
                    $request->getPost('recaptcha_challenge_field'),
                    $request->getPost('recaptcha_response_field')
                );

                // Recaptcha returned false
                if ($recaptcha->is_valid == false) {

                    // Return AJAX-errors first
                    if($request->isXmlHttpRequest()) {
                        $result = array('error' => '1', 'message' => Mage::helper('core')->__('Invalid captcha'));
                        print Mage::helper('core')->jsonEncode($result);
                        exit; 
                    }

                    // Set an error
                    Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Invalid captcha'));

                    // Remember POST-values from login-form
                    if(isset($post['login']['username'])) {
                        Mage::getSingleton('core/session')->setUsername($post['login']['username']);
                    }

                    // Remember POST-values from create-form
                    if(stristr($request->getOriginalPathInfo(), 'customer/account/createpost')) {
                        Mage::getSingleton('core/session')->setCustomerFormData($post);
                    }

                    // Remember POST-values from forgotpassword-form
                    if(stristr($request->getOriginalPathInfo(), 'customer/account/forgotpassword') && isset($post['email'])) {
                        Mage::getSingleton('core/session')->setEmailValue($post['email']);
                    }

                    // Remember POST-values from contact-form (@todo: does not work)
                    if(stristr($request->getOriginalPathInfo(), 'contact')) {
                        Mage::getSingleton('core/session')->setContactFormData($post);
                    }

                    // Remember POST-values from sendfriend-form (@todo: does not work)
                    if(stristr($request->getOriginalPathInfo(), 'sendfriend')) {
                        Mage::getSingleton('core/session')->setSendfriendFormData($post);
                    }

                    // Remember POST-values from review-form
                    if(stristr($request->getOriginalPathInfo(), 'review')) {
                        Mage::getSingleton('review/session')->setFormData($post);
                    }

                    // Determine the redirect URL
                    $redirect_url = Mage::helper('core/http')->getHttpReferer();
                    if(empty($redirect_url)) {
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
}
