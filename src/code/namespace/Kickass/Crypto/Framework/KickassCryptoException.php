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
// 2023-03-30 jj5 - we define an exception class for this component so that we can associate
// custom data with our exceptions... note that not all exceptions will have associated data.
//
\************************************************************************************************/

namespace Kickass\Crypto\Framework;

class KickassCryptoException extends \Exception {

  private $data;

  public function __construct( $message, $code = 0, $previous = null, $data = null ) {

    parent::__construct( $message, $code, $previous );

    $this->data = $data;

  }

  public function getData() { return $this->data; }

}