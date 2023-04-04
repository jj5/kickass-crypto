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
// key. The reason for having this script is that invariably, someone, somewhere, will copy the
// example files from our unit tests and then use them in production. And to be honest, sometimes
// I even copy them myself! We can't stop people from copying the test config files, but we can
// cycle our keys from time to time as a mitigation of sorts. Of course it's all there in the git
// history, I dunno. Would it be evil to run this script automatically when the user loads the
// library on their machine..?
//
// 2023-04-03 jj5 - this script has been updated to only run on files in src/test so that
// hopefully people don't use this script to accidentally hose their production keys.
//
\************************************************************************************************/

require_once __DIR__ . '/../../inc/utility.php';

function main( $argv ) {

  kickass_crypto_setup_environment();

  define( 'REGEX', "/'([a-zA-Z0-9\/+]{2,}={0,2})'/" );

  change_dir( __DIR__ );

  change_dir( '../../src/test' );

  process_dir( '.' );

}

function change_dir( $dir ) {

  if ( ! is_dir( $dir ) ) {

    report( "invalid dir: $dir" );

    // 2023-04-04 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels#61
    //
    exit( 61 );

  }

  try {

    chdir( $dir );

  }
  catch ( \Throwable $ex ) {

    report( "invalid dir: $dir" );

    // 2023-04-04 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels#61
    //
    exit( 61 );

  }
}

function process_dir( $dir ) {

  change_dir( $dir );

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

  change_dir( '..' );

}

function report( $line ) {

  fwrite( STDERR, $line . "\n" );

}

main( $argv );
