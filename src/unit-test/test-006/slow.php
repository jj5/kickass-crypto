#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt particularly large values;
// as this takes a fairly long time these tests are defined in this slow.php file.

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  test_setup();

  // 2023-03-30 jj5 - can't encrypt false... because this encryption fails the delay is triggered
  // which is why this test is in this slow.php file...
  //
  test_error( false );

  kickass_get_floats( $nan, $pos_inf, $neg_inf, $pos_zero, $neg_zero, $float_min, $float_max, $epslion );

  // 2023-04-01 jj5 - when we switched from PHP serialization to JSON encoding we lost the
  // ability to represent some values, those are the following, which we now make sure fail as
  // that's what we now expect.
  //
  test_error( $nan );
  test_error( $pos_inf );
  test_error( [ $pos_inf ] );

  $limit = 26;

  for ( $n = 0; $n < $limit; $n++ ) {

    //echo "n: $n\n";

    test_value( str_repeat( '0', pow( 2, $n ) ) );

  }

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( str_repeat( '0', pow( 2, $limit ) ) );

  $limit = 25;
  $big_array = [];

  for ( $n = 0; $n < $limit; $n++ ) {

    $big_array[] = str_repeat( '0', pow( 2, $n ) );

    // 2023-03-31 jj5 - these are big but should still work...
    //
    test_value( $big_array );

  }

  $big_array[] = str_repeat( '0', pow( 2, $limit ) );

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( $big_array );

}

main( $argv );
