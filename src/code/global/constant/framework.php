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

/**
 * 2023-04-04 jj5 - these are the constants used by the library.
 *
 * 2023-04-04 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
 * need to override any of these, for instance to test the correct handling of error scenarios,
 * pelase override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
 * these... please see the documentation in README.md for an explanation of these values.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

/**
 * 2023-04-05 jj5 - the key has is used to convert a secret key into a 32 byte (256-bit)
 * passphrase for use with either the OpenSSL or Sodium encryption library.
 *
 * @var string
 */
define( 'KICKASS_CRYPTO_KEY_HASH', 'sha512/256' );

/**
 * 2023-04-05 jj5 - the minimum key length is used to ensure that secret keys meet at least a
 * minimal requirement.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_KEY_LENGTH_MIN', 88 );

/**
 * 2023-04-05 jj5 - the minimum key length is used to ensure that passphrases meet at least a
 * minimal requirement.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_PASSPHRASE_LENGTH_MIN', 32 );

/**
 * 2023-04-04 jj5 - the JSON format uses the PHP json_encode() and json_decode() functions; the
 * encoding format strings must be four characters long.
 *
 * @var string
 */
define( 'KICKASS_CRYPTO_DATA_ENCODING_JSON', 'json' );

/**
 * 2023-04-04 jj5 - uses the PHP serialize() and unserialize() functions (not enabled by default
 * due to potential security issues, enable with CONFIG_ENCRYPTION_PHPS_ENABLE); the encoding
 * format strings must be four characters long.
 *
 * @var string
 */
define( 'KICKASS_CRYPTO_DATA_ENCODING_PHPS', 'phps' );

// 2023-04-02 jj5 - NOTE: you don't need to actually change the following constants, you can just
// override do_get_const_data_format() and return a different string. For example:
//
// protected function do_get_const_data_format() { return 'MYKA1'; }
//
// 2023-04-05 jj5 - NOTE: at the moment we're using version zero data format constants; we will
// make these version one when the library is ready for its first release.
//
// 2023-04-05 jj5 - NOTE: the data format relates to the formatting of the binary data generated
// and expected by the encryption library, it's not the message encoding or data encoding, which
// are two other separate things.

/**
 * 2023-04-05 jj5 - the OpenSSL data format...
 *
 * @var string
*/
define( 'KICKASS_CRYPTO_DATA_FORMAT_OPENSSL', 'KA0' );

/**
 * 2023-04-05 jj5 - the Sodium data format...
 *
 * @var string
 */
//
define( 'KICKASS_CRYPTO_DATA_FORMAT_SODIUM', 'KAS0' );

/**
 * 2023-04-05 jj5 - the data format indicator must meet this minimum character length...
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_DATA_FORMAT_LENGTH_MIN', 2 );

/**
 * 2023-04-05 jj5 - the data format indicator must meet this maximum character length...
 *
 * @var int
*/
define( 'KICKASS_CRYPTO_DATA_FORMAT_LENGTH_MAX', 8 );

/**
 * 2023-04-05 jj5 - this is the default chunk size; messages are padded up to the message length
 * modulo the chunk size; 2^12 is 4KiB; this value might be changed in future.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', pow( 2, 12 ) );

/**
 * 2023-04-05 jj5 - this is the default maximum chunk size; 2^26 is 64 MiB; this value might be
 * changed in future.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX', pow( 2, 26 ) );

/**
 * 2023-04-05 jj5 - default data encoding format is JSON.
 *
 * @var string
 */
define( 'KICKASS_CRYPTO_DEFAULT_DATA_ENCODING', KICKASS_CRYPTO_DATA_ENCODING_JSON );

/**
 * 2023-04-05 jj5 - this is the maximum data length supported; 2^26 is 64 MiB; this value might be
 * changed in future.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_DEFAULT_DATA_LENGTH_MAX', pow( 2, 26 ) );

/**
 * 2023-04-05 jj5 - these are the default JSON encoding options passed to the PHP json_encode()
 * function.
 *
 * @var int
 */
