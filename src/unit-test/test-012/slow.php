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
// 2023-04-02 jj5 - this test takes the default service locators for a good long spin...
//
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/etc/config.php';

function run_test() {

  if ( ! defined( 'DEBUG' ) ) { define( 'DEBUG', false ); }

  for ( $length = 0; $length < 10_000; $length += 1 ) {

    test_length( $length, 100 );

  }

  for ( $length = 10_000; $length < 1_000_000; $length += 100 ) {

    test_length( $length, 10_000 );

  }

  for ( $length = 1_000_000; $length < 10_000_000; $length += 10_000 ) {

    test_length( $length, 100_000 );

  }

  for ( $length = 10_000_000; $length < 100_000_000; $length += 100_000 ) {

    test_length( $length, 1_000_000 );

  }

  report( $length );

}

function test_length( $length, $report ) {

  if ( DEBUG ) {

    if ( 0 === $length % $report ) {

      report( $length );

    }
  }

  $data = $length ? random_bytes( $length ) : '';

  $text = base64_encode( $data );

  cycle( kickass_round_trip(), $text );

  cycle( kickass_at_rest(), $text );

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
