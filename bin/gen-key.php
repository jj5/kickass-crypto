#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this script generates a suitably long secret key, you can use it from the
// library base directory, like this:
//
// $ php bin/gen-key.php

echo base64_encode( random_bytes( 66 ) ) . "\n";
