<?php
use AktiveMerchant\Billing\Gateways\PayWay;
use AktiveMerchant\Billing\CreditCard;

require_once 'config.php';
class PayWayTest extends \AktiveMerchant\TestCase {
  var $gateway;
  
  function setup() {
  $this->gateway = $this->getMock('AktiveMerchant\Billing\Gateways\PayWay', array("ssl_post"), array(array(
      'username' => '12341234',
      'password' => 'abcdabcd',
      'pem'      => 'config/$payway->pem'
    ))
  );
    
    $this->amount = 1000;
    
    $this->credit_card = new CreditCard(array(
      'number'             => 4564710000000004,
      'month'              => 2,
      'year'               => 2019,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => '847',
      'type'               => 'visa'
    ));
    
    $this->options = array(
      'order_number'         => 'abc',
      'orginal_order_number' => 'xyz'
    );
  }

  function assertSuccess($response) {
    $this->assertTrue($response->success(), "Response should be successful.");
  }
  
  function assertFailure($response) {
    $this->assertFalse($response->success(), "Response should be a failure.");
  }

  function test_successful_purchase_visa() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_visa()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);

    $this->assertEquals('0',     $response->summary_code);
    $this->assertEquals('08',    $response->response_code);
    $this->assertEquals('VISA',  $response->card_scheme_name);
  }
  
  function test_successful_purchase_master_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_master_card()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',           $response->summary_code);
    $this->assertequals('08',          $response->response_code);
    $this->assertequals('MASTERCARD',  $response->card_scheme_name);
  }
  
  function test_successful_authorize_visa() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_visa()));
   
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',     $response->summary_code);
    $this->assertequals('08',    $response->response_code);
    $this->assertequals('VISA',  $response->card_scheme_name);
  }
  
  function test_successful_authorize_master_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_master_card()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',           $response->summary_code);
    $this->assertequals('08',          $response->response_code);
    $this->assertequals('MASTERCARD',  $response->card_scheme_name);
  }
  
  function test_successful_capture_visa() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_visa()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',     $response->summary_code);
    $this->assertequals('08',    $response->response_code);
    $this->assertequals('VISA',  $response->card_scheme_name);
  }
  
  function test_successful_capture_master_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_master_card()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',           $response->summary_code);
    $this->assertequals('08',          $response->response_code);
    $this->assertequals('MASTERCARD',  $response->card_scheme_name);
  }
  
  function test_successful_credit_visa() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_visa()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',     $response->summary_code);
    $this->assertequals('08',    $response->response_code);
    $this->assertequals('VISA',  $response->card_scheme_name);
  }
  
  function test_successful_credit_master_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->successful_response_master_card()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertsuccess($response);
    
    $this->assertequals('0',           $response->summary_code);
    $this->assertequals('08',          $response->response_code);
    $this->assertequals('MASTERCARD',  $response->card_scheme_name);
  }

  function test_purchase_with_invalid_credit_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->purchase_with_invalid_credit_card_response()));
    
    $this->credit_card->number = "4444333322221111";
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertfailure($response);
    
    $this->assertequals('1',   $response->summary_code);
    $this->assertequals('14',  $response->response_code);
  }

  function test_purchase_with_expired_credit_card() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->purchase_with_expired_credit_card_response()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertfailure($response);
    
    $this->assertequals('1',   $response->summary_code);
    $this->assertequals('54',  $response->response_code);
  }

  function test_purchase_with_invalid_month() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->purchase_with_invalid_month_response()));
    $this->credit_card->month = 13;
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertfailure($response);
    
    $this->assertequals('3',   $response->summary_code);
    $this->assertequals('QA',  $response->response_code);
  }

  function test_bad_login() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->bad_login_response()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertfailure($response);
    
    $this->assertequals('3',   $response->summary_code);
    $this->assertequals('QH',  $response->response_code);
  }

  function test_bad_merchant() {
    $this->gateway->expects($this->once())->method('ssl_post')->will($this->returnValue($this->bad_merchant_response()));
    
    $response = $this->gateway->purchase($this->amount, $this->credit_card, $this->options);
    
    $this->assertinstanceof("AktiveMerchant\Billing\Response", $response);
    $this->assertfailure($response);
    
    $this->assertequals('3',   $response->summary_code);
    $this->assertequals('QK',  $response->response_code);
  }

  private
  
    function successful_response_visa() {
      return "response.summaryCode=0&response.responseCode=08&response.cardSchemeName=VISA";
    }
    
    function successful_response_master_card() {
      return "response.summaryCode=0&response.responseCode=08&response.cardSchemeName=MASTERCARD";
    }
    
    function purchase_with_invalid_credit_card_response() {
      return "response.summaryCode=1&response.responseCode=14";
    }
    
    function purchase_with_expired_credit_card_response() {
      return "response.summaryCode=1&response.responseCode=54";
    } 
    
    function purchase_with_invalid_month_response() {
      return "response.summaryCode=3&response.responseCode=QA";
    }
    
    function bad_login_response() {
      return "response.summaryCode=3&response.responseCode=QH";
    }
    
    function bad_merchant_response() {
      return "response.summaryCode=3&response.responseCode=QK";
    }
}
