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

class Dc_MercadoPagoPlus_Model_Service_Mercadopago_Account extends Dc_MercadoPagoPlus_Model_Service_Mercadopago
{

    /**
     * Get account balance information
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function getBalance()
    {
        try {
            $access_token = $this->authenticate();
            $user_id = $this->getUserId();
            $data = array(
                'access_token' => $access_token
            );
            $response = $this->getClient()->restGet('/users/' . $user_id . '/mercadopago_account/balance', $data);
            $response_body = json_decode($response->getBody());
            if ($response_body && $response_body->total_amount) {
                return $response_body;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
