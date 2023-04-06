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
 * 2023-04-06 jj5 - this test just checks the error reporting and clearing works properly.
 *
 * 2023-04-06 jj5 - TODO: the tests in this script could be more comprehensive.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

class TestOpenSslRoundTrip extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

class TestOpenSslAtRest extends \KickassCrypto\OpenSsl\KickassOpenSslAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

class TestSodiumRoundTrip extends \KickassCrypto\Sodium\KickassSodiumRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

class TestSodiumAtRest extends \KickassCrypto\Sodium\KickassSodiumAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

function run_test() {

  $openssl_round_trip = new TestOpenSslRoundTrip;
  $openssl_at_rest = new TestOpenSslAtRest;

  $sodium_round_trip = new TestSodiumRoundTrip;
  $sodium_at_rest = new TestSodiumAtRest;

  for ( $n = 1; $n <= 100; $n++ ) {

    test( $openssl_round_trip );

    test( $openssl_at_rest );

    test( $sodium_round_trip );

    test( $sodium_at_rest );

  }
}

function test( $crypto ) {

  $secret = base64_encode( random_bytes( random_int( 100, 100 ) ) );

  $ciphertext = $crypto->encrypt( $secret );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $secret === $plaintext );

  assert( $crypto->get_error() === null );
  assert( $crypto->get_error_list() === [] );

  $result = $crypto->decrypt( $plaintext );

  assert( $result === false );
  assert( $crypto->get_error() === KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID );
  assert( count( $crypto->get_error_list() ) === 2 );

  $result = $crypto->decrypt( $plaintext );

  assert( $result === false );
  assert( $crypto->get_error() === KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID );
  assert( count( $crypto->get_error_list() ) === 4 );

  $crypto->clear_error();

  assert( $crypto->get_error() === null );
  assert( $crypto->get_error_list() === [] );

}

main( $argv );
