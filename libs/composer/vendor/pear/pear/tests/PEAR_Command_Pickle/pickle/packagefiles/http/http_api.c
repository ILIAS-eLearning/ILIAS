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

#include "SAPI.h"
#include "ext/standard/url.h"
#include "ext/standard/head.h"

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_api.h"
#include "php_http_headers_api.h"
#include "php_http_request_api.h"
#include "php_http_send_api.h"

#ifdef ZEND_ENGINE_2
#	include "zend_exceptions.h"
#	include "php_http_exception_object.h"
#endif

#include <ctype.h>

#ifdef HTTP_HAVE_MAGIC
#	if defined(PHP_WIN32) && !defined(USE_MAGIC_DLL) && !defined(USE_MAGIC_STATIC)
#		define USE_MAGIC_STATIC
#	endif
#	include <magic.h>
#endif

ZEND_EXTERN_MODULE_GLOBALS(http);

PHP_MINIT_FUNCTION(http_support)
{
	HTTP_LONG_CONSTANT("HTTP_SUPPORT", HTTP_SUPPORT);
	HTTP_LONG_CONSTANT("HTTP_SUPPORT_REQUESTS", HTTP_SUPPORT_REQUESTS);
	HTTP_LONG_CONSTANT("HTTP_SUPPORT_MAGICMIME", HTTP_SUPPORT_MAGICMIME);
	HTTP_LONG_CONSTANT("HTTP_SUPPORT_ENCODINGS", HTTP_SUPPORT_ENCODINGS);
	HTTP_LONG_CONSTANT("HTTP_SUPPORT_MHASHETAGS", HTTP_SUPPORT_MHASHETAGS);
	HTTP_LONG_CONSTANT("HTTP_SUPPORT_SSLREQUESTS", HTTP_SUPPORT_SSLREQUESTS);
	
	return SUCCESS;
}

PHP_HTTP_API long _http_support(long feature)
{
	long support = HTTP_SUPPORT;
	
#ifdef HTTP_HAVE_CURL
	support |= HTTP_SUPPORT_REQUESTS;
#	ifdef HTTP_HAVE_SSL
	support |= HTTP_SUPPORT_SSLREQUESTS;
#	endif
#endif
#ifdef HTTP_HAVE_MHASH
	support |= HTTP_SUPPORT_MHASHETAGS;
#endif
#ifdef HTTP_HAVE_MAGIC
	support |= HTTP_SUPPORT_MAGICMIME;
#endif
#if defined(HTTP_HAVE_ZLIB) || defined(HAVE_ZLIB)
	support |= HTTP_SUPPORT_ENCODINGS;
#endif

	if (feature) {
		return (feature == (support & feature));
	}
	return support;
}

/* char *pretty_key(char *, size_t, zend_bool, zend_bool) */
char *_http_pretty_key(char *key, size_t key_len, zend_bool uctitle, zend_bool xhyphen)
{
	if (key && key_len) {
		size_t i;
		int wasalpha;
		if (wasalpha = isalpha((int) key[0])) {
			key[0] = (char) (uctitle ? toupper((int) key[0]) : tolower((int) key[0]));
		}
		for (i = 1; i < key_len; i++) {
			if (isalpha((int) key[i])) {
				key[i] = (char) (((!wasalpha) && uctitle) ? toupper((int) key[i]) : tolower((int) key[i]));
				wasalpha = 1;
			} else {
				if (xhyphen && (key[i] == '_')) {
					key[i] = '-';
				}
				wasalpha = 0;
			}
		}
	}
	return key;
}
/* }}} */

/* {{{ */
void _http_key_list_default_decoder(const char *encoded, size_t encoded_len, char **decoded, size_t *decoded_len TSRMLS_DC)
{
	*decoded = estrndup(encoded, encoded_len);
	*decoded_len = (size_t) php_url_decode(*decoded, encoded_len);
}
/* }}} */

