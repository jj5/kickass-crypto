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
 * 2023-04-03 jj5 - this implements support for OpenSSL bindings.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\OpenSsl;

use KickassCrypto\KickassCrypto;

/**
 * 2023-04-05 jj5 - this is the base class for the OpenSSL crypto services.
 */
abstract class KickassOpenSsl extends \KickassCrypto\KickassCrypto {

  use \KickassCrypto\Traits\KICKASS_WRAPPER_PHP_OPENSSL;

  /**
   * 2023-03-29 jj5 - our list of errors is private, implementations can override the access
   * interface methods defined below...
   *
   * @var array
   */
  private $error_list = [];

  /**
   * 2023-03-30 jj5 - this is for tracking the first openssl error that occurs, if any...
   *
   * @var ?string
   */
  private $openssl_error = null;

  /**
   * 2023-04-07 jj5 - the constructor will throw if the environment is invalid.
   *
   * @throws KickassCrypto\KickassCryptoException if there are problems with the environment.
   */
  public function __construct() {

    parent::__construct();

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION' ) ) {

      /**
       * 2023-04-07 jj5 - programmers can disable cipher validation by defining this constant
       * as true.
       *
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION' ) ) {

      /**
       * 2023-04-07 jj5 - programmers can disable initialization vector length validation by
       * defining this constant as true.
       *
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION', false );

    }

    $cipher = $this->get_const_openssl_cipher();
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

    $iv_length_expected = $this->get_const_openssl_iv_length();
    $iv_length = $this->php_openssl_cipher_iv_length( $cipher );

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
   * 2023-04-07 jj5 - returns the earliest error message returned from the OpenSSL library or
   * null if no error.
   *
   * @return ?string the error from the OpenSSL library or null if no error.
   */
  public final function get_openssl_error() : ?string {

    return $this->openssl_error;

  }

  /**
   * 2023-04-07 jj5 - clears the error list and the OpenSSL error.
   *
   * @return void
   */
  protected function do_clear_error() {

    $this->error_list = [];
    $this->openssl_error = null;

  }

  /**
   * 2023-04-07 jj5 - gets the data format to use for OpenSSL; the data format is a string like
   * "KA0" which identifies the data format used by the encryption library so we can know what to
   * do when we receive it, unless overridden by implementations this function returns the
   * constant value KICKASS_CRYPTO_DATA_FORMAT_OPENSSL.
   *
   * @return string the data format string
   */
  protected function do_get_const_data_format() {

    return $this->get_const(
      'KICKASS_CRYPTO_DATA_FORMAT_OPENSSL',
      KICKASS_CRYPTO_DATA_FORMAT_OPENSSL
    );

  }

  /**
   * 2023-04-07 jj5 - gets the OpenSSL cipher algorithm to use with the the OpenSSL library;
   * unless overridden by implementations this function returns the constant value
   * KICKASS_CRYPTO_OPENSSL_CIPHER.
   *
   * @return string
   */
  protected function get_const_openssl_cipher() {

    return $this->get_const(
      'KICKASS_CRYPTO_OPENSSL_CIPHER',
      KICKASS_CRYPTO_OPENSSL_CIPHER
    );

  }

  /**
   * 2023-04-07 jj5 - gets the options to use when calling the OpenSSL library; unless
   * overridden by implementations this function returns the constant value
   * KICKASS_CRYPTO_OPENSSL_OPTIONS.
   *
   * @return int
   */
  protected function get_const_openssl_options() {

    return $this->get_const(
      'KICKASS_CRYPTO_OPENSSL_OPTIONS',
      KICKASS_CRYPTO_OPENSSL_OPTIONS
    );

  }

  /**
   * 2023-04-07 jj5 - gets the length of a valid passphrase; unless overridden by implementations
   * this function returns the constant value KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH.
   *
   * @return int
   */
  protected function get_const_passphrase_length() {

    return $this->get_const(
      'KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH',
      KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH
    );

  }

  /**
   * 2023-04-07 jj5 - gets the length of the initialization vector expected by the cipher used
   * with the OpenSSL library; unless overridden by implementations this function returns the
   * constant value KICKASS_CRYPTO_OPENSSL_IV_LENGTH.
   *
   * @return int
   */
  protected function get_const_openssl_iv_length() {

    return $this->get_const(
      'KICKASS_CRYPTO_OPENSSL_IV_LENGTH',
      KICKASS_CRYPTO_OPENSSL_IV_LENGTH
    );

  }

  /**
   * 2023-04-07 jj5 - gets the tag length expected by the cipher used with the OpenSSL library;
   * unless overridden by implementations this function returns the constant value
   * KICKASS_CRYPTO_OPENSSL_TAG_LENGTH.
   *
   * @return int
   */
  protected function get_const_openssl_tag_length() {

    return $this->get_const(
      'KICKASS_CRYPTO_OPENSSL_TAG_LENGTH',
      KICKASS_CRYPTO_OPENSSL_TAG_LENGTH
    );

  }

  /**
   * 2023-04-07 jj5 - gets the current secret key to use for encryption, and the first key to try
   * for decryption; unless overridden by implementations this function returns the constant value
   * CONFIG_OPENSSL_SECRET_CURR which should always be defined in a config file; this secret key
   * is required by the round-trip use case and not relevant to the at-rest use case.
   *
   * @return string|false the current secret key or false if it's missing.
   */
  protected function get_config_secret_curr() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_CURR' );

  }

