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
 * 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt both simple and complex
 * values using text serialization.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  test_setup();

  kickass_crypto_get_floats(
    $nan,
    $pos_inf,
    $neg_inf,
    $pos_zero,
    $neg_zero,
    $float_min,
    $float_max,
    $epslion
  );

  // 2023-04-01 jj5 - when we switched from PHP serialization to JSON encoding we lost the
  // ability to represent some values, those are these:
  //
  test_text( 0.0 );
  test_text( 1.0 );
  test_text( $nan );
  test_text( $pos_inf );
  test_text( $pos_zero );
  test_text( $neg_zero );
  test_error( [ 0.0 ] );
  test_error( [ 1.0 ] );
  test_error( [ $pos_inf ] );
  test_error( [ $pos_zero ] );
  test_error( [ $neg_zero ] );
  test_error( new DateTime() );
  test_error( new DateTime() );
  test_error( new stdClass() );
  test_error( kickass_round_trip() );
  test_error( kickass_at_rest() );

  // 2023-04-04 jj5 - following are tests that should work regardless on serialization format

  test_text( true );

  test_text( 0 );
  test_text( '0' );
  test_text( 1 );
  test_text( '1' );
  test_text( 123 );
  test_text( '123' );
  test_text( PHP_INT_MIN );
  test_text( PHP_INT_MAX );

  test_text( '0.0' );
  test_text( '1.0' );
  test_text( 1.23 );
  test_text( '1.23' );

  test_text( $neg_inf );
  test_text( $float_min );
  test_text( $float_max );
  test_text( $epslion );

  test_text( '' );
  test_text( ' ' );
  test_text( '   ' );
  test_text( "\0" );
  test_text( "\t" );
  test_text( "\r" );
  test_text( "\n" );
  test_text( "\r\n" );

  test_error( [] );

  test_error( [ false ] );
  test_error( [ true ] );

  test_error( [ 0 ] );
  test_error( [ '0' ] );
  test_error( [ 1 ] );
  test_error( [ '1' ] );
  test_error( [ 123 ] );
  test_error( [ '123' ] );
  test_error( [ PHP_INT_MIN ] );
  test_error( [ PHP_INT_MAX ] );

  test_error( [ '0.0' ] );
  test_error( [ '1.0' ] );
  test_error( [ 1.23 ] );
  test_error( [ '1.23' ] );

  test_error( [ $neg_inf ] );
  test_error( [ $float_min ] );
  test_error( [ $float_max ] );
  test_error( [ $epslion ] );

  test_error( [ '' ] );
  test_error( [ ' ' ] );
  test_error( [ '   ' ] );
  test_error( [ "\0" ] );
  test_error( [ "\t" ] );
  test_error( [ "\r" ] );
  test_error( [ "\n" ] );
  test_error( [ "\r\n" ] );

  test_error( [ false, 0, PHP_INT_MIN, PHP_FLOAT_MIN, '', [] ] );
  test_error( [ true, 1, PHP_INT_MAX, PHP_FLOAT_MAX, ' ', [ 1, 'two', '3' ] ] );

  test_error( [ 'a' => 1, 'b' => 2, 'c' => 3 ] );
  test_error( [ 1 => 'a', 2 => 'b', 3 => 'c' ] );

}

main( $argv );
