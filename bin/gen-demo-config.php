#!/usr/bin/env php
<?php

/************************************************************************************************\

 ____  __.__        __                           _________                        __
|    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____
|      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \
|    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> )
|____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/
        \/       \/     \/     \/     \/     \/          \/        \/     |__|

                                                                                        By jj5

\************************************************************************************************/

/************************************************************************************************\
//
// 2023-03-30 jj5 - this script will generate an initial config.php file for you with initial
// keys for both round-trip and at-rest use cases. You can call it from the library base
// directory, like this:
//
// $ [ ! -f config.php ] && php bin/gen-demo-config.php > config.php
//
// Be aware that the above command will not overwrite an existing config.php. If you have an
// existing config.php you should probably rotate your keys rather than replace them. See the
// README.md file for information about how to do that.
//
\************************************************************************************************/

function gen_key() {

  return base64_encode( random_bytes( 66 ) );

}

echo "<?php\n\n";
echo "if ( defined( 'CONFIG_ENCRYPTION_SECRET_PREV' ) ) { die( \"Constant already defined.\\n\" ); }\n\n";
echo "define( 'CONFIG_ENCRYPTION_SECRET_PREV', null );\n\n";
echo "if ( defined( 'CONFIG_ENCRYPTION_SECRET_CURR' ) ) { die( \"Constant already defined.\\n\" ); }\n\n";
echo "define( 'CONFIG_ENCRYPTION_SECRET_CURR', '" . gen_key() . "' );\n\n";
echo "if ( defined( 'CONFIG_ENCRYPTION_SECRET_LIST' ) ) { die( \"Constant already defined.\\n\" ); }\n\n";
echo "define( 'CONFIG_ENCRYPTION_SECRET_LIST',\n  [\n    '" . gen_key() . "',\n  ]\n);\n\n";
