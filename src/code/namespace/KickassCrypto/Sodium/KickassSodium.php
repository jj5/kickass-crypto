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

  // 2023-03-29 jj5 - our list of errors is private, implementations can override the access
  // interface methods defined below...
  //
  private $error_list = [];

  public function __construct() {

    parent::__construct();

  }

  protected function do_get_error_list() {

    return $this->error_list;

  }

  protected function do_get_error() {

    $count = count( $this->error_list );

    if ( $count === 0 ) { return null; }

    return $this->error_list[ $count - 1 ];

  }

  protected function do_clear_error() {

    $this->error_list = [];

    return true;

  }

  protected function do_get_const_data_format() {

    return $this->get_const( 'KICKASS_CRYPTO_DATA_FORMAT_SODIUM' );

  }

  protected function get_const_passphrase_length() {

    return $this->get_const( 'KICKASS_CRYPTO_SODIUM_PASSPHRASE_LENGTH' );

  }

  protected function get_config_secret_curr() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_CURR' );

  }

  protected function get_config_secret_prev() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_PREV' );

  }

  protected function get_config_secret_list() {

    return $this->get_const( 'CONFIG_SODIUM_SECRET_LIST' );

  }

  protected function do_encrypt_string( $plaintext, $passphrase ) {

    $nonce = $this->php_random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

    $ciphertext = $this->php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

    return $nonce . $ciphertext;

  }

  protected function do_error( $error ) {

    $this->error_list[] = $error;

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  protected function do_decrypt_string( $binary, $passphrase ) {

    if ( ! $this->parse_binary( $binary, $nonce, $ciphertext, $tag ) ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID );

    }

    $plaintext = false;

    try {

      $plaintext = $this->php_sodium_crypto_secretbox_open( $ciphertext, $nonce, $passphrase );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2 );

    }

    if ( ! $plaintext ) {

      return $this->error( __FUNCTION__, KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2 );

    }

    return $plaintext;

  }

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
