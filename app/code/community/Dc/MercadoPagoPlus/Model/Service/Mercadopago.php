<?php
/**
 * Dc_MercadoPagoPlus
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Dc
 * @package    Dc_MercadoPagoPlus
 * @copyright  Copyright (c) 2014-2015 Damián Culotta. (http://www.damianculotta.com.ar/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Dc_MercadoPagoPlus_Model_Service_Mercadopago extends Dc_MercadoPagoPlus_Model_Service_Abstract
{

    /**
     * Authenticate against API and return access token.
     *
     * @return mixed
     * @throws Exception
     */
    protected function authenticate()
    {
        try {
            //TODO: Validate which version of MercadoPago is installed.
            $api_client_id = Mage::getStoreConfig('payment/mpexpress/client_id');
            $api_client_secret = Mage::getStoreConfig('payment/mpexpress/client_secret');
            $endpoint = '/oauth/token';
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => $api_client_id,
                'client_secret' => $api_client_secret
            );
            $response = $this->getClient()->restPost($endpoint, $data);
            $response_body = json_decode($response->getBody());
            $access_token = $response_body->access_token;
            return $access_token;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Returns MercadoPago user id
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function getUserId()
    {
        try {
            $access_token = $this->authenticate();
            $data = array(
                'access_token' => $access_token,
            );
            $response = $this->getClient()->restGet('/users/me', $data);
            $response_body = json_decode($response->getBody());
            if ($response_body && $response_body->id) {
                return $response_body->id;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
