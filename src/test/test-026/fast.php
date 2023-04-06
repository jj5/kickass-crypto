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
 * 2023-04-07 jj5 - this just test what happens if you try to call `hash()` with an invalid
 * algorithm... it throws.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';

function run_test() {

  try {

    $result = hash( 'invalid', 'whatever' );

    var_dump( $result );

    assert( false );

  }
  catch ( Throwable $ex ) {

    assert( $ex->getMessage() === 'hash(): Argument #1 ($algo) must be a valid hashing algorithm' );

    return;

  }

  assert( false );

}

main( $argv );
