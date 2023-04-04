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
// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt both simple and complex
// values using PHP serialization.
//
// 2023-04-04 jj5 - All the tests here should run relatively quickly because they succeed and
// don't cause any delay.
//
\************************************************************************************************/

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
  test_value( 0.0 );
  test_value( 1.0 );
  test_nan( $nan );
  test_value( $pos_inf );
  test_value( $pos_zero );
  test_value( $neg_zero );
  test_value( [ 0.0 ] );
  test_value( [ 1.0 ] );
  test_value( [ $pos_inf ] );
  test_value( [ $pos_zero ] );
  test_value( [ $neg_zero ] );
  test_date( new DateTime() );
  test_class( new DateTime() );
  test_class( new stdClass() );
  test_class( kickass_round_trip() );
  test_class( kickass_at_rest() );

  // 2023-04-04 jj5 - following are tests that should work regardless on serialization format

  test_value( true );

  test_value( 0 );
  test_value( '0' );
  test_value( 1 );
  test_value( '1' );
  test_value( 123 );
  test_value( '123' );
  test_value( PHP_INT_MIN );
  test_value( PHP_INT_MAX );

  test_value( '0.0' );
  test_value( '1.0' );
  test_value( 1.23 );
  test_value( '1.23' );

  test_value( $neg_inf );
  test_value( $float_min );
  test_value( $float_max );
  test_value( $epslion );

  test_value( '' );
  test_value( ' ' );
  test_value( '   ' );
  test_value( "\0" );
  test_value( "\t" );
  test_value( "\r" );
  test_value( "\n" );
  test_value( "\r\n" );

  test_value( [] );

  test_value( [ false ] );
  test_value( [ true ] );

  test_value( [ 0 ] );
  test_value( [ '0' ] );
  test_value( [ 1 ] );
  test_value( [ '1' ] );
  test_value( [ 123 ] );
  test_value( [ '123' ] );
  test_value( [ PHP_INT_MIN ] );
  test_value( [ PHP_INT_MAX ] );

  test_value( [ '0.0' ] );
  test_value( [ '1.0' ] );
  test_value( [ 1.23 ] );
  test_value( [ '1.23' ] );

  test_value( [ $neg_inf ] );
  test_value( [ $float_min ] );
  test_value( [ $float_max ] );
  test_value( [ $epslion ] );

  test_value( [ '' ] );
  test_value( [ ' ' ] );
  test_value( [ '   ' ] );
  test_value( [ "\0" ] );
  test_value( [ "\t" ] );
  test_value( [ "\r" ] );
  test_value( [ "\n" ] );
  test_value( [ "\r\n" ] );

  test_value( [ false, 0, PHP_INT_MIN, PHP_FLOAT_MIN, '', [] ] );
  test_value( [ true, 1, PHP_INT_MAX, PHP_FLOAT_MAX, ' ', [ 1, 'two', '3' ] ] );

  test_value( [ 'a' => 1, 'b' => 2, 'c' => 3 ] );
  test_value( [ 1 => 'a', 2 => 'b', 3 => 'c' ] );

}

main( $argv );
