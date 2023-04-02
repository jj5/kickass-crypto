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
// 2023-03-31 jj5 - this file is for bits and pieces we want in all of our unit tests, including
// unit test which are managed by our unit test host (src/host/unit-test.php) and those which,
// for various reasons, host themselves.
//
\************************************************************************************************/

function kickass_setup_unit_test_environment() {

  set_error_handler( 'kickass_handle_error' );
  error_reporting( E_ALL | E_STRICT );

}

function kickass_handle_error( $errno, $errstr, $errfile, $errline ) {

  if ( error_reporting() === 0 ) { return; }

  throw new ErrorException( $errstr, $errno, $errno, $errfile, $errline );

}

function kickass_get_floats(
  &$nan = null,
  &$pos_inf = null,
  &$neg_inf = null,
  &$pos_zero = null,
  &$neg_zero = null,
  &$float_min = null,
  &$float_max = null,
  &$epslion = null
) {

  $nan = NAN;

  $pos_inf = INF;
  $neg_int = INF * -1.0;

  $pos_zero = 0.0;
  $neg_zero = 0.0 * -1.0;

  $float_min = PHP_FLOAT_MIN;
  $float_max = PHP_FLOAT_MAX;
  $epslion = PHP_FLOAT_EPSILON;

}
