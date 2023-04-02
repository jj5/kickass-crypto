#!/usr/bin/env php
<?php

// 2023-03-31 jj5 - this tests does some very rudiementary testing of our class counter telemetry.

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends KickassCrypto {

  use KICKASS_DEBUG;

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
= Functions =

__construct..: 1
encrypt......: 1

= Classes =

TestCrypto..: 1

= Lengths =

5516..: 1
");

}

main( $argv );
