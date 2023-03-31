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
// For round-trip define these keys:
//
//* CONFIG_ENCRYPTION_SECRET_CURR
//* CONFIG_ENCRYPTION_SECRET_PREV (optional)
//
// For at-rest use case define this list of keys:
//
//* CONFIG_ENCRYPTION_SECRET_LIST
//
// See bin/gen-key.php in this project for key generation.

(function() {

  // 2023-03-31 jj5 - this function is for validating our run-time environment. If there's a
  // problem we exit, unless the programmer has overridden that behavior by defining certain
  // constants. See the code for details.

  $errors = [];

  try {

    if ( version_compare( phpversion(), '7.4', '<' ) ) {

      if ( defined( 'KICKASS_CRYPTO_ENABLE_PHP_VERSION' ) && KICKASS_CRYPTO_ENABLE_PHP_VERSION ) {

        // 2023-03-31 jj5 - the programmer has enabled this version of PHP, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library requires PHP version 7.4 or greater. " .
          "define( 'KICKASS_CRYPTO_ENABLE_PHP_VERSION', true ) to force enablement.";

      }
    }

    if ( strval( PHP_INT_MAX ) !== '9223372036854775807' ) {

      if ( defined( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE' ) && KICKASS_CRYPTO_ENABLE_WORD_SIZE ) {

        // 2023-03-31 jj5 - the programmer has enabled this platform, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library has only been tested on 64-bit platforms. " .
          "define( 'KICKASS_CRYPTO_ENABLE_WORD_SIZE', true ) to force enablement.";

      }
    }

    foreach ( $errors as $error ) {

      if ( defined( 'STDERR' ) ) {

        fwrite( STDERR, "$error\n" );

      }
      else {

        error_log( $error );

      }
    }
  }
  catch ( Throwable $ex ) { ; }

  if ( $errors ) { exit( 100 ); }

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
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION', 'KA1' );

// 2023-03-30 jj5 - these are the default values for configuration... these might be changed in
// future...
//
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', 4096 );
define( 'KICKASS_CRYPTO_DEFAULT_SERIALIZE_LIMIT', pow( 2, 26 ) );

// 2023-03-29 jj5 - these delays are in nanoseconds, these might be changed in future...
//
define( 'KICKASS_CRYPTO_DELAY_NS_MIN',      1_000_000 );
define( 'KICKASS_CRYPTO_DELAY_NS_MAX', 10_000_000_000 );

// 2023-03-30 jj5 - this is our Base64 validation regex...
//
define(
  'KICKASS_CRYPTO_REGEX_BASE64',
  '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/'
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

// 2023-03-30 jj5 - these are the exception messages for each exception code. These exception
// messages should be stable, you can add new ones but don't change existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_MESSAGE', [
  KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE => 'invalid exception code.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG         => 'invalid config.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_SECRET_HASH    => 'invalid secret hash.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER         => 'invalid cipher.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH      => 'invalid IV length.',
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
// don't raise exceptions for these errors because a passphrase might be on the call stack and
// we don't want to accidentally leak it. If an error occurs the boolean value false is
// returned and the error constant is added to the error list.
//
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED', 'exception raised.' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2', 'exception raised (2).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3', 'exception raised (3).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4', 'exception raised (4).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5', 'exception raised (5).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_ENCODING', 'invalid encoding.' );
define( 'KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING', 'unknown encoding.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING', 'invalid base64 encoding.' );
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED', 'base64 decode failed.' );
define( 'KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE', 'cannot encrypt false.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE', 'invalid passphrase.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH', 'invalid passphrase length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2', 'invalid passphrase length (2).' );
define( 'KICKASS_CRYPTO_ERROR_WEAK_RESULT', 'weak result.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH', 'invalid IV length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2', 'invalid IV length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH', 'invalid tag length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2', 'invalid tag length (2).' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_3', 'encryption failed (3).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT', 'invalid ciphertext.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2', 'invalid ciphertext (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_DATA', 'invalid data.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PARTS', 'invalid parts.' );
define( 'KICKASS_CRYPTO_ERROR_SERIALIZE_FAILED', 'serialize failed.' );
define( 'KICKASS_CRYPTO_ERROR_SERIALIZE_TOO_LARGE', 'serialize too large.' );
define( 'KICKASS_CRYPTO_ERROR_UNSERIALIZE_FAILED', 'unserialize failed.' );
define( 'KICKASS_CRYPTO_ERROR_DEFLATE_FAILED', 'deflate failed.' );
define( 'KICKASS_CRYPTO_ERROR_INFLATE_FAILED', 'inflate failed.' );
define( 'KICKASS_CRYPTO_ERROR_NO_VALID_KEY', 'no valid key.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED', 'decryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2', 'decryption failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_3', 'decryption failed (3).' );

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
// is_int(), intval() and round() are called directly and not via these indirections.
//
trait PhpWrappers {

  protected function php_base64_encode( $input ) {

    return base64_encode( $input );

  }

  protected function php_base64_decode( $input ) {

    return base64_decode( $input );

  }

  protected function php_serialize( $input ) {

    return serialize( $input );

  }

  protected function php_unserialize( $input ) {

    return unserialize( $input );

  }

  protected function php_gzdeflate( $buffer ) {

    return gzdeflate( $buffer, 9 );

  }

  protected function php_gzinflate( $buffer ) {

    return gzinflate( $buffer );

  }

  protected function php_openssl_random_pseudo_bytes( $length, &$strong_result ) {

    $strong_result = null;

    return openssl_random_pseudo_bytes( $length, $strong_result );

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
    $plaintext, $cipher, $passphrase, $options, $iv, &$tag
  ) {

    $tag = null;

    return openssl_encrypt(
      $plaintext, $cipher, $passphrase, $options, $iv, $tag
    );

  }

  protected function php_openssl_decrypt(
    $ciphertext, $cipher, $passphrase, $options, $iv, $tag
  ) {

    return openssl_decrypt(
      $ciphertext, $cipher, $passphrase, $options, $iv, $tag
    );

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
// an instance of either KickassCryptoRoundTrip or KickassCryptoAtRest...
//
abstract class KickassCrypto {

  use PhpWrappers;

  // 2023-03-30 jj5 - our counters are stored here, call the count() method to increment...
  //
  private static $telemetry = [];

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

    $this->count( 'instance' );

    if ( is_a( $this, KickassCryptoRoundTrip::class ) ) {

      $this->count( 'round_trip' );

    }
    else if ( is_a( $this, KickassCryptoAtRest::class ) ) {

      $this->count( 'at_rest' );

    }

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

    return base64_encode( openssl_random_pseudo_bytes( 66 ) );

  }

  public static function GetTelemetry() {

    return self::$telemetry;

  }

  public static function ReportTelemetry() {

    $telemetry = self::GetTelemetry();
    $telemetry_formatted = [];
    $key_max_len = 0;
    $count_max_len = 0;

    foreach ( $telemetry as $key => $count ) {

      $formatted = number_format( $count );

      $key_max_len = max( strlen( $key ), $key_max_len );
      $count_max_len = max( strlen( $formatted ), $count_max_len );

      $telemetry_formatted[ $key ] = $formatted;

    }

    $key_pad = $key_max_len + 2;
    $count_pad = $count_max_len;

    foreach ( $telemetry_formatted as $key => $count ) {

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

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3 );

    }
  }

  public final function decrypt( string $ciphertext ) {

    try {

      $this->count( __FUNCTION__ );

      return $this->do_decrypt( $ciphertext );

    }
    catch ( Throwable $ex ) {

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4 );

    }
  }

  public final function delay() {

    try {

      $this->count( __FUNCTION__ );

      return $this->do_delay();

    }
    catch ( Throwable $ex ) {

      $this->do_delay_emergency();

      $error = KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5;

      try {

        $this->error_list[] = $error;

        while ( $openssl_error = $this->php_openssl_error_string() ) {

          $this->openssl_error = $openssl_error;

        }
      }
      catch ( Throwable $dummy ) { ; }

      return false;

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

  protected function count( $metric ) {

    if ( ! array_key_exists( $metric, self::$telemetry ) ) {

      self::$telemetry[ $metric ] = 0;

    }

    self::$telemetry[ $metric ]++;

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

  protected function get_config_serialize_limit( $default = KICKASS_CRYPTO_DEFAULT_SERIALIZE_LIMIT ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_SERIALIZE_LIMIT', $default );

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

    $serialized = $this->php_serialize( $input );

    if ( $serialized === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_SERIALIZE_FAILED );

    }

    if ( strlen( $serialized ) > $this->get_config_serialize_limit() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_SERIALIZE_TOO_LARGE );

    }

    $compressed = $this->php_gzdeflate( $serialized );

    if ( $compressed === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DEFLATE_FAILED );

    }

    $passphrase = $this->get_encryption_passphrase();

    if ( ! $passphrase ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE );

    }

    if ( strlen( $passphrase ) !== $this->get_const_pplen() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH );

    }

    $ciphertext = $this->encrypt_string( $compressed, $passphrase );

    if ( $ciphertext === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

    }

    $data_len = strlen( $ciphertext );

    $chunk_size = $this->get_config_chunk_size();

    assert( is_int( $chunk_size ) );
    assert( $chunk_size > 0 );

    $pad_len = $chunk_size - ( $data_len % $chunk_size );

    $message = $data_len . "|$ciphertext" . str_repeat( "\0", $pad_len );

    $ciphertext = $this->encrypt_string( $message, $passphrase );

    if ( $ciphertext === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2 );

    }

    return $this->encode( $ciphertext );

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

      if ( ! $plaintext ) { continue; }

      $result = $this->php_unserialize( $plaintext );

      if ( $result !== false ) { return $result; }

      $error = KICKASS_CRYPTO_ERROR_UNSERIALIZE_FAILED;

    }

    return $this->error( $error );

  }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NS_MIN
  ) {

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
    catch ( Throwable $ex ) { ; }

    usleep( random_int( 1_000, 10_000_000 ) );

  }

  protected function do_throw( int $code, $data = null, $previous = null ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE );

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

    if ( ! $this->is_valid_base64( $parts[ 1 ] ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING );

    }

    $result = $this->php_base64_decode( $parts[ 1 ] );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED );

    }

    return $result;

  }

  protected function encrypt_string( string $plaintext, string $passphrase ) {

    $iv = $this->php_openssl_random_pseudo_bytes( $this->get_const_ivlen(), $strong_result );

    if ( ! $strong_result ) {

      return $this->error( KICKASS_CRYPTO_ERROR_WEAK_RESULT );

    }

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

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED );

    }

    if ( strlen( $tag ) !== $this->get_const_taglen() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH );

    }

    if ( ! $ciphertext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_3 );

    }

    return $tag . $iv . $ciphertext;

  }

  protected function try_decrypt( string $binary, string $passphrase ) {

    $message = $this->decrypt_string( $binary, $passphrase );

    if ( $message === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

    }

    $parts = explode( '|', $message, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PARTS );

    }

    $length = intval( $parts[ 0 ] );
    $binary = $parts[ 1 ];

    $data = $this->decrypt_string( substr( $binary, 0, $length ), $passphrase );

    if ( $data === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2 );

    }

    $result = $this->php_gzinflate( $data );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INFLATE_FAILED );

    }

    return $result;

  }

  protected function decrypt_string( string $binary, string $passphrase ) {

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

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2 );

    }

    if ( ! $plaintext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_3 );

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
