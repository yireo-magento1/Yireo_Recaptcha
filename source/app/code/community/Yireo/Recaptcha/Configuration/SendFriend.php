<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_SendFriend extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'sendfriend_send';

    /**
     * @var array
     */
    protected $validationUrls = ['sendfriend/product/send/'];

    /**
     * @var array
     */
    protected $displayUrls = ['sendfriend/product/send/'];
}