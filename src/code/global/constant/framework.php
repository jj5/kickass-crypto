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
// 2023-04-04 jj5 - these are the constants used by the library.
//
// 2023-04-04 jj5 - NOTE: these constants are *constants* and not configuration settings. If you
// need to override any of these, for instance to test the correct handling of error scenarios,
// pelase override the relevant get_const_*() accessor in the KickassCrypto class, don't edit
// these... please see the documentation in README.md for an explanation of these values.
//
\************************************************************************************************/

define( 'KICKASS_CRYPTO_KEY_HASH', 'sha512/256' );
define( 'KICKASS_CRYPTO_KEY_LENGTH_MIN', 88 );

// 2023-04-04 jj5 - the encoding format strings must be four characters long...
//
define( 'KICKASS_CRYPTO_DATA_ENCODING_JSON', 'json' );
define( 'KICKASS_CRYPTO_DATA_ENCODING_PHPS', 'phps' );

// 2023-04-02 jj5 - NOTE: you don't need to actually change this constant, you can just override
// get_const_data_format_version() and return a different string. For example:
//
// protected function do_get_const_data_format_version() { return 'MYKA1'; }
//
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_OPENSSL', 'KA0' );
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_SODIUM', 'KAS0' );

// 2023-03-30 jj5 - these are the default values for configuration... these might be changed in
// future... note that 2^12 is 4KiB and 2^26 is 64 MiB.
//
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', pow( 2, 12 ) );
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX', pow( 2, 26 ) );
define( 'KICKASS_CRYPTO_DEFAULT_DATA_ENCODING', KICKASS_CRYPTO_DATA_ENCODING_JSON );
define( 'KICKASS_CRYPTO_DEFAULT_DATA_LENGTH_MAX', pow( 2, 26 ) );
define(
  'KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS',
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
define( 'KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );

// 2023-04-05 jj5 - PHP serialization is disabled by default because it can lead to code execution
// vulnerabilities... (I don't have a source for this claim, that might be a rumor or not true
// any more).
//
// 2023-04-05 jj5 - also note that you would only deserialize data which was successfully
// encrypted, so that would presumably make it harder for an attacker to inject code via PHP
// serialization format.
//
// 2023-04-05 jj5 - if you want to enable PHP serialization you will need these two defines in
// your config file:
//
// define( 'CONFIG_ENCRYPTION_PHPS_ENABLE', true );
// define( 'CONFIG_ENCRYPTION_DATA_ENCODING', KICKASS_CRYPTO_DATA_ENCODING_PHPS );
//
define( 'KICKASS_CRYPTO_DEFAULT_PHPS_ENABLE', false );

// 2023-04-05 jj5 - by default you can't encrypt false...
//
define( 'KICKASS_CRYPTO_DEFAULT_FALSE_ENABLE', false );

// 2023-03-29 jj5 - these delays are in nanoseconds, these might be changed in future...
//
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN',      1_000_000 );
define( 'KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX', 10_000_000_000 );

// 2023-04-03 jj5 - this delay is a floating-point value in seconds, it's for comparison of the
// value returned from the PHP microtime()...
//
define(
  'KICKASS_CRYPTO_DELAY_SECONDS_MIN',
  1.0 / ( KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN / 1_000 )
);

// 2023-03-30 jj5 - this is our Base64 validation regex...
//
define(
  'KICKASS_CRYPTO_REGEX_BASE64',
  // 2023-04-01 jj5 - SEE: https://www.progclub.org/blog/2023/04/01/php-preg_match-regex-fail/
  // 2023-04-01 jj5 - NEW:
  '/^[a-zA-Z0-9\/+]{2,}={0,2}$/'
  // 2023-04-01 jj5 - OLD: this old base64 validation regex had some really bad performance
  // characteristics when tested with pathological inputs such as 2^17 zeros, see the article
  // about the problem at the link above.
  //'/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/'
);

// 2023-03-29 jj5 - exceptions are thrown from the constructor only, these are the possible
// exceptions. The exception codes should be stable, you can add new ones but don't change
// existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE',  1_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG',          2_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH',        3_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER',          4_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH',       5_000 );
define( 'KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM',         6_000 );

// 2023-03-30 jj5 - these are the exception messages for each exception code. These exception
// messages should be stable, you can add new ones but don't change existing ones.
//
define( 'KICKASS_CRYPTO_EXCEPTION_MESSAGE', [
  KICKASS_CRYPTO_EXCEPTION_INVALID_EXCEPTION_CODE => 'invalid exception code.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG         => 'invalid config.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_KEY_HASH       => 'invalid key hash.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_CIPHER         => 'invalid cipher.',
  KICKASS_CRYPTO_EXCEPTION_INVALID_IV_LENGTH      => 'invalid IV length.',
  KICKASS_CRYPTO_EXCEPTION_INSECURE_RANDOM        => 'insecure random.',
]);

// 2023-03-30 jj5 - these are the errors that can happen during encryptiong and decryption, we
// don't raise exceptions for these errors because a secret key or a passphrase might be on the
// call stack and we don't want to accidentally leak it. If an error occurs the boolean value
// false is returned and the error constant is added to the error list. Sometimes the same basic
// error can happen from multiple code points; when that happens we add a number in the hope that
// later we can find the specific point in the code which flagged the error.

define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED', 'exception raised.' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2', 'exception raised (2).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3', 'exception raised (3).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4', 'exception raised (4).' );

define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID', 'data encoding invalid.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_2', 'data encoding invalid (2).' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_3', 'data encoding invalid (3).' );

