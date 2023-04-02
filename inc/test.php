<?php

// 2023-04-03 jj5 - this inc/test.php include file includes the unit test helper functions, but
// it doesn't include the unit testing host or the library. You can use this include if you want
// the test functions but don't want the test host or the library components to be automaticaly
// included.

require_once __DIR__ . '/../src/test/exit.php';
require_once __DIR__ . '/../src/test/util.php';
