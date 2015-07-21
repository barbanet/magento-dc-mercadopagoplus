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

class Dc_MercadoPagoPlus_Block_Payment_Info extends Mage_Payment_Block_Info
{

    /**
     * @var
     */
    protected $link;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dc/mercadopagoplus/payment/info.phtml');
    }

    /**
     * Get button payment link.
     *
     * @return string
     */
    public function getButtonLink()
    {
        if (is_null($this->link)) {
            $this->link = $this->getInfo()->getAdditionalInformation('init_point');
        }
        return $this->link;
    }
}