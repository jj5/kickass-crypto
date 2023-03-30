#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this script lists the OpenSSL cipher suites available on this computer, you
// can run it from the library base directory, like this:
//
// $ php bin/get-cipher-list.php

foreach ( openssl_get_cipher_methods() as $cipher ) {

  echo "$cipher\n";

}
