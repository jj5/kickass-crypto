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
 * 2023-03-31 jj5 - these are bits and pieces for running our crypto tests...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-04-07 jj5 - OpenSSL round-trip
 */
class TestOpenSslRoundTrip extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

/**
 * 2023-04-07 jj5 - OpenSSL at-rest
 */
class TestOpenSslAtRest extends \KickassCrypto\OpenSsl\KickassOpenSslAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

/**
 * 2023-04-07 jj5 - Sodium round-trip
 */
class TestSodiumRoundTrip extends \KickassCrypto\Sodium\KickassSodiumRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

/**
 * 2023-04-07 jj5 - Sodium at-rest
 */
class TestSodiumAtRest extends \KickassCrypto\Sodium\KickassSodiumAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

function test_setup() {

  global $openssl_round_trip, $openssl_at_rest, $sodium_round_trip, $sodium_at_rest;

  $openssl_round_trip = new TestOpenSslRoundTrip();
  $openssl_at_rest = new TestOpenSslAtRest();
  $sodium_round_trip = new TestSodiumRoundTrip();
  $sodium_at_rest = new TestSodiumAtRest();

}

function test_error( $instance ) {

  global $openssl_round_trip, $openssl_at_rest, $sodium_round_trip, $sodium_at_rest;

  test_service_error( $openssl_round_trip, $instance );

  test_service_error( $openssl_at_rest, $instance );

  test_service_error( $sodium_round_trip, $instance );

  test_service_error( $sodium_at_rest, $instance );

}

function test_text( $instance, $compare = 'text_equal' ) {

  global $openssl_round_trip, $openssl_at_rest, $sodium_round_trip, $sodium_at_rest;

  test_service_instance( $openssl_round_trip, $instance, $compare );

  test_service_instance( $openssl_at_rest, $instance, $compare );

  test_service_instance( $sodium_round_trip, $instance, $compare );

  test_service_instance( $sodium_at_rest, $instance, $compare );

}

function test_service_error( $crypto, $instance ) {

  $ciphertext = $crypto->encrypt( $instance );

  assert( $ciphertext === false );
  assert( count( $crypto->get_error_list() ) > 0 );

  $crypto->clear_error();

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $plaintext === false );
  assert( count( $crypto->get_error_list() ) > 0 );

  $crypto->clear_error();

}

function test_service_instance( $crypto, $instance, $compare ) {

  $ciphertext = $crypto->encrypt( $instance );

  if ( ! is_string( $ciphertext ) ) {

    var_dump([
      'error' => $crypto->get_error(),
      'instance' => $instance,
      'ciphertext' => $ciphertext,
    ]);

  }

  assert( is_string( $ciphertext ) );
  assert( count( $crypto->get_error_list() ) === 0 );

  $plaintext = $crypto->decrypt( $ciphertext );

  if ( ! $compare( $instance, $plaintext ) ) {

    var_dump([
      'value' => $instance,
      'plaintext' => $plaintext,
      'error_list' => $crypto->get_error_list(),
    ]);

  }

  if ( count( $crypto->get_error_list() ) !== 0 ) {

    var_dump( $crypto->get_error_list() );

  }

  assert( $compare( $instance, $plaintext ) );
  assert( count( $crypto->get_error_list() ) === 0 );

  if ( false ) {

    var_dump([
      'instance' => $instance,
      'ciphertext' => $ciphertext,
      'plaintext' => $plaintext,
    ]);

  }
}

function text_equal( $a, $b ) {
  if ( false ) {
    var_dump([
      'a' => strval( $a ),
      'b' => strval( $b ),
    ]);
  }
  return strval( $a ) === strval( $b );
}
