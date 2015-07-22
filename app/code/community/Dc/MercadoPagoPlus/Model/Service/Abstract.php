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

abstract class Dc_MercadoPagoPlus_Model_Service_Abstract extends Varien_Object
{

    /**
     * @var string
     */
    protected $api_url = 'https://api.mercadolibre.com';

    /**
     * @var
     */
    protected $client;

    /**
     * Get REST client instance.
     *
     * @return Zend_Rest_Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $client =  new Zend_Rest_Client($this->api_url);
        }
        return $client;
    }

}
