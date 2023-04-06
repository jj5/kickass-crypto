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
  '4VK+jn1P9/wnPP3/TdJrDGpWhFDHeC7VtxTRGp3BxQxArbw0oW4/DGXAES/ecrIFJaG/AlHD/IfHtgUsKxIV2phW'
);

define(
  'CONFIG_OPENSSL_SECRET_LIST',
  [
    'dWeSokq7y709HyKWCtp3e+y9UTdSBhk8StGM7VWOnSBguYff7bSimznb4Qo2p6UpfsjPApu3dVkWrigTHgSeXqEP',
  ]
);

define(
  'CONFIG_SODIUM_SECRET_CURR',
  'pUhRtZlEasdfmvrRw1umLMegXeGgKJHGVpRt0fUVfvL8GgxztIJzYCxgT5ze/bbn0AaRX1Oi8+VUhJqodtwfjxa2'
);

define(
  'CONFIG_SODIUM_SECRET_LIST',
  [
    '9l6gn4YQpuRrJiNEr990VNnKs2h2JLIU8F5ZRxnnJWQT4q2mtdGU5lKk9w0OgMfapmYXLhZP+QiJiLzyVr9823eO',
  ]
);
