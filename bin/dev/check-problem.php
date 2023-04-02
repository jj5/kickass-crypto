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
// 2023-04-03 jj5 - this script makes sure all of our config problems are tested.
//
\************************************************************************************************/

function main( $argv ) {

  define( 'REGEX', "/.*'(KICKASS_CRYPTO_CONFIG_PROBLEM_[^']*)'/" );

  $error = 0;
  $const_list = [];

  $lib = realpath( __DIR__ . '/../../src/code/KickassCrypto.php' );
  $test = realpath( __DIR__ . '/../../src/unit-test/test-002/fast.sh' );

  $lib_lines = file( $lib );
  $test_lines = file( $test );

  foreach ( $lib_lines as $lib_line ) {

    if ( ! preg_match( REGEX, $lib_line, $matches ) ) { continue; }

    $problem_const = $matches[ 1 ];

    $regex = "/$problem_const\$/";

    foreach ( $test_lines as $test_line ) {

      if ( preg_match( $regex, $test_line ) ) {

        $const_list[] = $problem_const;

        continue 2;

      }
    }

    echo "untested problem: $problem_const\n";

    $error = 1;

  }

  if ( $error ) {

    exit( $error );

  }

  foreach ( $const_list as $const ) {

    echo $const . "\n";

  }
}

main( $argv );
