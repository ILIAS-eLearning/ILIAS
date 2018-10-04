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

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_api.h"
#include "php_http_request_method_api.h"
#if defined(ZEND_ENGINE_2) && defined(HTTP_HAVE_CURL)
#	include "php_http_request_object.h"
#endif

#include "missing.h"
#include "phpstr/phpstr.h"

ZEND_EXTERN_MODULE_GLOBALS(http);

/* {{{ char *http_request_methods[] */
static const char *const http_request_methods[] = {
	"UNKNOWN",
	/* HTTP/1.1 */
	"GET",
	"HEAD",
	"POST",
	"PUT",
	"DELETE",
	"OPTIONS",
	"TRACE",
	"CONNECT",
	/* WebDAV - RFC 2518 */
	"PROPFIND",
	"PROPPATCH",
	"MKCOL",
	"COPY",
	"MOVE",
	"LOCK",
	"UNLOCK",
	/* WebDAV Versioning - RFC 3253 */
	"VERSION-CONTROL",
	"REPORT",
	"CHECKOUT",
	"CHECKIN",
	"UNCHECKOUT",
	"MKWORKSPACE",
	"UPDATE",
	"LABEL",
	"MERGE",
	"BASELINE-CONTROL",
	"MKACTIVITY",
	/* WebDAV Access Control - RFC 3744 */
	"ACL",
	NULL
};
/* }}} */

