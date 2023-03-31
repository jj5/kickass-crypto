#!/usr/bin/env php
<?php

require_once __DIR__ . '/../../test/util.php';

main( $argv );

function main( $argv ) {

  kickass_setup_unit_test_environment();

  $start = microtime( $as_float = true );

  try {

    $duration = microtime( $as_float = true ) - $start;

    $duration_format = number_format( $duration, 2 );

    $mode_in = $argv[ 1 ] ?? null;
    $test_in = $argv[ 2 ] ?? null;

    if ( $test_in === null ) {

      foreach ( get_test_list()[ $mode_in ] as $index => $fn ) {

        echo "$index\n";

      }

      exit( 90 );

    }

    $configure = get_test_list()[ $mode_in ][ intval( $test_in ) ] ?? null;

    if ( ! is_callable( $configure ) ) {

      throw new Exception( "Invalid test $mode_in:$test_in\n" );

    }

    $configure();

    require_once __DIR__ . '/../../code/KickassCrypto.php';

  }
  catch ( Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    exit( 1 );

  }
}

function get_test_list() {

  return [
    'fail' => [
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_ENABLE_PHP_VERSION', false );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE', false );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE', false );
      },
    ],
    'work' => [
      function() {
        // 2023-03-31 jj5 - if you're running on a supported platform, this should work by
        // default
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_ENABLE_PHP_VERSION', true );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE', true );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_ENABLE_PHP_VERSION', true );
        define( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE', true );
      },
    ],
  ];
}
