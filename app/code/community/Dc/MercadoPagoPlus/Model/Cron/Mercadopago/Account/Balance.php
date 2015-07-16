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

class Dc_MercadoPagoPlus_Model_Cron_Mercadopago_Account_Balance extends Dc_MercadoPagoPlus_Model_Cron_Abstract
{

    /**
     * @var string
     */
    protected $process_name  = 'account_balance';

    /**
     * @var
     */
    protected $messages;

    /**
     * Execute service main action
     *
     * @return void
     */
    public function run()
    {
        $enable = Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/enable');
        if ($enable) {
            $this->log(Mage::helper('mercadopagoplus')->__('Job execution has started.'), true);
            $this->getBalanceDetails();
            $this->sendEmail($this->messages);
            $this->log(Mage::helper('mercadopagoplus')->__('Job execution has finished.'), true);
        } else {
            $this->log(Mage::helper('mercadopagoplus')->__('The process can not run because it is disabled.'), true);
        }
    }

    /**
     * Information to object converter.
     */
    private function getBalanceDetails()
    {
        $service = Mage::getSingleton('mercadopagoplus/service_mercadopago_account');
        $balance = $service->getBalance();
        $this->messages = new Varien_Object();
        $this->messages->setCurrency($balance->currency_id);
        $this->messages->setTotalAmount(number_format($balance->total_amount, 2, ',', '.'));
        $this->messages->setAvailableBalance(number_format($balance->available_balance, 2, ',', '.'));
        $this->messages->setUnavailableBalance(number_format($balance->unavailable_balance, 2, ',', '.'));
        foreach ($balance->unavailable_balance_by_reason as $reason) {
            switch($reason->reason) {
                case 'dispute':
                    $this->messages->setUnavailableByDispute(number_format($reason->amount, 2, ',', '.'));
                    break;
                case 'fraud':
                    $this->messages->setUnavailableByFraud(number_format($reason->amount, 2, ',', '.'));
                    break;
                case 'ml_debt':
                    $this->messages->setUnavailableByMlDebt(number_format($reason->amount, 2, ',', '.'));
                    break;
                case 'time_period':
                    $this->messages->setUnavailableByTimePeriod(number_format($reason->amount, 2, ',', '.'));
                    break;
                case 'restriction':
                    $this->messages->setUnavailableByRestriction(number_format($reason->amount, 2, ',', '.'));
                    break;
            }
        }
    }

}
