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
// 2023-04-05 jj5 - environment validation for the framework.
//
\************************************************************************************************/

/**
 * 2023-03-31 jj5 - this function is for validating our run-time environment. If there's a problem
 * then we exit, unless the programmer has overridden that behavior by defining certain constants.
 *
 * - to disable PHP version check:
 *
 * define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true );
 *
 * - to disable PHP 64-bit word size check:
 *
 * define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true );
 *
 * @return void
 */
function kickass_crypto_validate_environment() {

  $errors = [];

  try {

    // 2023-03-31 jj5 - NOTE: we read in our environment settings by allowing them to be
    // overridden with constant values. We do this so that we can test our validation logic on
    // platforms which are otherwise valid.

    $php_version = defined( 'KICKASS_CRYPTO_TEST_PHP_VERSION' ) ?
      KICKASS_CRYPTO_TEST_PHP_VERSION :
      phpversion();

    $php_int_max = defined( 'KICKASS_CRYPTO_TEST_PHP_INT_MAX' ) ?
      KICKASS_CRYPTO_TEST_PHP_INT_MAX :
      PHP_INT_MAX;

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK' ) ) {

      /**
       * 2023-04-05 jj5 - defines whether the PHP version check is disabled or not.
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', false );

    }

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK' ) ) {

      /**
       * 2023-04-05 jj5 - defines whether the PHP word size check is disabled or not.
       * @var boolean
       */
      define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', false );

    }

    $php_version_min = '7.4';

    if ( version_compare( $php_version, $php_version_min, '<' ) ) {

      if ( KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK ) {

        // 2023-03-31 jj5 - the programmer has enabled this version of PHP, we will allow it.

      }
      else {

        $errors[] =
          "The kickass-crypto library requires PHP version $php_version_min or greater. " .
          "define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true ) to force enablement.";

      }
    }

    if ( strval( $php_int_max ) !== '9223372036854775807' ) {

      if ( KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK ) {

        // 2023-03-31 jj5 - the programmer has enabled this platform, we will allow it.

      }
      else {

        $errors[] =
          "The kickass-crypto library has only been tested on 64-bit platforms. " .
          "define( 'KICKASS_CRYPTO_DISABLE_WORD_SIZE_CHECK', true ) to force enablement.";

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
