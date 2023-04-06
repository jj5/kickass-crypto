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
 * 2023-03-30 jj5 - this is the Kickass Crypto library service framework.
 *
 * 2023-04-05 jj5 - if you just want a default implementation decide whether you want
 * the Sodium implementation or the OpenSSL implementation and then include one (or both) of:
 *
 * - inc/sodium.php
 * - inc/openssl.php
 *
 * if you're not sure, use Sodium.
 *
 * 2023-03-30 jj5 - make sure you load a valid config.php file, then use this library like this:
 *
 * $ciphertext = kickass_round_trip()->encrypt( 'secret data' );
 *
 * $plaintext = kickass_round_trip()->decrypt( $ciphertext );
 *
 * see README.md for more info.
 *
 * @link https://github.com/jj5/kickass-crypto
 **/

namespace KickassCrypto;

/**
 * 2023-04-05 jj5 - this class provides the base framework for a crypto service; you can extend
 * it yourself or use one (or more) of the services implemented as modules in this library.
 */
abstract class KickassCrypto implements \KickassCrypto\IKickassCrypto {

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
   * 2023-04-07 jj5 - this map is for tracking active functions which are presently on the call
   * stack; see the enter() and leave() functions to understand how this field is used.
   *
   * @var array keys are strings containing function names and values are a count of the number
   * of instances of the function that are presently on the call stack.
   */
  private $active = [];

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

