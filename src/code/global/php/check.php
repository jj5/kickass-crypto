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
// 2023-04-04 jj5 - this script makes sure we're running a supported version of PHP. Don't use
// fancy PHP features because they might not be available in older versions.
//
\************************************************************************************************/

(function() {

  $min_version = '7.4';

  if (
    ! function_exists( 'version_compare' ) ||
    ! function_exists( 'phpversion' ) ||
    version_compare( phpversion(), $min_version, '<' )
  ) {

    if ( ! defined( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK' ) ) {

      define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', false );

    }

    if ( KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK ) {

      // 2023-03-31 jj5 - the programmer has enabled this version of PHP, we will allow it.

    }
    else {

      $error = "The kickass-crypto library requires PHP version $min_version or greater. " .
        "define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true ) to force enablement.";

      $message = __FILE__ . ':' . __LINE__ . ': ' . $error;

      if ( defined( 'STDERR' ) ) {

        fwrite( STDERR, "$message\n" );

      }
      else {

        error_log( $message );

      }

      // 2023-04-03 jj5 - I use some standard error levels... error level 60 means "invalid
      // run-time environment, cannot run."
      //
      // 2023-04-03 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels#60

      exit( 60 );

    }
  }
})();