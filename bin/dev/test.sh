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
# 2023-04-03 jj5 - this script will run the library's unit tests. By default only the fast tests
# are run. Pass --slow to also run the slow tests, and --silly to run the silly tests. But don't
# run the silly tests... they're silly.
#
# 2023-04-03 jj5 - NOTE: if a test has a shell script, that is run. Otherwise the PHP script is
# run. Each test in a src/test/test-XXX directory can have fast, slow, and silly components,
# and they will be run in that order, if they are available.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )/../../src/test/" >/dev/null;
  pushd "../../bin/" >/dev/null;
  source inc/env.sh;
  popd >/dev/null;

  local fast=1;
  local slow=0;
  local silly=0;

  while [[ $# > 0 ]]; do
    local var="$1";
    shift;
    case $var in
      --fast) fast=1;;
      --slow) slow=1;;
      --silly) silly=1;;
      --fast-only) fast=1; slow=0; silly=0;;
      --slow-only) fast=0; slow=1; silly=0;;
      --silly-only) fast=0; slow=1; silly=1;;
    esac;
  done;

  # 2023-04-03 jj5 - before we do anything rotate our testing keys...
  #
  php ../../bin/dev/rotate-keys.php 2>/dev/null

  # 2023-04-03 jj5 - and update README.md with the "kickass" count...
  #
  bash ../../bin/dev/count-kickass.sh >/dev/null

  for test in *; do

    echo processing $test...;

    process_fast;

    process_slow;

    process_silly

  done;

}

process_fast() {

  if [ -f "$test/fast.sh" ]; then

    if [ "$fast" == '1' ]; then

      bash "$test/fast.sh";

    fi;

  elif [ -f "$test/fast.php" ]; then

    if [ "$fast" == '1' ]; then

      php "$test/fast.php";

    fi;

  fi;

}

process_slow() {

  if [ -f "$test/slow.sh" ]; then

    if [ "$slow" == '1' ]; then

      bash "$test/slow.sh";

    else

      report_skip_slow;

    fi;

  elif [ -f "$test/slow.php" ]; then

    if [ "$slow" == '1' ]; then

      php "$test/slow.php";

    else

      report_skip_slow;

    fi;

  fi;

  true;

}

process_silly() {

  if [ -f "$test/silly.sh" ]; then

    if [ "$silly" == '1' ]; then

      bash "$test/silly.sh";

    else

      report_skip_silly;

    fi;

  elif [ -f "$test/silly.php" ]; then

    if [ "$silly" == '1' ]; then

      php "$test/silly.php";

    else

      report_skip_silly;

    fi;

  fi;

  true;

}

report_skip_slow() {

  echo "  skipping slow; run with --slow to process slow running tests.";

}

report_skip_silly() {

  # 2023-03-31 jj5 - this report is disabled, because it's not worth taking people's time to tell
  # them about this. For people reading this the 'silly' tests took a really long time to run and
  # basically just tested what happened in various memory exhaustion situations. There wasn't
  # really very much learned there, just that PHP will eventually die if you run it out of memory.

  return 0;

  echo "  skipping silly; run with --silly to process silly tests.";

}

main "$@";
