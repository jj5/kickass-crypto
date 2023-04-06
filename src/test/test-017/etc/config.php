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
 * 2023-03-31 jj5 - this config file is for testing purposes; don't copy these keys!
 *
 * @link https://github.com/jj5/kickass-crypto
 */

// 2023-04-04 jj5 - these tests use PHP serialization...
//
define( 'CONFIG_ENCRYPTION_DATA_ENCODING', 'phps' );
define( 'CONFIG_ENCRYPTION_PHPS_ENABLE', true );

define(
  'CONFIG_OPENSSL_SECRET_CURR',
  'VKhrvUzhIYYxgIWbvWati+1iMRpnFt/lZ+7ZhCPowco19kty6OtIfineUzZWMLu06knpLSpvYtv5WMw6NigW8J5a'
);

define(
  'CONFIG_OPENSSL_SECRET_LIST',
  [
    'EXoswhfHjbWRpPBNwHrYadpCIgZJbJR6vY4p1gUtwafnVB7J+sh432WCD4rD61lP8I4Te1VkjLXP+nzka2axJCq0',
  ]
);

define(
  'CONFIG_SODIUM_SECRET_CURR',
  'bfeMpaNaFOrTcbFcVyeaxm18TK1DzjX1OMRSkvdSk6DU/rq73/rAA1Hkx8wOdW/u16SWmVD1Wo4zmx2Vs8X89YMG'
);

define(
  'CONFIG_SODIUM_SECRET_LIST',
  [
    'VVOOPjiNqlbNHHKOgV7QLTYtnQdCZKXEDYuimP4Uvj0pbtNuGReJBuJtA9GZgV8Vmgge0whk2IiFio+gwGR2MfZF',
  ]
);
