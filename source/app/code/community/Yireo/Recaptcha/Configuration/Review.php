<?php
/**
 * Google Recaptcha for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_Recaptcha_Configuration_Review extends Yireo_Recaptcha_Configuration_Generic
{
    /**
     * @var string
     */
    protected $id = 'review_form';

    /**
     * @var array
     */
    protected $validationUrls = ['review/product/post/'];

    /**
     * @var array
     */
    protected $displayUrls = ['review/product/'];
}