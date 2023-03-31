<?php

function kickass_exit( $error = 0, $default = 50 ) {

  // 2023-03-31 jj5 - I try to use more or less standard error levels, this is a work in
  // progress but the documentation is here...
  //
  // 2023-03-31 jj5 - SEE: https://www.jj5.net/sixsigma/Error

  // 2023-03-31 jj5 - NOTE: it is possible to exit with negative numbers and values greater than
  // 255, but such things might not do what you expect, so this function will not allow it.

  if ( is_int( $error ) && $error <= 255 && $error >= 0 ) { exit( $error ); }

  if ( is_a( $error, ErrorException::class ) ) { exit( 51 ); }
  if ( is_a( $error, AssertionError::class ) ) { exit( 53 ); }
  if ( is_a( $error, Throwable::class ) ) { exit( 52 ); }

  // 2023-03-31 jj5 - if the default is [0,255] we allow it...
  //
  if ( is_int( $default ) && $default <= 255 && $default >= 0  ) { exit( $default ); }

  $default = intval( $default );

  // 2023-03-31 jj5 - if we had to do an integer conversion for the default value we will only
  // allow values in the range (0,255] (i.e. zero is not allowed):
  //
  if ( is_int( $default ) && $default <= 255 && $default > 0  ) { exit( $default ); }

  // 2023-03-31 jj5 - if we landed here the programmer did something they should not have done.
  // Make some noise about it and exit with error level 59. Error level 59 is reserved
  // specifically for use in this situation.

  try {

    error_log( __FILE__ . ": invalid error level nominated, exiting with 59." );

  }
  catch ( Throwable $ex ) { ; }

  exit( 59 );

}
