<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class Qpg_QuickPayException extends Exception {
    
}

class Qpg_QuickPay {

    protected $_username;
    protected $_apikey;
    protected $_requestBody;
    protected $_requestURI;
    protected $_responseBody;
    protected $_responseInfo;
    protected $_Headers;
    protected $HTTP_CODE_OK;
    protected $_PAY_URL;

    //protected $_PAY_URL;
    const _SUCCESS_RCODE = 200;

    /**
     *  Initialises the class
     * @param type $apiKey
     * @throws Qpg_QuickPayException
     */
    public function __construct($apiKey, $isTest = FALSE) {
        $this->_PAY_URL = ($isTest === TRUE) ? "https://checkout-test.quickpay.co.ke/chargetoken" : "https://checkout.quickpay.co.ke/chargetoken";
        
        if (strlen($apiKey)== 0) {
            throw new Qpg_QuickPayException('Please supply both username and apikey files. key ');
        } else {
            $this->_apikey = $apiKey;
        }
    }

    /**
     *
     * @param type $referenceNo
     * @param type $orderInfo
     * @param type $amount
     * @param type $token
     * @param type $Currency
     */
    public function sendMessage($referenceNo, $orderInfo, $amount, $token, $Currency) {
        if (empty($referenceNo) || empty($orderInfo) || empty($amount) || empty($token) || empty($Currency)) {
            throw new Qpg_QuickPayException('Please supply both username and apikey files. ');
        } else {
            $params = array(
                "reference" => $referenceNo, "orderinfo" => $orderInfo,
                "currency" => $Currency, "amount" => $amount,
                "userkey" => $this->_apikey, "token" => $token
            );
//            Set up channel configurations
            $this->HTTP_CODE_OK = self::_SUCCESS_RCODE;
            $this->_requestURI = $this->_PAY_URL;//self::_PAY_URL;
            $this->_requestBody = json_encode($params);
            $this->exeutePost($params);
            if ($this->_responseInfo['http_code'] == self::_SUCCESS_RCODE) {
                $responseObject = json_decode($this->_responseBody);
                return $responseObject;
            } else {
                throw new Qpg_QuickPayException($this->_responseBody);
            }
            return "Error";
        }
    }

    private function exeutePost($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestBody);
        curl_setopt($ch, CURLOPT_POST, 1);
        $_Headers = array();
        $_Headers[] = 'SecureHash: ' . base64_encode(hash_hmac("sha256", json_encode($params), $this->_apikey, true));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_Headers);
        $this->doExecute($ch);
    }

    /**
     *
     * @param type $curlHandle_
     * @throws Exeption
     */
    private function doExecute(&$curlHandle_) {
        try {
            $this->setCurlOpts($curlHandle_);
            $responseBody = curl_exec($curlHandle_);
            $this->_responseInfo = curl_getinfo($curlHandle_);
            $this->_responseBody = $responseBody;
            curl_close($curlHandle_);
        } catch (Exeption $e) {
            curl_close($curlHandle_);
            throw $e;
        }
    }

    /**
     * 
     * @param type $curlHandle_s
     */
    private function setCurlOpts(&$curlHandle_) {
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 60);
        curl_setopt($curlHandle_, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle_, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandle_, CURLOPT_URL, $this->_requestURI);
        curl_setopt($curlHandle_, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

}