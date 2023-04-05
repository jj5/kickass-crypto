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

/************************************************************************************************\
//
// 2023-03-31 jj5 - these are bits and pieces for running our crypto tests...
//
\************************************************************************************************/

class TestOpenSslRoundTrip extends \KickassCrypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestOpenSslAtRest extends \KickassCrypto\Module\OpenSsl\KickassOpenSslAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestSodiumRoundTrip extends \KickassCrypto\Module\Sodium\KickassSodiumRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestSodiumAtRest extends \KickassCrypto\Module\Sodium\KickassSodiumAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

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

function test_inequality( $instance, $compare = 'value_unequal' ) {

  test_service_instance( kickass_round_trip(), $instance, $compare );

  test_service_instance( kickass_at_rest(), $instance, $compare );

}

function test_value( $instance, $compare = 'value_equal' ) {

  test_service_instance( kickass_round_trip(), $instance, $compare );

  test_service_instance( kickass_at_rest(), $instance, $compare );

}

function test_nan( $instance, $compare = 'nan_equal' ) {

  test_service_instance( kickass_round_trip(), $instance, $compare );

  test_service_instance( kickass_at_rest(), $instance, $compare );

}

function test_date( $instance, $compare = 'date_equal' ) {

  test_service_instance( kickass_round_trip(), $instance, $compare );

  test_service_instance( kickass_at_rest(), $instance, $compare );

}

function test_class( $instance, $compare = 'class_equal' ) {

  test_service_instance( kickass_round_trip(), $instance, $compare );

  test_service_instance( kickass_at_rest(), $instance, $compare );

}

function test_service_instance( $crypto, $instance, $compare ) {

  $ciphertext = $crypto->encrypt( $instance );

  if ( ! is_string( $ciphertext ) ) {

    var_dump([
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

function value_unequal( $a, $b ) {
  if ( false ) {
    var_dump([
      'a' => strlen( $a ),
      'b' => strlen( $b ),
    ]);
  }
  if ( $a === false ) { return false; }
  if ( $b === false ) { return false; }
  return $a !== $b;
}

function value_equal( $a, $b ) {
  if ( false ) {
    var_dump([
      'a' => strlen( $a ),
      'b' => strlen( $b ),
    ]);
  }
  return $a === $b;
}

function nan_equal( $a, $b ) {

  return is_nan( $a ) && is_nan( $b );

}

function date_equal( $a, $b ) { return $a->format( 'r' ) === $b->format( 'r' ); }

function class_equal( $a, $b ) { return get_class( $a ) === get_class( $b ); }
