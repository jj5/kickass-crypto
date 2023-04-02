<?php

/************************************************************************************************\

 ____  __.__        __                           _________                        __
|    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____
|      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \
|    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> )
|____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/
        \/       \/     \/     \/     \/     \/          \/        \/     |__|

                                                                                        By jj5

\************************************************************************************************/

/************************************************************************************************\
//
// 2023-03-30 jj5 - this is the Kickass Crypto library, if you want to use the library this is
// the only file that you need to include, but other goodies ship with the project. The actual
// proper and supported way to include this library is to include the inc/library.php include
// file which will handle including this file after making sure it is safe to do so.
//
// 2023-03-31 jj5 - SEE: the Kickass Crypto home page: https://github.com/jj5/kickass-crypto
//
// 2023-03-30 jj5 - make sure you load a valid config.php file, then use this library like this:
//
//   $ciphertext = kickass_round_trip()->encrypt( 'secret data' );
//   $plaintext = kickass_round_trip()->decrypt( $ciphertext );
//
// see README.md for more info.
//
// 2023-03-31 jj5 - a valid config.php file will define constants per relevant use-case.
//
// For round-trip use cases define these keys:
//
//* CONFIG_ENCRYPTION_SECRET_CURR
//* CONFIG_ENCRYPTION_SECRET_PREV (optional)
//
// For at-rest use cases define this list of keys:
//
//* CONFIG_ENCRYPTION_SECRET_LIST
//
// See bin/gen-key.php in this project for key generation.
//
\************************************************************************************************/

// 2023-03-30 jj5 - these two service locator functions will automatically create appropriate
// encryption components for each use case. If you want to override with a different
// implementation you can pass in a new instance, or you can manage construction yourself and
// access some other way. These functions are how you should ordinarily access this library.

function kickass_round_trip( $set = false ) {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) { $instance = new KickassCryptoRoundTrip(); }

  return $instance;

}

function kickass_at_rest( $set = false ) {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) { $instance = new KickassCryptoAtRest(); }

  return $instance;

}

(function() {

  // 2023-03-31 jj5 - this anonymous function is for validating our run-time environment. If
  // there's a problem then we exit, unless the programmer has overridden that behavior by
  // defining certain constants as detailed here:
  //
  //* to disable PHP version check:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true );
  //
  //* to disable PHP 64-bit word size check:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true );
  //
  //* to disable checks for the OpenSSL library functions:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true );
  //

  $errors = [];

  try {

    // 2023-03-31 jj5 - NOTE: we read in our environment settings by allowing them to be
    // overridden with constant values. We do this so that we can test our validation logic on
    // platforms which are otherwise valid.

    $php_version = defined( 'KICKASS_CRYPTO_TEST_PHP_VERSION' ) ?
      KICKASS_CRYPTO_TEST_PHP_VERSION :
      phpversion();

    $php_int_max = defined( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX' ) ?
      KICKASS_CRYPTO_TEST_PHP_INT_MAX :
      PHP_INT_MAX;

    // 2023-04-01 jj5 - innocent until proven guilty...
    //
    $has_openssl = true;

    if ( defined( 'KICKASS_CRYPTO_TEST_HAS_OPENSSL' ) ) {

      $has_openssl = KICKASS_CRYPTO_TEST_HAS_OPENSSL;

    }
    else {

      $openssl_functions = [
        'openssl_get_cipher_methods',
        'openssl_cipher_iv_length',
        'openssl_error_string',
        'openssl_encrypt',
        'openssl_decrypt',
      ];

      foreach ( $openssl_functions as $function ) {

        if ( ! function_exists( $function ) ) { $has_openssl = false; }

      }
    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', false );

    }

    if ( version_compare( $php_version, '7.4', '<' ) ) {

      if ( KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK ) {

        // 2023-03-31 jj5 - the programmer has enabled this version of PHP, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library requires PHP version 7.4 or greater. " .
          "define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true ) to force enablement.";

      }
    }

    if ( strval( $php_int_max ) !== '9223372036854775807' ) {

      if ( KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK ) {

        // 2023-03-31 jj5 - the programmer has enabled this platform, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library has only been tested on 64-bit platforms. " .
          "define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true ) to force enablement.";

      }
    }

    if ( ! $has_openssl ) {

      if ( KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK ) {

        // 2023-04-01 jj5 - the programmer has enabled OpenSSL anyway, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library requires the PHP OpenSSL library. " .
          "define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true ) to force enablement.";

      }
    }

    foreach ( $errors as $error ) {

      $message = __FILE__ . ': ' . $error;

      if ( defined( 'STDERR' ) ) {

        fwrite( STDERR, "$message\n" );

      }
      else {

        error_log( $message );

      }
    }
  }
  catch ( Throwable $ex ) {

    error_log( __FILE__ . ': ' . $ex->getMessage() );

  }

  // 2023-03-31 jj5 - SEE: my standard error levels: https://www.jj5.net/sixsigma/Error
  //
  // 2023-03-31 jj5 - the error level 40 means "invalid run-time environment, cannot run."
  //
  if ( $errors ) { exit( 40 ); }

})();

// 2023-03-30 jj5 - this is the current data format version for this library. If you fork this
// library and alter the data format you should change this. If you do change this please use
// something other than 'KA' as the prefix. If you don't want the data format version reported
// in your encoded data override the encode() and decode() methods.
//
// 2023-04-02 jj5 - NOTE: you don't need to actually change this constant, you can just override
// get_const_data_format_version() and return a different string. For example:
//
// protected function get_const_data_format_version() { return 'MYKA1'; }
//
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION', 'KA0' );

// 2023-03-30 jj5 - these are the default values for configuration... these might be changed in
// future... note that 2^12 is 4KiB and 2^26 is 64 MiB.
//
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', pow( 2, 12 ) );
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX', pow( 2, 26 ) );
define( 'KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_MAX', pow( 2, 26 ) );
define(
  'KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS',
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
define( 'KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );

// 2023-03-29 jj5 - these delays are in nanoseconds, these might be changed in future...
//
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN',      1_000_000 );
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX', 10_000_000_000 );

// 2023-04-03 jj5 - this delay is a floating-point value in seconds, it's for comparison of the
// value returned from the PHP microtime()...
//
define(
  'KICKASS_CRYPTO_DELAY_SECONDS_MIN',
  1.0 / ( KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN / 1_000 )
);

// 2023-03-30 jj5 - this is our Base64 validation regex...
//
define(
  'KICKASS_CRYPTO_REGEX_BASE64',
  // 2023-04-01 jj5 - SEE: https://www.progclub.org/blog/2023/04/01/php-preg_match-regex-fail/
  // 2023-04-01 jj5 - NEW:
  '/^[a-zA-Z0-9\/+]{2,}={0,2}$/'
  // 2023-04-01 jj5 - OLD: this old base64 validation regex had some really bad performance
  // characteristics when tested with pathological inputs such as 2^17 zeros, see the article
  // about the problem at the link above.
  //'/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/'
);

// 2023-03-29 jj5 - exceptions are thrown from the constructor only, these are the possible
// exceptions. The exception codes should be stable, you can add new ones but don't change
// existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE',  1_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG',          2_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH',        3_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER',          4_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH',       5_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM',         6_000 );

// 2023-03-30 jj5 - these are the exception messages for each exception code. These exception
// messages should be stable, you can add new ones but don't change existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_MESSAGE', [
  KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE => 'invalid exception code.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG         => 'invalid config.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH       => 'invalid key hash.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER         => 'invalid cipher.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH      => 'invalid IV length.',
  KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM        => 'insecure random.',
]);

// 2023-03-30 jj5 - config problems are things that can go wrong with a config file...
//
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_CURR',
  'config missing: CONFIG_ENCRYPTION_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_CURR',
  'config invalid: CONFIG_ENCRYPTION_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_PREV',
  'config invalid: CONFIG_ENCRYPTION_SECRET_PREV.'
);

