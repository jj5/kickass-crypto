#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt complex values...

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../host/unit-test.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  // 2023-03-30 jj5 - can't encrypt false...
  //
  test_error( false );

  test_value( true );

  test_value( 0 );
  test_value( '0' );
  test_value( 1 );
  test_value( '1' );
  test_value( 123 );
  test_value( '123' );
  test_value( PHP_INT_MIN );
  test_value( PHP_INT_MAX );

  test_value( 0.0 );
  test_value( '0.0' );
  test_value( 1.0 );
  test_value( '1.0' );
  test_value( 1.23 );
  test_value( '1.23' );
  test_value( PHP_FLOAT_MIN );
  test_value( PHP_FLOAT_MAX );
  test_value( PHP_FLOAT_EPSILON );

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

  test_value( [ 0.0 ] );
  test_value( [ '0.0' ] );
  test_value( [ 1.0 ] );
  test_value( [ '1.0' ] );
  test_value( [ 1.23 ] );
  test_value( [ '1.23' ] );
  test_value( [ PHP_FLOAT_MIN ] );
  test_value( [ PHP_FLOAT_MAX ] );
  test_value( [ PHP_FLOAT_EPSILON ] );

  test_value( [ '' ] );
  test_value( [ ' ' ] );
  test_value( [ '   ' ] );
  test_value( [ "\0" ] );
  test_value( [ "\t" ] );
  test_value( [ "\r" ] );
  test_value( [ "\n" ] );
  test_value( [ "\r\n" ] );

  test_value( [ false, 0, 0.0, PHP_INT_MIN, PHP_FLOAT_MIN, '', [] ] );
  test_value( [ true, 1, 1.0, PHP_INT_MAX, PHP_FLOAT_MAX, ' ', [ 1, 2.0, '3' ] ] );

  test_date( new DateTime() );

  test_class( new DateTime() );
  test_class( new stdClass() );
  test_class( kickass_round_trip() );
  test_class( kickass_at_rest() );

}

main( $argv );