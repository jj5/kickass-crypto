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
// 2023-04-05 jj5 - this script is just for looking at specific cases which ordinarily will run
// in fast.php.
//
\************************************************************************************************/

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {


  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( true );
        }
        protected function do_get_config_data_encoding( $default ) {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_is_valid_data_encoding( $data_encoding ) { return false; }
        protected function do_get_config_phps_enable( $default ) { return false; }
      };
    }
  );

}

main( $argv );
