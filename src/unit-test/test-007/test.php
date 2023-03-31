#!/usr/bin/env php
<?php

// 2023-03-31 jj5 - these tests take our run-time environment validation facilities for a spin.
// We check error handling by injecting values which should fail, and we validate that the
// run-time validation done by the library can be overridden by programmers using special
// constant defines.

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

      // 2023-03-31 jj5 - we just exit here with a non-zero value. It's not because we have failed
      // but just because we don't want the caller to think that a unit-test has succeeded if
      // they accidentally invoke this code path by mistake. Since our caller knows to expect
      // this error level it can be ignored by them.

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

    exit( 40 );

  }
}

function get_test_list() {

  // 2023-03-31 jj5 - so decide if the configured environment should be expected to work or
  // expected to fail and then define a function to apply that environment config. The test
  // runner will configure the environment as specified in your function and then load the
  // crypto library which should then succeed or fail depending on the configuration.

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
        define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', false );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', false );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', false );
      },
    ],
    'work' => [
      function() {
        // 2023-03-31 jj5 - if you're running on a supported platform, this should work by
        // default
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true );
        define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true );
      },
    ],
  ];
}
