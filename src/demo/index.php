<?php

define( 'KICKASS_CRYPTO_DEBUG', true );

require_once __DIR__ . '/../code/KickassCrypto.php';

require_once __DIR__ . '/../../config.php';

function main() {

  error_reporting( E_ALL | E_STRICT );

  try {

    $ciphertext = kickass_round_trip()->encrypt( $_POST[ 'new' ] );
    $plaintext = kickass_round_trip()->decrypt( $ciphertext );

    render_head();

    ?>

<p>Submit a new value, it will be encrypted then decrypted as the old value.</p>

<form method="POST">
  <dl>

    <dt><label for="old">Old:</label></dt>
    <dd><input id="old" name="old" value="<?= $plaintext ?>" disabled></dd>

    <dt><label for="new">New: </label></dt>
    <dd><input id="new" name="new" value=""></dd>

    <dt><label for="submit">Submit:</label</dt>
    <dd><button id="submit" name="submit">Submit</button></dd>

  </dl>
</form>

    <?php

    render_foot();

  }
  catch ( KickassException $ex ) {

    render_head();

    switch ( $ex->getCode() ) {

      case KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG:

        ?><p>The config.php file is invalid.</p><?php

        $problem = $ex->getData()[ 'problem' ] ?? null;

        ?><p>The problem is: <?= htmlentities( $problem ) ?>.</p><?php

        break;

      default:

        ?><p>There was a problem processing your request.</p><?php

        $message = $ex->getMessage();

        ?><p>The error message is: <?= htmlentities( $message ) ?>.</p><?php

    }

    render_foot();

  }
}

function render_head() {

  while ( ob_get_level() ) { ob_end_clean(); }

  ob_start();

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Kickass Crypto</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link href="/favicon.ico" rel="icon">

<style>

html {
  min-height: 101%;
}

dt {
  margin: 1em;
}

</style>

<script>

"use strict";

window.addEventListener( 'load', handle_load );

function handle_load() {

  document.getElementById( 'new' ).focus();

};

</script>

</head>
<body>
  <main>
    <h1>Kickass Crypto</h1>
    <p>This is the demo page for the
      <a href="https://github.com/jj5/kickass-crypto">Kickass Crypto library</a>.
      You can <a href="./">reload</a> this page.</p>


<?php
}

function render_foot() {
?>
  </main>
</body>
</html>
<?php
}

main();
