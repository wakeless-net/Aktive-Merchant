<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace AktiveMerchant\Billing\Gateways;

class NABTransact extends SecurePayAu {
      public static $test_url = 'https://transact.nab.com.au/test/xmlapi/payment';
      public static $live_url = 'https://transact.nab.com.au/live/xmlapi/payment';

      public static $test_periodic_url = 'https://transact.nab.com.au/test/xmlapi/payment';
      public static $live_periodic_url = 'https://transact.nab.com.au/live/xmlapi/payment';

      public static $supported_countries = array('AU');
      public static $supported_cardtypes = ['visa', 'master', 'american_express', 'diners_club', 'jcb'];

      # The homepage URL of the gateway
      public static $homepage_url = 'http://nab.com.au';

      # The name of the gateway
      public static $display_name = 'SecurePay';
      function build_base_xml() {
        return $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><NABTransactMessage></NABTransactMessage>');
      }

}
