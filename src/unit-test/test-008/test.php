#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt both simple and complex
// values. All the tests here should run relatively quickly because they succeed and don't cause
// any delay.

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends KickassCrypto {

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }
  protected function get_passphrase_list() { return []; }

}

function run_test() {

  $crypto = new TestCrypto();

  ob_start();

  KickassCrypto::ReportTelemetry();

  $output = ob_get_clean();

  assert( $output === get_expected_output() );

}

function get_expected_output() {

  return ltrim("
= Counters =

instance..: 1

= Classes =

TestCrypto..: 1
");

}

main( $argv );
