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

define(
  'CONFIG_OPENSSL_SECRET_CURR',
  '454yeVijtM3VBWXgeBYoZg7wF+x0gqZhDw1hxv3kACUHUiVTPHbu8WLWHUkUkx/kbXW20bBNre6GkzpbEfvQKZZP'
);

define(
  'CONFIG_OPENSSL_SECRET_LIST',
  [
    'GiUhB8GKpCbcYbnuu8v/mYO3JKWN/LQtPz5WTBsSOXrZyC7M9rxUfXq8DkUzJaCeDs6c+mvxwCnXhG+wxQ8lJzlv',
  ]
);

define(
  'CONFIG_SODIUM_SECRET_CURR',
  'SsQT6dv5/F7CZOklx0Ko4nejhpeSxlRpSCikUN5mYdL9RnXTKw0l2iz2VqF9SFLP+eMwP7trOhNzvqcX5aObXC9S'
);

define(
  'CONFIG_SODIUM_SECRET_LIST',
  [
    'rQuFJWhMWqL8IYHbFLD25o1q4Ni/EftKUyvshw2X1n+Of3SAcqf0KLP0+xiptC8o3CYfWkyRiTPUQv1RkfPqwoA2',
  ]
);
