<?php
/**
 * Class Yireo_Recaptcha_Test_Integration_LoginTest
 *
 * Usage:
 * - Make sure guzzlehttp/guzzle is installed through composer
 * - Create a dummy PHP script in your Magento root with the following content:
 *
 * require_once 'vendor/autoload.php';
 * require_once 'app/Mage.php';
 * Mage::app();
 * $username = 'foo';
 * $password = 'bar';
 * (new Yireo_Recaptcha_Test_Integration_LoginTest())->run($username, $password);
 *
 * Run the script from the command line
 */

/**
 * Class Yireo_Recaptcha_Test_Integration_LoginTest
 */
class Yireo_Recaptcha_Test_Integration_LoginTest
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     * @throws Exception
     */
    public function run($username, $password)
    {
        $client = new \GuzzleHttp\Client(['cookies' => true]);
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar;
        $customerLoginUrl = Mage::getUrl('customer/account/login');
        $customerLoginPostUrl = Mage::getUrl('customer/account/loginPost');

        $res = $client->request('GET', $customerLoginUrl, ['cookies' => $cookieJar]);
        $body = $res->getBody();
        $formKey = '';

        if (preg_match('/\<input name="form_key" type="hidden" value="([a-zA-Z0-9]+)"/', $body, $match)) {
            $formKey = $match[1];
        }

        if (empty($formKey)) {
            throw new Exception('Not a valid form key found');
        }

        $data = [
            'form_key' => $formKey,
            'login' => [
                'username' => $username,
                'password' => $password
            ]
        ];

        $response = $client->request('POST', $customerLoginPostUrl, ['cookies' => $cookieJar, 'form_params' => $data]);
        $body = $response->getBody();

        if (stristr($body, 'Invalid captcha')) {
            throw new Yireo_Recaptcha_Exception_InvalidCaptcha('Invalid request is blocked by reCAPTCHA');
        }

        return true;
    }
}