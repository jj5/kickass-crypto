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
 * 2023-04-04 jj5 - sets you up for debugging and unit-testing...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - these traits are defined for debugging purposes; they redefine the logging
 * function so that a log is not actually written unless the DEBUG constant is defined as true;
 * they generate single use secret keys and declare a valid environment so you don't have to
 * worry about providing a valid config file; and the delay for the minimum amount of time when
 * delay is invoked due to error.
 */
trait KICKASS_DEBUG {

  // 2023-04-02 jj5 - these traits will set you up for debugging...

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  use \KickassCrypto\Traits\KICKASS_DEBUG_KEYS;

  use \KickassCrypto\Traits\KICKASS_DEBUG_WITHOUT_DELAY;

}
