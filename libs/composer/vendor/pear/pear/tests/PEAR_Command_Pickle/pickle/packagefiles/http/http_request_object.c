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

#if defined(ZEND_ENGINE_2) && defined(HTTP_HAVE_CURL)

#include "zend_interfaces.h"

#include "php_http_std_defs.h"
#include "php_http_request_object.h"
#include "php_http_request_api.h"
#include "php_http_request_pool_api.h"
#include "php_http.h"
#include "php_http_api.h"
#include "php_http_url_api.h"
#include "php_http_message_api.h"
#include "php_http_message_object.h"
#include "php_http_exception_object.h"

#include "missing.h"

#ifdef PHP_WIN32
#	include <winsock2.h>
#endif
#include <curl/curl.h>

ZEND_EXTERN_MODULE_GLOBALS(http);

#define HTTP_BEGIN_ARGS(method, ret_ref, req_args) 	HTTP_BEGIN_ARGS_EX(HttpRequest, method, ret_ref, req_args)
#define HTTP_EMPTY_ARGS(method, ret_ref)			HTTP_EMPTY_ARGS_EX(HttpRequest, method, ret_ref)
#define HTTP_REQUEST_ME(method, visibility)			PHP_ME(HttpRequest, method, HTTP_ARGS(HttpRequest, method), visibility)
#define HTTP_REQUEST_ALIAS(method, func)			HTTP_STATIC_ME_ALIAS(method, func, HTTP_ARGS(HttpRequest, method))

HTTP_EMPTY_ARGS(__destruct, 0);
HTTP_BEGIN_ARGS(__construct, 0, 0)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(method, 0)
	HTTP_ARG_VAL(options, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getOptions, 0);
HTTP_BEGIN_ARGS(setOptions, 0, 0)
	HTTP_ARG_VAL(options, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getSslOptions, 0);
HTTP_BEGIN_ARGS(setSslOptions, 0, 0)
	HTTP_ARG_VAL(ssl_options, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getHeaders, 0);
HTTP_BEGIN_ARGS(setHeaders, 0, 0)
	HTTP_ARG_VAL(headers, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addHeaders, 0, 1)
	HTTP_ARG_VAL(headers, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getCookies, 0);
HTTP_BEGIN_ARGS(setCookies, 0, 0)
	HTTP_ARG_VAL(cookies, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addCookies, 0, 1)
	HTTP_ARG_VAL(cookies, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getUrl, 0);
HTTP_BEGIN_ARGS(setUrl, 0, 1)
	HTTP_ARG_VAL(url, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getMethod, 0);
HTTP_BEGIN_ARGS(setMethod, 0, 1)
	HTTP_ARG_VAL(request_method, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getContentType, 0);
HTTP_BEGIN_ARGS(setContentType, 0, 1)
	HTTP_ARG_VAL(content_type, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getQueryData, 0);
HTTP_BEGIN_ARGS(setQueryData, 0, 0)
	HTTP_ARG_VAL(query_data, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addQueryData, 0, 1)
	HTTP_ARG_VAL(query_data, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getPostFields, 0);
HTTP_BEGIN_ARGS(setPostFields, 0, 0)
	HTTP_ARG_VAL(post_fields, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addPostFields, 0, 1)
	HTTP_ARG_VAL(post_fields, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getPostFiles, 0);
HTTP_BEGIN_ARGS(setPostFiles, 0, 0)
	HTTP_ARG_VAL(post_files, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addPostFile, 0, 2)
	HTTP_ARG_VAL(formname, 0)
	HTTP_ARG_VAL(filename, 0)
	HTTP_ARG_VAL(content_type, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getRawPostData, 0);
HTTP_BEGIN_ARGS(setRawPostData, 0, 0)
	HTTP_ARG_VAL(raw_post_data, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addRawPostData, 0, 1)
	HTTP_ARG_VAL(raw_post_data, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getPutFile, 0);
HTTP_BEGIN_ARGS(setPutFile, 0, 0)
	HTTP_ARG_VAL(filename, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getResponseData, 0);
