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
// want the Sodium implementation or the OpenSSL implementation and then include one (or both) of:
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

namespace KickassCrypto\Framework;

/**
 * 2023-04-05 jj5 - this class provides the base framework for a crypto service; you can extend
 * it yourself or use one (or more) of the services implemented as modules in this library.
 */
abstract class KickassCrypto implements \KickassCrypto\Contract\IKickassCrypto {

  use \KickassCrypto\Traits\KICKASS_WRAPPER_PHP;

  /**
   * 2023-03-30 jj5 - our counters are stored here
   *
   * - count_function() will increment the 'function' counter
   * - count_class() will increment the 'class' counter
   * - count_length() will increment the 'length' counter
   *
   * 2023-04-02 jj5 - the function counters count how many times some key functions were called.
   *
   * 2023-04-02 jj5 - the class counters count how many times certain classes were constructed.
   *
   * 2023-04-02 jj5 - the length counter counts the lengths of successfully encrypted data that
   * occur, these should group due to chunking.
   *
   * @var array
   */
  private static $telemetry = [
    'function' => [],
    'class' => [],
    'length' => [],
  ];

  /**
   * 2023-04-02 jj5 - this flag indicates whether we need to inject a random delay or not, it gets
   * set when a call to either encrypt() or decrypt() is made. It gets set back to false after a
   * delay has been injected so that multiple errors won't trigger multiple delays.
   *
   * @var boolean
   */
  private $inject_delay = false;

  /**
   * 2023-03-30 jj5 - we throw exceptions from the constructor if our environment is invalid; if
   * the constructor succeeds then encryption and decryption should also usually succeed later on.
   * If encryption or decryption won't be able to succeed the constructor should throw.
   */
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

  /**
   * 2023-03-30 jj5 - implementations need to define what a valid config looks like and provide a
   * list of passphrases. The first passphrase in the list is the one that's used for encryption,
   * others are potentially used for decryption.
   *
   * @param string|null $problem a description of the problem if invalid, null otherwisej.
   * @return boolean
   */
  abstract protected function do_is_valid_config( &$problem );

  /**
   * 2023-04-05 jj5 - the list of passphrases; the passphrases are generated by using the secret
   * key hashing function on the secret keys defined in the config file.
   *
   * @return array
   */
  abstract protected function do_get_passphrase_list();

  /**
   * 2023-04-05 jj5 - the data format code; this will be prefixed to the base64 encoded ciphertext
   * so we can determine which service we need to decrypt values.
   *
   * @return string
   */
  abstract protected function do_get_const_data_format();

  /**
   * 2023-04-05 jj5 - this will call the encryption library (either OpenSSL or Sodium by default)
   * and do the actual encryption.
   *
   * @param string $plaintext the string to encrypt.
   * @param string $passphrase the passphrase (in binary format, should be 32 bytes).
   * @return string|false returns the encrypted string on success or false on failure.
   */
  abstract protected function do_encrypt_string( string $plaintext, string $passphrase );

  /**
   * 2023-04-05 jj5 - this function will register an error.
   *
   * @param string $error the error which has occurred, should ordinarily be on of the
   * KICKASS_CRYPTO_ERROR_* constants. The return value will be ignored.
   *
   * @return void
   */
  abstract protected function do_error( $error );

  /**
   * 2023-04-05 jj5 - this will call the encryption library (either OpenSSL or Sodium by default)
   * and do the actual decryption.
   *
   * @param string $binary the string to decrypt.
   * @param string $passphrase the passphrase (in binary format, should be 32 bytes).
   * @return string|false returns the decrypted string on success or false on failure.
   */
  abstract protected function do_decrypt_string( string $binary, string $passphrase );

  /**
   * 2023-04-05 jj5 - this function extracts the initialization vector, ciphertext, and tag
   * from the concatenated binary data; not all service define all pieces; the OpenSSL library
   * will use all three; the Sodium library uses the initialization vector for its nonce and it
   * does not use the tag.
   *
   * @return boolean returns true on success; false on failure.
   */
  abstract protected function do_parse_binary( $binary, &$iv, &$ciphertext, &$tag );

  /**
   * 2023-03-30 jj5 - this function will generate a secret key suitable for use in the config
   *  file.
   *
   * @return string an 88 character ASCII string for use as a secret key.
   */
  public static function GenerateSecret() {

    return base64_encode( random_bytes( 66 ) );

  }

