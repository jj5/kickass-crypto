#!/usr/bin/env php
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
// 2023-04-03 jj5 - this script makes sure all of our errors are tested.
//
\************************************************************************************************/

function main( $argv ) {

  $error = 0;

  $lib = realpath( __DIR__ . '/../../src/code/KickassCrypto.php' );
  $test = realpath( __DIR__ . '/../../src/unit-test/test-003/test.php' );

  $lib_lines = file( $lib );
  $test_lines = file( $test );

  foreach ( $lib_lines as $lib_line ) {

    if ( ! preg_match( "/.*define\( '(KICKASS_CRYPTO_ERROR_[^']*)'/", $lib_line, $matches ) ) { continue; }

    $error_const = $matches[ 1 ];

    $match = "$error_const,";

    foreach ( $test_lines as $test_line ) {

      if ( strpos( $test_line, $match ) !== false ) { continue 2; }

    }

    echo "untested error: $error_const\n";

    $error = 1;

  }

  exit( $error );

}

main( $argv );
