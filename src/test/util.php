<?php

// 2023-03-31 jj5 - this file is for bits and pieces we want in all of our unit tests, including
// unit test which are managed by our unit test host (src/host/unit-test.php) and those which,
// for various reasons, host themselves.

function kickass_setup_unit_test_environment() {

  set_error_handler( 'kickass_handle_error' );
  error_reporting( E_ALL | E_STRICT );

}

function kickass_handle_error( $errno, $errstr, $errfile, $errline ) {

  if ( error_reporting() === 0 ) { return; }

  throw new ErrorException( $errstr, $errno, $errno, $errfile, $errline );

}
