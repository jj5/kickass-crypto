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
 * 2023-04-04 jj5 - these constants are for use with the Sodium module.
 *
 * 2023-04-04 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
 * need to override any of these, for instance to test the correct handling of error scenarios,
 * please override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
 * these... please see the documentation in README.md for an explanation of these values.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-04-05 jj5 - this is the length of the passphrase (it's the $key parameter) expected by the
 * sodium_crypto_secretbox() function.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_SODIUM_PASSPHRASE_LENGTH', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );

//
// 2023-03-30 jj5 - config problems are things that can go wrong with a config file...
//

/**
 * 2023-04-05 jj5 - this happens when CONFIG_SODIUM_SECRET_CURR is not defined for the round-trip
 * use case and the Sodium module.
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SODIUM_SECRET_CURR',
  'config missing: CONFIG_SODIUM_SECRET_CURR.'
);

/**
 * 2023-04-05 jj5 - this happens when CONFIG_SODIUM_SECRET_CURR is defined for the round-trip
 * use case and the Sodium module but it is not valid.
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_CURR',
  'config invalid: CONFIG_SODIUM_SECRET_CURR.'
);

/**
 * 2023-04-05 jj5 - this happens when CONFIG_SODIUM_SECRET_PREV is defined for the round-trip
 * use case and the Sodium module but it is not valid.
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_PREV',
  'config invalid: CONFIG_SODIUM_SECRET_PREV.'
);

/**
 * 2023-04-05 jj5 - this happens when CONFIG_SODIUM_SECRET_LIST is not defined for the at-rest
 * use case and the Sodium module.
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SODIUM_SECRET_LIST',
  'config missing: CONFIG_SODIUM_SECRET_LIST.'
);

/**
 * 2023-04-05 jj5 - this happens when CONFIG_SODIUM_SECRET_LIST is defined for the at-rest
 * use case and the Sodium module but it is not valid.
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_LIST',
  'config invalid: CONFIG_SODIUM_SECRET_LIST.'
);
