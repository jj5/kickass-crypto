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

(function() {

  // 2023-03-31 jj5 - this anonymous function is for validating our run-time environment. If
  // there's a problem then we exit, unless the programmer has overridden that behavior by
  // defining certain constants as detailed here:
  //
  //* to disable checks for the OpenSSL library functions:
  //
  //  define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true );
  //

  $errors = [];

  try {

    // 2023-03-31 jj5 - NOTE: we read in our environment settings by allowing them to be
    // overridden with constant values. We do this so that we can test our validation logic on
    // platforms which are otherwise valid.

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

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', false );

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

  // 2023-03-31 jj5 - SEE: my standard error levels: https://www.jj5.net/sixsigma/Error
  //
  // 2023-03-31 jj5 - the error level 40 means "invalid run-time environment, cannot run."
  //
  if ( $errors ) { exit( 40 ); }

})();

// 2023-03-29 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
// need to override any of these, for instance to test the correct handling of error scenarios,
// pelase override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
// these... please see the documentation in README.md for an explanation of these values.
//
define( 'KICKASS_CRYPTO_OPENSSL_CIPHER', 'aes-256-gcm' );
define( 'KICKASS_CRYPTO_OPENSSL_OPTIONS', OPENSSL_RAW_DATA );
define( 'KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH', 32 );
define( 'KICKASS_CRYPTO_OPENSSL_IV_LENGTH', 12 );
define( 'KICKASS_CRYPTO_OPENSSL_TAG_LENGTH', 16 );

// 2023-03-30 jj5 - config problems are things that can go wrong with a config file...
//
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_CURR',
  'config missing: CONFIG_OPENSSL_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_CURR',
  'config invalid: CONFIG_OPENSSL_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_PREV',
  'config invalid: CONFIG_OPENSSL_SECRET_PREV.'
);

define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_LIST',
  'config missing: CONFIG_OPENSSL_SECRET_LIST.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST',
  'config invalid: CONFIG_OPENSSL_SECRET_LIST.'
);

trait KICKASS_PHP_WRAPPER_OPENSSL {

  protected final function php_openssl_get_cipher_methods() {
    try {

      return $this->do_php_openssl_get_cipher_methods();

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_get_cipher_methods() {

    return openssl_get_cipher_methods();

  }

  protected final function php_openssl_cipher_iv_length( $cipher ) {

    try {

      return $this->do_php_openssl_cipher_iv_length( $cipher );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_cipher_iv_length( $cipher ) {

    return openssl_cipher_iv_length( $cipher );

  }

  protected final function php_openssl_error_string() {

    try {

      return $this->do_php_openssl_error_string();

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_error_string() {

    return openssl_error_string();

  }

  protected final function php_openssl_encrypt(
    $plaintext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    &$tag
  ) {

    $tag = null;

    try {

      return $this->do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_encrypt(
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

  protected final function php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    try {

      return $this->do_php_openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( Throwable $ex ) {

      $this->catch( $ex );

      throw $ex;

    }
  }

  protected function do_php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    return openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

  }
}


abstract class KickassCryptoOpenSsl extends KickassCrypto {

  use KICKASS_PHP_WRAPPER_OPENSSL;

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
    catch ( Throwable $ex ) {

      $this->catch( $ex );

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

    if ( ! $this->do_parse_binary( $binary, $iv, $ciphertext, $tag ) ) {

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
    catch ( Throwable $ex ) {

      $this->catch( $ex );

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

    if ( is_a( $this, KickassCryptoOpenSslRoundTrip::class ) ) {

      $this->count_class( KickassCryptoOpenSslRoundTrip::class );

    }
    else if ( is_a( $this, KickassCryptoOpenSslAtRest::class ) ) {

      $this->count_class( KickassCryptoOpenSslAtRest::class );

    }
    else {

      $this->count_class( get_class( $this ) );

    }
  }

}

// 2023-03-30 jj5 - if you need to round trip data from the web server to the client and back
// again via hidden HTML form <input> tags use this KickassCryptoOpenSslRoundTrip class. This
// class uses one or two secret keys from the config file. The first key is required and it's
// called the "current" key, its config option is 'CONFIG_OPENSSL_SECRET_CURR'; the second
// key is option and it's called the "previous" key, its config option is
// 'CONFIG_OPENSSL_SECRET_PREV'.

class KickassCryptoOpenSslRoundTrip extends KickassCryptoOpenSsl {

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

class KickassCryptoOpenSslAtRest extends KickassCryptoOpenSsl {

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
