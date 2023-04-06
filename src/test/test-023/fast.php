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
 * 2023-04-06 jj5 - this test just checks some sample code which is in the documentation.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';

class Test {

  public function test() {

    $result = $this->is_valid_settings( 1000, str_repeat( 'A', 5 ) );

    assert( $result === true );

    $result = $this->is_valid_settings( 10, '10' );

    assert( $result === false );

    $result = $this->is_valid_settings( 1000, str_repeat( '0', 30 ) );

    assert( $result === false );

  }

  protected final function is_valid_settings( int $setting_a, string $setting_b ) : bool {

    if ( strlen( $setting_b ) > 20 ) { return false; }

    return $this->do_is_valid_settings( $setting_a, $setting_b );

  }

  protected function do_is_valid_settings( $setting_a, $setting_b ) {

    if ( $setting_a < 100 ) { return false; }

    if ( strlen( $setting_b ) > 10 ) { return false; }

    return 1;

  }
}

function run_test() {

  $obj = new Test;

  $obj->test();

}

main( $argv );
