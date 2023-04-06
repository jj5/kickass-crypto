#!/usr/bin/env php
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
// 2023-04-06 jj5 - this test is to checkout what happens when we try to recurse infinitely.
//
\************************************************************************************************/

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

class Test extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

  protected function do_encrypt( $input ) {

    return $this->encrypt( $input );

  }
}

function run_test() {

  // 2023-04-07 jj5 - the thing about XDebug is it limits the call stack to 256 functions
  /*
  if ( extension_loaded( 'xdebug' ) ) {

    echo "It makes more sense to run this script with XDebug disabled.\n";

  }
  */

  $crypto = new Test;

  $ciphertext = $crypto->encrypt( 'secret' );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $plaintext === false );

}

main( $argv );
