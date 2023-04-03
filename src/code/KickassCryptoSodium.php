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
// 2023-04-03 jj5 - this implements support for Sodium bindings.
//
\************************************************************************************************/

(function() {

  // 2023-04-03 jj5 - this anonymous function is for validating our run-time environment. If
  // there's a problem then we exit, unless the programmer has overridden that behavior by
  // defining certain constants as detailed here:
  //
  //* to disable checks for the Sodium library functions:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_SODIUM_CHECK', true );
  //

  $errors = [];

  try {

    // 2023-04-03 jj5 - NOTE: we read in our environment settings by allowing them to be
    // overridden with constant values. We do this so that we can test our validation logic on
    // platforms which are otherwise valid.

    // 2023-04-03 jj5 - innocent until proven guilty...
    //
    $has_sodium = true;

    if ( defined( 'KICKASS_CRYPTO_TEST_HAS_SODIUM' ) ) {

      $has_sodium = KICKASS_CRYPTO_TEST_HAS_SODIUM;

    }
    else {

      $sodium_functions = [
        'sodium_crypto_secretbox',
        'sodium_crypto_secretbox_open',
      ];

      foreach ( $sodium_functions as $function ) {

        if ( ! function_exists( $function ) ) { $has_sodium = false; }

      }
    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_SODIUM_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_SODIUM_CHECK', false );

    }

    if ( ! $has_sodium ) {

      if ( KICKASS_CRYPTO_DISABLE_SODIUM_CHECK ) {

        // 2023-04-01 jj5 - the programmer has enabled sodium anyway, we will allow it.

      }
      else {

        $errors[] = "The kickass-crypto library requires the PHP Sodium library. " .
          "define( 'KICKASS_CRYPTO_DISABLE_SODIUM_CHECK', true ) to force enablement.";

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

  // 2023-04-03 jj5 - SEE: my standard error levels: https://www.jj5.net/sixsigma/Error_levels
  //
  // 2023-04-03 jj5 - the error level 40 means "invalid run-time environment, cannot run."
  //
  if ( $errors ) { exit( 40 ); }

})();

define( 'KICKASS_CRYPTO_SODIUM_PASSPHRASE_LENGTH', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );

trait KICKASS_PHP_WRAPPER_SODIUM {

  protected final function php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase ) {

    try {

      return $this->do_php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase ) {

    return sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

  }

  protected final function php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase ) {

    try {

      return $this->do_php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase ) {

    return sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase );

  }
}

abstract class KickasCryptoSodium extends KickassCrypto {

  use KICKASS_PHP_WRAPPER_SODIUM;

  // 2023-03-29 jj5 - our list of errors is private, implementations can override the access
  // interface methods defined below...
  //
  private $error_list = [];

  public function __construct() {

    parent::__construct();

  }

  public function get_error_list() {

    return $this->error_list;

  }

  public function get_error() {

    $count = count( $this->error_list );

    if ( $count === 0 ) { return null; }

    return $this->error_list[ $count - 1 ];

  }

  public function clear_error() {

    $this->error_list = [];

  }

  protected function get_const_data_format_version() {

    return $this->get_const( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_SODIUM' );

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

  protected function do_encrypt_string( string $plaintext, string $passphrase ) {

    $nonce = $this->php_random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

    $ciphertext = $this->php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

    return $nonce . $ciphertext;

  }

  protected function do_error( $error ) {

    $this->error_list[] = $error;

    // 2023-04-02 jj5 - this return value will be ignored by the caller...

    return false;

  }

  protected function do_decrypt_string( string $binary, string $passphrase ) {

    if ( ! $this->parse_binary( $binary, $nonce, $ciphertext, $tag ) ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_DATA );

    }

    $plaintext = false;

    try {

      $plaintext = $this->php_sodium_crypto_secretbox_open( $ciphertext, $nonce, $passphrase );

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

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH );

    }

    $nonce = substr( $binary, 0, $nonce_length );

    if ( strlen( $nonce ) !== $nonce_length ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2 );

    }

    $ciphertext = substr( $binary, $nonce_length, $ciphertext_length );

    if ( ! is_string( $ciphertext ) || $ciphertext === '' ) {

      return $this->error( KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2 );

    }

    return true;

  }

  protected function count_this( $caller ) {

    $this->count_function( $caller );

    if ( is_a( $this, KickassCryptoSodiumRoundTrip::class ) ) {

      $this->count_class( KickassCryptoSodiumRoundTrip::class );

    }
    else if ( is_a( $this, KickassCryptoSodiumAtRest::class ) ) {

      $this->count_class( KickassCryptoSodiumAtRest::class );

    }
    else {

      $this->count_class( get_class( $this ) );

    }
  }
}

class KickassCryptoSodiumRoundTrip extends KickasCryptoSodium {

  use KICKASS_ROUND_TRIP;

  protected function get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result === null ) { $result = $this->generate_passphrase_list(); }

    return $result;

  }
}

class KickassCryptoSodiumAtRest extends KickasCryptoSodium {

  use KICKASS_AT_REST;

  protected function get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result === null ) { $result = $this->generate_passphrase_list(); }

    return $result;

  }
}
