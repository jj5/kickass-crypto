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
 * 2023-04-03 jj5 - if the demo config.php file gets loaded twice it should die when it finds the
 * config settings are already defined. You can make sure that happens by running this script.
 * All this script does is die.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

if ( file_exists( __DIR__ . '/../../config.php' ) ) {

  require __DIR__ . '/../../config.php';
  require __DIR__ . '/../../config.php';

  die( "The config.php file didn't die like it was supposed to.\n" );

}

die( "The config.php file is missing.\n" );
