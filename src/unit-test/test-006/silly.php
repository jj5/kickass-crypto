#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - these tests are silly. there's really no need to run them. the values for
// $n were picked based on what my development workstation could handle, you might have more
// or less luck.

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  test_setup();

  $limit = 26;

  $limit_str = str_repeat( '0', pow( 2, $limit ) );

  test_error( $limit_str );

  $big_array = [ $limit_str ];

  for ( $n = 1; $n < 8; $n++ ) {

    //echo "n.1: $n\n";

    $big_array[] = $big_array;

    test_error( $big_array );

  }

  for ( $n = 1; $n < 16; $n++ ) {

    //echo "n.2: $n\n";

    $big_array[] = str_repeat( $limit_str, $n );

    test_error( $big_array );

  }

  for ( $n = 1; $n < 8; $n++ ) {

    //echo "n.3: $n\n";

    $big_array[] = $limit_str;

    test_error( $big_array );

  }
}

function half( $str ) {

  return substr( $str, 0, intval( strlen( $str ) / 2 ) );

}

main( $argv );
