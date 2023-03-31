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

  $limit = 26;

  for ( $n = 0; $n < $limit; $n++ ) {

    test_value( str_repeat( '0', pow( 2, $n ) ) );

  }

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( str_repeat( '0', pow( 2, $limit ) ) );

  $limit = 25;
  $big_array = [];

  for ( $n = 0; $n < $limit; $n++ ) {

    $big_array[] = str_repeat( '0', pow( 2, $n ) );

    test_value( $big_array );

  }

  $big_array[] = str_repeat( '0', pow( 2, $limit ) );

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( $big_array );

}

main( $argv );
