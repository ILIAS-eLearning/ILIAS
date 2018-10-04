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

#ifndef PHP_HTTP_API_H
#define PHP_HTTP_API_H

#include "php_http_std_defs.h"
#include "php_http_send_api.h"

#define HTTP_SUPPORT				0x01L
#define HTTP_SUPPORT_REQUESTS		0x02L
#define HTTP_SUPPORT_MAGICMIME		0x04L
#define HTTP_SUPPORT_ENCODINGS		0x08L
#define HTTP_SUPPORT_MHASHETAGS		0x10L
#define HTTP_SUPPORT_SSLREQUESTS	0x20L

extern PHP_MINIT_FUNCTION(http_support);

#define http_support(f) _http_support(f)
PHP_HTTP_API long _http_support(long feature);

#define pretty_key(key, key_len, uctitle, xhyphen) _http_pretty_key(key, key_len, uctitle, xhyphen)
extern char *_http_pretty_key(char *key, size_t key_len, zend_bool uctitle, zend_bool xhyphen);

typedef void (*http_key_list_decode_t)(const char *encoded, size_t encoded_len, char **decoded, size_t *decoded_len TSRMLS_DC);
#define http_key_list_default_decoder _http_key_list_default_decoder
extern void _http_key_list_default_decoder(const char *encoded, size_t encoded_len, char **decoded, size_t *decoded_len TSRMLS_DC);

#define http_parse_cookie(l, i) _http_parse_key_list((l), (i), ';', http_key_list_default_decoder, 1 TSRMLS_CC)
#define http_parse_key_list(l, i, s, d, f) _http_parse_key_list((l), (i), (s), (d), (f) TSRMLS_CC)
extern STATUS _http_parse_key_list(const char *list, HashTable *items, char separator, http_key_list_decode_t decode, zend_bool first_entry_is_name_value_pair TSRMLS_DC);

#define http_error(type, code, string) _http_error_ex(type, code, "%s", string)
#define http_error_ex _http_error_ex
extern void _http_error_ex(long type TSRMLS_DC, long code, const char *format, ...);

#define HTTP_CHECK_CURL_INIT(ch, init, action) \
	if ((!(ch)) && (!((ch) = init))) { \
		http_error(HE_WARNING, HTTP_E_REQUEST, "Could not initialize curl"); \
		action; \
	}
#define HTTP_CHECK_CONTENT_TYPE(ct, action) \
	if (!strchr((ct), '/')) { \
		http_error_ex(HE_WARNING, HTTP_E_INVALID_PARAM, \
			"Content type \"%s\" does not seem to contain a primary and a secondary part", (ct)); \
		action; \
	}
#define HTTP_CHECK_MESSAGE_TYPE_RESPONSE(msg, action) \
		if (!HTTP_MSG_TYPE(RESPONSE, (msg))) { \
			http_error(HE_NOTICE, HTTP_E_MESSAGE_TYPE, "HttpMessage is not of type HTTP_MSG_RESPONSE"); \
			action; \
		}
#define HTTP_CHECK_MESSAGE_TYPE_REQUEST(msg, action) \
		if (!HTTP_MSG_TYPE(REQUEST, (msg))) { \
			http_error(HE_NOTICE, HTTP_E_MESSAGE_TYPE, "HttpMessage is not of type HTTP_MSG_REQUEST"); \
			action; \
		}
#define HTTP_CHECK_GZIP_LEVEL(level, action) \
	if (level < -1 || level > 9) { \
		http_error_ex(HE_WARNING, HTTP_E_INVALID_PARAM, "Invalid compression level (-1 to 9): %d", level); \
		action; \
	}

#define http_log(f, i, m) _http_log_ex((f), (i), (m) TSRMLS_CC)
extern void http_log_ex(char *file, const char *ident, const char *message TSRMLS_DC);

#define http_exit(s, h) http_exit_ex((s), (h), NULL, 1)
#define http_exit_ex(s, h, b, e) _http_exit_ex((s), (h), (b), (e) TSRMLS_CC)
extern STATUS _http_exit_ex(int status, char *header, char *body, zend_bool send_header TSRMLS_DC);

