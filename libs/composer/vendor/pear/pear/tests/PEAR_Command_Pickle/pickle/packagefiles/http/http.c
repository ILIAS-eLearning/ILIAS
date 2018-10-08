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
#include "php_ini.h"
#include "ext/standard/info.h"

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_api.h"
#include "php_http_send_api.h"
#include "php_http_cache_api.h"
#include "php_http_headers_api.h"
#include "php_http_request_method_api.h"
#ifdef HTTP_HAVE_CURL
#	include "php_http_request_api.h"
#endif

#ifdef ZEND_ENGINE_2
#	include "php_http_util_object.h"
#	include "php_http_message_object.h"
#	ifndef WONKY
#		include "php_http_response_object.h"
#	endif
#	ifdef HTTP_HAVE_CURL
#		include "php_http_request_object.h"
#		include "php_http_requestpool_object.h"
#	endif
#	include "php_http_exception_object.h"
#endif

#include "missing.h"
#include "phpstr/phpstr.h"

#ifdef HTTP_HAVE_CURL
#	ifdef PHP_WIN32
#		include <winsock2.h>
#	endif
#	include <curl/curl.h>
#endif
#ifdef HTTP_HAVE_MHASH
#	include <mhash.h>
#endif
#ifdef HTTP_HAVE_ZLIB
#	include <zlib.h>
#endif

#include <ctype.h>

ZEND_DECLARE_MODULE_GLOBALS(http);
HTTP_DECLARE_ARG_PASS_INFO();

#ifdef COMPILE_DL_HTTP
ZEND_GET_MODULE(http)
#endif

/* {{{ http_functions[] */
zend_function_entry http_functions[] = {
	PHP_FE(http_test, NULL)
	PHP_FE(http_date, NULL)
	PHP_FE(http_build_uri, NULL)
	PHP_FALIAS(http_absolute_uri, http_build_uri, NULL)
	PHP_FE(http_negotiate_language, http_arg_pass_ref_2)
	PHP_FE(http_negotiate_charset, http_arg_pass_ref_2)
	PHP_FE(http_redirect, NULL)
	PHP_FE(http_throttle, NULL)
	PHP_FE(http_send_status, NULL)
	PHP_FE(http_send_last_modified, NULL)
	PHP_FE(http_send_content_type, NULL)
	PHP_FE(http_send_content_disposition, NULL)
	PHP_FE(http_match_modified, NULL)
	PHP_FE(http_match_etag, NULL)
	PHP_FE(http_cache_last_modified, NULL)
	PHP_FE(http_cache_etag, NULL)
	PHP_FE(http_send_data, NULL)
	PHP_FE(http_send_file, NULL)
	PHP_FE(http_send_stream, NULL)
	PHP_FE(http_chunked_decode, NULL)
	PHP_FE(http_parse_message, NULL)
	PHP_FE(http_parse_headers, NULL)
	PHP_FE(http_get_request_headers, NULL)
	PHP_FE(http_get_request_body, NULL)
	PHP_FE(http_match_request_header, NULL)
#ifdef HTTP_HAVE_CURL
	PHP_FE(http_get, http_arg_pass_ref_3)
	PHP_FE(http_head, http_arg_pass_ref_3)
	PHP_FE(http_post_data, http_arg_pass_ref_4)
	PHP_FE(http_post_fields, http_arg_pass_ref_5)
	PHP_FE(http_put_file, http_arg_pass_ref_4)
	PHP_FE(http_put_stream, http_arg_pass_ref_4)
#endif
	PHP_FE(http_request_method_register, NULL)
	PHP_FE(http_request_method_unregister, NULL)
	PHP_FE(http_request_method_exists, NULL)
	PHP_FE(http_request_method_name, NULL)
#ifndef ZEND_ENGINE_2
	PHP_FE(http_build_query, NULL)
#endif
	PHP_FE(ob_etaghandler, NULL)
#ifdef HTTP_HAVE_ZLIB
	PHP_FE(http_gzencode, NULL)
	PHP_FE(http_gzdecode, NULL)
	PHP_FE(http_deflate, NULL)
	PHP_FE(http_inflate, NULL)
	PHP_FE(http_compress, NULL)
	PHP_FE(http_uncompress, NULL)
#endif
	PHP_FE(http_support, NULL)
	
	EMPTY_FUNCTION_ENTRY
};
/* }}} */

