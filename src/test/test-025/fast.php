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

/**
 * 2023-04-07 jj5 - try to leave unentered function...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

class Test extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

  public $count = 0;

  public function test() {

    return $this->leave( __FUNCTION__ );

  }

  protected function do_log_error( $message, $file, $line, $function ) {

    $this->count++;

    assert( $message === "tried to leave unentered function 'test'." );

    return true;

  }
}

function run_test() {

  $crypto = new Test;

  $crypto->test();

  assert( $crypto->count === 1 );

}

main( $argv );
