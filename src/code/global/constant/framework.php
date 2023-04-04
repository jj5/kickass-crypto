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

// 2023-03-30 jj5 - these are the current data format versions for this library. If you fork this
// library and alter the data format you should change these. If you do change this please use
// something other than 'KA' as the prefix. If you don't want the data format version reported
// in your encoded data override the encode() and decode() methods.
//
// 2023-04-02 jj5 - NOTE: you don't need to actually change this constant, you can just override
// get_const_data_format_version() and return a different string. For example:
//
// protected function get_const_data_format_version() { return 'MYKA1'; }
//
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_OPENSSL', 'KA0' );
define( 'KICKASS_CRYPTO_DATA_FORMAT_VERSION_SODIUM', 'KAS0' );

// 2023-03-30 jj5 - these are the default values for configuration... these might be changed in
// future... note that 2^12 is 4KiB and 2^26 is 64 MiB.
//
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE', pow( 2, 12 ) );
define( 'KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX', pow( 2, 26 ) );
define( 'KICKASS_CRYPTO_DEFAULT_JSON_LENGTH_MAX', pow( 2, 26 ) );
define(
  'KICKASS_CRYPTO_DEFAULT_JSON_ENCODE_OPTIONS',
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
define( 'KICKASS_CRYPTO_DEFAULT_JSON_DECODE_OPTIONS', JSON_THROW_ON_ERROR );

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
//
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED', 'exception raised.' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2', 'exception raised (2).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3', 'exception raised (3).' );
define( 'KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4', 'exception raised (4).' );
define( 'KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED', 'JSON encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED', 'JSON decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_ENCODING', 'invalid encoding.' );
define( 'KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING', 'unknown encoding.' );
define( 'KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED', 'base64 decode failed.' );
define( 'KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE', 'cannot encrypt false.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE', 'invalid passphrase.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH', 'invalid passphrase length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2', 'invalid passphrase length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE', 'invalid chunk size.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH', 'invalid binary length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH', 'invalid IV length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2', 'invalid IV length (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH', 'invalid tag length.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2', 'invalid tag length (2).' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED', 'encryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2', 'encryption failed (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT', 'invalid ciphertext.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2', 'invalid ciphertext (2).' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_DATA', 'invalid data.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT', 'invalid message format.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_SPEC', 'invalid data length spec.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE', 'invalid data length range.' );
define( 'KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_LENGTH', 'invalid message length.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED', 'data encoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE', 'data encoding too large.' );
define( 'KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED', 'data decoding failed.' );
define( 'KICKASS_CRYPTO_ERROR_NO_VALID_KEY', 'no valid key.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED', 'decryption failed.' );
define( 'KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2', 'decryption failed (2).' );