PHP_MINIT_FUNCTION(http_request_method)
{
	/* HTTP/1.1 */
	HTTP_LONG_CONSTANT("HTTP_METH_GET", HTTP_GET);
	HTTP_LONG_CONSTANT("HTTP_METH_HEAD", HTTP_HEAD);
	HTTP_LONG_CONSTANT("HTTP_METH_POST", HTTP_POST);
	HTTP_LONG_CONSTANT("HTTP_METH_PUT", HTTP_PUT);
	HTTP_LONG_CONSTANT("HTTP_METH_DELETE", HTTP_DELETE);
	HTTP_LONG_CONSTANT("HTTP_METH_OPTIONS", HTTP_OPTIONS);
	HTTP_LONG_CONSTANT("HTTP_METH_TRACE", HTTP_TRACE);
	HTTP_LONG_CONSTANT("HTTP_METH_CONNECT", HTTP_CONNECT);
	/* WebDAV - RFC 2518 */
	HTTP_LONG_CONSTANT("HTTP_METH_PROPFIND", HTTP_PROPFIND);
	HTTP_LONG_CONSTANT("HTTP_METH_PROPPATCH", HTTP_PROPPATCH);
	HTTP_LONG_CONSTANT("HTTP_METH_MKCOL", HTTP_MKCOL);
	HTTP_LONG_CONSTANT("HTTP_METH_COPY", HTTP_COPY);
	HTTP_LONG_CONSTANT("HTTP_METH_MOVE", HTTP_MOVE);
	HTTP_LONG_CONSTANT("HTTP_METH_LOCK", HTTP_LOCK);
	HTTP_LONG_CONSTANT("HTTP_METH_UNLOCK", HTTP_UNLOCK);
	/* WebDAV Versioning - RFC 3253 */
	HTTP_LONG_CONSTANT("HTTP_METH_VERSION_CONTROL", HTTP_VERSION_CONTROL);
	HTTP_LONG_CONSTANT("HTTP_METH_REPORT", HTTP_REPORT);
	HTTP_LONG_CONSTANT("HTTP_METH_CHECKOUT", HTTP_CHECKOUT);
	HTTP_LONG_CONSTANT("HTTP_METH_CHECKIN", HTTP_CHECKIN);
	HTTP_LONG_CONSTANT("HTTP_METH_UNCHECKOUT", HTTP_UNCHECKOUT);
	HTTP_LONG_CONSTANT("HTTP_METH_MKWORKSPACE", HTTP_MKWORKSPACE);
	HTTP_LONG_CONSTANT("HTTP_METH_UPDATE", HTTP_UPDATE);
	HTTP_LONG_CONSTANT("HTTP_METH_LABEL", HTTP_LABEL);
	HTTP_LONG_CONSTANT("HTTP_METH_MERGE", HTTP_MERGE);
	HTTP_LONG_CONSTANT("HTTP_METH_BASELINE_CONTROL", HTTP_BASELINE_CONTROL);
	HTTP_LONG_CONSTANT("HTTP_METH_MKACTIVITY", HTTP_MKACTIVITY);
	/* WebDAV Access Control - RFC 3744 */
	HTTP_LONG_CONSTANT("HTTP_METH_ACL", HTTP_ACL);
	
	return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(http_request_method)
{
	int i, c = zend_hash_num_elements(&HTTP_G(request).methods.custom);
	
	for (i = 0; i < c; ++i) {
		http_request_method_unregister(HTTP_MAX_REQUEST_METHOD + i);
	}
	
	return SUCCESS;
}

/* {{{ char *http_request_method_name(http_request_method) */
PHP_HTTP_API const char *_http_request_method_name(http_request_method m TSRMLS_DC)
{
	zval **meth;

	if (HTTP_STD_REQUEST_METHOD(m)) {
		return http_request_methods[m];
	}

	if (SUCCESS == zend_hash_index_find(&HTTP_G(request).methods.custom, HTTP_CUSTOM_REQUEST_METHOD(m), (void **) &meth)) {
		return Z_STRVAL_PP(meth);
	}

	return http_request_methods[0];
}
/* }}} */

/* {{{ ulong http_request_method_exists(zend_bool, ulong, char *) */
PHP_HTTP_API ulong _http_request_method_exists(zend_bool by_name, ulong id, const char *name TSRMLS_DC)
{
	if (by_name) {
		unsigned i;

		for (i = HTTP_NO_REQUEST_METHOD + 1; i < HTTP_MAX_REQUEST_METHOD; ++i) {
			if (!strcmp(name, http_request_methods[i])) {
				return i;
			}
		}
		{
			zval **data;
			char *key;
			ulong idx;

			FOREACH_HASH_KEYVAL(&HTTP_G(request).methods.custom, key, idx, data) {
				if (!strcmp(name, Z_STRVAL_PP(data))) {
					return idx + HTTP_MAX_REQUEST_METHOD;
				}
			}
		}
		return 0;
	} else {
		return HTTP_STD_REQUEST_METHOD(id) || zend_hash_index_exists(&HTTP_G(request).methods.custom, HTTP_CUSTOM_REQUEST_METHOD(id)) ? id : 0;
	}
}
/* }}} */

/* {{{ ulong http_request_method_register(char *) */
PHP_HTTP_API ulong _http_request_method_register(const char *method_name, size_t method_name_len TSRMLS_DC)
{
	zval array;
	char *http_method, *method;
	ulong i, meth_num = HTTP_G(request).methods.custom.nNextFreeElement + HTTP_MAX_REQUEST_METHOD;

	method = emalloc(method_name_len + 1);
	for (i = 0; i < method_name_len; ++i) {
		method[i] = toupper(method_name[i]);
	}
	method[method_name_len] = '\0';
	
	INIT_ZARR(array, &HTTP_G(request).methods.custom);
	add_next_index_stringl(&array, method, method_name_len, 0);

	method_name_len = spprintf(&http_method, 0, "HTTP_METH_%s", method);
	zend_register_long_constant(http_method, method_name_len + 1, meth_num, CONST_CS, http_module_number TSRMLS_CC);
	efree(http_method);
	
#if defined(ZEND_ENGINE_2) && defined(HTTP_HAVE_CURL) && !defined(WONKY)
	method_name_len = spprintf(&http_method, 0, "METH_%s", method);
	zend_declare_class_constant_long(http_request_object_ce, http_method, method_name_len, meth_num TSRMLS_CC);
	efree(http_method);
#endif

	return meth_num;
}
/* }}} */

/* {{{ STATUS http_request_method_unregister(usngigned long) */
PHP_HTTP_API STATUS _http_request_method_unregister(ulong method TSRMLS_DC)
{
	zval **zmethod;
	char *http_method;
	int method_len;

	if (SUCCESS != zend_hash_index_find(&HTTP_G(request).methods.custom, HTTP_CUSTOM_REQUEST_METHOD(method), (void **) &zmethod)) {
		http_error_ex(HE_NOTICE, HTTP_E_REQUEST_METHOD, "Request method with id %lu does not exist", method);
		return FAILURE;
	}

#if defined(ZEND_ENGINE_2) && defined(HTTP_HAVE_CURL) && !defined(WONKY)
	method_len = spprintf(&http_method, 0, "METH_%s", Z_STRVAL_PP(zmethod));
	if ((SUCCESS != zend_hash_del(&http_request_object_ce->constants_table, http_method, method_len + 1))) {
		http_error_ex(HE_NOTICE, HTTP_E_REQUEST_METHOD, "Could not unregister request method: HttpRequest::%s", http_method);
		efree(http_method);
		return FAILURE;
	}
	efree(http_method);
#endif
	
	method_len = spprintf(&http_method, 0, "HTTP_METH_%s", Z_STRVAL_PP(zmethod));
	if (	(SUCCESS != zend_hash_index_del(&HTTP_G(request).methods.custom, HTTP_CUSTOM_REQUEST_METHOD(method)))
		||	(SUCCESS != zend_hash_del(EG(zend_constants), http_method, method_len + 1))) {
		http_error_ex(HE_NOTICE, HTTP_E_REQUEST_METHOD, "Could not unregister request method: %s", http_method);
		efree(http_method);
		return FAILURE;
	}
	efree(http_method);
	
	return SUCCESS;
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