  /**
   * 2023-04-01 jj5 - NOTE: the telemetry might be considered sensitive data... but probably not
   * so sensitive that it can't be logged. It's not at the same level as secrets or passphrases.
   *
   * 2023-04-05 jj5 - the telemetry map will contain counters for the following:
   *
   * - function: the functions which have been called and how many times.
   * - class: the classes which have been created and how many times.
   * - length: the encrypted data lengths which have been generated and how many times.
   *
   * @return array the telemetry map.
   */
  public static function GetTelemetry() {

    return self::$telemetry;

  }

  /**
   * 2023-04-05 jj5 - this function will print the telemetry data to STDOUT; it's suitable
   * for use in a console.
   * @return void
   */
  public static function ReportTelemetry() {

    $telemetry = self::GetTelemetry();

    echo "= Functions =\n\n";

    self::ReportCounters( $telemetry[ 'function' ] );

    echo "\n= Classes =\n\n";

    self::ReportCounters( $telemetry[ 'class' ] );

    echo "\n= Lengths =\n\n";

    self::ReportCounters( $telemetry[ 'length' ] );

  }

  /**
   * 2023-04-05 jj5 - this will report a list of counts to STDOUT.
   *
   * @param array $table a key-value list; keys are strings, values are integers.
   */
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

  /**
   * 2023-04-05 jj5 - encrypts the input and returns the ciphertext; returns false on failure.
   *
   * @param mixed $input the complex value you want encrypted.
   * @return string|false the ciphertext on success, or false on failure.
   */
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

  /**
   * 2023-04-05 jj5 - decrypts the ciphertext and returns the deserialized value; returns false on
   * failure.
   *
   * @param string $ciphertext the ciphertext previously returned from the encrypt() method.
   * @return mixed the decrypted and deserialized value, or false on failure.
   */
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

