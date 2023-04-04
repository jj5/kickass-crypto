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
// 2023-04-05 jj5 - unit test host.
//
\************************************************************************************************/

/**
 * 2023-03-31 jj5 - this function hosts a unit test, it's just for convenience. It sets up your
 * environment and then calls run_test() which you should define. If debugging is enabled then
 * no try-catch block is used, otherwise it is. Also we make sure DEBUG is defined before we run
 * your code so you shouldn't have to worry about whether it is defined or not.
 *
 * @param array $argv the command-line options.
 * @return void
 */
function main( $argv ) {

  kickass_crypto_setup_unit_test_environment();

  if ( defined( 'DEBUG' ) && DEBUG ) {

    return run_test( $argv );

  }

  try {

    if ( ! defined( 'DEBUG' ) ) { define( 'DEBUG', false ); }

    run_test( $argv );

  }
  catch ( \Throwable $ex ) {

    fwrite( STDERR, $ex->getMessage() . "\n" );

    kickass_crypto_exit( $ex, KICKASS_CRYPTO_EXIT_EXCEPTION );

  }
}