HTTP_BEGIN_ARGS(getResponseHeader, 0, 0)
	HTTP_ARG_VAL(name, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(getResponseCookie, 0, 0)
	HTTP_ARG_VAL(name, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getResponseBody, 0);
HTTP_EMPTY_ARGS(getResponseCode, 0);
HTTP_BEGIN_ARGS(getResponseInfo, 0, 0)
	HTTP_ARG_VAL(name, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getResponseMessage, 1);
HTTP_EMPTY_ARGS(getRequestMessage, 1);
HTTP_EMPTY_ARGS(getHistory, 1);
HTTP_EMPTY_ARGS(clearHistory, 0);
HTTP_EMPTY_ARGS(send, 1);

HTTP_BEGIN_ARGS(get, 0, 1)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(head, 0, 1)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(postData, 0, 2)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(data, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(postFields, 0, 2)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(data, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(putFile, 0, 2)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(file, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(putStream, 0, 2)
	HTTP_ARG_VAL(url, 0)
	HTTP_ARG_VAL(stream, 0)
	HTTP_ARG_VAL(options, 0)
	HTTP_ARG_VAL(info, 1)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(methodRegister, 0, 1)
	HTTP_ARG_VAL(method_name, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(methodUnregister, 0, 1)
	HTTP_ARG_VAL(method, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(methodName, 0, 1)
	HTTP_ARG_VAL(method_id, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(methodExists, 0, 1)
	HTTP_ARG_VAL(method, 0)
HTTP_END_ARGS;

#define http_request_object_declare_default_properties() _http_request_object_declare_default_properties(TSRMLS_C)
static inline void _http_request_object_declare_default_properties(TSRMLS_D);

zend_class_entry *http_request_object_ce;
zend_function_entry http_request_object_fe[] = {
	HTTP_REQUEST_ME(__construct, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
	HTTP_REQUEST_ME(__destruct, ZEND_ACC_PUBLIC|ZEND_ACC_DTOR)

	HTTP_REQUEST_ME(setOptions, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getOptions, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(setSslOptions, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getSslOptions, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(addHeaders, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getHeaders, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(setHeaders, ZEND_ACC_PUBLIC)
	
	HTTP_REQUEST_ME(addCookies, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getCookies, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(setCookies, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setMethod, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getMethod, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setUrl, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getUrl, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setContentType, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getContentType, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setQueryData, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getQueryData, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(addQueryData, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setPostFields, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getPostFields, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(addPostFields, ZEND_ACC_PUBLIC)
	
	HTTP_REQUEST_ME(setRawPostData, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getRawPostData, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(addRawPostData, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setPostFiles, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(addPostFile, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getPostFiles, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(setPutFile, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getPutFile, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(send, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ME(getResponseData, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseHeader, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseCookie, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseCode, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseBody, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseInfo, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getResponseMessage, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getRequestMessage, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(getHistory, ZEND_ACC_PUBLIC)
	HTTP_REQUEST_ME(clearHistory, ZEND_ACC_PUBLIC)

	HTTP_REQUEST_ALIAS(get, http_get)
	HTTP_REQUEST_ALIAS(head, http_head)
	HTTP_REQUEST_ALIAS(postData, http_post_data)
	HTTP_REQUEST_ALIAS(postFields, http_post_fields)
	HTTP_REQUEST_ALIAS(putFile, http_put_file)
	HTTP_REQUEST_ALIAS(putStream, http_put_stream)

	HTTP_REQUEST_ALIAS(methodRegister, http_request_method_register)
	HTTP_REQUEST_ALIAS(methodUnregister, http_request_method_unregister)
	HTTP_REQUEST_ALIAS(methodName, http_request_method_name)
	HTTP_REQUEST_ALIAS(methodExists, http_request_method_exists)

	EMPTY_FUNCTION_ENTRY
};
static zend_object_handlers http_request_object_handlers;

PHP_MINIT_FUNCTION(http_request_object)
{
	HTTP_REGISTER_CLASS_EX(HttpRequest, http_request_object, NULL, 0);
	http_request_object_handlers.clone_obj = _http_request_object_clone_obj;
	return SUCCESS;
}

zend_object_value _http_request_object_new(zend_class_entry *ce TSRMLS_DC)
{
	return http_request_object_new_ex(ce, curl_easy_init(), NULL);
}

zend_object_value _http_request_object_new_ex(zend_class_entry *ce, CURL *ch, http_request_object **ptr TSRMLS_DC)
{
	zend_object_value ov;
	http_request_object *o;

	o = ecalloc(1, sizeof(http_request_object));
	o->zo.ce = ce;
	o->ch = ch;
	
	if (ptr) {
		*ptr = o;
	}

	phpstr_init(&o->history);
	phpstr_init(&o->request);
	phpstr_init_ex(&o->response, HTTP_CURLBUF_SIZE, 0);

	ALLOC_HASHTABLE(OBJ_PROP(o));
	zend_hash_init(OBJ_PROP(o), 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(OBJ_PROP(o), &ce->default_properties, (copy_ctor_func_t) zval_add_ref, NULL, sizeof(zval *));

	ov.handle = putObject(http_request_object, o);
	ov.handlers = &http_request_object_handlers;

	return ov;
}

zend_object_value _http_request_object_clone_obj(zval *this_ptr TSRMLS_DC)
{
	zend_object *old_zo;
	zend_object_value new_ov;
	http_request_object *new_obj;
	getObject(http_request_object, old_obj);
	
	old_zo = zend_objects_get_address(this_ptr TSRMLS_CC);
	new_ov = http_request_object_new_ex(old_zo->ce, curl_easy_duphandle(old_obj->ch), &new_obj);
	
	zend_objects_clone_members(&new_obj->zo, new_ov, old_zo, Z_OBJ_HANDLE_P(this_ptr) TSRMLS_CC);
	phpstr_append(&new_obj->history, old_obj->history.data, old_obj->history.used);
	phpstr_append(&new_obj->request, old_obj->request.data, old_obj->request.used);
	phpstr_append(&new_obj->response, old_obj->response.data, old_obj->response.used);
	
	return new_ov;
}

static inline void _http_request_object_declare_default_properties(TSRMLS_D)
{
	zend_class_entry *ce = http_request_object_ce;

	DCL_PROP_N(PROTECTED, options);
	DCL_PROP_N(PROTECTED, responseInfo);
	DCL_PROP_N(PROTECTED, responseData);
	DCL_PROP_N(PROTECTED, responseCode);
	DCL_PROP_N(PROTECTED, responseMessage);
	DCL_PROP_N(PROTECTED, postFields);
	DCL_PROP_N(PROTECTED, postFiles);

	DCL_PROP(PROTECTED, long, method, HTTP_GET);

	DCL_PROP(PROTECTED, string, url, "");
	DCL_PROP(PROTECTED, string, contentType, "");
	DCL_PROP(PROTECTED, string, rawPostData, "");
	DCL_PROP(PROTECTED, string, queryData, "");
	DCL_PROP(PROTECTED, string, putFile, "");

	DCL_PROP(PUBLIC, bool, recordHistory, 0);

#ifndef WONKY
	/*
	 * Request Method Constants
	 */
	/* HTTP/1.1 */
	DCL_CONST(long, "METH_GET", HTTP_GET);
	DCL_CONST(long, "METH_HEAD", HTTP_HEAD);
	DCL_CONST(long, "METH_POST", HTTP_POST);
	DCL_CONST(long, "METH_PUT", HTTP_PUT);
	DCL_CONST(long, "METH_DELETE", HTTP_DELETE);
	DCL_CONST(long, "METH_OPTIONS", HTTP_OPTIONS);
	DCL_CONST(long, "METH_TRACE", HTTP_TRACE);
	DCL_CONST(long, "METH_CONNECT", HTTP_CONNECT);
	/* WebDAV - RFC 2518 */
	DCL_CONST(long, "METH_PROPFIND", HTTP_PROPFIND);
	DCL_CONST(long, "METH_PROPPATCH", HTTP_PROPPATCH);
	DCL_CONST(long, "METH_MKCOL", HTTP_MKCOL);
	DCL_CONST(long, "METH_COPY", HTTP_COPY);
	DCL_CONST(long, "METH_MOVE", HTTP_MOVE);
	DCL_CONST(long, "METH_LOCK", HTTP_LOCK);
	DCL_CONST(long, "METH_UNLOCK", HTTP_UNLOCK);
	/* WebDAV Versioning - RFC 3253 */
	DCL_CONST(long, "METH_VERSION_CONTROL", HTTP_VERSION_CONTROL);
	DCL_CONST(long, "METH_REPORT", HTTP_REPORT);
	DCL_CONST(long, "METH_CHECKOUT", HTTP_CHECKOUT);
	DCL_CONST(long, "METH_CHECKIN", HTTP_CHECKIN);
	DCL_CONST(long, "METH_UNCHECKOUT", HTTP_UNCHECKOUT);
	DCL_CONST(long, "METH_MKWORKSPACE", HTTP_MKWORKSPACE);
	DCL_CONST(long, "METH_UPDATE", HTTP_UPDATE);
	DCL_CONST(long, "METH_LABEL", HTTP_LABEL);
	DCL_CONST(long, "METH_MERGE", HTTP_MERGE);
	DCL_CONST(long, "METH_BASELINE_CONTROL", HTTP_BASELINE_CONTROL);
	DCL_CONST(long, "METH_MKACTIVITY", HTTP_MKACTIVITY);
	/* WebDAV Access Control - RFC 3744 */
	DCL_CONST(long, "METH_ACL", HTTP_ACL);

	/*
	 * Auth Constants
	 */
#	if LIBCURL_VERSION_NUM >= 0x070a05
	DCL_CONST(long, "AUTH_BASIC", CURLAUTH_BASIC);
	DCL_CONST(long, "AUTH_DIGEST", CURLAUTH_DIGEST);
	DCL_CONST(long, "AUTH_NTLM", CURLAUTH_NTLM);
	DCL_CONST(long, "AUTH_ANY", CURLAUTH_ANY);
#	endif /* LIBCURL_VERSION_NUM */
#endif /* WONKY */
}

void _http_request_object_free(zend_object *object TSRMLS_DC)
{
	http_request_object *o = (http_request_object *) object;

	if (OBJ_PROP(o)) {
		zend_hash_destroy(OBJ_PROP(o));
		FREE_HASHTABLE(OBJ_PROP(o));
	}
	if (o->ch) {
		/* avoid nasty segfaults with already cleaned up callbacks */
		curl_easy_setopt(o->ch, CURLOPT_NOPROGRESS, 1);
		curl_easy_setopt(o->ch, CURLOPT_PROGRESSFUNCTION, NULL);
		curl_easy_setopt(o->ch, CURLOPT_VERBOSE, 0);
		curl_easy_setopt(o->ch, CURLOPT_DEBUGFUNCTION, NULL);
		curl_easy_cleanup(o->ch);
	}
	phpstr_dtor(&o->response);
	phpstr_dtor(&o->request);
	phpstr_dtor(&o->history);
	efree(o);
}

STATUS _http_request_object_requesthandler(http_request_object *obj, zval *this_ptr, http_request_body *body TSRMLS_DC)
{
	zval *meth, *URL, *meth_p, *URL_p;
	char *request_uri;
	STATUS status = SUCCESS;

	if (!body) {
		return FAILURE;
	}
	HTTP_CHECK_CURL_INIT(obj->ch, curl_easy_init(), return FAILURE);

	URL = convert_to_type_ex(IS_STRING, GET_PROP(obj, url), &URL_p);
	// HTTP_URI_MAXLEN+1 long char *
	if (!(request_uri = http_absolute_uri_ex(Z_STRVAL_P(URL), Z_STRLEN_P(URL), NULL, 0, NULL, 0, 0))) {
		if (URL_p) {
			zval_ptr_dtor(&URL_p);
		}
		return FAILURE;
	}
	if (URL_p) {
		zval_ptr_dtor(&URL_p);
	}
	
	meth = convert_to_type_ex(IS_LONG, GET_PROP(obj, method), &meth_p);
	switch (Z_LVAL_P(meth))
	{
		case HTTP_GET:
		case HTTP_HEAD:
			body->type = -1;
			body = NULL;
		break;

		case HTTP_PUT:
		{
			php_stream_statbuf ssb;
			php_stream *stream = php_stream_open_wrapper(Z_STRVAL_P(GET_PROP(obj, putFile)), "rb", REPORT_ERRORS|ENFORCE_SAFE_MODE, NULL);
			
			if (stream && !php_stream_stat(stream, &ssb)) {
				body->type = HTTP_REQUEST_BODY_UPLOADFILE;
				body->data = stream;
				body->size = ssb.sb.st_size;
			} else {
				status = FAILURE;
			}
		}
		break;

		case HTTP_POST:
		default:
		{
			/* check for raw post data */
			zval *raw_data_p, *raw_data = convert_to_type_ex(IS_STRING, GET_PROP(obj, rawPostData), &raw_data_p);
			
			if (Z_STRLEN_P(raw_data)) {
				zval *ctype_p, *ctype = convert_to_type_ex(IS_STRING, GET_PROP(obj, contentType), &ctype_p);
				
				if (Z_STRLEN_P(ctype)) {
					zval **headers, *opts = GET_PROP(obj, options);
					
					convert_to_array(opts);
					
					if (SUCCESS == zend_hash_find(Z_ARRVAL_P(opts), "headers", sizeof("headers"), (void **) &headers)) {
						zval **ct_header;
						
						convert_to_array(*headers);
						/* only override if not already set */
						if (SUCCESS != zend_hash_find(Z_ARRVAL_PP(headers), "Content-Type", sizeof("Content-Type"), (void **) &ct_header)) {
							add_assoc_stringl(*headers, "Content-Type", Z_STRVAL_P(ctype), Z_STRLEN_P(ctype), 1);
						}
					} else {
						zval *headers;
						
						MAKE_STD_ZVAL(headers);
						array_init(headers);
						add_assoc_stringl(headers, "Content-Type", Z_STRVAL_P(ctype), Z_STRLEN_P(ctype), 1);
						add_assoc_zval(opts, "headers", headers);
					}
				}

				if (ctype_p) {
					zval_ptr_dtor(&ctype_p);
				}
				
				body->type = HTTP_REQUEST_BODY_CSTRING;
				body->data = estrndup(Z_STRVAL_P(raw_data), Z_STRLEN_P(raw_data));
				body->size = Z_STRLEN_P(raw_data);
			} else {
				status = http_request_body_fill(body, Z_ARRVAL_P(GET_PROP(obj, postFields)), Z_ARRVAL_P(GET_PROP(obj, postFiles)));
			}

			if (raw_data_p) {
				zval_ptr_dtor(&raw_data_p);
			}
		}
		break;
	}

	if (meth_p) {
		zval_ptr_dtor(&meth_p);
	}
	
	if (status == SUCCESS) {
		zval *qdata_p, *qdata = convert_to_type_ex(IS_STRING, GET_PROP(obj, queryData), &qdata_p);
		
		if (Z_STRLEN_P(qdata)) {
			if (!strchr(request_uri, '?')) {
				strlcat(request_uri, "?", HTTP_URI_MAXLEN);
			} else {
				strlcat(request_uri, "&", HTTP_URI_MAXLEN);
			}
			strlcat(request_uri, Z_STRVAL_P(qdata), HTTP_URI_MAXLEN);
		}
		
		if (qdata_p) {
			zval_ptr_dtor(&qdata_p);
		}
		
		status = http_request_init(obj->ch, Z_LVAL_P(meth), request_uri, body, Z_ARRVAL_P(GET_PROP(obj, options)));
	}
	efree(request_uri);

	/* clean previous response */
	phpstr_dtor(&obj->response);
	/* clean previous request */
	phpstr_dtor(&obj->request);

	return status;
}

STATUS _http_request_object_responsehandler(http_request_object *obj, zval *this_ptr TSRMLS_DC)
{
	http_message *msg;

	phpstr_fix(&obj->request);
	phpstr_fix(&obj->response);
	
	msg = http_message_parse(PHPSTR_VAL(&obj->response), PHPSTR_LEN(&obj->response));
	
	if (!msg) {
		return FAILURE;
	} else {
		char *body;
		size_t body_len;
		zval *headers, *message,
			*resp = convert_to_type(IS_ARRAY, GET_PROP(obj, responseData)),
			*info = convert_to_type(IS_ARRAY, GET_PROP(obj, responseInfo));

		SEP_PROP(&resp);
		SEP_PROP(&info);
		
		if (zval_is_true(GET_PROP(obj, recordHistory))) {
			/* we need to act like a zipper, as we'll receive
			 * the requests and the responses in separate chains
			 * for redirects
			 */
			http_message *response = msg, *request = http_message_parse(PHPSTR_VAL(&obj->request), PHPSTR_LEN(&obj->request));
			http_message *free_msg = request;

			do {
				char *message;
				size_t msglen;

				http_message_tostring(response, &message, &msglen);
				phpstr_append(&obj->history, message, msglen);
				efree(message);

				http_message_tostring(request, &message, &msglen);
				phpstr_append(&obj->history, message, msglen);
				efree(message);

			} while ((response = response->parent) && (request = request->parent));

			http_message_free(&free_msg);
			phpstr_fix(&obj->history);
		}

		UPD_PROP(obj, long, responseCode, msg->http.info.response.code);

		MAKE_STD_ZVAL(headers)
		array_init(headers);

		zend_hash_copy(Z_ARRVAL_P(headers), &msg->hdrs, (copy_ctor_func_t) zval_add_ref, NULL, sizeof(zval *));
		phpstr_data(PHPSTR(msg), &body, &body_len);

		add_assoc_zval(resp, "headers", headers);
		add_assoc_stringl(resp, "body", body, body_len, 0);
		SET_PROP(obj, responseData, resp);

		MAKE_STD_ZVAL(message);
		ZVAL_OBJVAL(message, http_message_object_new_ex(http_message_object_ce, msg, NULL));
		SET_PROP(obj, responseMessage, message);
		zval_ptr_dtor(&message);

		http_request_info(obj->ch, Z_ARRVAL_P(info));
		SET_PROP(obj, responseInfo, info);

		return SUCCESS;
	}
}

#define http_request_object_set_options_subr(key, ow) \
	_http_request_object_set_options_subr(INTERNAL_FUNCTION_PARAM_PASSTHRU, (key), sizeof(key), (ow))
static inline void _http_request_object_set_options_subr(INTERNAL_FUNCTION_PARAMETERS, char *key, size_t len, int overwrite)
{
	zval *opts, **options, *new_options = NULL;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|a/!", &new_options)) {
		RETURN_FALSE;
	}

	opts = convert_to_type(IS_ARRAY, GET_PROP(obj, options));

	if (SUCCESS == zend_hash_find(Z_ARRVAL_P(opts), key, len, (void **) &options)) {
		convert_to_array(*options);
		if (overwrite) {
			zend_hash_clean(Z_ARRVAL_PP(options));
		}
		if (new_options && zend_hash_num_elements(Z_ARRVAL_P(new_options))) {
			if (overwrite) {
				array_copy(new_options, *options);
			} else {
				array_merge(new_options, *options);
			}
		}
	} else if (new_options && zend_hash_num_elements(Z_ARRVAL_P(new_options))) {
		ZVAL_ADDREF(new_options);
		add_assoc_zval(opts, key, new_options);
	}

	RETURN_TRUE;
}

#define http_request_object_get_options_subr(key) \
	_http_request_get_options_subr(INTERNAL_FUNCTION_PARAM_PASSTHRU, (key), sizeof(key))
static inline void _http_request_get_options_subr(INTERNAL_FUNCTION_PARAMETERS, char *key, size_t len)
{
	NO_ARGS;

	IF_RETVAL_USED {
		zval *opts_p, *opts, **options;
		getObject(http_request_object, obj);

		opts = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, options), &opts_p);

		array_init(return_value);

		if (SUCCESS == zend_hash_find(Z_ARRVAL_P(opts), key, len, (void **) &options)) {
			convert_to_array(*options);
			array_copy(*options, return_value);
		}

		if (opts_p) {
			zval_ptr_dtor(&opts_p);
		}
	}
}


/* ### USERLAND ### */

/* {{{ proto void HttpRequest::__construct([string url[, int request_method = HTTP_METH_GET[, array options]]])
 *
 * Instantiate a new HttpRequest object.
 * 
 * Accepts a string as optional parameter containing the target request url.
 * Additianally accepts an optional int parameter specifying the request method
 * to use and an associative array as optional third parameter which will be
 * passed to HttpRequest::setOptions(). 
 * 
 * Throws HttpException.
 */
PHP_METHOD(HttpRequest, __construct)
{
	char *URL = NULL;
	int URL_len;
	long meth = -1;
	zval *options = NULL;
	getObject(http_request_object, obj);

	SET_EH_THROW_HTTP();
	if (SUCCESS == zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|sla", &URL, &URL_len, &meth, &options)) {
		INIT_PARR(obj, options);
		INIT_PARR(obj, responseInfo);
		INIT_PARR(obj, responseData);
		INIT_PARR(obj, postFields);
		INIT_PARR(obj, postFiles);

		if (URL) {
			UPD_STRL(obj, url, URL, URL_len);
		}
		if (meth > -1) {
			UPD_PROP(obj, long, method, meth);
		}
		if (options) {
			zend_call_method_with_1_params(&getThis(), Z_OBJCE_P(getThis()), NULL, "setoptions", NULL, options);
		}
	}
	SET_EH_NORMAL();
}
/* }}} */

/* {{{ proto void HttpRequest::__destruct()
 *
 * Destroys the HttpRequest object.
 */
PHP_METHOD(HttpRequest, __destruct)
{
	getObject(http_request_object, obj);

	NO_ARGS;

	FREE_PARR(obj, options);
	FREE_PARR(obj, responseInfo);
	FREE_PARR(obj, responseData);
	FREE_PARR(obj, postFields);
	FREE_PARR(obj, postFiles);
}
/* }}} */

/* {{{ proto bool HttpRequest::setOptions([array options])
 *
 * Set the request options to use.  See http_get() for a full list of available options.
 * 
 * Accepts an array as optional parameters, which values will overwrite the 
 * currently set request options.  If the parameter is empty or mitted,
 * the optoions of the HttpRequest object will be reset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setOptions)
{
	char *key = NULL;
	ulong idx = 0;
	zval *opts = NULL, *old_opts, **opt;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|a!", &opts)) {
		RETURN_FALSE;
	}
	
	old_opts = convert_to_type(IS_ARRAY, GET_PROP(obj, options));
	SEP_PROP(&old_opts);
	
	if (!opts || !zend_hash_num_elements(Z_ARRVAL_P(opts))) {
		zend_hash_clean(Z_ARRVAL_P(old_opts));
		SET_PROP(obj, options, old_opts);
		RETURN_TRUE;
	}
	
	/* some options need extra attention -- thus cannot use array_merge() directly */
	FOREACH_KEYVAL(opts, key, idx, opt) {
		if (key) {
			if (!strcmp(key, "headers")) {
				zval **headers;
				if (SUCCESS == zend_hash_find(Z_ARRVAL_P(old_opts), "headers", sizeof("headers"), (void **) &headers)) {
					convert_to_array_ex(opt);
					convert_to_array(*headers);
					array_merge(*opt, *headers);
					continue;
				}
			} else if (!strcmp(key, "cookies")) {
				zval **cookies;
				if (SUCCESS == zend_hash_find(Z_ARRVAL_P(old_opts), "cookies", sizeof("cookies"), (void **) &cookies)) {
					convert_to_array_ex(opt);
					convert_to_array(*cookies);
					array_merge(*opt, *cookies);
					continue;
				}
			} else if (!strcmp(key, "ssl")) {
				zval **ssl;
				if (SUCCESS == zend_hash_find(Z_ARRVAL_P(old_opts), "ssl", sizeof("ssl"), (void **) &ssl)) {
					convert_to_array_ex(opt);
					convert_to_array(*ssl);
					array_merge(*opt, *ssl);
					continue;
				}
			} else if ((!strcasecmp(key, "url")) || (!strcasecmp(key, "uri"))) {
				if (Z_TYPE_PP(opt) != IS_STRING) {
					convert_to_string_ex(opt);
				}
				UPD_STRL(obj, url, Z_STRVAL_PP(opt), Z_STRLEN_PP(opt));
				continue;
			} else if (!strcmp(key, "method")) {
				if (Z_TYPE_PP(opt) != IS_LONG) {
					convert_to_long_ex(opt);
				}
				UPD_PROP(obj, long, method, Z_LVAL_PP(opt));
				continue;
			}

			ZVAL_ADDREF(*opt);
			add_assoc_zval(old_opts, key, *opt);

			/* reset */
			key = NULL;
		}
	}
	SET_PROP(obj, options, old_opts);

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto array HttpRequest::getOptions()
 *
 * Get currently set options.
 * 
 * Returns an associative array containing currently set options.
 */
PHP_METHOD(HttpRequest, getOptions)
{
	NO_ARGS;

	IF_RETVAL_USED {
		zval *opts_p, *opts;
		getObject(http_request_object, obj);

		opts = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, options), &opts_p);
		array_init(return_value);
		array_copy(opts, return_value);

		if (opts_p) {
			zval_ptr_dtor(&opts_p);
		}
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setSslOptions([array options])
 *
 * Set SSL options.
 * 
 * Accepts an associative array as parameter containing any SSL specific options.
 * If the parameter is empty or omitted, the SSL options will be reset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setSslOptions)
{
	http_request_object_set_options_subr("ssl", 1);
}
/* }}} */

/* {{{ proto bool HttpRequest::addSslOptions(array options)
 *
 * Set additional SSL options.
 * 
 * Expects an associative array as parameter containing additional SSL specific options.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addSslOptions)
{
	http_request_object_set_options_subr("ssl", 0);
}
/* }}} */

/* {{{ proto array HttpRequest::getSslOtpions()
 *
 * Get previously set SSL options.
 * 
 * Returns an associative array containing any previously set SSL options.
 */
PHP_METHOD(HttpRequest, getSslOptions)
{
	http_request_object_get_options_subr("ssl");
}
/* }}} */

/* {{{ proto bool HttpRequest::addHeaders(array headers)
 *
 * Add request header name/value pairs.
 * 
 * Expects an ssociative array as parameter containing additional header
 * name/value pairs.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addHeaders)
{
	http_request_object_set_options_subr("headers", 0);
}

/* {{{ proto bool HttpRequest::setHeaders([array headers])
 *
 * Set request header name/value pairs.
 * 
 * Accepts an associative array as parameter containing header name/value pairs.
 * If the parameter is empty or omitted, all previously set headers will be unset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setHeaders)
{
	http_request_object_set_options_subr("headers", 1);
}
/* }}} */

/* {{{ proto array HttpRequest::getHeaders()
 *
 * Get previously set request headers.
 * 
 * Returns an associative array containing all currently set headers.
 */
PHP_METHOD(HttpRequest, getHeaders)
{
	http_request_object_get_options_subr("headers");
}
/* }}} */

/* {{{ proto bool HttpRequest::setCookies([array cookies])
 *
 * Set cookies.
 * 
 * Accepts an associative array as parameter containing cookie name/value pairs.
 * If the parameter is empty or omitted, all previously set cookies will be unset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setCookies)
{
	http_request_object_set_options_subr("cookies", 1);
}
/* }}} */

/* {{{ proto bool HttpRequest::addCookies(array cookies)
 *
 * Add cookies.
 * 
 * Expects an associative array as parameter containing any cookie name/value
 * pairs to add.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addCookies)
{
	http_request_object_set_options_subr("cookies", 0);
}
/* }}} */

/* {{{ proto array HttpRequest::getCookies()
 *
 * Get previously set cookies.
 * 
 * Returns an associative array containing any previously set cookies.
 */
PHP_METHOD(HttpRequest, getCookies)
{
	http_request_object_get_options_subr("cookies");
}
/* }}} */

/* {{{ proto bool HttpRequest::setUrl(string url)
 *
 * Set the request URL.
 * 
 * Expects a string as parameter specifying the request url.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setUrl)
{
	char *URL = NULL;
	int URL_len;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &URL, &URL_len)) {
		RETURN_FALSE;
	}

	UPD_STRL(obj, url, URL, URL_len);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpRequest::getUrl()
 *
 * Get the previously set request URL.
 * 
 * Returns the currently set request url as string.
 */
PHP_METHOD(HttpRequest, getUrl)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *URL = GET_PROP(obj, url);

		RETURN_ZVAL(URL, 1, 0);
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setMethod(int request_method)
 *
 * Set the request method.
 * 
 * Expects an int as parameter specifying the request method to use.
 * In PHP 5.1+ HttpRequest::METH, otherwise the HTTP_METH constants can be used.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setMethod)
{
	long meth;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &meth)) {
		RETURN_FALSE;
	}

	UPD_PROP(obj, long, method, meth);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto int HttpRequest::getMethod()
 *
 * Get the previously set request method.
 * 
 * Returns the currently set request method.
 */
PHP_METHOD(HttpRequest, getMethod)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *meth = GET_PROP(obj, method);
		
		RETURN_ZVAL(meth, 1, 0);
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setContentType(string content_type)
 *
 * Set the content type the post request should have.
 * 
 * Expects a string as parameters containing the content type of the request
 * (primary/secondary).
 * 
 * Returns TRUE on success, or FALSE if the content type does not seem to
 * contain a primary and a secondary part.
 */
PHP_METHOD(HttpRequest, setContentType)
{
	char *ctype;
	int ct_len;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &ctype, &ct_len)) {
		RETURN_FALSE;
	}

	HTTP_CHECK_CONTENT_TYPE(ctype, RETURN_FALSE);
	UPD_STRL(obj, contentType, ctype, ct_len);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpRequest::getContentType()
 *
 * Get the previously content type.
 * 
 * Returns the previously set content type as string.
 */
PHP_METHOD(HttpRequest, getContentType)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *ctype = GET_PROP(obj, contentType);
		
		RETURN_ZVAL(ctype, 1, 0);
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setQueryData([mixed query_data])
 *
 * Set the URL query parameters to use, overwriting previously set query parameters.
 * Affects any request types.
 * 
 * Accepts a string or associative array parameter containing the pre-encoded 
 * query string or to be encoded query fields.  If the parameter is empty or
 * omitted, the query data will be unset. 
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setQueryData)
{
	zval *qdata = NULL;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z!", &qdata)) {
		RETURN_FALSE;
	}

	if ((!qdata) || Z_TYPE_P(qdata) == IS_NULL) {
		UPD_STRL(obj, queryData, "", 0);
	} else if ((Z_TYPE_P(qdata) == IS_ARRAY) || (Z_TYPE_P(qdata) == IS_OBJECT)) {
		char *query_data = NULL;
		
		if (SUCCESS != http_urlencode_hash(HASH_OF(qdata), &query_data)) {
			RETURN_FALSE;
		}
		
		UPD_PROP(obj, string, queryData, query_data);
		efree(query_data);
	} else {
		convert_to_string_ex(&qdata);
		UPD_STRL(obj, queryData, Z_STRVAL_P(qdata), Z_STRLEN_P(qdata));
	}
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpRequest::getQueryData()
 *
 * Get the current query data in form of an urlencoded query string.
 * 
 * Returns a string containing the urlencoded query.
 */
PHP_METHOD(HttpRequest, getQueryData)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *qdata_p, *qdata = convert_to_type_ex(IS_STRING, GET_PROP(obj, queryData), &qdata_p);
		
		RETURN_ZVAL(qdata, 1, 0);

		if (qdata_p) {
			zval_ptr_dtor(&qdata_p);
		}
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::addQueryData(array query_params)
 *
 * Add parameters to the query parameter list, leaving previously set unchanged.
 * Affects any request type.
 * 
 * Expects an associative array as parameter containing the query fields to add.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addQueryData)
{
	zval *qdata, *old_qdata, *old_qdata_p;
	char *query_data = NULL;
	size_t query_data_len = 0;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &qdata)) {
		RETURN_FALSE;
	}

	old_qdata = convert_to_type_ex(IS_STRING, GET_PROP(obj, queryData), &old_qdata_p);

	if (SUCCESS != http_urlencode_hash_ex(HASH_OF(qdata), 1, Z_STRVAL_P(old_qdata), Z_STRLEN_P(old_qdata), &query_data, &query_data_len)) {
		if (old_qdata_p) {
			zval_ptr_dtor(&old_qdata_p);
		}
		RETURN_FALSE;
	}

	UPD_STRL(obj, queryData, query_data, query_data_len);
	efree(query_data);

	if (old_qdata_p) {
		zval_ptr_dtor(&old_qdata_p);
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool HttpRequest::addPostFields(array post_data)
 *
 * Adds POST data entries, leaving previously set unchanged, unless a
 * post entry with the same name already exists. 
 * Affects only POST and custom requests.
 * 
 * Expects an associative array as parameter containing the post fields.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addPostFields)
{
	zval *post, *post_data;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &post_data)) {
		RETURN_FALSE;
	}

	post = convert_to_type(IS_ARRAY, GET_PROP(obj, postFields));
	array_merge(post_data, post);

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool HttpRequest::setPostFields([array post_data])
 *
 * Set the POST data entries, overwriting previously set POST data.
 * Affects only POST and custom requests.
 * 
 * Accepts an associative array as parameter containing the post fields.
 * If the parameter is empty or omitted, the post data will be unset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setPostFields)
{
	zval *post, *post_data = NULL;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a!", &post_data)) {
		RETURN_FALSE;
	}

	post = convert_to_type(IS_ARRAY, GET_PROP(obj, postFields));
	
	SEP_PROP(&post);
	zend_hash_clean(Z_ARRVAL_P(post));
	if (post_data && zend_hash_num_elements(Z_ARRVAL_P(post_data))) {
		array_copy(post_data, post);
	}
	SET_PROP(obj, postFields, post);

	RETURN_TRUE;
}
/* }}}*/

/* {{{ proto array HttpRequest::getPostFields()
 *
 * Get previously set POST data.
 * 
 * Returns the currently set post fields as associative array.
 */
PHP_METHOD(HttpRequest, getPostFields)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *post_data_p, *post_data = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, postFields), &post_data_p);
		
		array_init(return_value);
		array_copy(post_data, return_value);

		if (post_data_p) {
			zval_ptr_dtor(&post_data_p);
		}
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setRawPostData([string raw_post_data])
 *
 * Set raw post data to send, overwriting previously set raw post data.  Don't 
 * forget to specify a content type. Affects only POST and custom requests.
 * Only either post fields or raw post data can be used for each request.
 * Raw post data has higher precedence and will be used even if post fields
 * are set.  
 * 
 * Accepts a string as parameter containing the *raw* post data.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setRawPostData)
{
	char *raw_data = NULL;
	int data_len = 0;
	getObject(http_request_object, obj);
	
	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &raw_data, &data_len)) {
		RETURN_FALSE;
	}
	
	if (!raw_data) {
		raw_data = "";
	}
	
	UPD_STRL(obj, rawPostData, raw_data, data_len);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool HttpRequest::addRawPostData(string raw_post_data)
 *
 * Add raw post data, leaving previously set raw post data unchanged.
 * Affects only POST and custom requests.
 * 
 * Expects a string as parameter containing the raw post data to concatenate.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, addRawPostData)
{
	char *raw_data, *new_data;
	int data_len;
	getObject(http_request_object, obj);
	
	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &raw_data, &data_len)) {
		RETURN_FALSE;
	}
	
	if (data_len) {
		zval *zdata_p, *zdata = convert_to_type_ex(IS_STRING, GET_PROP(obj, rawPostData), &zdata_p);
		
		new_data = emalloc(Z_STRLEN_P(zdata) + data_len + 1);
		new_data[Z_STRLEN_P(zdata) + data_len] = '\0';
		
		if (Z_STRLEN_P(zdata)) {
			memcpy(new_data, Z_STRVAL_P(zdata), Z_STRLEN_P(zdata));
		}
		
		memcpy(new_data + Z_STRLEN_P(zdata), raw_data, data_len);
		UPD_STRL(obj, rawPostData, new_data, Z_STRLEN_P(zdata) + data_len);

		if (zdata_p) {
			zval_ptr_dtor(&zdata_p);
		}
	}
	
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpRequest::getRawPostData()
 *
 * Get previously set raw post data.
 * 
 * Returns a string containing the currently set raw post data.
 */
PHP_METHOD(HttpRequest, getRawPostData)
{
	NO_ARGS;
	
	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *raw_data_p, *raw_data = convert_to_type_ex(IS_STRING, GET_PROP(obj, rawPostData), &raw_data_p);
		
		RETVAL_ZVAL(raw_data, 1, 0);

		if (raw_data_p) {
			zval_ptr_dtor(&raw_data_p);
		}
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::addPostFile(string name, string file[, string content_type = "application/x-octetstream"])
 *
 * Add a file to the POST request, leaving prefiously set files unchanged.
 * Affects only POST and custom requests. Cannot be used with raw post data.
 * 
 * Expects a string parameter containing the form element name, and a string
 * parameter containing the path to the file which should be uploaded.
 * Additionally accepts an optional string parameter which could contain
 * the content type of the file.
 * 
 * Returns TRUE on success, or FALSE if the content type seems not to contain a 
 * primary and a secondary content type part.
 */
PHP_METHOD(HttpRequest, addPostFile)
{
	zval *files, *entry;
	char *name, *file, *type = NULL;
	int name_len, file_len, type_len = 0;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss|s", &name, &name_len, &file, &file_len, &type, &type_len)) {
		RETURN_FALSE;
	}

	if (type_len) {
		HTTP_CHECK_CONTENT_TYPE(type, RETURN_FALSE);
	} else {
		type = "application/x-octetstream";
		type_len = sizeof("application/x-octetstream") - 1;
	}

	MAKE_STD_ZVAL(entry);
	array_init(entry);

	add_assoc_stringl(entry, "name", name, name_len, 1);
	add_assoc_stringl(entry, "type", type, type_len, 1);
	add_assoc_stringl(entry, "file", file, file_len, 1);

	files = convert_to_type(IS_ARRAY, GET_PROP(obj, postFiles));
	add_next_index_zval(files, entry);

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool HttpRequest::setPostFiles([array post_files])
 *
 * Set files to post, overwriting previously set post files.
 * Affects only POST and requests. Cannot be used with raw post data.
 * 
 * Accepts an array containing the files to post.  Each entry should be an
 * associative array with "name", "file" and "type" keys.  If the parameter
 * is empty or omitted the post files will be unset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setPostFiles)
{
	zval *files, *pFiles;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &files)) {
		RETURN_FALSE;
	}

	pFiles = convert_to_type(IS_ARRAY, GET_PROP(obj, postFiles));
	
	SEP_PROP(&pFiles);
	zend_hash_clean(Z_ARRVAL_P(pFiles));
	if (files && zend_hash_num_elements(Z_ARRVAL_P(files))) {
		array_copy(files, pFiles);
	}
	SET_PROP(obj, postFiles, pFiles);

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto array HttpRequest::getPostFiles()
 *
 * Get all previously added POST files.
 * 
 * Returns an array containing currently set post files.
 */
PHP_METHOD(HttpRequest, getPostFiles)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *files_p, *files = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, postFiles), &files_p);

		array_init(return_value);
		array_copy(files, return_value);

		if (files_p) {
			zval_ptr_dtor(&files_p);
		}
	}
}
/* }}} */

