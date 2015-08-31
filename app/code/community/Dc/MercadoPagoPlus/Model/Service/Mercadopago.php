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
     * @var
     */
    private $client_id;

    /**
     * @var
     */
    private $client_secret;

    /**
     * Assign API credentials based on which module version is in use.
     */
    protected function assignCredentials()
    {
        $source = Mage::getStoreConfig('mercadopagoplus/credentials/source');
        switch ($source) {
            case Dc_MercadoPagoPlus_Model_System_Config_Source_Credentials_Source::CUSTOM:
                $this->client_id = Mage::getStoreConfig('mercadopagoplus/credentials/client_id');
                $this->client_secret = Mage::getStoreConfig('mercadopagoplus/credentials/client_secret');
                break;
            case Dc_MercadoPagoPlus_Model_System_Config_Source_Credentials_Source::MPEXPRESS:
                $this->client_id = Mage::getStoreConfig('payment/mpexpress/client_id');
                $this->client_secret = Mage::getStoreConfig('payment/mpexpress/client_secret');
                break;
            case Dc_MercadoPagoPlus_Model_System_Config_Source_Credentials_Source::MERCADOPAGO:
                $this->client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
                $this->client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
                break;
        }
    }

    /**
     * Authenticate against API and return access token.
     *
     * @return mixed
     * @throws Exception
     */
    protected function authenticate()
    {
        try {
            $this->assignCredentials();
            $endpoint = '/oauth/token';
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
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
