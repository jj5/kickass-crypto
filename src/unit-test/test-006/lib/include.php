#!/usr/bin/env php
<?php

define( 'KICKASS_CRYPTO_DEBUG', true );

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

function date_equal( $a, $b ) { return $a->format( 'r' ) === $b->format( 'r' ); }

function class_equal( $a, $b ) { return get_class( $a ) === get_class( $b ); }
