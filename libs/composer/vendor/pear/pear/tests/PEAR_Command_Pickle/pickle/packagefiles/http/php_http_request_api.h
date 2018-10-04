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

#ifndef PHP_HTTP_REQUEST_API_H
#define PHP_HTTP_REQUEST_API_H

#ifdef HTTP_HAVE_CURL

#include "php_http_std_defs.h"
#include "php_http_request_method_api.h"

#include "phpstr/phpstr.h"

#ifdef PHP_WIN32
#	include <winsock2.h>
#endif
#include <curl/curl.h>

extern PHP_MINIT_FUNCTION(http_request);
extern PHP_MSHUTDOWN_FUNCTION(http_request);

#define HTTP_REQUEST_BODY_CSTRING		1
#define HTTP_REQUEST_BODY_CURLPOST		2
#define HTTP_REQUEST_BODY_UPLOADFILE	3
typedef struct {
	int type;
	void *data;
	size_t size;
} http_request_body;

typedef struct {
	void ***tsrm_ctx;
	void *data;
} http_request_callback_ctx;

typedef struct {
	phpstr *response;
	phpstr *request;
	curl_infotype last_info;
} http_request_conv;

#define HTTP_REQUEST_CALLBACK_DATA(from, type, var) \
	http_request_callback_ctx *__CTX = (http_request_callback_ctx *) (from); \
	TSRMLS_FETCH_FROM_CTX(__CTX->tsrm_ctx); \
	type (var) = (type) (__CTX->data)

#define http_request_callback_data(data) _http_request_callback_data_ex((data), 1 TSRMLS_CC)
#define http_request_callback_data_ex(data, copy) _http_request_callback_data_ex((data), (copy) TSRMLS_CC)
extern http_request_callback_ctx *_http_request_callback_data_ex(void *data, zend_bool cpy TSRMLS_DC);


#define COPY_STRING		1
#define	COPY_SLIST		2
#define COPY_CONTEXT	3
#define COPY_CONV		4
#define http_request_data_copy(type, data) _http_request_data_copy((type), (data) TSRMLS_CC)
extern void *_http_request_data_copy(int type, void *data TSRMLS_DC);
#define http_request_data_free_string _http_request_data_free_string
extern void _http_request_data_free_string(void *string);
#define http_request_data_free_slist _http_request_data_free_slist
extern void _http_request_data_free_slist(void *list);
#define http_request_data_free_context _http_request_data_free_context
extern void _http_request_data_free_context(void *context);
#define http_request_data_free_conv _http_request_data_free_conv
extern void _http_request_data_free_conv(void *conv);

#define http_request_conv(ch, rs, rq) _http_request_conv((ch), (rs), (rq) TSRMLS_CC)
extern void _http_request_conv(CURL *ch, phpstr* response, phpstr *request TSRMLS_DC);

#define http_request_body_new() _http_request_body_new(TSRMLS_C)
PHP_HTTP_API http_request_body *_http_request_body_new(TSRMLS_D);

#define http_request_body_fill(b, fields, files) _http_request_body_fill((b), (fields), (files) TSRMLS_CC)
PHP_HTTP_API STATUS _http_request_body_fill(http_request_body *body, HashTable *fields, HashTable *files TSRMLS_DC);

#define http_request_body_dtor(b) _http_request_body_dtor((b) TSRMLS_CC)
PHP_HTTP_API void _http_request_body_dtor(http_request_body *body TSRMLS_DC);

#define http_request_body_free(b) _http_request_body_free((b) TSRMLS_CC)
PHP_HTTP_API void _http_request_body_free(http_request_body *body TSRMLS_DC);

#define http_request_init(ch, meth, url, body, options) _http_request_init((ch), (meth), (url), (body), (options) TSRMLS_CC)
PHP_HTTP_API STATUS _http_request_init(CURL *ch, http_request_method meth, char *url, http_request_body *body, HashTable *options TSRMLS_DC);

#define http_request_exec(ch, i, response, request) _http_request_exec((ch), (i), (response), (request) TSRMLS_CC)
PHP_HTTP_API STATUS _http_request_exec(CURL *ch, HashTable *info, phpstr *response, phpstr *request TSRMLS_DC);

#define http_request_info(ch, i) _http_request_info((ch), (i) TSRMLS_CC)
PHP_HTTP_API void _http_request_info(CURL *ch, HashTable *info TSRMLS_DC);

#define http_request(meth, url, body, opt, info, resp) _http_request_ex(NULL, (meth), (url), (body), (opt), (info), (resp) TSRMLS_CC)
#define http_request_ex(ch, meth, url, body, opt, info, resp) _http_request_ex((ch), (meth), (url), (body), (opt), (info), (resp) TSRMLS_CC)
PHP_HTTP_API STATUS _http_request_ex(CURL *ch, http_request_method meth, char *URL, http_request_body *body, HashTable *options, HashTable *info, phpstr *response TSRMLS_DC);

#define http_get(u, o, i, r) _http_request_ex(NULL, HTTP_GET, (u), NULL, (o), (i), (r) TSRMLS_CC)
#define http_get_ex(c, u, o, i, r) _http_request_ex((c), HTTP_GET, (u), NULL, (o), (i), (r) TSRMLS_CC)

#define http_head(u, o, i, r) _http_request_ex(NULL, HTTP_HEAD, (u), NULL, (o), (i), (r) TSRMLS_CC)
#define http_head_ex(c, u, o, i, r) _http_request_ex((c), HTTP_HEAD, (u), NULL, (o), (i), (r) TSRMLS_CC)

#define http_post(u, b, o, i, r) _http_request_ex(NULL, HTTP_POST, (u), (b), (o), (i), (r) TSRMLS_CC)
#define http_post_ex(c, u, b, o, i, r) _http_request_ex((c), HTTP_POST, (u), (b), (o), (i), (r) TSRMLS_CC)

#define http_put(u, b, o, i, r) _http_request_ex(NULL, HTTP_PUT, (u), (b), (o), (i), (r) TSRMLS_CC)
#define http_put_ex(c, u, b, o, i, r) _http_request_ex((c), HTTP_PUT, (u), (b), (o), (i), (r) TSRMLS_CC)

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

