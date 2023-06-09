#!/usr/bin/env php
<?php

/************************************************************************************************\
*                                                                                                *
*  ____  __.__        __                           _________                        __           *
* |    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____   *
* |      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \  *
* |    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> ) *
* |____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/  *
*         \/       \/     \/     \/     \/     \/          \/        \/     |__|                 *
*                                                                                                *
*                                                                                        By jj5  *
*                                                                                                *
\************************************************************************************************/

/**
 * 2023-04-02 jj5 - this test takes emergency delays for a spin...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/openssl.php';
require_once __DIR__ . '/../../../inc/test.php';

function main( $argv ) {

  kickass_crypto_setup_unit_test_environment();

  $mode = null;
  $debug = false;

  foreach ( $argv as $arg ) {

    switch ( $arg ) {

      case '--debug' : $debug = true; break;

      default : $mode = $arg; break;

    }
  }

  switch ( $mode ) {

    case 'nano' :

      return test_instance( new TestDelay );

    case 'micro' :

      define( 'KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP', true );

      return test_instance( new TestDelay );

    default :

      exit( KICKASS_CRYPTO_EXIT_TEST_FAILED );

  }
}

class TestDelay extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_KEYS;

  protected function do_delay( $ns_min, $ns_max ) {

    // 2023-04-02 jj5 - we don't do a delay, this will cause the emergency delay to kick in

    return;

  }
}

function test_instance( $crypto ) {

  $crypto->delay();

}

main( $argv );
