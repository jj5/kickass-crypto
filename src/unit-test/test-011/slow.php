#!/usr/bin/env php
<?php

// 2023-04-02 jj5 - this test takes emergency delays for a spin...

require_once __DIR__ . '/../../../inc/library.php';
require_once __DIR__ . '/../../../inc/test.php';

function main( $argv ) {

  kickass_setup_unit_test_environment();

  switch ( $argv[ 1 ] ?? null ) {

    case 'nano' :

      return test_instance( new TestDelay );

    case 'micro' :

      define( 'KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP', true );

      return test_instance( new TestDelay );

    default :

      exit( 54 );

  }
}

class TestDelay extends KickassCrypto {

  use KICKASS_DEBUG_KEYS;

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN
  ) {

    // 2023-04-02 jj5 - we don't do a delay, this will cause the emergency delay to kick in

    return;

  }
}

function test_instance( $crypto ) {

  $crypto->delay();

}

main( $argv );