/* {{{ proto bool HttpRequest::setPutFile([string file])
 *
 * Set file to put. Affects only PUT requests.
 * 
 * Accepts a string as parameter referencing the path to file.
 * If the parameter is empty or omitted the put file will be unset.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpRequest, setPutFile)
{
	char *file = "";
	int file_len = 0;
	getObject(http_request_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &file, &file_len)) {
		RETURN_FALSE;
	}

	UPD_STRL(obj, putFile, file, file_len);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpRequest::getPutFile()
 *
 * Get previously set put file.
 * 
 * Returns a string containing the path to the currently set put file.
 */
PHP_METHOD(HttpRequest, getPutFile)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *putfile_p, *putfile = convert_to_type_ex(IS_STRING, GET_PROP(obj, putFile), &putfile_p);
		
		RETVAL_ZVAL(putfile, 1, 0);

		if (putfile_p) {
			zval_ptr_dtor(&putfile_p);
		}
	}
}
/* }}} */

/* {{{ proto array HttpRequest::getResponseData()
 *
 * Get all response data after the request has been sent.
 * 
 * Returns an associative array with the key "headers" containing an associative
 * array holding all response headers, as well as the ley "body" containing a
 * string with the response body.  
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseData)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *data_p, *data = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, responseData), &data_p);
		
		array_init(return_value);
		array_copy(data, return_value);

		if (data_p) {
			zval_ptr_dtor(&data_p);
		}
	}
}
/* }}} */

