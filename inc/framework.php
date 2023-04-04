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
// 2023-04-01 jj5 - this inc/framework.php include file is for including the KickassCrypto client
// framework; this is the base upon which the Sodum and OpenSSL modules are based.
//
// 2023-04-01 jj5 - the PHP in this file is deliberately very simple so that it will run on old
// versions of PHP. Older versions of PHP may not be able to parse the code in
// src/code/KickassCrypto.php so we check our PHP version here before we try to load that code.
//
\************************************************************************************************/

(function() {

  if (
    ! function_exists( 'version_compare' ) ||
    ! function_exists( 'phpversion' ) ||
    version_compare( phpversion(), '7.4', '<' )
  ) {

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', false );

    }

    if ( KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK ) {

      // 2023-03-31 jj5 - the programmer has enabled this version of PHP, we will allow it.

    }
    else {

      $error = "The kickass-crypto library requires PHP version 7.4 or greater. " .
        "define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true ) to force enablement.";

      $message = __FILE__ . ':' . __LINE__ . ': ' . $error;

      if ( defined( 'STDERR' ) ) {

        fwrite( STDERR, "$message\n" );

      }
      else {

        error_log( $message );

      }

      // 2023-04-03 jj5 - I use some standard error levels... error level 40 means "invalid
      // run-time environment, cannot run."
      //
      // 2023-04-03 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels

      exit( 40 );

    }
  }
})();

require_once __DIR__ . '/../src/code/KickassCrypto.php';
