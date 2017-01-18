<?php
defined('ABSPATH') or die('No script kiddies please!');
include("QuickPay.php");

class Qpg_Checkout
{
    private $private_key;
    private $isTest;
    private $dbModel;

    function __construct($private_key, $dbModel)
    {
        $this->private_key = $private_key;
        $this->isTest      = FALSE;
        $this->dbModel     = $dbModel;
    }

    /**
     * Used to set change environment to test
     * @param type $env
     */
    function activate_test()
    {
        $this->isTest = TRUE;
    }

    /**
     * Used to process checkout returns response has string
     */
    function process()
    {
        if (!filter_has_var(INPUT_POST, "qpToken")) {
            status_header(400, "Sorry invalid request");
            return "Sorry invalid request";
        }
        $referenceNo = time();
        $orderInfo   = "item-".time();
        $amount      = (is_numeric(filter_input(INPUT_POST, 'amount'))) ? filter_input(INPUT_POST,
                "amount") : filter_input(INPUT_POST, "qpAmount");
        $currency    = filter_input(INPUT_POST, "currency");
        $token       = filter_input(INPUT_POST, "qpToken");
        try {
            $gateway  = new Qpg_QuickPay($this->private_key, $this->isTest);
            //Perform payment
            $response = $gateway->sendMessage($referenceNo, $orderInfo, $amount,
                $token, $currency);
            if ($response->code == 0) {
                $res_msg = "Payment was successfull";
            } else if ($response->code > 1 && $response->code < 10) {//catch issuer bank errors
                status_header(400,
                    "The issuer bank declined your request this may be due to insuffincient funds");
                $res_msg = "The issuer bank declined your request this may be due to insuffincient funds";
            } else {//catch general errors
                status_header(400,
                    "The transaction failed this may be due to insuffincient funds or invalid card (Details: ".$response->data->message.")");
                $res_msg = "The transaction failed this may be due to insuffincient funds or invalid card (Details: ".$response->data->message.")";
            }
            //log the transaction in the database
            $this->dbModel->insert_transaction($this->packageResponse($response,
                    $token));
        } catch (Qpg_QuickPayException $ex) {
            status_header(500, "Internal Server error occured please try again");
            $res_msg = "Internal Server error occured please try again (Details: ".$ex->getMessage().")";
        }
        return $res_msg;
    }

    /**
     * used to package response from an object to an array
     * @param type $result
     */
    private function packageResponse($result, $token)
    {
        $data['amount']        = isset($result->data->data->amount) ? $result->data->data->amount
                : '';
        $data['currency']      = isset($result->data->data->currency) ? $result->data->data->currency
                : '';
        $data['merchant_id']   = isset($result->data->data->merchantID) ? $result->data->data->merchantID
                : '';
        $data['ref_no']        = isset($result->data->data->referenceNo) ? $result->data->data->referenceNo
                : '';
        $data['order_info']    = isset($result->data->data->orderInfo) ? $result->data->data->orderInfo
                : '';
        $data['receipt_no']    = isset($result->data->data->receiptNo) ? $result->data->data->receiptNo
                : '';
        $data['auth_id']       = isset($result->data->data->authId) ? $result->data->data->authId
                : '';
        $data['response_code'] = isset($result->data->data->responseCode) ? $result->data->data->responseCode
                : '';
        $data['trans_id']      = isset($result->data->data->transactionNo) ? $result->data->data->transactionNo
                : '';
        $data['description']   = isset($result->data->message) ? $result->data->message
                : '';
        $data['status_code']   = isset($result->code) ? $result->code : '';
        $data['token']         = $token;
        //format amount
        $data['amount']        = (double) ($data['amount'] / 100);
        return $data;
    }
}