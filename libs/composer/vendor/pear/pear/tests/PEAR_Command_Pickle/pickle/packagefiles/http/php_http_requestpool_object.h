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

#ifndef PHP_HTTP_REQUESTPOOL_OBJECT_H
#define PHP_HTTP_REQUESTPOOL_OBJECT_H
#ifdef HTTP_HAVE_CURL
#ifdef ZEND_ENGINE_2

#ifdef	PHP_WIN32
#	include <winsock2.h>
#endif

#include <curl/curl.h>

#include "php_http_request_pool_api.h"

typedef struct {
	zend_object zo;
	http_request_pool pool;
	struct {
		long pos;
	} iterator;
} http_requestpool_object;

extern zend_class_entry *http_requestpool_object_ce;
extern zend_function_entry http_requestpool_object_fe[];

extern PHP_MINIT_FUNCTION(http_requestpool_object);

#define http_requestpool_object_new(ce) _http_requestpool_object_new(ce TSRMLS_CC)
extern zend_object_value _http_requestpool_object_new(zend_class_entry *ce TSRMLS_DC);
#define http_requestpool_object_free(o) _http_requestpool_object_free(o TSRMLS_CC)
extern void _http_requestpool_object_free(zend_object *object TSRMLS_DC);

PHP_METHOD(HttpRequestPool, __construct);
PHP_METHOD(HttpRequestPool, __destruct);
PHP_METHOD(HttpRequestPool, attach);
PHP_METHOD(HttpRequestPool, detach);
PHP_METHOD(HttpRequestPool, send);
PHP_METHOD(HttpRequestPool, reset);
PHP_METHOD(HttpRequestPool, socketPerform);
PHP_METHOD(HttpRequestPool, socketSelect);
PHP_METHOD(HttpRequestPool, valid);
PHP_METHOD(HttpRequestPool, current);
PHP_METHOD(HttpRequestPool, key);
PHP_METHOD(HttpRequestPool, next);
PHP_METHOD(HttpRequestPool, rewind);
PHP_METHOD(HttpRequestPool, getAttachedRequests);
PHP_METHOD(HttpRequestPool, getFinishedRequests);

#endif
#endif
#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

