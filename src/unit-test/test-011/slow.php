#!/usr/bin/env php
<?php

// 2023-04-02 jj5 - this test takes emergency delays for a spin...

require_once __DIR__ . '/../../../inc/library.php';
require_once __DIR__ . '/../../../inc/test.php';

function main( $argv ) {

  kickass_setup_unit_test_environment();

  switch ( $argv[ 1 ] ?? null ) {

    case 'nano' :

      test_instance( new TestDelay );

      break;

    case 'micro' :

      define( 'KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP', true );

      test_instance( new TestDelay );

      break;

    default :

      exit( 54 );

  }

}

class TestDelay extends KickassCrypto {

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

  protected function do_delay( int $ns_max = KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX, int $ns_min = KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN ) {

    return;

  }

}

function test_instance( $crypto ) {

  $crypto->delay();

}

main( $argv );
