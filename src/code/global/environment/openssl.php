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
 * 2023-04-05 jj5 - environment validation for the OpenSSL module.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-03-31 jj5 - this function is for validating our run-time environment. If there's a problem
 * then we exit, unless the programmer has overridden that behavior by defining certain constants.
 *
 * - to disable checks for the OpenSSL library functions:
 *
 * define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true );
 *
 * @return void
 */
function kickass_crypto_validate_environment_openssl() {

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

      /**
       * 2023-04-05 jj5 - defines whether the OpenSSL library check is disabled or not.
       *
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', false );

    }

    if ( ! $has_openssl ) {

      if ( KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK ) {

        // 2023-04-01 jj5 - the programmer has enabled OpenSSL anyway, we will allow it.

      }
      else {

        $errors[] =
          "The kickass-crypto library requires the PHP OpenSSL library. " .
          "define( 'KICKASS_CRYPTO_DISABLE_OPENSSL_CHECK', true ) to force enablement.";

      }
    }

    foreach ( $errors as $error ) {

      $message = __FILE__ . ':' . __LINE__ . ': ' . $error;

      if ( defined( 'STDERR' ) ) {

        fwrite( STDERR, "$message\n" );

      }
      else {

        error_log( $message );

      }
    }
  }
  catch ( \Throwable $ex ) {

    try {

      error_log( __FILE__ . ':' . __LINE__ . ': ' . $ex->getMessage() );

    }
    catch ( \Throwable $ignore ) { ; }

  }

  if ( $errors ) { exit( KICKASS_CRYPTO_EXIT_BAD_ENVIRONMENT ); }

}