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
// 2023-03-30 jj5 - this test takes the various constructors for a spin, all code paths which
// should result in an exception from the constructor should be exercised...
//
\************************************************************************************************/

require_once __DIR__ . '/../../../inc/test-host.php';

class TestException extends KickassException {}

trait CustomThrow {

  protected function do_throw( int $code, $data = null, $previous = null ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE );

    }

    throw new TestException( $message, $code, $previous, $data );

  }
}

class TestCryptoAtRestInvalidConfig extends KickassCryptoAtRest {

  use CustomThrow;

}

class TestCryptoRoundTripInvalidConfig extends KickassCryptoRoundTrip {

  use CustomThrow;

}

class TestCryptoRoundTrip extends KickassCryptoRoundTrip {

  use KICKASS_DEBUG_LOG;

  use CustomThrow;

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }

}

function run_test() {

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE,
    function() {
      return new class extends TestCryptoRoundTrip {
        public function __construct() {
          $this->throw( 1234 );
        }
      };
    }
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoRoundTripInvalidConfig{};
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_CURR,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoRoundTripInvalidConfig {
        protected function get_config_secret_curr() { return 'invalid-secret'; }
      };
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_CURR,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoRoundTripInvalidConfig {
        protected function get_config_secret_curr() { return self::GenerateSecret(); }
        protected function get_config_secret_prev() { return 'invalid-secret'; }
      };
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_PREV,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH,
    function() {
      return new class extends TestCryptoRoundTrip {
        protected function get_const_key_hash() { return 'invalid-hash'; }
      };
    },
    [
      'key_hash' => 'invalid-hash',
      'hash_list' => hash_algos(),
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoAtRestInvalidConfig{};
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_LIST,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoAtRestInvalidConfig {
        protected function get_config_secret_list() { return 'not-an-array'; }
      };
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
    function() {
      return new class extends TestCryptoAtRestInvalidConfig {
        protected function get_config_secret_list() { return [ 'invalid-secret' ]; }
      };
    },
    [
      'problem' => KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST,
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER,
    function() {
      return new class extends TestCryptoRoundTrip {
        protected function get_const_cipher() { return 'invalid-cipher'; }
      };
    },
    [
      'cipher' => 'invalid-cipher',
      'cipher_list' => openssl_get_cipher_methods(),
    ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH,
    function() {
      return new class extends TestCryptoRoundTrip {
        protected function get_const_iv_length() { return 123; }
      };
    },
    [ 'cipher' => 'aes-256-gcm', 'iv_length' => 12, 'iv_length_expected' => 123 ]
  );

  test_exception(
    KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM,
    function() {
      return new class extends TestCryptoRoundTrip {
        protected function do_php_random_bytes( $length ) { throw new Exception( 'fail' ); }
      };
    },
    null
  );

}

function test_exception( int $code, callable $fn, $data = null ) {

  $exception = null;

  try {

    $crypto = $fn();

    assert( false );

  }
  catch ( TestException $ex ) {

    $exception = $ex;

  }
  catch ( AssertionError $ex ) {

    throw $ex;

  }
  catch ( Throwable $ex ) {

    var_dump( $ex );

    assert( false );

  }

  $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ];

  assert( get_class( $exception ) === TestException::class );
  assert( $code === $exception->getCode() );
  assert( $message === $exception->getMessage() );

  if ( $data !== null ) {

    if ( $exception->getData() !== $data ) {

      var_dump([
        'exception_data' => $exception->getData(),
        'expected_data' => $data,
      ]);

    }

    assert( $exception->getData() === $data );

  }
}

main( $argv );