define(
  'KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS',
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

/**
 * 2023-04-05 jj5 - these are the default JSON decoding options passed to the PHP json_decode()
 * function.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );

/**
 * 2023-04-05 jj5 - PHP serialization is disabled by default because it can lead to code execution
 * vulnerabilities... (I don't have a source for this claim, that might be a rumor or not true
 * any more).
 *
 * 2023-04-05 jj5 - also note that you would only deserialize data which was successfully
 * encrypted, so that would presumably make it harder for an attacker to inject code via PHP
 * serialization format.
 *
 * 2023-04-05 jj5 - if you want to enable PHP serialization you will need these two defines in
 * your config file:
 *
 * define( 'CONFIG_ENCRYPTION_PHPS_ENABLE', true );
 *
 * define( 'CONFIG_ENCRYPTION_DATA_ENCODING', KICKASS_CRYPTO_DATA_ENCODING_PHPS );
 *
 * @var boolean
 */
define( 'KICKASS_CRYPTO_DEFAULT_PHPS_ENABLE', false );

/**
 * 2023-04-05 jj5 - by default you can't encrypt false...
 *
 * @var boolean
*/
define( 'KICKASS_CRYPTO_DEFAULT_FALSE_ENABLE', false );

/**
 * 2023-04-05 jj5 - the minimum random delay (in nanoseconds) used for timing attack mitigation;
 * this value might be changed in future.
 *
 * @var int
*/
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN',      1_000_000 );

/**
 * 2023-04-05 jj5 - the maximum random delay (in nanoseconds) used for timing attack mitigation;
 * this value might be changed in future.
 *
 * @var int
*/
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX', 10_000_000_000 );

/**
 * 2023-04-03 jj5 - this delay is a floating-point value in seconds, it's for comparison of the
 * value returned from the PHP microtime() to check that our delay implementation did actually
 * delay for at least this minimum amount of time.
 *
 * @var float
 */
define(
  'KICKASS_CRYPTO_DELAY_SECONDS_MIN',
  1.0 / ( KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN / 1_000 )
);

/**
 * 2023-04-05 jj5 - this is our Base64 validation regex; see the link for discussion concerning
 * the previous regex and poor performance.
 *
 * @link https://www.progclub.org/blog/2023/04/01/php-preg_match-regex-fail/
 *
 * @var string
 */
define(
  'KICKASS_CRYPTO_REGEX_BASE64',
  // 2023-04-01 jj5 - NEW:
  '/^[a-zA-Z0-9\/+]{2,}={0,2}$/'
  // 2023-04-01 jj5 - OLD: this old base64 validation regex had some really bad performance
  // characteristics when tested with pathological inputs such as 2^17 zeros, see the article
  // about the problem at the link above.
  //'/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/'
);

/**
 * 2023-04-07 jj5 - this is the limit of recursion that we allow... the Xdebug limit is 256 and
 * PHP by itself has no limit (it will recurse until it runs out of memory); we pick a value less
 * than the Xdebug limit so that we can handle things ourselves.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_RECURSION_LIMIT', 100 );

// 2023-03-29 jj5 - exceptions are thrown from the constructor only, these are the possible
// exceptions. The exception codes should be stable, you can add new ones but don't change
// existing ones.

/**
 * 2023-04-05 jj5 - if an invalid exception code is passed to the exception raising facility
 * this exception is raised instead.
 *
 * @var int
*/
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE',  1_000 );

/**
 * 2023-04-05 jj5 - this exception is raise if the config is invalid. Modules define what
 * constitutes a valid config based on the use case.
 *
 * 2023-04-05 jj5 - possible combinations of encryption module and use case are:
 *
 * - Sodium round-trip
 * - Sodium at-rest
 * - OpenSSL round-trip
 * - OpenSSL at-rest
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG',          2_000 );

/**
 * 2023-04-05 jj5 - if the hash algorithm is invalid or not available this exception is raised.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH',        3_000 );

/**
 * 2023-04-05 jj5 - if the cipher nominated for the OpenSSL library is not available in the
 * environment this exception is raise.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER',          4_000 );

/**
 * 2023-04-05 jj5 - if the initialization vector for the OpenSSL library is not what we've been
 * coded to expect then this exception is raise.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH',       5_000 );

/**
 * 2023-04-05 jj5 - if the PHP random_bytes() implementation is not using a secure PRNG then this
 * exception is supposed to be raised.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM',         6_000 );

/**
 * 2023-04-06 jj5 - if we can't get a valid error list from an implementation we bail with an
 * exception.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_ERROR_LIST',      7_000 );

/**
 * 2023-04-07 jj5 - called if recursion/reentrancy is detected.
 *
 * @var int
 */
define( 'KICKASS_CRYPTO_EXCEPTION_RECURSION_DETECTED',      8_000 );

/**
 * 2023-03-30 jj5 - these are the exception messages for each exception code. These exception
 * messages should be stable, you can add new ones but don't change existing ones.
 *
 * @var array<int, string>
 */
define( 'KICKASS_CRYPTO_EXCEPTION_MESSAGE', [
  KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE => 'invalid exception code.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG         => 'invalid config.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH       => 'invalid key hash.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER         => 'invalid cipher.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH      => 'invalid IV length.',
  KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM        => 'insecure random.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_ERROR_LIST     => 'invalid error list.',
  KICKASS_CRYPTO_EXCEPTION_RECURSION_DETECTED     => 'recursion detected.',
]);

// 2023-03-30 jj5 - following are the errors that can happen during encryptiong and decryption, we
// don't raise exceptions for these errors because a secret key or a passphrase might be on the
// call stack and we don't want to accidentally leak it. If an error occurs the boolean value
// false is returned and the error constant is added to the error list. Sometimes the same basic
// error can happen from multiple code points; when that happens we add a number in the hope that
// later we can find the specific point in the code which flagged the error.

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED', 'exception raised.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2', 'exception raised (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3', 'exception raised (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4', 'exception raised (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID', 'data encoding invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_2', 'data encoding invalid (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_3', 'data encoding invalid (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE', 'data encoding too large.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED', 'data encoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_2', 'data encoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_3', 'data encoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_4', 'data encoding failed (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED', 'JSON encoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_2', 'JSON encoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_3', 'JSON encoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_4', 'JSON encoding failed (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED', 'PHPS encoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_2', 'PHPS encoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_3', 'PHPS encoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED', 'PHPS encoding disabled.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED_2', 'PHPS encoding disabled (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED', 'data decoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_2', 'data decoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_3', 'data decoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_4', 'data decoding failed (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED', 'JSON decoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_2', 'JSON decoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_3', 'JSON decoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_4', 'JSON decoding failed (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED', 'PHPS decoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED_2', 'PHPS decoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED', 'message encoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_2', 'message encoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_3', 'message encoding failed (3).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_4', 'message encoding failed (4).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DECODING_FAILED', 'message decoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_INVALID', 'message encoding invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_UNKNOWN', 'message encoding unknown.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED', 'base64 decoding failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2', 'base64 decoding failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE', 'cannot encrypt false.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_MISSING', 'passphrase missing.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID', 'passphrase invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID', 'passphrase length invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID_2', 'passphrase length invalid (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_CHUNK_SIZE_INVALID', 'chunk size invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID', 'binary length invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID', 'IV length invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID_2', 'IV length invalid (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID', 'tag length invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID_2', 'tag length invalid (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED', 'decryption failed.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2', 'decryption failed (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID', 'ciphertext invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID_2', 'ciphertext invalid (2).' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID', 'binary data invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID', 'message format invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_SPEC_INVALID', 'message data length spec invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID', 'message data length range invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_LENGTH_INVALID', 'message length invalid.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_ERROR_NO_VALID_KEY', 'no valid key.' );

//
// 2023-04-06 jj5 - following are log messages and prefixes...
//

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_ERROR_INVALID_PASSPHRASE', 'error: invalid passphrase.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_WARNING_SHORT_SECRET', 'warning: secret shorter than recommended.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_WARNING_SHORT_PASSPHRASE', 'warning: passphrase shorter than recommended.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_WARNING_DELAY', 'warning: delayed due to error.' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_PREFIX_EMERGENCY_DELAY', 'emergency delay: ' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_HANDLE', 'handled exception: ' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_NOTIFY', 'caught exception: ' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_IGNORE', 'ignored exception: ' );

/**
 * @var string
 */
define( 'KICKASS_CRYPTO_LOG_PREFIX_EXCEPTION_THROW', 'throwing exception: ' );
