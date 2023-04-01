#!/usr/bin/env php
<?php

// 2023-03-31 jj5 - this tests does some very rudiementary testing of our class counter telemetry.

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends KickassCrypto {

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }
  protected function get_passphrase_list() {
    static $list = null;
    if ( $list === null ) {
      $secret = self::GenerateSecret();
      $passphrase = $this->calc_passphrase( $secret );
      $list = [ $passphrase ];
    }
    return $list;
  }

}

function run_test() {

  $crypto = new TestCrypto();

  $ciphertext = $crypto->encrypt( 'test' );

  ob_start();

  KickassCrypto::ReportTelemetry();

  $output = ob_get_clean();

  assert( $output === get_expected_output() );

}

function get_expected_output() {

  return ltrim("
= Counters =

instance..: 1
encrypt...: 1

= Classes =

TestCrypto..: 1

= Lengths =

5516..: 1
");

}

main( $argv );