/* {{{ http_module_entry */
zend_module_entry http_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"http",
	http_functions,
	PHP_MINIT(http),
	PHP_MSHUTDOWN(http),
	PHP_RINIT(http),
	PHP_RSHUTDOWN(http),
	PHP_MINFO(http),
#if ZEND_MODULE_API_NO >= 20010901
	HTTP_PEXT_VERSION,
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

int http_module_number;

/* {{{ http_globals */
static void http_globals_init_once(zend_http_globals *G)
{
	memset(G, 0, sizeof(zend_http_globals));
}

static inline void http_globals_init(zend_http_globals *G)
{
	G->send.buffer_size = HTTP_SENDBUF_SIZE;
	zend_hash_init(&G->request.methods.custom, 0, NULL, ZVAL_PTR_DTOR, 0);
#ifdef HTTP_HAVE_CURL
	zend_llist_init(&G->request.copies.strings, sizeof(char *), http_request_data_free_string, 0);
	zend_llist_init(&G->request.copies.slists, sizeof(struct curl_slist *), http_request_data_free_slist, 0);
	zend_llist_init(&G->request.copies.contexts, sizeof(http_request_callback_ctx *), http_request_data_free_context, 0);
	zend_llist_init(&G->request.copies.convs, sizeof(http_request_conv *), http_request_data_free_conv, 0);
#endif
}

static inline void http_globals_free(zend_http_globals *G)
{
	STR_SET(G->send.content_type, NULL);
	STR_SET(G->send.unquoted_etag, NULL);
	zend_hash_destroy(&G->request.methods.custom);
#ifdef HTTP_HAVE_CURL
	zend_llist_clean(&G->request.copies.strings);
	zend_llist_clean(&G->request.copies.slists);
	zend_llist_clean(&G->request.copies.contexts);
	zend_llist_clean(&G->request.copies.convs);
#endif
}
/* }}} */

/* {{{ static inline void http_check_allowed_methods(char *, int) */
#define http_check_allowed_methods(m, l) _http_check_allowed_methods((m), (l) TSRMLS_CC)
static inline void _http_check_allowed_methods(char *methods, int length TSRMLS_DC)
{
	if (length && SG(request_info).request_method) {
		if (SUCCESS != http_check_method_ex(SG(request_info).request_method, methods)) {
			char *header = emalloc(length + sizeof("Allow: "));
			sprintf(header, "Allow: %s", methods);
			http_exit(405, header);
		}
	}
}
/* }}} */

/* {{{ PHP_INI */
PHP_INI_MH(http_update_allowed_methods)
{
	http_check_allowed_methods(new_value, new_value_length);
	return OnUpdateString(entry, new_value, new_value_length, mh_arg1, mh_arg2, mh_arg3, stage TSRMLS_CC);
}

PHP_INI_DISP(http_etag_mode_displayer)
{
	long value;
	
	if (type == ZEND_INI_DISPLAY_ORIG && ini_entry->modified) {
		value = (ini_entry->orig_value) ? atoi(ini_entry->orig_value) : HTTP_ETAG_MD5;
	} else if (ini_entry->value) {
		value = (ini_entry->value[0]) ? atoi(ini_entry->value) : HTTP_ETAG_MD5;
	} else {
		value = HTTP_ETAG_MD5;
	}
	
	switch (value)
	{
		case HTTP_ETAG_CRC32:
			ZEND_WRITE("HTTP_ETAG_CRC32", lenof("HTTP_ETAG_CRC32"));
		break;
		
		case HTTP_ETAG_SHA1:
			ZEND_WRITE("HTTP_ETAG_SHA1", lenof("HTTP_ETAG_SHA1"));
		break;
		
		case HTTP_ETAG_MD5:
#ifndef HTTP_HAVE_MHASH
		default:
#endif
			ZEND_WRITE("HTTP_ETAG_MD5", lenof("HTTP_ETAG_MD5"));
		break;
		
#ifdef HTTP_HAVE_MHASH
		default:
		{
			const char *hash_name = mhash_get_hash_name_static(value);
			
			if (!hash_name) {
				ZEND_WRITE("HTTP_ETAG_MD5", lenof("HTTP_ETAG_MD5"));
			} else {
				ZEND_WRITE("HTTP_ETAG_MHASH_", lenof("HTTP_ETAG_MHASH_"));
				ZEND_WRITE(hash_name, strlen(hash_name));
			}
		}
		break;
#endif
	}
}