  /**
   * 2023-04-07 jj5 - gets an optional extra secret key, known as the previous secret key, to try
   * when decrypting input data; unless overridden by implementations this function returns the
   * constant value CONFIG_OPENSSL_SECRET_PREV which can optionally be defined in the config file;
   * this secret key is optional in the round-trip use case and not relevant to the at-rest use
   * case.
   *
   * @return string|false the previous secret key or false if it's missing.
   */
  protected function get_config_secret_prev() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_PREV' );

  }

  /**
   *2023-04-07 jj5 - gets an array of strings containing secret keys to use for the at-rest use
   * case; this list is not relevant to the round-trip use case; unless overridden by
   * implementations this function returns the constant value CONFIG_OPENSSL_SECRET_LIST which
   * should be defined in the config file if you're using the library support for the OpenSSL
   * at-rest use case.
   *
   * @return array an array of strings, must contain at least one, and they must be valid secret
   * keys.
   */
  protected function get_config_secret_list() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_LIST' );

  }

  /**
   * 2023-04-07 jj5 - this does the actual work of encrypting a string by deferring to the
   * OpenSSL library to do the heavy lifting; returns false on failure or the encrypted result
   * otherwise.
   *
   * @param string $plaintext the string to encrypt.
   *
   * @param string $passphrase the passphrase to use.
   *
   * @return ?string the encrypted data on success or false on error.
   */
  protected function do_encrypt_string( $plaintext, $passphrase ) {

    $iv = $this->php_random_bytes( $this->get_const_openssl_iv_length() );

    if ( strlen( $iv ) !== $this->get_const_openssl_iv_length() ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID );

    }

    $cipher = $this->get_const_openssl_cipher();
    $options = $this->get_const_openssl_options();

    $ciphertext = false;

    try {

      $ciphertext = $this->php_openssl_encrypt(
        $plaintext, $cipher, $passphrase, $options, $iv, $tag
      );

    }
    catch ( \Throwable $ex ) {

      $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED );

    }

    if ( strlen( $tag ) !== $this->get_const_openssl_tag_length() ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID );

    }

    if ( ! $ciphertext ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2 );

    }

    // 2023-04-02 jj5 - apparently it's traditional to format these items in this order...

    return $iv . $ciphertext . $tag;

  }

  /**
   * 2023-04-07 jj5 - does the actual work of registering an error; keeps the error list up to
   * date but also keeps the OpenSSL error string up to date as well.
   *
   * @param string $error the error string to register.
   *
   * @return boolean this function always returns the value false.
   */
  protected function do_error( $error ) {

    $this->error_list[] = $error;

    while ( $openssl_error = $this->php_openssl_error_string() ) {

      assert( is_string( $openssl_error ) );

      $this->openssl_error = strval( $openssl_error );

    }

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  /**
   * 2023-04-07 jj5 - this does the actual work of decrypting a string by deferring to the
   * OpenSSL library to do the heavy lifting; returns false on failure or the decrypted result
   * otherwise.
   *
   * @param string $binary the ciphertext to decrypt.
   *
   * @param string $passphrase the passphrase to use for decryption.
   *
   * @return string|false returns the decrypted string on success or false on failure.
   */
  protected function do_decrypt_string( $binary, $passphrase ) {

    if ( ! $this->parse_binary( $binary, $iv, $ciphertext, $tag ) ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID );

    }

    $cipher = $this->get_const_openssl_cipher();
    $options = $this->get_const_openssl_options();

    $plaintext = false;

    try {

      $plaintext = $this->php_openssl_decrypt(
        $ciphertext, $cipher, $passphrase, $options, $iv, $tag
      );

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
   * @param string $iv the initialization vector extracted from the binary data.
   *
   * @param string $ciphertext the ciphertext extracted from the binary data.
   *
   * @param string $tag the authentication tag extracted from the binary data.
   *
   * @return boolean true on success or false on error.
   */
  protected function do_parse_binary( $binary, &$iv, &$ciphertext, &$tag ) {

    // 2023-04-02 jj5 - the binary data is: IV + ciphertext + tag; the IV and tag are fixed length

    $iv = false;
    $ciphertext = false;
    $tag = false;

    $binary_length = strlen( $binary );

    $iv_length = $this->get_const_openssl_iv_length();
    $tag_length = $this->get_const_openssl_tag_length();
    $ciphertext_length = $binary_length - $iv_length - $tag_length;

    $min_length = $iv_length + 1 + $tag_length;

    // 2023-04-02 jj5 - NOTE: this test obviates the possibility of the latter tests failing, but
    // I left them in anyway, just in case a bug is introduced into this part of the function...
    //
    if ( $binary_length < $min_length ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID );

    }

    $iv = substr( $binary, 0, $iv_length );

    if ( strlen( $iv ) !== $iv_length ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID_2 );

    }

    $ciphertext = substr( $binary, $iv_length, $ciphertext_length );

    if ( ! is_string( $ciphertext ) || $ciphertext === '' ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID_2 );

    }

    $tag = substr( $binary, $iv_length + $ciphertext_length );

    if ( strlen( $tag ) !== $tag_length ) {

      return $this->error(
        __FUNCTION__,
        KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID_2,
        [
          'tag_len' => strlen( $tag ),
          'expected_tag_len' => $tag_length,
        ]
      );

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

    if ( is_a( $this, KickassOpenSslRoundTrip::class ) ) {

      return $this->count_class( KickassOpenSslRoundTrip::class );

    }

    if ( is_a( $this, KickassOpenSslAtRest::class ) ) {

      return $this->count_class( KickassOpenSslAtRest::class );

    }

    return $this->count_class( get_class( $this ) );

  }
}
