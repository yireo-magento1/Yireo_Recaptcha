/**
 * Google reCAPTCHA extension for Magento
 *
 * @package     Yireo_Recaptcha
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL)
 */

var Recaptcha;

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['prototype'], $);
    } else {
        Recaptcha = factory($);
    }
})(function ($) {

    return {
        disableAllButtons: function (formElement) {
            var form = $(formElement).up('form');

            if (!form) {
                return false;
            }

            this.disableLoginButton(form);

            var that = this;
            var buttons = form.select('button.button').each(function (button) {
                that.disableButton(button);
            });

            return true;
        },

        disableLoginButton: function (form) {
            if (this.isLoginForm(form) == false) {
                return false;
            }

            buttons = $('checkout-step-login').select('button');
            if (buttons) {
                var that = this;
                buttons.forEach(function (button) {
                    that.disableButton(button);
                });
            }

            var registerButton = $('onepage-guest-register-button');
            if (registerButton) {
                this.disableButton(registerButton, true);
            }

            return true;
        },

        disableButton: function (button, allButtons) {
            return this.toggleButton(button, allButtons, false);
        },

        enableAllButtons: function (formElement) {
            var form = $(formElement).up('form');

            if (!form) {
                return false;
            }

            this.enableLoginButton(form);

            var that = this;
            var buttons = form.select('button.button').each(function (button) {
                that.enableButton(button);
            });
        },

        enableLoginButton: function (form) {
            if (this.isLoginForm(form) == false) {
                return false;
            }

            buttons = $('checkout-step-login').select('button');
            if (buttons) {
                var that = this;
                buttons.forEach(function (button) {
                    that.enableButton(button);
                });
            }

            var registerButton = $('onepage-guest-register-button');
            if (registerButton) {
                this.enableButton(registerButton, true);
            }

            return true;
        },

        enableButton: function (button, allButtons) {
            return this.toggleButton(button, allButtons, true);
        },

        isLoginForm: function (form) {
            if (form.id !== 'login-form') {
                return false;
            }

            if (typeof(loginForm) == 'undefined') {
                return false;
            }

            return true;
        },

        toggleButton: function (button, allButtons, visible) {
            if ((allButtons == false || allButtons == undefined) && button.type !== 'submit') {
                return false;
            }

            button.disabled = (!visible);

            if (visible) {
                button.removeClassName('disabled');
            } else {
                button.addClassName('disabled');
            }
        }
    };
});

