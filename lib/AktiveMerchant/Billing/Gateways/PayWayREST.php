<?php

namespace AktiveMerchant\Billing\Gateways;

use AktiveMerchant\Billing\Gateway;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Http\Request;
use AktiveMerchant\Billing\Response;

class PayWayREST extends GateWay {

    const LIVE_URL = "https://api.payway.com.au/rest/v1";

    public static $default_currency = "aud";

    function __construct($options = array()) {
        $this->required_options(array("publishable_key", "secret_key", "merchant_id"), $options);
        $this->options = $options;

        $this->options['currency'] = $this->options["currency"] ?: self::$default_currency;
        $this->options['ip'] = isset($options['ip']) ? $options['ip'] : $_SERVER['REMOTE_ADDR'];
    }

    function build_credit_card($transaction) {
        $name = explode(" ", $transaction["CardHoldersName"]);
        return new CreditCard(array(
            "first_name" => $name[0],
            "last_name" => $name[1],
            "month" => $transaction["CardExpiryMonth"],
            "year" => $transaction["CardExpiryYear"],
            "type" => $transaction["CardType"],
            "number" => $transaction["CardNumber"],
            "verification_value" => $transaction["CardVerification"]
        ));
    }

    function createSingleUseTokenId(CreditCard $creditcard) {

        $endpoint = "/single-use-tokens";
        
        $response = $this->parse(
            $this->ssl_request($endpoint, 'POST', array(
                "paymentMethod" => "creditCard",
                "cardNumber" => $creditcard->number,
                "cardholderName" => $creditcard->name(),
                "cvn" => $creditcard->verification_value,
                "expiryDateMonth" => $creditcard->month,
                "expiryDateYear" => $creditcard->year
            ), $this->options["publishable_key"]));
        
        return $response["singleUseTokenId"];
    }

    function purchase($transaction, $data) {
        $endpoint = "/transactions";
        $token = $this->createSingleUseTokenId(
            $this->build_credit_card($transaction)
        );
        
        $response = $this->parse(
            $this->ssl_request($endpoint, 'POST', array(
                "singleUseTokenId" => $token,
                "customerNumber" => uniqid() . date("His"),
                "transactionType" => "payment",
                "principalAmount" => $transaction->payment(),
                "currency" => strtolower($this->options['currency']),
                "merchantId" => $this->options["merchant_id"],
                "customerIpAddress" => $this->options['ip']
            ), $this->options["secret_key"]));

        return $this->build_response(
            $this->success_from($response), 
            $this->message_from($response), 
            $response);
    }

    function authorize($transaction, $data) {
        $endpoint = "/transactions";
        $token = $this->createSingleUseTokenId(
            $this->build_credit_card($transaction)
        );

        $response = $this->parse(
            $this->ssl_request($endpoint, 'POST', array(
                "singleUseTokenId" => $token,
                "customerNumber" => uniqid() . date("His"),
                "transactionType" => "preAuth",
                "principalAmount" => $transaction->payment(),
                "currency" => strtolower($this->options['currency']),
                "merchantId" => $this->options["merchant_id"],
                "customerIpAddress" => $this->options['ip']
            ), $this->options["secret_key"]));

        return $this->build_response(
            $this->success_from($response), 
            $this->message_from($response), 
            $response);
    }

    function capture($transaction) {
        $endpoint = "/transactions";

        $response = $this->parse(
            $this->ssl_request($endpoint, 'POST', array(
                "transactionType" => "capture",
                "parentTransactionId" => $transaction->reference(),
                "principalAmount" => $transaction->payment(),
                "customerIpAddress" => $this->options['ip']
            ), $this->options["secret_key"]));

        return $this->build_response(
            $this->success_from($response), 
            $this->message_from($response), 
            $response);
    }

    function refund($transaction, $reference, $options = array()) {

        $endpoint = "/transactions";
        $token = $this->createSingleUseTokenId(
            $this->build_credit_card($transaction)
        );

        $response = $this->parse(
            $this->ssl_request($endpoint, 'POST', array(
                "transactionType" => "refund",
                "parentTransactionId" => $reference,
                "principalAmount" => abs($transaction->payment()),
                "customerIpAddress" => $this->options['ip']
            ), $this->options["secret_key"]));


        return $this->build_response(
            $this->success_from($response), 
            $this->message_from($response), 
            $response);
    }

    private function parse($body) {
        return json_decode($body, true);
    }

    function ssl_request($endpoint, $method, $data, $api_key) {
        $url = self::LIVE_URL . $endpoint;

        $request = new Request($url, $method);
        $request->addHeader("Authorization", "Basic " . base64_encode($api_key));
        $request->setBody($data);
        if (true == $request->send()) {
            return $request->getResponseBody();
        }
    }

    protected function build_response($success, $message, $response) {
        return new Response($success, $message, $response);
    }

    private function success_from($response) {
        return ($response["responseCode"] == "00" || $response["responseCode"] == "08");
    }

    private function message_from($response) {
        return ( isset($response['responseText']) ? $response['responseText'] : "" );
    }

}