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
// 2023-04-03 jj5 - this implements support for OpenSSL bindings.
//
\************************************************************************************************/

namespace Kickass\Crypto\Module\OpenSsl;

use Kickass\Crypto\Framework\KickassCrypto;

abstract class KickassOpenSsl extends \Kickass\Crypto\Framework\KickassCrypto {

  use \Kickass\Crypto\Traits\KICKASS_WRAPPER_PHP_OPENSSL;

  // 2023-03-29 jj5 - our list of errors is private, implementations can override the access
  // interface methods defined below...
  //
  private $error_list = [];

  // 2023-03-30 jj5 - this is for tracking the first openssl error that occurs, if any...
  //
  private $openssl_error = null;

  public function __construct() {

    parent::__construct();

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_CIPHER_VALIDATION', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION' ) ) {

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

  protected function get_const_data_format_version() {

    return $this->get_const( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_OPENSSL' );

  }

  protected function get_const_openssl_cipher() {

    return $this->get_const( 'KICKASS_CRYPTO_OPENSSL_CIPHER' );

  }

  protected function get_const_openssl_options() {

    return $this->get_const( 'KICKASS_CRYPTO_OPENSSL_OPTIONS' );

  }

  protected function get_const_passphrase_length() {

    return $this->get_const( 'KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH' );

  }

  protected function get_const_openssl_iv_length() {

    return $this->get_const( 'KICKASS_CRYPTO_OPENSSL_IV_LENGTH' );

  }

  protected function get_const_openssl_tag_length() {

    return $this->get_const( 'KICKASS_CRYPTO_OPENSSL_TAG_LENGTH' );

  }

  protected function get_config_secret_curr() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_CURR' );

  }

  protected function get_config_secret_prev() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_PREV' );

  }

  protected function get_config_secret_list() {

    return $this->get_const( 'CONFIG_OPENSSL_SECRET_LIST' );

  }

  protected function do_encrypt_string( string $plaintext, string $passphrase ) {

    $iv = $this->php_random_bytes( $this->get_const_openssl_iv_length() );

    if ( strlen( $iv ) !== $this->get_const_openssl_iv_length() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH );

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

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED );

    }

    if ( strlen( $tag ) !== $this->get_const_openssl_tag_length() ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH );

    }

    if ( ! $ciphertext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2 );

    }

    // 2023-04-02 jj5 - apparently it's traditional to format these items in this order...

    return $iv . $ciphertext . $tag;

  }

  protected function do_error( $error ) {

    $this->error_list[] = $error;

    while ( $openssl_error = $this->php_openssl_error_string() ) {

      $this->openssl_error = $openssl_error;

    }

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  protected function do_decrypt_string( string $binary, string $passphrase ) {

    if ( ! $this->parse_binary( $binary, $iv, $ciphertext, $tag ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA );

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

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      return $this->error( KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2 );

    }

    if ( ! $plaintext ) {

      return $this->error( KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2 );

    }

    return $plaintext;

  }

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

  protected function count_this( $caller ) {

    $this->count_function( $caller );

    if ( is_a( $this, KickassOpenSslRoundTrip::class ) ) {

      $this->count_class( KickassOpenSslRoundTrip::class );

    }
    else if ( is_a( $this, KickassOpenSslAtRest::class ) ) {

      $this->count_class( KickassOpenSslAtRest::class );

    }
    else {

      $this->count_class( get_class( $this ) );

    }
  }

}
