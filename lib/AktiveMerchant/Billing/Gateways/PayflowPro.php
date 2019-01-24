<?php

namespace AktiveMerchant\Billing\Gateways;

require("PayFlowPro/PayflowProCommon.php"); // inserted, autoloader not working

use AktiveMerchant\Billing\Gateways\PayFlowPro\PayflowProCommon;
use AktiveMerchant\Billing\Response;

class PayflowPro extends PayflowProCommon
{
    private $post = array();

    private $TRXTYPE_MAP = array(
        'Authorization' => 'A',
        'BalanceInquiry' => 'B',
        'Credit' => 'C',
        'DelayedCapture' => 'D',
        'VoiceAuth' => 'F',
        'Inquiry' => 'Inquiry',
        'RateLookup' => 'K',
        'Data Upload' => 'L',
        'DuplicateTransaction' => 'N',
        'Sale' => 'S',
        'Void' => 'V',
    );

    private $CARD_TYPE = [
        'Visa',
        'MasterCard',
        'Discover',
        'American Express',
        'Diner\'s Club',
        'JCB'
    ];

    function authorize($money, $credit_card_or_reference, $options = array())
    {
        $this->build_post_data(
            'Authorization',
            $money,
            $credit_card_or_reference,
            $options
        );

        $this->commit();
    }

    function purchase($money, $credit_card_or_reference, $options = array())
    {
        $this->build_post_data(
            'Sale',
            $money,
            $credit_card_or_reference,
            $options
        );
    }

    function build_post_data($action, $money, $credit_card_or_reference, $options)
    {
        $card = $credit_card_or_reference;
        
        $this->post['TRXTYPE'] = $this->TRXTYPE_MAP[$action];
        $this->post['TENDER'] = "C";

        $this->post['USER'] = $this->options['login'];
        $this->post['VENDOR'] = $this->options['login'];
        $this->post['PARTNER'] = $this->partner;
        $this->post['PWD'] = $this->options['password'];

        $this->post['ACCT'] = $card->number;
        $this->post['AMT'] = $money;
        $this->post['EXPDATE'] = $this->get_expiry($card->month, $card->year);
        $this->post['CVV2'] = $card->verification_value;
        $this->post['CARDTYPE'] = array_search($card->type, $this->CARD_TYPE);
        $this->post['CURRENCY'] = (isset($this->options['currency'])) ? $this->options['currency'] : "USD";

        $this->post['BILLTOFIRSTNAME'] = $card->first_name;
        $this->post['VERBOSITY'] = "HIGH";
    }

    function get_expiry($month, $year)
    {
        return $month . str_split($year, 2)[1];
    }

    function post_data()
    {
        return $this->urlize($this->post);
    }

    protected function build_response($success, $message, $response, $options=array())
    {
        return new Response($success, $message, $response, $options);
    }

}