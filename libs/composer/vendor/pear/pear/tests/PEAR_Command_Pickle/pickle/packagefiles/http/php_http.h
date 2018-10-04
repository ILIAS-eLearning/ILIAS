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

#ifndef PHP_EXT_HTTP_H
#define PHP_EXT_HTTP_H

#define HTTP_PEXT_VERSION "0.16.1dev"

/* make compile on Win32 */
#ifdef HTTP_HAVE_CURL
#	ifdef PHP_WIN32
#		include <winsock2.h>
#	endif
#	include <curl/curl.h>
#endif

#include "phpstr/phpstr.h"

extern zend_module_entry http_module_entry;
#define phpext_http_ptr &http_module_entry

extern int http_module_number;

ZEND_BEGIN_MODULE_GLOBALS(http)

#ifdef ZEND_ENGINE_2
	zend_bool only_exceptions;
#endif
	struct _http_globals_etag {
		long mode;
		void *ctx;
		zend_bool started;
	} etag;

	struct _http_globals_log {
		char *cache;
		char *redirect;
		char *allowed_methods;
		char *composite;
	} log;

	struct _http_globals_send {
		double throttle_delay;
		size_t buffer_size;
		char *content_type;
		char *unquoted_etag;
		time_t last_modified;
		int gzip_encoding;
	} send;

	struct _http_globals_request {
		struct _http_globals_request_methods {
			char *allowed;
			HashTable custom;
		} methods;

#ifdef HTTP_HAVE_CURL
		struct _http_globals_request_copies {
			zend_llist strings;
			zend_llist slists;
			zend_llist contexts;
			zend_llist convs;
		} copies;
#	ifndef HAVE_CURL_EASY_STRERROR
		char error[CURL_ERROR_SIZE + 1];
#	endif
#endif /* HTTP_HAVE_CURL */
	} request;

ZEND_END_MODULE_GLOBALS(http)

#ifdef ZTS
#	include "TSRM.h"
#	define HTTP_G(v) TSRMG(http_globals_id, zend_http_globals *, v)
#	define HTTP_GLOBALS ((zend_http_globals *) (*((void ***) tsrm_ls))[TSRM_UNSHUFFLE_RSRC_ID(http_globals_id)])
#else
#	define HTTP_G(v) (http_globals.v)
#	define HTTP_GLOBALS (&http_globals)
#endif
#define getGlobals(G) zend_http_globals *G = HTTP_GLOBALS;

PHP_FUNCTION(http_test);
PHP_FUNCTION(http_date);
PHP_FUNCTION(http_build_uri);
PHP_FUNCTION(http_negotiate_language);
PHP_FUNCTION(http_negotiate_charset);
PHP_FUNCTION(http_redirect);
PHP_FUNCTION(http_throttle);
PHP_FUNCTION(http_send_status);
PHP_FUNCTION(http_send_last_modified);
PHP_FUNCTION(http_send_content_type);
PHP_FUNCTION(http_send_content_disposition);
PHP_FUNCTION(http_match_modified);
PHP_FUNCTION(http_match_etag);
PHP_FUNCTION(http_cache_last_modified);
PHP_FUNCTION(http_cache_etag);
PHP_FUNCTION(http_send_data);
PHP_FUNCTION(http_send_file);
PHP_FUNCTION(http_send_stream);
PHP_FUNCTION(http_chunked_decode);
PHP_FUNCTION(http_parse_message);
PHP_FUNCTION(http_parse_headers);
PHP_FUNCTION(http_get_request_headers);
PHP_FUNCTION(http_get_request_body);
PHP_FUNCTION(http_match_request_header);
#ifdef HTTP_HAVE_CURL
PHP_FUNCTION(http_get);
PHP_FUNCTION(http_head);
PHP_FUNCTION(http_post_data);
PHP_FUNCTION(http_post_fields);
PHP_FUNCTION(http_put_file);
PHP_FUNCTION(http_put_stream);
#endif /* HTTP_HAVE_CURL */
PHP_FUNCTION(http_request_method_register);
PHP_FUNCTION(http_request_method_unregister);
PHP_FUNCTION(http_request_method_exists);
PHP_FUNCTION(http_request_method_name);
#ifndef ZEND_ENGINE_2
PHP_FUNCTION(http_build_query);
#endif /* ZEND_ENGINE_2 */
PHP_FUNCTION(ob_etaghandler);
#ifdef HTTP_HAVE_ZLIB
PHP_FUNCTION(http_gzencode);
PHP_FUNCTION(http_gzdecode);
PHP_FUNCTION(http_deflate);
PHP_FUNCTION(http_inflate);
PHP_FUNCTION(http_compress);
PHP_FUNCTION(http_uncompress);
#endif
PHP_FUNCTION(http_support);

PHP_MINIT_FUNCTION(http);
PHP_MSHUTDOWN_FUNCTION(http);
PHP_RINIT_FUNCTION(http);
PHP_RSHUTDOWN_FUNCTION(http);
PHP_MINFO_FUNCTION(http);

#endif	/* PHP_HTTP_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

