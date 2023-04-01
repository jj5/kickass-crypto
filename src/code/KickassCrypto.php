<?php

// 2023-03-30 jj5 - this is the Kickass Crypto library, if you want to use the library this is the
// only file that you need to include, but other goodies ship with the project.
//
// 2023-03-31 jj5 - SEE: https://github.com/jj5/kickass-crypto
//
// 2023-03-30 jj5 - make sure you load a valid config.php file, then encrypt like this:
//
//   $ciphertext = kickass_round_trip()->encrypt( 'secret data' );
//
// and decrypt like this:
//
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

(function() {

  // 2023-03-31 jj5 - this anonymous function is for validating our run-time environment. If
  // there's a problem we exit, unless the programmer has overridden that behavior by defining
  // certain constants. Read the code in this function for details.
  //
  // 2023-03-31 jj5 - to override PHP version requirements:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true );
  //
  // 2023-03-31 jj5 - to override PHP 64-bit word size requirements:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true );
  //
  // 2023-04-01 jj5 - to override checks for the OpenSSL library functions:
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

      $message = __FILE__ . ": $error";

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

// 2023-03-30 jj5 - these two service locator functions will automatically create appropriate
// encryption components for each use case. If you want to override with a different
// implementation you can pass in a new instance, or you can manage construction yourself and
// access some other way.

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

// 2023-03-30 jj5 - this is the current data format version for this library. If you fork this
// library and alter the data format you should change this. If you do change this please use
// something other than 'KA' as the prefix. If you don't want the data format version reported
// in your encoded data override the encode() and decode() methods.
//
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION', 'KA0' );

// 2023-03-30 jj5 - these are the default values for configuration... these might be changed in
// future...
//
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', 4096 );
define( 'KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_LIMIT', pow( 2, 26 ) );
define( 'KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
define( 'KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );

// 2023-03-29 jj5 - these delays are in nanoseconds, these might be changed in future...
//
define( 'KICKASS_CRYPTO_DELAY_NS_MIN',      1_000_000 );
define( 'KICKASS_CRYPTO_DELAY_NS_MAX', 10_000_000_000 );

// 2023-03-30 jj5 - this is our Base64 validation regex...
//
define(
  'KICKASS_CRYPTO_REGEX_BASE64',
  '/^[a-zA-Z0-9\/+]{2,}={0,2}$/'
  //'/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/'
);

// 2023-03-29 jj5 - exceptions are thrown from the constructor only, these are the possible
// exceptions. The exception codes should be stable, you can add new ones but don't change
// existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE',  1_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG',          2_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_SECRET_HASH',     3_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER',          4_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH',       5_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM',         6_000 );

// 2023-03-30 jj5 - these are the exception messages for each exception code. These exception
// messages should be stable, you can add new ones but don't change existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_MESSAGE', [
  KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE => 'invalid exception code.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG         => 'invalid config.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_SECRET_HASH    => 'invalid secret hash.',
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
// false is returned and the error constant is added to the error list.
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
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH', 'invalid IV length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2', 'invalid IV length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH', 'invalid tag length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2', 'invalid tag length (2).' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT', 'invalid ciphertext.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2', 'invalid ciphertext (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_DATA', 'invalid data.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PARTS', 'invalid parts.' );
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
define( 'KICKASS_CRYPTO_SECRET_HASH', 'sha512/256' );
define( 'KICKASS_CRYPTO_CIPHER', 'aes-256-gcm' );
define( 'KICKASS_CRYPTO_OPTIONS', OPENSSL_RAW_DATA );
define( 'KICKASS_CRYPTO_KEYMINLEN', 88 );
define( 'KICKASS_CRYPTO_PPLEN', 32 );
define( 'KICKASS_CRYPTO_IVLEN', 12 );
define( 'KICKASS_CRYPTO_TAGLEN', 16 );

// 2023-03-30 jj5 - we define an exception class for this component so that we can associate
// custom data with our exceptions...
//
class KickassException extends Exception {

  private $data;

  public function __construct( $message, $code = 0, $previous = null, $data = null ) {

    parent::__construct( $message, $code, $previous );

    $this->data = $data;

  }

  public function getData() { return $this->data; }

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
// as the library functions you are calling use, or just don't provide a default value at all,
// that's a sensible enough option, you can make the wrapper demand a value from the caller if
// you want.
//
trait PHP_WRAPPER {

  protected function php_base64_encode( $input ) {

    return base64_encode( $input );

  }

  protected function php_base64_decode( $input, $strict ) {

    return base64_decode( $input, $strict );

  }

  protected function php_json_encode( $value, $flags, $depth = 512 ) {

    return json_encode( $value, $flags, $depth );

  }

  protected function php_json_decode( $json, $associative, $depth, $flags ) {

    return json_decode( $json, $associative, $depth, $flags );

  }

  protected function php_serialize( $input ) {

    return serialize( $input );

  }

  protected function php_unserialize( $input ) {

    return unserialize( $input );

  }

  protected function php_random_bytes( $length ) {

    return random_bytes( $length );

  }

  protected function php_openssl_get_cipher_methods() {

    return openssl_get_cipher_methods();

  }

  protected function php_openssl_cipher_iv_length( $cipher ) {

    return openssl_cipher_iv_length( $cipher );

  }

  protected function php_openssl_error_string() {

    return openssl_error_string();

  }

  protected function php_openssl_encrypt(
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

  protected function php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    return openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

  }

  protected function php_time_nanosleep( $seconds, $nanoseconds ) {

    return time_nanosleep( $seconds, $nanoseconds );

  }

  protected function php_random_int( int $min, int $max ) {

    return random_int( $min, $max );

  }

  protected function php_sapi_name() {

    return php_sapi_name();

  }
}

// 2023-03-30 jj5 - the KickassCrypt class is the core of this library, but to use it you need
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
// KickassCryptoRoundTrip or KickassCryptoAtRest instance directly, not the inheriters of them.
// Other inheriters of KickassCrypto are counted separately, which is probably what you want. See
// the count_this() method if you need to change counting behavior.
//
abstract class KickassCrypto {

  use PHP_WRAPPER;

  // 2023-03-30 jj5 - our counters are stored here, call the count() or count_class() methods to
  // increment...
  //
  private static $telemetry = [
    'counter' => [],
    'class' => [],
  ];

  // 2023-03-29 jj5 - our list of errors is private, implementations can override the access
  // interface methods defined below...
  //
  private $error_list = [];

  // 2023-03-30 jj5 - this is for tracking the first openssl error that occurs, if any...
  //
  private $openssl_error = null;

  // 2023-03-30 jj5 - we throw exceptions from the constructor if our environment is invalid... if
  // the constructor succeeds then encryption and decryption should also succeed later on, if
  // encryption or decryption won't be able to succeed the constructor should throw.
  //
  public function __construct() {

    // 2023-03-31 jj5 - NOTE: we count all instances created, even if their constructors end up
    // throwing an exception thus making them unusable.

    $this->count_this();

    if ( ! $this->is_valid_config( $problem ) ) {

      $this->throw(
        KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG,
        [
          'problem' => $problem,
        ]
      );

    }

    assert( $problem === null );

    $secret_hash = $this->get_const_secret_hash();
    $hash_list = hash_algos();

    if ( ! in_array( $secret_hash, $hash_list ) ) {

      $this->throw(
        KICKASS_CRYPTO_EXCEPTION_INVALID_SECRET_HASH,
        [
          'secret_hash' => $secret_hash,
          'hash_list' => $hash_list,
        ]
      );

    }

    $cipher = $this->get_const_cipher();
    $cipher_list = $this->php_openssl_get_cipher_methods();

    if ( ! in_array( $cipher, $cipher_list ) ) {

      $this->throw(
        KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER,
        [
          'cipher' => $cipher,
          'cipher_list' => $cipher_list,
        ]
      );

    }

    $ivlen = $this->php_openssl_cipher_iv_length( $cipher );
    $ivlen_expected = $this->get_const_ivlen();

    if ( $ivlen !== $ivlen_expected ) {

      $this->throw(
        KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH,
        [
          'cipher' => $cipher,
          'ivlen' => $ivlen,
          'ivlen_expected' => $ivlen_expected,
        ]
      );

    }

    try {

      $test_bytes = $this->php_random_bytes( 2 );

    }
    catch ( Random\RandomException $ex ) {

      $this->catch( $ex );

      return $this->throw( KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM );

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

  public static function GetTelemetry() {

    return self::$telemetry;

  }

  public static function ReportTelemetry() {

    $telemetry = self::GetTelemetry();

    echo "= Counters =\n\n";

    self::ReportCounters( $telemetry[ 'counter' ] );

    echo "\n= Classes =\n\n";

    self::ReportCounters( $telemetry[ 'class' ] );

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

      $this->count( __FUNCTION__ );

      return $this->do_encrypt( $input );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3 );

    }
  }

  public final function decrypt( string $ciphertext ) {

    try {

      $this->count( __FUNCTION__ );

      return $this->do_decrypt( $ciphertext );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4 );

    }
  }

  public final function delay() {

    try {

      $this->count( __FUNCTION__ );

      return $this->do_delay();

    }
    catch ( Throwable $ex ) {

      try {

        // 2023-04-01 jj5 - it's important to do things in this order, in case something throws...

        $this->do_delay_emergency();

        $error = KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5;

        $this->error_list[] = $error;

        while ( $openssl_error = $this->php_openssl_error_string() ) {

          $this->openssl_error = $openssl_error;

        }

        $this->catch( $ex );

      }
      catch ( Throwable $dummy ) { ; }

      return false;

    }
  }

  protected final function catch( $ex ) {

    try {

      $this->count( __FUNCTION__ );

      return $this->do_catch( $ex );

    }
    catch ( Throwable $ex ) {

      // 2023-04-01 jj5 - this function is called from exception handlers, and then notifies
      // impementations via the do_catch() method, as above. We don't trust implementations not
      // to throw, and as we're presently *in* an exception handler, we don't want to throw
      // another exception, because code might not be set up to accommodate that. So if we
      // land here do_catch() above (or count()?) has thrown, so just log and ignore.

      try { error_log( __FILE__ . ': ' . $ex->getMessage() ); } catch ( Throwable $dummy ) { ; }

    }
  }

  protected final function throw( int $code, $data = null, $previous = null ) {

    $this->count( __FUNCTION__ );

    return $this->do_throw( $code, $data, $previous );

  }

  protected final function error( $error ) {

    $this->count( __FUNCTION__ );

    return $this->do_error( $error );

  }

  protected function count_this() {

    $this->count( 'instance' );

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

  protected function count( $metric ) {

    return $this->increment_counter( self::$telemetry[ 'counter' ], $metric );

  }

  protected function count_class( $class ) {

    return $this->increment_counter( self::$telemetry[ 'class' ], $class );

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

  protected function get_config_json_length_limit(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_LIMIT
  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_LENGTH_LIMIT', $default );

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

  protected function get_const_secret_hash() {

    return $this->get_const( 'KICKASS_CRYPTO_SECRET_HASH' );

  }

  protected function get_const_cipher() {

    return $this->get_const( 'KICKASS_CRYPTO_CIPHER' );

  }

  protected function get_const_options() {

    return $this->get_const( 'KICKASS_CRYPTO_OPTIONS' );

  }

  protected function get_const_pplen() {

    return $this->get_const( 'KICKASS_CRYPTO_PPLEN' );

  }

  protected function get_const_ivlen() {

    return $this->get_const( 'KICKASS_CRYPTO_IVLEN' );

  }

  protected function get_const_taglen() {

    return $this->get_const( 'KICKASS_CRYPTO_TAGLEN' );

  }

  protected function get_const( $const, $default = false ) {

    return defined( $const ) ? constant( $const ) : $default;

  }

  protected function get_encryption_passphrase() {

    return $this->get_passphrase_list()[ 0 ] ?? false;

  }

  protected function is_debug() {

    return defined( 'DEBUG' ) && DEBUG;

  }

  protected function is_valid_secret( $secret ) {

    if ( strlen( $secret ) < KICKASS_CRYPTO_KEYMINLEN ) { return false; }

    return true;

  }

  protected function is_valid_base64( string $input ) {

    if ( empty( $input ) ) { return false; }

    if ( preg_match( KICKASS_CRYPTO_REGEX_BASE64, $input ) ) { return true; }

    return false;

  }

  public function get_openssl_error() {

    return $this->openssl_error;

  }

  public function get_error() {

    $count = count( $this->error_list );

    if ( $count === 0 ) { return null; }

    return $this->error_list[ $count - 1 ];

  }

  public function get_error_list() {

    return $this->error_list;

  }

  public function clear_error() {

    $this->error_list = [];
    $this->openssl_error = null;

  }

  protected function do_encrypt( $input ) {

    if ( $input === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE );

    }

    $json = $this->data_encode( $input );

    if ( $json === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

    }

    if ( strlen( $json ) > $this->get_config_json_length_limit() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE );

    }

    $passphrase = $this->get_encryption_passphrase();

    if ( ! $passphrase ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE );

    }

    if ( strlen( $passphrase ) !== $this->get_const_pplen() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH );

    }

    $data_length = strlen( $json );

    $chunk_size = $this->get_config_chunk_size();

    assert( is_int( $chunk_size ) );
    assert( $chunk_size > 0 );

    $pad_length = $chunk_size - ( $data_length % $chunk_size );

    $message = $data_length . '|' . $json . $this->get_padding( $pad_length );

    $ciphertext = $this->do_encrypt_string( $message, $passphrase );

    if ( $ciphertext === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

    }

    return $this->encode( $ciphertext );

  }

  protected function get_padding( int $length ) {

    return $this->php_random_bytes( $length );

    // 2023-04-01 jj5 - the following is also an option, and might be faster..?
    //
    //return str_repeat( "\0", $length );

  }

  protected function do_decrypt( string $ciphertext ) {

    $error = KICKASS_CRYPTO_ERROR_NO_VALID_KEY;
    $binary = $this->decode( $ciphertext );

    if ( $binary === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT );

    }

    foreach ( $this->get_passphrase_list() as $passphrase ) {

      if ( strlen( $passphrase ) !== $this->get_const_pplen() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2 );

      }

      $plaintext = $this->try_decrypt( $binary, $passphrase );

      if ( $plaintext === false ) { continue; }

      $result = $this->data_decode( $plaintext );

      if ( $result !== false ) { return $result; }

      $error = KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED;

    }

    return $this->error( $error );

  }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NS_MIN
  ) {

    if ( $this->is_debug() ) {

      error_log( __FILE__ . ': delayed due to error...' );

      //debug_print_backtrace();

    }

    $this->get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

    assert( is_int( $seconds ) );
    assert( $seconds >= 0 );
    assert( is_int( $nanoseconds ) );
    assert( $nanoseconds < 1_000_000_000 );

    return $this->php_time_nanosleep( $seconds, $nanoseconds );

  }

  protected final function do_delay_emergency() {

    // 2023-03-30 jj5 - ordinarily do_delay() does our delay, but there are a bunch of ways that
    // could go wrong. If do_delay() throws we make a sincere effort to call this function,
    // which endeavors to "fail safe". In this case failing safe means ensuring that there is
    // some delay. This code tries very hard to make sure there's some sort of random delay...

    try {

      $ns_min =      1_000_000;
      $ns_max = 10_000_000_000;

      $delay = random_int( $ns_min, $ns_max );

      $seconds = intval( round( $delay / 1_000_000_000 ) );
      $nanoseconds = $delay % 1_000_000_000;

      $result = time_nanosleep( $seconds, $nanoseconds );

      if ( $result ) { return; }

    }
    catch ( Throwable $ex ) {

      try { $this->catch( $ex ); } catch ( Throwable $dummy ) { ; }

    }

    usleep( random_int( 1_000, 10_000_000 ) );

  }

  protected function do_catch( $ex ) {

    if ( $this->is_debug() ) {

      $message = $ex->getMessage();

      error_log( "caught exception: $message" );

    }
  }

  protected function do_throw( int $code, $data = null, $previous = null ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE );

    }

    if ( $this->is_debug() ) {

      error_log( "error: $message" );

    }

    throw new KickassException( $message, $code, $previous, $data );

  }

  protected function do_error( $error ) {

    if ( count( $this->error_list ) === 0 ) {

      $this->delay();

    }

    while ( $openssl_error = $this->php_openssl_error_string() ) {

      $this->openssl_error = $openssl_error;

    }

    $this->error_list[] = $error;

    return false;

  }

  protected function data_encode( $input ) {

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

  protected function verify_encoding( $input, $decoded ) {

    // 2023-04-01 jj5 - NOTE: we don't actually do this. Turns out some things which successfully
    // encode will also successfully decode, but as different things! Notably floats and many
    // objects.

    return;

    if ( $input !== $decoded ) {

      var_dump([
        'input' => $input,
        'decoded' => $decoded,
      ]);

    }

    assert( $input === $decoded );

  }

  protected function data_decode( string $json ) {

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

  protected function encode( string $binary ) {

    return $this->get_const_data_format_version() . '/' . $this->php_base64_encode( $binary );

  }

  protected function decode( string $encoded ) {

    $parts = explode( '/', $encoded, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_ENCODING );

    }

    if ( $parts[ 0 ] !== $this->get_const_data_format_version() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING );

    }

    /*
    if ( ! $this->is_valid_base64( $parts[ 1 ] ) ) {

      var_dump( $parts );

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING );

    }
    */

    $result = $this->php_base64_decode( $parts[ 1 ], $strict = true );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED );

    }

    if ( $result === '' ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED );

    }

    return $result;

  }

  protected function do_encrypt_string( string $plaintext, string $passphrase ) {

    $iv = $this->php_random_bytes( $this->get_const_ivlen() );

    if ( strlen( $iv ) !== $this->get_const_ivlen() ) {

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

    if ( strlen( $tag ) !== $this->get_const_taglen() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH );

    }

    if ( ! $ciphertext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2 );

    }

    return $tag . $iv . $ciphertext;

  }

  protected function try_decrypt( string $binary, string $passphrase ) {

    $message = $this->do_decrypt_string( $binary, $passphrase );

    if ( $message === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

    }

    $parts = explode( '|', $message, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PARTS );

    }

    $length = intval( $parts[ 0 ] );
    $binary = $parts[ 1 ];

    $json = substr( $binary, 0, $length );

    return $json;
  }

  protected function do_decrypt_string( string $binary, string $passphrase ) {

    if ( ! $this->parse_data( $binary, $iv, $tag, $ciphertext ) ) {

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

  protected function calc_passphrase( string $key ) {

    return hash( $this->get_const_secret_hash(), $key, $binary = true );

  }

  protected function parse_data( $binary, &$iv, &$tag, &$ciphertext ) {

    $iv = false;
    $tag = false;
    $ciphertext = false;

    $ivlen = $this->get_const_ivlen();
    $taglen = $this->get_const_taglen();
    $tag = substr( $binary, 0, $taglen );

    if ( strlen( $tag ) !== $taglen ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2,
        [
          'tag_len' => strlen( $tag ),
          'expected_tag_len' => $taglen,
        ]
      );

    }

    $iv = substr( $binary, $taglen, $ivlen );

    if ( strlen( $iv ) !== $ivlen ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2 );

    }

    $ciphertext = substr( $binary, $taglen + $ivlen );

    if ( ! $ciphertext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2 );

    }

    return true;

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

  protected function is_cli() {

    return $this->php_sapi_name() === 'cli';

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
