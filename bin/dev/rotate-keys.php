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
// 2023-04-03 jj5 - this script changes anything which looks like a secret key to a new secret
// key.
//
\************************************************************************************************/

function main( $argv ) {

  define( 'REGEX', "/'([a-zA-Z0-9\/+]{2,}={0,2})'/" );

  process_dir( '.' );

}

function process_dir( $dir ) {

  chdir( $dir );

  //echo getcwd() . "\n";

  $files = scandir( '.' );

  foreach ( $files as $file ) {

    if ( $file[ 0 ] === '.' ) { continue; }

    if ( is_dir( $file ) ) {

      process_dir( $file );

    }
    else {

      $lines = file( $file );
      $changed = false;

      for ( $i = 0; $i < count( $lines ); $i++ ) {

        $line = $lines[ $i ];

        if ( ! preg_match( REGEX, $line, $matches ) ) { continue; }

        $match = $matches[ 1 ];

        if ( strlen( $match ) !== 88 ) { continue; }

        $changed = true;

        $new_key = base64_encode( random_bytes( 66 ) );

        $lines[ $i ] = str_replace( $match, $new_key, $line );

      }

      if ( ! $changed ) { continue; }

      echo getcwd() . "/$file\n";

      $code = implode( '', $lines );

      file_put_contents( $file, $code );

    }
  }

  chdir( '..' );

}

main( $argv );
