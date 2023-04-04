#!/bin/bash

##################################################################################################
#                                                                                                #
#  ____  __.__        __                           _________                        __           #
# |    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____   #
# |      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \  #
# |    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> ) #
# |____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/  #
#         \/       \/     \/     \/     \/     \/          \/        \/     |__|                 #
#                                                                                                #
#                                                                                        By jj5  #
#                                                                                                #
##################################################################################################

##################################################################################################
#
# 2023-04-05 jj5 - this script downloads phpDocumentor.phar.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;
  pushd "../" >/dev/null;
  source inc/env.sh;
  popd >/dev/null;

  mkdir -p phpdoc;

  pushd "phpdoc/" >/dev/null;

  rm -f phpDocumentor.phar

  wget https://phpdoc.org/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.2.1/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v2.9.0/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0-alpha1/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0-alpha.2/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0-alpha.2-nightly-gc1ab753/phpDocumentor.phar

  #wget https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.0.0-alpha.2-nightly-g46e6b49/phpDocumentor.phar

  chmod +x phpDocumentor.phar;

}

main "$@";