/* {{{ proto mixed HttpRequest::getResponseHeader([string name])
 *
 * Get response header(s) after the request has been sent.
 * 
 * Accepts an string as optional parameter specifying a certain header to read.
 * If the parameter is empty or omitted all response headers will be returned.
 * 
 * Returns either a string with the value of the header matching name if requested, 
 * FALSE on failure, or an associative array containing all response headers.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseHeader)
{
	IF_RETVAL_USED {
		zval *data_p, *data, **headers, **header;
		char *header_name = NULL;
		int header_len = 0;
		getObject(http_request_object, obj);

		if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &header_name, &header_len)) {
			RETURN_FALSE;
		}

		data = convert_to_type_ex(IS_ARRAY, GET_PROP(obj, responseData), &data_p);
		if (SUCCESS == zend_hash_find(Z_ARRVAL_P(data), "headers", sizeof("headers"), (void **) &headers)) {
			convert_to_array(*headers);
			if (!header_len || !header_name) {
				array_init(return_value);
				array_copy(*headers, return_value);
			} else if (SUCCESS == zend_hash_find(Z_ARRVAL_PP(headers), pretty_key(header_name, header_len, 1, 1), header_len + 1, (void **) &header)) {
				RETVAL_ZVAL(*header, 1, 0);
			} else {
				RETVAL_FALSE;
			}
		} else {
			RETVAL_FALSE;
		}

		if (data_p) {
			zval_ptr_dtor(&data_p);
		}
	}
}
/* }}} */

