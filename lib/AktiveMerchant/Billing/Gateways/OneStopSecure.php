<?php

namespace AktiveMerchant\Billing\Gateways;

use AktiveMerchant\Billing\Gateway;
use AktiveMerchant\Http\Request;

class OneStopSecure extends Gateway {

    const TEST_URL = 'https://anu-test.onestopsecure.com/OneStopWeb/EP/tranadd';
    const LIVE_URL = 'https://anu-test.onestopsecure.com/OneStopWeb/EP/tranadd';

    protected $options;
    protected $post = array(
        'UDS_ACTION' => 'DEFAULT',
        'TAXCODE' => 'ZE',
        'STOREID' => 'CURRINDA',
        'Description' => 'PaymentForCurrinda'
    );

    function __construct($options = array()) 
    {
        $this->required_options('uds_action, glcode', $options);
        $this->options = $options;
    }

    function getRedirectURI($money, $data) 
    {
        $this->build_post_data($money, $data);
        return $this->build_redirect_uri();
    }

    protected function post_data()
    {
        return $this->urlize($this->post);
    }

    protected function build_post_data($money, $data)
    {
        $this->post['UDS_ACTION_DATA'] = $this->options['uds_action'];
        $this->post['GLCODE'] = $this->options['glcode'];

        $this->post['ORDERID'] = $data['reference'];
        $this->post['EMAIL'] = $data['email'];
        $this->post['UNITAMOUNTINCTAX'] = $money + 12;
    }


    protected function build_redirect_uri()
    {
        $url = $this->isTest() ? self::TEST_URL : self::LIVE_URL;

        return $url . '?' . $this->post_data();
    }

}