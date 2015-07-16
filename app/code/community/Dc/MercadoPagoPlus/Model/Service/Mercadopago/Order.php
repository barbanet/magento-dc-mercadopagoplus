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

class Dc_MercadoPagoPlus_Model_Service_Mercadopago_Order extends Dc_MercadoPagoPlus_Model_Service_Mercadopago
{

    /**
     * Search an Order by Increment ID on MercadoPago.
     *
     * @param $incremental_id
     * @return bool
     * @throws Exception
     */
    public function searchByExternalReference($incremental_id)
    {
        try {
            $access_token = $this->authenticate();
            //TODO: Validate external reference on new version of MercadoPago module.
            $data = array(
                'access_token' => $access_token,
                'external_reference' => 'mpexpress-' . $incremental_id
            );
            $response = $this->getClient()->restGet('/collections/search', $data);
            $response_body = json_decode($response->getBody());
            if ($response_body && $response_body->paging->total > 0) {
                return $response_body->results[0]->collection;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