/* {{{ proto array HttpRequest::getResponseCookie([string name])
 *
 * Get response cookie(s) after the request has been sent.
 * 
 * Accepts a string as optional parameter specifying the name of the cookie to read.
 * If the parameter is empty or omitted, an associative array with all received
 * cookies will be returned.
 * 
 * Returns either an associative array with the cookie's name, value and any
 * additional params of the cookie matching name if requested, FALSE on failure,
 * or an array containing all received cookies as arrays.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseCookie)
{
	IF_RETVAL_USED {
		zval *data, **headers;
		char *cookie_name = NULL;
		int cookie_len = 0;
		getObject(http_request_object, obj);

		if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &cookie_name, &cookie_len)) {
			RETURN_FALSE;
		}

		array_init(return_value);

		data = convert_to_type(IS_ARRAY, GET_PROP(obj, responseData));
		if (SUCCESS == zend_hash_find(Z_ARRVAL_P(data), "headers", sizeof("headers"), (void **) &headers)) {
			ulong idx = 0;
			char *key = NULL;
			zval **header = NULL;

			convert_to_array_ex(headers);
			FOREACH_HASH_KEYVAL(Z_ARRVAL_PP(headers), key, idx, header) {
				if (key && !strcasecmp(key, "Set-Cookie")) {
					/* several cookies? */
					if (Z_TYPE_PP(header) == IS_ARRAY) {
						zval **cookie;

						FOREACH_HASH_VAL(Z_ARRVAL_PP(header), cookie) {
							zval *cookie_hash;
							MAKE_STD_ZVAL(cookie_hash);
							array_init(cookie_hash);

							if (SUCCESS == http_parse_cookie(Z_STRVAL_PP(cookie), Z_ARRVAL_P(cookie_hash))) {
								if (!cookie_len) {
									add_next_index_zval(return_value, cookie_hash);
								} else {
									zval **name;

									if (	(SUCCESS == zend_hash_find(Z_ARRVAL_P(cookie_hash), "name", sizeof("name"), (void **) &name)) &&
											(!strcmp(Z_STRVAL_PP(name), cookie_name))) {
										add_next_index_zval(return_value, cookie_hash);
										return; /* <<< FOUND >>> */
									} else {
										zval_dtor(cookie_hash);
										efree(cookie_hash);
									}
								}
							} else {
								zval_dtor(cookie_hash);
								efree(cookie_hash);
							}
						}
					} else {
						zval *cookie_hash;
						
						MAKE_STD_ZVAL(cookie_hash);
						array_init(cookie_hash);
						convert_to_string_ex(header);
						
						if (SUCCESS == http_parse_cookie(Z_STRVAL_PP(header), Z_ARRVAL_P(cookie_hash))) {
							if (!cookie_len) {
								add_next_index_zval(return_value, cookie_hash);
							} else {
								zval **name;

								if (	(SUCCESS == zend_hash_find(Z_ARRVAL_P(cookie_hash), "name", sizeof("name"), (void **) &name)) &&
										(!strcmp(Z_STRVAL_PP(name), cookie_name))) {
									add_next_index_zval(return_value, cookie_hash);
								} else {
									zval_dtor(cookie_hash);
									efree(cookie_hash);
								}
							}
						} else {
							zval_dtor(cookie_hash);
							efree(cookie_hash);
						}
					}
					break;
				}
				/* reset key */
				key = NULL;
			}
		}
	}
}
/* }}} */

