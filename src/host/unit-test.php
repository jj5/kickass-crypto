<?php

require_once __DIR__ . '/../code/KickassCrypto.php';

function main( $argv ) {

  set_error_handler( 'handle_error' );
  error_reporting( E_ALL | E_STRICT );
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

    exit( 1 );

  }
}

function handle_error( $errno, $errstr, $errfile, $errline ) {

  throw new ErrorException( $errstr, $errno, $errno, $errfile, $errline );

}
