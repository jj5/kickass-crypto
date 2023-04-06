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

/**
 * 2023-03-31 jj5 - this file is for bits and pieces we want in all of our unit tests, including
 * unit tests which are managed by our unit test host and those which, for various reasons, host
 * themselves.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-04-05 jj5 - this function will set up a test environment.
 *
 * @return void
 */
function kickass_crypto_setup_unit_test_environment() {

  set_error_handler( 'kickass_crypto_handle_error' );

  error_reporting( E_ALL | E_STRICT );

}

/**
 * 2023-04-05 jj5 - this function will generate a bunch of special purpose floating-point
 * values.
 *
 * @param float $nan the NaN (Not a Number) floating-point value
 *
 * @param float $pos_inf the positive infinity floating-point value
 *
 * @param float $neg_inf the negative infinity floating-point value
 *
 * @param float $pos_zero the positive zero floating-point value
 *
 * @param float $neg_zero the negative zero floating-point value
 *
 * @param float $float_min the minimum floating-point value
 *
 * @param float $float_max the maximum floating-point value
 *
 * @param float $epslion the smallest absolute non-zero floating-point value
 *
 * @return void
*/
function kickass_crypto_get_floats(
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
