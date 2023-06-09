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
 * 2023-04-04 jj5 - this implements auto-loading...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

spl_autoload_register( 'kickass_crypto_autoload' );

/**
 * 2023-04-05 jj5 - this function registered with PHP's spl_autoload_register() to provide
 * automatic loading of namespace elements.
 *
 * @param string $class_name the full name of the relevant interface, class, or trait.
 *
 * @return boolean true on success or false on failure (the return value is not used by PHP).
 */
function kickass_crypto_autoload( $class_name ) {

  $class_path = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );

  $path = __DIR__ . '/../../namespace/' . $class_path . '.php';

  if ( file_exists( $path ) ) { require_once $path; return true; }

  return false;

}
