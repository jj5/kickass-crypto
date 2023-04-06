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
 * 2023-04-04 jj5 - here we include our entire library including OpenSSL and Sodium components.
 *
 * 2023-04-04 jj5 - this include file includes everything except for the testing components.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

require_once __DIR__ . '/framework.php';

// 2023-04-04 jj5 - load Sodium first so that we get its service locators...
//
require_once __DIR__ . '/sodium.php';

require_once __DIR__ . '/openssl.php';
