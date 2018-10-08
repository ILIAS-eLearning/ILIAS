/*
    +--------------------------------------------------------------------+
    | PECL :: http                                                       |
    +--------------------------------------------------------------------+
    | Redistribution and use in source and binary forms, with or without |
    | modification, are permitted provided that the conditions mentioned |
    | in the accompanying LICENSE file are met.                          |
    +--------------------------------------------------------------------+
    | Copyright (c) 2004-2005, Michael Wallner <mike@php.net>            |
    +--------------------------------------------------------------------+
*/

/* $Id$ */

#ifdef HAVE_CONFIG_H
#	include "config.h"
#endif
#include "php.h"

#ifdef HTTP_HAVE_CURL

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_api.h"
#include "php_http_request_api.h"
#include "php_http_request_method_api.h"
#include "php_http_url_api.h"
#ifdef ZEND_ENGINE_2
#	include "php_http_request_object.h"
#endif

#include "phpstr/phpstr.h"

#ifdef PHP_WIN32
#	include <winsock2.h>
#endif

#include <curl/curl.h>

/* {{{ cruft for thread safe SSL crypto locks */
#if defined(ZTS) && defined(HTTP_HAVE_SSL)
#	ifdef PHP_WIN32
#		define HTTP_NEED_SSL_TSL
#		define HTTP_NEED_OPENSSL_TSL
#		include <openssl/crypto.h>
#	else /* !PHP_WIN32 */
#		if defined(HTTP_HAVE_OPENSSL)
#			if defined(HAVE_OPENSSL_CRYPTO_H)
#				define HTTP_NEED_SSL_TSL
#				define HTTP_NEED_OPENSSL_TSL
#				include <openssl/crypto.h>
#			else
#				warning \
					"libcurl was compiled with OpenSSL support, but configure could not find " \
					"openssl/crypto.h; thus no SSL crypto locking callbacks will be set, which may " \
					"cause random crashes on SSL requests"
#			endif
#		elif defined(HTTP_HAVE_GNUTLS)
#			if defined(HAVE_GCRYPT_H)
#				define HTTP_NEED_SSL_TSL
#				define HTTP_NEED_GNUTLS_TSL
#				include <gcrypt.h>
#			else
#				warning \
					"libcurl was compiled with GnuTLS support, but configure could not find " \
					"gcrypt.h; thus no SSL crypto locking callbacks will be set, which may " \
					"cause random crashes on SSL requests"
#			endif
#		else
#			warning \
				"libcurl was compiled with SSL support, but configure could not determine which" \
				"library was used; thus no SSL crypto locking callbacks will be set, which may " \
				"cause random crashes on SSL requests"
#		endif /* HTTP_HAVE_OPENSSL || HTTP_HAVE_GNUTLS */
#	endif /* PHP_WIN32 */
#endif /* ZTS && HTTP_HAVE_SSL */
/* }}} */

ZEND_EXTERN_MODULE_GLOBALS(http);

#ifdef HTTP_NEED_SSL_TSL
static inline void http_ssl_init(void);
static inline void http_ssl_cleanup(void);
#endif

