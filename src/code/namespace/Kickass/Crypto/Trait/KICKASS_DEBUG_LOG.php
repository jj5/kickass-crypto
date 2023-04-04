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
// 2023-04-02 jj5 - these traits make a bunch of assumptions about the class that hosts them.
// They've basically been designed to be in a class which extends KickassCrypto, they're not for
// use in other circumstances.
//
\************************************************************************************************/

namespace Kickass\Crypto\Trait;

trait KICKASS_DEBUG_LOG {

  // 2023-04-02 jj5 - if you include this trait logs will only be written if DEBUG is defined...

  protected function do_log_error( $message, $file, $line, $function ) {

    if ( ! $this->is_debug() ) { return false; }

    return parent::do_log_error( $message, $file, $line, $function );

  }
}

