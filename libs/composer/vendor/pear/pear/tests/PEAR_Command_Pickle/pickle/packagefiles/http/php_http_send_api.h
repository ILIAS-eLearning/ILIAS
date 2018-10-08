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

#ifndef PHP_HTTP_SEND_API_H
#define PHP_HTTP_SEND_API_H

#include "SAPI.h"
#include "php_streams.h"
#include "php_http_std_defs.h"

typedef enum {
	SEND_DATA,
	SEND_RSRC
} http_send_mode;

#define http_send_status(s) sapi_header_op(SAPI_HEADER_SET_STATUS, (void *) (s) TSRMLS_CC)
#define http_send_header(n, v, r) _http_send_header_ex((n), strlen(n), (v), strlen(v), (r) TSRMLS_CC)
#define http_send_header_ex(n, nl, v, vl, r, s) _http_send_header_ex((n), (nl), (v), (vl), (r), (s) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_header_ex(const char *name, size_t name_len, const char *value, size_t value_len, zend_bool replace, char **sent_header TSRMLS_DC);
#define http_send_header_string(h) _http_send_status_header_ex(0, (h), strlen(h), 1 TSRMLS_CC)
#define http_send_header_string_ex(h, l, r) _http_send_status_header_ex(0, (h), (l), (r) TSRMLS_CC)
#define http_send_status_header(s, h) _http_send_status_header_ex((s), (h), (h)?strlen(h):0, 1 TSRMLS_CC)
#define http_send_status_header_ex(s, h, l, r) _http_send_status_header_ex((s), (h), (l), (r) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_status_header_ex(int status, const char *header, size_t header_len, zend_bool replace TSRMLS_DC);

#define http_send_last_modified(t) _http_send_last_modified_ex((t), NULL TSRMLS_CC)
#define http_send_last_modified_ex(t, s) _http_send_last_modified_ex((t), (s) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_last_modified_ex(time_t t, char **sent_header TSRMLS_DC);

#define http_send_etag(e, l) _http_send_etag_ex((e), (l), NULL TSRMLS_CC)
#define http_send_etag_ex(e, l, s) _http_send_etag_ex((e), (l), (s) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_etag_ex(const char *etag, size_t etag_len, char **sent_header TSRMLS_DC);

#define http_send_cache_control(cc, cl) http_send_header_ex("Cache-Control", lenof("Cache-Control"), (cc), (cl), 1, NULL)

#define http_send_content_type(c, l) _http_send_content_type((c), (l) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_content_type(const char *content_type, size_t ct_len TSRMLS_DC);

#define http_send_content_disposition(f, l, i) _http_send_content_disposition((f), (l), (i) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_content_disposition(const char *filename, size_t f_len, zend_bool send_inline TSRMLS_DC);

#define http_send_data(d, l) http_send((d), (l), SEND_DATA)
#define http_send_data_ex(d, l, nc) http_send_ex((d), (l), SEND_DATA, (nc))
#define http_send(d, s, m) _http_send_ex((d), (s), (m), 0 TSRMLS_CC)
#define http_send_ex(d, s, m, nc) _http_send_ex((d), (s), (m), (nc) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_ex(const void *data, size_t data_size, http_send_mode mode, zend_bool no_cache TSRMLS_DC);

#define http_send_file(f) http_send_stream_ex(php_stream_open_wrapper(f, "rb", REPORT_ERRORS|ENFORCE_SAFE_MODE, NULL), 1, 0)
#define http_send_file_ex(f, nc) http_send_stream_ex(php_stream_open_wrapper(f, "rb", REPORT_ERRORS|ENFORCE_SAFE_MODE, NULL), 1, (nc))
#define http_send_stream(s) http_send_stream_ex((s), 0, 0)
#define http_send_stream_ex(s, c, nc) _http_send_stream_ex((s), (c), (nc) TSRMLS_CC)
PHP_HTTP_API STATUS _http_send_stream_ex(php_stream *s, zend_bool close_stream, zend_bool no_cache TSRMLS_DC);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