PHP_MINIT_FUNCTION(http_request)
{
#ifdef HTTP_NEED_SSL_TSL
	http_ssl_init();
#endif

	if (CURLE_OK != curl_global_init(CURL_GLOBAL_ALL)) {
		return FAILURE;
	}
	
#if LIBCURL_VERSION_NUM >= 0x070a05
	HTTP_LONG_CONSTANT("HTTP_AUTH_BASIC", CURLAUTH_BASIC);
	HTTP_LONG_CONSTANT("HTTP_AUTH_DIGEST", CURLAUTH_DIGEST);
	HTTP_LONG_CONSTANT("HTTP_AUTH_NTLM", CURLAUTH_NTLM);
	HTTP_LONG_CONSTANT("HTTP_AUTH_ANY", CURLAUTH_ANY);
#endif /* LIBCURL_VERSION_NUM */

	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(http_request)
{
	curl_global_cleanup();
#ifdef HTTP_NEED_SSL_TSL
	http_ssl_cleanup();
#endif
	return SUCCESS;
}

#ifndef HAVE_CURL_EASY_STRERROR
#	define curl_easy_strerror(code) HTTP_G(request).error
#endif

#define HTTP_CURL_INFO(I) HTTP_CURL_INFO_EX(I, I)
#define HTTP_CURL_INFO_EX(I, X) \
	switch (CURLINFO_ ##I & ~CURLINFO_MASK) \
	{ \
		case CURLINFO_STRING: \
		{ \
			char *c; \
			if (CURLE_OK == curl_easy_getinfo(ch, CURLINFO_ ##I, &c)) { \
				add_assoc_string(&array, pretty_key(http_request_data_copy(COPY_STRING, #X), sizeof(#X)-1, 0, 0), c ? c : "", 1); \
			} \
		} \
		break; \
\
		case CURLINFO_DOUBLE: \
		{ \
			double d; \
			if (CURLE_OK == curl_easy_getinfo(ch, CURLINFO_ ##I, &d)) { \
				add_assoc_double(&array, pretty_key(http_request_data_copy(COPY_STRING, #X), sizeof(#X)-1, 0, 0), d); \
			} \
		} \
		break; \
\
		case CURLINFO_LONG: \
		{ \
			long l; \
			if (CURLE_OK == curl_easy_getinfo(ch, CURLINFO_ ##I, &l)) { \
				add_assoc_long(&array, pretty_key(http_request_data_copy(COPY_STRING, #X), sizeof(#X)-1, 0, 0), l); \
			} \
		} \
		break; \
	}

#define HTTP_CURL_OPT(OPTION, p) curl_easy_setopt(ch, CURLOPT_##OPTION, (p))
#define HTTP_CURL_OPT_STRING(keyname) HTTP_CURL_OPT_STRING_EX(keyname, keyname)
#define HTTP_CURL_OPT_SSL_STRING(keyname) HTTP_CURL_OPT_STRING_EX(keyname, SSL##keyname)
#define HTTP_CURL_OPT_SSL_STRING_(keyname) HTTP_CURL_OPT_STRING_EX(keyname, SSL_##keyname)
#define HTTP_CURL_OPT_STRING_EX(keyname, optname) \
	if (!strcasecmp(key, #keyname)) { \
		convert_to_string_ex(param); \
		HTTP_CURL_OPT(optname, http_request_data_copy(COPY_STRING, Z_STRVAL_PP(param))); \
		key = NULL; \
		continue; \
	}
#define HTTP_CURL_OPT_LONG(keyname) HTTP_OPT_SSL_LONG_EX(keyname, keyname)
#define HTTP_CURL_OPT_SSL_LONG(keyname) HTTP_CURL_OPT_LONG_EX(keyname, SSL##keyname)
#define HTTP_CURL_OPT_SSL_LONG_(keyname) HTTP_CURL_OPT_LONG_EX(keyname, SSL_##keyname)
#define HTTP_CURL_OPT_LONG_EX(keyname, optname) \
	if (!strcasecmp(key, #keyname)) { \
		convert_to_long_ex(param); \
		HTTP_CURL_OPT(optname, Z_LVAL_PP(param)); \
		key = NULL; \
		continue; \
	}

#define http_curl_getopt(o, k, t) _http_curl_getopt_ex((o), (k), sizeof(k), (t) TSRMLS_CC)
#define http_curl_getopt_ex(o, k, l, t) _http_curl_getopt_ex((o), (k), (l), (t) TSRMLS_CC)
static inline zval *_http_curl_getopt_ex(HashTable *options, char *key, size_t keylen, int type TSRMLS_DC);
#define http_curl_defaults(ch) _http_curl_defaults((ch))
static inline void _http_curl_defaults(CURL *ch);
static size_t http_curl_read_callback(void *, size_t, size_t, void *);
static int http_curl_progress_callback(void *, double, double, double, double);
static int http_curl_raw_callback(CURL *, curl_infotype, char *, size_t, void *);
static int http_curl_dummy_callback(char *data, size_t n, size_t l, void *s) { return n*l; }

/* {{{ http_request_callback_ctx http_request_callback_data(void *) */
http_request_callback_ctx *_http_request_callback_data_ex(void *data, zend_bool cpy TSRMLS_DC)
{
	http_request_callback_ctx *ctx = emalloc(sizeof(http_request_callback_ctx));
	
	TSRMLS_SET_CTX(ctx->tsrm_ctx);
	ctx->data = data;
	
	if (cpy) {
		return http_request_data_copy(COPY_CONTEXT, ctx);
	} else {
		return ctx;
	}
}
/* }}} */

/* {{{ void *http_request_data_copy(int, void *) */
void *_http_request_data_copy(int type, void *data TSRMLS_DC)
{
	switch (type)
	{
		case COPY_STRING:
		{
			char *new_str = estrdup(data);
			zend_llist_add_element(&HTTP_G(request).copies.strings, &new_str);
			return new_str;
		}

		case COPY_SLIST:
		{
			zend_llist_add_element(&HTTP_G(request).copies.slists, &data);
			return data;
		}

		case COPY_CONTEXT:
		{
			zend_llist_add_element(&HTTP_G(request).copies.contexts, &data);
			return data;
		}

		case COPY_CONV:
		{
			zend_llist_add_element(&HTTP_G(request).copies.convs, &data);
			return data;
		}

		default:
		{
			return data;
		}
	}
}
/* }}} */

/* {{{ void http_request_data_free_string(char **) */
void _http_request_data_free_string(void *string)
{
	efree(*((char **)string));
}
/* }}} */

/* {{{ void http_request_data_free_slist(struct curl_slist **) */
void _http_request_data_free_slist(void *list)
{
	curl_slist_free_all(*((struct curl_slist **) list));
}
/* }}} */

/* {{{ _http_request_data_free_context(http_request_callback_ctx **) */
void _http_request_data_free_context(void *context)
{
	efree(*((http_request_callback_ctx **) context));
}
/* }}} */

/* {{{ _http_request_data_free_conv(http_request_conv **) */
void _http_request_data_free_conv(void *conv)
{
	efree(*((http_request_conv **) conv));
}
/* }}} */

/* {{{ http_request_body *http_request_body_new() */
PHP_HTTP_API http_request_body *_http_request_body_new(TSRMLS_D)
{
	http_request_body *body = ecalloc(1, sizeof(http_request_body));
	return body;
}
/* }}} */

/* {{{ STATUS http_request_body_fill(http_request_body *body, HashTable *, HashTable *) */
PHP_HTTP_API STATUS _http_request_body_fill(http_request_body *body, HashTable *fields, HashTable *files TSRMLS_DC)
{
	if (files && (zend_hash_num_elements(files) > 0)) {
		char *key = NULL;
		ulong idx;
		zval **data;
		struct curl_httppost *http_post_data[2] = {NULL, NULL};

		/* normal data */
		FOREACH_HASH_KEYVAL(fields, key, idx, data) {
			CURLcode err;
			if (key) {
				convert_to_string_ex(data);
				err = curl_formadd(&http_post_data[0], &http_post_data[1],
					CURLFORM_COPYNAME,			key,
					CURLFORM_COPYCONTENTS,		Z_STRVAL_PP(data),
					CURLFORM_CONTENTSLENGTH,	(long) Z_STRLEN_PP(data),
					CURLFORM_END
				);
				if (CURLE_OK != err) {
					http_error_ex(HE_WARNING, HTTP_E_ENCODING, "Could not encode post fields: %s", curl_easy_strerror(err));
					curl_formfree(http_post_data[0]);
					return FAILURE;
				}

				/* reset */
				key = NULL;
			}
		}

		/* file data */
		FOREACH_HASH_VAL(files, data) {
			zval **file, **type, **name;
			
			if (	SUCCESS != zend_hash_find(Z_ARRVAL_PP(data), "name", sizeof("name"), (void **) &name) ||
					SUCCESS != zend_hash_find(Z_ARRVAL_PP(data), "type", sizeof("type"), (void **) &type) ||
					SUCCESS != zend_hash_find(Z_ARRVAL_PP(data), "file", sizeof("file"), (void **) &file)) {
				http_error(HE_NOTICE, HTTP_E_INVALID_PARAM, "Post file array entry misses either 'name', 'type' or 'file' entry");
			} else {
				CURLcode err = curl_formadd(&http_post_data[0], &http_post_data[1],
					CURLFORM_COPYNAME,		Z_STRVAL_PP(name),
					CURLFORM_FILE,			Z_STRVAL_PP(file),
					CURLFORM_CONTENTTYPE,	Z_STRVAL_PP(type),
					CURLFORM_END
				);
				if (CURLE_OK != err) {
					http_error_ex(HE_WARNING, HTTP_E_ENCODING, "Could not encode post files: %s", curl_easy_strerror(err));
					curl_formfree(http_post_data[0]);
					return FAILURE;
				}
			}
		}

		body->type = HTTP_REQUEST_BODY_CURLPOST;
		body->data = http_post_data[0];
		body->size = 0;

	} else {
		char *encoded;
		size_t encoded_len;

		if (SUCCESS != http_urlencode_hash_ex(fields, 1, NULL, 0, &encoded, &encoded_len)) {
			http_error(HE_WARNING, HTTP_E_ENCODING, "Could not encode post data");
			return FAILURE;
		}

		body->type = HTTP_REQUEST_BODY_CSTRING;
		body->data = encoded;
		body->size = encoded_len;
	}

	return SUCCESS;
}
/* }}} */

/* {{{ void http_request_body_dtor(http_request_body *) */
PHP_HTTP_API void _http_request_body_dtor(http_request_body *body TSRMLS_DC)
{
	if (body) {
		switch (body->type)
		{
			case HTTP_REQUEST_BODY_CSTRING:
				if (body->data) {
					efree(body->data);
				}
			break;

			case HTTP_REQUEST_BODY_CURLPOST:
				curl_formfree(body->data);
			break;

			case HTTP_REQUEST_BODY_UPLOADFILE:
				php_stream_close(body->data);
			break;
		}
	}
}
/* }}} */

/* {{{ void http_request_body_free(http_request_body *) */
PHP_HTTP_API void _http_request_body_free(http_request_body *body TSRMLS_DC)
{
	if (body) {
		http_request_body_dtor(body);
		efree(body);
	}
}
/* }}} */

/* {{{ STATUS http_request_init(CURL *, http_request_method, char *, http_request_body *, HashTable *) */
PHP_HTTP_API STATUS _http_request_init(CURL *ch, http_request_method meth, char *url, http_request_body *body, HashTable *options TSRMLS_DC)
{
	zval *zoption;
	zend_bool range_req = 0;

	/* reset CURL handle */
#ifdef HAVE_CURL_EASY_RESET
	curl_easy_reset(ch);
#endif
	http_curl_defaults(ch);

	/* set options */
	if (url) {
		HTTP_CURL_OPT(URL, http_request_data_copy(COPY_STRING, url));
	}

	HTTP_CURL_OPT(HEADER, 0);
	HTTP_CURL_OPT(FILETIME, 1);
	HTTP_CURL_OPT(AUTOREFERER, 1);
	HTTP_CURL_OPT(READFUNCTION, http_curl_read_callback);
	/* we'll get all data through the debug function */
	HTTP_CURL_OPT(WRITEFUNCTION, http_curl_dummy_callback);
	HTTP_CURL_OPT(HEADERFUNCTION, NULL);

	HTTP_CURL_OPT(VERBOSE, 1);
	HTTP_CURL_OPT(DEBUGFUNCTION, http_curl_raw_callback);

#if defined(ZTS) && (LIBCURL_VERSION_NUM >= 0x070a00)
	HTTP_CURL_OPT(NOSIGNAL, 1);
#endif
#if LIBCURL_VERSION_NUM < 0x070c00
	HTTP_CURL_OPT(ERRORBUFFER, HTTP_G(request).error);
#endif

	/* progress callback */
	if (zoption = http_curl_getopt(options, "onprogress", 0)) {
		HTTP_CURL_OPT(NOPROGRESS, 0);
		HTTP_CURL_OPT(PROGRESSFUNCTION, http_curl_progress_callback);
		HTTP_CURL_OPT(PROGRESSDATA,  http_request_callback_data(zoption));
	}

	/* proxy */
	if (zoption = http_curl_getopt(options, "proxyhost", IS_STRING)) {
		HTTP_CURL_OPT(PROXY, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
		/* port */
		if (zoption = http_curl_getopt(options, "proxyport", IS_LONG)) {
			HTTP_CURL_OPT(PROXYPORT, Z_LVAL_P(zoption));
		}
		/* user:pass */
		if (zoption = http_curl_getopt(options, "proxyauth", IS_STRING)) {
			HTTP_CURL_OPT(PROXYUSERPWD, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
		}
#if LIBCURL_VERSION_NUM >= 0x070a07
		/* auth method */
		if (zoption = http_curl_getopt(options, "proxyauthtype", IS_LONG)) {
			HTTP_CURL_OPT(PROXYAUTH, Z_LVAL_P(zoption));
		}
#endif
	}

	/* outgoing interface */
	if (zoption = http_curl_getopt(options, "interface", IS_STRING)) {
		HTTP_CURL_OPT(INTERFACE, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
	}

	/* another port */
	if (zoption = http_curl_getopt(options, "port", IS_LONG)) {
		HTTP_CURL_OPT(PORT, Z_LVAL_P(zoption));
	}

	/* auth */
	if (zoption = http_curl_getopt(options, "httpauth", IS_STRING)) {
		HTTP_CURL_OPT(USERPWD, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
	}
#if LIBCURL_VERSION_NUM >= 0x070a06
	if (zoption = http_curl_getopt(options, "httpauthtype", IS_LONG)) {
		HTTP_CURL_OPT(HTTPAUTH, Z_LVAL_P(zoption));
	}
#endif

	/* compress, empty string enables all supported if libcurl was build with zlib support */
	if ((zoption = http_curl_getopt(options, "compress", IS_BOOL)) && Z_LVAL_P(zoption)) {
#if defined(HTTP_HAVE_ZLIB) || defined(HAVE_ZLIB)
		HTTP_CURL_OPT(ENCODING, "gzip;q=1.0, deflate;q=0.5, *;q=0");
#else
		HTTP_CURL_OPT(ENCODING, "");
#endif
	}

	/* redirects, defaults to 0 */
	if (zoption = http_curl_getopt(options, "redirect", IS_LONG)) {
		HTTP_CURL_OPT(FOLLOWLOCATION, Z_LVAL_P(zoption) ? 1 : 0);
		HTTP_CURL_OPT(MAXREDIRS, Z_LVAL_P(zoption));
		if (zoption = http_curl_getopt(options, "unrestrictedauth", IS_BOOL)) {
			HTTP_CURL_OPT(UNRESTRICTED_AUTH, Z_LVAL_P(zoption));
		}
	}

	/* referer */
	if (zoption = http_curl_getopt(options, "referer", IS_STRING)) {
		HTTP_CURL_OPT(REFERER, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
	}

	/* useragent, default "PECL::HTTP/version (PHP/version)" */
	if (zoption = http_curl_getopt(options, "useragent", IS_STRING)) {
		HTTP_CURL_OPT(USERAGENT, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
	}

	/* additional headers, array('name' => 'value') */
	if (zoption = http_curl_getopt(options, "headers", IS_ARRAY)) {
		char *header_key;
		ulong header_idx;
		struct curl_slist *headers = NULL;

		FOREACH_KEY(zoption, header_key, header_idx) {
			if (header_key) {
				zval **header_val;
				if (SUCCESS == zend_hash_get_current_data(Z_ARRVAL_P(zoption), (void **) &header_val)) {
					char header[1024] = {0};
					snprintf(header, 1023, "%s: %s", header_key, Z_STRVAL_PP(header_val));
					headers = curl_slist_append(headers, http_request_data_copy(COPY_STRING, header));
				}

				/* reset */
				header_key = NULL;
			}
		}

		if (headers) {
			HTTP_CURL_OPT(HTTPHEADER, http_request_data_copy(COPY_SLIST, headers));
		}
	}

	/* cookies, array('name' => 'value') */
	if (zoption = http_curl_getopt(options, "cookies", IS_ARRAY)) {
		char *cookie_key = NULL;
		ulong cookie_idx = 0;
		phpstr *qstr = phpstr_new();

		FOREACH_KEY(zoption, cookie_key, cookie_idx) {
			if (cookie_key) {
				zval **cookie_val;
				if (SUCCESS == zend_hash_get_current_data(Z_ARRVAL_P(zoption), (void **) &cookie_val)) {
					phpstr_appendf(qstr, "%s=%s; ", cookie_key, Z_STRVAL_PP(cookie_val));
				}

				/* reset */
				cookie_key = NULL;
			}
		}

		if (qstr->used) {
			phpstr_fix(qstr);
			HTTP_CURL_OPT(COOKIE, http_request_data_copy(COPY_STRING, qstr->data));
		}
		phpstr_free(&qstr);
	}

	/* session cookies */
	if (zoption = http_curl_getopt(options, "cookiesession", IS_BOOL)) {
		if (Z_LVAL_P(zoption)) {
			/* accept cookies for this session */
			HTTP_CURL_OPT(COOKIEFILE, "");
		} else {
			/* reset session cookies */
			HTTP_CURL_OPT(COOKIESESSION, 1);
		}
	}

	/* cookiestore, read initial cookies from that file and store cookies back into that file */
	if ((zoption = http_curl_getopt(options, "cookiestore", IS_STRING)) && Z_STRLEN_P(zoption)) {
		HTTP_CURL_OPT(COOKIEFILE, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
		HTTP_CURL_OPT(COOKIEJAR, http_request_data_copy(COPY_STRING, Z_STRVAL_P(zoption)));
	}

	/* resume */
	if ((zoption = http_curl_getopt(options, "resume", IS_LONG)) && (Z_LVAL_P(zoption) != 0)) {
		range_req = 1;
		HTTP_CURL_OPT(RESUME_FROM, Z_LVAL_P(zoption));
	}

	/* maxfilesize */
	if (zoption = http_curl_getopt(options, "maxfilesize", IS_LONG)) {
		HTTP_CURL_OPT(MAXFILESIZE, Z_LVAL_P(zoption));
	}

	/* lastmodified */
	if (zoption = http_curl_getopt(options, "lastmodified", IS_LONG)) {
		if (Z_LVAL_P(zoption)) {
			if (Z_LVAL_P(zoption) > 0) {
				HTTP_CURL_OPT(TIMEVALUE, Z_LVAL_P(zoption));
			} else {
				HTTP_CURL_OPT(TIMEVALUE, time(NULL) + Z_LVAL_P(zoption));
			}
			HTTP_CURL_OPT(TIMECONDITION, range_req ? CURL_TIMECOND_IFUNMODSINCE : CURL_TIMECOND_IFMODSINCE);
		} else {
			HTTP_CURL_OPT(TIMECONDITION, CURL_TIMECOND_NONE);
		}
	}

	/* timeout, defaults to 0 */
	if (zoption = http_curl_getopt(options, "timeout", IS_LONG)) {
		HTTP_CURL_OPT(TIMEOUT, Z_LVAL_P(zoption));
	}

	/* connecttimeout, defaults to 3 */
	if (zoption = http_curl_getopt(options, "connecttimeout", IS_LONG)) {
		HTTP_CURL_OPT(CONNECTTIMEOUT, Z_LVAL_P(zoption));
	}

	/* ssl */
	if (zoption = http_curl_getopt(options, "ssl", IS_ARRAY)) {
		ulong idx;
		char *key = NULL;
		zval **param;

		FOREACH_KEYVAL(zoption, key, idx, param) {
			if (key) {
				HTTP_CURL_OPT_SSL_STRING(CERT);
#if LIBCURL_VERSION_NUM >= 0x070903
				HTTP_CURL_OPT_SSL_STRING(CERTTYPE);
#endif
				HTTP_CURL_OPT_SSL_STRING(CERTPASSWD);

				HTTP_CURL_OPT_SSL_STRING(KEY);
				HTTP_CURL_OPT_SSL_STRING(KEYTYPE);
				HTTP_CURL_OPT_SSL_STRING(KEYPASSWD);

				HTTP_CURL_OPT_SSL_STRING(ENGINE);
				HTTP_CURL_OPT_SSL_LONG(VERSION);

				HTTP_CURL_OPT_SSL_LONG_(VERIFYPEER);
				HTTP_CURL_OPT_SSL_LONG_(VERIFYHOST);
				HTTP_CURL_OPT_SSL_STRING_(CIPHER_LIST);


				HTTP_CURL_OPT_STRING(CAINFO);
#if LIBCURL_VERSION_NUM >= 0x070908
				HTTP_CURL_OPT_STRING(CAPATH);
#endif
				HTTP_CURL_OPT_STRING(RANDOM_FILE);
				HTTP_CURL_OPT_STRING(EGDSOCKET);

				/* reset key */
				key = NULL;
			}
		}
	}

	/* request method */
	switch (meth)
	{
		case HTTP_GET:
			curl_easy_setopt(ch, CURLOPT_HTTPGET, 1);
		break;

		case HTTP_HEAD:
			curl_easy_setopt(ch, CURLOPT_NOBODY, 1);
		break;

		case HTTP_POST:
			curl_easy_setopt(ch, CURLOPT_POST, 1);
		break;

		case HTTP_PUT:
			curl_easy_setopt(ch, CURLOPT_UPLOAD, 1);
		break;

		default:
			if (http_request_method_exists(0, meth, NULL)) {
				curl_easy_setopt(ch, CURLOPT_CUSTOMREQUEST, http_request_method_name(meth));
			} else {
				http_error_ex(HE_WARNING, HTTP_E_REQUEST_METHOD, "Unsupported request method: %d", meth);
				return FAILURE;
			}
		break;
	}

	/* attach request body */
	if (body && (meth != HTTP_GET) && (meth != HTTP_HEAD)) {
		switch (body->type)
		{
			case HTTP_REQUEST_BODY_CSTRING:
				curl_easy_setopt(ch, CURLOPT_POSTFIELDS, body->data);
				curl_easy_setopt(ch, CURLOPT_POSTFIELDSIZE, body->size);
			break;

			case HTTP_REQUEST_BODY_CURLPOST:
				curl_easy_setopt(ch, CURLOPT_HTTPPOST, (struct curl_httppost *) body->data);
			break;

			case HTTP_REQUEST_BODY_UPLOADFILE:
				curl_easy_setopt(ch, CURLOPT_READDATA, http_request_callback_data(body));
				curl_easy_setopt(ch, CURLOPT_INFILESIZE, body->size);
			break;

			default:
				/* shouldn't ever happen */
				http_error_ex(HE_ERROR, 0, "Unknown request body type: %d", body->type);
				return FAILURE;
			break;
		}
	}

	return SUCCESS;
}
/* }}} */

/* {{{ void http_request_conv(CURL *, phpstr *, phpstr *) */
void _http_request_conv(CURL *ch, phpstr* response, phpstr *request TSRMLS_DC)
{
	http_request_conv *conv = emalloc(sizeof(http_request_conv));
	conv->response = response;
	conv->request = request;
	conv->last_info = -1;
	HTTP_CURL_OPT(DEBUGDATA, http_request_callback_data(http_request_data_copy(COPY_CONV, conv)));
}
/* }}} */

/* {{{ STATUS http_request_exec(CURL *, HashTable *) */
PHP_HTTP_API STATUS _http_request_exec(CURL *ch, HashTable *info, phpstr *response, phpstr *request TSRMLS_DC)
{
	CURLcode result;

	http_request_conv(ch, response, request);

	/* perform request */
	if (CURLE_OK != (result = curl_easy_perform(ch))) {
		http_error_ex(HE_WARNING, HTTP_E_REQUEST, "Could not perform request: %s", curl_easy_strerror(result));
		return FAILURE;
	} else {
		/* get curl info */
		if (info) {
			http_request_info(ch, info);
		}
		return SUCCESS;
	}
}
/* }}} */

/* {{{ void http_request_info(CURL *, HashTable *) */
PHP_HTTP_API void _http_request_info(CURL *ch, HashTable *info TSRMLS_DC)
{
	zval array;
	INIT_ZARR(array, info);

	HTTP_CURL_INFO(EFFECTIVE_URL);
#if LIBCURL_VERSION_NUM >= 0x070a07
	HTTP_CURL_INFO(RESPONSE_CODE);
#else
	HTTP_CURL_INFO_EX(HTTP_CODE, RESPONSE_CODE);
#endif
	HTTP_CURL_INFO(HTTP_CONNECTCODE);
#if LIBCURL_VERSION_NUM >= 0x070500
	HTTP_CURL_INFO(FILETIME);
#endif
	HTTP_CURL_INFO(TOTAL_TIME);
	HTTP_CURL_INFO(NAMELOOKUP_TIME);
	HTTP_CURL_INFO(CONNECT_TIME);
	HTTP_CURL_INFO(PRETRANSFER_TIME);
	HTTP_CURL_INFO(STARTTRANSFER_TIME);
#if LIBCURL_VERSION_NUM >= 0x070907
	HTTP_CURL_INFO(REDIRECT_TIME);
	HTTP_CURL_INFO(REDIRECT_COUNT);
#endif
	HTTP_CURL_INFO(SIZE_UPLOAD);
	HTTP_CURL_INFO(SIZE_DOWNLOAD);
	HTTP_CURL_INFO(SPEED_DOWNLOAD);
	HTTP_CURL_INFO(SPEED_UPLOAD);
	HTTP_CURL_INFO(HEADER_SIZE);
	HTTP_CURL_INFO(REQUEST_SIZE);
	HTTP_CURL_INFO(SSL_VERIFYRESULT);
#if LIBCURL_VERSION_NUM >= 0x070c03
	/*HTTP_CURL_INFO(SSL_ENGINES); todo: CURLINFO_SLIST */
#endif
	HTTP_CURL_INFO(CONTENT_LENGTH_DOWNLOAD);
	HTTP_CURL_INFO(CONTENT_LENGTH_UPLOAD);
	HTTP_CURL_INFO(CONTENT_TYPE);
#if LIBCURL_VERSION_NUM >= 0x070a03
	/*HTTP_CURL_INFO(PRIVATE);*/
#endif
#if LIBCURL_VERSION_NUM >= 0x070a08
	HTTP_CURL_INFO(HTTPAUTH_AVAIL);
	HTTP_CURL_INFO(PROXYAUTH_AVAIL);
#endif
#if LIBCURL_VERSION_NUM >= 0x070c02
	/*HTTP_CURL_INFO(OS_ERRNO);*/
#endif
#if LIBCURL_VERSION_NUM >= 0x070c03
	HTTP_CURL_INFO(NUM_CONNECTS);
#endif
}
/* }}} */

/* {{{ STATUS http_request_ex(CURL *, http_request_method, char *, http_request_body, HashTable, HashTable, phpstr *) */
PHP_HTTP_API STATUS _http_request_ex(CURL *ch, http_request_method meth, char *url, http_request_body *body, HashTable *options, HashTable *info, phpstr *response TSRMLS_DC)
{
	STATUS status;
	zend_bool clean_curl = !ch;

	HTTP_CHECK_CURL_INIT(ch, curl_easy_init(), return FAILURE);

	status =	((SUCCESS == http_request_init(ch, meth, url, body, options)) &&
				(SUCCESS == http_request_exec(ch, info, response, NULL))) ? SUCCESS : FAILURE;

	if (clean_curl) {
		curl_easy_cleanup(ch);
	}
	return status;
}
/* }}} */

/* {{{ static size_t http_curl_read_callback(void *, size_t, size_t, void *) */
static size_t http_curl_read_callback(void *data, size_t len, size_t n, void *s)
{
	HTTP_REQUEST_CALLBACK_DATA(s, http_request_body *, body);

	if (body->type != HTTP_REQUEST_BODY_UPLOADFILE) {
		return 0;
	}
	return php_stream_read((php_stream *) body->data, data, len * n);
}
/* }}} */

/* {{{ static int http_curl_progress_callback(void *, double, double, double, double) */
static int http_curl_progress_callback(void *data, double dltotal, double dlnow, double ultotal, double ulnow)
{
	zval *params_pass[4], params_local[4], retval;
	HTTP_REQUEST_CALLBACK_DATA(data, zval *, func);

	params_pass[0] = &params_local[0];
	params_pass[1] = &params_local[1];
	params_pass[2] = &params_local[2];
	params_pass[3] = &params_local[3];

	INIT_PZVAL(params_pass[0]);
	INIT_PZVAL(params_pass[1]);
	INIT_PZVAL(params_pass[2]);
	INIT_PZVAL(params_pass[3]);
	ZVAL_DOUBLE(params_pass[0], dltotal);
	ZVAL_DOUBLE(params_pass[1], dlnow);
	ZVAL_DOUBLE(params_pass[2], ultotal);
	ZVAL_DOUBLE(params_pass[3], ulnow);

	return call_user_function(EG(function_table), NULL, func, &retval, 4, params_pass TSRMLS_CC);
}
/* }}} */

/* {{{ static int http_curl_raw_callback(CURL *, curl_infotype, char *, size_t, void *) */
static int http_curl_raw_callback(CURL *ch, curl_infotype type, char *data, size_t length, void *ctx)
{
	HTTP_REQUEST_CALLBACK_DATA(ctx, http_request_conv *, conv);

	switch (type)
	{
		case CURLINFO_DATA_IN:
			if (conv->response && conv->last_info == CURLINFO_HEADER_IN) {
				phpstr_appends(conv->response, HTTP_CRLF);
			}
		case CURLINFO_HEADER_IN:
			if (conv->response) {
				phpstr_append(conv->response, data, length);
			}
		break;
		case CURLINFO_DATA_OUT:
			if (conv->request && conv->last_info == CURLINFO_HEADER_OUT) {
				phpstr_appends(conv->request, HTTP_CRLF);
			}
		case CURLINFO_HEADER_OUT:
			if (conv->request) {
				phpstr_append(conv->request, data, length);
			}
		break;
#if 0
		default:
			fprintf(stderr, "## ", type);
			if (!type) {
				fprintf(stderr, "%s", data);
			} else {
				ulong i;
				for (i = 1; i <= length; ++i) {
					fprintf(stderr, "%02X ", data[i-1] & 0xFF);
					if (!(i % 20)) {
						fprintf(stderr, "\n## ");
					}
				}
				fprintf(stderr, "\n");
			}
			if (data[length-1] != 0xa) {
				fprintf(stderr, "\n");
			}
		break;
#endif
	}

	if (type) {
		conv->last_info = type;
	}
	return 0;
}
/* }}} */

/* {{{ static inline zval *http_curl_getopt(HashTable *, char *, size_t, int) */
static inline zval *_http_curl_getopt_ex(HashTable *options, char *key, size_t keylen, int type TSRMLS_DC)
{
	zval **zoption;

	if (!options || (SUCCESS != zend_hash_find(options, key, keylen, (void **) &zoption))) {
		return NULL;
	}

	if (Z_TYPE_PP(zoption) != type) {
		switch (type)
		{
			case IS_BOOL:	convert_to_boolean_ex(zoption);	break;
			case IS_LONG:	convert_to_long_ex(zoption);	break;
			case IS_DOUBLE:	convert_to_double_ex(zoption);	break;
			case IS_STRING:	convert_to_string_ex(zoption);	break;
			case IS_ARRAY:	convert_to_array_ex(zoption);	break;
			case IS_OBJECT:	convert_to_object_ex(zoption);	break;
			default:
			break;
		}
	}

	return *zoption;
}
/* }}} */

#ifdef HTTP_NEED_OPENSSL_TSL
/* {{{ */
static MUTEX_T *http_openssl_tsl = NULL;

static void http_ssl_lock(int mode, int n, const char * file, int line)
{
	if (mode & CRYPTO_LOCK) {
		tsrm_mutex_lock(http_openssl_tsl[n]);
	} else {
		tsrm_mutex_unlock(http_openssl_tsl[n]);
	}
}

static ulong http_ssl_id(void)
{
	return (ulong) tsrm_thread_id();
}

static inline void http_ssl_init(void)
{
	int i, c = CRYPTO_num_locks();
	
	http_openssl_tsl = malloc(c * sizeof(MUTEX_T));
	
	for (i = 0; i < c; ++i) {
		http_openssl_tsl[i] = tsrm_mutex_alloc();
	}
	
	CRYPTO_set_id_callback(http_ssl_id);
	CRYPTO_set_locking_callback(http_ssl_lock);
}

static inline void http_ssl_cleanup(void)
{
	if (http_openssl_tsl) {
		int i, c = CRYPTO_num_locks();
		
		CRYPTO_set_id_callback(NULL);
		CRYPTO_set_locking_callback(NULL);
		
		for (i = 0; i < c; ++i) {
			tsrm_mutex_free(http_openssl_tsl[i]);
		}
		
		free(http_openssl_tsl);
		http_openssl_tsl = NULL;
	}
}
#endif /* HTTP_NEED_OPENSSL_TSL */
/* }}} */

#ifdef HTTP_NEED_GNUTLS_TSL
/* {{{ */
static int http_ssl_mutex_create(void **m)
{
	if (*((MUTEX_T *) m) = tsrm_mutex_alloc()) {
		return SUCCESS;
	} else {
		return FAILURE;
	}
}

static int http_ssl_mutex_destroy(void **m)
{
	tsrm_mutex_free(*((MUTEX_T *) m));
	return SUCCESS;
}

static int http_ssl_mutex_lock(void **m)
{
	return tsrm_mutex_lock(*((MUTEX_T *) m));
}

static int http_ssl_mutex_unlock(void **m)
{
	return tsrm_mutex_unlock(*((MUTEX_T *) m));
}

static struct gcry_thread_cbs http_gnutls_tsl = {
	GCRY_THREAD_OPTIONS_USER,
	NULL,
	http_ssl_mutex_create,
	http_ssl_mutex_destroy,
	http_ssl_mutex_lock,
	http_ssl_mutex_unlock
};

static inline void http_ssl_init(void)
{
	gcry_control(GCRYCTL_SET_THREAD_CBS, &http_gnutls_tsl);
}

static inline void http_ssl_cleanup(void)
{
	return;
}
#endif /* HTTP_NEED_GNUTLS_TSL */
/* }}} */

/* {{{ http_curl_defaults(CURL *) */
static inline void _http_curl_defaults(CURL *ch)
{
	HTTP_CURL_OPT(URL, NULL);
	HTTP_CURL_OPT(NOPROGRESS, 1);
	HTTP_CURL_OPT(PROXY, NULL);
	HTTP_CURL_OPT(PROXYPORT, 0);
	HTTP_CURL_OPT(PROXYUSERPWD, NULL);
#if LIBCURL_VERSION_NUM >= 0x070a07
	HTTP_CURL_OPT(PROXYAUTH, 0);
#endif
	HTTP_CURL_OPT(INTERFACE, NULL);
	HTTP_CURL_OPT(PORT, 0);
	HTTP_CURL_OPT(USERPWD, NULL);
#if LIBCURL_VERSION_NUM >= 0x070a06
	HTTP_CURL_OPT(HTTPAUTH, 0);
#endif
	HTTP_CURL_OPT(ENCODING, 0);
	HTTP_CURL_OPT(FOLLOWLOCATION, 0);
	HTTP_CURL_OPT(UNRESTRICTED_AUTH, 0);
	HTTP_CURL_OPT(REFERER, NULL);
	HTTP_CURL_OPT(USERAGENT, "PECL::HTTP/" HTTP_PEXT_VERSION " (PHP/" PHP_VERSION ")");
	HTTP_CURL_OPT(HTTPHEADER, NULL);
	HTTP_CURL_OPT(COOKIE, NULL);
	HTTP_CURL_OPT(COOKIEFILE, NULL);
	HTTP_CURL_OPT(COOKIEJAR, NULL);
	HTTP_CURL_OPT(RESUME_FROM, 0);
	HTTP_CURL_OPT(MAXFILESIZE, 0);
	HTTP_CURL_OPT(TIMECONDITION, 0);
	HTTP_CURL_OPT(TIMEVALUE, 0);
	HTTP_CURL_OPT(TIMEOUT, 0);
	HTTP_CURL_OPT(CONNECTTIMEOUT, 3);
	HTTP_CURL_OPT(SSLCERT, NULL);
#if LIBCURL_VERSION_NUM >= 0x070903
	HTTP_CURL_OPT(SSLCERTTYPE, NULL);
#endif
	HTTP_CURL_OPT(SSLCERTPASSWD, NULL);
	HTTP_CURL_OPT(SSLKEY, NULL);
	HTTP_CURL_OPT(SSLKEYTYPE, NULL);
	HTTP_CURL_OPT(SSLKEYPASSWD, NULL);
	HTTP_CURL_OPT(SSLENGINE, NULL);
	HTTP_CURL_OPT(SSLVERSION, 0);
	HTTP_CURL_OPT(SSL_VERIFYPEER, 0);
	HTTP_CURL_OPT(SSL_VERIFYHOST, 0);
	HTTP_CURL_OPT(SSL_CIPHER_LIST, NULL);
	HTTP_CURL_OPT(CAINFO, NULL);
#if LIBCURL_VERSION_NUM >= 0x070908
	HTTP_CURL_OPT(CAPATH, NULL);
#endif
	HTTP_CURL_OPT(RANDOM_FILE, NULL);
	HTTP_CURL_OPT(EGDSOCKET, NULL);
	HTTP_CURL_OPT(POSTFIELDS, NULL);
	HTTP_CURL_OPT(POSTFIELDSIZE, 0);
	HTTP_CURL_OPT(HTTPPOST, NULL);
	HTTP_CURL_OPT(READDATA, NULL);
	HTTP_CURL_OPT(INFILESIZE, 0);
}
/* }}} */

#endif /* HTTP_HAVE_CURL */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

