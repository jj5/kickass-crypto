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

/**
 * 2023-03-31 jj5 - these tests take our JSON encoding for a spin.
 *
 * 2023-03-31 jj5 - NOTE: we don't use the test-host for these unit tests, because we need to
 * configure our environment specially before it's loaded, so we take care of such things for
 * ourselves in this script. See the fast.sh shell script for details about how this fast.php
 * script is run.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test.php';

main( $argv );

function main( $argv ) {

  assert( class_exists( JsonException::class ) );

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

    kickass_crypto_get_floats(
      $nan,
      $pos_inf,
      $neg_inf,
      $pos_zero,
      $neg_zero,
      $float_min,
      $float_max,
      $epslion
    );

    test_cycle( 'test' );

    test_cycle( true );

    test_cycle( 0 );
    test_cycle( '0' );
    test_cycle( 1 );
    test_cycle( '1' );
    test_cycle( 123 );
    test_cycle( '123' );
    test_cycle( PHP_INT_MIN );
    test_cycle( PHP_INT_MAX );

    test_cycle( '0.0' );
    test_cycle( '1.0' );
    test_cycle( 1.23 );
    test_cycle( '1.23' );

    test_cycle( $neg_inf );
    test_cycle( $float_min );
    test_cycle( $float_max );
    test_cycle( $epslion );

    test_cycle( '' );
    test_cycle( ' ' );
    test_cycle( '   ' );
    test_cycle( "\0" );
    test_cycle( "\t" );
    test_cycle( "\r" );
    test_cycle( "\n" );
    test_cycle( "\r\n" );

    test_cycle( [] );

    test_cycle( [ false ] );
    test_cycle( [ true ] );

    test_cycle( [ 0 ] );
    test_cycle( [ '0' ] );
    test_cycle( [ 1 ] );
    test_cycle( [ '1' ] );
    test_cycle( [ 123 ] );
    test_cycle( [ '123' ] );
    test_cycle( [ PHP_INT_MIN ] );
    test_cycle( [ PHP_INT_MAX ] );

    test_cycle( [ '0.0' ] );
    test_cycle( [ '1.0' ] );
    test_cycle( [ 1.23 ] );
    test_cycle( [ '1.23' ] );

    test_cycle( [ $neg_inf ] );
    test_cycle( [ $float_min ] );
    test_cycle( [ $float_max ] );
    test_cycle( [ $epslion ] );

    test_cycle( [ '' ] );
    test_cycle( [ ' ' ] );
    test_cycle( [ '   ' ] );
    test_cycle( [ "\0" ] );
    test_cycle( [ "\t" ] );
    test_cycle( [ "\r" ] );
    test_cycle( [ "\n" ] );
    test_cycle( [ "\r\n" ] );

    test_cycle( [ false, 0, PHP_INT_MIN, PHP_FLOAT_MIN, '', [] ] );
    test_cycle( [ true, 1, PHP_INT_MAX, PHP_FLOAT_MAX, ' ', [ 1, 'two', '3' ] ] );

    test_cycle( [ 'a' => 1, 'b' => 2, 'c' => 3 ] );
    test_cycle( [ 1 => 'a', 2 => 'b', 3 => 'c' ] );

    if ( defined( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS' ) ) {

      $options = CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS;

      if ( kickass_crypto_is_set( $options, JSON_INVALID_UTF8_IGNORE ) ) {

        $invalid_utf8 = "\xE2\x28\xA1";

        test_cycle( $invalid_utf8, '(' );

      }

      if ( kickass_crypto_is_set( $options, JSON_INVALID_UTF8_SUBSTITUTE ) ) {

        $invalid_utf8 = "\xE2\x28\xA1";

        test_cycle( $invalid_utf8, hex2bin( 'efbfbd28efbfbd' ) );

      }
    }

    //KickassCrypto::ReportTelemetry();

  }
  catch ( \Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    kickass_crypto_exit( $ex, KICKASS_CRYPTO_EXIT_TEST_FAILED );

  }
}

function test_cycle( $input, $expect = null ) {

  if ( $expect === null ) { $expect = $input; }

  $ciphertext = kickass_round_trip()->encrypt( $input );

  $plaintext = kickass_round_trip()->decrypt( $ciphertext );

  if ( false ) {

    $encode_options = defined( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS' ) ?
      CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS :
      null;

    $decode_options = defined( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS' ) ?
      CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS :
      null;

    if ( $plaintext !== $expect ) {

      var_dump([
        'plaintext' => $plaintext,
        'input' => $input,
        'expect' => $expect,
        'hex' => bin2hex( $plaintext ),
        'class' => get_class( kickass_round_trip() ),
        'encode_options' => $encode_options,
        'decode_options' => $decode_options,
      ]);

      debug_print_backtrace();

    }
  }

  assert( $plaintext === $expect );

}

function get_test_list() {

  // 2023-03-31 jj5 - so decide if the configured environment should be expected to work or
  // expected to fail and then define a function to apply that environment config. The test
  // runner will configure the environment as specified in your function and then load the
  // crypto library which should then succeed or fail depending on the configuration.

  return [
    'fail' => [
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_NUMERIC_CHECK );
      },
    ],
    'work' => [
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_FORCE_OBJECT );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_HEX_QUOT );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_HEX_TAG );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_HEX_AMP );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_HEX_APOS );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_INVALID_UTF8_IGNORE );
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_INVALID_UTF8_IGNORE );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_INVALID_UTF8_SUBSTITUTE );
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_INVALID_UTF8_SUBSTITUTE );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_PARTIAL_OUTPUT_ON_ERROR );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_PRESERVE_ZERO_FRACTION );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_PRETTY_PRINT );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_UNESCAPED_LINE_TERMINATORS );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_UNESCAPED_SLASHES );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_UNESCAPED_UNICODE );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_THROW_ON_ERROR );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_BIGINT_AS_STRING );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_INVALID_UTF8_IGNORE );
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_INVALID_UTF8_IGNORE );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_INVALID_UTF8_SUBSTITUTE );
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_INVALID_UTF8_SUBSTITUTE );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_OBJECT_AS_ARRAY );
      },
      function() {
        define( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );
      },
    ],
  ];
}
