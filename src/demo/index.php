<?php

require_once __DIR__ . '/../code/KickassCrypto.php';

require_once __DIR__ . '/../../config.php';

function encrypt_if_not_null( $input ) {

  if ( $input === null || $input === '' ) { return ''; }

  $result = kickass_round_trip()->encrypt( $input );

  if ( false && ! $result ) {

    var_dump([
      'input' => $input,
      'result' => $result,
    ]);

    exit;

  }

  return $result;

}

function decrypt_if_not_null( $input ) {

  if ( $input === null || $input === '' ) { return ''; }

  $result = kickass_round_trip()->decrypt( $input );

  if ( false && ! $result ) {

    var_dump([
      'input' => $input,
      'result' => $result,
    ]);

    exit;

  }

  return $result;

}

function main() {

  error_reporting( E_ALL | E_STRICT );

  try {

    $oldest_ciphertext = $_POST[ 'older_ciphertext' ] ?? null;
    $older_ciphertext = $_POST[ 'old_ciphertext' ] ?? null;
    $old_ciphertext = encrypt_if_not_null( $_POST[ 'new' ] ?? null );

    $old = decrypt_if_not_null( $old_ciphertext );
    $older = decrypt_if_not_null( $older_ciphertext );
    $oldest = decrypt_if_not_null( $oldest_ciphertext );

    $oldest_ciphertext = encrypt_if_not_null( $oldest );
    $older_ciphertext = encrypt_if_not_null( $older );
    $old_ciphertext = encrypt_if_not_null( $old );

    render_head();

    ?>

<p>Submit a new value, it will be encrypted then decrypted as the old value. Keep submitting
  new values to cycle the ciphertexts.</p>

<form method="POST">
  <dl>

    <dt><label for="new">New: </label></dt>
    <dd><input id="new" name="new" value=""></dd>

    <dt><label for="old">Old:</label></dt>
    <dd><input id="old" name="old" value="<?= $old ?>" disabled></dd>

    <dt><label for="older">Older:</label></dt>
    <dd><input id="older" name="older" value="<?= $older ?>" disabled></dd>

    <dt><label for="oldest">Oldest:</label></dt>
    <dd><input id="oldest" name="oldest" value="<?= $oldest ?>" disabled></dd>

    <dt><label for="submit">Submit:</label</dt>
    <dd><button id="submit" name="submit">Submit</button></dd>

  </dl>

  <h2>Old ciphertexts</h2>
  <p>You can edit these, but if you do they will not be able to be decrypted, and that will
    trigger the delay mechanism, so you will have to wait a few seconds for the response.</p>
  <p><textarea id="old_ciphertext" name="old_ciphertext"><?= $old_ciphertext ?></textarea></p>
  <p><textarea id="older_ciphertext" name="older_ciphertext"><?= $older_ciphertext ?></textarea></p>
  <p><textarea id="oldest_ciphertext" name="oldest_ciphertext"><?= $oldest_ciphertext ?></textarea></p>

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
