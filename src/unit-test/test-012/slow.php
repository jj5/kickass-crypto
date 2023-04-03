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
// 2023-04-02 jj5 - this test takes the four crypto providers for a good long spin...
//
\************************************************************************************************/

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/etc/config.php';

function run_test() {

  global $openssl_round_trip, $openssl_at_rest, $sodium_round_trip, $sodium_at_rest;

  $openssl_round_trip = new KickassCryptoOpenSslRoundTrip();
  $openssl_at_rest = new KickassCryptoOpenSslAtRest();
  $sodium_round_trip = new KickassCryptoSodiumRoundTrip();
  $sodium_at_rest = new KickassCryptoSodiumAtRest();

  test_data( '', 1 );

  for ( $length = 1; $length < 1_000; $length += 1 ) {

    test_length( $length, 100 );

  }

  for ( $length = 1_000; $length < 10_000; $length += 10 ) {

    test_length( $length, 1_000 );

  }

  for ( $length = 10_000; $length < 100_000; $length += 100 ) {

    test_length( $length, 1_000 );

  }

  for ( $length = 100_000; $length < 1_000_000; $length += 1_000 ) {

    test_length( $length, 10_000 );

  }

  for ( $length = 1_000_000; $length < 10_000_000; $length += 10_000 ) {

    test_length( $length, 100_000 );

  }

  for ( $length = 10_000_000; $length < 50_000_000; $length += 1_000_000 ) {

    test_length( $length, 1_000_000 );

  }

  // 2023-04-03 jj5 - this is as big as we can go. The -2 is for the pair of double quotes that
  // go around the string to turn it into JSON...
  //
  $length = KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_MAX - 2;

  test_text(
    str_repeat( '0', $length ),
    $length,
    $length
  );

}

function test_length( $length, $report ) {

  $data = random_bytes( $length );

  test_data( $data, $report, $length );

}

function test_data( $data, $report, $length = 0 ) {

  $text = base64_encode( $data );

  test_text( $text, $report, $length );

}

function test_text( $text, $report, $length = 0 ) {

  global $openssl_round_trip, $openssl_at_rest, $sodium_round_trip, $sodium_at_rest;

  if ( DEBUG ) {

    if ( 0 === $length % $report ) {

      report( $length );

    }
  }

  cycle( $openssl_round_trip, $text );

  cycle( $openssl_at_rest, $text );

  cycle( $sodium_round_trip, $text );

  cycle( $sodium_at_rest, $text );

}

function cycle( $crypto, $text ) {

  $ciphertext = $crypto->encrypt( $text );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $text === $plaintext );

}

function report( $length ) {

  $formatted = number_format( $length );

  echo "$formatted...\n";

}

main( $argv );
