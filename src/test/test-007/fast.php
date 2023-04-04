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
// 2023-03-31 jj5 - these tests take our run-time environment validation facilities for a spin.
// We check error handling by injecting values which should fail, and we validate that the
// run-time validation done by the library can be overridden by programmers using special
// constant defines.
//
// 2023-03-31 jj5 - NOTE: we don't use the test-host for these unit tests, because we need to
// configure our environment specially before it's loaded, so we take care of such things for
// ourselves in this script. See the fast.sh shell script for details about how this fast.php
// script is run.
//
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test.php';

main( $argv );

function main( $argv ) {

  kickass_crypto_setup_unit_test_environment();

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

      exit( KICKASS_CRYPTO_EXIT_OPTIONS_LISTED );

    }

    $configure = get_test_list()[ $mode_in ][ intval( $test_in ) ] ?? null;

    if ( ! is_callable( $configure ) ) {

      throw new \Exception( "Invalid test $mode_in:$test_in\n" );

    }

    $configure();

    require_once __DIR__ . '/../../../inc/openssl.php';

  }
  catch ( \Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    kickass_crypto_exit( $ex, KICKASS_CRYPTO_EXIT_TEST_FAILED );

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
        define( 'KICKASS_CRYPTO_TEST_HAS_OPENSSL', false );
      },
      function() {
        define( 'KICKASS_CRYPTO_TEST_PHP_VERSION', '7.0' );
        define( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX', '123' );
        define( 'KICKASS_CRYPTO_TEST_HAS_OPENSSL', false );
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
        define( 'KICKASS_CRYPTO_TEST_HAS_OPENSSL', false );
        define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true );
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