/* {{{ */
STATUS _http_parse_key_list(const char *list, HashTable *items, char separator, http_key_list_decode_t decode, zend_bool first_entry_is_name_value_pair TSRMLS_DC)
{
	const char *key = list, *val = NULL;
	int vallen = 0, keylen = 0, done = 0;
	zval array;

	INIT_ZARR(array, items);

	if (!(val = strchr(list, '='))) {
		return FAILURE;
	}

#define HTTP_KEYLIST_VAL(array, k, str, len) \
	{ \
		char *decoded; \
		size_t decoded_len; \
		if (decode) { \
			decode(str, len, &decoded, &decoded_len TSRMLS_CC); \
		} else { \
			decoded_len = len; \
			decoded = estrndup(str, decoded_len); \
		} \
		add_assoc_stringl(array, k, decoded, decoded_len, 0); \
	}
#define HTTP_KEYLIST_FIXKEY() \
	{ \
			while (isspace(*key)) ++key; \
			keylen = val - key; \
			while (isspace(key[keylen - 1])) --keylen; \
	}
#define HTTP_KEYLIST_FIXVAL() \
	{ \
			++val; \
			while (isspace(*val)) ++val; \
			vallen = key - val; \
			while (isspace(val[vallen - 1])) --vallen; \
	}

	HTTP_KEYLIST_FIXKEY();

	if (first_entry_is_name_value_pair) {
		HTTP_KEYLIST_VAL(&array, "name", key, keylen);

		/* just one name=value */
		if (!(key = strchr(val, separator))) {
			key = val + strlen(val);
			HTTP_KEYLIST_FIXVAL();
			HTTP_KEYLIST_VAL(&array, "value", val, vallen);
			return SUCCESS;
		}
		/* additional info appended */
		else {
			HTTP_KEYLIST_FIXVAL();
			HTTP_KEYLIST_VAL(&array, "value", val, vallen);
		}
	}

	do {
		char *keydup = NULL;

		if (!(val = strchr(key, '='))) {
			break;
		}

		/* start at 0 if first_entry_is_name_value_pair==0 */
		if (zend_hash_num_elements(items)) {
			++key;
		}

		HTTP_KEYLIST_FIXKEY();
		keydup = estrndup(key, keylen);
		if (!(key = strchr(val, separator))) {
			done = 1;
			key = val + strlen(val);
		}
		HTTP_KEYLIST_FIXVAL();
		HTTP_KEYLIST_VAL(&array, keydup, val, vallen);
		efree(keydup);
	} while (!done);

	return SUCCESS;
}
/* }}} */

/* {{{ void http_error(long, long, char*) */
void _http_error_ex(long type TSRMLS_DC, long code, const char *format, ...)
{
	va_list args;
	
	va_start(args, format);
#ifdef ZEND_ENGINE_2
	if ((type == E_THROW) || (PG(error_handling) == EH_THROW)) {
		char *message;
		
		vspprintf(&message, 0, format, args);
		zend_throw_exception(http_exception_get_for_code(code), message, code TSRMLS_CC);
		efree(message);
	} else
#endif
	php_verror(NULL, "", type, format, args TSRMLS_CC);
	va_end(args);
}
/* }}} */

/* {{{ void http_log(char *, char *, char *) */
void _http_log_ex(char *file, const char *ident, const char *message TSRMLS_DC)
{
	time_t now;
	struct tm nowtm;
	char datetime[128];
	
	time(&now);
	strftime(datetime, sizeof(datetime), "%Y-%m-%d %H:%M:%S", php_localtime_r(&now, &nowtm));

#define HTTP_LOG_WRITE(file, type, msg) \
	if (file && *file) { \
	 	php_stream *log = php_stream_open_wrapper(file, "ab", REPORT_ERRORS|ENFORCE_SAFE_MODE, NULL); \
		 \
		if (log) { \
			php_stream_printf(log TSRMLS_CC, "%s\t[%s]\t%s\t<%s>%s", datetime, type, msg, SG(request_info).request_uri, PHP_EOL); \
			php_stream_close(log); \
		} \
	 \
	}
	
	HTTP_LOG_WRITE(file, ident, message);
	HTTP_LOG_WRITE(HTTP_G(log).composite, ident, message);
}
/* }}} */

/* {{{ STATUS http_exit(int, char*, char*) */
STATUS _http_exit_ex(int status, char *header, char *body, zend_bool send_header TSRMLS_DC)
{
	if (status || send_header) {
		if (SUCCESS != http_send_status_header(status, send_header ? header : NULL)) {
			http_error_ex(HE_WARNING, HTTP_E_HEADER, "Failed to exit with status/header: %d - %s", status, header ? header : "");
			STR_FREE(header);
			STR_FREE(body);
			return FAILURE;
		}
	}
	
	if (php_header(TSRMLS_C) && body) {
		PHPWRITE(body, strlen(body));
	}
	
	switch (status)
	{
		case 301:	http_log(HTTP_G(log).redirect, "301-REDIRECT", header);			break;
		case 302:	http_log(HTTP_G(log).redirect, "302-REDIRECT", header);			break;
		case 303:	http_log(HTTP_G(log).redirect, "303-REDIRECT", header);			break;
		case 307:	http_log(HTTP_G(log).redirect, "307-REDIRECT", header);			break;
		case 304:	http_log(HTTP_G(log).cache, "304-CACHE", header);				break;
		case 405:	http_log(HTTP_G(log).allowed_methods, "405-ALLOWED", header);	break;
		default:	http_log(NULL, header, body);									break;
	}
	
	STR_FREE(header);
	STR_FREE(body);
	
	zend_bailout();
	/* fake */
	return SUCCESS;
}
/* }}} */

