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

/**
 * 2023-04-03 jj5 - this script makes sure all of our errors are tested.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

require_once __DIR__ . '/../../inc/utility.php';

function main( $argv ) {

  define( 'REGEX', "/.*error\( (KICKASS_CRYPTO_ERROR_[^ ]*)/" );

  $error = 0;
  $const_list = [];
  $const_map = [];

  $lib = realpath( __DIR__ . '/../../src/code/namespace/Kickass/Crypto/KickassCrypto.php' );

  $lib_lines = file( $lib );

  foreach ( $lib_lines as $lib_line ) {

    if ( ! preg_match( REGEX, $lib_line, $matches ) ) { continue; }

    $error_const = $matches[ 1 ];

    if ( ! array_key_exists( $error_const, $const_map ) ) { $const_map[ $error_const ] = 0; }

    $const_map[ $error_const ]++;

  }

  foreach ( $const_map as $const => $count ) {

    if ( $count === 1 ) { continue; }

    echo "duplicate error: $const: $count\n";

    $error = KICKASS_CRYPTO_EXIT_CANNOT_CONTINUE;

  }

  if ( $error ) {

    exit( $error );

  }

  foreach ( $const_list as $const ) {

    echo $const . "\n";

  }
}

main( $argv );