  /**
   * 2023-04-05 jj5 - delays the process for a random amount of time between 1 millisecond and
   * 10 seconds; this is used as a timing attack mitigation, it slows down attackers trying to
   * brute force errors.
   *
   * @return void
   */
  public final function delay() : void {

    try {

      $this->count_function( __FUNCTION__ );

      // 2023-04-02 jj5 - we time the do_delay() implementation and if it doesn't meet the
      // minimum requirement we do the emergency delay.

      $start = microtime( $as_float = true );

      $this->do_delay(
        KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN, KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX
      );

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

  /**
   * 2023-04-01 jj5 - the point of catch() is simply to notify that an exception has been caught
   * and "handled"; sometimes "handling" the exception is tantamount to ignoring it, so we call
   * this method that we may make some noise about it (during debugging, usually). See do_catch()
   * for the rest of the story.
   *
   * @return void
   */
  //
  protected final function catch( $ex, $file, $line, $function ) : void {

    try {

      $this->count_function( __FUNCTION__ );

      $this->do_catch( $ex, $file, $line, $function );

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

  /**
   * 2023-04-05 jj5 - this function throws an exception.
   *
   * @param int $code the exception code of the exception to throw, this is one of the
   * KICKASS_CRYPTO_EXCEPTION_* constants.
   * @param type $data optional data to associate with the exception.
   * @param type $previous the previous exception, if any.
   * @return void
   * @throws KickassCrypto\Framework\KickassCryptoException
   */
  protected final function throw( int $code, $data = null, $previous = null ) : void {

    $this->count_function( __FUNCTION__ );

    $this->do_throw( $code, $data, $previous );

    assert( false );

  }

  /**
   * 2023-04-05 jj5 - registers an error; if this is the first error a random delay is injected
   * as a timing attack mitigation.
   *
   * @param string $error the error description, usually one of the KICKASS_CRYPTO_ERROR_*
   * constants.
   *
   * @return bool always false.
   */
  protected final function error( $error ) : bool {

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

  /**
   * 2023-04-05 jj5 - will count this instance.
   *
   * @param string $caller the name of the invoking function.
   * @return void
   */
  protected final function count_this( $caller ) : void {

    $this->do_count_this( $caller );

  }

  /**
   * 2023-04-05 jj5 - by default will count the caller function (should be the constructor) and
   * this class instance; can be overridden by implementations.
   *
   * @param string $caller the name of the invoking function.
   * @return void
   */
  protected function do_count_this( $caller ) {

    $this->count_function( $caller );

    $this->count_class( get_class( $this ) );

  }

  /**
   * 2023-04-05 jj5 - will increment a function count metric.
   *
   * @param string $function the name of the function.
   * @return int the current count for this function.
   */
  protected final function count_function( $function ) : int {

    return $this->do_count_function( $function );

  }

  /**
   * 2023-04-05 jj5 - by default will increment the counter for this function metric; can be
   * overridden by implementations.
   *
   * @param string $function the name of the function.
   * @return int the current count for the function.
   */
  protected function do_count_function( $function ) {

    return $this->increment_counter( self::$telemetry[ 'function' ], $function );

  }

  /**
   * 2023-04-05 jj5 - will increment a class count metric.
   *
   * @param string $class the name of the class.
   * @return int the current count for the class.
   */
  protected final function count_class( $class ) : int {

    return $this->do_count_class( $class );

  }

  /**
   * 2023-04-05 jj5 - by default will increment the count for this class metric; can be
   * overridden by implementations.
   *
   * @param string $class the name of the class.
   * @return int the current count for the class.
   */
  protected function do_count_class( $class ) {

    return $this->increment_counter( self::$telemetry[ 'class' ], $class );

  }

  /**
   * 2023-04-05 jj5 - will increment a length count metric.
   *
   * @param int $length the length of the encrypted data.
   * @return int the current count for the length.
   */
  protected final function count_length( int $length ) : int {

    return $this->do_count_length( $length );

  }

  /**
   * 2023-04-05 jj5 - by default will increment the count for this length metric; can be
   * overridden by implementations.
   *
   * @param int $length the length of the encrypted data.
   * @return int the current count for the length.
   */
  protected function do_count_length( int $length ) {

    return $this->increment_counter( self::$telemetry[ 'length' ], $length );

  }

  /**
   * 2023-04-05 jj5 - will increment the value for a key in an array; will initialize to zero if
   * missing.
   *
   * @param array $array a reference to the array to operate on.
   * @param string|int $key the key to operate on.
   * @return int the current count.
   */
  protected final function increment_counter( &$array, $key ) : int {

    return $this->do_increment_counter( $array, $key );

  }

  /**
   * 2023-04-05 jj5 - by default will increment the value for a key in an array; will initialize
   * to zero if missing; can be overridden by implementations.
   *
   * @param array $array a reference to the array to operate on.
   * @param string|int $key the key to operate on.
   * @return int the current count.
   */
  protected function do_increment_counter( &$array, $key ) {

    if ( ! array_key_exists( $key, $array ) ) {

      $array[ $key ] = 0;

    }

    $array[ $key ]++;

    return $array[ $key ];

  }

  /**
   * 2023-04-05 jj5 - gets the data format constant; defers to abstract method for implementation.
   *
   * @return string the data format constant.
   */
  protected final function get_const_data_format() : string {

    return $this->do_get_const_data_format();

  }

  /**
   * 2023-04-05 jj5 - gets the name of the hashing algorithm to use for secret key hashing.
   *
   * @return string the name of the PHP hashing algorithm.
   */
  protected final function get_const_key_hash() : string {

    return $this->do_get_const_key_hash();

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the KICKASS_CRYPTO_KEY_HASH constant; can be
   * overridden by implementations.
   *
   * @return string the name of the hash algorithm.
   */
  protected function do_get_const_key_hash() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_HASH' );

  }

  /**
   * 2023-04-05 jj5 - gets the minimum length of a secret key.
   *
   * @return int the minimum length of a secret key.
   */
  protected final function get_const_key_length_min() : int {

    return $this->do_get_const_key_length_min();

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the KICKASS_CRYPTO_KEY_LENGTH_MIN constant;
   * can be overridden by implementations.
   *
   * @return int the minimum length of a secret key.
   */
  protected function do_get_const_key_length_min() {

    return $this->get_const( 'KICKASS_CRYPTO_KEY_LENGTH_MIN' );

  }

  /**
   * 2023-04-05 jj5 - gets the chunk size, it's used for padding encrypted messages.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the chunk size.
   */
  protected final function get_config_chunk_size(
    int $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE
  ) : int {

    return $this->do_get_config_chunk_size( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_CHUNK_SIZE constant or
   * the default value if CONFIG_ENCRYPTION_CHUNK_SIZE is not defined; can be overridden by
   * implementations.
   *
   * @param int $default the default chunk size.
   * @return int the chunk size.
   */
  protected function do_get_config_chunk_size( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE', $default );

  }

  /**
   * 2023-04-05 jj5 - gets the maximum chunk size, it's used for validating the chunk size.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the maximum chunk size.
   */
  protected final function get_config_chunk_size_max(
    int $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX
  ) : int {

    return $this->do_get_config_chunk_size_max( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_CHUNK_SIZE_MAX
   * constant or the default value if CONFIG_ENCRYPTION_CHUNK_SIZE_MAX is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default maximum chunk size.
   * @return int the maximum chunk size.
   */
  protected function do_get_config_chunk_size_max( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE_MAX', $default );

  }

  /**
   * 2023-04-05 jj5 - gets the maximum supported data length, it's used for validating the
   * encoded data.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the maximum supported data length.
   */
  protected final function get_config_data_length_max(
    int $default = KICKASS_CRYPTO_DEFAULT_DATA_LENGTH_MAX
  ) : int {

    return $this->do_get_config_data_length_max( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_DATA_LENGTH_MAX
   * constant or the default value if CONFIG_ENCRYPTION_DATA_LENGTH_MAX is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default maximum data length.
   * @return int the maximum data length.
   */
  protected function do_get_config_data_length_max( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_DATA_LENGTH_MAX', $default );

  }

  /**
   * 2023-04-05 jj5 - gets the data encoding.
   *
   * 2023-04-05 jj5 - data formats which are supported directly are:
   *
   * - KICKASS_CRYPTO_DATA_ENCODING_JSON
   * - KICKASS_CRYPTO_DATA_ENCODING_PHPS
   *
   * 2023-04-05 jj5 - implementations can define their own data formats. Data format codes must
   * be ASCII values make from capital letters and numbers, see is_valid_data_format() for the
   * gory details.
   *
   * @param string $default the value to use if the config option is not defined.
   * @return string the data encoding.
   */
  protected final function get_config_data_encoding(
    string $default = KICKASS_CRYPTO_DEFAULT_DATA_ENCODING
  ) : string {

    return $this->do_get_config_data_encoding( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_DATA_ENCODING constant
   * or the default value if CONFIG_ENCRYPTION_DATA_ENCODING is not defined; can be
   * overridden by implementations.
   *
   * @param string $default the default data format.
   * @return string the data format.
   */
  protected function do_get_config_data_encoding( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_DATA_ENCODING', $default );

  }

  /**
   * 2023-04-05 jj5 - gets the JSON encoding options which will be passed to the PHP json_encode()
   * function.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the JSON encoding options.
   */
  protected final function get_config_json_encode_options(
    int $default = KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS
  ) : int {

    return $this->do_get_config_json_encode_options( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS
   * constant or the default value if CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default JSON encoding options.
   * @return int the JSON encoding options.
   */
  protected function do_get_config_json_encode_options( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', $default );

  }

  /**
   * 2023-04-05 jj5 - gets the JSON decoding options which will be passed to the PHP json_decode()
   * function.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the JSON decoding options.
   */
  protected final function get_config_json_decode_options(
    int $default = KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS
  ) : int {

    return $this->do_get_config_json_decode_options( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS
   * constant or the default value if CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the value to use if the config option is not defined.
   * @return int the JSON decoding options.
   */
  protected function do_get_config_json_decode_options( $default  ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', $default );

  }

  /**
   * 2023-04-05 jj5 - gets a boolean value indicating whether PHPS serialization and
   * deserialization is enabled or not.
   *
   * @param boolean $default the value to use if the config option is not defined.
   * @return boolean true if PHPS serialization and deserialization is enabled, false otherwise.
   */
  protected final function get_config_phps_enable(
    bool $default = KICKASS_CRYPTO_DEFAULT_PHPS_ENABLE
  ) : bool {

    return $this->do_get_config_phps_enable( $default );

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_PHPS_ENABLE constant
   * or the default value if CONFIG_ENCRYPTION_PHPS_ENABLE is not defined; can be overridden by
   * implementations.
   *
   * @param boolean $default the value to use if the config option is not defined.
   * @return boolean true if PHPS serialization and deserialization is enabled, false otherwise.
   */
  protected function do_get_config_phps_enable( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_PHPS_ENABLE', $default );

  }

  protected final function get_config_false_enable(
    bool $default = KICKASS_CRYPTO_DEFAULT_FALSE_ENABLE
  ) : bool {

    return $this->do_get_config_false_enable( $default );

  }

  protected function do_get_config_false_enable( $default ) {

    return $this->get_const( 'CONFIG_ENCRYPTION_FALSE_ENABLE', $default );

  }

  protected final function get_const( $const, $default = false ) {

    return $this->do_get_const( $const, $default );

  }

  protected function do_get_const( $const, $default ) {

    return defined( $const ) ? constant( $const ) : $default;

  }

  protected final function get_passphrase_list() : array {

    return $this->do_get_passphrase_list();

  }

  protected final function get_encryption_passphrase() : string {

    return $this->do_get_encryption_passphrase();

  }

  protected function do_get_encryption_passphrase() {

    return $this->get_passphrase_list()[ 0 ] ?? false;

  }

  protected final function is_cli() : bool {

    return $this->do_is_cli();

  }

  protected function do_is_cli() {

    return $this->php_sapi_name() === 'cli';

  }

  protected final function is_debug() : bool {

    return $this->do_is_debug();

  }

  protected function do_is_debug() {

    return defined( 'DEBUG' ) && DEBUG;

  }

  protected final function is_valid_config( ?string &$problem = null ) : bool {

    return $this->do_is_valid_config( $problem );

  }

  protected final function is_valid_secret( $secret ) : bool {

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

  protected final function is_valid_base64( $input ) : bool {

    return $this->do_is_valdi_base64( $input );

  }

  protected function do_is_valid_base64( $input ) {

    if ( empty( $input ) ) { return false; }

    if ( ! is_string( $input ) ) { return false; }

    if ( preg_match( KICKASS_CRYPTO_REGEX_BASE64, $input ) ) { return true; }

    return false;

  }

  protected function do_encrypt( $input ) {

    if ( $input === false && ! $this->get_config_false_enable() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE );

    }

    $data_encoding = $this->get_config_data_encoding();

    if ( ! $this->is_valid_data_encoding( $data_encoding ) ) {

      if ( $data_encoding === KICKASS_CRYPTO_DATA_ENCODING_PHPS ) {

        if ( ! $this->get_config_phps_enable() ) {

          return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED );

        }
      }

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID );

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

      return $this->error( KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID );

    }

    $passphrase_length = strlen( $passphrase );

    if ( $passphrase_length !== $this->get_const_passphrase_length() ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID,
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
        KICKASS_CRYPTO_ERROR_CHUNK_SIZE_INVALID,
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

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_2 );

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

  protected final function is_valid_data_format( string $data_format ) : bool {

    // 2023-04-05 jj5 - NOTE: we don't give the client the option of defining the valid data
    // format.

    $length = strlen( $data_format );

    if ( $length < KICKASS_CRYPTO_DATA_FORMAT_LENGTH_MIN ) {

      return false;

    }

    if ( $length > KICKASS_CRYPTO_DATA_FORMAT_LENGTH_MAX ) {

      return false;

    }

    if ( ! preg_match( '/^[A-Z][A-Z0-9]+$/', $data_format ) ) { return false; }

    return true;

  }

  protected final function is_valid_data_encoding( string $data_encoding ) : bool {

    // 2023-04-04 jj5 - the string length is non-negotiable, we need our padded messsages to
    // always be the same size.
    //
    if ( strlen( $data_encoding ) !== 4 ) { return false; }

    return $this->do_is_valid_data_encoding( $data_encoding );

  }

  protected function do_is_valid_data_encoding( $data_encoding ) {

    switch ( $data_encoding ) {

      case KICKASS_CRYPTO_DATA_ENCODING_JSON :

        return true;

      case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

        // 2023-04-04 jj5 - this data encoding is valid if it has been made available...
        //
        return $this->get_config_phps_enable();

      default :

        return false;

    }
  }

  protected final function get_data_encoding() : string {

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

      return $this->error( KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID );

    }

    foreach ( $this->get_passphrase_list() as $passphrase ) {

      if ( strlen( $passphrase ) !== $this->get_const_passphrase_length() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID_2 );

      }

      $encoded_data = $this->try_decrypt( $binary, $passphrase, $data_encoding );

      if ( $encoded_data === false ) { continue; }

      $result = $this->data_decode( $encoded_data, $data_encoding, $is_false );

      if ( $result !== false ) { return $result; }

      if ( $is_false ) { return false; }

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

  protected final function parse_binary( $binary, &$iv, &$ciphertext, &$tag ) : bool {

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

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID );

    }

    $encoded_data_length_string = $parts[ 0 ];
    $data_encoding_read = $parts[ 1 ];
    $binary = $parts[ 2 ];

    if ( strlen( $encoded_data_length_string ) !== 8 ) {

      return $this->error(
        KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_SPEC_INVALID,
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
        KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID,
        [
          'json_length' => $encoded_data_length,
        ]
      );

    }

    if ( ! $this->is_valid_data_encoding( $data_encoding_read ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_3 );

    }

    // 2023-04-02 jj5 - the binary data is the JSON with the random padding after it. So take
    // the JSON from the beginning of the string, ignore the padding, and return the JSON.

    if ( $encoded_data_length > strlen( $binary ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_LENGTH_INVALID );

    }

    $encoded_data = substr( $binary, 0, $encoded_data_length );

    $data_encoding = $data_encoding_read;

    return $encoded_data;

  }

  protected function do_delay( int $ns_min, int $ns_max ) {

    $this->log_error( 'delayed due to error...', __FILE__, __LINE__, __FUNCTION__ );

    $this->get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

    assert( is_int( $seconds ) );
    assert( $seconds >= 0 );
    assert( is_int( $nanoseconds ) );
    assert( $nanoseconds < 1_000_000_000 );

    return $this->php_time_nanosleep( $seconds, $nanoseconds );

  }

  protected final function emergency_delay() : void {

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

        $this->report_emergency_delay( 'nanosleep', __FILE__, __LINE__, __FUNCTION__ );

        return;

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

    $this->report_emergency_delay( 'microsleep', __FILE__, __LINE__, __FUNCTION__ );

  }

  private function report_emergency_delay( string $type, $file, $line, $function ) : void {

    try {

      $this->do_report_emergency_delay( $type, $file, $line, $function );

    }
    catch ( \Throwable $ex ) {

      try {

        $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }
  }

  protected function do_report_emergency_delay( $type, $file, $line, $function ) {

    $this->log_error( 'emergency delay: ' . $type, $file, $line, $function );

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

    throw new \KickassCrypto\Framework\KickassCryptoException( $message, $code, $previous, $data );

  }

  protected final function data_encode( $data, $data_encoding ) {

    try {

      return $this->do_data_encode( $data, $data_encoding );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_2 );

    }
  }

  protected function do_data_encode( $data, $data_encoding ) {

    try {

      if (
        $data_encoding === false &&
        defined( 'KICKASS_CRYPTO_TEST_DATA_ENCODE' ) &&
        KICKASS_CRYPTO_TEST_DATA_ENCODE
      ) {

        throw new \Exception( 'fault injection' );

      }

      switch ( $data_encoding ) {

        case KICKASS_CRYPTO_DATA_ENCODING_JSON :

          return $this->json_encode( $data );

        case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

          return $this->phps_encode( $data );

        default :

          return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_3 );

      }
    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_4 );

    }
  }

  protected final function json_encode( $input ) {

    try {

      return $this->do_json_encode( $input );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_4 );

    }
  }

  protected function do_json_encode( $input ) {

    try {

      $options = $this->get_config_json_encode_options();

      $result = $this->php_json_encode( $input, $options );

      $error = $this->php_json_last_error();

      if ( $error ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

      }

      if ( $result === false ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_2 );

      }

      return $result;

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_3 );

    }
  }

  protected final function phps_encode( $input ) {

    try {

      if ( ! $this->get_config_phps_enable() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED_2 );

      }

      return $this->do_phps_encode( $input );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_3 );

    }
  }

  protected function do_phps_encode( $input ) {

    try {

      $result = $this->php_serialize( $input );

      if ( ! $result ) {

        return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED );

      }

      return $result;

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_2 );

    }
  }

  protected final function data_decode(
    $encoded_data,
    $data_encoding = KICKASS_CRYPTO_DATA_ENCODING_JSON,
    &$is_false = null
  ) {

    try {

      $is_false = false;

      return $this->do_data_decode( $encoded_data, $data_encoding, $is_false );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_2 );

    }
  }

  protected function do_data_decode( string $encoded_data, $data_encoding, &$is_false ) {

    try {

      $is_false = false;

      if (
        $data_encoding === false &&
        defined( 'KICKASS_CRYPTO_TEST_DATA_DECODE' ) &&
        KICKASS_CRYPTO_TEST_DATA_DECODE
      ) {

        throw new \Exception( 'fault injection' );

      }

      switch ( $data_encoding ) {

        case KICKASS_CRYPTO_DATA_ENCODING_JSON :

          return $this->json_decode( $encoded_data, $is_false );

        case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

          return $this->phps_decode( $encoded_data, $is_false );

        default :

          return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_3 );

      }

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_4 );

    }
  }

  protected final function json_decode( $input, &$is_false ) {

    try {

      $is_false = false;

      return $this->do_json_decode( $input, $is_false );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

    }
  }

  protected function do_json_decode( $input, &$is_false ) {

    try {

      static $false_json = null;

      if ( $false_json === null ) {

        $false_json = $this->php_json_encode( false, $this->get_config_json_encode_options() );

      }

      $is_false = false;

      $options = $this->get_config_json_decode_options();

      $result = $this->php_json_decode( $input, $assoc = true, 512, $options );

      $error = $this->php_json_last_error();

      if ( $error ) {

        return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_2 );

      }

      if ( $result === false ) {

        if ( $input === $false_json ) {

          $is_false = true;

        }

        if ( ! $this->get_config_false_enable() ) {

          return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_3 );

        }
      }

      return $result;

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_4 );

    }
  }

  protected final function phps_decode( $input, &$is_false ) {

    try {

      $is_false = false;

      return $this->do_phps_decode( $input, $is_false );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED );

    }
  }

