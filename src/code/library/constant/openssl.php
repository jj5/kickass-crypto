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
// 2023-04-04 jj5 - these constants are for use with the OpenSSL module.
//
// 2023-03-29 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
// need to override any of these, for instance to test the correct handling of error scenarios,
// pelase override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
// these... please see the documentation in README.md for an explanation of these values.
//
\************************************************************************************************/

define( 'KICKASS_CRYPTO_OPENSSL_CIPHER', 'aes-256-gcm' );
define( 'KICKASS_CRYPTO_OPENSSL_OPTIONS', OPENSSL_RAW_DATA );
define( 'KICKASS_CRYPTO_OPENSSL_PASSPHRASE_LENGTH', 32 );
define( 'KICKASS_CRYPTO_OPENSSL_IV_LENGTH', 12 );
define( 'KICKASS_CRYPTO_OPENSSL_TAG_LENGTH', 16 );

// 2023-03-30 jj5 - config problems are things that can go wrong with a config file...
//
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_OPENSSL_SECRET_CURR',
  'config missing: CONFIG_OPENSSL_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_CURR',
  'config invalid: CONFIG_OPENSSL_SECRET_CURR.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_PREV',
  'config invalid: CONFIG_OPENSSL_SECRET_PREV.'
);

define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_OPENSSL_SECRET_LIST',
  'config missing: CONFIG_OPENSSL_SECRET_LIST.'
);
define(
  'KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_LIST',
  'config invalid: CONFIG_OPENSSL_SECRET_LIST.'
);
