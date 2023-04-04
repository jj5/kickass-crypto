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
// 2023-04-03 jj5 - the kickass_crypto_exit() function just exits with some standard error levels.
//
// 2023-03-31 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels
//
\************************************************************************************************/

// 2023-04-04 jj5 - error level constants are defined here.
//
// 2023-04-04 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels

define( 'KICKASS_CRYPTO_EXIT_SUCCESS', 0 );
define( 'KICKASS_CRYPTO_EXIT_CANNOT_CONTINUE', 10 );
define( 'KICKASS_CRYPTO_EXIT_BAD_ENVIRONMENT', 60 );
define( 'KICKASS_CRYPTO_EXIT_FILE_MISSING', 61 );
define( 'KICKASS_CRYPTO_EXIT_PROBLEM', 80 );
define( 'KICKASS_CRYPTO_EXIT_ERROR', 81 );
define( 'KICKASS_CRYPTO_EXIT_EXCEPTION', 82 );
define( 'KICKASS_CRYPTO_EXIT_ASSERT', 83 );
define( 'KICKASS_CRYPTO_EXIT_TEST_FAILED', 84 );
define( 'KICKASS_CRYPTO_EXIT_INVALID', 89 );
define( 'KICKASS_CRYPTO_EXIT_OPTIONS_LISTED', 98 );
define( 'KICKASS_CRYPTO_EXIT_HELP', 99 );

function kickass_crypto_exit(
  $error = KICKASS_CRYPTO_EXIT_SUCCESS,
  int $default = KICKASS_CRYPTO_EXIT_PROBLEM
) {

  // 2023-04-04 jj5 - SEE: https://www.jj5.net/sixsigma/Error_levels

  // 2023-03-31 jj5 - I try to use more or less standard error levels, this is a work in
  // progress but the documentation is here...

  // 2023-03-31 jj5 - NOTE: it is possible to exit with negative numbers and values greater than
  // 255, but such things might not do what you expect, so this function will not allow it.

  if ( is_int( $error ) && $error <= 255 && $error >= 0 ) { exit( $error ); }

  if ( is_a( $error, ErrorException::class ) ) { exit( KICKASS_CRYPTO_EXIT_ERROR ); }
  if ( is_a( $error, AssertionError::class ) ) { exit( KICKASS_CRYPTO_EXIT_ASSERT ); }
  if ( is_a( $error, Throwable::class ) ) { exit( KICKASS_CRYPTO_EXIT_EXCEPTION ); }

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

    $error = "invalid error level nominated, exiting with KICKASS_CRYPTO_EXIT_INVALID.";

    $message = __FILE__ . ':' . __LINE__ . ': ' . $error;

    error_log( $message );

  }
  catch ( \Throwable $ex ) { ; }

  exit( KICKASS_CRYPTO_EXIT_INVALID );

}
