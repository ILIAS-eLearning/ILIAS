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

#ifdef ZEND_ENGINE_2

#include "zend_exceptions.h"

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_exception_object.h"

#define HTTP_EX_DEF_CE http_exception_object_ce
zend_class_entry *http_exception_object_ce;
#define HTTP_EX_CE(name) http_ ##name## _exception_object_ce
zend_class_entry *HTTP_EX_CE(runtime);
zend_class_entry *HTTP_EX_CE(header);
zend_class_entry *HTTP_EX_CE(malformed_headers);
zend_class_entry *HTTP_EX_CE(request_method);
zend_class_entry *HTTP_EX_CE(message_type);
zend_class_entry *HTTP_EX_CE(invalid_param);
zend_class_entry *HTTP_EX_CE(encoding);
zend_class_entry *HTTP_EX_CE(request);
zend_class_entry *HTTP_EX_CE(request_pool);
zend_class_entry *HTTP_EX_CE(socket);
zend_class_entry *HTTP_EX_CE(response);
zend_class_entry *HTTP_EX_CE(url);

PHP_MINIT_FUNCTION(http_exception_object)
{
	HTTP_REGISTER_EXCEPTION(HttpException, http_exception_object_ce, zend_exception_get_default());
	HTTP_REGISTER_EXCEPTION(HttpRuntimeException, HTTP_EX_CE(runtime), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpInvalidParamException, HTTP_EX_CE(invalid_param), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpHeaderException, HTTP_EX_CE(header), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpMalformedHeadersException, HTTP_EX_CE(malformed_headers), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpRequestMethodException, HTTP_EX_CE(request_method), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpMessageTypeException, HTTP_EX_CE(message_type), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpEncodingException, HTTP_EX_CE(encoding), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpRequestException, HTTP_EX_CE(request), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpRequestPoolException, HTTP_EX_CE(request_pool), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpSocketException, HTTP_EX_CE(socket), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpResponseException, HTTP_EX_CE(response), HTTP_EX_DEF_CE);
	HTTP_REGISTER_EXCEPTION(HttpUrlException, HTTP_EX_CE(url), HTTP_EX_DEF_CE);

	HTTP_LONG_CONSTANT("HTTP_E_RUNTIME", HTTP_E_RUNTIME);
	HTTP_LONG_CONSTANT("HTTP_E_INVALID_PARAM", HTTP_E_INVALID_PARAM);
	HTTP_LONG_CONSTANT("HTTP_E_HEADER", HTTP_E_HEADER);
	HTTP_LONG_CONSTANT("HTTP_E_MALFORMED_HEADERS", HTTP_E_MALFORMED_HEADERS);
	HTTP_LONG_CONSTANT("HTTP_E_REQUEST_METHOD", HTTP_E_REQUEST_METHOD);
	HTTP_LONG_CONSTANT("HTTP_E_MESSAGE_TYPE", HTTP_E_MESSAGE_TYPE);
	HTTP_LONG_CONSTANT("HTTP_E_ENCODING", HTTP_E_ENCODING);
	HTTP_LONG_CONSTANT("HTTP_E_REQUEST", HTTP_E_REQUEST);
	HTTP_LONG_CONSTANT("HTTP_E_REQUEST_POOL", HTTP_E_REQUEST_POOL);
	HTTP_LONG_CONSTANT("HTTP_E_SOCKET", HTTP_E_SOCKET);
	HTTP_LONG_CONSTANT("HTTP_E_RESPONSE", HTTP_E_RESPONSE);
	HTTP_LONG_CONSTANT("HTTP_E_URL", HTTP_E_URL);
	
	return SUCCESS;
}

zend_class_entry *_http_exception_get_default()
{
	return http_exception_object_ce;
}

zend_class_entry *_http_exception_get_for_code(long code)
{
	zend_class_entry *ex = http_exception_object_ce;

	switch (code)
	{
		case HTTP_E_RUNTIME:					ex = HTTP_EX_CE(runtime);					break;
		case HTTP_E_INVALID_PARAM:				ex = HTTP_EX_CE(invalid_param);				break;
		case HTTP_E_HEADER:						ex = HTTP_EX_CE(header);					break;
		case HTTP_E_MALFORMED_HEADERS:			ex = HTTP_EX_CE(malformed_headers);			break;
		case HTTP_E_REQUEST_METHOD:				ex = HTTP_EX_CE(request_method);			break;
		case HTTP_E_MESSAGE_TYPE:				ex = HTTP_EX_CE(message_type);				break;
		case HTTP_E_ENCODING:					ex = HTTP_EX_CE(encoding);					break;
		case HTTP_E_REQUEST:					ex = HTTP_EX_CE(request);					break;
		case HTTP_E_REQUEST_POOL:				ex = HTTP_EX_CE(request_pool);				break;
		case HTTP_E_SOCKET:						ex = HTTP_EX_CE(socket);					break;
		case HTTP_E_RESPONSE:					ex = HTTP_EX_CE(response);					break;
		case HTTP_E_URL:						ex = HTTP_EX_CE(url);						break;
	}

	return ex;
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