/* {{{ proto string HttpRequest::getResponseBody()
 *
 * Get the response body after the request has been sent.
 * 
 * Returns a string containing the response body.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseBody)
{
	NO_ARGS;

	IF_RETVAL_USED {
		zval **body;
		getObject(http_request_object, obj);
		zval *data = convert_to_type(IS_ARRAY, GET_PROP(obj, responseData));
		
		if (SUCCESS == zend_hash_find(Z_ARRVAL_P(data), "body", sizeof("body"), (void **) &body)) {
			RETURN_ZVAL(*body, 1, 0);
		} else {
			RETURN_FALSE;
		}
	}
}
/* }}} */

/* {{{ proto int HttpRequest::getResponseCode()
 *
 * Get the response code after the request has been sent.
 * 
 * Returns an int representing the response code.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseCode)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_request_object, obj);
		zval *code_p, *code = convert_to_type_ex(IS_LONG, GET_PROP(obj, responseCode), &code_p);
		
		RETVAL_ZVAL(code, 1, 0);

		if (code_p) {
			zval_ptr_dtor(&code_p);
		}
	}
}
/* }}} */

/* {{{ proto mixed HttpRequest::getResponseInfo([string name])
 *
 * Get response info after the request has been sent.
 * See http_get() for a full list of returned info.
 * 
 * Accepts a string as optional parameter specifying the info to read.
 * If the parameter is empty or omitted, an associative array containing
 * all available info will be returned.
 * 
 * Returns either a scalar containing the value of the info matching name if
 * requested, FALSE on failure, or an associative array containing all
 * available info.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.
 */
