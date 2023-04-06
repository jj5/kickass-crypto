# Kickass Crypto

A contemporary PHP cryptography library circa 2023.

**Synopsis:**
* an extensible and uniform interface to two separate and contemporary encryption libraries with
support for input and config validation, serialization and deserialization (using JSON by default
with PHP serialization as an option), data size limits, passphrase management, message
padding, output encoding and input decoding, data format version management, logging, telemetry,
timing attack mitigation, reentrancy detection and limits, and exception handling and error
management
* [XSalsa20 stream cipher](https://libsodium.gitbook.io/doc/advanced/stream_ciphers/xsalsa20)
encryption with
[Poly1305 MAC](https://en.wikipedia.org/wiki/Poly1305)
authentication by using the
[sodium_crypto_secretbox()](https://www.php.net/manual/en/function.sodium-crypto-secretbox.php)
function from the
[Sodium library](https://doc.libsodium.org/)
* [AES](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption with
[GCM](https://en.wikipedia.org/wiki/Galois/Counter_Mode)
authentication by using the
[AES-256-GCM](https://crypt-app.net/info/aes-256-gcm.html)
cipher suite from the
[OpenSSL library](https://www.php.net/manual/en/book.openssl.php)
* key rotation for separate round-trip and at-rest use cases and some key management functions

This library is a wrapper around the
[PHP Sodium library](https://www.php.net/manual/en/book.sodium.php) and the
[PHP OpenSSL library](https://www.php.net/manual/en/book.openssl.php).

The Sodium code in this library is based on the example given in the documentation for the PHP
[sodium_crypto_secretbox()](https://www.php.net/manual/en/function.sodium-crypto-secretbox.php)
function.

The OpenSSL code in this library is based on the example given in the documentation for the PHP
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

## Warning

**Please read this section.**

There are a lot of ways you can go wrong with your crypto code. This library was written as an
attempt to reduce crypto footguns; hopefully it hasn't introduced any!

The first thing to know about crypto is your data is only as secure as your keys. There's more to
know about key management than I can possibly tell you here (and I'm not an expert anyway), but
here are a few things to think about:

* if you backup your keys make sure your backups are secure
* if you don't backup your keys you won't be able to decrypt your data if you lose them
* don't commit your keys to source control
* your keys don't only need to be secure now, they have to be secure _for all time_; if people are
intercepting and recording your data now they will be able to decrypt it if they get a copy of the
key in the future
* be careful you don't do things with your keys that cause them to potentially be swapped to the
swap file (this might be unavoidable, such as when they're in a config file that you edit with a
text editor, in which case make sure you are on a secure host)
* if you do leak your key (by accidentally putting it in source control or otherwise doing
something which might have exposed it) then you have to immediately rotate your keys and
re-encrypt all of your data (and the damage might have been done even if you do this, if your key
leaks and an attacker already has a copy of the encrypted data it's too late and they know
everything now)

Some other things to be aware of:

* if your data really is so sensitive that it requires encryption, it might be so sensitive that
it's better to delete it than risk it possibly being decrypted; deletion is generally a better
option than encryption for sensitive data if you don't absolutely need it (secure deletion is not
covered in this documentation or by this library)
* be careful not to introduce syntax errors into config files
* be careful not to attempt to redefine secret configuration constants
* be careful not to deploy syntax errors to hosted code (production or otherwise)
* take your web servers offline while you update the source code and configuration files and then
bring them back online after that process is complete, this is to avoid problems with trying to
serve temporarily missing or changing files with potentially mutually inconsistent content such
as when half of your code has been updated and half of it hasn't
* never log anything whose name includes the case-insensitive string "secret" or "pass"; this
library will make sure that any secret or sensitive data is named with either of those substrings
as part of its name
* don't try to encrypt the boolean value false; the boolean value false is used to indicate
decryption errors so we don't want to decrypt it as a valid value so we refuse to encrypt it (it
is possible to force support for encrypting the value false by way of a configuration option, but
if you do that you should always call `get_error()` after encryption to make sure it's null
indicating no error).

Another thing, which surprised me when I learned it, although it's quite obvious once you know, is
that you should _not_ compress your data before you encrypt it. This isn't _always_ a problem,
but in certain circumstances it can be, so it's probably best just never to do it.

The problem with compression is that if an attacker can control some of the input data they can
include a particular value and then if the output decreases in size they can know that the other
input also included in the particular value. Ouch.

This code base is not mature or well tested, before you use it you should read all the code to
make sure it meets your quality standards. If you do so I would be pleased to hear from you.

If you can think of anything else that everyone should know about and be careful about please
[let me know](mailto:jj5@jj5.net?subject=Kickass%20Crypto)!

## tl;dr

Don't want to RTFM..? And here I am, writing all this stuff... sheesh. At least read the
[warnings](#warning) listed above.

```
#!/bin/bash
mkdir -p kickass-demo/lib
cd kickass-demo
git clone https://github.com/jj5/kickass-crypto.git lib/kickass-crypto 2>/dev/null
php lib/kickass-crypto/bin/gen-demo-config.php > config.php
cat > demo.php <<'EOF'
<?php
require_once __DIR__ . '/lib/kickass-crypto/inc/sodium.php';
require_once __DIR__ . '/config.php';
$ciphertext = kickass_round_trip()->encrypt( 'secret text' );
$plaintext = kickass_round_trip()->decrypt( $ciphertext );
echo "the secret data is: $plaintext.\n";
EOF
php demo.php
```

For slightly more elaboration maybe check out the
[sample code](https://github.com/jj5/kickass-crypto/blob/main/src/demo/index.php).

Or if you want the bottom line on how this library works read the code in the
[library framework](https://github.com/jj5/kickass-crypto/blob/main/src/code/namespace/KickassCrypto/KickassCrypto.php)
or the
[other code](https://github.com/jj5/kickass-crypto/blob/main/src/code/namespace/KickassCrypto/).

## Why was this library written?

Gee, it started simply enough but it got kind of complicated in the end.

I wanted to round-trip some relatively sensitive data (row version numbers for optimistic
concurrency control) between my server and its clients in a relatively secure fashion, secrecy and
tamperproofing preferred.

I had heard that the OpenSSL library was available in PHP so I searched for information
concerning how to use that. I found example code in the PHP documentation for the
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

And then... people started telling me about the Sodium library, and suggesting that I use that
instead. Since I'd already done a bunch of work for key management and input serialization and
message formatting and encoding and so on I figured I could just reuse all of that and provide a
wrapper around Sodium too. So that's what I did.

Now if you use this library you can decide whether you want to use the Sodium
implementation or the OpenSSL implementation. Because the two implementations can happily
co-exist you can also write code to move from one to the other, if you so desired. The
implementations never share key configuration or data formats, they are entirely separate. (That
said, it's not exactly trivial to switch encryption algorithms and you probably have to go offline
to migrate all of your data and if you can't do that you're gonna have a bad time so don't plan on
switching algorithms, if you're not sure start with Sodium and stick with it.)

I don't consider this library _rolling my own crypto_, rather I think of it as _figuring out how
to actually use Sodium and OpenSSL_. If I've made any mistakes, obvious or otherwise, I would
[really appreciate hearing about it](mailto:jj5@jj5.net?subject=Kickass%20Crypto).

## Library demo

Assuming I remember to update it from time to time, there's a demo system over here:

* [https://www.progclub.net/~jj5/kickass-crypto/](https://www.progclub.net/~jj5/kickass-crypto/)

The demo facility just shows how to round-trip encrypted data between the client and server using
HTML and HTTP.

The demo code is available in this library in the
[src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/)
directory if you'd like to host it yourself.

## Library PHP docs

Assuming I remember to update them from time to time, the PHP docs are over here:

* [https://www.progclub.net/~jj5/kickass-crypto-phpdoc/](https://www.progclub.net/~jj5/kickass-crypto-phpdoc/)

## Using this library

As mentioned above you can check out the code from git with a command like this:

```
git clone https://github.com/jj5/kickass-crypto.git
```

**This code is unreleased, there is no stable version.**

If you want to include the client library for use in your application include either the
[inc/sodium.php](https://github.com/jj5/kickass-crypto/tree/main/inc/sodium.php) or the
[inc/openssl.php](https://github.com/jj5/kickass-crypto/tree/main/inc/openssl.php)
file which will take care of loading everything else; use something like this:

```
require_once __DIR__ . '/lib/kickass-crypto/inc/sodium.php';
```

After loading this library you will usually access via the `kickass_round_trip()` or
`kickass_at_rest()` service locators which are documented below, something like this:

```
$ciphertext = kickass_round_trip()->encrypt( 'secret text' );
$plaintext = kickass_round_trip()->decrypt( $ciphertext );
echo "the secret data is: $plaintext.\n";
```

It took a lot of work to make things that simple!

If you want to host the demo code you need to host the files in
[src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/)
and include a valid `config.php` file in the project base directory (that's the directory that
includes this [README.md](https://github.com/jj5/kickass-crypto/tree/main/README.md) file).
For demonstration purposes a valid `config.php` file only needs to define a constant string for
`CONFIG_SODIUM_SECRET_CURR`, but it needs to be a long and random string, you can generate
an appropriate string with:

```
php bin/gen-key.php
```

Or you can just generate a whole demo `config.php` file with:

```
php bin/gen-demo-config.php > config.php
```

## Library metrics

Here are some notes about the software files and lines of code.

### File count

```
Total Number of Files = 124
Total Number of Source Code Files = 124
```

| Directory | Files | By language |
| --------- | -----:| ----------- |
| test      |    59 | php=55,sh=4 |
| code      |    35 | php=35      |
| bin       |    22 | php=13,sh=9 |
| inc       |     7 | php=7       |
| demo      |     1 | php=1       |

#### Totals grouped by language (dominant language first)

| Language | Files | Percentage |
| -------- | -----:| ----------:|
| php      |   111 |   (89.52%) |
| sh       |    13 |   (10.48%) |


### Lines of code

```
Total Physical Source Lines of Code (SLOC)                = 8,471
Development Effort Estimate, Person-Years (Person-Months) = 1.89 (22.62)
 (Basic COCOMO model, Person-Months = 2.4 * (KSLOC**1.05))
Schedule Estimate, Years (Months)                         = 0.68 (8.18)
 (Basic COCOMO model, Months = 2.5 * (person-months**0.38))
Estimated Average Number of Developers (Effort/Schedule)  = 2.77
Total Estimated Cost to Develop                           = $ 254,667
 (average salary = $56,286/year, overhead = 2.40).
```

| Directory | SLOC  | By language     |
| --------- | -----:| --------------- |
| code      | 4,669 | php=4669        |
| test      | 3,091 | php=2921,sh=170 |
| bin       |   603 | php=423,sh=180  |
| demo      |    71 | php=71          |
| inc       |    37 | php=37          |

#### Totals grouped by language (dominant language first)

| Language | SLOC  | Percentage |
| -------- | -----:| ----------:|
| php      | 8,121 |   (95.87%) |
| sh       |   350 |    (4.13%) |

## Supported PHP versions

This code should work on PHP 7.4 or greater. If you try to run this code on an older version of
PHP it will try to log an error message and then exit your process.

## Supported platforms and environment

This code will check to make sure it's running on a 64-bit platform. If it's not it will complain
and exit.

If you load the Sodium module the library will ensure that the Sodium library is actually
available. If it's not, the process will complain and exit.

If you load the OpenSSL module the library will ensure that the OpenSSL library is actually
available. If it's not, the process will complain and exit.

I believe this code should run on any operating system, but I have only tested it on Linux. If you
have had success on MacOS or Windows I would be
[happy to hear about it](mailto:jj5@jj5.net?subject=Kickass%20Crypto).

Shell scripts are written for bash. If you don't have bash you may need to port.

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

To give you an example, let's create a test file called `double-define.php` like this:

```
<?php
define( 'TEST', 123 );
define( 'TEST', 456 );
```

Then when we run the code, something like this happens:

```
$ php double-define.php
PHP Warning:  Constant TEST already defined in ./double-define.php on line 4
PHP Stack trace:
PHP   1. {main}() ./double-define.php:0
PHP   2. define($constant_name = 'TEST', $value = 456) ./double-define.php:4
```

If that constant value contained your secret key then you've just had a very bad day.

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

You can find an example of what happens if you double include the `config.php` in
[config-die.php](https://github.com/jj5/kickass-crypto/blob/main/bin/dev/config-die.php).

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
configuration is available via the configuration constants. Search for `CONFIG_SODIUM` to
find what's available for Sodium and `CONFIG_OPENSSL` to find what's available for OpenSSL.

Please be advised that at the moment this code is configured directly in the
`config.php` file.

In future the `config.php` file will include separately managed config files, being:

* etc/keys-round-trip-sodium.php
* etc/keys-round-trip-openssl.php
* etc/keys-at-rest-sodium.php
* etc/keys-at-rest-openssl.php

There will be management scripts for automatically rotating and provisioning keys in these files.

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
`KickassCrypto`. These are defined in the `KICKASS_WRAPPER_PHP` trait. This indirection allows for
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
class MyKickassCrypto extends \Kickass\Crypto\Framework\KickassCrypto {

  protected function do_is_valid_config( &$problem = null ) { return TODO; }
  protected function do_get_passphrase_list() { return TODO; }
  // ... other function overrides ...

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
* encrypt with either the Sodium library or the OpenSSL library
* concatenate initialization vector, cipher text, and authentication tag
* encode as base64
* prefix with data-format indicator

Note that the Sodium library uses a nonce instead of an initialization vector (to similar
effect) and Sodium handles its own authentication tag.

## Data-format prefix

When this library encodes its ciphertext it includes a data-format prefix of "KAS0/" for the
Sodium implementation and "KA0/" for the OpenSSL implementation.

The zero ("0") in the data-format prefix is for _version zero_, which is intended to imply that
_the interface is unstable and may change_.

Future versions of this library might implement a new data-format prefix for a stable data format.

When this library decodes its ciphertext it verifies the data-format prefix. At present only
"KAS0/" or "KA0/" is supported.

## Data format

The version zero data format, mentioned above, presently implies the following:

After data encoding (JSON by default, discussed in the following section) padding is done and
the data length is prefixed. Before encryption the message is formatted, like this:

```
$message = $encoded_data_length . '|json|' . $encoded_data . $this->get_padding( $pad_length );
```

The JSON data length is formatted as an 8-character hexadecimal value. The size of 8 characters
is constant and does not vary depending on the magnitude of the JSON data length.

The reason for the padding is to obscure the actual data size. Padding is done in up to 4 KiB
boundaries (2<sup>12</sup> bytes), which we call chunks. The chunk size is configurable and the
default may change in future.

Then if we're encrypting with Sodium the message is encrypted with `sodium_crypto_secretbox()`
and then nonce and the ciphertext are concatenated together, like this:

```
$nonce . $ciphertext
```

Otherwise if we're encrypting with OpenSSL the message is encrypted with AES-256-GCM and the
initialization vector, ciphertext, and authentication tag are concatenated together, like this:

```
$iv . $ciphertext . $tag
```

Then everything is base64 encoded with the PHP
[base64_encode()](https://www.php.net/manual/en/function.base64-encode.php) function and the
data-format prefix is added.

For Sodium that's done like this:

```
"KAS0/" . base64_encode( $nonce . $ciphertext )
```

And for OpenSSL that's done like this:

```
"KA0/" . base64_encode( $iv . $ciphertext . $tag )
```

The decryption process expects to find the 24 byte nonce and the ciphertext for the "KAS0"
data format and the 12 byte initialization vector, the ciphertext, and
the 16 byte authentication tag for the KA0 data format.

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
`CONFIG_ENCRYPTION_DATA_LENGTH_MAX`.

The default data encoding limit is 67,108,864 (2^<sup>26</sup>) bytes, which
is roughly 67 MB.

It's possible to configure this data encoding limit, if you need to make it
larger or smaller. Just be aware that if you make the limit too large you will
end up with memory problems and your process might get terminated.

If you wanted to decrease the data encoding limit you could do that in your
`config.php` file like this:

```define( 'CONFIG_ENCRYPTION_DATA_LENGTH_MAX', pow( 2, 25 ) );```

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
😜) could figure out that your process is sleeping rather than doing actual work.

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
the delay logic and it might be less safe to do so.

## Exceptions and errors

When an instance of one of:

* `KickassSodiumRoundTrip`
* `KickassSodiumAtRest`
* `KickassOpenSSLRoundTrip`
* `KickassOpenSSLAtRest`

is created the configuration settings are validated. If the configuration settings
are not valid the constructor will throw an exception. If the constructor succeeds
then encryption and decryption later on should also (usually) succeed. If there
are any configuration problems that will mean encryption or decryption won't
be able to succeed the constructor should throw.

This library defines its own exception class called `KickassException`. This
works like a normal Exception except that it adds a method `getData()` which
can return any data associated with the exception. A `KickassException` doesn't
always have associated data.

Of course not all problems will be able to be diagnosed in advance. If the
library can't complete an encryption or decryption operation after a successful
construction it will signal the error by returning the boolean value false.
Returning false on error is a PHP idiom, and we use this idiom rather than
raising an exception to limit the possibility of an exception being thrown
while an encryption secret or passphrase is on the call stack.

The problem with having sensitive data on the call stack when an exception is raised is
that the data can be copied into stack traces, which can get saved, serialized,
displayed to users, logged, etc. We don't want that so we try very hard not to
raise exceptions while sensitive data might be on the stack.

If false is returned on error, one or more error messages will be added to an
internal list of errors. The caller can get the latest error by calling the
method `get_error`. If you want the full list of errors, call `get_error_list`.

If there were any errors registered by the OpenSSL library functions (which the OpenSSL module
calls to do the heavy lifting), then the last such error is available
if you call the `get_openssl_error()`. You can clear the current error list (and
OpenSSL error message) by calling the method `clear_error()`.

## Cipher suite

For the PHP Sodium implementation the function we use is
[sodium_crypto_secretbox()](https://www.php.net/manual/en/function.sodium-crypto-secretbox.php).
That's
[XSalsa20 stream cipher](https://libsodium.gitbook.io/doc/advanced/stream_ciphers/xsalsa20)
encryption with
[Poly1305 MAC](https://en.wikipedia.org/wiki/Poly1305)
authentication and integrity checking.

For the PHP OpenSSL implementation the cipher
suite we use is
[AES-256-GCM](https://crypt-app.net/info/aes-256-gcm.html).
That's
[Advanced Encryption Standard](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
encryption with
[Galois/Counter Mode](https://en.wikipedia.org/wiki/Galois/Counter_Mode)
authentication and integrity checking.

## Secret keys and passphrases

Secret keys are the secret values you keep in your `config.php` file which
will be processed and turned into passphrases for use by the Sodium and OpenSSL library
functions. This library automatically handles converting secret keys into
passphrases so your only responsibility is to nominate the secret keys.

The secret keys used vary based on the use case and the module. There are two default use
cases, known as round-trip and at-rest.

The "256" in AES-256-GCM means that this cipher suite expects 256-bit (32 byte)
passphrases. The Sodium library `sodium_crypto_secretbox()` function also expects a 256-bit
(32 byte) passphrase.

We use a hash algorithm to convert our secret keys into 256-bit
binary strings which can be used as the passphrases the cipher algorithms
expect.

The minimum secret key length required is 88 bytes. When these keys are
generated by this library they are generated with 66 bytes of random data which
is then base64 encoded.

The secret key hashing algorithm we use is SHA512/256. That's 256-bits worth of data
taken from the SHA512 hash of the secret key. When this hash code is applied
with raw binary output from an 88 byte base64 encoded input you should be
getting about 32 bytes of randomness for your keys.

## Nonce

The Sodium library expects to be provided with a nonce, in lieu of an initialization vector.

To understand what problem the nonce mitigates, think about what would happen if you
were encrypting people's birthday. If you had two users with the same birthday
and you encrypted those birthdays with the same key, then both users would
have the same ciphertext for their birthdays. When this happens you can see
who has the same birthday, even when you might not know exactly when it is. The
initialization vector avoids this potential problem.

## Initialization vector

Our AES-256-GCM cipher suite supports the use of a 12 byte
[initialization vector](https://en.wikipedia.org/wiki/Initialization_vector),
which we provide. The initialization vector ensures that even if you
encrypt the same values with the same passphrase the resultant ciphertext still
varies.

This mitigates the same problem as the Sodium nonce.

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

The Sodium library also uses an authentication tag but it takes care of that by itself, it's not
something we have to manage. When you `parse_binary()` in the Sodium module the tag is set
to false.

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

The config setting for the current key for the Sodium module is: `CONFIG_SODIUM_SECRET_CURR`.

The config setting for the current key for the OpenSSL module is: `CONFIG_OPENSSL_SECRET_CURR`.

The config setting for the previous key for the Sodium module is: `CONFIG_SODIUM_SECRET_PREV`.

The config setting for the previous key for the OpenSSL module is: `CONFIG_OPENSSL_SECRET_PREV`.

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

The config setting for the key list for the Sodium module is: `CONFIG_SODIUM_SECRET_LIST`.

The config setting for the key list for the OpenSSL module is: `CONFIG_OPENSSL_SECRET_LIST`.

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

If someone gets a copy of your secret keys, they will be able to decrypt your data.

If someone gets a copy of your encrypted data now, they can keep it and decrypt it if they get a
copy of your secret keys in the future. So your keys don't have to be only secret now, but they
have to be secret for all time.

If you lose your secret keys, you won't be able to decrypt your data.

Your round-trip data is probably less essential than your at-rest data.

It's a very good idea to make sure you have backups of the secret keys for your
essential round-trip or at-rest data. You can consider:

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
clients that access them. You should use asymmetric encryption for your data in
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
[src/test/](https://github.com/jj5/kickass-crypto/tree/main/src/test/)
directory, numbered sequentially.

There's some test runners in
[bin/dev/](https://github.com/jj5/kickass-crypto/tree/main/bin/dev),
as you can see. Read the scripts for the gory details but in brief:

* [bin/dev/test.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test.sh) will run the
fast tests, takes about 10 seconds
* [bin/dev/test-all.sh](https://github.com/jj5/kickass-crypto/tree/main/bin/dev/test-all.sh) will
run the fast tests and the slow tests, takes about 10 minutes

There are also some silly tests, but we won't talk about those. They are not
ordinarily run. And they're silly.

If you want to add a normal/fast test create the unit test directory as
`src/test/test-XXX`, then add either `fast.php` or `fast.sh`. If you
create both then `fast.sh` will have precedence and `fast.php` will be ignored.

If you want to add a slow test create the unit test directory as
`src/test/test-XXX`, then add either `slow.php` or `slow.sh`. If you
create both then `slow.sh` will have precedence and `slow.php` will be ignored.

You usually only need to supply a shell script if your unit tests require multiple processes
to work. That can happen when you need to test different constant definitions. As you can't
redefine constants in PHP you have to restart your process if you want to run with different
values.

See existing unit tests for examples of how to use the simple unit test host.

## Regarding PHPUnit

I have heard of and used PHPUnit (although I haven't used it for a long while). I don't use it in
this project because I don't feel I need it or that it adds much value. Tests are a shell script,
if that's missing they're a PHP script. If I need to make assertions I call assert(). Easy.

## Programming this library

Here are some notes about the various idioms and approaches taken in this library.

### Typed final wrapper idiom

In the code you will see things like this:

```
  protected final function is_valid_settings( int $setting_a, string $setting_b ) : bool {

    if ( strlen( $setting_b ) > 20 ) { return false; }

    return $this->do_is_valid_settings( $setting_a, $setting_b );

  }

  protected function do_is_valid_settings( $setting_a, $setting_b ) {

    if ( $setting_a < 100 ) { return false; }

    if ( strlen( $setting_b ) > 10 ) { return false; }

    return 1;

  }
```

There are several things to note about this idiom.

In talking about the above code we will call the first function `is_valid_settings()` the "final
wrapper" (or sometimes the "main function') and we call the second function
`do_is_valid_settings()` the "default implementation".

The first thing to note is that the final wrapper `is_valid_settings()` is declared final and thus
cannot be overridden by implementations; and the second thing to note is that the final wrapper
declares the data types on its interface.

In contrast the default implementation `do_is_valid_settings()` is not marked as final, and it
does not declare the types on its interface.

This is an example of
[Postel's Law](https://en.wikipedia.org/wiki/Robustness_principle),
which is also known as the Robustness Principle. The final wrapper is liberal in what it accepts,
such as with the return value one (`1`) from the default implementation; and conservative in what
it does, such as always returning a properly typed boolean value and always providing values of
the correct type to the default implementation.

Not needing to write out and declare the types on the interface of the default implementation also
makes implementation and debugging easier, as there's less code to write. (Also I find the syntax
for return types a bit ugly and have a preference for avoiding it when possible, but that's a
trivial matter.)

Ordinarily users of this code will only call the main function `is_valid_settings()`, and anyone
implementing new code only needs to override `do_is_valid_settings()`.

In general you should always wrap any non-final methods (except for private ones) with a final
method per this idiom, so that you can have callers override functionality as they may want to do
but retain the ability to maintain standards as you may want to do.

If you're refactoring a private method to make it public or protected be sure to introduce the
associated final wrapper.

One last thing: if your component has a public function, it should probably be a final wrapper and
just defer to a default implementation.

Default implementations should pretty much always be protected, certainly not public, and maybe
private if you're not ready to expose the implementation yet.

#### The advantages of the typed interface on the final wrapper

Having types on the interface of the final method `is_valid_settings()` confers three main
advantages.

The first is that the interface is strongly typed, which means your callers can know what to
expect and PHP can take care of fixing up some of the smaller details for us.

The second advantage of this approach is that our final wrapper function is marked as final. This
means that the implementer can maintain particular standards within the library and be assured
that those standards haven't been elided, accidentally or otherwise.

Having code that you rely on marked as final helps you to reason about the possible states of your
component. In the example given above the requirement that `$setting_b` is less than or equal to
20 bytes in length is a requirement that cannot be changed by implementations; implementations can
only make the requirements stronger, such as is done in the default implementation given in the
example, where the maximum length is reduced further to 10 bytes.

Another advantage of the typed interface is that it provides extra information which can be
automatically added into the documentation. The typed interface communicates intent to the PHP
run-time but also to other programmers reading, using, or maintaining the code.

#### The advantages of the untyped interface on the default implementation

Not having types on the interface of the default implementation `do_is_valid_settings()` confers
four main advantages.

The first is that it's easier to type out and maintain the overriding function as you don't need
to worry about writing out the types.

Also, in future, the `is_valid_settings()` might declare a new interface and change its types. If
this happens it can maintain support for both old and new `do_is_valid_settings()` implementations
without implementers necessarily needing to update their code.

The third advantage of an untyped interface for the `do_is_valid_settings()` function is that
it allows for the injection of "impossible" values. These are values which will never be able to
make it past the types declared on the main function `is_valid_settings()` and into the
`do_is_valid_settings()` function, and being able to inject such "impossible" values can make unit
testing of particular situations easier, as you can pass in a value that could never possibly
occur in production in order to signal something from the test in question.

The fourth and perhaps most important implication of the approach to the default implementation
is that it is not marked as final which means that programmers inheriting from your class can
provide a new implementation, thereby replacing, or augmenting, the default implementation.

### Enter/leave idiom and recursion limits

One way a programmer can go wrong is to infinitely recurse. For example like this:

```
class InfiniteRecursion extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  protected function do_encrypt( $input ) {

    return $this->encrypt( $input );

  }
}
```

If the `do_encrypt()` function calls the `encrypt()` function, the `encrypt()` function will call
the `do_encrypt()` function, and then off we go to infinity.

If you do this and you have Xdebug installed and enabled that will limit the call depth to 256
by default. If you don't have Xdebug installed and enabled PHP will just start recurring and will
continue to do so until it hits its memory limit or runs out of RAM.

Since there's pretty much nothing this library can do to stop programmers from accidentally
writing code like the above what we do is to detect when it's probably happened by tracking how
deep our calls are nested using an enter/leave discipline, like this:

```
    try {

      $this->enter( __FUNCTION__ );

      // 2023-04-07 jj5 - do work...

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }
    finally {

      try { $this->leave( __FUNCTION__ ); } catch ( \Throwable $ignore ) { ; }

    }

```

The `leave()` function has no business throwing an exception, but we wrap it in a try-catch block
just in case.

The example code above is shown with typical catch blocks included, but the key point is that
the very first thing we do is register the function entry with the call to `enter()` and then
in our finally block we register the function exit with the call to `leave()`.

If a function enters more than the number of times allowed by KICKASS_CRYPTO_RECURSION_LIMIT
without leaving then an exception is thrown in order to break the recursion. At the time of
writing KICKASS_CRYPTO_RECURSION_LIMIT is defined as 100, which is less than the Xdebug limit of
256, which means we should always be able to break our own recursive loops.

And for all the trouble we've gone to if the inheritor calls themselves and recurs directly there
is nothing to be done:

```
class EpicFail extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  protected function do_encrypt( $input ) {

    return $this->do_encrypt( $input );

  }
}
```


### Return false on error idiom

As mentioned above and elaborated on in the following section this library won't usually throw
exceptions from the methods on its public interface because we don't want to leak secrets from our
call stack if there's a problem.

Instead of throwing exceptions the methods on the classes in this library will usually return
`false` instead, or some other invalid value such as `null` or `[]`.

The avoidance of exceptions is only a firm rule for sensitive function calls which handle secret
keys, passphrases, unencrypted content, or any other sensitive data. At the time of writing it's
possible for the public `get_error_list()` function to throw an exception if the implementer has
returned an invalid value from `do_get_error_list()`, apart from in that specific and hopefully
unlikely situation everything else should be exception safe and use the boolean value false (or
another appropriate sentinel value) to communicate errors to the caller.

Sometimes because of the nature of a typed interface it's not possible to return the boolean value
false and in some circumstances the empty string (`''`), an empty array (`[]`), null (`null`), the
floating-point value zero (`0.0`), or the integer zero (`0`) or minus one (`-1`) may be returned
instead; however, returning false is definitely preferred if it's possible.

Aside: in some cases minus one (`-1`) can be used as the sentinel value to signal an error,
such as when you want to indicate an invalid array index or an invalid count, but unlike in some
other languages in PHP minus one isn't necessarily an invalid array index, and returning false is
still preferred. This library does use minus one in some cases, if there's a problem with
managing the telemetry counters.

The fact that an error has occurred can be registered with your component by a call to `error()`
so that if the callers get a false return value they can interrogate your component with a call to
`get_error()` or `get_error_list()` to get the recent errors (the caller can clear these errors
with `clear_error()` too).

In our library the function for registering that an error has occurred is the `error()` function
defined in the
[KickassCrypto](https://github.com/jj5/kickass-crypto/tree/main/src/code/namespace/KickassCrypto/KickassCrypto.php)
class.

In some error situations the best and safest thing to do is swallow the error and return a
sensible and safe and uncontroversial default value as a fallback.

### Catch and throw idiom

This library is very particular about exception handling and error reporting.

If you have sensitive data on your call stack you must not throw exceptions. Sensitive data
includes:

- secret keys or other secrets
- passphrases
- unencrypted data
- PHP Exception and Throwable objects
- potentially other things

If you encounter a situation from which you cannot continue processing of the typical and expected
program logic the way to register this problem is by calling the `error()` function with a string
identifying and describing the problem and then returning false to indicate failure.

As the `error()` function always returns the boolean value false you can usually register the
error and return false on the same like, like this:

```
  return $this->error( __FUNCTION__, 'something bad happened.' );
```

When I nominate error strings I usually start them with a lowercase letter and end them with a
period.

Note that it's okay to intercept and rethrow PHP AssertionError exceptions. These should only ever
occur during development and not in production. If you're calling code you don't trust you might
not wish to rethrow AssertionError exceptions, but if you're calling code you don't trust you've
probably got bigger problems in life.

If you have a strong opinion regarding AssertionError exceptions and think I should not rethrow
them I would be happy to hear from you to understand your concern and potentially address the
issue.

Following is some example code showing how to handle exceptions and manage errors.

```
  protected final function do_work_with_secret( $secret ) {

    try {

      $result = str_repeat( $secret, 2 );

      $this->call_some_function_you_might_not_control( $result );

      return $result;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        try {

          $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

        }
        catch ( \Throwable $ignore ) { ; }

      }
    }

    try {

      return $this->error( __FUNCTION__, 'error working with string.' );

    }
    catch ( \Throwable $ignore ) {

      try {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) { ; }

    }

    return false;

  }
```

In actual code you would define an error constant for use instead of the string literal `'error
working with string.'`. In this library the names of error constants begin with
"KICKASS_CRYPTO_ERROR_" and they are defined in the
[src/code/global/constant/framework.php](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/constant/framework.php)
file.

Note that we don't even assume it's safe to call `handle()`, `ignore()`, or `error()`; we wrap all
such calls in try-catch handlers too. There are some edge case situations where even these
functions which are supposed to be thread safe can lead to exceptions, such as when there's
infinite recursion which gets aborted by the run-time. If you're an expert on such matters the
code might do with a review from you.

Now I will agree that the above code is kind of insane, it's just that it seems to me like there's
no avoiding it if we want to be safe. We have to explicitly allow the AssertionError exception
every single time in every single method just so that assertions remain useful to us as a
development tool, and then when we handle other exceptions we want to make some noise about them
so we call `handle()`, but the thing is that `handle()` will defer to `do_handle()` which can be
overridden by implementers, which means it can throw... so if `handle()` throws we don't want to
just do nothing, we want to give the programmer a last chance to learn of their errant code,
so we notify that we're going to ignore the exception with a call to `ignore()`, but that will
defer to `do_ignore()`, which the programmer could override, and throw from... but if that
happens we will just silently ignore such a problem.

And then if we get through all of that and our function hasn't returned then that's an error
situation so we want to notify the error, but `error()` defers to `do_error()` and that could
be overridden and throw, so we wrap in a try-catch block and then do the exception ignore dance
again.

I mean it's all over the top and excessive but it should at least be safe and it meets two
requirements:

- exceptions will not leak sensitive data on the call stack.
- programmers are given the best chance to find out that exceptions or errors are occurring.

In the usual happy code path none of the exception handling code even runs.

### The is_() functions for boolean tests

There are a bunch of functions for testing boolean conditions, and they begin with "is_" and
return a boolean. These functions should only do the test and return true or false, they should
_not_ register errors using the `error()` function, if that's necessary the caller will do that.

The is_() functions can be implemented using the typed final wrapper idiom documented above.

Following is a good example from the code.

```
  protected final function is_valid_secret( $secret ) : bool {

    try {

      $is_valid = $this->do_is_valid_secret( $secret );

      // ...

      assert( is_bool( $is_valid ) );

      return $is_valid;

    }
    catch ( \AssertionError $ex ) {

      throw $ex;

    }
    catch ( \Throwable $ex ) {

      try {

        $this->handle( $ex, __FILE__, __LINE__, __FUNCTION__ );

      }
      catch ( \Throwable $ignore ) {

        $this->ignore( $ignore, __FILE__, __LINE__, __FUNCTION__ );

      }
    }

    return false;

  }
```

Note that `do_is_valid_secret()` _also_ has a secret on the call stack, so it should be
implemented as exception safe in the same way (in case it is called directly from some other part
of the code).

Note too that it's okay to just rethrow assertion violations, these should never happen in
production and they make testing the code easier.

### Tests are scripts idiom

The approach to unit-testing taken by this library is simple and powerful. There are three types
of test which can be defined for each unit test:

- fast
- slow
- silly

Each script will be either a shell script with the same name, e.g. `fast.sh`, or if that's missing
a PHP script with the same name, e.g. `fast.php`. The test runner just finds these scripts and
runs them. This is easy to do and provides all the power we need to run our tests, including
support for the various situations where each test instance needs to run in its own process and be
isolated from other testing environments.

If you have flakey and unreliable tests you can stick them in as silly tests. The fast and slow
tests are the important ones, and you shouldn't put slow tests in the fast test scripts. The fast
tests are for day to day programming and testing and the slow scripts are for running prior to a
version release.

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
* [inc/](https://github.com/jj5/kickass-crypto/tree/main/inc/): include files
* [inc/framework.php](https://github.com/jj5/kickass-crypto/tree/main/inc/framework.php): the include file for the base framework if you want to build your own crypto module on top of it
* [inc/library.php](https://github.com/jj5/kickass-crypto/tree/main/inc/library.php): this loads the entire library and both crypto modules
* [inc/openssl.php](https://github.com/jj5/kickass-crypto/tree/main/inc/openssl.php): the OpenSSL implementation
* [inc/sodium.php](https://github.com/jj5/kickass-crypto/tree/main/inc/sodium.php): the Sodium implementation
* [inc/test-host.php](https://github.com/jj5/kickass-crypto/tree/main/inc/test-host.php): the include file for the unit testing framework
* [inc/test.php](https://github.com/jj5/kickass-crypto/tree/main/inc/test.php): the include file for the unit testing toolkit
* [inc/utility.php](https://github.com/jj5/kickass-crypto/tree/main/inc/utility.php): the include file for the utility functions
* etc/: library configuration files (planned)
* [src/](https://github.com/jj5/kickass-crypto/tree/main/src/): PHP source code
* [src/code/](https://github.com/jj5/kickass-crypto/tree/main/src/code/): the PHP source code
* [src/code/global/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/): global PHP functions
* [src/code/global/autoload/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/autoload/): PHP autoloading functions
* [src/code/global/constant/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/constant/): PHP constants
* [src/code/global/environment/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/environment/): environment validation
* [src/code/global/host/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/host/): process hosts, presently for unit-testing
* [src/code/global/php/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/php/): PHP version validation
* [src/code/global/server/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/server/): service locators for library modules
* [src/code/global/test/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/test/): helpers for use during testing
* [src/code/global/utility/](https://github.com/jj5/kickass-crypto/tree/main/src/code/global/utility/): utility code
* [src/code/namespace/](https://github.com/jj5/kickass-crypto/tree/main/src/code/namespace/): namespaced PHP components (classes, interfaces, and traits)
* [src/demo/](https://github.com/jj5/kickass-crypto/tree/main/src/demo/): a web-client for demonstration purposes
* [src/test/](https://github.com/jj5/kickass-crypto/tree/main/src/test/): unit tests:
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

## Copyright

Copyright (c) 2023 John Elliot V.

## License

This code is licensed under the
[MIT License](https://github.com/jj5/kickass-crypto/tree/main/LICENSE).

## Contributors

See the [contributors](https://github.com/jj5/kickass-crypto/tree/main/CONTRIBUTORS.md) file.

## Commit messages

I should probably be more disciplined with my commit messages... if this library matures and gets
widely used I will try to be more careful with my commits.

## Colophon

The Kickass Crypto ASCII banner is in the Graffiti font courtesy of
[TAAG](http://www.patorjk.com/software/taag/#p=display&f=Graffiti&t=Kickass%20Crypto).

The string "kickass" appears in the source code 1,423 times (including the ASCII banners).

SLOC and file count reports generated using David A. Wheeler's 'SLOCCount'.

## Comments? Questions? Suggestions?

I'd love to hear from you! Hit me up at
[jj5@jj5.net](mailto:jj5@jj5.net?subject=Kickass%20Crypto).
Put "Kickass Crypto" in
the subject line to make it past my mail filters.
