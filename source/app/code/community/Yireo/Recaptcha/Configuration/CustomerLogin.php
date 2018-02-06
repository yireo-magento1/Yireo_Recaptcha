<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_CustomerLogin extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'customer_form_login';

    /**
     * @var array
     */
    protected $validationUrls = ['customer/account/loginPost/'];

    /**
     * @var array
     */
    protected $displayUrls = ['customer/account/login/'];
}