define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_LIST',
  'config missing: CONFIG_ENCRYPTION_SECRET_LIST.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST',
  'config invalid: CONFIG_ENCRYPTION_SECRET_LIST.'
);

// 2023-03-30 jj5 - these are the errors that can happen during encryptiong and decryption, we
// don't raise exceptions for these errors because a secret key or a passphrase might be on the
// call stack and we don't want to accidentally leak it. If an error occurs the boolean value
// false is returned and the error constant is added to the error list. Sometimes the same basic
// error can happen from multiple code points; when that happens we add a number in the hope that
// later we can find the specific point in the code which flagged the error.
//
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED', 'exception raised.' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2', 'exception raised (2).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3', 'exception raised (3).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4', 'exception raised (4).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5', 'exception raised (5).' );
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED', 'JSON encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED', 'JSON decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_ENCODING', 'invalid encoding.' );
define( 'KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING', 'unknown encoding.' );
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED', 'base64 decode failed.' );
define( 'KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE', 'cannot encrypt false.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE', 'invalid passphrase.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH', 'invalid passphrase length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2', 'invalid passphrase length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE', 'invalid chunk size.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH', 'invalid binary length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH', 'invalid IV length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2', 'invalid IV length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH', 'invalid tag length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2', 'invalid tag length (2).' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT', 'invalid ciphertext.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2', 'invalid ciphertext (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_DATA', 'invalid data.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT', 'invalid message format.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_SPEC', 'invalid data length spec.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE', 'invalid data length range.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_LENGTH', 'invalid message length.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED', 'data encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE', 'data encoding too large.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED', 'data decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_NO_VALID_KEY', 'no valid key.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED', 'decryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2', 'decryption failed (2).' );

// 2023-03-29 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
// need to override any of these, for instance to test the correct handling of error scenarios,
// pelase override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
// these... please see the documentation in README.md for an explanation of these values.
//
define( 'KICKASS_CRYPTO_KEY_HASH', 'sha512/256' );
define( 'KICKASS_CRYPTO_CIPHER', 'aes-256-gcm' );
define( 'KICKASS_CRYPTO_OPTIONS', OPENSSL_RAW_DATA );
define( 'KICKASS_CRYPTO_KEY_LENGTH_MIN', 88 );
define( 'KICKASS_CRYPTO_PASSPHRASE_LENGTH', 32 );
define( 'KICKASS_CRYPTO_IV_LENGTH', 12 );
define( 'KICKASS_CRYPTO_TAG_LENGTH', 16 );

// 2023-03-30 jj5 - we define an exception class for this component so that we can associate
// custom data with our exceptions... note that not all exceptions will have associated data.
//
class KickassException extends Exception {

  private $data;

  public function __construct( $message, $code = 0, $previous = null, $data = null ) {

    parent::__construct( $message, $code, $previous );

    $this->data = $data;

  }

  public function getData() { return $this->data; }

}

// 2023-04-02 jj5 - these traits make a bunch of assumptions about the class that hosts them.
// They've basically been designed to be in a class which extends KickassCrypto, they're not for
// use in other circumstances.

trait KICKASS_DEBUG_LOG {

  // 2023-04-02 jj5 - if you include this trait logs will only be written if DEBUG is defined...

  protected function do_log_error( $message ) {

    if ( ! $this->is_debug() ) { return false; }

    return parent::do_log_error( $message );

  }
}

trait KICKASS_DEBUG_KEYS {

  // 2023-04-02 jj5 - if you include this trait you'll be set up with a test key and a valid
  // config. The secret key isn't kept anywhere so you won't be able to decrypt data after your
  // test completes.

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }

  protected function get_passphrase_list() {
    static $list = null;
    if ( $list === null ) {
      $secret = self::GenerateSecret();
      $passphrase = $this->calc_passphrase( $secret );
      $list = [ $passphrase ];
    }
    return $list;
  }
}

trait KICKASS_DEBUG {

  // 2023-04-02 jj5 - these traits will set you up for debugging...

  use KICKASS_DEBUG_LOG;

  use KICKASS_DEBUG_KEYS;

}

// 2023-03-30 jj5 - these are indirections to default PHP functions. The main reason for using
// these is so that we can use them to inject errors during testing... some PHP functions such as
// is_int(), intval() and round() are called directly and not via these indirections. If you need
// to be able to inject invalid return values during testing this is the place to make such
// arrangements to do such things.
//
// 2023-03-31 jj5 - NOTE: these wrappers should do as little as possible and just defer entirely
// to the PHP implementation. One exception is that I like to initialize variables passed by
// reference to null, this is probably not necessary but it gives me the warm and fuzzies.
//
// 2023-03-31 jj5 - NOTE: when defining default variables you should use the same default values
// as the library functions you are calling use, or just don't provide a default value at all;
// that's a sensible enough option, you can make the wrapper demand a value from the caller if
// you want.
//
// 2023-04-02 jj5 - NOTE: the only assumption this trait makes about its environment is that a
// catch() method has been defined to notify exceptions. After exceptions are notified they are
// rethrown.
//
trait KICKASS_PHP_WRAPPER {

  protected final function php_base64_encode( $input ) {

    try {

      return $this->do_php_base64_encode( $input );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_base64_encode( $input ) {

    return base64_encode( $input );

  }

  protected final function php_base64_decode( $input, $strict ) {

    try {

      return $this->do_php_base64_decode( $input, $strict );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_base64_decode( $input, $strict ) {

    return base64_decode( $input, $strict );

  }

  protected final function php_json_encode( $value, $flags, $depth = 512 ) {

    try {

      return $this->do_php_json_encode( $value, $flags, $depth );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_json_encode( $value, $flags, $depth = 512 ) {

    return json_encode( $value, $flags, $depth );

  }

  protected final function php_json_decode( $json, $associative, $depth, $flags ) {

    try {

      return $this->do_php_json_decode( $json, $associative, $depth, $flags );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_json_decode( $json, $associative, $depth, $flags ) {

    return json_decode( $json, $associative, $depth, $flags );

  }

  protected final function php_random_int( $min, $max ) {

    try {

      return $this->do_php_random_int( $min, $max );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_random_int( $min, $max ) {

    return random_int( $min, $max );

  }

  protected final function php_random_bytes( $length ) {

    try {

      return $this->do_php_random_bytes( $length );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_random_bytes( $length ) {

    return random_bytes( $length );

  }

  protected final function php_openssl_get_cipher_methods() {
    try {

      return $this->do_php_openssl_get_cipher_methods();

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_get_cipher_methods() {

    return openssl_get_cipher_methods();

  }

  protected final function php_openssl_cipher_iv_length( $cipher ) {

    try {

      return $this->do_php_openssl_cipher_iv_length( $cipher );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_cipher_iv_length( $cipher ) {

    return openssl_cipher_iv_length( $cipher );

  }

  protected final function php_openssl_error_string() {

    try {

      return $this->do_php_openssl_error_string();

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_error_string() {

    return openssl_error_string();

  }

  protected final function php_openssl_encrypt(
    $plaintext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    &$tag
  ) {

    $tag = null;

    try {

      return $this->do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_encrypt(
    $plaintext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    &$tag
  ) {

    $tag = null;

    return openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );

  }

  protected final function php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    try {

      return $this->do_php_openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    return openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

  }

  protected final function php_time_nanosleep( $seconds, $nanoseconds ) {

    try {

      return $this->do_php_time_nanosleep( $seconds, $nanoseconds );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_time_nanosleep( $seconds, $nanoseconds ) {

    return time_nanosleep( $seconds, $nanoseconds );

  }

  protected final function php_sapi_name() {

    try {

      return $this->do_php_sapi_name();

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_sapi_name() {

    return php_sapi_name();

  }
}

// 2023-03-30 jj5 - the KickassCrypto class is the core of this library, but to use it you need
// an instance of either KickassCryptoRoundTrip or KickassCryptoAtRest, it's also possible to
// create your own instance by inheriting KickassCrypto and providing an implementation of the
// abstract methods to suite your use case.
//
// 2023-03-31 jj5 - NOTE: the intention with this library as a framework for implementing your
// own use cases is that you inherit directly from KickassCrypto itself and go from there. If it
// was possible for me to do so I would make KickassCryptoRoundTrip and KickassCryptoAtRest
// final, but I need to keep them open for unit-testing purposes. While there is nothing to stop
// you from inheriting either KickassCryptoRoundTrip or KickassCryptoAtRest if you do so you
// will have an effect on the class counter telemetry. The class counter telemetry only counts
// KickassCryptoRoundTrip or KickassCryptoAtRest instance directly, not the things which inherit
// from them. Other classes which inherit from KickassCrypto are counted separately, which is
// probably what you want. See the count_this() method if you need to change counting behavior.
//
abstract class KickassCrypto {

  use KICKASS_PHP_WRAPPER;

  // 2023-03-30 jj5 - our counters are stored here, call
  //* count_function() to increment a 'function' counter
  //* count_class() to increment a 'class' counter
  //* count_length() to increment a 'length' counter
  //
  // 2023-04-02 jj5 - the function counters count how many times some key functions were called.
  //
  // 2023-04-02 jj5 - the class counters count how many times certain classes were constructed.
  //
  // 2023-04-02 jj5 - the length counter counts the lengths of successfully encrypted data that
  // occur, these should group due to chunking.
  //
  private static $telemetry = [
    'function' => [],
    'class' => [],
    'length' => [],
  ];

  // 2023-03-29 jj5 - our list of errors is private, implementations can override the access
  // interface methods defined below...
  //
  private $error_list = [];

  // 2023-03-30 jj5 - this is for tracking the first openssl error that occurs, if any...
  //
  private $openssl_error = null;

  // 2023-04-02 jj5 - this flag indicates whether we need to inject a random delay or not, it gets
  // set if there's an error...
  //
  private $inject_delay = false;

  // 2023-03-30 jj5 - we throw exceptions from the constructor if our environment is invalid... if
  // the constructor succeeds then encryption and decryption should also usually succeed later on.
  // If encryption or decryption won't be able to succeed the constructor should throw.
  //
  public function __construct() {

    // 2023-03-31 jj5 - NOTE: we count all instances created, even if their constructors end up
    // throwing an exception thus making them unusable.

    $this->count_this( __FUNCTION__ );

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_CONFIG_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_CONFIG_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_KEY_HASH_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_KEY_HASH_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_RANDOM_BYTES_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_RANDOM_BYTES_VALIDATION', false );

    }

    if ( ! KICKASS_CRYPTO_DISABLE_CONFIG_VALIDATION ) {

      if ( ! $this->is_valid_config( $problem ) ) {

        $this->throw(
          KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
          [
            'problem' => $problem,
          ]
        );

      }

      assert( $problem === null );

    }

    $key_hash = $this->get_const_key_hash();
    $hash_list = hash_algos();

    if ( ! KICKASS_CRYPTO_DISABLE_KEY_HASH_VALIDATION ) {

      if ( ! in_array( $key_hash, $hash_list ) ) {

        $this->throw(
          KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH,
          [
            'key_hash' => $key_hash,
            'hash_list' => $hash_list,
          ]
        );

      }
    }

    $cipher = $this->get_const_cipher();
    $cipher_list = $this->php_openssl_get_cipher_methods();

    if ( ! KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION ) {

      if ( ! in_array( $cipher, $cipher_list ) ) {

        $this->throw(
          KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER,
          [
            'cipher' => $cipher,
            'cipher_list' => $cipher_list,
          ]
        );

      }
    }

    $iv_length = $this->php_openssl_cipher_iv_length( $cipher );
    $iv_length_expected = $this->get_const_iv_length();

    if ( ! KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION ) {

      if ( $iv_length !== $iv_length_expected ) {

        $this->throw(
          KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH,
          [
            'cipher' => $cipher,
            'iv_length' => $iv_length,
            'iv_length_expected' => $iv_length_expected,
          ]
        );

      }
    }

    if ( ! KICKASS_CRYPTO_DISABLE_RANDOM_BYTES_VALIDATION ) {

      try {

        $test_bytes = $this->php_random_bytes( 1 );

      }
      catch ( Random\RandomException $ex ) {

        $this->catch( $ex );

        return $this->throw( KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM );

      }
    }
  }

  // 2023-03-30 jj5 - implementations need to define what a valid config looks like and provide
  // a list of passphrases. The first passphrase in the list is the one that's used for
  // encryption, others are potentially used for decryption. See KickassCryptoRoundTrip and
  // KickassCryptoAtRest for details.
  //
  abstract protected function is_valid_config( &$problem = null );
  abstract protected function get_passphrase_list();

  // 2023-03-30 jj5 - this function will generate a secret key suitable for use in the config
  // file...
  //
  public static function GenerateSecret() {

    return base64_encode( random_bytes( 66 ) );

  }

  // 2023-04-01 jj5 - NOTE: the telemetry might be considered sensitive data... but probably not
  // so sensitive that it can't be logged. It's not at the same level as secrets or passphrases.
  //
  public static function GetTelemetry() {

    return self::$telemetry;

  }

  public static function ReportTelemetry() {

    $telemetry = self::GetTelemetry();

    echo "= Functions =\n\n";

    self::ReportCounters( $telemetry[ 'function' ] );

    echo "\n= Classes =\n\n";

    self::ReportCounters( $telemetry[ 'class' ] );

    echo "\n= Lengths =\n\n";

    self::ReportCounters( $telemetry[ 'length' ] );

  }

  public static function ReportCounters( $table ) {

    $table_formatted = [];
    $key_max_len = 0;
    $count_max_len = 0;

    foreach ( $table as $key => $count ) {

      $formatted = number_format( $count );

      $key_max_len = max( strlen( $key ), $key_max_len );
      $count_max_len = max( strlen( $formatted ), $count_max_len );

      $table_formatted[ $key ] = $formatted;

    }

    $key_pad = $key_max_len + 2;
    $count_pad = $count_max_len;

    foreach ( $table_formatted as $key => $count ) {

      echo str_pad( $key, $key_pad, '.' );
      echo ': ';
      echo str_pad( $count, $count_pad, ' ', STR_PAD_LEFT );
      echo "\n";

    }
  }

  public final function encrypt( $input ) {

    try {

      $this->inject_delay = true;

      $this->count_function( __FUNCTION__ );

      $result = $this->do_encrypt( $input );

      if ( is_string( $result ) ) {

        $this->count_length( strlen( $result ) );

      }

      return $result;

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error(
        KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3,
        [
          'ex' => $ex,
        ]
      );

    }
  }

  public final function decrypt( string $ciphertext ) {

    try {

      $this->inject_delay = true;

      $this->count_function( __FUNCTION__ );

      return $this->do_decrypt( $ciphertext );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error(
        KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4,
        [
          'ex' => $ex,
        ]
      );

    }
  }

  public final function delay() {

    try {

      $this->count_function( __FUNCTION__ );

      // 2023-04-02 jj5 - we time the do_delay() implementation and if it doesn't meed the
      // minimum requirement we do the emergency delay.

      $start = microtime( $as_float = true );

      $result = $this->do_delay();

      $duration = microtime( $as_float = true ) - $start;

      if ( $duration < KICKASS_CRYPTO_DELAY_SECONDS_MIN ) {

        $this->emergency_delay();

      }

      return $result;

    }
    catch ( Throwable $ex ) {

      try {

        // 2023-04-01 jj5 - it's important to do things in this order, in case something throws...

        // 2023-04-02 jj5 - in order to "fail safe" we inject this emergency delay immediately
        // so that nothing can accidentally interfere with it happening...
        //
        $this->emergency_delay();

        $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5 );

        $this->catch( $ex );

      }
      catch ( Throwable $ignore ) { ; }

      return false;

    }
  }

  // 2023-04-01 jj5 - the point of catch() is simply to notify that an exception has been caught
  // and "handled"; sometimes "handling" the exception is tantamount to ignoring it, so we call
  // this method that we may make some noise about it (during debugging, usually). See do_catch()
  // for the rest of the story.
  //
  protected final function catch( $ex ) {

    try {

      $this->count_function( __FUNCTION__ );

      return $this->do_catch( $ex );

    }
    catch ( Throwable $ex ) {

      // 2023-04-01 jj5 - this function is called from exception handlers, and then notifies
      // impementations via the do_catch() method, as above. We don't trust implementations not
      // to throw, and as we're presently *in* an exception handler, we don't want to throw
      // another exception, because code might not be set up to accommodate that. So if we
      // land here do_catch() above (or count_function()?) has thrown, so just log and ignore.

      // 2023-04-03 jj5 - note that here we call the PHP error directly so no one has a chance
      // to interfere with this message being logged. It should never happen and if it does we
      // want to give ourselves our best chance of finding out about it so we can address.

      try { error_log( __FILE__ . ': ' . $ex->getMessage() ); } catch ( Throwable $ignore ) { ; }

    }
  }

  protected final function throw( int $code, $data = null, $previous = null ) {

    $this->count_function( __FUNCTION__ );

    return $this->do_throw( $code, $data, $previous );

  }

  protected final function error( $error ) {

    try {

      // 2023-04-02 jj5 - the very first thing we do is inject our delay so we can make sure that
      // happens...
      //
      if ( $this->inject_delay ) {

        $this->delay();

        $this->inject_delay = false;

      }

      $this->count_function( __FUNCTION__ );

      $this->do_error( $error );

      // 2023-04-02 jj5 - this function must always return false. We don't give implementations
      // the option to make a mistake about that.
      //
      return false;

    }
    catch ( Throwable $ex ) {

      // 2023-04-01 jj5 - the whole point of this function is to *not* throw an exception. Neither
      // delay(), count_function() or do_error() has any business throwing an exception. If they
      // do we make some noise in the log file and return false. Note that we call the PHP
      // error log function here directly because we don't want to make sure this message which
      // should never happen is visible.

      try { error_log( __FILE__ . ': ' . $ex->getMessage() ); } catch ( Throwable $ignore ) { ; }

    }

    return false;

  }

  // 2023-04-01 jj5 - implementations can vary this behavior. By default we don't count extensions
  // of KickassCryptoRoundTrip or KickassCryptoAtRest separately, but we do count other extensions
  // separately...
  //
  protected function count_this( $caller ) {

    $this->count_function( $caller );

    if ( is_a( $this, KickassCryptoRoundTrip::class ) ) {

      $this->count_class( KickassCryptoRoundTrip::class );

    }
    else if ( is_a( $this, KickassCryptoAtRest::class ) ) {

      $this->count_class( KickassCryptoAtRest::class );

    }
    else {

      $this->count_class( get_class( $this ) );

    }
  }

  protected function count_function( $metric ) {

    return $this->increment_counter( self::$telemetry[ 'function' ], $metric );

  }

  protected function count_class( $class ) {

    return $this->increment_counter( self::$telemetry[ 'class' ], $class );

  }

  protected function count_length( int $length ) {

    return $this->increment_counter( self::$telemetry[ 'length' ], $length );

  }

  protected function increment_counter( &$array, $key ) {

    if ( ! array_key_exists( $key, $array ) ) {

      $array[ $key ] = 0;

    }

    $array[ $key ]++;

    return $array[ $key ];

  }

  protected function get_config_secret_curr() {

    return $this->get_const( 'CONFIG_ENCRYPTION_SECRET_CURR' );

  }

  protected function get_config_secret_prev() {

    return $this->get_const( 'CONFIG_ENCRYPTION_SECRET_PREV' );

  }

  protected function get_config_secret_list() {

    return $this->get_const( 'CONFIG_ENCRYPTION_SECRET_LIST' );

  }

  protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE', $default );

  }

  protected function get_config_chunk_size_max(
    $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX
  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE_MAX', $default );

  }

  protected function get_config_json_length_max(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_MAX
  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_LENGTH_MAX', $default );

  }

  protected function get_config_json_encode_options(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS
  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', $default );

  }

  protected function get_config_json_decode_options(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS
  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', $default );

  }

  protected function get_const_data_format_version() {

    return $this->get_const( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION' );

  }

  protected function get_const_key_hash() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_HASH' );

  }

  protected function get_const_cipher() {

    return $this->get_const( 'KICKASS_CRYPTO_CIPHER' );

  }

  protected function get_const_options() {

    return $this->get_const( 'KICKASS_CRYPTO_OPTIONS' );

  }

  protected function get_const_key_length_min() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_LENGTH_MIN' );

  }

  protected function get_const_passphrase_length() {

    return $this->get_const( 'KICKASS_CRYPTO_PASSPHRASE_LENGTH' );

  }

  protected function get_const_iv_length() {

    return $this->get_const( 'KICKASS_CRYPTO_IV_LENGTH' );

  }

  protected function get_const_tag_length() {

    return $this->get_const( 'KICKASS_CRYPTO_TAG_LENGTH' );

  }

  protected function get_const( $const, $default = false ) {

    return defined( $const ) ? constant( $const ) : $default;

  }

  protected function get_encryption_passphrase() {

    return $this->get_passphrase_list()[ 0 ] ?? false;

  }

  protected function is_cli() {

    return $this->php_sapi_name() === 'cli';

  }

  protected function is_debug() {

    return defined( 'DEBUG' ) && DEBUG;

  }

  protected function is_valid_secret( $secret ) {

    if ( ! is_string( $secret ) ) { return false; }

    if ( strlen( $secret ) < $this->get_const_key_length_min() ) { return false; }

    return true;

  }

  protected function is_valid_base64( $input ) {

    if ( empty( $input ) ) { return false; }

    if ( ! is_string( $input ) ) { return false; }

    if ( preg_match( KICKASS_CRYPTO_REGEX_BASE64, $input ) ) { return true; }

    return false;

  }

  public function get_error_list() {

    return $this->error_list;

  }

  public function get_error() {

    $count = count( $this->error_list );

    if ( $count === 0 ) { return null; }

    return $this->error_list[ $count - 1 ];

  }

  public function get_openssl_error() {

    return $this->openssl_error;

  }

  public function clear_error() {

    $this->error_list = [];
    $this->openssl_error = null;

  }

  protected function do_encrypt( $input ) {

    if ( $input === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE );

    }

    $json = $this->json_encode( $input );

    if ( $json === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

    }

    $json_length = strlen( $json );

    if ( $json_length > $this->get_config_json_length_max() ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE,
        [
          'json_length' => $json_length,
          'json_length_max' => $this->get_config_json_length_max(),
        ]
      );

    }

    $passphrase = $this->get_encryption_passphrase();

    if ( ! $passphrase ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE );

    }

    $passphrase_length = strlen( $passphrase );

    if ( $passphrase_length !== $this->get_const_passphrase_length() ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH,
        [
          'passphrase_length' => $passphrase_length,
          'passphrase_length_required' => $this->get_const_passphrase_length(),
        ]
      );

    }

    $chunk_size = $this->get_config_chunk_size();

    if (
      ! is_int( $chunk_size ) ||
      $chunk_size <= 0 ||
      $chunk_size > $this->get_config_chunk_size_max()
    ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
        [
          'chunk_size' => $chunk_size,
          'chunk_size_max' => $this->get_config_chunk_size_max(),
        ]
      );

    }

    $pad_length = $chunk_size - ( $json_length % $chunk_size );

    assert( $pad_length <= $chunk_size );

    // 2023-04-01 jj5 - we format as hex like this so it's always the same length...
    //
    $hex_json_length = sprintf( '%08x', $json_length );

    $message = $hex_json_length . '|' . $json . $this->get_padding( $pad_length );

    $ciphertext = $this->encrypt_string( $message, $passphrase );

    if ( $ciphertext === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

    }

    $encoded = $this->encode( $ciphertext );

    return $encoded;

  }

  protected final function encrypt_string( string $plaintext, string $passphrase ) {

    return $this->do_encrypt_string( $plaintext, $passphrase );

  }

  protected function do_encrypt_string( string $plaintext, string $passphrase ) {

    $iv = $this->php_random_bytes( $this->get_const_iv_length() );

    if ( strlen( $iv ) !== $this->get_const_iv_length() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH );

    }

    $cipher = $this->get_const_cipher();
    $options = $this->get_const_options();

    $ciphertext = false;

    try {

      $ciphertext = $this->php_openssl_encrypt(
        $plaintext, $cipher, $passphrase, $options, $iv, $tag
      );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED );

    }

    if ( strlen( $tag ) !== $this->get_const_tag_length() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH );

    }

    if ( ! $ciphertext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2 );

    }

    // 2023-04-02 jj5 - apparently it's traditional to format these items in this order...

    return $iv . $ciphertext . $tag;

  }

  protected function do_decrypt( string $ciphertext ) {

    $error = KICKASS_CRYPTO_ERROR_NO_VALID_KEY;

    $binary = $this->decode( $ciphertext );

    if ( $binary === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT );

    }

    foreach ( $this->get_passphrase_list() as $passphrase ) {

      if ( strlen( $passphrase ) !== $this->get_const_passphrase_length() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2 );

      }

      $json = $this->try_decrypt( $binary, $passphrase );

      if ( $json === false ) { continue; }

      $result = $this->json_decode( $json );

      if ( $result !== false ) { return $result; }

      // 2023-04-02 jj5 - if we make it this far during any of our decryption attempts then this
      // is the error we will return.

      $error = KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED;

    }

    // 2023-04-02 jj5 - the $error here will be one of:
    //
    //* KICKASS_CRYPTO_ERROR_NO_VALID_KEY
    //* KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED

    return $this->error( $error );

  }

  protected final function try_decrypt( string $binary, string $passphrase ) {

    $message = $this->decrypt_string( $binary, $passphrase );

    if ( $message === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

    }

    return $this->decode_message( $message );

  }

  protected final function decrypt_string( string $binary, string $passphrase ) {

    return $this->do_decrypt_string( $binary, $passphrase );

  }

  protected function do_decrypt_string( string $binary, string $passphrase ) {

    if ( ! $this->parse_binary( $binary, $iv, $ciphertext, $tag ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA );

    }

    $cipher = $this->get_const_cipher();
    $options = $this->get_const_options();

    $plaintext = false;

    try {

      $plaintext = $this->php_openssl_decrypt(
        $ciphertext, $cipher, $passphrase, $options, $iv, $tag
      );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2 );

    }

    if ( ! $plaintext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2 );

    }

    return $plaintext;

  }

  protected final function decode_message( string $message ) {

    return $this->do_decode_message( $message );

  }

  protected function do_decode_message( string $message ) {

    // 2023-04-02 jj5 - this function decodes a message, which is:
    //
    // $json_length . '|' . $json . $random_padding
    //
    // 2023-04-02 jj5 - this function will read the data length and then extract the JSON. This
    // function doesn't validate the JSON.

    // 2023-04-02 jj5 - NOTE: this limit of 2 GiB worth of JSON is just a heuristic for this
    // part of the code; the data can't actually be this long, but other parts of the code will
    // make sure of that.
    //
    static $max_json_length = 2_147_483_647;

    assert( hexdec( '7fffffff' ) === $max_json_length );

    $parts = explode( '|', $message, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT );

    }

    $json_length_string = $parts[ 0 ];

    if ( strlen( $json_length_string ) !== 8 ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_SPEC,
        [
          'json_length_string' => $json_length_string,
        ]
      );

    }

    $json_length = hexdec( $json_length_string );

    if (
      ! is_int( $json_length ) ||
      $json_length <= 0 ||
      $json_length > $max_json_length
    ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE,
        [
          'json_length' => $json_length,
        ]
      );

    }

    // 2023-04-02 jj5 - the binary data is the JSON with the random padding after it. So take
    // the JSON from the beginning of the string, ignore the padding, and return the JSON.

    $binary = $parts[ 1 ];

    if ( $json_length > strlen( $binary ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_LENGTH );

    }

    $json = substr( $binary, 0, $json_length );

    return $json;

  }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN
  ) {

    $this->log_error( 'delayed due to error...' );

    $this->get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

    assert( is_int( $seconds ) );
    assert( $seconds >= 0 );
    assert( is_int( $nanoseconds ) );
    assert( $nanoseconds < 1_000_000_000 );

    return $this->php_time_nanosleep( $seconds, $nanoseconds );

  }

  protected final function emergency_delay() {

    // 2023-03-30 jj5 - ordinarily do_delay() does our delay, but there are a bunch of ways that
    // could go wrong. If do_delay() throws we make a sincere effort to call this function,
    // which endeavors to "fail safe". In this case failing safe means ensuring that there is
    // some delay. This code tries very hard to make sure there's some sort of random delay...

    try {

      if (
        defined( 'KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP' ) &&
        KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP
      ) {

        throw new Exception(
          'test running: KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP'
        );

      }

      $ns_min =      1_000_000;
      $ns_max = 10_000_000_000;

      $delay = random_int( $ns_min, $ns_max );

      $seconds = intval( round( $delay / 1_000_000_000 ) );
      $nanoseconds = $delay % 1_000_000_000;

      $result = time_nanosleep( $seconds, $nanoseconds );

      if ( $result ) {

        return $this->report_emergency_delay( 'nanosleep' );

      }

      // 2023-04-02 jj5 - otherwise we fall through to the usleep() fallback below...

    }
    catch ( Throwable $ex ) {

      try { $this->catch( $ex ); } catch ( Throwable $ignore ) { ; }

    }

    usleep( random_int( 1_000, 10_000_000 ) );

    return $this->report_emergency_delay( 'microsleep' );

  }

  private function report_emergency_delay( string $type ) {

    try {

      return $this->log_error( 'emergency delay: ' . $type );

    }
    catch ( Throwable $ex ) {

      try { $this->catch( $ex ); } catch ( Throwable $ignore ) { ; }

    }
  }

  // 2023-04-01 jj5 - implementations can decide what to do when errors are handled. By default
  // we write a log entry when debugging is enabled. It would probably be reasonable to log this
  // even in production.
  //
  protected function do_catch( $ex ) {

    $this->log_error( 'caught exception: ' . $ex->getMessage() );

  }

  protected function do_throw( int $code, $data = null, $previous = null ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE );

    }

    $this->log_error( 'exception: ' . $message );

    throw new KickassException( $message, $code, $previous, $data );

  }

  protected function do_error( $error ) {

    $this->error_list[] = $error;

    while ( $openssl_error = $this->php_openssl_error_string() ) {

      $this->openssl_error = $openssl_error;

    }

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  protected final function json_encode( $input ) {

    try {

      return $this->do_json_encode( $input );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

    }
  }

  protected function do_json_encode( $input ) {

    try {

      $options = $this->get_config_json_encode_options();

      $result = $this->php_json_encode( $input, $options );

      if ( $result === false ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

      }

      return $result;

    }
    catch ( JsonException $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

    }
  }

  protected final function json_decode( $json ) {

    try {

      return $this->do_json_decode( $json );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

    }
  }

  protected function do_json_decode( string $json ) {

    try {

      $options = $this->get_config_json_decode_options();

      $result = $this->php_json_decode( $json, $assoc = true, 512, $options );

      if ( $result === false ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

      }

      return $result;

    }
    catch ( JsonException $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

    }
  }

  protected final function encode( string $binary ) {

    return $this->do_encode( $binary );

  }

  protected function do_encode( string $binary ) {

    return $this->get_const_data_format_version() . '/' . $this->php_base64_encode( $binary );

  }

  protected final function decode( string $encoded ) {

    return $this->do_decode( $encoded );

  }

  protected function do_decode( string $encoded ) {

    $parts = explode( '/', $encoded, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_ENCODING );

    }

    if ( $parts[ 0 ] !== $this->get_const_data_format_version() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING );

    }

    // 2023-04-01 jj5 - OLD: we don't do this any more, if base64 decoding fails we can
    // surmise that the encoding was not valid, there's not much point doing validation in
    // advance, especially as the normal case is that the encoding is valid.
    //
    /*
    if ( ! $this->is_valid_base64( $parts[ 1 ] ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING );

    }
    */

    $result = $this->php_base64_decode( $parts[ 1 ], $strict = true );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED );

    }

    // 2023-04-01 jj5 - NOTE: but we did have to include this extra check for empty because
    // it's not always false which is returned... actually empty() is probably stronger than
    // required, a simplye $result !== '' would probably do, but this should be fine...
    //
    if ( empty( $result ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED );

    }

    return $result;

  }

  protected function calc_passphrase( string $key ) {

    return hash( $this->get_const_key_hash(), $key, $binary = true );

  }

  protected function parse_binary( $binary, &$iv, &$ciphertext, &$tag ) {

    // 2023-04-02 jj5 - the binary data is: IV + ciphertext + tag; the IV and tag are fixed length

    $iv = false;
    $ciphertext = false;
    $tag = false;

    $binary_length = strlen( $binary );

    $iv_length = $this->get_const_iv_length();
    $tag_length = $this->get_const_tag_length();
    $ciphertext_length = $binary_length - $iv_length - $tag_length;

    $min_length = $iv_length + 1 + $tag_length;

    // 2023-04-02 jj5 - NOTE: this test obviates the possibility of the latter tests failing, but
    // I left them in anyway, just in case a bug is introduced into this part of the function...
    //
    if ( $binary_length < $min_length ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH );

    }

    $iv = substr( $binary, 0, $iv_length );

    if ( strlen( $iv ) !== $iv_length ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2 );

    }

    $ciphertext = substr( $binary, $iv_length, $ciphertext_length );

    if ( ! is_string( $ciphertext ) || $ciphertext === '' ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2 );

    }

    $tag = substr( $binary, $iv_length + $ciphertext_length );

    if ( strlen( $tag ) !== $tag_length ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2,
        [
          'tag_len' => strlen( $tag ),
          'expected_tag_len' => $tag_length,
        ]
      );

    }

    return true;

  }

  protected function get_padding( int $length ) {

    return $this->php_random_bytes( $length );

    // 2023-04-01 jj5 - the following is also an option, and might be faster..?
    //
    //return str_repeat( "\0", $length );

  }

  protected function get_delay(
    int $ns_min,
    int $ns_max,
    &$seconds,
    &$nanoseconds
  ) {

    assert( $ns_min >= 0 );
    assert( $ns_max >= 0 );
    assert( $ns_max >= $ns_min );

    $delay = $this->php_random_int( $ns_min, $ns_max );

    assert( is_int( $delay ) );
    assert( $delay >= $ns_min );
    assert( $delay <= $ns_max );

    $seconds = intval( round( $delay / 1_000_000_000 ) );
    $nanoseconds = $delay % 1_000_000_000;

    assert( is_int( $seconds ) );
    assert( $seconds >= 0 );
    assert( is_int( $nanoseconds ) );
    assert( $nanoseconds >= ( $ns_min % 1_000_000_000 ) );
    assert( $nanoseconds < 1_000_000_000 );

  }

  protected final function log_error( $message ) {

    try {

      return $this->do_log_error( $message );

    }
    catch ( Throwable $ex ) {

      try { $this->catch( $ex ); } catch ( Throwable $ignore ) { ; }

      return false;

    }
  }

  protected function do_log_error( $message ) {

    if (
      defined( 'KICKASS_CRYPTO_DISABLE_LOG' ) &&
      KICKASS_CRYPTO_DISABLE_LOG
    ) {

      return false;

    }

    return error_log( __FILE__ . ': ' . $message );

  }
}

