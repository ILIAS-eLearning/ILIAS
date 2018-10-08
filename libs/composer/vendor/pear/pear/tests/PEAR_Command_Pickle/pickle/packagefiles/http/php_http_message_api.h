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

#ifndef PHP_HTTP_MESSAGE_API_H
#define PHP_HTTP_MESSAGE_API_H

#include "php_http_info_api.h"
#include "phpstr/phpstr.h"

typedef enum {
	HTTP_MSG_NONE		= 0,
	HTTP_MSG_REQUEST	= IS_HTTP_REQUEST,
	HTTP_MSG_RESPONSE	= IS_HTTP_RESPONSE,
} http_message_type;

typedef struct _http_message http_message;

struct _http_message {
	phpstr body;
	HashTable hdrs;
	http_message_type type;
	struct http_info http;
	http_message *parent;
};

/* required minimum length of an HTTP message "HTTP/1.1" */
#define HTTP_MSG_MIN_SIZE 8

/* shorthand for type checks */
#define HTTP_MSG_TYPE(TYPE, msg) ((msg) && ((msg)->type == HTTP_MSG_ ##TYPE))

#define http_message_new() _http_message_init_ex(NULL, 0)
#define http_message_init(m) _http_message_init_ex((m), 0)
#define http_message_init_ex(m, t) _http_message_init_ex((m), (t))
PHP_HTTP_API http_message *_http_message_init_ex(http_message *m, http_message_type t);

#define http_message_set_type(m, t) _http_message_set_type((m), (t))
PHP_HTTP_API void _http_message_set_type(http_message *m, http_message_type t);

#define http_message_header(m, h) _http_message_header_ex((m), (h), sizeof(h))
#define http_message_header_ex _http_message_header_ex
static inline zval *_http_message_header_ex(http_message *msg, char *key_str, size_t key_len)
{
	zval **header;
	if (SUCCESS == zend_hash_find(&msg->hdrs, key_str, key_len, (void **) &header)) {
		return *header;
	}
	return NULL;
}

#define http_message_parse(m, l) http_message_parse_ex(NULL, (m), (l))
#define http_message_parse_ex(h, m, l) _http_message_parse_ex((h), (m), (l) TSRMLS_CC)
PHP_HTTP_API http_message *_http_message_parse_ex(http_message *msg, const char *message, size_t length TSRMLS_DC);

#define http_message_tostring(m, s, l) _http_message_tostring((m), (s), (l))
PHP_HTTP_API void _http_message_tostring(http_message *msg, char **string, size_t *length);

#define http_message_serialize(m, s, l) _http_message_serialize((m), (s), (l))
PHP_HTTP_API void _http_message_serialize(http_message *message, char **string, size_t *length);

#define http_message_tostruct_recursive(m, s) _http_message_tostruct_recursive((m), (s) TSRMLS_CC)
PHP_HTTP_API void _http_message_tostruct_recursive(http_message *msg, zval *strct TSRMLS_DC);

#define http_message_send(m) _http_message_send((m) TSRMLS_CC)
PHP_HTTP_API STATUS _http_message_send(http_message *message TSRMLS_DC);

#define http_message_dup(m) _http_message_dup((m) TSRMLS_CC)
PHP_HTTP_API http_message *_http_message_dup(http_message *msg TSRMLS_DC);

#define http_message_dtor(m) _http_message_dtor((m))
PHP_HTTP_API void _http_message_dtor(http_message *message);

#define http_message_free(m) _http_message_free((m))
PHP_HTTP_API void _http_message_free(http_message **message);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

