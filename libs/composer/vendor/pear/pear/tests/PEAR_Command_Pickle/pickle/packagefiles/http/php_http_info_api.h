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

#ifndef PHP_HTTP_INFO_API_H
#define PHP_HTTP_INFO_API_H

#define IS_HTTP_REQUEST		1
#define IS_HTTP_RESPONSE	2

#define HTTP_INFO(ptr) (ptr)->http.info

typedef struct {
	char *method;
	char *URI;
} http_request_info;

typedef struct {
	int code;
	char *status;
} http_response_info;

typedef union {
	http_request_info request;
	http_response_info response;
} http_info_t;

struct http_info {
	http_info_t info;
	double version;
};

typedef struct {
	struct http_info http;
	int type;
} http_info;

typedef void (*http_info_callback)(void **callback_data, HashTable **headers, http_info *info TSRMLS_DC);

#define http_info_default_callback _http_info_default_callback
PHP_HTTP_API void _http_info_default_callback(void **nothing, HashTable **headers, http_info *info TSRMLS_DC);
#define http_info_dtor _http_info_dtor
PHP_HTTP_API void _http_info_dtor(http_info *info);
#define http_info_parse(p, i) _http_info_parse_ex((p), (i), 1 TSRMLS_CC)
#define http_info_parse_ex(p, i, s) _http_info_parse_ex((p), (i), (s) TSRMLS_CC)
PHP_HTTP_API STATUS _http_info_parse_ex(const char *pre_header, http_info *info , zend_bool silent TSRMLS_DC);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