PHP_METHOD(HttpRequest, getResponseInfo)
{
	IF_RETVAL_USED {
		zval *info, **infop;
		char *info_name = NULL;
		int info_len = 0;
		getObject(http_request_object, obj);

		if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &info_name, &info_len)) {
			RETURN_FALSE;
		}

		info = convert_to_type(IS_ARRAY, GET_PROP(obj, responseInfo));

		if (info_len && info_name) {
			if (SUCCESS == zend_hash_find(Z_ARRVAL_P(info), pretty_key(info_name, info_len, 0, 0), info_len + 1, (void **) &infop)) {
				RETURN_ZVAL(*infop, 1, 0);
			} else {
				http_error_ex(HE_NOTICE, HTTP_E_INVALID_PARAM, "Could not find response info named %s", info_name);
				RETURN_FALSE;
			}
		} else {
			array_init(return_value);
			array_copy(info, return_value);
		}
	}
}
/* }}}*/

/* {{{ proto HttpMessage HttpRequest::getResponseMessage()
 *
 * Get the full response as HttpMessage object after the request has been sent.
 * 
 * Returns an HttpMessage object of the response.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.  Use HttpMessage::getParentMessage()
 * to access the data of previously received responses whithin this request
 * cycle.
 * 
 * Throws HttpException.
 */
