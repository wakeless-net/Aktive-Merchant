<?php

namespace AktiveMerchant\Billing\Gateways\PayFlowPro;

use AktiveMerchant\Billing\Gateway;

class PayflowProCommon extends Gateway
{
    const TEST_URL = 'https://pilot-payflowpro.paypal.com';
    const LIVE_URL = 'https://payflowpro.paypal.com';

    protected $options;
    protected $partner = 'PayPal';

    function __construct($options = array())
    {
        $this->required_options('login, password', $options);

        $this->options = $options;
        if (isset($options['partner']))
            $this->partner = $options['partner'];

        if (isset($options['currency']))
            self::$default_currency = $options['currency'];
    }

    protected function commit()
    {
        $url = $this->isTest() ? self::TEST_URL : self::LIVE_URL;
        
        $response = $this->parse(
            $this->ssl_post($url, $this->post_data())
        );

        return $this->build_response($this->success_from($response), $this->message_from($response), $response, $options);
    }

    private function success_from($response)
    {
        return (in_array($response['ACK'], $this->SUCCESS_CODES));
    }

    private function message_from($response)
    {
        return ( isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : $response['ACK'] );
    }

    private function parse($response)
    {
        parse_str($response, $parsed_response);

        return $parsed_response;
    }
    


}