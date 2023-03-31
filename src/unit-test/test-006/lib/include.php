#!/usr/bin/env php
<?php

// 2023-03-31 jj5 - these are bits and pieces for running our crypto tests...

function test_error( $instance ) {

  test_service_error( kickass_round_trip(), $instance );

  test_service_error( kickass_at_rest(), $instance );

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

  assert( is_string( $ciphertext ) );
  assert( count( $crypto->get_error_list() ) === 0 );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $compare( $instance, $plaintext ) );
  assert( count( $crypto->get_error_list() ) === 0 );

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

function get_floats(
  &$nan = null,
  &$pos_inf = null,
  &$neg_inf = null,
  &$pos_zero = null,
  &$neg_zero = null,
  &$float_min = null,
  &$float_max = null,
  &$epslion = null
) {

  $nan = NAN;

  $pos_inf = INF;
  $neg_int = INF * -1.0;

  $pos_zero = 0.0;
  $neg_zero = 0.0 * -1.0;

  $float_min = PHP_FLOAT_MIN;
  $float_max = PHP_FLOAT_MAX;
  $epslion = PHP_FLOAT_EPSILON;

}
