<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_OnepageBilling extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'checkout_onepage_billing';

    /**
     * @var array
     */
    protected $validationUrls = ['checkout/onepage/saveBilling/'];

    /**
     * @var array
     */
    protected $displayUrls = ['checkout/'];
}