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

/**
 * 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt particularly large values;
 * as this takes a fairly long time these tests are defined in this slow.php file.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

  test_setup();

  // 2023-03-30 jj5 - can't encrypt false... because this encryption fails the delay is triggered
  // which is why this test is in this slow.php file...
  //
  test_error( false );

  kickass_crypto_get_floats(
    $nan,
    $pos_inf,
    $neg_inf,
    $pos_zero,
    $neg_zero,
    $float_min,
    $float_max,
    $epslion
  );

  // 2023-04-01 jj5 - when we switched from PHP serialization to JSON encoding we lost the
  // ability to represent some values, those are the following, which we now make sure fail as
  // that's what we now expect.
  //
  test_error( $nan );
  test_error( $pos_inf );
  test_error( [ $pos_inf ] );

  $limit = 26;

  for ( $n = 0; $n < $limit; $n++ ) {

    //echo "n: $n\n";

    test_value( str_repeat( '0', pow( 2, $n ) ) );

  }

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( str_repeat( '0', pow( 2, $limit ) ) );

  $limit = 25;
  $big_array = [];

  for ( $n = 0; $n < $limit; $n++ ) {

    $big_array[] = str_repeat( '0', pow( 2, $n ) );

    // 2023-03-31 jj5 - these are big but should still work...
    //
    test_value( $big_array );

  }

  $big_array[] = str_repeat( '0', pow( 2, $limit ) );

  // 2023-03-30 jj5 - this is too big and should fail...
  //
  test_error( $big_array );

}

main( $argv );
