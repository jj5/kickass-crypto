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
 * 2023-04-03 jj5 - this implements support for Sodium bindings.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\Sodium;

use KickassCrypto\KickassCrypto;

/**
 * 2023-04-05 jj5 - this is the base class for the Sodium crypto services.
 */
abstract class KickassSodium extends \KickassCrypto\KickassCrypto {

  use \KickassCrypto\Traits\KICKASS_WRAPPER_PHP_SODIUM;

  /**
   * 2023-03-29 jj5 - our list of errors is private, implementations can override the access
   * interface methods defined below...
   *
   * @var array
   */
  private $error_list = [];

  /**
   * 2023-04-07 jj5 - the constructor will throw if the environment is invalid.
   *
   * @throws KickassCrypto\KickassCryptoException if there are problems with the environment.
   */
  public function __construct() {

    parent::__construct();

  }

  /**
   * 2023-04-07 jj5 - returns the list of errors; errors are strings with a description of the
   * problem, the list is empty if there are no errors.
   *
   * @return array an array of strings containing error descriptions, the list is empty if there
   * are no errors.
   */
  protected function do_get_error_list() {

    return $this->error_list;

  }

  /**
   * 2023-04-07 jj5 - gets the most recent error as a string or returns null if there are no
   * errors.
   *
   * @return ?string the error description or null if no error.
   */
  protected function do_get_error() {

    $count = count( $this->error_list );

    if ( $count === 0 ) { return null; }

    return $this->error_list[ $count - 1 ];

  }

  /**
   * 2023-04-07 jj5 - clears the error list.
   *
   * @return void
   */
  protected function do_clear_error() {

    $this->error_list = [];

  }

  /**
   * 2023-04-07 jj5 - gets the data format to use for Sodium; the data format is a string like
   * "KAS0" which identifies the data format used by the encryption library so we can know what to
   * do when we receive it, unless overridden by implementations this function returns the
   * constant value KICKASS_CRYPTO_DATA_FORMAT_SODIUM.
   *
   * @return string the data format string
   */
  protected function do_get_const_data_format() {

    return $this->get_const( 'KICKASS_CRYPTO_DATA_FORMAT_SODIUM' );

  }

  /**
   * 2023-04-07 jj5 - gets the length of a valid passphrase; unless overridden by implementations
   * this function returns the constant value KICKASS_CRYPTO_SODIUM_PASSPHRASE_LENGTH.
   *
   * @return int
   */
  protected function get_const_passphrase_length() {

    return $this->get_const( 'KICKASS_CRYPTO_SODIUM_PASSPHRASE_LENGTH' );

  }

