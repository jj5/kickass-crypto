# Kickass Crypto

A contemporary PHP cryptography library circa 2023.

**Synopsis:** this library provides
[AES](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption with
[GCM](https://en.wikipedia.org/wiki/Galois/Counter_Mode)
authentication by using the
[AES-256-GCM](https://crypt-app.net/info/aes-256-gcm.html)
cipher suite in the
[OpenSSL library](https://www.php.net/manual/en/book.openssl.php)
for
[PHP](https://www.php.net/); it supports key rotation for separate round-trip and at-rest use
cases and some key management functions.

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
server and its clients in a relatively secure fashion, secrecy and tamperproofing preferred.
As mentioned above I found example code in the PHP documentation for the
[openssl_encrypt()](https://www.php.net/manual/en/function.openssl-encrypt.php)
function.

Initially it wasn't clear to me how to use this code. Particularly it was difficult to figure
out what to do with the three parts: the authentication tag, the initialization vector, and the
cipher text. Eventually I figured out I could just concatenate them. But if I was to do that I
would need to standardize on their length and placement so that I could retrieve them later...

...and then I figured it would be better to mask my actual data size by padding it to fixed
lengths at certain boundaries, so I did that...

...and then I wanted to support rich data which demanded some form of serialization. Initially I
was using the PHP [serialize()](https://www.php.net/manual/en/function.serialize.php) function but
that was changed later to
[json_encode()](https://www.php.net/manual/en/function.json-encode.php).

The example code didn't indicate anything at all about how to rotate keys in a supported fashion.
So I came up with the two use cases supported by this library with different approaches to key
management for round-trip and at-rest scenarios. This library lets you rotate in new keys while
maintaining support for older keys, as you are likely wont to do.

Then I layered in a careful approach to exception handling and error reporting, some unit testing
and validation, timing attack mitigation, service locators, usage demonstration, data size limits,
passphrase initialization, key generation scripts, telemetry, and things like that.

Basically this whole library was just everything I felt like I had to do so that I could actually
use the built-in PHP OpenSSL library implementation.

I don't consider this library _rolling my own crypto_, rather I think of it as _figuring out how
to actually use OpenSSL_. If I've made any mistakes, obvious or otherwise, I would
[really appreciate hearing about it](mailto:jj5@jj5.net?subject=Kickass%20Crypto).

## tl;dr

Don't want to RTFM..? And here I am, writing all this stuff... sheesh.

```
#!/bin/bash
mkdir -p kickass-demo/lib
cd kickass-demo
git clone https://github.com/jj5/kickass-crypto.git lib/kickass-crypto 2>/dev/null
php lib/kickass-crypto/bin/gen-demo-config.php > config.php
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

For slightly more elaboration maybe check out the
[sample code](https://github.com/jj5/kickass-crypto/blob/main/src/demo/index.php).

Or if you want the bottom line on how this library works read the code in
[this file](https://github.com/jj5/kickass-crypto/blob/main/src/code/KickassCrypto.php).

## Library demo

Assuming I remember to update it from time to time, there's a demo system over
here:

* [https://www.progclub.net/~jj5/kickass-crypto/](https://www.progclub.net/~jj5/kickass-crypto/)

The demo facility just shows how to round-trip encrypted data between the client and server using
HTML and HTTP.

The demo code is available in this library in the
[src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/)
directory if you'd like to host it yourself.

## Using this library

As mentioned above you can check out the code from git with a command like this:

```
git clone https://github.com/jj5/kickass-crypto.git
```

**This code is unreleased, there is no stable version.**

If you want to include the client library for use in your application include the
[inc/library.php](https://github.com/jj5/kickass-crypto/tree/main/inc/library.php)
file which will take care of loading everything else; use something like this:

```
require_once __DIR__ . '/lib/kickass-crypto/inc/library.php';
```

After loading this library you will usually access via the `kickass_round_trip()` or
`kickass_at_rest()` service locators which are documented below.

If you want to host the demo code you need to host the files in
[src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/)
and include a valid `config.php` file in the project base directory (that's the directory that
includes this [README.md](https://github.com/jj5/kickass-crypto/tree/main/README.md) file).
For demonstration purposes a valid `config.php` file only needs to define a constant string for
`CONFIG_ENCRYPTION_SECRET_CURR`, but it needs to be a long and random string, you can generate
an appropriate string with:

```
php bin/gen-key.php
```

Or you can just generate a whole demo `config.php` file with:

```
php bin/gen-demo-config.php > config.php
```

## Supported PHP versions

This code should work on PHP 8.0 or greater (but so far I have only actually tested it on
PHP 8.1.2). If you try to run this code on an older version of PHP it will try to log an error
message and then exit your process.

## Supported platforms and environment

This code assumes it's running on a 64-bit platform. It also assumes your PHP
installation includes the bundled OpenSSL library.

Actually this code does a little more than assume it's on a 64-bit platform with the OpenSSL
library, it actually makes an attempt to ensure those things are true. If they're not an error
is logged and the process is exited.

I believe this code should run on any operating system, but I have only tested it on Linux. If you
have had success on MacOS or Windows I would be
[happy to hear about it](mailto:jj5@jj5.net?subject=Kickass%20Crypto).

Shell scripts are written for bash. If you don't have bash you may need to
port.

## On being old fashioned

Yes, I have heard of namespaces and PSR-4 autoloading, and no, I don't like them. If I wanted to
program in Java, I would program in Java. This library does things the old fashioned way. I don't
need a framework to include a couple of files. If I only need a function, I don't want to have
to invent a class to put it in.

Yes I have heard of PHPUnit. No, I don't need it. Tests are a shell script, if that's missing
they're a PHP script. If I need to make assertions I call assert(). Easy.

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

## Naming and specifying secret things

The preferred and supported way to nominate secrets in config files is as constants using the
PHP define() function. The problem with using class/instance fields or global variables is that
the values can fairly easily leak into debug and logging code, this is less likely (though still
possible) for constants. Similarly if you need to cache global/static data (such as read from the
config file) the best way to do that is with a local static variable in a function, if possible,
as using instance fields, class fields, or globals can more easily lead to secret leakage.

The safest way to define a constant in PHP is to check that it's not already defined first,
because attempting to define an already defined constant will result in error. If you find an
already defined constant you can either abort with an error message (if you do don't provide too
much detail because the public web might see it) or just keep the existing value and don't try
to redefine it. The
[bin/gen-demo-config.php](https://github.com/jj5/kickass-crypto/blob/main/bin/gen-demo-config.php)
config file generator takes the first approach and calls the PHP `die()` function if a duplicate
is detected. You can see what happens by including the generated `config.php` file twice, as:

```
require __DIR__ . '/config.php';
require __DIR__ . '/config.php';
```

Consequently, as with most PHP source files, it's best to use `require_once` when including the
`config.php` file:

```
require_once __DIR__ . '/config.php';
```

When I name things which are secret I make sure the name contains the string "pass" (as in
"password", "passwd", and "passphrase", or even, at a stretch, "passport") or "secret". In my
general purpose logging facilities (which aren't included in this library) I scrub and redact
anything with a name that matches (case-insensitive) prior to logging diagnostic data. I encourage
you to adopt this practice.

In this library if a variable or constant might contain sensitive data it will be named
with either "pass" or "secret" as a substring in the name.

**_Don't write sensitive data into logs._**

**_Do put either 'pass' or 'secret' in the name of sensitive variables, fields, or constants._**

## Configuration settings

In addition to inheriting from `KickassCrypto` and overriding particular functionality a lot of
configuration is available via the configuration constants. Search for `CONFIG_ENCRYPTION` to
find what's available.

Please be advised that at the moment this code is configured directly in the
`config.php` file.

In future the `config.php` file will include two separately managed config files,
being:

* etc/keys-round-trip.php
* etc/keys-at-rest.php

There will be management scripts for automatically rotating and provisioning
keys in these files.

Experienced Linux users know that you don't edit `/etc/sudoers` directly, you
edit it with `visudo` so that you can verify you haven't accidentally
introduced a syntax error and hosed your system.

I intend to provide similar scripts for editing and managing `config.php`
and other config files. So stand-by for those updates. In the mean time...
_just be very careful_.

One thing you should be very careful you don't do is manage your keys in anything other than
a PHP file with a ".php" file extension. If you put your keys in a ".ini" file or something like
that _they might very well be served as plain text by your web server_. So don't do that. Also
be careful not to introduce syntax errors into your config file or other source files running
in production because details might leak with the potential resulting error messages.

## Configurability and extensibility

As mentioned in the previous section a fair amount of configurability is provided by support
for named configuration constants.

In addition to the configuration constants there's a lot you can do if you inherit from the
`KickassCrypto` base class and override its methods.

As an alternative to the configuration constants (which can only be defined once per process and
thereafter cannot be changed) there are instance methods as `get_config_...()` for configuration
options and `get_const_...()` for constant evaluation. Most important constants and configuration
options are read indirectly via these accessors so you should be able to override them reliably.

Most calls to PHP built-in functions are done by thin wrappers via protected functions on
`KickassCrypto`. These are defined in the `KICKASS_PHP_WRAPPER` trait. This indirection allows for
certain PHP function invocations to be intercepted and potentially modified. This has been done
primarily to support fault injection during unit testing, but you could use for other purposes to
change implementation particulars.

Things which are considered sensitive in `KickassCrypto` are defined as _private_ and/or _final_.
If it's not private and it's not final it's fair game for overriding (unless I've made a
mistake). Particularly the instance methods which start with `do_` were specifically made to be
replaced or intercepted by implementers.

## Service locators

This library provides two service locator functions which manage an instance of
the crypto library each, those are:

* `kickass_round_trip()`
* `kickass_at_rest()`

You can replace the instance of the service provided by the service locator
function by calling the function and passing the new instance as the sole
parameter, like this:

```
class MyKickassCrypto extends KickassCrypto {

  protected function is_valid_config( &$problem = null ) { return TODO; }
  protected function get_passphrase_list() { return TODO; }

}

kickass_round_trip( new MyKickassCrypto );
```

Ideally this library will meet your requirements out of the box (or with certain
configuration) and you won't need to replace the instances provided by the service locators
by default.

## Data encryption

The encryption process is roughly:

* JSON encode
* prefix with JSON data length
* pad with random data
* encrypt with AES-256-GCM using the OpenSSL library
* concatenate initialization vector, cipher text, and authentication tag
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
prefixed. Before encryption the message is formatted, like this:

```
$message = $json_data_length . '|' . $json . $this->get_padding( $pad_length );
```

The JSON data length is formatted as an 8-character hexadecimal value. The size of 8 characters
is constant and does not vary depending on the magnitude of the JSON data length.

The reason for the padding is to obscure the actual data size. Padding is done in up to 4 KiB
boundaries (2<sup>12</sup> bytes), which we call chunks. The chunk size is configurable and the
default may change in future.

The message is then encrypted with AES-256-GCM and the initialization vector, ciphertext,
and authentication tag are concatenated together, like this:

```
$iv . $ciphertext . $tag
```

Then everything is base64 encoded with the PHP
[base64_encode()](https://www.php.net/manual/en/function.base64-encode.php) function and the
data-format prefix is added, like this:

```
"KA0/" . base64_encode( $iv . $ciphertext . $tag )
```

The decryption process expects to find the 12 byte initialization vector, the ciphertext, and
the 16 byte authentication tag.

After decrypting the ciphertext the library expects to find the size of the JSON data as an ASCII
string representing an 8 character hex encoded value, followed by a single pipe character,
followed by the JSON, and then the padding. The library can then remove the JSON from its
padding and take care of the rest of the decoding.

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
1 millisecond (1 ms) and 10 seconds (10 s) is introduced. This is a mitigation
against potential timing attacks. See
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

The user of this library has the opportunity to override the `do_delay` method and provide their
own delay logic.

If that `do_delay` override throws an exception it will be handled and an emergency delay will be
injected.

If you do override `do_delay` but don't actually delay for at least the minimum duration (which
is 1 ms) then the library will inject the emergency delay.

The main reason for allowing the implementer to customize the delay logic is so that unit tests
can delay for a minimum amount of time. Ordinarily there shouldn't be any reason to meddle with
the delay logic.

## Exceptions and errors

When an instance of either `KickassCryptoRoundTrip` or `KickassCryptoAtRest` is
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
[Advanced Encryption Standard](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption with
[Galois/Counter Mode](https://en.wikipedia.org/wiki/Galois/Counter_Mode)
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
[Galois/Counter Mode](https://en.wikipedia.org/wiki/Galois/Counter_Mode). The GCM is a
[Message Authentication Code](https://en.wikipedia.org/wiki/Message_authentication_code)
(MAC) similar to a
[Hash-based Message Authentication Code](https://en.wikipedia.org/wiki/HMAC)
(HMAC) which you may have heard of before. The goal of the
GCM authentication tag is to make your encrypted data
[tamperproof](https://en.wikipedia.org/wiki/Tamperproofing).

## Random data

This library requires secure random data inputs for various purposes:

* for generating secret keys
* for initialization vectors
* for message padding

There are two main options for generating suitable random data in PHP, those are:

* [openssl_random_pseudo_bytes()](https://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php)
* [random_bytes()](https://www.php.net/manual/en/function.random-bytes.php)

Both are reasonable choices but this library uses
[random_bytes()](https://www.php.net/manual/en/function.random-bytes.php).

If the `random_bytes()` function is unable to generate secure random data it will throw an
exception. See the documentation for details.

We also use the PHP
[random_int()](https://www.php.net/manual/en/function.random-int.php)
function to generate a random delay for use in timing attack mitigation.

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

**When doing key management it is important to make sure your config files are edited in a secure
way. A syntax error in a config file could lead to a secret key being exposed to the public web.
If this happened you would have to rotate all of your keys immediately and then destroy the old
compromised keys, _even then it might be too late_.**

It would be a good idea to stand ready to do a key rotation in an automated and tested fashion
immediately in case of emergency.

## Key coordination

When you rotate your round-trip and at-rest keys you need to make sure they
are synchronized across all of your web servers.

I intend to implement some facilities to help with key deployment and config
file editing but those facilities are not done yet.

## Data in motion

This library supports encrypted data at-rest, and encrypted data round-trips.
Another consideration is data in motion. Data in motion is also sometimes called data in transit.

Data is in motion when it moves between your web servers and your database
server. Data is also in motion when it moves between your web servers and the
clients that access them. You should use asymetric encryption for your data in
motion. Use SSL encryption support when you connect to your database, and
use HTTPS for your web clients.

## Client-side encryption

This library is a server-side component. We don't support encrypting data client-side in web
browsers.

## Telemetry

This library collects some basic telemetry:

* function counters: how many times certain key functions have been called
* class counters: how many times certain key classes have been constructed
* length counters: counters for data lengths of successfully generated ciphertext

Call `KickassCrypto::GetTelemetry()` to get the telemetry and `KickassCrypto::ReportTelemetry()`
to report it.

## How the unit tests work

The unit tests are in the
[src/unit-test/](https://github.com/jj5/kickass-crypto/tree/main/src/unit-test/)
directory, numbered sequentially.

There's some test runners in
[bin/dev/](https://github.com/jj5/kickass-crypto/tree/main/bin/dev),
as you can see. Read the scripts for the gory details but in brief:

* [bin/dev/test.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test.sh) will run the
fast tests
* [bin/dev/test-all.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test-all.sh) will
run the fast tests and the slow tests

There are also some silly tests, but we won't talk about those. They are not
ordinarily run. And they're silly.

If you want to add a normal/fast test create the unit test directory as
`src/unit-test/test-XXX`, then add either `fast.php` or `fast.sh`. If you
create both then `fast.sh` will have precedence and `fast.php` will be ignored.

If you want to add a slow test create the unit test directory as
`src/unit-test/test-XXX`, then add either `slow.php` or `slow.sh`. If you
create both then `slow.sh` will have precedence and `slow.php` will be ignored.

You usually only need to supply a shell script if your unit tests require multiple processes
to work. That can happen when you need to test different constant definitions. As you can't
redefine constants in PHP you have to restart your process if you want to run with different
values.

See existing unit tests for examples of how to use the simple unit test host in
[src/host/unit-test.php](https://github.com/jj5/kickass-crypto/tree/main/src/host/unit-test.php).

## Directory structure

Here are some notes regarding notable components:

* [bin/](https://github.com/jj5/kickass-crypto/tree/main/bin/): command-line commands
* [bin/dev/](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/): development scripts
* [bin/dev/test-all.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test-all.sh): run fast and slow unit tests
* [bin/dev/test.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test.sh): run unit tests, control with flags, slow tests skipped by default
* [bin/gen-demo-config.php](https://github.com/jj5/kickass-crypto/tree/main/bin/gen-demo-config.php): generate an initial `config.php` file for the demo
* [bin/gen-key.php](https://github.com/jj5/kickass-crypto/tree/main/bin/gen-key.php): generate a secret key
* [bin/get-cipher-list.php](https://github.com/jj5/kickass-crypto/tree/main/bin/get-cipher-list.php): list cipher suites supported by your version of PHP
* [bin/get-hash-list.php](https://github.com/jj5/kickass-crypto/tree/main/bin/get-hash-list.php): list hash algorithms supported by your version of PHP
* [doc/](https://github.com/jj5/kickass-crypto/tree/main/doc/): additional documentation
* [doc/tex/](https://github.com/jj5/kickass-crypto/tree/main/doc/tex/): LaTeX write-up (planned)
* [inc/](https://github.com/jj5/kickass-crypto/tree/main/inc/): include files
* [inc/library.php](https://github.com/jj5/kickass-crypto/tree/main/inc/library.php): the include file for this library, clients include this the we do the rest
* [inc/test-host.php](https://github.com/jj5/kickass-crypto/tree/main/inc/test-host.php): the include file for the unit testing framework
* [inc/test.php](https://github.com/jj5/kickass-crypto/tree/main/inc/test.php): the include file for the unit testing toolkit
* etc/: library configuration files (planned)
* [src/](https://github.com/jj5/kickass-crypto/tree/main/src/): PHP source code
* [src/code/](https://github.com/jj5/kickass-crypto/tree/main/src/code/): the library components (presently only one)
* [src/code/KickassCrypto.php](https://github.com/jj5/kickass-crypto/tree/main/src/code/KickassCrypto.php): the full library
* [src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/): a web-client for demonstration purposes
* [src/host/](https://github.com/jj5/kickass-crypto/tree/main/src/host/): software hosts (presently for hosting unit-tests)
* [src/test/](https://github.com/jj5/kickass-crypto/tree/main/src/test/): facilities for use during testing
* [src/unit-test/](https://github.com/jj5/kickass-crypto/tree/main/src/unit-test/): unit tests:
fast, slow, and silly; see [bin/dev/test.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test.sh)
* [.gitignore](https://github.com/jj5/kickass-crypto/tree/main/.gitignore): the list of files ignored by git
* [LICENSE](https://github.com/jj5/kickass-crypto/tree/main/LICENSE): the software license
* [README.md](https://github.com/jj5/kickass-crypto/tree/main/README.md): this documentation file
* config.php: the library config file, used by demo web-client (create your own)

## Bans and restrictions

Some countries have banned the import or use of strong cryptography, such as
256 bit AES.

Please be advised that this library does not contain cryptographic functions,
they are provided by your PHP implementation.

## License

This code is licensed under the MIT License.

## Commit messages

I should probably be more disciplined with my commit messages... ff this library matures and gets
widely used I will try to be more careful with my commits.

## Colophon

The Kickass Crypto ASCII banner is in the Graffiti font courtesy of
[TAAG](http://www.patorjk.com/software/taag/#p=display&f=Graffiti&t=Kickass%20Crypto).

The string "kickass" appears in the source code 671 times.

## Comments? Questions? Suggestions?

I'd love to hear from you! Hit me up at
[jj5@jj5.net](mailto:jj5@jj5.net?subject=Kickass%20Crypto).
Put "Kickass Crypto" in
the subject line to make it past my mail filters.
