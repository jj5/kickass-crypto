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
// 2023-04-03 jj5 - a crypto component will provide this interface...
//
// 2023-04-03 jj5 - oh man, I really wanted to use the PHP 8.0 type system, but the demo server
// for this library is still on 7.4 so no typed interface. :(
//
\************************************************************************************************/

namespace Kickass\Crypto\Contract;

interface IKickassCrypto {

  // 2023-04-03 jj5 - the list of errors which have happened since the last time clear_error()
  // was called...
  //
  public function get_error_list() : array;

  // 2023-04-03 jj5 - the most recent error; this is a string or null if no errors...
  //
  public function get_error() : ?string;

  // 2023-04-03 jj5 - this will clear the current error list...
  //
  public function clear_error() : void;

  // 2023-04-03 jj5 - this will JSON encode the input and encrypt the result; returns false on
  // error...
  //
  public function encrypt( $input );

  // 2023-04-03 jj5 - this will decrypt the ciphertext and decode it as JSON; returns false on
  // error...
  //
  public function decrypt( string $ciphertext );

  // 2023-04-03 jj5 - this will sleep for a random amount of time, from 1 millisecond to 10
  // seconds... this is called automatically on the first error as a mitigation against timing
  // attacks.
  //
  public function delay() : void;

}
