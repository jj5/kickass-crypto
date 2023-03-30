#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this script lists the hash algorithms available on this computer, you can run
// it from the library base directory, like this:
//
// $ php bin/get-hash-list.php

foreach ( hash_algos() as $hash ) {

  echo "$hash\n";

}
