<?php

// 2023-04-03 jj5 - this inc/test-host.php include file will set you up with a simple framework
// that will host a unit test for you. Basically it defines main() and then calls your test
// after configuring the environment. Your unit-test still needs to call main().

require_once __DIR__ . '/test.php';
require_once __DIR__ . '/library.php';
require_once __DIR__ . '/../src/host/unit-test.php';
