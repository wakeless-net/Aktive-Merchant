<?php

use AktiveMerchant\Billing\Gateways\PayWay;
use AktiveMerchant\Billing\CreditCard;

#USERNAME = $File->new('config/$credentials->txt')$->readlines[0]$->gsub("\n","");
#PASSWORD = $File->new('config/$credentials->txt')$->readlines[1]$->gsub("\n","");
#PEM_FILE = 'config/$payway->pem'
require_once 'config.php';

class RemotePayWayTest extends \AktiveMerchant\TestCase {
  
  function setup() {
    $this->amount   = 1100;
    
    $this->options  = array(
      'order_number'           => time() * 1000,
      'original_order_number'  => 'xyz'
    );
    
    $this->gateway = new PayWay(array(
      'username' => USERNAME, 
      'password' => PASSWORD, 
      'merchant' => 'TEST', 
      'pem'      => PEM_FILE
    ));
    
    $this->visa = new CreditCard(array(
      'number'             => 4564710000000004,
      'month'              => 2,
      'year'               => 2019,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 847,
      'type'               => 'visa'
    ));
    
    $this->mastercard = new CreditCard(array(
      'number'             => 5163200000000008,
      'month'              => 8,
      'year'               => 2020,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => '070',
      'type'               => 'master'
    ));
    
    $this->amex = new CreditCard(array(
      'number'             => 376000000000006,
      'month'              => 6,
      'year'               => 2020,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 2349,
      'type'               => 'american_express'
    ));
    
    $this->diners = new CreditCard(array(
      'number'             => 36430000000007,
      'month'              => 6,
      'year'               => 2022,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 348,
      'type'               => 'diners_club'
    ));
    
    $this->expired = new CreditCard(array(
      'number'             => 4564710000000012,
      'month'              => 2,
      'year'               => 2005,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 963,
      'type'               => 'visa'
    ));
    
    $this->low = new CreditCard(array(
      'number'             => 4564710000000020,
      'month'              => 5,
      'year'               => 2020,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 234,
      'type'               => 'visa'
    ));
    
    $this->stolen_mastercard = new CreditCard(array(
      'number'             => 5163200000000016,
      'month'              => 12,
      'year'               => 2019,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 728,
      'type'               => 'master'
    ));
    
    $this->invalid = new CreditCard(array(
      'number'             => 4564720000000037,
      'month'              => 9,
      'year'               => 2019,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => '030',
      'type'               => 'visa'
    ));
    
    $this->restricted = new CreditCard(array(
      'number'             => 343400000000016,
      'month'              => 1,
      'year'               => 2019,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 9023,
      'type'               => 'american_express'
    ));
    
    $this->stolen_diners = new CreditCard(array(
      'number'             => 36430000000015,
      'month'              => 8,
      'year'               => 2021,
      'first_name'         => 'Bob',
      'last_name'          => 'Smith',
      'verification_value' => 988,
      'type'               => 'diners_club'
    ));
  }
  
  function test_successful_visa() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->visa, $this->options));
    $this->assertsuccess($response);
    $this->assertresponse_message_prefix('Approved', $response);
  }
  
  function test_successful_mastercard() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->mastercard, $this->options));
    $this->assertsuccess($response);
    $this->assertresponse_message_prefix('Approved', $response);
  }
  
  function test_successful_amex() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->amex, $this->options));
    $this->assertsuccess($response);
    $this->assertresponse_message_prefix('Approved', $response);
  }
  
  function test_successful_diners() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->diners, $this->options));
    $this->assertsuccess($response);
    $this->assertresponse_message_prefix('Approved', $response);
  }
  
  function test_expired_visa() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->expired, $this->options));
    $this->assertfailure($response);
    $this->assertEquals('Declined - Expired card', $response->message);
  }
  
  function test_low_visa() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->low, $this->options));
    $this->assertfailure($response);
    $this->assertEquals('Declined - Not sufficient funds', $response->message);
  }
  
  function test_stolen_mastercard() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->stolen_mastercard, $this->options));
    $this->assertfailure($response);
    $this->assertEquals('Declined - Pick-up card', $response->message);
  }
  
  function test_invalid_visa() {
    $this->assert($response = $this->gateway->purchase($this->amount, $this->invalid, $this->options));
    $this->assertfailure($response);
    $this->assertEquals('Declined - Do not honour', $response->message);
  }

    function test_restricted_amex() {
      $this->assert($response = $this->gateway->purchase($this->amount, $this->restricted, $this->options));
      $this->assertfailure($response);
      $this->assertEquals('Rejected - Restricted card', $response->message);
    }
  
    function test_stolen_diners() {
      $this->assert($response = $this->gateway->purchase($this->amount, $this->stolen_diners, $this->options));
      $this->assertfailure($response);
      $this->assertEquals('Declined - Pick-up card', $response->message);
    }
  
  function test_invalid_login() {
    $gateway = new PayWay(array(
      'username' => '',
      'password' => '',
      'merchant' => 'TEST',
      'pem'      => PEM_FILE
    ));
    $this->assert($response = $gateway->purchase($this->amount, $this->visa, $this->options));
    $this->assertfailure($response);
    $this->assertEquals('Rejected - Unknown Customer Username or Password', $response->message);
  }
  
  protected
  
   function assertresponse_message_prefix($prefix, $response) {
    $split = explode(" - ", $response->message());
    $this->assertEquals($prefix, $split[2]);
  }
  
}

