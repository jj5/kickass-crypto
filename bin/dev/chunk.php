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
 * 2023-04-02 jj5 - this file was just me thinking about the padding length logic... padding is
 * done in KickassCrypto::do_encrypt().
 *
 * @link https://github.com/jj5/kickass-crypto
 */

define( 'JSON_INC', 8 );
define( 'CHUNK_INC', 3 );

for ( $chunk_size = 10; $chunk_size < 100; $chunk_size += CHUNK_INC ) {

  for ( $json_length = 0; $json_length < 20; $json_length += JSON_INC ) {

    $pad_length = $chunk_size - ( $json_length % $chunk_size );

    $total_length = $json_length + $pad_length;

    echo "chunk_size....: $chunk_size\n";
    echo "json_length...: $json_length\n";
    echo "pad_length....: $pad_length\n";
    echo "total_length..: $total_length\n";
    echo "\n";

    //if ( $pad_length === $chunk_size ) { exit; }

    assert( $pad_length <= $chunk_size );

  }
}