// 2023-03-30 jj5 - if you need to round trip data from the web server to the client and back
// again via hidden HTML form <input> tags use this KickassCryptoRoundTrip class. This class uses
// one or two secret keys from the config file. The first key is required and it's called the
// "current" key, its config option is 'CONFIG_ENCRYPTION_SECRET_CURR'; the second key is
// option and it's called the "previous" key, its config option is 'CONFIG_ENCRYPTION_SECRET_PREV'.

class KickassCryptoRoundTrip extends KickassCrypto {

  protected function is_valid_config( &$problem = null ) {

    $secret_curr = $this->get_config_secret_curr();
    $secret_prev = $this->get_config_secret_prev();

    if ( ! $secret_curr ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_CURR;

      return false;

    }

    if ( ! $this->is_valid_secret( $secret_curr ) ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_CURR;

      return false;

    }

    if ( $secret_prev && ! $this->is_valid_secret( $secret_prev ) ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_PREV;

      return false;

    }

    $problem = null;

    return true;

  }

  protected function get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result !== null ) { return $result; }

    $secret_curr = $this->get_config_secret_curr();
    $secret_prev = $this->get_config_secret_prev();

    $result = [ $this->calc_passphrase( $secret_curr ) ];

    if ( $secret_prev ) {

      $result[] = $this->calc_passphrase( $secret_prev );

    }

    return $result;

  }
}

class KickassCryptoAtRest extends KickassCrypto {

  protected function is_valid_config( &$problem = null ) {

    $secret_list = $this->get_config_secret_list();

    if ( $secret_list === false ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_LIST;

      return false;

    }

    if ( ! is_array( $secret_list ) || count( $secret_list ) === 0 ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST;

      return false;

    }

    foreach ( $secret_list as $secret ) {

      if ( ! $this->is_valid_secret( $secret ) ) {

        $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST;

        return false;

      }
    }

    $problem = null;

    return true;

  }

  protected function get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result !== null ) { return $result; }

    $secret_list = $this->get_config_secret_list();
    $result = [];

    foreach ( $secret_list as $secret ) {

      $result[] = $this->calc_passphrase( $secret );

    }

    return $result;

  }
}