/* {{{ STATUS http_check_method(char *) */
STATUS _http_check_method_ex(const char *method, const char *methods)
{
	const char *found;

	if (	(found = strstr(methods, method)) &&
			(found == method || !isalpha(found[-1])) &&
			(!isalpha(found[strlen(method) + 1]))) {
		return SUCCESS;
	}
	return FAILURE;
}
/* }}} */

/* {{{ zval *http_get_server_var_ex(char *, size_t) */
PHP_HTTP_API zval *_http_get_server_var_ex(const char *key, size_t key_size, zend_bool check TSRMLS_DC)
{
	zval **hsv;
	zval **var;
	
	if (SUCCESS != zend_hash_find(&EG(symbol_table), "_SERVER", sizeof("_SERVER"), (void **) &hsv)) {
		return NULL;
	}
	if (SUCCESS != zend_hash_find(Z_ARRVAL_PP(hsv), (char *) key, key_size, (void **) &var)) {
		return NULL;
	}
	if (check && !(Z_STRVAL_PP(var) && Z_STRLEN_PP(var))) {
		return NULL;
	}
	return *var;
}
/* }}} */

/* {{{ STATUS http_get_request_body(char **, size_t *) */
PHP_HTTP_API STATUS _http_get_request_body_ex(char **body, size_t *length, zend_bool dup TSRMLS_DC)
{
	*length = 0;
	*body = NULL;

	if (SG(request_info).raw_post_data) {
		*length = SG(request_info).raw_post_data_length;
		*body = (char *) (dup ? estrndup(SG(request_info).raw_post_data, *length) : SG(request_info).raw_post_data);
		return SUCCESS;
	}
	return FAILURE;
}
/* }}} */


/* {{{ char *http_guess_content_type(char *magic_file, long magic_mode, void *data, size_t size, http_send_mode mode) */
PHP_HTTP_API char *_http_guess_content_type(const char *magicfile, long magicmode, void *data_ptr, size_t data_len, http_send_mode data_mode TSRMLS_DC)
{
	char *ct = NULL;

#ifdef HTTP_HAVE_MAGIC
	/*	magic_load() fails if MAGIC_MIME is set because it 
		cowardly adds .mime to the file name */
	struct magic_set *magic = magic_open(magicmode &~ MAGIC_MIME);
	
	if (!magic) {
		http_error_ex(HE_WARNING, HTTP_E_INVALID_PARAM, "Invalid magic mode: %ld", magicmode);
	} else if (-1 == magic_load(magic, magicfile)) {
		http_error_ex(HE_WARNING, HTTP_E_RUNTIME, "Failed to load magic database '%s' (%s)", magicfile, magic_error(magic));
	} else {
		const char *ctype = NULL;
		
		magic_setflags(magic, magicmode);
		
		switch (data_mode)
		{
			case SEND_RSRC:
			{
				char *buffer;
				size_t b_len;
				
				b_len = php_stream_copy_to_mem(data_ptr, &buffer, 65536, 0);
				ctype = magic_buffer(magic, buffer, b_len);
				efree(buffer);
			}
			break;
			
			case SEND_DATA:
				ctype = magic_buffer(magic, data_ptr, data_len);
			break;
			
			default:
				ctype = magic_file(magic, data_ptr);
			break;
		}
		
		if (ctype) {
			ct = estrdup(ctype);
		} else {
			http_error_ex(HE_WARNING, HTTP_E_RUNTIME, "Failed to guess Content-Type: %s", magic_error(magic));
		}
	}
	if (magic) {
		magic_close(magic);
	}
#else
	http_error(HE_WARNING, HTTP_E_RUNTIME, "Cannot guess Content-Type; libmagic not available");
#endif
	
	return ct;
}
/* }}} */
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

