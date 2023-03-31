<?php

// 2023-03-31 jj5 - this file hosts a unit test, it's just for convenience. If you want to load
// the KickassCrypto classes yourself don't use this file.

function main( $argv ) {

  kickass_setup_unit_test_environment();

  $start = microtime( $as_float = true );

  try {

    run_test( $argv );

    $duration = microtime( $as_float = true ) - $start;

    $duration_format = number_format( $duration, 2 );

    //echo "duration: $duration_format\n";

    //KickassCrypto::ReportTelemetry();

    //echo "\n";

    exit( 0 );

  }
  catch ( Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    kickass_exit( $ex, 54 );

  }
}
