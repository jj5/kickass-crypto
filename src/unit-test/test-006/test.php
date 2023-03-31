#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt both simple and complex
// values. All the tests here should run relatively quickly because they succeed and don't cause
// any delay.

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  get_floats( $nan, $pos_inf, $neg_inf, $pos_zero, $neg_zero, $float_min, $float_max, $epslion );

  // 2023-04-01 jj5 - when we switched from PHP serialization to JSON encoding we lost the
  // ability to represent some values, those are these:
  //
  //test_value( 0.0 );
  //test_value( 1.0 );
  //test_nan( $nan );
  //test_value( $pos_inf );
  //test_value( $pos_zero );
  //test_value( $neg_zero );
  //test_value( [ 0.0 ] );
  //test_value( [ 1.0 ] );
  //test_value( [ $pos_inf ] );
  //test_value( [ $pos_zero ] );
  //test_value( [ $neg_zero ] );
  //test_date( new DateTime() );
  //test_class( new DateTime() );
  //test_class( new stdClass() );
  //test_class( kickass_round_trip() );
  //test_class( kickass_at_rest() );

  // 2023-04-01 jj5 - when we switched from PHP serialization to JSON encoding we lost the
  // ability to represent some values, those are the following, which we now make sure fail as
  // that's what we now expect. NOTE: "fail" is the wrong word. These succeed, but they don't
  // come back in their original form. See slow.php for things which actually don't encode at all
  // anymore.
  //
  test_inequality( 0.0 );
  test_inequality( 1.0 );
  test_inequality( $pos_zero );
  test_inequality( $neg_zero );
  test_inequality( [ 0.0 ] );
  test_inequality( [ 1.0 ] );
  test_inequality( [ $pos_zero ] );
  test_inequality( [ $neg_zero ] );
  test_inequality( new DateTime() );
  test_inequality( new stdClass() );
  test_inequality( kickass_round_trip() );
  test_inequality( kickass_at_rest() );

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