  /**
   * 2023-04-07 jj5 - gets the current secret key to use for encryption, and the first key to try
   * for decryption; unless overridden by implementations this function returns the constant value
   * CONFIG_SODIUM_SECRET_CURR which should always be defined in a config file; this secret key
   * is required by the round-trip use case and not relevant to the at-rest use case.
   *
   * @return string|false the current secret key or false if it's missing.
   */
  protected function get_config_secret_curr() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_CURR' );

  }

  /**
   * 2023-04-07 jj5 - gets an optional extra secret key, known as the previous secret key, to try
   * when decrypting input data; unless overridden by implementations this function returns the
   * constant value CONFIG_SODIUM_SECRET_PREV which can optionally be defined in the config file;
   * this secret key is optional in the round-trip use case and not relevant to the at-rest use
   * case.
   *
   * @return string|false the previous secret key or false if it's missing.
   */
  protected function get_config_secret_prev() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_PREV' );

  }

  /**
   *2023-04-07 jj5 - gets an array of strings containing secret keys to use for the at-rest use
   * case; this list is not relevant to the round-trip use case; unless overridden by
   * implementations this function returns the constant value CONFIG_SODIUM_SECRET_LIST which
   * should be defined in the config file if you're using the library support for the OpenSSL
   * at-rest use case.
   *
   * @return array an array of strings, must contain at least one, and they must be valid secret
   * keys.
   */
  protected function get_config_secret_list() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_LIST' );

  }

  /**
   * 2023-04-07 jj5 - this does the actual work of encrypting a string by deferring to the
   * Sodium library to do the heavy lifting; returns false on failure or the encrypted result
   * otherwise.
   *
   * @param string $plaintext the string to encrypt.
   *
   * @param string $passphrase the passphrase to use.
   *
   * @return ?string the encrypted data on success or false on error.
   */
  protected function do_encrypt_string( $plaintext, $passphrase ) {

    $nonce = $this->php_random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

    $ciphertext = $this->php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

    return $nonce . $ciphertext;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of registering an error and keeps the error list up to
   * date.
   *
   * @param string $error the error string to register.
   *
   * @return boolean this function always returns the value false.
   */
  protected function do_error( $error ) {

    $this->error_list[] = $error;

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  /**
   * 2023-04-07 jj5 - this does the actual work of decrypting a string by deferring to the
   * Sodium library to do the heavy lifting; returns false on failure or the decrypted result
   * otherwise.
   *
   * @param string $binary the ciphertext to decrypt.
   *
   * @param string $passphrase the passphrase to use for decryption.
   *
   * @return string|false returns the decrypted string on success or false on failure.
   */
  protected function do_decrypt_string( $binary, $passphrase ) {

    if ( ! $this->parse_binary( $binary, $nonce, $ciphertext, $tag ) ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID );

    }

    $plaintext = false;

    try {

      $plaintext = $this->php_sodium_crypto_secretbox_open( $ciphertext, $nonce, $passphrase );

    }
    catch ( \Throwable $ex ) {

      $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2 );

    }

    if ( ! $plaintext ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2 );

    }

    return $plaintext;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of splitting the concatenated binary data into its
   * constituent initialization vector, ciphertext, and authentication tag.
   *
   * @param string $binary the binary data to extract the pieces from.
   *
   * @param string $iv the initialization vector extracted from the binary data, this is actually
   * known as the nonce in Sodium parlance.
   *
   * @param string $ciphertext the ciphertext extracted from the binary data.
   *
   * @param string $tag the authentication tag is always false, the Sodium library doesn't use it
   * because it manages its authentication tag by itself and separately.
   *
   * @return boolean true on success or false on error.
   */
  protected function do_parse_binary( $binary, &$nonce, &$ciphertext, &$tag ) {

    // 2023-04-02 jj5 - the binary data is: nonce + ciphertext + tag; the nonce is
    // SODIUM_CRYPTO_SECRETBOX_NONCEBYTES; the tag is unused.

    $nonce = false;
    $ciphertext = false;
    $tag = false;

    $binary_length = strlen( $binary );

    $nonce_length = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    $ciphertext_length = $binary_length - $nonce_length;

    $min_length = $nonce_length + 1;

    // 2023-04-02 jj5 - NOTE: this test obviates the possibility of the latter tests failing, but
    // I left them in anyway, just in case a bug is introduced into this part of the function...
    //
    if ( $binary_length < $min_length ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID );

    }

    $nonce = substr( $binary, 0, $nonce_length );

    if ( strlen( $nonce ) !== $nonce_length ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID_2 );

    }

    $ciphertext = substr( $binary, $nonce_length, $ciphertext_length );

    if ( ! is_string( $ciphertext ) || $ciphertext === '' ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID_2 );

    }

    return true;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of registering the telemetry for a new instance of this
   * class.
   *
   * @param string $caller should be the name of the constructor, being '__construct'.
   *
   * @return int the current count of instances created for this class.
   */
  protected function do_count_this( $caller ) {

    $this->count_function( $caller );

    if ( is_a( $this, KickassSodiumRoundTrip::class ) ) {

      return $this->count_class( KickassSodiumRoundTrip::class );

    }

    if ( is_a( $this, KickassSodiumAtRest::class ) ) {

      return $this->count_class( KickassSodiumAtRest::class );

    }

    return $this->count_class( get_class( $this ) );

  }
}
