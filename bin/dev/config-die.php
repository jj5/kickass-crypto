#!/usr/bin/env php
<?php

// 2023-04-03 jj5 - if the demo config.php file gets loaded twice it should die when it finds the
// config settings are already defined. You can make sure that happens by running this script.
// All this script does is die.

if ( file_exists( __DIR__ . '/../../config.php' ) ) {

  require __DIR__ . '/../../config.php';
  require __DIR__ . '/../../config.php';

  die( "The config.php file didn't die like it was supposed to.\n" );

}

die( "The config.php file is missing.\n" );