#ifndef ZEND_ENGINE_2
#	define OnUpdateLong OnUpdateInt
#endif

PHP_INI_BEGIN()
	HTTP_PHP_INI_ENTRY("http.allowed_methods", "", PHP_INI_ALL, http_update_allowed_methods, request.methods.allowed)
	HTTP_PHP_INI_ENTRY("http.cache_log", "", PHP_INI_ALL, OnUpdateString, log.cache)
	HTTP_PHP_INI_ENTRY("http.redirect_log", "", PHP_INI_ALL, OnUpdateString, log.redirect)
	HTTP_PHP_INI_ENTRY("http.allowed_methods_log", "", PHP_INI_ALL, OnUpdateString, log.allowed_methods)
	HTTP_PHP_INI_ENTRY("http.composite_log", "", PHP_INI_ALL, OnUpdateString, log.composite)
#ifdef ZEND_ENGINE_2
	HTTP_PHP_INI_ENTRY("http.only_exceptions", "0", PHP_INI_ALL, OnUpdateBool, only_exceptions)
#endif
	HTTP_PHP_INI_ENTRY_EX("http.etag_mode", "-2", PHP_INI_ALL, OnUpdateLong, http_etag_mode_displayer, etag.mode)
PHP_INI_END()
/* }}} */

/* {{{ PHP_MINIT_FUNCTION */
PHP_MINIT_FUNCTION(http)
{
	http_module_number = module_number;

	ZEND_INIT_MODULE_GLOBALS(http, http_globals_init_once, NULL)

	REGISTER_INI_ENTRIES();
	
	if (	(SUCCESS != PHP_MINIT_CALL(http_support))	||
			(SUCCESS != PHP_MINIT_CALL(http_headers))	||
			(SUCCESS != PHP_MINIT_CALL(http_cache))		||
#ifdef HTTP_HAVE_CURL
			(SUCCESS != PHP_MINIT_CALL(http_request))	||
#endif /* HTTP_HAVE_CURL */
			(SUCCESS != PHP_MINIT_CALL(http_request_method))) {
		return FAILURE;
	}

#ifdef ZEND_ENGINE_2
	if (	(SUCCESS != PHP_MINIT_CALL(http_util_object))		||
			(SUCCESS != PHP_MINIT_CALL(http_message_object))	||
#	ifndef WONKY
			(SUCCESS != PHP_MINIT_CALL(http_response_object))	||
#	endif /* WONKY */
#	ifdef HTTP_HAVE_CURL
			(SUCCESS != PHP_MINIT_CALL(http_request_object))	||
			(SUCCESS != PHP_MINIT_CALL(http_requestpool_object))	||
#	endif /* HTTP_HAVE_CURL */
			(SUCCESS != PHP_MINIT_CALL(http_exception_object))) {
		return FAILURE;
	}
#endif /* ZEND_ENGINE_2 */

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION */
PHP_MSHUTDOWN_FUNCTION(http)
{
	UNREGISTER_INI_ENTRIES();
#ifdef HTTP_HAVE_CURL
	return PHP_MSHUTDOWN_CALL(http_request);
#endif
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RINIT_FUNCTION */
PHP_RINIT_FUNCTION(http)
{
	char *m;

	if (m = INI_STR("http.allowed_methods")) {
		http_check_allowed_methods(m, strlen(m));
	}

	http_globals_init(HTTP_GLOBALS);
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RSHUTDOWN_FUNCTION */
PHP_RSHUTDOWN_FUNCTION(http)
{
	STATUS status = SUCCESS;
	
#if defined(ZEND_ENGINE_2) && defined(HTTP_HAVE_CURL)
	status = PHP_RSHUTDOWN_CALL(http_request_method);
#endif
	
	http_globals_free(HTTP_GLOBALS);
	return status;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION */
PHP_MINFO_FUNCTION(http)
{
	php_info_print_table_start();
	{
		php_info_print_table_row(2, "Extended HTTP support", "enabled");
		php_info_print_table_row(2, "Extension Version", HTTP_PEXT_VERSION);
#ifdef HTTP_HAVE_CURL
		php_info_print_table_row(2, "cURL HTTP Requests", curl_version());
#else
		php_info_print_table_row(2, "cURL HTTP Requests", "disabled");
#endif
#ifdef HTTP_HAVE_ZLIB
		{
			char my_zlib_version[64] = {0};
			
			strlcat(my_zlib_version, "zlib/", 63);
			strlcat(my_zlib_version, zlibVersion(), 63);
			php_info_print_table_row(2, "zlib GZIP Encodings", my_zlib_version);
		}
#else
		php_info_print_table_row(2, "zlib GZIP Encodings", "disabled");
#endif
#ifdef HTTP_HAVE_MHASH
		{
			char mhash_info[32];
			
			snprintf(mhash_info, 32, "libmhash/%d", MHASH_API_VERSION);
			php_info_print_table_row(2, "mhash ETag Generator", mhash_info);
		}
#else
		php_info_print_table_row(2, "mhash ETag Generator", "disabled");
#endif
#if defined(HTTP_HAVE_MAGIC) && !defined(WONKY)
		php_info_print_table_row(2, "magic MIME Guessing", "libmagic/unknown");
#else
		php_info_print_table_row(2, "magic MIME Guessing", "disabled");
#endif
		php_info_print_table_row(2, "Registered Classes",
#ifndef ZEND_ENGINE_2
			"none"
#else
			"HttpUtil, "
			"HttpMessage, "
#	ifdef HTTP_HAVE_CURL
			"HttpRequest, "
			"HttpRequestPool, "
#	endif
#	ifndef WONKY
			"HttpResponse"
#	endif
#endif
		);
	}
	php_info_print_table_end();
	
	php_info_print_table_start();
	php_info_print_table_colspan_header(2, "Supported ETag Hash Algorithms");
	{
			
		php_info_print_table_row(2, "PHP", "CRC32, MD5, SHA1");
#ifdef HTTP_HAVE_MHASH
		{
			phpstr *algos = phpstr_new();
			int i, c = mhash_count();
			
			for (i = 0; i <= c; ++i) {
				const char *hash = mhash_get_hash_name_static(i);
				
				if (hash) {
					phpstr_appendf(algos, "%s, ", hash);
				}
			}
			phpstr_fix(algos);
			php_info_print_table_row(2, "MHASH", PHPSTR_VAL(algos));
			phpstr_free(&algos);
		}
#else
		php_info_print_table_row(2, "MHASH", "not available");
#endif
	}
	php_info_print_table_end();

	php_info_print_table_start();
	php_info_print_table_colspan_header(2, "Request Methods");
	{
		unsigned i;
		zval **custom_method;
		phpstr *known_request_methods = phpstr_new();
		phpstr *custom_request_methods = phpstr_new();

		for (i = HTTP_NO_REQUEST_METHOD+1; i < HTTP_MAX_REQUEST_METHOD; ++i) {
			phpstr_appendl(known_request_methods, http_request_method_name(i));
			phpstr_appends(known_request_methods, ", ");
		}
		FOREACH_HASH_VAL(&HTTP_G(request).methods.custom, custom_method) {
			phpstr_append(custom_request_methods, Z_STRVAL_PP(custom_method), Z_STRLEN_PP(custom_method));
			phpstr_appends(custom_request_methods, ", ");
		}

		phpstr_append(known_request_methods, PHPSTR_VAL(custom_request_methods), PHPSTR_LEN(custom_request_methods));
		phpstr_fix(known_request_methods);
		phpstr_fix(custom_request_methods);

		php_info_print_table_row(2, "Known", PHPSTR_VAL(known_request_methods));
		php_info_print_table_row(2, "Custom",
			PHPSTR_LEN(custom_request_methods) ? PHPSTR_VAL(custom_request_methods) : "none registered");
		php_info_print_table_row(2, "Allowed", strlen(HTTP_G(request).methods.allowed) ? HTTP_G(request).methods.allowed : "(ANY)");
		
		phpstr_free(&known_request_methods);
		phpstr_free(&custom_request_methods);
	}
	php_info_print_table_end();

	DISPLAY_INI_ENTRIES();
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

