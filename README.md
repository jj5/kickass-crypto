# Kickass Crypto

A contemporary PHP cryptography library circa 2023.

This library is a wrapper around the
[PHP OpenSSL library](https://www.php.net/manual/en/book.openssl.php).

The code in this library is based on the example given in the documentation for
the PHP
[openssl_encrypt](https://www.php.net/manual/en/function.openssl-encrypt.php)
function.

This library aims to ensure that the data it encrypts is as secure as the
secret keys used to encrypt it. Also measures are taken to ensure that the
encrypted data is tamperproof.

**_This library can't solve the hard problem of key management._**

![The key's under the mat.](/jj5/kickass-crypto/raw/main/src/demo/res/key-management.jpg)

## Status of this library

**This library is a work in progress.**

I am sharing this code with friends and colleagues, soliciting as much
criticism and feedback as possible. When I feel this library is as good
as I can make it I will update this status note.

## tl;dr

Don't want to RTFM..?

```
mkdir -p kickass-demo/lib
cd kickass-demo
git clone https://github.com/jj5/kickass-crypto.git lib/kickass-crypto 2>/dev/null
php lib/kickass-crypto/bin/gen-config.php > config.php
cat > demo.php <<'EOF'
<?php
require __DIR__ . '/lib/kickass-crypto/inc/library.php';
require __DIR__ . '/config.php';
$ciphertext = kickass_round_trip()->encrypt( 'secret text' );
$plaintext = kickass_round_trip()->decrypt( $ciphertext );
echo "the secret data is: $plaintext.\n";
EOF
php demo.php
```

## Library demo

Assuming I remember to update it from time to time, there's a demo system over
here:

* [https://www.progclub.net/~jj5/kickass-crypto/](https://www.progclub.net/~jj5/kickass-crypto/)

## Supported PHP versions

This code should work on PHP 7.4 or greater. If you try to run this code on an
older version of PHP it will log an error message and exit your process.

## Supported use cases

This code supports two specific use cases:

* round-trip encryption
* at-rest encryption

Keys are managed separately and differently for each use case.

The details of how each use case is supported are documented below.

## Configuration settings

Please be advised that at the moment this code is configured directly in the
`config.php` file.

In future the `config.php` will include two separately managed config files,
being:

* etc/config-round-trip.php
* etc/config-at-rest.php

There will be management scripts for automatically rotating and provisioning
keys in these files.

Experienced Linux users know that you don't edit /etc/sudoers directly, you
edit it with `visudo` so that you can verify you haven't accidentally
introduced a syntax error and hosed your system.

I intend to provide similar scripts for editing and managing `config.php`
and other config files. So stand-by for those updates. In the mean time...
_just be very careful_.

## Service locators

This library provides two service locator functions which manage an instance of
the crypto library each, those are:

* kickass_round_trip()
* kickass_at_rest()

You can replace the instance of the service provided by the service locator
function by calling the function and passing the new instance as the sole
parameter. Ideally this library will meet your requirements out of the box
and you won't need to replace the instances provided by the service locators
by default.

## Data-format prefix

When this library encodes its ciphertext it includes a data-format prefix of
"KA1/". Future versions of this library might implement a new data-format
prefix.

When this library decodes its ciphertext it verifies the data-format prefix. At
present only "KA1/" is supported.

## Chunk size

When this library encrypts its data it pads its output up to a configurable
chunk size.

The config constant for the chunk size is `CONFIG_ENCRYPTION_CHUNK_SIZE`.

The default chunk size is 4,096 (2<sup>12</sup>).

If you wanted to increase the chunk size to 8,192 you could do that in your
`config.php` file like this:

```define( 'CONFIG_ENCRYPTION_CHUNK_SIZE', 8912 );```

## Data size limits

Before data is encrypted it is serialized then compressed. After data is
serialized it is limited to a configurable maxlimum length.

The config constant for the maximum serialization length is
`CONFIG_ENCRYPTION_SERIALIZE_LIMIT`.

The default serialization limit is 67,108,864 (2^<sup>26</sup>) characters.

It's possible to configure this serialization limit, if you need to make it
larger or smaller. Just be aware that if you make the limit too large you will
end up with memory problems and your process might get terminated.

If you wanted to decrease the serialization limit you could do that in your
`config.php` file like this:

```define( 'CONFIG_ENCRYPTION_SERIALIZE_LIMIT', pow( 2, 25 ) );```

## Data compression

After data is serialized, and before it is encrypted, it is compressed with
the PHP function
[gzdeflate](https://www.php.net/manual/en/function.gzdeflate.php) with
compression level 9. The
[gzinflate](https://www.php.net/manual/en/function.gzinflate.php) function is
used for decompression.

## Data encryption

This library actually encrypts your data twice. The process is roughly:

* serialize
* compress
* encrypt
* pad
* encrypt

## Timing attack mitigation

If an error is encountered during encryption or decryption a delay of between
1 millisecond (1ms) and 10 seconds (10s) is introduced. This is a mitigation
aginst potential timing attacks. See
[s2n and Lucky 13](https://aws.amazon.com/blogs/security/s2n-and-lucky-13/)
for discussion.

Note that avoiding timing attacks is
[hard](https://www.openwall.com/lists/oss-security/2023/01/25/3). A malicious
guest on your VPS host (or a malicious person listening to your server's fans!
😜) could figure out that your process is sleeping rather than doing actual
work.

The library includes a method called `delay`, and this method is called
automatically on the first instance of an error. The `delay` method is public
and you can call it yourself if you feel the need.

## Fail safe

The user of this library has the opportunity to override the `do_delay`
method and provide their own delay logic.

If that `do_delay` override throws an exception it will be handled and an
emergency delay will be injected.

**If you override `do_delay` and then don't actually do a delay and don't
throw an exception that would be bad. Don't do that.** (Except for in your
unit tests, it's okay to skip delay in unit tests. If you're testing the
delay in your unit tests put those tests in the slow tests.)

## Exceptions and errors

When an instance of either KickassCryptoRoundTrip or KickassCryptoAtRest is
created the configuration settings are validated. If the configuration settings
are not valid the constructor throw an exception. If the constructor succeeds
then encryption and decryption later on should also (usually) succeed. If there
are any configuration problems that will mean encryption or decryption won't
be able to succeed the constructor should throw.

This library defines its own exception class called `KickassException`. This
works like a normal Exception except that it adds a method `getData` which
can return any data associated with the exception. A `KickassException` doesn't
always have associated data.

Of course not all problems will be able to be diagnosed in advance. If the
library can't complete an encryption or decryption operation after a successful
construction it will signal the error by returning the boolean value false.
Returning false on error is a PHP idiom, and we use this idiom rather than
raising an exception to limit the possibility of an excpetion being thrown
while an encryption secret or passphrase is on the call stack. The problem
with having sensitive data on the call stack when an exception is raised is
that the data can be copied into stack traces, which can get saved, serialized,
displayed to users, logged, etc. We don't want that so we try very hard not to
raise exceptions while sensitive data might be on the stack.

If false is returned on error, one or more error messages will be added to an
internal list of errors. The caller can get the latest error by calling the
method `get_error`. If you want the full list of errors, call `get_error_list`.
If there were any errors registered by the OpenSSL library functions this
library calls to do the heavy lifting, then the last such error is available
if you call the `get_openssl_error`. You can clear the current error list and
OpenSSL error messages by calling the method `clear_error`.

## Cipher suite

This library is a wrapper around the PHP OpenSSL implementation. The cipher
suite we use is the
[AES-256-GCM](https://crypt-app.net/info/aes-256-gcm.html).

## Secret keys and passphrases

Secret keys are the secret values you keep in your `config.php` file which
will be processed and turned into passphrases for use by the OpenSSL library
functions. The library automatically handles converting secret keys into
passphrases so your only responsibility is to nominate the secret keys.

The secret keys used vary based on the use case. There are two default use
cases, known as round-trip and at-rest.

The "256" in AES-256-GCM means that this cipher suite expects 256-bit (32 byte)
passphrases. We use a hash algorithm to convert our secret keys into 256-bit
binary strings which can be used as the passphrases the cipher algorithm
expects.

The minimum secret key length required is 88 bytes. When these keys are
generated by this library they are generated with 66 bytes of random data which
is then base64 encoded.

The secret hash algorithm we use is SHA512/256. That's 256-bits worth of data
taken from the SHA512 hash of the secret key. When this hash code is applied
with raw binary output from an 88 byte base64 encoded input you should be
getting about 32 bytes of randomness for your keys.

## Initialization vector

Our AES-256-GCM cipher suite supports the use of a 12 byte initialization
vector, which we provide. The initialization vector ensures that even if you
encrypt the same values with the same passphrase the resultant ciphertext still
varies.

To understand what problem this mitigates, think about what would happen if you
were encrypting people's birthday. If you had two users with the same birthday
and you encrypted those birthdays with the same key, then both users would
have the same ciphertext for their birthdays. When this happens you can see
who has the same birthday, even when you might not know exactly when it is. The
initialization vector avoids this potential problem.

## Authentication tag

Our AES-256-GCM cipher suite supports the validation of a 16 byte
authentication tag.

The "GCM" in AES-256-GCM stands for Galois/Counter Mode. The GCM is a
Message Authentication Code (MAC) similar to a Hash-based Message
Authentication Code (HMAC) which you may have heard of before. The goal of the
GCM authentication tag is to make your encrypted data tamperproof.

## Round-trip use case

The round-trip use case is for when you want to send data to the client in
hidden HTML form &lt;input> elements and have it POSTed back later.

This use case is supported with two types of secret key.

The first key is called the _current key_ and it is required.

The second key is called the _previous key_ and it is optional.

Data is always encrypted with the current key.

Data is decrypted with the current key, and if that fails it is decrypted with
the previous key. If decryption with the previous key also fails then the data
cannot be decrypted, in that case the boolean value false will be returned to
signal the error.

When you rotate your round-trip secret keys you copy the current key into the
previous key, replacing the old previous key, and then you generate a new
current key.

The config setting for the current key is: `CONFIG_ENCRYPTION_SECRET_CURR`.

The config setting for the previous key is: `CONFIG_ENCRYPTION_SECRET_PREV`.

To encrypt round-trip data:

```
$ciphertext = kickass_round_trip()->encrypt( 'secret data' );
```

To decrypt round-trip data:

```
$plaintext = kickass_round_trip()->decrypt( $ciphertext );
```

## At-rest use case

The at-rest use case if for when you want to encrypt data for storage in a
database or elsewhere.

This use case is supported with an arbitrarily long list of secret keys.

The list must include are least one value. The first value in the list is
used for encryption. For decryption each secret key in the list is tried until
one is found that works. If none work the data cannot be decrypted and the
boolean value false is returned to signal the error.

When you rotate your at-rest secret keys you add a new master key as the first
item in the list. You need to keep at least one extra key, and you can keep
as many in addition to that as suits your purposes.

After you rotate your at-rest secret keys you should consider re-encrypting
all your existing at-rest data so that it is using the latest key. After you
have re-encrypted your at-rest data, you can remove the older key.

**Please be aware:** if you restore an old backup of your database, you will
also need to restore your old keys.

**_Be very careful that you don't lose your at-rest secret keys. If you lose
these keys you won't be able to decrypt your at-rest data._**

To encrypt at-rest data:

```
$ciphertext = kickass_at_rest()->encrypt( 'secret data' );
```

To decrypt at-test data:

```
$plaintext = kickass_at_rest()->decrypt( $ciphertext );
```

## Notes on key management

It has been noted that key management is the hardest part of cybersecurity.
**This library can't help you with that.**

Your encrypted data is only as secure as the secret keys.

If someone gets a copy of your secret keys, they will be able to decrypt your
data.

If you lose your secret keys, you won't be able to decrypt your data.

Your round-trip data is probably less essential than your at-rest data.

It's a very good idea to make sure you have backups of the secret keys for your
essential rount-trip or at-rest data. You can consider:

* off-server secret key backups (only send to trusted hosts on secure networks)
* off-site secret key backups (only send to trusted hosts on secure networks)
* hardcopy secret key backups (don't send to insecure printers!)
* hand-written secret key backups (make sure your writing is legible, and don't
leave them on your desk)

**When doing key management it is important to make sure your config files are
edited in a secure way. A syntax error in a config file could lead to a
secret key being exposed to the public web. If this happened you would have to
rotate all of your keys immediately and then destroy the old compromised keys.**

## Key coordination

When you rotate your round-trip and at-rest keys you need to make sure they
are synchronized across all of your web servers.

I intend to implement some facilities to help with key deployment, and config
file editing but those facilities are not done yet.

## Data in motion

This library supports encrypted data at-rest, and encrypted data round-trips.
Another consideration is data in motion.

Data is in motion when it moves between your web servers and your database
server. Data is also in motion when it moves between your web servers and the
clients that access them. You should use asymetric encryption for your data in
motion. Use SSL encryption support when you connect to your database, and
use HTTPS for your web clients.

## How the unit tests work

The unit tests are in the `src/unit-test/` directory, numbered sequentially.

There's some test runners in `bin/dev/`, as you can see. Read the scripts
for the gory details but in brief:

* `bin/dev/test.sh` will run the fast tests
* `bin/dev/test-all.sh` will run the fast tests and the slow tests

There are also some silly tests, but we won't talk about those. They are not
ordinarily run.

If you want to add a normal/fast test create the unit test directory in
`src/unit-test/test-XXX`, then add either `test.php` or `test.sh`. If you
create both `test.sh` will have precedence and `test.php` will be ignored.

If you want to add a slow test create the unit test directory in
`src/unit-test/test-XXX`, then add either `slow.php` or `slow.sh`. If you
create both `slow.sh` will have precedence and `slow.php` will be ignored.

See existing unit tests for examples of how to use the simple unit test
host in `src/host/unit-test.php`.

## Directory structure

* bin/: command-line commands
* bin/dev/: development scripts
* bin/dev/test-all.sh: run fast and slow unit tests
* bin/dev/test.sh: run unit tests, control with flags
* bin/gen-config.php: generate an initial config.php file
* bin/gen-key.php: generate a secret key
* bin/get-cipher-list.php: list cipher suites supported by your version of PHP
* bin/get-hash-list.php: list hash algorithms supported by your version of PHP
* doc/: additional documentation
* doc/tex: LaTeX write-up (planned)
* etc/: library configuration files (planned)
* src/: PHP source code
* src/code/: the full library
* src/demo/: a web-client for demonstration purposes
* src/host/: software hosts (presently for hosting unit-tests)
* src/unit-test/: unit tests: fast, slow, and silly; see bin/dev/test.sh
* .gitignore: the list of files ignored by git
* LICENSE: the software license
* README.md: this documentation file
* config.php: the library config file, used by demo web-client (create your own)

## Bans and restrictions

Some countries have banned the import or use of strong cryp­togra­phy, such as
256 bit AES.

Please be advised that this library does not contain cryptographic functions,
they are provided by your PHP implementation.

## License

This code is licensed under the MIT License.

## Commit messages

I should be more disciplined with my commit messages. I promise if this library
matures and gets widely used I will be more careful with my commits.

## Comments? Questions? Suggestions?

I'd love to hear from you! Hit me up at
[jj5@jj5.net](mailto:jj5@jj5.net?subject=Kickass%20Crypto).
Put "Kickass Crypto" in
the subject line to make it past my mail filters.
