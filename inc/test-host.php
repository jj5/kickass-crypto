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
 * 2023-04-03 jj5 - this inc/test-host.php include file will set you up with a simple framework
 * that will host a unit test for you. Basically it defines main() which will call your test
 * after configuring the environment. Your unit-test still needs to call main().
 *
 * @link https://github.com/jj5/kickass-crypto
 */

require_once __DIR__ . '/../src/code/global/php/check.php';

require_once __DIR__ . '/library.php';
require_once __DIR__ . '/test.php';

require_once __DIR__ . '/../src/code/global/host/unit-test.php';
