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

class Dc_MercadoPagoPlus_Model_System_Config_Source_Credentials_Source
{

    const CUSTOM = '1';
    const MPEXPRESS = '2';
    const MERCADOPAGO = '3';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::CUSTOM,
                'label' => Mage::helper('mercadopagoplus')->__('Custom')
            ),
            array(
                'value' => self::MPEXPRESS,
                'label' => Mage::helper('mercadopagoplus')->__('MPExpress')
            ),
            array(
                'value' => self::MERCADOPAGO,
                'label' => Mage::helper('mercadopagoplus')->__('MercadoPago')
            )
        );
    }

}
