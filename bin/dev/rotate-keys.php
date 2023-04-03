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
// 2023-04-03 jj5 - this is a development script, if you run it in production it will replace all
// of your keys. If you do that and you don't have backups there goes your data.
//
// 2023-04-03 jj5 - this script changes anything which looks like a secret key to a new secret
// key. The reason for having this script is that invariably, someone, somewhere, will copy the
// example files from our unit tests and then use them in production. We can't stop that
// happening, but we can cycle our keys from time to time as a mitigation of sorts. Of course
// it's all there in the git history, I dunno. Would it be evil to run this script automatically
// when the user loads the library on their machine..?
//
\************************************************************************************************/

function main( $argv ) {

  define( 'REGEX', "/'([a-zA-Z0-9\/+]{2,}={0,2})'/" );

  process_dir( '.' );

}

function process_dir( $dir ) {

  chdir( $dir );

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

      report( getcwd() . DIRECTORY_SEPARATOR . $file );

      $code = implode( '', $lines );

      file_put_contents( $file, $code );

    }
  }

  chdir( '..' );

}

function report( $line ) {

  fwrite( STDERR, $line . "\n" );

}

main( $argv );
