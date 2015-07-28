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

class Dc_MercadoPagoPlus_Model_Payment_Button extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'mercadopagoplus_button';

    protected $_isInitializeNeeded = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = false;

    protected $_canUseForMultishipping = false;

    protected $_infoBlockType = 'mercadopagoplus/payment_info';

    public function assignData($data)
    {
        $quote = $this->getInfoInstance()->getQuote();
        $button_response = $this->createButton($quote);
        if (is_array($button_response)) {
            $this->getInfoInstance()->setAdditionalInformation(array('init_point' => $button_response['response']['init_point']));
        }
        return $this;
    }

    private function createButton($quote)
    {
        //TODO: Validate which version of MercadoPago is installed.
        $api_client_id = Mage::app()->getStore()->getConfig('payment/mpexpress/client_id');
        $api_client_secret = Mage::app()->getStore()->getConfig('payment/mpexpress/client_secret');
        $mercadopago = new Dc_MercadoPagoPlus_MercadoPago($api_client_id, $api_client_secret);

        $currency_code = $quote->getStoreCurrencyCode();
        $date_created_at = new Zend_Date($quote->getCreatedAt(), Zend_Date::ISO_8601);

        $items = array();
        foreach ($quote->getAllItems() as $quote_item) {
            if ($quote_item->getParentItem()) {
                continue;
            }
            $items[] = array(
                            'id' => $quote_item->getSku(),
                            'title' => $quote_item->getName(),
                            'currency_id' => $currency_code,
                            'picture_url' => $this->getProductThumbnail($quote_item->getProduct()),
                            'quantity' => $quote_item->getQty(),
                            'unit_price' => ($quote_item->getPriceInclTax() - $quote_item->getDiscountAmount())
                        );
        }
        $items[] = array(
                    'id' => 'shipment',
                    'title' => Mage::helper('mercadopagoplus')->__('Shipment'),
                    'currency_id' => $currency_code,
                    'quantity' => 1,
                    'unit_price' => ($quote->getShippingAddress()->getShippingInclTax() - $quote->getShippingAddress()->getShippingDiscountAmount())
                );


        $preference_data = array(
            'items' => $items,
            'payer' => array(
                'name' => $quote->getCustomerFirstname(),
                'surname' => $quote->getCustomerLastname(),
                'email' => $quote->getCustomerEmail(),
                'date_created' => $date_created_at->toString('yyyy-MM-ddTHH:mm:ss')
            ),
            'notification_url' => Mage::app()->getStore()->getConfig('payment/mercadopagoplus_button/notification_url'),
            'external_reference' => $this->getOrderId()
        );

        $button_response = $mercadopago->create_preference($preference_data);

        if ($button_response['status'] == 200 || $button_response['status'] == 201) {
            return $button_response;
        } else {
            throw Mage::exception(Mage::helper('mercadopagoplus')->__('Is not possible to create the payment button right now.'));
        }

    }

    /**
     * Order increment ID getter (either real from order or a reserved from quote).
     *
     * @return string
     */
    private function getOrderId()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }

    /**
     * Whether current operation is order placement.
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    /**
     * Creates the product thumbnail.
     *
     * @param $product
     * @return string
     */
    private function getProductThumbnail($product)
    {
        return (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(100);
    }

}
