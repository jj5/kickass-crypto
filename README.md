# Kickass Crypto

A contemporary PHP cryptography library circa 2023.

This library is a wrapper around the
[PHP OpenSSL library](https://www.php.net/manual/en/book.openssl.php).

The code in this library is based on the example given in the documentation for
the PHP
[openssl_encrypt()](https://www.php.net/manual/en/function.openssl-encrypt.php)
function.

This library aims to ensure that the data it encrypts is as secure as the
secret keys used to encrypt it. Also measures are taken in an attempt to ensure
that the encrypted data is tamperproof.

**_This library can't solve the hard problem of key management._**

![The key's under the mat.](/src/demo/res/key-management.jpg)

## Status of this library

**This library is a work in progress.**

I am sharing this code with friends and colleagues, soliciting as much
criticism and feedback as possible. When I feel this library is as good
as I can make it I will update this status note. In the mean time
breaking changes are almost certain and encryption weaknesses are quite possible. If you find
something you think I should know about please
[let me know](mailto:jj5@jj5.net?subject=Kickass%20Crypto)!

## Why was this library written?

I wanted to use the PHP OpenSSL library to round-trip some relatively sensitive data between my
server and its clients in a relatively secure fashion.
As mentioned above I found example code in the PHP documentation for the
[openssl_encrypt()](https://www.php.net/manual/en/function.openssl-encrypt.php)
function.

Initially it wasn't clear to me how to use this code. Particularly it was difficult to figure
out what to do with the three parts: the authentication tag, the initialization vector, and the
cipher text. Eventually I figured out I could just concatenate them. But if I was to do that I
would need to standardize on their length so that I could retrieve them later...

...and then I figured it would be better to mask my actual data size by padding it to fixed
lengths at certain boundaries, so I did that...

...and I wanted to support rich data which demanded some form of serialization. Initially I was using
the PHP [serialize()](https://www.php.net/manual/en/function.serialize.php) function but that
was changed later to
[json_encode()](https://www.php.net/manual/en/function.json-encode.php).

The example code didn't indicate anything at all about how to rotate keys in a supported fashion.
So I came up with the two use cases supported by this library with different approaches to key
management for round-trip and at-rest scenarios. This library lets you rotate in new keys while
maintaining support for older keys, as you are likely wont to do.

Then I layered in a careful approach to exception handling and error reporting, some testing,
timing attack mitigation, and things like that.

Basically this whole library was just everything I felt like I had to do so that I could actually
use the built-in PHP OpenSSL library implementation.

I don't consider this library _rolling my own crypto_, rather I think of it as _figuring out how
to actually use OpenSSL_. If I've made any mistakes, obvious or otherwise, I would
[really appreciate hearing about it](mailto:jj5@jj5.net?subject=Kickass%20Crypto).

## tl;dr

Don't want to RTFM..? And here I am, writing all this stuff... sheesh.

```
mkdir -p kickass-demo/lib
cd kickass-demo
git clone https://github.com/jj5/kickass-crypto.git lib/kickass-crypto 2>/dev/null
php lib/kickass-crypto/bin/gen-config.php > config.php
cat > demo.php <<'EOF'
<?php
require_once __DIR__ . '/lib/kickass-crypto/inc/library.php';
require_once __DIR__ . '/config.php';
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

This code should work on PHP 7.4 or greater (but so far I have only actually tested it on
PHP 8.1.2). If you try to run this code on an older version of PHP it will log an error message
and exit your process.

## Supported platforms

This code assumes it's running on a 64-bit platform. It also assumes your PHP
installation includes the bundled OpenSSL library.

Actually this code does a little more than assume it's on a 64-bit platform with the OpenSSL
library, it actually makes an attempt to ensure those things are true. If they're not an error
is logged and the process is exited.

I believe this code should run in any PHP environment, but I have only tested it on Linux. If you
have had success on MacOS or Windows I would be happy to hear about it.

Shell scripts are written for bash. If you don't have bash you may need to
port.

## Supported use cases

This code supports two specific use cases:

* round-trip encryption
* at-rest encryption

Keys are managed separately and differently for each use case.

The details of how each use case is supported are documented below.

Using this library for at-rest encryption is generally a bigger risk and a
bigger commitment than using it simply for round-trip encryption. If you
lose your round-trip encryption keys or are forced to rotate them urgently
that will likely be less of a problem than if something similar happened with
your at-rest keys.

The main use case for which this library was developed was to support
round-tripping a few kilobytes of data containing mildly sensitive but
not mission critical row version numbers for optimistic concurrency control.
As compared with the alternative (not encrypting or tamperproofing the
optimistic concurrency control data) the use of this library is an improvement.
Whether it's really suitable in other applications is an open question, I'm
not sure. Certainly you shouldn't use this library if it doesn't provide the
level of security you require.

## Configuration settings

Please be advised that at the moment this code is configured directly in the
`config.php` file.

In future the `config.php` file will include two separately managed config files,
being:

* etc/keys-round-trip.php
* etc/keys-at-rest.php

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

## Data encryption

The encryption process is roughly:

* JSON encode
* prefix with JSON data length
* pad with random data
* encrypt with AES-256-GCM using the OpenSSL library
* concatenate authentication tag, initialization vector, and cipher text
* encode as base64
* prefix with data-format indicator

## Data-format prefix

When this library encodes its ciphertext it includes a data-format prefix of "KA0/".

The zero in "KA0" is for _version zero_, which is intended to imply that
_the interface is unstable and may change_.

Future versions of this library might implement a new data-format prefix for a stable data format.

When this library decodes its ciphertext it verifies the data-format prefix. At present only
"KA0/" is supported.

## Data format

The KA0 data format, mentioned above, presently implies the following:

After JSON encoding (discussed in the following section) padding is done and the data length is
prefixed. Before encryption the message is formatted like this:

```
$message = $json_data_length . '|' . $json . $this->get_padding( $pad_length );
```

The reason for the padding is to obscure the actual data size. Padding is done in up to 4KiB
boundaries, which we call chunks. The chunk size is configurable and the default may change
in future.

The message is then encrypted with AES-256-GCM and the authentication tag, initialization vector,
and cipher text are concatenated together, like this:

```
$tag . $iv . $ciphertext
```

Then everything is base64 encoded with the PHP
[base64_encode()](https://www.php.net/manual/en/function.base64-encode.php) function.
The decryption process expects to find the 16 byte
authentication tag, the 12 byte initialization vector, and the ciphertext. After decrypting the
ciphertext the library expects to find the size of the JSON data as an ASCII string representing
a decimal value, followed by a single pipe character, followed by the JSON, and then the padding.
The library can then remove the JSON from its padding and take care of the rest of the decoding.

## JSON encoding and decoding

Prior to encryption input data is encoded as JSON using the PHP
[json_encode()](https://www.php.net/manual/en/function.json-encode.php) function. Initially this
library used the PHP
[serialize()](https://www.php.net/manual/en/function.serialize.php) function but apparently that
can lead to some code-execution scenarios (I'm not sure on the details) so it was decided that
JSON encoding was safer. Thus, now, we use JSON encoding instead.

The use of JSON as the data encoding format has some minor implications
concerning the values we can support. Particularly we can't encode object instances that can
later be decoded back to object instances (if the objects implement the JsonSerializable interface
they can be serialized as data, but those will only be decoded back to PHP arrays, not the PHP
objects from which they came), and some odd floating point values can't be represented (i.e. NaN,
Pos Inf, Neg Info, and Neg Zero).

By default these options are used for JSON encoding:

```
JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
```

But these options won't affect an implementation's ability to decode the JSON. Implementations
can fine tune the JSON encoding and decoding if necessary by overriding the data_encode() and
data_decode() methods. Alternatively you can nominate the JSON encoding and decoding options
in your `config.php` file with the `CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS` and
`CONFIG_ENCRYPTION_JSON_DECODE_OPTIONS` constants, for example:

```
define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
define( 'CONFIG_ENCRYPTION_JSON_ENCODE_OPTIONS', JSON_THROW_ON_ERROR );
```

This library should work regardless of whether `JSON_THROW_ON_ERROR` is specified or not.

If you specify `JSON_PARTIAL_OUTPUT_ON_ERROR` in your JSON encoding options your data may silently
become invalid, so do that at your own risk. Perhaps counter-intuitively I have found that
enabling `JSON_PARTIAL_OUTPUT_ON_ERROR` is the least worst strategy because at least in that case
you get _something_. If you don't enable `JSON_PARTIAL_OUTPUT_ON_ERROR` if any part of your input
can't be encoded (such as when you have binary strings that aren't in a valid encoding such as
UTF-8) then the whole of the data is removed. With `JSON_PARTIAL_OUTPUT_ON_ERROR` only the
unrepresentable portion is omitted. At the moment `JSON_PARTIAL_OUTPUT_ON_ERROR` is not
automatically specified, but this is something I might revisit in future.

If you use any of these JSON encoding/decoding options you might very well end up having a bad
time:

* `JSON_NUMERIC_CHECK`
* `JSON_INVALID_UTF8_IGNORE`
* `JSON_INVALID_UTF8_SUBSTITUTE`

## Chunk size

When this library encrypts its data it pads its output up to a configurable
chunk size.

The config constant for the chunk size is `CONFIG_ENCRYPTION_CHUNK_SIZE`.

The default chunk size is 4,096 (2<sup>12</sup>).

If you wanted to increase the chunk size to 8,192 you could do that in your
`config.php` file like this:

```define( 'CONFIG_ENCRYPTION_CHUNK_SIZE', 8912 );```

## Encryptable values

So long as the data size limits are observed (these are discussed next), this
library can encrypt anything which can be encoded as JSON by PHP.

This includes a variety of things, such as:

* the boolean value true
* signed 64-bit integers
* some floats, including: PHP_FLOAT_MIN, PHP_FLOAT_MAX, and PHP_FLOAT_EPSILON
* strings; but only ones in a valid string encoding, not PHP byte arrays
* arrays; both associative and indexed
* combinations of the above

Things that can't be supported with JSON:

* very large unsigned integers
* floats which can be represented as integers, e.g. 0.0, 1.0, etc; these can be encoded but get
decoded as integers not doubles
* some special floats, including: NaN, Pos Inf, Neg Inf, and Neg Zero
* objects (except if they implement the JsonSerializable interface)

Note that the boolean value false cannot be encrypted. It's not because we
couldn't encrypt it, it's because we return it when decryption fails. So we
refuse to encrypt false so that it can't be confused with an error upon
decryption.

If you need to encrypt the boolean value false consider either putting it in an array, like this:

```
$input = [ 'value' => false ];
```

Or encoding as JSON, like this:

```
$input = json_encode( false );
```

If you do either of those things you will be able to encrypt your value.

It's worth pointing out that in PHP "strings" are essentially byte arrays, which means they can
contain essentially "binary" data. Such binary data cannot be represented as JSON however. If
you need to handle binary data the best way is probably to encoded it as base64 with
[base64_encode()](https://www.php.net/manual/en/function.base64-encode.php)
or hexadecimal with
[bin2hex()](https://www.php.net/manual/en/function.bin2hex.php) and then encrypt that.

In future the ability to work with data that isn't always JSON encoded might be added to this
library. Let me know if that's a feature you care to have.

## Data size limits

After data is encoded as JSON it is limited to a configurable maximum length.

The config constant for the maximum JSON encoding length is
`CONFIG_ENCRYPTION_DATA_ENCODING_LIMIT`.

The default data encoding limit is 67,108,864 (2^<sup>26</sup>) bytes, which
is roughly 67 MB.

It's possible to configure this data encoding limit, if you need to make it
larger or smaller. Just be aware that if you make the limit too large you will
end up with memory problems and your process might get terminated.

If you wanted to decrease the data encoding limit you could do that in your
`config.php` file like this:

```define( 'CONFIG_ENCRYPTION_DATA_ENCODING_LIMIT', pow( 2, 25 ) );```

## Data compression

This library does _not_ compress input data, because compression can introduce cryptographic
weaknesses, such as in the
[CRIME SSL/TLS attack](https://www.acunetix.com/vulnerabilities/web/crime-ssl-tls-attack/).

The problem is that if the attacker can modify some of the plain text they can find out if the
data they input exists in other parts of the plain text, because if they put in a value and the
result is smaller that's because it exists in the part of the plain text they didn't know, but do
now!

**_It's very important that you don't compress data that an attacker can supply with other data
that is secret. It's best just not to compress at all._**

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

This library includes a method called `delay`, and this method is called
automatically on the first instance of an error. The `delay` method does what is says on the tin:
it injects a random delay into the process. The `delay` method is public
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
are not valid the constructor will throw an exception. If the constructor succeeds
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
while an encryption secret or passphrase is on the call stack.

The problem with having sensitive data on the call stack when an exception is raised is
that the data can be copied into stack traces, which can get saved, serialized,
displayed to users, logged, etc. We don't want that so we try very hard not to
raise exceptions while sensitive data might be on the stack.

If false is returned on error, one or more error messages will be added to an
internal list of errors. The caller can get the latest error by calling the
method `get_error`. If you want the full list of errors, call `get_error_list`.
If there were any errors registered by the OpenSSL library functions (which this
library calls to do the heavy lifting), then the last such error is available
if you call the `get_openssl_error`. You can clear the current error list and
OpenSSL error message by calling the method `clear_error`.

## Cipher suite

This library is a wrapper around the PHP OpenSSL implementation. The cipher
suite we use is
[AES-256-GCM](https://crypt-app.net/info/aes-256-gcm.html).
That's
[Advanced_Encryption_Standard](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption with
[Galois/Counter_Mode](https://en.wikipedia.org/wiki/Galois/Counter_Mode)
authentication and integrity checking.

## Secret keys and passphrases

Secret keys are the secret values you keep in your `config.php` file which
will be processed and turned into passphrases for use by the OpenSSL library
functions. This library automatically handles converting secret keys into
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

Our AES-256-GCM cipher suite supports the use of a 12 byte
[initialization vector](https://en.wikipedia.org/wiki/Initialization_vector),
which we provide. The initialization vector ensures that even if you
encrypt the same values with the same passphrase the resultant ciphertext still
varies.

To understand what problem this mitigates, think about what would happen if you
were encrypting people's birthday. If you had two users with the same birthday
and you encrypted those birthdays with the same key, then both users would
have the same ciphertext for their birthdays. When this happens you can see
who has the same birthday, even when you might not know exactly when it is. The
initialization vector avoids this potential problem.

## Authentication tag

Our AES-256-GCM cipher suite supports the validation of a 16 byte authentication tag.

The "GCM" in AES-256-GCM stands for
[Galois/Counter_Mode](https://en.wikipedia.org/wiki/Galois/Counter_Mode). The GCM is a
[Message Authentication Code](https://en.wikipedia.org/wiki/Message_authentication_code)
(MAC) similar to a
[Hash-based Message Authentication Code](https://en.wikipedia.org/wiki/HMAC)
(HMAC) which you may have heard of before. The goal of the
GCM authentication tag is to make your encrypted data
[tamperproof](https://en.wikipedia.org/wiki/Tamperproofing).

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

The list must include at least one value. The first value in the list is
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

If someone gets a copy of your encrypted data now, they can keep it and decrypt it if they get a
copy of your secret keys in the future. So your keys don't have to be only secret now, but they
have to be secret for all time.

If you lose your secret keys, you won't be able to decrypt your data.

Your round-trip data is probably less essential than your at-rest data.

It's a very good idea to make sure you have backups of the secret keys for your
essential rount-trip or at-rest data. You can consider:

* off-server secret key backups (only send to trusted hosts on secure networks)
* off-site secret key backups (only send to trusted hosts on secure networks)
* air-gapped secret key backups (transferred to storage via secure physical media)
* hardcopy secret key backups (don't send to insecure printers!)
* hand-written secret key backups (make sure your writing is legible, and don't
leave them on your desk)

**When doing key management it is important to make sure your config files are
edited in a secure way. A syntax error in a config file could lead to a
secret key being exposed to the public web. If this happened you would have to
rotate all of your keys immediately and then destroy the old compromised keys.**

It would be a good idea to stand ready to do a key rotation in an automated and tested fashion
immediately in case of emergency.

## Key coordination

When you rotate your round-trip and at-rest keys you need to make sure they
are synchronized across all of your web servers.

I intend to implement some facilities to help with key deployment and config
file editing but those facilities are not done yet.

## Data in motion

This library supports encrypted data at-rest, and encrypted data round-trips.
Another consideration is data in motion.

Data is in motion when it moves between your web servers and your database
server. Data is also in motion when it moves between your web servers and the
clients that access them. You should use asymetric encryption for your data in
motion. Use SSL encryption support when you connect to your database, and
use HTTPS for your web clients.

## Client-side encryption

This library is a server-side component. We don't support encrypting data client-side in web
browsers.

## How the unit tests work

The unit tests are in the `src/unit-test/` directory, numbered sequentially.

There's some test runners in `bin/dev/`, as you can see. Read the scripts
for the gory details but in brief:

* `bin/dev/test.sh` will run the fast tests
* `bin/dev/test-all.sh` will run the fast tests and the slow tests

There are also some silly tests, but we won't talk about those. They are not
ordinarily run. And they're silly.

If you want to add a normal/fast test create the unit test directory in
`src/unit-test/test-XXX`, then add either `test.php` or `test.sh`. If you
create both then `test.sh` will have precedence and `test.php` will be ignored.

If you want to add a slow test create the unit test directory in
`src/unit-test/test-XXX`, then add either `slow.php` or `slow.sh`. If you
create both then `slow.sh` will have precedence and `slow.php` will be ignored.

You usually only need to supply a shell script if your unit tests require multiple processes
to work. That can happen when you need to test different constant definitions. As you can't
redefine constants in PHP you have to restart your process if you want to run with different
values.

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
* inc/: include files
* inc/library.php: the include file for this library, clients include this we do the rest
* inc/test-host.php: the include file for the unit testing framework
* inc/test.php: the include file for the unit testing toolkit
* etc/: library configuration files (planned)
* src/: PHP source code
* src/code/: the full library
* src/demo/: a web-client for demonstration purposes
* src/host/: software hosts (presently for hosting unit-tests)
* src/test/: facilities for use during testing
* src/unit-test/: unit tests: fast, slow, and silly; see bin/dev/test.sh
* .gitignore: the list of files ignored by git
* LICENSE: the software license
* README.md: this documentation file
* config.php: the library config file, used by demo web-client (create your own)

## Bans and restrictions

Some countries have banned the import or use of strong cryptography, such as
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
