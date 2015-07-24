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

abstract class Dc_MercadoPagoPlus_Model_Cron_Abstract implements Dc_MercadoPagoPlus_Model_Cron_Interface
{

    /**
     * @var
     */
    protected $process_name;

    /**
     * Initialize translator.
     */
    public function __construct()
    {
        Mage::app()->getTranslator()->init(Mage_Core_Model_App_Area::AREA_ADMINHTML);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $_enable = Mage::app()->getStore()->getConfig('mercadopagoplus/' . $this->process_name . '/save_log');
        if ($_enable) {
            Mage::log($message, null, 'mercadopagoplus.log', true);
        }
    }

    /**
     * Send email notification according to configuration
     *
     * @param null $content
     * @return bool
     */
    public function sendEmail($content = null)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        try {
            $mailer = Mage::getModel('core/email_template_mailer');
            $recipients = explode(',', Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/recipient'));
            foreach ($recipients as $recipient) {
                $email = Mage::getModel('core/email_info');
                $email->addTo(trim($recipient));
                $mailer->addEmailInfo($email);
                unset($email);
            }
            $mailer->setSender(Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/identity'));
            $mailer->setStoreId(0);
            $mailer->setTemplateId(Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/template'));
            $mailer->setTemplateParams(array('store_name' => Mage::getStoreConfig('general/store_information/name'), 'data' => $content));
            $mailer->send();
            $this->log(Mage::helper('mercadopagoplus')->__('Email sent.'));
            return true;
        } catch (Exception $e) {
            //TODO: choose between system.log, exception.log or mercadopagosplus.log.
            Mage::log($e->getMessage());
            $this->log(Mage::helper('mercadopagoplus')->__('There was an error trying to send the email.'));
            return false;
        }
    }

}
