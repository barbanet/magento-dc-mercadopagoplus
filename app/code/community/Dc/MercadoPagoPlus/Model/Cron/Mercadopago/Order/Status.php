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

class Dc_MercadoPagoPlus_Model_Cron_Mercadopago_Order_Status extends Dc_MercadoPagoPlus_Model_Cron_Abstract
{

    /**
     * @var string
     */
    protected $process_name  = 'order_status';

    //TODO: Payment method code needs to be dynamic.
    /**
     * @var string
     */
    protected $payment_method_code = 'mpexpress';

    /**
     * @var array
     */
    protected $orders = array();

    /**
     * Execute service main action
     *
     * @return void
     */
    public function run()
    {
        $enable = Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/enable');
        if ($enable) {
            $this->log(Mage::helper('mercadopagoplus')->__('Job execution has started.'));
            $this->setOrders();
            foreach ($this->orders as $order) {
                $this->updateOrderInformation($order['increment_id']);
            }
            $this->sendEmail($this->orders);
            $this->log(Mage::helper('mercadopagoplus')->__('Job execution has finished.'));
        } else {
            $this->log(Mage::helper('mercadopagoplus')->__('The process can not run because it is disabled.'));
        }
    }

    /**
     * Get all Orders with the given status and MercadoPago payment method.
     */
    private function setOrders() {
        $order_status = explode(',', Mage::getStoreConfig('mercadopagoplus/' . $this->process_name . '/order_status'));
        $orders = Mage::getModel('sales/order')->getCollection()
                            ->addFieldToFilter('status', array('in' => $order_status));
        $orders->getSelect()->join(
            array('payments' => $orders->getResource()->getTable('sales/order_payment')),
            'payments.parent_id = main_table.entity_id',
            array()
        );
        $orders->addFieldToFilter('method', $this->payment_method_code);
        foreach ($orders as $order) {
            $this->orders[$order->getIncrementId()] = array(
                'order_id' => $order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'magento_status' => $order->getStatus(),
                'magento_created_at' => $order->getCreatedAt(),
                'mercadopago_status' => false,
                'mercadopago_status_detail' => false,
                'mercadopago_created_at' => false,
                'mercadopago_updated_at' => false,
                'email' => $order->getCustomerEmail(),
                'firstname' => $order->getCustomerFirstname(),
                'lastname' => $order->getCustomerLastname(),
                'total' => Mage::helper('core')->formatPrice($order->getGrandTotal(), false)
            );
        }
    }

    /**
     * Update Order array with MercadoPago information.
     *
     * @param $increment_id
     */
    private function updateOrderInformation($increment_id)
    {
        $service = Mage::getSingleton('mercadopagoplus/service_mercadopago_order');
        try {
            $data = $service->searchByExternalReference($increment_id);
            if ($data) {
                $this->orders[$increment_id]['mercadopago_status'] = $data->status;
                $this->orders[$increment_id]['mercadopago_status_detail'] = $data->status_detail;
                $date_created = new Zend_Date($data->date_created);
                $this->orders[$increment_id]['mercadopago_created_at'] = $date_created->toString('yyyy-MM-dd HH:mm:ss');
                $last_modified = new Zend_Date($data->last_modified);
                $this->orders[$increment_id]['mercadopago_updated_at'] = $last_modified->toString('yyyy-MM-dd HH:mm:ss');
            }
        } catch (Exception $e) {
            $this->log(Mage::helper('mercadopagoplus')->__('API Error. Order: %s. Message: %s', $increment_id, $e->getMessage()));
        }
    }

}