#define http_check_method(m) http_check_method_ex((m), HTTP_KNOWN_METHODS)
#define http_check_method_ex(m, a) _http_check_method_ex((m), (a))
extern STATUS _http_check_method_ex(const char *method, const char *methods);

#define HTTP_GSC(var, name, ret)  HTTP_GSP(var, name, return ret)
#define HTTP_GSP(var, name, ret) \
		if (!(var = _http_get_server_var_ex(name, strlen(name)+1, 1 TSRMLS_CC))) { \
			ret; \
		}
#define http_got_server_var(v) (NULL != _http_get_server_var_ex((v), sizeof(v), 1 TSRMLS_CC))
#define http_get_server_var(v) http_get_server_var_ex((v), sizeof(v))
#define http_get_server_var_ex(v, s) _http_get_server_var_ex((v), (s), 0 TSRMLS_CC)
PHP_HTTP_API zval *_http_get_server_var_ex(const char *key, size_t key_size, zend_bool check TSRMLS_DC);

#define http_get_request_body(b, l) _http_get_request_body_ex((b), (l), 1 TSRMLS_CC)
#define http_get_Request_body_ex(b, l, d) _http_get_request_body_ex((b), (l), (d) TSRMLS_CC)
PHP_HTTP_API STATUS _http_get_request_body_ex(char **body, size_t *length, zend_bool dup TSRMLS_DC);

#define http_guess_content_type(mf, mm, d, l, m) _http_guess_content_type((mf), (mm), (d), (l), (m) TSRMLS_CC)
PHP_HTTP_API char *_http_guess_content_type(const char *magic_file, long magic_mode, void *data_ptr, size_t data_len, http_send_mode mode TSRMLS_DC);


#define http_locate_body _http_locate_body
static inline const char *_http_locate_body(const char *message)
{
	const char *cr = strstr(message, "\r\n\r\n");
	const char *lf = strstr(message, "\n\n");

	if (lf && cr) {
		return MIN(lf + 2, cr + 4);
	} else if (lf || cr) {
		return MAX(lf + 2, cr + 4);
	} else {
		return NULL;
	}
}

#define http_locate_eol _http_locate_eol
static inline const char *_http_locate_eol(const char *line, int *eol_len)
{
	const char *eol = strpbrk(line, "\r\n");
	
	if (eol_len) {
		*eol_len = eol ? ((eol[0] == '\r' && eol[1] == '\n') ? 2 : 1) : 0;
	}
	return eol;
}

#define convert_to_type(t, z) _convert_to_type((t), (z))
static inline zval *_convert_to_type(int type, zval *z)
{
	if (Z_TYPE_P(z) != type) {
		switch (type)
		{
			case IS_NULL:	convert_to_null(z);		break;
			case IS_BOOL:	convert_to_boolean(z);	break;
			case IS_LONG:	convert_to_long(z);		break;
			case IS_DOUBLE:	convert_to_array(z);	break;
			case IS_STRING:	convert_to_string(z);	break;
			case IS_ARRAY:	convert_to_array(z);	break;
			case IS_OBJECT:	convert_to_object(z);	break;
		}
	}
	return z;
}
#define convert_to_type_ex(t, z, p) _convert_to_type_ex((t), (z), (p))
static inline zval *_convert_to_type_ex(int type, zval *z, zval **p)
{
	*p = z;
	if (Z_TYPE_P(z) != type) {
		switch (type)
		{
			case IS_NULL:	convert_to_null_ex(&z);		break;
			case IS_BOOL:	convert_to_boolean_ex(&z);	break;
			case IS_LONG:	convert_to_long_ex(&z);		break;
			case IS_DOUBLE:	convert_to_array_ex(&z);	break;
			case IS_STRING:	convert_to_string_ex(&z);	break;
			case IS_ARRAY:	convert_to_array_ex(&z);	break;
			case IS_OBJECT:	convert_to_object_ex(&z);	break;
		}
	}
	if (*p == z) {
		*p = NULL;
	} else {
		*p = z;
	}
	return z;
}


#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

