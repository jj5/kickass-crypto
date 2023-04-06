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
 * 2023-04-04 jj5 - help with error management...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-04-05 jj5 - this function will set up a standard run-time environment.
 *
 * @return void
 */
function kickass_crypto_setup_environment() {

  set_error_handler( 'kickass_crypto_handle_error' );

  error_reporting( E_ALL | E_STRICT );

}

/**
 * 2023-04-05 jj5 - this function handles a PHP error by throwing it as an ErrorException. This
 * function is called by PHP when it's registered as an error handler with the set_error_handler()
 * function. This function doesn't throw if error reporting is disabled.
 *
 * @param int $errno the error number.
 *
 * @param string $errstr the error string.
 *
 * @param string $errfile the file the error was triggered from.
 *
 * @param string $errline the line in the file the error was triggered from.
 *
 * @throws ErrorException the PHP ErrorException class.
*/
function kickass_crypto_handle_error( $errno, $errstr, $errfile, $errline ) {

  if ( error_reporting() === 0 ) { return; }

  throw new ErrorException( $errstr, $errno, $errno, $errfile, $errline );

}