  protected function do_phps_decode( $input, &$is_false ) {

    static $false_phps = null;

    if ( $false_phps === null ) { $false_phps = $this->php_serialize( false ); }

    $is_false = false;

    $result = $this->php_unserialize( $input );

    if ( $result === false ) {

      if ( $input === $false_phps ) {

        $is_false = true;

      }

      if ( ! $this->get_config_false_enable() ) {

        return $this->error( KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED_2 );

      }
    }

    return $result;

  }

  protected final function message_encode( string $binary ) {

    try {

      return $this->do_message_encode( $binary );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED );

    }
  }

  protected function do_message_encode( string $binary ) {

    $data_format = $this->get_data_format();
    $base64 = $this->php_base64_encode( $binary );

    if ( ! is_string( $base64 ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_2 );

    }

    if ( ! $this->is_valid_data_format( $data_format ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_3 );

    }

    if ( ! $base64 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_4 );

    }

    return $data_format . '/' . $base64;

  }

  protected final function message_decode( string $encoded ) {

    try {

      return $this->do_message_decode( $encoded );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_DECODING_FAILED );

    }
  }

  protected function do_message_decode( string $encoded ) {

    $parts = explode( '/', $encoded, 2 );

    if ( count( $parts ) !== 2 ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_INVALID );

    }

    $data_format = $parts[ 0 ];

    if ( $data_format !== $this->get_data_format() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_UNKNOWN );

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

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED );

    }

    // 2023-04-01 jj5 - NOTE: but we did have to include this extra check for empty because
    // it's not always false which is returned... actually empty() is probably stronger than
    // required, a simplye $result !== '' would probably do, but this should be fine...
    //
    if ( empty( $result ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2 );

    }

    return $result;

  }

  protected final function get_data_format() {

    // 2023-04-04 jj5 - this function just makes sure that only our implementation can use the
    // "KA" data format version prefix. If we're running someone elses code and they don't
    // nominate a new data format version for their own use we just put an 'X' in front of the
    // version so as to avoid it having the same value as used by our canonical implementation.

    $version = $this->get_const_data_format();

    $class = get_class( $this );

    switch ( $class ) {

      case 'KickassCrypto\Module\OpenSsl\KickassOpenSslRoundTrip' :
      case 'KickassCrypto\Module\OpenSsl\KickassOpenSslAtRest' :
      case 'KickassCrypto\Module\Sodium\KickassSodiumRoundTrip' :
      case 'KickassCrypto\Module\Sodium\KickassSodiumAtRest' :

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

        // 2023-04-05 jj5 - this could infinite loop...
        //
        //$this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $message );

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