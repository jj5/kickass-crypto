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
// 2023-04-03 jj5 - this is the demo code, it's for use in a web browser. Host this file then
// navigate to it so you can try out some basic encrypted round-tripping. This code tries to
// help you set up your environment. What you need is a config.php file in the project base
// directory. You can generate a demo config.php file with this command:
//
//$ [ -f config.php ] || php bin/gen-demo-config.php > config.php
//
\************************************************************************************************/

if ( file_exists( __DIR__ . '/../code/KickassCrypto.php' ) ) {

  require_once __DIR__ . '/../code/KickassCrypto.php';

}
else {

  render_head();

?>
  <p>You seem to be missing the KickassCrypto library.</p>
  <p>Make sure you can include the library from the
    <code>src/code/KickassCrypto.php</code> file.</p>
<?php

  render_foot();

}

if ( file_exists( __DIR__ . '/../../config.php' ) ) {

  require_once __DIR__ . '/../../config.php';

}

function encrypt_if_not_empty( $input ) {

  if ( empty( $input ) ) { return ''; }

  return kickass_round_trip()->encrypt( $input );

}

function decrypt_if_not_empty( $input ) {

  if ( empty( $input ) ) { return ''; }

  return kickass_round_trip()->decrypt( $input );

}

function henc( $input ) {

  static $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5;

  $text = strval( $input );

  if ( $text === '' ) { return ''; }

  $result = htmlspecialchars( $text, $flags, 'UTF-8', true );

  // 2019-07-18 jj5 - this should never happen due to ENT_SUBSTITUTE | ENT_DISALLOWED being in
  // $flags above...
  //
  if ( $result === '' ) {

    return '0x' . bin2hex( $text );

  }

  return $result;

}

function main() {

  error_reporting( E_ALL | E_STRICT );

  try {

    // 2023-04-03 jj5 - make sure our library is constructed (and therefore valid, this will
    // throw if the config is invalid)...
    //
    kickass_round_trip();

    $oldest_ciphertext = $_POST[ 'older_ciphertext' ] ?? null;
    $older_ciphertext = $_POST[ 'old_ciphertext' ] ?? null;
    $old_ciphertext = encrypt_if_not_empty( $_POST[ 'new' ] ?? null );

    $old = decrypt_if_not_empty( $old_ciphertext );
    $older = decrypt_if_not_empty( $older_ciphertext );
    $oldest = decrypt_if_not_empty( $oldest_ciphertext );

    $oldest_ciphertext = encrypt_if_not_empty( $oldest );
    $older_ciphertext = encrypt_if_not_empty( $older );
    $old_ciphertext = encrypt_if_not_empty( $old );

    render_head();

?>

<p>Submit a new value, it will be encrypted then decrypted as the old value. Keep submitting
  new values to cycle the ciphertexts.</p>

<form method="POST">
  <dl>

    <dt><label for="new">New: </label></dt>
    <dd><input id="new" name="new" value=""></dd>

    <dt><label for="old">Old:</label></dt>
    <dd><input id="old" name="old" value="<?= henc( $old ) ?>" disabled></dd>

    <dt><label for="older">Older:</label></dt>
    <dd><input id="older" name="older" value="<?= henc( $older ) ?>" disabled></dd>

    <dt><label for="oldest">Oldest:</label></dt>
    <dd><input id="oldest" name="oldest" value="<?= henc( $oldest ) ?>" disabled></dd>

    <dt><label for="submit">Submit:</label</dt>
    <dd><button id="submit" name="submit">Submit</button></dd>

  </dl>

  <h2>Old ciphertexts</h2>
  <p>You can edit these, but if you do they will not be able to be decrypted, and that will
    trigger the delay mechanism, so you will have to wait a few seconds for the response.</p>
  <p><textarea id="old_ciphertext" name="old_ciphertext"><?= henc( $old_ciphertext ) ?></textarea></p>
  <p><textarea id="older_ciphertext" name="older_ciphertext"><?= henc( $older_ciphertext ) ?></textarea></p>
  <p><textarea id="oldest_ciphertext" name="oldest_ciphertext"><?= henc( $oldest_ciphertext ) ?></textarea></p>

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

        ?>
          <p>The problem is: <?= henc( $problem ) ?>.</p>
          <p>You can generate a demo config file with this command:</p>
          <pre>[ -f config.php ] || php bin/gen-demo-config.php > config.php</p>
        <?php

        break;

      default:

        ?><p>There was a problem processing your request.</p><?php

        $message = $ex->getMessage();

        ?><p>The error message is: <?= henc( $message ) ?>.</p><?php

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
