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
  '8JslPlV7cZSfwmuDG12VZlAvQrBudzFF/EuS/LCk+Q4KyZzv/3rinpl1M4FpHNxg3nA4hCnfWO2epQaIhUxsQEJm'
);

define(
  'CONFIG_OPENSSL_SECRET_LIST',
  [
    'wSUAq0BXxnf6ZeUuWdJ6DLh/S1pdLF0iGvhDdPKFAuLmhOlX5aCSflnQpt5EzsFiXfylc/MeuHTX+mDJ1hy6NpDB',
  ]
);

define(
  'CONFIG_SODIUM_SECRET_CURR',
  '2iN55G40g3k/mDPvxQ5yByyHA6l3vuHgFnQN25DOyYJSeftHURo+KzGcrm2WoQ3v6ZXXKKa7PabxRT8cC+oqheQY'
);

define(
  'CONFIG_SODIUM_SECRET_LIST',
  [
    'j0PkapOBIlsQH/DnQvfJ+dxAzI9sAhx6EoMnSoFudkkg61BEo2IRBJs4tC1nFeYdgwWX9ElraAbhAjM2IQwoZG38',
  ]
);