      /**
       * 2023-04-07 jj5 - programmers can disable config validation by defining this constant
       * as true.
       *
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_CONFIG_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_KEY_HASH_VALIDATION' ) ) {

      /**
       * 2023-04-07 jj5 - programmers can disable secret key hash function validation by defining
       * this constant as true.
       *
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_KEY_HASH_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_RANDOM_BYTES_VALIDATION' ) ) {

      /**
       * 2023-04-07 jj5 - programmers can disable validation of the random_bytes() function by
       * defining this constant as true.
       *
       * @var boolean
       */
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
      catch ( \AssertionError $ex ) {

        throw $ex;

      }
      catch ( \Throwable $ex ) {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

        $this->throw( KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM );

        assert( false );

      }
    }
  }

  /**
   * 2023-04-07 jj5 - gets the list of errors, it's a list of strings, it can be empty, in fact
   * it's best if it is!
   *
   * @return array the list of errors, or an empty array if none.
   */
  abstract protected function do_get_error_list();

  /**
   * 2023-04-07 jj5 - gets the most recent error, it's a string, or null if no error.
   *
   * @return string|null the most recent error or null if no error.
   */
  abstract protected function do_get_error();

  /**
   * 2023-04-07 jj5 - clears the active list of errors.
   *
   * @return void
   */
  abstract protected function do_clear_error();

  /**
   * 2023-03-30 jj5 - implementations need to define what a valid configuration is.
   *
   * @param string|null $problem a description of the problem if invalid, null otherwisej.
   *
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
   *
   * @param string $passphrase the passphrase (in binary format, should be 32 bytes).
   *
   * @return string|false returns the encrypted string on success or false on failure.
   */
  abstract protected function do_encrypt_string( $plaintext, $passphrase );

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
   *
   * @param string $passphrase the passphrase (in binary format, should be 32 bytes).
   *
   * @return string|false returns the decrypted string on success or false on failure.
   */
  abstract protected function do_decrypt_string( $binary, $passphrase );

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

    $result = base64_encode( random_bytes( 66 ) );

    assert( is_string( $result ) );
    assert( strlen( $result ) === 88 );

    return $result;

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
   *
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
   *
   * @return void
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
   *
   * @return string|false the ciphertext on success, or false on failure.
   */
  public final function encrypt( $input ) {

    try {

      $this->inject_delay = true;

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      $result = $this->do_encrypt( $input );

      if ( is_string( $result ) ) {

        $this->count_length( strlen( $result ) );

      }

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - decrypts the ciphertext and returns the deserialized value; returns false on
   * failure.
   *
   * @param string $ciphertext the ciphertext previously returned from the encrypt() method.
   *
   * @return mixed the decrypted and deserialized value, or false on failure.
   */
  public final function decrypt( string $ciphertext ) {

    try {

      $this->inject_delay = true;

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      $result = $this->do_decrypt( $ciphertext );

      // 2023-04-05 jj5 - this result could be pretty much anything, there's no assertion to be
      // made...
      //
      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

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

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      // 2023-04-02 jj5 - we time the do_delay() implementation and if it doesn't meet the
      // minimum requirement we do the emergency delay.

      $start = microtime( $as_float = true );

      assert( is_int( KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN ) );
      assert( is_int( KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX ) );

      $this->do_delay(
        KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN, KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX
      );

      $duration = microtime( $as_float = true ) - $start;

      if ( $duration < KICKASS_CRYPTO_DELAY_SECONDS_MIN ) {

        $this->emergency_delay();

      }
    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        // 2023-04-01 jj5 - it's important to do things in this order, in case something throws...

        // 2023-04-02 jj5 - in order to "fail safe" we inject this emergency delay immediately
        // so that nothing can accidentally interfere with it happening...
        //
        $this->emergency_delay();

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-01 jj5 - the point of handle() is simply to notify that an exception has been caught
   * and "handled"; sometimes "handling" the exception is tantamount to ignoring it, so we call
   * this method that we may make some noise about it (during debugging, usually). See do_handle()
   * for the rest of the story.
   *
   * It's very important that this function does not throw exceptions, except for AssertionError,
   * which is allowed.
   *
   * @return void
   */
  protected final function handle( $ex, $file, $line, $function ) : void {

    try {

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      $this->do_handle( $ex, $file, $line, $function );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      // 2023-04-01 jj5 - this function is called from exception handlers, and then notifies
      // impementations via the do_handle() method, as above. We don't trust implementations not
      // to throw, and as we're presently *in* an exception handler, we don't want to throw
      // another exception, because code might not be set up to accommodate that. So if we
      // land here do_handle() above (or count_function()?) has thrown, so just log and ignore.

      // 2023-04-03 jj5 - note that here we call the PHP error directly so no one has a chance
      // to interfere with this message being logged. It should never happen and if it does we
      // want to give ourselves our best chance of finding out about it so we can address.

      try {

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-01 jj5 - implementations can decide what to do when errors are handled; by default
   * we write a log entry; can be overridden by implementations.
   *
   * @param \Throwable $ex the exception which was caught.
   *
   * @param string $file the path to the file that caught the exception.
   *
   * @param int $line the line in the file where the caught exception was reported.
   *
   * @param string $function the function in which the exception was caught.
   */
  protected function do_handle( $ex, $file, $line, $function ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->log_error(
        KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_HANDLE . $ex->getMessage(), $file, $line, $function
      );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        // 2023-04-07 jj5 - we don't call handle here, if this happens (it really shouldn't) then
        // we make some noise in the log file without giving the programmer a chance to stop us.
        //
        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-01 jj5 - the point of notify() is simply to notify that an exception has been caught
   * but it's going to be rethrown; it's best if this function doesn't throw an exception but it
   * shouldn't be a huge problem if it does, the caller will throw when this function returns
   * anyway.
   *
   * @return void
   */
  protected final function notify( $ex, $file, $line, $function ) : void {

    try {

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      $this->do_notify( $ex, $file, $line, $function );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-01 jj5 - implementations can decide what to do when errors are notified; by default
   * we write a log entry; can be overridden by implementations.
   *
   * @param \Throwable $ex the exception which was caught and will be rethrown.
   *
   * @param string $file the path to the file that caught the exception.
   *
   * @param int $line the line in the file where the caught exception was reported.
   *
   * @param string $function the function in which the exception was caught.
   */
  protected function do_notify( $ex, $file, $line, $function ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->log_error(
        KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_NOTIFY . $ex->getMessage(), $file, $line, $function
      );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-06 jj5 - the point of ignore() is simply to notify that an exception has been caught
   * and it will be ignored.
   *
   * It's very important that this function does not throw exceptions, except for AssertionError,
   * which is allowed. This function is the safest of all.
   *
   * @return void
   */
  protected final function ignore( $ex, $file, $line, $function ) : void {

    try {

      $this->enter( __FUNCTION__ );

      //$this->count_function( __FUNCTION__ );

      $this->do_ignore( $ex, $file, $line, $function );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      // 2023-04-01 jj5 - this function is called from exception handlers, and then notifies
      // impementations via the do_ignore() method, as above. We don't trust implementations not
      // to throw, and as we're presently *in* an exception handler, we don't want to throw
      // another exception, because code might not be set up to accommodate that. So if we
      // land here do_ignore() above (or count_function()?) has thrown, so just log and ignore.

      // 2023-04-03 jj5 - note that here we call the PHP error directly so no one has a chance
      // to interfere with this message being logged. It should never happen and if it does we
      // want to give ourselves our best chance of finding out about it so we can address.

      try {

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) { ; }

    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-01 jj5 - implementations can decide what to do when errors are ignored; by default
   * we write a log entry; can be overridden by implementations.
   *
   * @param \Throwable $ex the exception which was caught and will be ignored.
   *
   * @param string $file the path to the file that caught the exception.
   *
   * @param int $line the line in the file where the caught exception was reported.
   *
   * @param string $function the function in which the exception was caught.
   */
  protected function do_ignore( $ex, $file, $line, $function ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->log_error(
        KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_IGNORE . $ex->getMessage(), $file, $line, $function
      );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) { ; }

    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-05 jj5 - this function throws an exception.
   *
   * @param int $code the exception code of the exception to throw, this is one of the
   * KICKASS_CRYPTO_EXCEPTION_* constants.
   *
   * @param type $data optional data to associate with the exception.
   *
   * @param type $previous the previous exception, if any.
   *
   * @return void
   *
   * @throws KickassCrypto\KickassCryptoException
   */
  protected final function throw( int $code, $data = null, $previous = null ) : void {

    $this->count_function( __FUNCTION__ );

    $this->do_throw( $code, $data, $previous );

    assert( false );

  }

  /**
   * 2023-04-05 jj5 - by default this finds the message for the given exception code and then
   * throws an exception with the relevant code and message; can be overridden by implementations.
   *
   * @param int $code the exception code is one of the KICKASS_CRYPTO_EXCEPTION_* constants.
   *
   * @param mixed $data the data to associate with the exception, if any.
   *
   * @param \Throwable $previous the previous exception, if any.
   *
   * @throws \KickassCrypto\KickassCryptoException after finding the relevant details.
   */
  protected function do_throw( $code, $data, $previous ) {

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    if ( ! $message ) {

      $data = [
        'invalid_code' => $code,
        'data' => $data,
      ];

      $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE, $data, $previous );

    }

    $this->log_error(
      KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_THROW . $message, __FILE__, __LINE__, __FUNCTION__
    );

    throw new \KickassCrypto\KickassCryptoException( $message, $code, $previous, $data );

  }

  /**
   * 2023-04-07 jj5 - gets the current list of errors, the list is empty if there are no errors,
   * otherwise it contains strings which describe the errors that have occurred.
   *
   * @return array an array of strings, can be empty.
   *
   * @throws \AssertionError can throw AssertionError during debugging.
   */
  public final function get_error_list() : array {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_error_list();

      if ( is_array( $result ) ) { return $result; }

      if ( $result === null ) { return []; }

      // 2023-04-06 jj5 - fall through to throw below

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    $this->throw( KICKASS_CRYPTO_EXCEPTION_INVALID_ERROR_LIST );

    assert( false );

  }

  /**
   * 2023-04-07 jj5 - gets the latest error as a string description or returns null if there's no
   * error.
   *
   * @return string|null the error description or null if no error.
   *
   * @throws \AssertionError can throw AssertionError during debugging.
   */
  public final function get_error() : ?string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_error();

      if ( is_string( $result ) ) { return $result; }

      if ( $result === null ) { return null; }

      $this->error( __FUNCTION__, 'TODO: model this error' );

      return null;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      $this->error( __FUNCTION__, 'TODO: model this error' );

      return null;

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return null;

  }

  /**
   * 2023-04-07 jj5 - clears the current errors, if any.
   *
   * @return void
   *
   * @throws \AssertionError can throw AssertionError during debugging.
   */
  public final function clear_error() : void {

    try {

      $this->enter( __FUNCTION__ );

      $this->do_clear_error();

      return;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }
  }

  /**
   * 2023-04-05 jj5 - registers an error; if this is the first error a random delay is injected
   * as a timing attack mitigation.
   *
   * @param string $function the name of the function registering the error.
   *
   * @param string $error the error description, usually one of the KICKASS_CRYPTO_ERROR_*
   * constants.
   *
   * @param mixed $data any error assoicated with the error; this could be sensitive, do not log
   * without scrubbing or expose without thought.
   *
   * @return bool always false.
   */
  protected final function error( $function, $error, $data = null ) : bool {

    try {

      // 2023-04-02 jj5 - the very first thing we do is inject our delay so we can make sure that
      // happens...
      //
      if ( $this->inject_delay ) {

        $this->delay();

        $this->inject_delay = false;

      }

      $this->enter( __FUNCTION__ );

      $this->count_function( __FUNCTION__ );

      $this->do_error( $error );

      // 2023-04-02 jj5 - this function must always return false. We don't give implementations
      // the option to make a mistake about that.
      //
      return false;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      // 2023-04-01 jj5 - the whole point of this function is to *not* throw an exception. Neither
      // delay(), count_function() or do_error() has any business throwing an exception. If they
      // do we make some noise in the log file and return false. Note that we call the PHP
      // error log function here directly because we don't want to make sure this message which
      // should never happen is visible.

      try {

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $ex->getMessage() );

      }
      catch ( \Throwable $ignore ) { ; }

    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - will count this instance.
   *
   * @param string $caller the name of the invoking function.
   *
   * @return int the count of instances made for this class.
   */
  protected final function count_this( string $caller ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_count_this( $caller );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default will count the caller function (should be the constructor) and
   * this class instance; can be overridden by implementations.
   *
   * @param string $caller the name of the invoking function.
   *
   * @return void
   */
  protected function do_count_this( $caller ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->count_function( $caller );

      $result = $this->count_class( get_class( $this ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - will increment a function count metric.
   *
   * @param string $function the name of the function.
   *
   * @return int the current count for this function.
   */
  protected final function count_function( $function ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_count_function( $function );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default will increment the counter for this function metric; can be
   * overridden by implementations.
   *
   * @param string $function the name of the function.
   *
   * @return int the current count for the function.
   */
  protected function do_count_function( $function ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->increment_counter( self::$telemetry[ 'function' ], $function );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - will increment a class count metric.
   *
   * @param string $class the name of the class.
   *
   * @return int the current count for the class.
   */
  protected final function count_class( $class ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_count_class( $class );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default will increment the count for this class metric; can be
   * overridden by implementations.
   *
   * @param string $class the name of the class.
   *
   * @return int the current count for the class.
   */
  protected function do_count_class( $class ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->increment_counter( self::$telemetry[ 'class' ], $class );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - will increment a length count metric.
   *
   * @param int $length the length of the encrypted data.
   *
   * @return int the current count for the length.
   */
  protected final function count_length( int $length ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_count_length( $length );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default will increment the count for this length metric; can be
   * overridden by implementations.
   *
   * @param int $length the length of the encrypted data.
   *
   * @return int the current count for the length.
   */
  protected function do_count_length( $length ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->increment_counter( self::$telemetry[ 'length' ], $length );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - will increment the value for a key in an array; will initialize to zero if
   * missing.
   *
   * @param array $array a reference to the array to operate on.
   *
   * @param string|int $key the key to operate on.
   *
   * @return int the current count.
   */
  protected final function increment_counter( &$array, $key ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_increment_counter( $array, $key );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default will increment the value for a key in an array; will initialize
   * to zero if missing; can be overridden by implementations.
   *
   * @param array $array a reference to the array to operate on.
   *
   * @param string|int $key the key to operate on.
   *
   * @return int the current count.
   */
  protected function do_increment_counter( &$array, $key ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->increment_counter_internal( $array, $key );

      $result = $array[ $key ];

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - gets the data format constant; defers to abstract method for implementation.
   *
   * @return string the data format constant.
   */
  protected final function get_const_data_format() : string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_const_data_format();

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - gets the name of the hashing algorithm to use for secret key hashing.
   *
   * @return string the name of the PHP hashing algorithm.
   */
  protected final function get_const_key_hash() : string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_const_key_hash();

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the KICKASS_CRYPTO_KEY_HASH constant; can be
   * overridden by implementations.
   *
   * @return string the name of the hash algorithm.
   */
  protected function do_get_const_key_hash() {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'KICKASS_CRYPTO_KEY_HASH' );

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - gets the minimum length of a secret key.
   *
   * @return int the minimum length of a secret key.
   */
  protected final function get_const_key_length_min() : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_const_key_length_min();

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the KICKASS_CRYPTO_KEY_LENGTH_MIN constant;
   * can be overridden by implementations.
   *
   * @return int the minimum length of a secret key.
   */
  protected function do_get_const_key_length_min() {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'KICKASS_CRYPTO_KEY_LENGTH_MIN' );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - gets the minimum length of a passphrase.
   *
   * @return int the minimum length of a passphrase.
   */
  protected final function get_const_passphrase_length_min() : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_const_passphrase_length_min();

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the KICKASS_CRYPTO_PASSPHRASE_LENGTH_MIN
   *  constant; can be overridden by implementations.
   *
   * @return int the minimum length of a passphrase.
   */
  protected function do_get_const_passphrase_length_min() {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'KICKASS_CRYPTO_PASSPHRASE_LENGTH_MIN' );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - gets the chunk size, it's used for padding encrypted messages.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the chunk size.
   */
  protected final function get_config_chunk_size(
    int $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE
  ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_chunk_size( $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_CHUNK_SIZE constant or
   * the default value if CONFIG_ENCRYPTION_CHUNK_SIZE is not defined; can be overridden by
   * implementations.
   *
   * @param int $default the default chunk size.
   *
   * @return int the chunk size.
   */
  protected function do_get_config_chunk_size( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE', $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - gets the maximum chunk size, it's used for validating the chunk size.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the maximum chunk size.
   */
  protected final function get_config_chunk_size_max(
    int $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX
  ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_chunk_size_max( $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_CHUNK_SIZE_MAX
   * constant or the default value if CONFIG_ENCRYPTION_CHUNK_SIZE_MAX is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default maximum chunk size.
   *
   * @return int the maximum chunk size.
   */
  protected function do_get_config_chunk_size_max( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_CHUNK_SIZE_MAX', $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - gets the maximum supported data length, it's used for validating the
   * encoded data.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the maximum supported data length.
   */
  protected final function get_config_data_length_max(
    int $default = KICKASS_CRYPTO_DEFAULT_DATA_LENGTH_MAX
  ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_data_length_max( $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_DATA_LENGTH_MAX
   * constant or the default value if CONFIG_ENCRYPTION_DATA_LENGTH_MAX is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default maximum data length.
   *
   * @return int the maximum data length.
   */
  protected function do_get_config_data_length_max( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_DATA_LENGTH_MAX', $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return -1;

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
   *
   * @return string the data encoding.
   */
  protected final function get_config_data_encoding(
    string $default = KICKASS_CRYPTO_DEFAULT_DATA_ENCODING
  ) : string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_data_encoding( $default );

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_DATA_ENCODING constant
   * or the default value if CONFIG_ENCRYPTION_DATA_ENCODING is not defined; can be
   * overridden by implementations.
   *
   * @param string $default the default data format.
   *
   * @return string the data format.
   */
  protected function do_get_config_data_encoding( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_DATA_ENCODING', $default );

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - gets the JSON encoding options which will be passed to the PHP json_encode()
   * function.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the JSON encoding options.
   */
  protected final function get_config_json_encode_options(
    int $default = KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS
  ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_json_encode_options( $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return 0;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS
   * constant or the default value if CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the default JSON encoding options.
   *
   * @return int the JSON encoding options.
   */
  protected function do_get_config_json_encode_options( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return 0;

  }

  /**
   * 2023-04-05 jj5 - gets the JSON decoding options which will be passed to the PHP json_decode()
   * function.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the JSON decoding options.
   */
  protected final function get_config_json_decode_options(
    int $default = KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS
  ) : int {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_json_decode_options( $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return 0;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS
   * constant or the default value if CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS is not defined; can be
   * overridden by implementations.
   *
   * @param int $default the value to use if the config option is not defined.
   *
   * @return int the JSON decoding options.
   */
  protected function do_get_config_json_decode_options( $default  ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS', $default );

      assert( is_int( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return 0;

  }

  /**
   * 2023-04-05 jj5 - gets a boolean value indicating whether PHPS serialization and
   * deserialization is enabled or not.
   *
   * @param boolean $default the value to use if the config option is not defined.
   *
   * @return boolean true if PHPS serialization and deserialization is enabled, false otherwise.
   */
  protected final function get_config_phps_enable(
    bool $default = KICKASS_CRYPTO_DEFAULT_PHPS_ENABLE
  ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_phps_enable( $default );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_PHPS_ENABLE constant
   * or the default value if CONFIG_ENCRYPTION_PHPS_ENABLE is not defined; can be overridden by
   * implementations.
   *
   * @param boolean $default the value to use if the config option is not defined.
   *
   * @return boolean true if PHPS serialization and deserialization is enabled, false otherwise.
   */
  protected function do_get_config_phps_enable( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_PHPS_ENABLE', $default );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - gets a boolean value indicating whether the boolean value false can be
   * encrypted or not.
   *
   * @param boolean $default the value to use if the config option is not defined.
   *
   * @return boolean true if the boolean value false can be encrypted, false otherwise.
   */
  protected final function get_config_false_enable(
    bool $default = KICKASS_CRYPTO_DEFAULT_FALSE_ENABLE
  ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_config_false_enable( $default );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the CONFIG_ENCRYPTION_FALSE_ENABLE constant
   * of the default value if CONFIG_ENCRYPTION_FALSE_ENABLE is not defined; can be overridden by
   * implementations.
   *
   * @param boolean $default the value to use if the config option is not defined.
   *
   * @return boolean true if the boolean value false can be encrypted, false otherwise.
   */
  protected function do_get_config_false_enable( $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_const( 'CONFIG_ENCRYPTION_FALSE_ENABLE', $default );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - gets a constant value, or returns the default value if the constant is not
   * defined.
   *
   * @param string $const the name of the constant.
   *
   * @param mixed $default the value to return if the constant is not defined.
   *
   * @return mixed the constant value or the default value if the constant is not defined.
   */
  protected final function get_const( $const, $default = false ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_const( $const, $default );

      // 2023-04-05 jj5 - the result could be pretty much anything...

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return $default;

  }

  /**
   * 2023-04-05 jj5 - by default returns the value of the constant, or returns the default value
   * if the constant is not defined; can be overridden by implementations.
   *
   * @param string $const the name of the constant.
   *
   * @param mixed $default the value to return if the constant is not defined.
   *
   * @return mixed the value of the constant or the default value if the constant is not defined.
   */
  protected function do_get_const( $const, $default ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = defined( $const ) ? constant( $const ) : $default;

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return $default;

  }

  /**
   * 2023-04-05 jj5 - returns the list of passphrases; defers to abstract implementation.
   *
   * @return array a list of strings to use as passphrases.
   */
  protected final function get_passphrase_list() : array {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_passphrase_list();

      assert( is_array( $result ) );

      foreach ( $result as $passphrase ) {

        if ( $this->is_valid_passphrase( $passphrase) ) { continue; }

        $this->log_error(
          KICKASS_CRYPTO_LOG_ERROR_INVALID_PASSPHRASE, __FILE__, __LINE__, __FUNCTION__
        );

        $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID );

        return [];

      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID );

      return [];

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return [];

  }

  /**
   * 2023-04-05 jj5 - returns the passphrase to use for encryption or null if it's missing.
   *
   * @return string the encryption passphrase or null.
   */
  protected final function get_encryption_passphrase() : ?string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_encryption_passphrase();

      assert( is_string( $result ) || $result === null );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default returns the first passphrase from the passphrase list; can be
   * overridden by implementations.
   *
   * @return ?string the passphrase or null if none.
   */
  protected function do_get_encryption_passphrase() {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_passphrase_list()[ 0 ] ?? null;

      assert( is_string( $result ) || $result === null );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return null;

  }

  /**
   * 2023-04-05 jj5 - reports if the program is running from the command-line.
   *
   * @return bool true if running from the command-line, false otherwise.
   */
  protected final function is_cli() : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_is_cli();

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default checks to see if php_sapi_name() === 'cli'; can be overridden by
   * implementations.
   *
   * @return boolean true if running from the command-line, false otherwise.
   */
  protected function do_is_cli() {

    try {

      $this->enter( __FUNCTION__ );

      $result = ( $this->php_sapi_name() === 'cli' );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks to see if the program is running in DEBUG mode.
   *
   * @return boolean true if running in DEBUG mode, false otherwise.
   */
  protected final function is_debug() : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_is_debug();

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default checks to see if the DEBUG constant is defined and true; can be
   * overridden by implementations.
   *
   * @return boolean true if running in DEBUG mode, false otherwise.
   */
  protected function do_is_debug() {

    try {

      $this->enter( __FUNCTION__ );

      $result = ( defined( 'DEBUG' ) && DEBUG );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks to see if the environment configuration is valid; this function
   * defers to the abstract method do_is_valid_config().
   *
   * @param string|null $problem a reference to the problem, if any.
   *
   * @return boolean true if the config is valid, false otherwise.
   */
  protected final function is_valid_config( ?string &$problem = null ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_is_valid_config( $problem );

      assert( is_bool( $result ) );

      if ( $result ) {

        assert( $problem === null );

      }
      else {

        assert( is_string( $problem ) );

      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks to see if a secret is valid; this function defers to
   * do_is_valid_secret();
   *
   * @param string $secret the secret key
   *
   * @return boolean true if the secret key is valid, false otherwise.
   */
  protected final function is_valid_secret( $secret ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $is_valid = $this->do_is_valid_secret( $secret );

      // 2023-04-06 jj5 - if the client says that the secret is valid then we just check to make
      // sure it looks sensible. We will allow the caller to redefine what a valid secret key is
      // but if it doesn't meet our standards we will make some noise bout it in the log.
      //
      // 2023-04-06 jj5 - NOTE: here we use the KICKASS_CRYPTO_KEY_LENGTH_MIN constant directly
      // and do not give the caller the opporunity to inject a different value.
      //
      if ( $is_valid && strlen( $secret ) < KICKASS_CRYPTO_KEY_LENGTH_MIN ) {

        $this->log_error(
          KICKASS_CRYPTO_LOG_WARNING_SHORT_SECRET, __FILE__, __LINE__, __FUNCTION__
        );

      }

      assert( is_bool( $is_valid ) );

      return $is_valid;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - my default checks that the secret key is a string and meets the minimum
   * length requirements; this function can be overridden by implementations.
   *
   * @param string $secret the secret key
   *
   * @return boolean true if the secret is valid, false otherwise.
   */
  protected function do_is_valid_secret( $secret ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( ! is_string( $secret ) ) { return false; }

      if ( strlen( $secret ) < $this->get_const_key_length_min() ) { return false; }

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-06 jj5 - checks to see if a passphrase is valid; this function defers to
   * do_is_valid_passphrase();
   *
   * @param string $passphrase the passphrase key
   *
   * @return boolean true if the passphrase key is valid, false otherwise.
   */
  protected final function is_valid_passphrase( $passphrase ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $is_valid = $this->do_is_valid_passphrase( $passphrase );

      if ( $is_valid ) {

        // 2023-04-06 jj5 - the code in here is non-negotiable. Also we use the
        // KICKASS_CRYPTO_PASSPHRASE_LENGTH_MIN directly so that implementations can't change it.

        if ( ! is_string( $passphrase ) ) { return false; }

        if ( strlen( $passphrase ) < KICKASS_CRYPTO_PASSPHRASE_LENGTH_MIN ) {

          $this->log_error(
            KICKASS_CRYPTO_LOG_WARNING_SHORT_PASSPHRASE, __FILE__, __LINE__, __FUNCTION__
          );

          return false;

        }
      }

      assert( is_bool( $is_valid ) );

      return $is_valid;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-06 jj5 - by default checks the string length; can be overridden by implementations.
   *
   * @param mixed $passphrase a passphrase to validate.
   *
   * @return boolean true on valid; false otherwise.
   */
  protected function do_is_valid_passphrase( $passphrase ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( ! is_string( $passphrase ) ) { return false; }

      if ( strlen( $passphrase ) < $this->get_const_passphrase_length_min() ) { return false; }

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks the input is in valid base64 format; defers to virtual
   * do_is_valid_base64().
   *
   * @param string $input the value which should be in base64 format.
   *
   * @return boolean true if the input is in base64 format, false otherwise.
   */
  protected final function is_valid_base64( $input ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_is_valdi_base64( $input );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default checks to make sure the input is a non-empty string and then
   * validates it with the KICKASS_CRYPTO_REGEX_BASE64 regular expression; can be overridden by
   * implementations.
   *
   * @param string $input supposedly base64 encoded value.
   *
   * @return boolean true if value is in valid base64 format, false otherwise.
   */
  protected function do_is_valid_base64( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( empty( $input ) ) { return false; }

      if ( ! is_string( $input ) ) { return false; }

      if ( preg_match( KICKASS_CRYPTO_REGEX_BASE64, $input ) ) { return true; }

      return false;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default encrypts the input value; can be overridden by implementations.
   *
   * @param mixed $input the value to encrypt.
   *
   * @return string|false the ciphertext or false on failure.
   */
  protected function do_encrypt( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( $input === false && ! $this->get_config_false_enable() ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE );

      }

      $data_encoding = $this->get_config_data_encoding();

      if ( ! $this->is_valid_data_encoding( $data_encoding ) ) {

        if ( $data_encoding === KICKASS_CRYPTO_DATA_ENCODING_PHPS ) {

          if ( ! $this->get_config_phps_enable() ) {

            return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED );

          }
        }

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID );

      }

      $encoded_data = $this->data_encode( $input, $data_encoding );

      if ( $encoded_data === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED );

      }

      $encoded_data_length = strlen( $encoded_data );

      if ( $encoded_data_length > $this->get_config_data_length_max() ) {

        return $this->error(
          __FUNCTION__,
          KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE,
          [
            'data_length' => $encoded_data_length,
            'data_length_max' => $this->get_config_data_length_max(),
          ]
        );

      }

      $passphrase = $this->get_encryption_passphrase();

      if ( ! $passphrase ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PASSPHRASE_MISSING );

      }

      $passphrase_length = strlen( $passphrase );

      if ( $passphrase_length !== $this->get_const_passphrase_length() ) {

        return $this->error(
          __FUNCTION__,
          KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID,
          [
            'passphrase_length' => $passphrase_length,
            'passphrase_length_required' => $this->get_const_passphrase_length(),
          ]
        );

      }

      if ( ! $this->is_valid_passphrase( $passphrase ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID );

      }

      $chunk_size = $this->get_config_chunk_size();

      if (
        ! is_int( $chunk_size ) ||
        $chunk_size <= 0 ||
        $chunk_size > $this->get_config_chunk_size_max()
      ) {

        return $this->error(
          __FUNCTION__,
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

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_2 );

      }

      $message =
        $encoded_data_length_hex . '|' .
        $data_encoding . '|' .
        $encoded_data .
        $this->get_padding( $pad_length );

      $ciphertext = $this->encrypt_string( $message, $passphrase );

      if ( $ciphertext === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

      }

      $encoded = $this->message_encode( $ciphertext );

      return $encoded;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks to see if the data format is valid; this behavior cannot be
   * overridden by implementations.
   *
   * @param string $data_format the data format string.
   *
   * @return boolean true if the data format is valid, false otherwise.
   */
  protected final function is_valid_data_format( string $data_format ) : bool {

    try {

      $this->enter( __FUNCTION__ );

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
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - checks to see if the data encoding is a valid supported encoding; defers
   * to the virtual do_is_valid_encoding() method.
   *
   * @param string $data_encoding the data encoding to validate.
   *
   * @return boolean true if the data encoding is value, false otherwise.
   */
  protected final function is_valid_data_encoding( string $data_encoding ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      // 2023-04-04 jj5 - the string length is non-negotiable, we need our padded messsages to
      // always be the same size.
      //
      if ( strlen( $data_encoding ) !== 4 ) { return false; }

      $result = $this->do_is_valid_data_encoding( $data_encoding );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default checks that the data encoding is one of the two supported data
   * encodings; can be overridden by implementations.
   *
   * 2023-04-05 jj5 - the two supported data encodings are:
   *
   * - KICKASS_CRYPTO_DATA_ENCODING_JSON
   * - KICKASS_CRYPTO_DATA_ENCODING_PHPS
   *
   * Note that the PHPS data encoding is only a valid and supported data encoding if
   * get_config_phps_enable() is true.
   *
   * @param string $data_encoding the data encoding.
   *
   * @return boolean true if the data encoding is a valid supported value, false otherwise.
   */
  protected function do_is_valid_data_encoding( $data_encoding ) {

    try {

      $this->enter( __FUNCTION__ );

      switch ( $data_encoding ) {

        case KICKASS_CRYPTO_DATA_ENCODING_JSON :

          return true;

        case KICKASS_CRYPTO_DATA_ENCODING_PHPS :

          // 2023-04-04 jj5 - this data encoding is valid if it has been made available...
          //
          $result = $this->get_config_phps_enable();

          assert( is_bool( $result ) );

          return $result;

        default :

          return false;

      }
    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - gets the data encoding; defers to virtual do_get_data_encoding().
   *
   * @return string the data encoding.
   */
  protected final function get_data_encoding() : string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_data_encoding();

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - by default gets the data encoding from the config option as returned from
   * get_config_data_encoding().
   *
   * @return string the data encoding.
   */
  protected function do_get_data_encoding() {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->get_config_data_encoding();

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return '';

  }

  /**
   * 2023-04-05 jj5 - encrypts a plaintext string using the passphrase; defers to
   * do_encrypt_string().
   *
   * @param string $plaintext the plaintext to encrypt.
   *
   * @param string $passphrase the passphrase to use for encryption.
   *
   * @return string|false the ciphertext on success or false on error.
   */
  protected final function encrypt_string( string $plaintext, string $passphrase ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_encrypt_string( $plaintext, $passphrase );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default handles the standard decryption process; can be overridden by
   * implementations.
   *
   * @param string $ciphertext the ciphertext to decrypt.
   *
   * @return mixed the decrypted and deserialized value on success, false otherwise.
   */
  protected function do_decrypt( $ciphertext ) {

    try {

      $this->enter( __FUNCTION__ );

      $error = KICKASS_CRYPTO_ERROR_NO_VALID_KEY;

      $binary = $this->message_decode( $ciphertext );

      if ( $binary === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID );

      }

      foreach ( $this->get_passphrase_list() as $passphrase ) {

        if ( ! $this->is_valid_passphrase( $passphrase ) ) {

          return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID );

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

      return $this->error( __FUNCTION__, $error );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - tries to decrypt a binary string using the passphrase.
   *
   * @param string $binary the binary data to decrypt.
   *
   * @param string $passphrase the passphrase to use for decryption.
   *
   * @param string $data_encoding a reference to the data encoding, this is set if its possible to
   * determine.
   *
   * @return mixed the decoded message on success, false otherwise.
   */
  protected final function try_decrypt( string $binary, string $passphrase, &$data_encoding = null ) {

    try {

      $this->enter( __FUNCTION__ );

      $data_encoding = null;

      $message = $this->decrypt_string( $binary, $passphrase );

      if ( $message === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

      }

      return $this->decode_message( $message, $data_encoding );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - decrypts a binary string using the passphrase.
   *
   * @param string $binary the binary data to decrypt.
   *
   * @param string $passphrase the passphrase to use for decryption.
   *
   * @return mixed the decrypted string or false on error.
   */
  protected final function decrypt_string( string $binary, string $passphrase ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_decrypt_string( $binary, $passphrase );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - parses the binary message into its fixed length components.
   *
   * @param string $binary the binary message.
   *
   * @param string $iv the initialization vector for OpenSLL or the nonce for Sodium.
   *
   * @param string $ciphertext the ciphertext.
   *
   * @param string $tag the tag, only provided by OpenSSL, false for Sodium.
   *
   * @return boolean true on success, false otherwise.
   */
  protected final function parse_binary( $binary, &$iv, &$ciphertext, &$tag ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $iv = false;
      $ciphertext = false;
      $tag = false;

      $result = $this->do_parse_binary( $binary, $iv, $ciphertext, $tag );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - decodes a message; defers to virtual do_decode_message().
   *
   * @param string $message the encoded message.
   *
   * @param string $data_encoding a reference to the data encoding nominated in the message.
   *
   * @return string|false the decoded message or false on failure.
   */
  protected final function decode_message( string $message, &$data_encoding ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_decode_message( $message, $data_encoding );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default will decode the message and determine the data encoding using;
   * can be overridden by implementations.
   *
   * @staticvar int $max_json_length this is the maximum length supported by the JSON format.
   * Note due to other limits this limit could never be reached.
   *
   * @param string $message the message to decode.
   *
   * @param string $data_encoding a reference to the data encoding extracted from the message.
   *
   * @return string the decoded string or false on error.
   */
  protected function do_decode_message( $message, &$data_encoding ) {

    try {

      $this->enter( __FUNCTION__ );

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

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID );

      }

      $encoded_data_length_string = $parts[ 0 ];
      $data_encoding_read = $parts[ 1 ];
      $binary = $parts[ 2 ];

      if ( strlen( $encoded_data_length_string ) !== 8 ) {

        return $this->error(
          __FUNCTION__,
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
          __FUNCTION__,
          KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID,
          [
            'json_length' => $encoded_data_length,
          ]
        );

      }

      if ( ! $this->is_valid_data_encoding( $data_encoding_read ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_3 );

      }

      // 2023-04-02 jj5 - the binary data is the JSON with the random padding after it. So take
      // the JSON from the beginning of the string, ignore the padding, and return the JSON.

      if ( $encoded_data_length > strlen( $binary ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_LENGTH_INVALID );

      }

      $encoded_data = substr( $binary, 0, $encoded_data_length );

      $data_encoding = $data_encoding_read;

      assert( is_string( $encoded_data ) );
      assert( is_string( $data_encoding ) );

      return $encoded_data;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default sleeps for a random amount of time between the minimum time given
   * in nanoseconds and the maximum time also given in nanoseconds; can be overridden by
   * implementations.
   *
   * @param int $ns_min minimum delay in nanoseconds.
   *
   * @param int $ns_max maximum delay in nanoseconds.
   */
  protected function do_delay( $ns_min, $ns_max ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->log_error( KICKASS_CRYPTO_LOG_WARNING_DELAY, __FILE__, __LINE__, __FUNCTION__ );

      $this->get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

      assert( is_int( $seconds ) );
      assert( $seconds >= 0 );
      assert( is_int( $nanoseconds ) );
      assert( $nanoseconds < 1_000_000_000 );

      $this->php_time_nanosleep( $seconds, $nanoseconds );

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - does an emergency delay; this doesn't give the caller any option to
   * interfere so the emergency delay should run reliably if it's necessary.
   *
   * @return void
   *
   * @throws \AssertionError if there's an assertion violation during testing (not enable in
   * production).
   *
   * @throws \Exception may throw an exception if set up for error injection, this should only be
   * done during testing.
   */
  protected final function emergency_delay() : void {

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

      // 2023-04-05 jj5 - don't do anything until *after* the delay, just in case it throws etc;
      // as long as the delay happens not much else matters.

      $this->enter( __FUNCTION__ );

      if (
        defined( 'KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP' ) &&
        KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP
      ) {

        throw new \Exception(
          'test running: KICKASS_CRYPTO_TEST_EMERGENCY_DELAY_MICROSLEEP'
        );

      }

      if ( $result ) {

        $this->report_emergency_delay( 'nanosleep', __FILE__, __LINE__, __FUNCTION__ );

        return;

      }

      // 2023-04-02 jj5 - otherwise we fall through to the usleep() fallback below...

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      usleep( random_int( 1_000, 10_000_000 ) );

      $this->report_emergency_delay( 'microsleep', __FILE__, __LINE__, __FUNCTION__ );

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
  }

  /**
   * 2023-04-05 jj5 - this function makes some noice is the emergency delay is invoked; the
   * emergency delay should not usually be activated so if it is we want to give ourself our best
   * chance of finding out about it.
   *
   * @param string $type the type of delay, could be 'nanosleep' or 'microsleep'.
   *
   * @param string $file the file that reported the emergecny delay.
   *
   * @param int $line the line in the file where the emergency delay was reported.
   *
   * @param string $function the function from which the emergency delay was reported.
   *
   * @return void
   *
   * @throws \AssertionError on an assertion violation, this isn't done in production.
   */
  private function report_emergency_delay( string $type, $file, $line, $function ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $this->do_report_emergency_delay( $type, $file, $line, $function );

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default writes a message to the error log; can be overridden by
   * implementations.
   *
   * @param string $type the type of delay, could be 'nanosleep' or 'microsleep'.
   *
   * @param string $file the file that reported the emergecny delay.
   *
   * @param int $line the line in the file where the emergency delay was reported.
   *
   * @param string $function the function from which the emergency delay was reported.
   */
  protected function do_report_emergency_delay( $type, $file, $line, $function ) {

    try {

      $this->enter( __FUNCTION__ );

      $this->log_error(
        KICKASS_CRYPTO_LOG_PREFIX_EMERGENCY_DELAY . $type, $file, $line, $function
      );

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - serializes the data based on the data encoding in use; defers to a virtual
   * do_data_encode() method.
   *
   * @param mixed $data input data, can be pretty much anything.
   *
   * @param string $data_encoding the data encoding to use, usually
   * either KICKASS_CRYPTO_DATA_ENCODING_JSON for JSON encoding or
   * KICKASS_CRYPTO_DATA_ENCODING_PHPS for PHP serialization (if it's enabled).
   *
   * @return string|false the encoded string on success or false on failure.
   *
   * @throws \AssertionError if there's an assertion violation, this doesn't happen in production.
   */
  protected final function data_encode( $data, $data_encoding ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_data_encode( $data, $data_encoding );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_2 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default serializes data based on the data encoding in use; can be
   * overridden by implementations.
   *
   * @param mixed $data input data, can be pretty much anything.
   *
   * @param string $data_encoding the data encoding to use, usually
   * either KICKASS_CRYPTO_DATA_ENCODING_JSON for JSON encoding or
   * KICKASS_CRYPTO_DATA_ENCODING_PHPS for PHP serialization (if it's enabled).
   *
   * @return string|false the encoded string on success or false on failure.
   *
   * @throws \AssertionError if there's an assertion violation, this doesn't happen in production.
   *
   * @throws \Exception may be thrown for fault injection during testing, this shouldn't happen
   * in production.
   */
  protected function do_data_encode( $data, $data_encoding ) {

    try {

      $this->enter( __FUNCTION__ );

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

          return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_3 );

      }
    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_4 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - encodes a valud as JSON; defers to do_json_encode() for implementation.
   *
   * @param mixed $input input can be almost anything.
   *
   * @return string|false the JSON string on success, false on failure.
   *
   * @throws \AssertionError potentially thrown during testing.
   */
  protected final function json_encode( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_json_encode( $input );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_4 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - by default encodes the input as JSON using the JSON encoding options read
   * from the config file; can be overridden by implementations.
   *
   * @param string $input JSON input.
   *
   * @return mixed returns the decoded JSON value or false on failure.
   *
   * @throws \AssertionError potentially thrown during testing.
   */
  protected function do_json_encode( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      $options = $this->get_config_json_encode_options();

      $result = $this->php_json_encode( $input, $options );

      $error = $this->php_json_last_error();

      if ( $error ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED );

      }

      if ( $result === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_2 );

      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_3 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-05 jj5 - serializes a value using PHP serialization; defers to virtual method
   * do_phps_encode() for implementation.
   *
   * @param mixed $input can be pretty much anything, the PHP serialization has good support for
   * odd values such as object instances and weird floats.
   *
   * @return string|false the serialized input or false on error.
   *
   * @throws \AssertionError
   */
  protected final function phps_encode( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( ! $this->get_config_phps_enable() ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED_2 );

      }

      $result = $this->do_phps_encode( $input );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_3 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of PHP serialization; can be overridden by
   * implementers; by default calls the PHP serialize() function via its wrapper.
   *
   * @param mixed $input can be pretty much anything.
   *
   * @return string|false the serialized value on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_phps_encode( $input ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->php_serialize( $input );

      if ( ! $result ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED );

      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_2 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - decodes the encoded data using the nominated encoding.
   *
   * @param string $encoded_data the serialized data.
   *
   * @param string $data_encoding the data encoding to use, can be JSON or PHPS.
   *
   * @param boolean $is_false set to true if the encoded data is deserialized to the boolean value
   * false.
   *
   * @return mixed returns the decoded data (can be pretty much anything) or false on error;
   * false can also be a valid return value, if it is then $is_false will be set.
   *
   * @throws \AssertionError
   */
  protected final function data_decode(
    $encoded_data,
    $data_encoding = KICKASS_CRYPTO_DATA_ENCODING_JSON,
    &$is_false = null
  ) {

    try {

      $this->enter( __FUNCTION__ );

      $is_false = false;

      return $this->do_data_decode( $encoded_data, $data_encoding, $is_false );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_2 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - provides the default implementation for data decoding, which provides
   * support for decoding either JSON or PHPS.
   *
   * @param string $encoded_data the serialized data.
   *
   * @param string $data_encoding the data encoding to use, can be JSON or PHPS.
   *
   * @param boolean $is_false set to true if the deserialized value is the boolean value false.
   *
   * @return mixed can return pretty much anything, will return false on error, but false can also
   * be a valid return value.
   *
   * @throws \AssertionError
   *
   * @throws \Exception may inject an exception for testing purposes, you can control this by
   * passing $data_encoding === false and defining the KICKASS_CRYPTO_TEST_DATA_DECODE constant
   * true.
   */
  protected function do_data_decode( $encoded_data, $data_encoding, &$is_false ) {

    try {

      $this->enter( __FUNCTION__ );

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

          return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_3 );

      }

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_4 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - deserialized JSON data.
   *
   * @param string $input the JSON.
   *
   * @param boolean $is_false set to true if the JSON deserializes to the boolean value false.
   *
   * @return mixed can return pretty much anything, will return false on failure but false can be
   * a valid return value.
   *
   * @throws \AssertionError
   */
  protected final function json_decode( $input, &$is_false ) {

    try {

      $this->enter( __FUNCTION__ );

      $is_false = false;

      return $this->do_json_decode( $input, $is_false );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of JSON decoding by calling the PHP function via its
   * wrapper.
   *
   * @staticvar type $false_json the JSON for the boolean value false encoded as JSON, this is
   * used to determine if the JSON represents the value false or not; this value is calculated
   * only once and uses the JSON encoding options that were current on the instance which did
   * the initial encoding.
   *
   * @param string $input the JSON to decode.
   *
   * @param boolean $is_false set to true if the JSON decodes to the boolean value false.
   *
   * @return mixed returns the decoded value on success or the value false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_json_decode( $input, &$is_false ) {

    try {

      $this->enter( __FUNCTION__ );

      static $false_json = null;

      if ( $false_json === null ) {

        $false_json = $this->php_json_encode( false, $this->get_config_json_encode_options() );

      }

      $is_false = false;

      $options = $this->get_config_json_decode_options();

      $result = $this->php_json_decode( $input, $assoc = true, 512, $options );

      $error = $this->php_json_last_error();

      if ( $error ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_2 );

      }

      if ( $result === false ) {

        if ( $input === $false_json ) {

          $is_false = true;

        }

        if ( ! $this->get_config_false_enable() ) {

          return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_3 );

        }
      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_4 );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - deserializes the input data using the PHP unserialize() function.
   *
   * @param string $input the serialized data.
   *
   * @param boolean $is_false set to true if the deserialized data is the boolean value false.
   *
   * @return mixed the deserialized data on success or false on failure, the value false can be
   * returned on success if that's what the serialized data represented.
   *
   * @throws \AssertionError
   */
  protected final function phps_decode( $input, &$is_false ) {

    try {

      $this->enter( __FUNCTION__ );

      $is_false = false;

      return $this->do_phps_decode( $input, $is_false );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of PHP deserialization by deferring to the
   * implementation on the PHP wrapper.
   *
   * @staticvar type $false_phps keeps a static copy of what the serialized boolean value false
   * looks like, this is used to determine if the input data is the serialized value false or not.
   *
   * @param string $input the serialized data to deserialize.
   *
   * @param boolean $is_false set to true if the deserialized value is the boolean value false.
   *
   * @return mixed the deserialized data or false on error.
   *
   * @throws \AssertionError
   */
  protected function do_phps_decode( $input, &$is_false ) {

    try {

      $this->enter( __FUNCTION__ );

      static $false_phps = null;

      if ( $false_phps === null ) { $false_phps = $this->php_serialize( false ); }

      $is_false = false;

      $result = $this->php_unserialize( $input );

      if ( $result === false ) {

        if ( $input === $false_phps ) {

          $is_false = true;

        }

        if ( ! $this->get_config_false_enable() ) {

          return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED_2 );

        }
      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - encodes a message.
   *
   * @param string $binary the binary data to encode.
   *
   * @return string|false the encoded value on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function message_encode( string $binary ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_message_encode( $binary );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of message encoding; can be overridden by
   * implementations.
   *
   * @param string $binary the binary data to encode.
   *
   * @return string|false the encoded string on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_message_encode( $binary ) {

    try {

      $this->enter( __FUNCTION__ );

      $data_format = $this->get_data_format();
      $base64 = $this->php_base64_encode( $binary );

      if ( ! is_string( $base64 ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_2 );

      }

      if ( ! $this->is_valid_data_format( $data_format ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_3 );

      }

      if ( ! $base64 ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_4 );

      }

      return $data_format . '/' . $base64;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - decodes a message into binary.
   *
   * @param string $encoded the encoded message.
   *
   * @return string|false the decoded message or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function message_decode( string $encoded ) {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_message_decode( $encoded );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_DECODING_FAILED );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of message decoding; can be overridden by
   * implementations.
   *
   * @param string $encoded the encoded message.
   *
   * @return string|false the decoded message or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_message_decode( $encoded ) {

    try {

      $this->enter( __FUNCTION__ );

      $parts = explode( '/', $encoded, 2 );

      if ( count( $parts ) !== 2 ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_INVALID );

      }

      $data_format = $parts[ 0 ];

      if ( $data_format !== $this->get_data_format() ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_UNKNOWN );

      }

      // 2023-04-01 jj5 - OLD: we don't do this any more, if base64 decoding fails we can
      // surmise that the encoding was not valid, there's not much point doing validation in
      // advance, especially as the normal case is that the encoding is valid.
      //
      /*
      if ( ! $this->is_valid_base64( $parts[ 1 ] ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING );

      }
      */

      $result = $this->php_base64_decode( $parts[ 1 ], $strict = true );

      if ( $result === false ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED );

      }

      // 2023-04-01 jj5 - NOTE: but we did have to include this extra check for empty because
      // it's not always false which is returned... actually empty() is probably stronger than
      // required, a simplye $result !== '' would probably do, but this should be fine...
      //
      if ( empty( $result ) ) {

        return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2 );

      }

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    try {

      return $this->error( __FUNCTION__, 'TODO: model this error' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - gets the data format; the data format can be "KA0" for OpenSSL
   * implementations and "KAS0" for Sodium implementations; if not the official implementation
   * then the string 'X' is prepended to the data format.
   *
   * @return string|false the data format string or false on error.
   *
   * @throws \AssertionError
   */
  protected final function get_data_format() {

    try {

      $this->enter( __FUNCTION__ );

      // 2023-04-04 jj5 - this function just makes sure that only our implementation can use the
      // "KA" data format version prefix. If we're running someone elses code and they don't
      // nominate a new data format version for their own use we just put an 'X' in front of the
      // version so as to avoid it having the same value as used by our canonical implementation.

      $version = $this->get_const_data_format();

      assert( is_string( $version ) );

      $class = get_class( $this );

      switch ( $class ) {

        case 'KickassCrypto\OpenSsl\KickassOpenSslRoundTrip' :
        case 'KickassCrypto\OpenSsl\KickassOpenSslAtRest' :
        case 'KickassCrypto\Sodium\KickassSodiumRoundTrip' :
        case 'KickassCrypto\Sodium\KickassSodiumAtRest' :

          return $version;

        default :

          if ( strpos( $version, 'KA' ) !== 0 ) { return $version; }

          return 'X' . $version;

      }
    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - converts a secret key to a passphrase by applying the hashing function.
   *
   * @param string $key the secret key to convert
   *
   * @return string|false the generated passphrase or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function convert_secret_to_passphrase( $key ) {

    try {

      $this->enter( __FUNCTION__ );

      // 2023-04-05 jj5 - definitely don't want to hash an empty value and think we have something
      // useful.

      if ( empty( $key ) ) { return false; }

      $result = $this->do_convert_secret_to_passphrase( $key );

      assert( is_string( $result ) || $result === false );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - the default implementation of the passphrase hashing logic.
   *
   * @param string $key the secret key to hash.
   *
   * @return string|false the hashed string to use as the passphraser or false on error.
   *
   * @throws \AssertionError
   */
  protected function do_convert_secret_to_passphrase( $key ) {

    try {

      $this->enter( __FUNCTION__ );

      // 2023-04-05 jj5 - definitely don't want to hash an empty value and think we have something
      // useful.

      if ( empty( $key ) ) { return false; }

      return hash( $this->get_const_key_hash(), $key, $binary = true );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - gets the string to use as padding; it's usually just a string of random
   * bytes.
   *
   * @param int $length the length of the random padding to generate.
   *
   * @return string|false the padding string to use or false on error.
   *
   * @throws \AssertionError
   */
  protected final function get_padding( int $length ) : string {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_get_padding( $length );

      assert( is_string( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of generating the random padding; can be overridden by
   * implementations.
   *
   * @param int $length the length of the padding to generate.
   *
   * @return string|false the padding on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_get_padding( $length ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( ! $this->is_debug() ) {

        return $this->php_random_bytes( $length );

      }

      // 2023-04-06 jj5 - for debugging we generate random numbers in various other ways. You would
      // be surprised to know doing this actually helped me fix a problem! When the random padding
      // was a long string of the same ASCII character the regular expression for base64 encoding
      // failed! I only learned that because I was experimenting with approaches to padding... who
      // would have thought...

      switch ( random_int( 1, 3 ) ) {

        case 1 :

          return str_repeat( "\0", $length );

        case 2 :

          return str_repeat( '0', $length );

        default :

          return $this->php_random_bytes( $length );

      }
    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - calculates the seconds and extra nanoseconds which will be needed by the
   * delay implementation; this function defers to do_get_delay() which can be overridden by
   * implementations.
   *
   * @param int $ns_min the total number of nanoseconds, minimum.
   *
   * @param int $ns_max the total number of nanoseconds, maximum.
   *
   * @param int $seconds the number of whole seconds to delay.
   *
   * @param int $nanoseconds the extra number of nanoseconds to delay beyond the number of
   * seconds.
   *
   * @return boolean true on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function get_delay(
    int $ns_min,
    int $ns_max,
    &$seconds,
    &$nanoseconds
  ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      assert( is_int( $ns_min ) );
      assert( is_int( $ns_max ) );

      assert( $ns_min > 0 );
      assert( $ns_max > $ns_min );

      $this->do_get_delay( $ns_min, $ns_max, $seconds, $nanoseconds );

      assert( is_int( $seconds ) );
      assert( is_int( $nanoseconds ) );

      assert( $seconds >= 0 );
      assert( $nanoseconds > 0 );

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - provides the default implementation of the delay calculations; can be
   * overridden by implementations.
   *
   * @param int $ns_min the total number of nanoseconds, minimum.
   *
   * @param int $ns_max the total number of nanoseconds, maximum.
   *
   * @param int $seconds the number of whole seconds to delay.
   *
   * @param int $nanoseconds the extra number of nanoseconds to delay beyond the number of
   * seconds.
   *
   * @return boolean true on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_get_delay( $ns_min, $ns_max, &$seconds, &$nanoseconds ) {

    try {

      $this->enter( __FUNCTION__ );

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

      return true;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - logs an error.
   *
   * @param string $message the message to log.
   *
   * @param string $file the path of the file writing the log entry.
   *
   * @param int $line the line in the file writing the log entry.
   *
   * @param string $function the name of the function writing the log entry.
   *
   * @return boolean true on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function log_error( $message, $file, $line, $function ) : bool {

    try {

      $this->enter( __FUNCTION__ );

      $result = $this->do_log_error( $message, $file, $line, $function );

      assert( is_bool( $result ) );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        // 2023-04-05 jj5 - this could infinite loop...
        //
        //$this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $message );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - provides a default implementation for log writing; can be overridden by
   * implementations.
   *
   * @param string $message the message to log.
   *
   * @param string $file the path of the file writing the log entry.
   *
   * @param int $line the line in the file writing the log entry.
   *
   * @param string $function the name of the function writing the log entry.
   *
   * @return boolean true on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected function do_log_error( $message, $file, $line, $function ) {

    try {

      $this->enter( __FUNCTION__ );

      if ( defined( 'KICKASS_CRYPTO_DISABLE_LOG' ) && KICKASS_CRYPTO_DISABLE_LOG ) {

        return false;

      }

      return $this->write_log( $message, $file, $line, $function );

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        // 2023-04-05 jj5 - this could infinite loop...
        //
        //$this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

        error_log( __FILE__ . ':' . __LINE__ . ': ' . __FUNCTION__ . '(): ' . $message );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }

  /**
   * 2023-04-07 jj5 - provides our private implementation of log writing, in case we need to use
   * it ourselves without potential interference from inheritors of our class.
   *
   * @param string $message the message to log.
   *
   * @param string $file the path of the file writing the log entry.
   *
   * @param int $line the line in the file writing the log entry.
   *
   * @param string $function the name of the function writing the log entry.
   *
   * @return boolean true on success or false on failure.
   *
   * @throws \AssertionError
   */
  protected final function write_log( $message, $file, $line, $function ) {

    return error_log( $file . ':' . $line . ': ' . $function . '(): ' . $message );

  }

  /**
   * 2023-04-07 jj5 - registers when a function gets called so as to track the depth of a function
   * on the call stack.
   *
   * @param string $function the name of the function.
   *
   * @return void
   *
   * @throws \KickassCrypto\KickassCryptoException
   */
  protected function enter( string $function ) {

    $has_exceeded_limit = false;

    if ( ( $this->active[ $function ] ?? 0 ) > KICKASS_CRYPTO_RECURSION_LIMIT ) {

      $has_exceeded_limit = true;

    }

    $this->increment_counter_internal( $this->active, $function );

    if ( ! $has_exceeded_limit ) { return; }

    $code = KICKASS_CRYPTO_EXCEPTION_RECURSION_DETECTED;

    $message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $code ] ?? null;

    assert( ! empty( $message ) );

    $this->log_error(
      KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_THROW . $message, __FILE__, __LINE__, __FUNCTION__
    );

    $previous = null;

    $data = [
      'function' => $function,
    ];

    throw new \KickassCrypto\KickassCryptoException( $message, $code, $previous, $data );

  }

  /**
   * 2023-04-07 jj5 - registers when a runction returns so as to track the depth of a function on
   * the call stack.
   *
   * @param string $function the name of the function.
   */
  protected function leave( string $function ) {

    if ( ! array_key_exists( $function, $this->active ) || $this->active[ $function ] === 0 ) {

      // 2023-04-07 jj5 - there's no point throwing an exception to alert the programmer here,
      // the call to leave() is always in a try-catch block with an ignored exception, so all
      // we can do is log.

      $message = "tried to leave unentered function '$function'.";

      // 2023-04-07 jj5 - we call write_log() directly because we don't want to recurse!
      //
      $this->log_error( $message, __FILE__, __LINE__, __FUNCTION__ );

    }

    if ( array_key_exists( $function, $this->active ) ) {

      $this->active[ $function ]--;

    }
  }

  /**
   * 2023-04-07 jj5 - this is our private implementation for incrementing a counter for a key,
   * we use it in enter().
   *
   * @param array $array a reference to the array to manage.
   *
   * @param string $key the key of the counter to increment.
   */
  private function increment_counter_internal( &$array, $key ) {

    if ( ! array_key_exists( $key, $array ) ) {

      $array[ $key ] = 0;

    }

    $array[ $key ]++;

  }
}
