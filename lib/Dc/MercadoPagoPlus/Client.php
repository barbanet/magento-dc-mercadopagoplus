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

/**
 * MercadoPago cURL RestClient
 * Original code from https://github.com/mercadopago/sdk-php
 */
class Dc_MercadoPagoPlus_Client
{

    const API_BASE_URL = "https://api.mercadopago.com";

    private static function get_connect($uri, $method, $content_type)
    {
        if (!extension_loaded ("curl")) {
            throw new Exception("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        $connect = curl_init(self::API_BASE_URL . $uri);

        curl_setopt($connect, CURLOPT_USERAGENT, "Dc_MercadoPagoPlus " . Mage::getConfig()->getModuleConfig('Dc_MercadoPagoPlus')->version);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($connect, CURLOPT_CAINFO, Mage::getBaseDir('lib') . DS . 'Dc' . DS . 'MercadoPagoPlus' . DS . 'cacert.pem');
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: " . $content_type));

        return $connect;
    }

    private static function set_data(&$connect, $data, $content_type)
    {
        if ($content_type == "application/json") {
            if (gettype($data) == "string") {
                json_decode($data, true);
            } else {
                $data = json_encode($data);
            }

            if(function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception("JSON Error [{$json_error}] - Data: {$data}");
                }
            }
        }

        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    private static function exec($method, $uri, $data, $content_type)
    {
        $connect = self::get_connect($uri, $method, $content_type);
        if ($data) {
            self::set_data($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === FALSE) {
            throw new Exception (curl_error ($connect));
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response['status'] >= 400) {
            $message = $response['response']['message'];
            if (isset ($response['response']['cause'])) {
                if (isset ($response['response']['cause']['code']) && isset ($response['response']['cause']['description'])) {
                    $message .= " - ".$response['response']['cause']['code'].': '.$response['response']['cause']['description'];
                } else if (is_array ($response['response']['cause'])) {
                    foreach ($response['response']['cause'] as $cause) {
                        $message .= " - ".$cause['code'].': '.$cause['description'];
                    }
                }
            }

            throw new Exception ($message, $response['status']);
        }

        curl_close($connect);

        return $response;
    }

    public static function get($uri, $content_type = "application/json")
    {
        return self::exec("GET", $uri, null, $content_type);
    }

    public static function post($uri, $data, $content_type = "application/json")
    {
        return self::exec("POST", $uri, $data, $content_type);
    }

    public static function put($uri, $data, $content_type = "application/json")
    {
        return self::exec("PUT", $uri, $data, $content_type);
    }

    public static function delete($uri, $content_type = "application/json")
    {
        return self::exec("DELETE", $uri, null, $content_type);
    }

}