PHP_METHOD(HttpRequest, getResponseMessage)
{
	NO_ARGS;

	IF_RETVAL_USED {
		zval *message;
		getObject(http_request_object, obj);

		SET_EH_THROW_HTTP();
		message = GET_PROP(obj, responseMessage);
		if (Z_TYPE_P(message) == IS_OBJECT) {
			RETVAL_OBJECT(message);
		} else {
			RETVAL_NULL();
		}
		SET_EH_NORMAL();
	}
}
/* }}} */

/* {{{ proto HttpMessage HttpRequest::getRequestMessage()
 *
 * Get sent HTTP message.
 * 
 * Returns an HttpMessage object representing the sent request.
 * 
 * If redirects were allowed and several responses were received, the data 
 * references the last received response.  Use HttpMessage::getParentMessage()
 * to access the data of previously sent requests whithin this request
 * cycle.
 */
PHP_METHOD(HttpRequest, getRequestMessage)
{
	NO_ARGS;

	IF_RETVAL_USED {
		http_message *msg;
		getObject(http_request_object, obj);

		SET_EH_THROW_HTTP();
		if (msg = http_message_parse(PHPSTR_VAL(&obj->request), PHPSTR_LEN(&obj->request))) {
			RETVAL_OBJVAL(http_message_object_new_ex(http_message_object_ce, msg, NULL));
		}
		SET_EH_NORMAL();
	}
}
/* }}} */

/* {{{ proto HttpMessage HttpRequest::getHistory()
 *
 * Get all sent requests and received responses as an HttpMessage object.
 * 
 * If you don't want to record history at all, set the instance variable
 * HttpRequest::$recoedHistory to FALSE. 
 * 
 * Returns an HttpMessage object representing the complete request/response
 * history.
 * 
 * The object references the last received response, use HttpMessage::getParentMessage() 
 * to access the data of previously sent requests and received responses.
 * 
 * Throws HttpMalformedHeaderException.
 */
PHP_METHOD(HttpRequest, getHistory)
{
	NO_ARGS;

	IF_RETVAL_USED {
		http_message *msg;
		getObject(http_request_object, obj);

		SET_EH_THROW_HTTP();
		if (msg = http_message_parse(PHPSTR_VAL(&obj->history), PHPSTR_LEN(&obj->history))) {
			RETVAL_OBJVAL(http_message_object_new_ex(http_message_object_ce, msg, NULL));
		}
		SET_EH_NORMAL();
	}
}
/* }}} */

/* {{{ proto void HttpRequest::clearHistory()
 *
 * Clear the history.
 */
PHP_METHOD(HttpRequest, clearHistory)
{
	NO_ARGS {
		getObject(http_request_object, obj);
		phpstr_dtor(&obj->history);
	}
}
/* }}} */

/* {{{ proto HttpMessage HttpRequest::send()
 *
 * Send the HTTP request.
 * 
 * Returns the received response as HttpMessage object.
 * 
 * Throws HttpRuntimeException, HttpRequestException, 
 * HttpMalformedHeaderException, HttpEncodingException.
 *
 * GET example:
 * <pre>
 * <?php
 * $r = new HttpRequest('http://example.com/feed.rss', HTTP_GET);
 * $r->setOptions(array('lastmodified' => filemtime('local.rss')));
 * $r->addQueryData(array('category' => 3));
 * try {
 *     $r->send();
 *     if ($r->getResponseCode() == 200) {
 *         file_put_contents('local.rss', $r->getResponseBody());
 *    }
 * } catch (HttpException $ex) {
 *     echo $ex;
 * }
 * ?>
 * </pre>
 *
 * POST example:
 * <pre>
 * <?php
 * $r = new HttpRequest('http://example.com/form.php', HTTP_POST);
 * $r->setOptions(array('cookies' => array('lang' => 'de')));
 * $r->addPostFields(array('user' => 'mike', 'pass' => 's3c|r3t'));
 * $r->addPostFile('image', 'profile.jpg', 'image/jpeg');
 * try {
 *     echo $r->send()->getBody();
 * } catch (HttpException $ex) {
 *     echo $ex;
 * }
 * ?>
 * </pre>
 */
PHP_METHOD(HttpRequest, send)
{
	http_request_body body = {0, NULL, 0};
	getObject(http_request_object, obj);

	NO_ARGS;

	SET_EH_THROW_HTTP();

	if (obj->pool) {
		http_error(HE_WARNING, HTTP_E_RUNTIME, "Cannot perform HttpRequest::send() while attached to an HttpRequestPool");
		SET_EH_NORMAL();
		RETURN_FALSE;
	}

	RETVAL_NULL();
	
	if (	(SUCCESS == http_request_object_requesthandler(obj, getThis(), &body)) &&
			(SUCCESS == http_request_exec(obj->ch, NULL, &obj->response, &obj->request)) &&
			(SUCCESS == http_request_object_responsehandler(obj, getThis()))) {
		RETVAL_OBJECT(GET_PROP(obj, responseMessage));
	}
	http_request_body_dtor(&body);

	SET_EH_NORMAL();
}
/* }}} */

#endif /* ZEND_ENGINE_2 && HTTP_HAVE_CURL */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

