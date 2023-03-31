<?php

function kickass_setup_unit_test_environment() {

  set_error_handler( 'kickass_handle_error' );
  error_reporting( E_ALL | E_STRICT );

}

function kickass_handle_error( $errno, $errstr, $errfile, $errline ) {

  if ( error_reporting() === 0 ) { return; }

  throw new ErrorException( $errstr, $errno, $errno, $errfile, $errline );

}
