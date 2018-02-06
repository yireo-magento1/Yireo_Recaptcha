<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_CustomerRegistration extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'customer_form_register';

    /**
     * @var array
     */
    protected $validationUrls = ['customer/account/createPost/'];

    /**
     * @var array
     */
    protected $displayUrls = ['customer/account/create/'];
}