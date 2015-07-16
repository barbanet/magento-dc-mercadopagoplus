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

class Dc_MercadoPagoPlus_Model_System_Config_Cron_Account_Balance extends Mage_Core_Model_Config_Data
{

    const CRON_STRING_PATH = 'crontab/jobs/mercadopagoplus_account_balance/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/mercadopagoplus_account_balance/run/model';

    /**
     * @return Mage_Core_Model_Abstract|void
     * @throws Exception
     */
    protected function _afterSave() {
        $cronExprString = $this->getData('groups/account_balance/fields/cron_syntax');
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('mercadopagoplus')->__('Unable to save Cron expression'));
        }
    }

}