define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE', 'data encoding too large.' );

define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED', 'data encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_2', 'data encoding failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_3', 'data encoding failed (3).' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_4', 'data encoding failed (4).' );

define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED', 'JSON encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_2', 'JSON encoding failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_3', 'JSON encoding failed (3).' );

define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED', 'PHPS encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_2', 'PHPS encoding failed (2).' );

define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED', 'PHPS encoding disabled.' );
define( 'KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED_2', 'PHPS encoding disabled (2).' );

define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED', 'data decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_2', 'data decoding failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_3', 'data decoding failed (3).' );
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_4', 'data decoding failed (4).' );

define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED', 'JSON decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_2', 'JSON decoding failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_3', 'JSON decoding failed (3).' );
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_4', 'JSON decoding failed (4).' );

define( 'KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED', 'PHPS decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED_2', 'PHPS decoding failed (2).' );

define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED', 'message encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_2', 'message encoding failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_3', 'message encoding failed (3).' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_4', 'message encoding failed (4).' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_5', 'message encoding failed (5).' );

define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DECODING_FAILED', 'message decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_INVALID', 'message encoding invalid.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_UNKNOWN', 'message encoding unknown.' );

define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED', 'base64 decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2', 'base64 decoding failed (2).' );

define( 'KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE', 'cannot encrypt false.' );

define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_INVALID', 'passphrase invalid.' );
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID', 'passphrase length invalid.' );
define( 'KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID_2', 'passphrase length invalid (2).' );

define( 'KICKASS_CRYPTO_ERROR_CHUNK_SIZE_INVALID', 'chunk size invalid.' );
define( 'KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID', 'binary length invalid.' );
define( 'KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID', 'IV length invalid.' );
define( 'KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID_2', 'IV length invalid (2).' );
define( 'KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID', 'tag length invalid.' );
define( 'KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID_2', 'tag length invalid (2).' );

define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );

define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED', 'decryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2', 'decryption failed (2).' );

define( 'KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID', 'ciphertext invalid.' );
define( 'KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID_2', 'ciphertext invalid (2).' );
define( 'KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID', 'binary data invalid.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID', 'message format invalid.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_SPEC_INVALID', 'message data length spec invalid.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID', 'message data length range invalid.' );
define( 'KICKASS_CRYPTO_ERROR_MESSAGE_LENGTH_INVALID', 'message length invalid.' );

define( 'KICKASS_CRYPTO_ERROR_NO_VALID_KEY', 'no valid key.' );
