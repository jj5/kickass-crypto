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
// 2023-03-30 jj5 - this is the Kickass Crypto library, if you want to use it decide whether you
// want the Sodium implementation or the OpenSSL implementation and then include one of:
//
//* inc/sodium.php
//* inc/openssl.php
//
// 2023-04-03 jj5 - if you're not sure, use Sodium.
//
// 2023-03-30 jj5 - make sure you load a valid config.php file, then use this library like this:
//
//   $ciphertext = kickass_round_trip()->encrypt( 'secret data' );
//   $plaintext = kickass_round_trip()->decrypt( $ciphertext );
//
// see README.md for more info.
//
// 2023-03-31 jj5 - SEE: the Kickass Crypto home page: https://github.com/jj5/kickass-crypto
//
\************************************************************************************************/

namespace Kickass\Crypto\Framework;

abstract class KickassCrypto implements \Kickass\Crypto\Contract\IKickassCrypto {

  use \Kickass\Crypto\Traits\KICKASS_WRAPPER_PHP;

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

  // 2023-04-02 jj5 - this flag indicates whether we need to inject a random delay or not, it gets
  // set when a call to either encrypt() or decrypt() is made. It gets set back to false after a
  // delay has been injected so that multiple errors won't trigger multiple delays.
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

    if ( ! KICKASS_CRYPTO_DISABLE_RANDOM_BYTES_VALIDATION ) {

      try {

        $test_bytes = $this->php_random_bytes( 1 );

      }
      catch ( \Exception $ex ) {

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

        return $this->throw( KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM );

      }
    }
  }

  // 2023-03-30 jj5 - implementations need to define what a valid config looks like and provide
  // a list of passphrases. The first passphrase in the list is the one that's used for
  // encryption, others are potentially used for decryption.
  //
  abstract protected function do_is_valid_config( &$problem );
  abstract protected function do_get_passphrase_list();
  abstract protected function do_get_const_data_format_version();
  abstract protected function do_encrypt_string( string $plaintext, string $passphrase );
  abstract protected function do_error( $error );
  abstract protected function do_decrypt_string( string $binary, string $passphrase );
  abstract protected function do_parse_binary( $binary, &$iv, &$ciphertext, &$tag );

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
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

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
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

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

      // 2023-04-02 jj5 - we time the do_delay() implementation and if it doesn't meet the
      // minimum requirement we do the emergency delay.

      $start = microtime( $as_float = true );

      $this->do_delay();

      $duration = microtime( $as_float = true ) - $start;

      if ( $duration < KICKASS_CRYPTO_DELAY_SECONDS_MIN ) {

        $this->emergency_delay();

      }
    }
    catch ( \Throwable $ex ) {

      try {

        // 2023-04-01 jj5 - it's important to do things in this order, in case something throws...

        // 2023-04-02 jj5 - in order to "fail safe" we inject this emergency delay immediately
        // so that nothing can accidentally interfere with it happening...
        //
        $this->emergency_delay();

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }
  }

  // 2023-04-01 jj5 - the point of catch() is simply to notify that an exception has been caught
  // and "handled"; sometimes "handling" the exception is tantamount to ignoring it, so we call
  // this method that we may make some noise about it (during debugging, usually). See do_catch()
  // for the rest of the story.
  //
  protected final function catch( $ex, $file, $line, $function ) {

    try {

      $this->count_function( __FUNCTION__ );

      return $this->do_catch( $ex, $file, $line, $function );

    }
    catch ( \Throwable $ex ) {

      // 2023-04-01 jj5 - this function is called from exception handlers, and then notifies
      // impementations via the do_catch() method, as above. We don't trust implementations not
      // to throw, and as we're presently *in* an exception handler, we don't want to throw
      // another exception, because code might not be set up to accommodate that. So if we
      // land here do_catch() above (or count_function()?) has thrown, so just log and ignore.

      // 2023-04-03 jj5 - note that here we call the PHP error directly so no one has a chance
      // to interfere with this message being logged. It should never happen and if it does we
      // want to give ourselves our best chance of finding out about it so we can address.

      try { error_log( __FILE__ . ': ' . $ex->getMessage() ); } catch ( \Throwable $ignore ) { ; }

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
    catch ( \Throwable $ex ) {

      // 2023-04-01 jj5 - the whole point of this function is to *not* throw an exception. Neither
      // delay(), count_function() or do_error() has any business throwing an exception. If they
      // do we make some noise in the log file and return false. Note that we call the PHP
      // error log function here directly because we don't want to make sure this message which
      // should never happen is visible.

      try { error_log( __FILE__ . ': ' . $ex->getMessage() ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  protected final function count_this( $caller ) {

    return $this->do_count_this( $caller );

  }

  protected function do_count_this( $caller ) {

    $this->count_function( $caller );

    $this->count_class( get_class( $this ) );

  }

  protected final function count_function( $metric ) {

    return $this->do_count_function( $metric );

  }

  protected function do_count_function( $metric ) {

    return $this->increment_counter( self::$telemetry[ 'function' ], $metric );

  }

  protected final function count_class( $class ) {

    return $this->do_count_class( $class );

  }

  protected function do_count_class( $class ) {

    return $this->increment_counter( self::$telemetry[ 'class' ], $class );

  }

  protected final function count_length( int $length ) {

    return $this->do_count_length( $length );

  }

  protected function do_count_length( int $length ) {

    return $this->increment_counter( self::$telemetry[ 'length' ], $length );

  }

  protected final function increment_counter( &$array, $key ) {

    return $this->do_increment_counter( $array, $key );

  }

  protected function do_increment_counter( &$array, $key ) {

    if ( ! array_key_exists( $key, $array ) ) {

      $array[ $key ] = 0;

    }

    $array[ $key ]++;

    return $array[ $key ];

  }

  protected final function get_const_data_format_version() {

    return $this->do_get_const_data_format_version();

  }

  protected final function get_const_key_hash() {

    return $this->do_get_const_key_hash();

  }

  protected function do_get_const_key_hash() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_HASH' );

  }

  protected final function get_const_key_length_min() {

    return $this->do_get_const_key_length_min();

  }

  protected function do_get_const_key_length_min() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_LENGTH_MIN' );

  }

  protected final function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {

    return $this->do_get_config_chunk_size( $default );

  }

  protected function do_get_config_chunk_size( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE', $default );

  }

  protected final function get_config_chunk_size_max(
    $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX
  ) {

    return $this->do_get_config_chunk_size_max( $default );

  }

  protected function do_get_config_chunk_size_max( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE_MAX', $default );

  }

  protected final function get_config_data_length_max(
    $default = KICKASS_CRYPTO_DEFAULT_DATA_LENGTH_MAX
  ) {

    return $this->do_get_config_data_length_max( $default );

  }

  protected function do_get_config_data_length_max( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_DATA_LENGTH_MAX', $default );

  }

  protected final function get_config_data_encoding(
    $default = KICKASS_CRYPTO_DEFAULT_DATA_ENCODING
  ) {

    return $this->do_get_config_data_encoding( $default );

  }

  protected function do_get_config_data_encoding( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_DATA_ENCODING', $default );

  }

  protected final function get_config_json_encode_options(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS
  ) {

    return $this->do_get_config_json_encode_options( $default );

  }

  protected function do_get_config_json_encode_options( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', $default );

  }

  protected final function get_config_json_decode_options(
    $default = KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS
  ) {

    return $this->do_get_config_json_decode_options( $default );

  }

  protected function do_get_config_json_decode_options( $default  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', $default );

  }

  protected final function get_const( $const, $default = false ) {

    return $this->do_get_const( $const, $default );

  }

  protected function do_get_const( $const, $default ) {

    return defined( $const ) ? constant( $const ) : $default;

  }

  protected final function get_passphrase_list() {

    return $this->do_get_passphrase_list();

  }

  protected final function get_encryption_passphrase() {

    return $this->do_get_encryption_passphrase();

  }

  protected function do_get_encryption_passphrase() {

    return $this->get_passphrase_list()[ 0 ] ?? false;

  }

  protected final function is_cli() {

    return $this->do_is_cli();

  }

  protected function do_is_cli() {

    return $this->php_sapi_name() === 'cli';

  }

  protected final function is_debug() {

    return $this->do_is_debug();

  }

  protected function do_is_debug() {

    return defined( 'DEBUG' ) && DEBUG;

  }

  protected final function is_valid_config( &$problem = null ) {

    return $this->do_is_valid_config( $problem );

  }

  protected final function is_valid_secret( $secret ) {

    $is_valid = $this->do_is_valid_secret( $secret );

    if ( $is_valid && strlen( $secret ) < KICKASS_CRYPTO_KEY_LENGTH_MIN ) {

      $this->log_error( 'secret shorter than recommended.', __FILE__, __LINE__, __FUNCTION__ );

    }

    return $is_valid;

  }

  protected function do_is_valid_secret( $secret ) {

    if ( ! is_string( $secret ) ) { return false; }

    if ( strlen( $secret ) < $this->get_const_key_length_min() ) { return false; }

    return true;

  }

  protected final function is_valid_base64( $input ) {

    return $this->do_is_valdi_base64( $input );

  }

  protected function do_is_valid_base64( $input ) {

    if ( empty( $input ) ) { return false; }

    if ( ! is_string( $input ) ) { return false; }

    if ( preg_match( KICKASS_CRYPTO_REGEX_BASE64, $input ) ) { return true; }

    return false;

  }

  protected function do_encrypt( $input ) {

    if ( $input === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE );

    }

    $data_encoding = $this->get_config_data_encoding();

    if ( ! $this->is_valid_data_encoding( $data_encoding ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA_ENCODING );

    }

    $encoded_data = $this->data_encode( $input, $data_encoding );

    if ( $encoded_data === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

    }

    $encoded_data_length = strlen( $encoded_data );

    if ( $encoded_data_length > $this->get_config_data_length_max() ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE,
        [
          'data_length' => $encoded_data_length,
          'data_length_max' => $this->get_config_data_length_max(),
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

    $pad_length = $chunk_size - ( $encoded_data_length % $chunk_size );

    assert( $pad_length <= $chunk_size );

    // 2023-04-01 jj5 - we format as hex like this so it's always the same length...
    //
    $encoded_data_length_hex = sprintf( '%08x', $encoded_data_length );

    $data_encoding = $this->get_data_encoding();

    if ( ! $this->is_valid_data_encoding( $data_encoding ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA_ENCODING );

    }

    $message =
      $encoded_data_length_hex . '|' .
      $data_encoding . '|' .
      $encoded_data .
      $this->get_padding( $pad_length );

    $ciphertext = $this->encrypt_string( $message, $passphrase );

    if ( $ciphertext === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

    }

    $encoded = $this->message_encode( $ciphertext );

    return $encoded;

  }

  protected final function is_valid_data_encoding( $data_encoding ) {

    // 2023-04-04 jj5 - the string length is non-negotiable, we need our padded messsages to
    // always be the same size.
    //
    if ( strlen( $data_encoding ) !== 4 ) { return false; }

    return $this->do_is_valid_data_encoding( $data_encoding );

  }

  protected function do_is_valid_data_encoding( $data_encoding ) {

    switch ( $data_encoding ) {

      case KICKASS_CRYPTO_DATA_ENCODING_JSON :
      case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

        return true;

      default :

        return false;

    }
  }

  protected final function get_data_encoding() {

    return $this->do_get_data_encoding();

  }

  protected function do_get_data_encoding() {

    return $this->get_config_data_encoding();

  }

  protected final function encrypt_string( string $plaintext, string $passphrase ) {

    return $this->do_encrypt_string( $plaintext, $passphrase );

  }

  protected function do_decrypt( string $ciphertext ) {

    $error = KICKASS_CRYPTO_ERROR_NO_VALID_KEY;

    $binary = $this->message_decode( $ciphertext );

    if ( $binary === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT );

    }

    foreach ( $this->get_passphrase_list() as $passphrase ) {

      if ( strlen( $passphrase ) !== $this->get_const_passphrase_length() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2 );

      }

      $encoded_data = $this->try_decrypt( $binary, $passphrase, $data_encoding );

      if ( $encoded_data === false ) { continue; }

      $result = $this->data_decode( $encoded_data, $data_encoding );

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

  protected final function try_decrypt( string $binary, string $passphrase, &$data_encoding = null ) {

    $data_encoding = null;

    $message = $this->decrypt_string( $binary, $passphrase );

    if ( $message === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

    }

    return $this->decode_message( $message, $data_encoding );

  }

  protected final function decrypt_string( string $binary, string $passphrase ) {

    return $this->do_decrypt_string( $binary, $passphrase );

  }

  protected final function parse_binary( $binary, &$iv, &$ciphertext, &$tag ) {

    $iv = false;
    $ciphertext = false;
    $tag = false;

    return $this->do_parse_binary( $binary, $iv, $ciphertext, $tag );

  }

  protected final function decode_message( string $message, &$data_encoding ) {

    return $this->do_decode_message( $message, $data_encoding );

  }

  protected function do_decode_message( string $message, &$data_encoding ) {

    // 2023-04-04 jj5 - this should be null unless its valid.
    //
    $data_encoding = null;

    // 2023-04-02 jj5 - this function decodes a message, which is:
    //
    // $encoded_data_length . '|' . $encoded_data . $random_padding
    //
    // 2023-04-02 jj5 - this function will read the data length and then extract the JSON. This
    // function doesn't validate the JSON.

    // 2023-04-02 jj5 - NOTE: this limit of 2 GiB worth of JSON is just a heuristic for this
    // part of the code; the data can't actually be this long, but other parts of the code will
    // make sure of that.
    //
    static $max_json_length = 2_147_483_647;

    assert( hexdec( '7fffffff' ) === $max_json_length );

    $parts = explode( '|', $message, 3 );

    if ( count( $parts ) !== 3 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT );

    }

    $encoded_data_length_string = $parts[ 0 ];
    $data_encoding_read = $parts[ 1 ];
    $binary = $parts[ 2 ];

    if ( strlen( $encoded_data_length_string ) !== 8 ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_DATA_LENGTH_SPEC,
        [
          'data_length_string' => $encoded_data_length_string,
        ]
      );

    }

    $encoded_data_length = hexdec( $encoded_data_length_string );

    if (
      ! is_int( $encoded_data_length ) ||
      $encoded_data_length <= 0 ||
      $encoded_data_length > $max_json_length
    ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_DATA_LENGTH_RANGE,
        [
          'json_length' => $encoded_data_length,
        ]
      );

    }

    if ( ! $this->is_valid_data_encoding( $data_encoding_read ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA_ENCODING );

    }

    // 2023-04-02 jj5 - the binary data is the JSON with the random padding after it. So take
    // the JSON from the beginning of the string, ignore the padding, and return the JSON.

    if ( $encoded_data_length > strlen( $binary ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_LENGTH );

    }

    $encoded_data = substr( $binary, 0, $encoded_data_length );

    $data_encoding = $data_encoding_read;

    return $encoded_data;

  }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN
  ) {

    $this->log_error( 'delayed due to error...', __FILE__, __LINE__, __FUNCTION__ );

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

        throw new \Exception(
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

        return $this->report_emergency_delay( 'nanosleep', __FILE__, __LINE__, __FUNCTION__ );

      }

      // 2023-04-02 jj5 - otherwise we fall through to the usleep() fallback below...

    }
    catch ( \Throwable $ex ) {

      try {

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    usleep( random_int( 1_000, 10_000_000 ) );

    return $this->report_emergency_delay( 'microsleep', __FILE__, __LINE__, __FUNCTION__ );

  }

  private function report_emergency_delay( string $type, $file, $line, $function ) {

    try {

      return $this->do_report_emergency_delay( $type, $file, $line, $function );

    }
    catch ( \Throwable $ex ) {

      try {

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }
  }

  protected function do_report_emergency_delay( $type, $file, $line, $function ) {

    return $this->log_error( 'emergency delay: ' . $type, $file, $line, $function );

  }

  // 2023-04-01 jj5 - implementations can decide what to do when errors are handled. By default
  // we write a log entry when debugging is enabled. It would probably be reasonable to log this
  // even in production.
  //
  protected function do_catch( $ex, $file, $line, $function ) {

    $this->log_error( 'caught exception: ' . $ex->getMessage(), $file, $line, $function );

  }

  protected function do_throw( int $code, $data = null, $previous = null ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE );

    }

    $this->log_error( 'exception: ' . $message, __FILE__, __LINE__, __FUNCTION__ );

    throw new \Kickass\KickassException( $message, $code, $previous, $data );

  }

  protected final function data_encode( $data, $data_encoding ) {

    try {

      return $this->do_data_encode( $data, $data_encoding );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

    }
  }

  protected function do_data_encode( $data, $data_encoding ) {

    try {

      switch ( $data_encoding ) {

        case KICKASS_CRYPTO_DATA_ENCODING_JSON :

          return $this->encode_json( $data );

        case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

          return $this->encode_phps( $data );

        default :

          return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

      }

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

    }
  }

  protected final function encode_json( $input ) {

    return $this->do_encode_json( $input );

  }

  protected function do_encode_json( $input ) {

    try {

      $options = $this->get_config_json_encode_options();

      $result = $this->php_json_encode( $input, $options );

      if ( $result === false ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

      }

      return $result;

    }
    catch ( \JsonException $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

    }
  }

  protected final function encode_phps( $input ) {

    return $this->do_encode_phps( $input );

  }

  protected function do_encode_phps( $input ) {

    try {

      $result = $this->php_serialize( $input );

      if ( ! $result ) {

        return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED );

      }

      return $result;

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED );

    }
  }

  protected final function data_decode( $encoded_data, $data_encoding = KICKASS_CRYPTO_DATA_ENCODING_JSON ) {

    try {

      return $this->do_data_decode( $encoded_data, $data_encoding );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED );

    }
  }

  protected function do_data_decode( string $encoded_data, $data_encoding ) {

    try {

      switch ( $data_encoding ) {

        case KICKASS_CRYPTO_DATA_ENCODING_JSON :

          return $this->decode_json( $encoded_data );

        case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

          return $this->decode_phps( $encoded_data );

        default :

          return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED );

      }

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED );

    }
  }

  protected final function decode_json( $input ) {

    return $this->do_decode_json( $input );

  }

  protected function do_decode_json( $input ) {

    $options = $this->get_config_json_decode_options();

    $result = $this->php_json_decode( $input, $assoc = true, 512, $options );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

    }

    return $result;

  }

  protected final function decode_phps( $input ) {

    return $this->do_decode_phps( $input );

  }

  protected function do_decode_phps( $input ) {

    $result = $this->php_unserialize( $input );

    if ( $result === false ) {

      return $this->error( KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED );

    }

    return $result;

  }

  protected final function message_encode( string $binary ) {

    return $this->do_message_encode( $binary );

  }

  protected function do_message_encode( string $binary ) {

    return $this->get_data_format_version() . '/' . $this->php_base64_encode( $binary );

  }

  protected final function message_decode( string $encoded ) {

    return $this->do_message_decode( $encoded );

  }

  protected function do_message_decode( string $encoded ) {

    $parts = explode( '/', $encoded, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_ENCODING );

    }

    $data_format_version = $parts[ 0 ];

    if ( $data_format_version !== $this->get_data_format_version() ) {

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

  protected final function get_data_format_version() {

    // 2023-04-04 jj5 - this function just makes sure that only our implementation can use the
    // "KA" data format version prefix. If we're running someone elses code and they don't
    // nominate a new data format version for their own use we just put an 'X' in front of the
    // version so as to avoid it having the same value as used by our canonical implementation.

    $version = $this->get_const_data_format_version();

    $class = get_class( $this );

    switch ( $class ) {

      case 'Kickass\Crypto\Module\OpenSsl\KickassOpenSslRoundTrip' :
      case 'Kickass\Crypto\Module\OpenSsl\KickassOpenSslAtRest' :
      case 'Kickass\Crypto\Module\Sodium\KickassSodiumRoundTrip' :
      case 'Kickass\Crypto\Module\Sodium\KickassSodiumAtRest' :

        return $version;

      default :

        if ( strpos( $version, 'KA' ) !== 0 ) { return $version; }

        return 'X' . $version;

    }
  }

  protected final function convert_secret_to_passphrase( string $key ) {

    return $this->do_convert_secret_to_passphrase( $key );

  }

  protected function do_convert_secret_to_passphrase( string $key ) {

    return hash( $this->get_const_key_hash(), $key, $binary = true );

  }

  protected final function get_padding( int $length ) {

    return $this->do_get_padding( $length );

  }

  protected function do_get_padding( int $length ) {

    return $this->php_random_bytes( $length );

    // 2023-04-01 jj5 - the following is also an option, and might be faster..?
    //
    //return str_repeat( "\0", $length );

  }

  protected final function get_delay( int $ns_min, int $ns_max, &$seconds, &$nanoseconds ) {

    return $this->do_get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

  }

  protected function do_get_delay( int $ns_min, int $ns_max, &$seconds, &$nanoseconds ) {

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

  protected final function log_error( $message, $file, $line, $function ) {

    try {

      return $this->do_log_error( $message, $file, $line, $function );

    }
    catch ( \Throwable $ex ) {

      try {

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

      return false;

    }
  }

  protected function do_log_error( $message, $file, $line, $function ) {

    if ( defined( 'KICKASS_CRYPTO_DISABLE_LOG' ) && KICKASS_CRYPTO_DISABLE_LOG ) {

      return false;

    }

    return error_log( $file . ':' . $line . ': ' . $function . '(): ' . $message );

  }
}
