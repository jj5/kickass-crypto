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
// 2023-04-04 jj5 - here we include our framework components and validate our environment... if
// you want to extend the crypto framework with your own implementation this is the include file
// to use. If you just want to use either of the existing modules include either inc/sodium.php or
// inc/openssl.php instead. If you're not sure use inc/sodium.php.
//
\************************************************************************************************/

require_once __DIR__ . '/../src/code/global/php/check.php';

require_once __DIR__ . '/utility.php';

require_once __DIR__ . '/../src/code/global/autoload/autoload.php';

require_once __DIR__ . '/../src/code/global/constant/framework.php';
require_once __DIR__ . '/../src/code/global/constant/openssl.php';
require_once __DIR__ . '/../src/code/global/constant/sodium.php';

require_once __DIR__ . '/../src/code/global/environment/framework.php';
require_once __DIR__ . '/../src/code/global/environment/openssl.php';
require_once __DIR__ . '/../src/code/global/environment/sodium.php';

kickass_crypto_validate_environment();
