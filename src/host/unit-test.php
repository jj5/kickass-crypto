<?php

// 2023-03-31 jj5 - this file hosts a unit test, it's just for convenience. If you want to load
// the KickassCrypto classes yourself don't use this file.

function main( $argv ) {

  kickass_setup_unit_test_environment();

  if ( defined( 'DEBUG' ) && DEBUG ) {

    return run_test( $argv );

  }

  try {

    if ( ! defined( 'DEBUG' ) ) { define( 'DEBUG', false ); }

    run_test( $argv );

  }
  catch ( Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    kickass_exit( $ex, 54 );

  }
}
