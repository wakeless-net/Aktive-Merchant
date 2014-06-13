<?php

namespace AktiveMerchant\Billing\Gateways;

use AktiveMerchant\Billing\Interfaces as Interfaces;
use AktiveMerchant\Billing\Gateway;
use AktiveMerchant\Billing\Response;
use AktiveMerchant\HTTP\Request;
use AktiveMerchant\Billing\CreditCard;

class PayWay extends Gateway implements 
    Interfaces\Charge,
    Interfaces\Credit
{

  static $URL           = 'https://ccapi.client.qvalent.com/payway/ccapi';
  
  static $SUMMARY_CODES = array( 
                    '0' => 'Approved',
                    '1' => 'Declined',
                    '2' => 'Erred',
                    '3' => 'Rejected'
                  );
                  
  static $response_CODES = array(
                    '00' => 'Completed Successfully',
                    '01' => 'Refer to card issuer',
                    '03' => 'Invalid merchant',
                    '04' => 'Pick-up card',
                    '05' => 'Do not honour',
                    '08' => 'Honour only with identification',
                    '12' => 'Invalid transaction',
                    '13' => 'Invalid $amount',
                    '14' => 'Invalid card number (no such number)',
                    '30' => 'Format error',
                    '36' => 'Restricted card',
                    '41' => 'Lost card',
                    '42' => 'No universal card',
                    '43' => 'Stolen card',
                    '51' => 'Not sufficient funds',
                    '54' => 'Expired card',
                    '61' => 'Exceeds withdrawal $amount limits',
                    '62' => 'Restricted card',
                    '65' => 'Exceeds withdrawal frequency limit',
                    '91' => 'Issuer or switch is inoperative',
                    '92' => 'Financial institution or intermediate network facility cannot be found for routing',
                    '94' => 'Duplicate transmission',
                    'Q1' => 'Unknown Buyer',
                    'Q2' => 'Transaction P}ing',
                    'Q3' => 'Payment Gateway Connection Error',
                    'Q4' => 'Payment Gateway Unavailable',
                    'Q5' => 'Invalid Transaction',
                    'Q6' => 'Duplicate Transaction - requery to determine status',
                    'QA' => 'Invalid parameters or Initialisation failed',
                    'QB' => 'Order type not currently supported',
                    'QC' => 'Invalid Order Type',
                    'QD' => 'Invalid Payment $amount - Payment amount less than minimum/exceeds maximum allowed limit',
                    'QE' => 'Internal Error',
                    'QF' => 'Transaction Failed',
                    'QG' => 'Unknown Customer Order Number',
                    'QH' => 'Unknown Customer Username or Password',
                    'QI' => 'Transaction incomplete - contact Westpac to confirm reconciliation',
                    'QJ' => 'Invalid Client Certificate',
                    'QK' => 'Unknown Customer Merchant',
                    'QL' => 'Business Group not configured for customer',
                    'QM' => 'Payment Instrument not configured for customer',
                    'QN' => 'Configuration Error',
                    'QO' => 'Missing Payment Instrument',
                    'QP' => 'Missing Supplier Account',
                    'QQ' => 'Invalid Credit Card Verification Number',
                    'QR' => 'Transaction Retry',
                    'QS' => 'Transaction Successful',
                    'QT' => 'Invalid currency',
                    'QU' => 'Unknown Customer IP Address',
                    'QV' => 'Invalid Original Order Number specified for Refund, Refund $amount exceeds capture amount, or Previous capture was not approved',
                    'QW' => 'Invalid Reference Number',
                    'QX' => 'Network Error has occurred',
                    'QY' => 'Card Type Not Accepted',
                    'QZ' => 'Zero value transaction'
                  );
                  
  static $TRANSACTIONS = array(
                    'authorization'  => 'preauth',
                    'purchase'       => 'capture',
                    'capture'        => 'captureWithoutAuth',
                    'status'         => 'query',
                    'credit'         => 'refund'
                  );
  
  static $supported_countries = array( 'AU' );
  static $supported_cardtypes = array( 'visa', 'master', 'diners_club', 'american_express', 'bankcard' );
  static $display_name        = 'Pay Way';
  static $homepage_url        = 'http://www.payway.com.au';
  static $default_currency    = 'AUD';
  static $money_format        = 'cents';
  
  # Create a new Payway $gateway->
   function __construct($options = array()) {
    $this->required_options(array('username', 'password', 'pem'), $options);
    $this->options = $options;

    @$this->options['eci'] = $this->options["eci"]       ?: 'SSL';
    @$this->options['currency'] = $this->options["currency"] ?: self::$default_currency;
    @$this->options['merchant'] = $this->options["merchant"]  ?: 'TEST';
    @$this->options['pem'] = $this->options["pem"];
    @$this->options['pem_password'] = $this->options["pem_password"];
    
    $this->post = array();
    $this->transaction = array();
  }
  
  # Build the string and send it
  function process($action, $amount, $credit_card) {
    
    $this->transaction = array_merge($this->transaction, array(
      'type'         => $action,
      'amount'       => $amount,
      'credit_card'  => $credit_card
    ));
    
    if(!in_array($action, ["capture", "credit"])) $this->build_card();
    $this->build_order();
    $this->build_customer();
    
    return $this->send_post();
  }
  
   function authorize($amount, CreditCard $credit_card, $options = array()) {
    $this->required_options(array('order_number'), $options);
    
    $this->transaction = array_merge($this->transaction, array('order_number' => $options['order_number'] ));
    return $this->process('authorization', $amount, $credit_card);
  }
  
   function capture($amount, $credit_card, $options = array()) {
    $this->required_options(array('order_number', "original_order_number"), $options);
    
    $this->transaction = array_merge($this->transaction, array(
      'order_number' => $options['order_number'],
      'original_order_number'  => $options['original_order_number']
    ));
    
    return $this->process('capture', $amount, $credit_card);
  }
  
   function purchase($amount, CreditCard $credit_card, $options = array()) {
    $this->required_options(array('order_number'), $options);
    
    $this->transaction = array_merge($this->transaction, array('order_number' => $options['order_number'] ));
    
    return $this->process('purchase', $amount, $credit_card);
  }
  
   function credit($amount, $identification, $options = array()) {
    $this->required_options(array('order_number', "original_order_number"), $options);
    
    $this->transaction = array_merge($this->transaction, array(
      'order_number' => $options['order_number'],
      'original_order_number'  => $options['original_order_number']
    ));
    
    return $this->process('credit', $amount, @new CreditCard([]));
  }

  function void($authorization, $options = array()) { //do nothing 
  }
  
   function status($options = array()) {
    $this->required_options(array('order_number'), $options);
    $this->transaction['type'] = self::$TRANSACTIONS['status'];
    
    $this->build_order();
    
    $this->send_post();
  }
  
  private
    
    # Adds credit card details to the post hash
    function build_card() {
      $card = $this->transaction['credit_card'];
      $this->post = array_merge($this->post, array(
        'card.cardHolderName' => "{$card->first_name} {$card->last_name}",
        'card.PAN'            => $card->number,
        'card.CVN'            => $card->verification_value,
        'card.expiryYear'     => $this->cc_format($card->year, "two_digits"),
        'card.expiryMonth'    => $this->cc_format($card->month, 'two_digits'),
        'card.currency'       => $this->options['currency']
      ));
    }
    
    # Adds the order arguments to the post hash
    function build_order() {
      $this->post = array_merge($this->post, array(
        'order.ECI'           => $this->options['eci'],
        'order.amount'        => $this->transaction['amount'],
        'order.type'          => self::$TRANSACTIONS[$this->transaction['type']]
      ));

      
      if(isset($this->transaction['original_order_number'])) {
        $this->post['customer.originalOrderNumber'] = $this->transaction['original_order_number'];
      }
    }
    
    # Adds the customer arguments to the post hash
    function build_customer() {
      $this->post = array_merge($this->post, array(
        'customer.username'   => $this->options['username'],
        'customer.password'   => $this->options['password'],
        'customer.merchant'   => $this->options['merchant'],
        'customer.orderNumber'=> "{$this->transaction['order_number']}"
      ));
    }
    
    # Creates the request and returns the sumarised result
    function send_post() {
      $body = http_build_query($this->post);

      $this->response = $this->ssl_post(self::$URL, $body, [
        "pem" => @$this->options["pem"],
        "pem_password" => @$this->options["pem_password"]
      ]);

      return $this->process_response();
    }
    
    function process_response() {
      $params = array();
      $vars = array();

      parse_str($this->response, $vars);
      foreach($vars as $key => $value) {
        $actual_key = explode("_", $key);
        $actual_key = end($actual_key);

        $params[$this->underscore($actual_key)] = $value;
      }
      
      $msg = self::$SUMMARY_CODES[$params['summary_code']]." - ".self::$response_CODES[$params['response_code']];
      
      $success = $params['summary_code'] == "0";
      $options = array('test' => $this->options['merchant'] == "TEST");
      
      return new Response($success, $msg, $params, $options);
    }
}

