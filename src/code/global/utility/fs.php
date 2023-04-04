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
// 2023-04-05 jj5 - file-system functions.
//
\************************************************************************************************/

/**
 * 2023-04-05 jj5 - this function will change the current directory; if there's any sort of
 * problem the program will be immediately aborted with an appropriate error level; this function
 * is for command-line scripts, not web services.
 *
 * @param string $dir the directory to change into
 * @return void
 */
function kickass_crypto_change_dir( $dir ) {

  if ( ! is_dir( $dir ) ) {

    kickass_crypto_report_error( "invalid dir: $dir" );

    exit( KICKASS_CRYPTO_EXIT_FILE_MISSING );

  }

  try {

    if ( @chdir( $dir ) ) { return; }

    kickass_crypto_report_error( "invalid dir: $dir" );

    exit( KICKASS_CRYPTO_EXIT_FILE_MISSING );

  }
  catch ( \Throwable $ex ) {

    kickass_crypto_report_error( "invalid dir: $dir" );

    exit( KICKASS_CRYPTO_EXIT_FILE_MISSING );

  }
}
