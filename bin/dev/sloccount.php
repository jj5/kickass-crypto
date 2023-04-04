#!/usr/bin/env php
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
// 2023-04-05 jj5 - this script uses the `sloccount` command to generate a report.
//
\************************************************************************************************/

require_once __DIR__ . '/../../inc/utility.php';

function main( $argv ) {

  kickass_crypto_setup_environment();

  kickass_crypto_change_dir( __DIR__ . '/../../' );

  define( 'REGEX_A', '/^([^\s]*)\s+([^\s]*)\s+(.*)$/' );

  $text = `sloccount --filecount bin src 2>/dev/null`;

  $parts = get_parts( $text );

  echo "### File count\n\n";

  format_part_c( $parts[ 'c' ] );

  echo "\n";

  format_part_a( $parts[ 'a' ] );

  echo "\n";

  format_part_b( $parts[ 'b' ] );

  echo "\n";

  $text = `sloccount bin src 2>/dev/null`;

  $parts = get_parts( $text );

  echo "\n### Lines of Code\n\n";

  format_part_c( $parts[ 'c' ] );

  echo "\n";

  format_part_a( $parts[ 'a' ] );

  echo "\n";

  format_part_b( $parts[ 'b' ] );

}

function get_parts( $text ) {

  $lines = explode( "\n", $text );

  $part_a = [];
  $part_b = [];
  $part_c = [];

  $i = 0;

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line === '' ) { break; }

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line !== '' ) { break; }

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line === '' ) { break; }

    $part_a[] = $line;

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line !== '' ) { break; }

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line === '' ) { break; }

    $part_b[] = $line;

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line !== '' ) { break; }

    $i++;

  }

  for ( ;; ) {

    $line = $lines[ $i ];

    if ( $line === '' ) { break; }

    $part_c[] = $line;

    $i++;

  }

  return [
    'a' => $part_a,
    'b' => $part_b,
    'c' => $part_c,
  ];

}

function format_part_a( $part_a ) {

  // 2023-04-05 jj5 - example markdown table...
  //
  /*

## Tables

| Left columns  | Right columns |
| ------------- |:-------------:|
| left foo      | right foo     |
| left bar      | right bar     |
| left baz      | right baz     |

  */

  preg_match( REGEX_A, $part_a[ 0 ], $head_parts );

  $head_a = trim( $head_parts[ 1 ] );
  $head_b = trim( $head_parts[ 2 ] );
  $head_c = trim( $head_parts[ 3 ] );

  $max_a = strlen( $head_a );
  $max_b = strlen( $head_b );
  $max_c = strlen( $head_c );

  $cell_list = [];

  for ( $i = 1; $i < count( $part_a ); $i++ ) {

    preg_match( REGEX_A, $part_a[ $i ], $cell_parts );

    $cell_a = number_format( trim( $cell_parts[ 1 ] ) );
    $cell_b = trim( $cell_parts[ 2 ] );
    $cell_c = trim( $cell_parts[ 3 ] );

    $max_a = max( strlen( $cell_a ), $max_a );
    $max_b = max( strlen( $cell_b ), $max_b );
    $max_c = max( strlen( $cell_c ), $max_c );

    $cell_list[] = [ $cell_a, $cell_b, $cell_c ];

  }

  echo
    '| ' . pad_r( $head_a, $max_a ) . ' ' .
    '| ' . pad_r( $head_b, $max_b ) . ' ' .
    '| ' . pad_r( $head_c, $max_c ) . ' ' .
    "|\n";

  echo
    '|:' . pad_r( '', $max_a, '-' ) . ':' .
    '| ' . pad_r( '', $max_b, '-' ) . ' ' .
    '| ' . pad_r( '', $max_c, '-' ) . ' ' .
    "|\n";

  foreach ( $cell_list as $cell ) {

    echo
      '| ' . pad_l( $cell[ 0 ], $max_a ) . ' ' .
      '| ' . pad_r( $cell[ 1 ], $max_b ) . ' ' .
      '| ' . pad_r( $cell[ 2 ], $max_c ) . ' ' .
      "|\n";

  }
}

function format_part_b( $part_b ) {

  // 2023-04-05 jj5 - example markdown table...
  //
  /*

## Tables

| Left columns  | Right columns |
| ------------- |:-------------:|
| left foo      | right foo     |
| left bar      | right bar     |
| left baz      | right baz     |

  */

  $head = trim( $part_b[ 0 ] );

  $head_a = 'Language';
  $head_b = 'SLOC';
  $head_c = 'Percentage';

  $max_a = strlen( $head_a );
  $max_b = strlen( $head_b );
  $max_c = strlen( $head_c );

  $cell_list = [];

  for ( $i = 1; $i < count( $part_b ); $i++ ) {

    preg_match( REGEX_A, $part_b[ $i ], $cell_parts );

    $cell_a = rtrim( trim( $cell_parts[ 1 ] ), ':' );
    $cell_b = number_format( trim( $cell_parts[ 2 ] ) );
    $cell_c = trim( $cell_parts[ 3 ] );

    $max_a = max( strlen( $cell_a ), $max_a );
    $max_b = max( strlen( $cell_b ), $max_b );
    $max_c = max( strlen( $cell_c ), $max_c );

    $cell_list[] = [ $cell_a, $cell_b, $cell_c ];

  }

  echo "#### $head\n\n";

  echo
    '| ' . pad_r( $head_a, $max_a ) . ' ' .
    '| ' . pad_r( $head_b, $max_b ) . ' ' .
    '| ' . pad_r( $head_c, $max_c ) . ' ' .
    "|\n";

  echo
    '|:' . pad_r( '', $max_a, '-' ) . ':' .
    '| ' . pad_r( '', $max_b, '-' ) . ' ' .
    '| ' . pad_r( '', $max_c, '-' ) . ' ' .
    "|\n";

  foreach ( $cell_list as $cell ) {

    echo
      '| ' . pad_r( $cell[ 0 ], $max_a ) . ' ' .
      '| <div style="text-align:right;">' . pad_l( $cell[ 1 ], $max_b ) . '</div> ' .
      '| ' . pad_l( $cell[ 2 ], $max_c ) . ' ' .
      "|\n";

  }
}

function format_part_c( $part_c ) {

  echo "```\n";

  foreach ( $part_c as $line ) {

    if ( strpos( $line, 'SLOCCount' ) !== false ) { continue; }
    if ( strpos( $line, 'GNU GPL' ) !== false ) { continue; }
    if ( strpos( $line, 'see the documentation' ) !== false ) { continue; }

    echo $line . "\n";

  }

  echo "```\n";

}

function pad_l( $string, $length, $pad_string = ' ' ) {

  return str_pad( $string, $length, $pad_string, STR_PAD_LEFT );

}

function pad_r( $string, $length, $pad_string = ' ' ) {

  return str_pad( $string, $length, $pad_string, STR_PAD_RIGHT );

}

main( $argv );
