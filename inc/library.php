<?php

// 2023-04-01 jj5 - this inc/library.php include file is for including the KickassCrypto client
// library. Just include this file, make sure your configuration constants are defined, and
// assuming the environment validation logic doesn't exit your process you will be good to go!

// 2023-04-01 jj5 - the PHP in this file is deliberately very simple so that it will run on old
// versions of PHP. Older versions of PHP may not be able to parse the code in
// src/code/KickassCrypto.php so we check our PHP version here before we try to load that code.
// Including this library via this inc/library.php include file is the best and safest way to
// include this library.

(function() {

  if ( version_compare( phpversion(), '7.4', '<' ) ) {

    $error = "The kickass-crypto library requires PHP version 7.4 or greater. " .
      "define( 'KICKASS_CRYPTO_DISABLE_PHP_VERSION_CHECK', true ) to force enablement.";

    $message = __FILE__ . ": $error";

    if ( defined( 'STDERR' ) ) {

      fwrite( STDERR, "$message\n" );

    }
    else {

      error_log( $message );

    }

    exit( 40 );

  }
})();

require_once __DIR__ . '/../src/code/KickassCrypto.php';
