<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_CustomerForgotPassword extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'customer_form_forgotpassword';

    /**
     * @var array
     */
    protected $validationUrls = ['customer/account/forgotPasswordPost/'];

    /**
     * @var array
     */
    protected $displayUrls = ['customer/account/forgotPassword/'];
}