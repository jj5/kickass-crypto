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
// 2023-04-03 jj5 - this script makes sure all of our exceptions are tested.
//
\************************************************************************************************/

function main( $argv ) {

  define( 'REGEX', "/.*define\( '(KICKASS_CRYPTO_EXCEPTION_[^']*)'/" );

  $error = 0;
  $const_list = [];

  $lib = realpath( __DIR__ . '/../../src/code/KickassCrypto.php' );
  $test = realpath( __DIR__ . '/../../src/unit-test/test-001/fast.php' );

  $lib_lines = file( $lib );
  $test_lines = file( $test );

  foreach ( $lib_lines as $lib_line ) {

    if ( ! preg_match( REGEX, $lib_line, $matches ) ) { continue; }

    $exception_const = $matches[ 1 ];

    if ( $exception_const === 'KICKASS_CRYPTO_EXCEPTION_MESSAGE' ) { continue; }

    $match = "$exception_const,";

    foreach ( $test_lines as $test_line ) {

      if ( strpos( $test_line, $match ) !== false ) {

        $const_list[] = $exception_const;

        continue 2;

      }
    }

    echo "untested exception: $exception_const\n";

    // 2023-04-04 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels#10
    //
    $error = 10;

  }

  if ( $error ) {

    exit( $error );

  }

  foreach ( $const_list as $const ) {

    echo $const . "\n";

  }
}

main( $argv );
