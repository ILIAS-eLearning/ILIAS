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

#ifndef PHP_HTTP_RESPONSE_OBJECT_H
#define PHP_HTTP_RESPONSE_OBJECT_H
#ifdef ZEND_ENGINE_2

#include "missing.h"

#ifndef WONKY

extern zend_class_entry *http_response_object_ce;
extern zend_function_entry http_response_object_fe[];

extern PHP_MINIT_FUNCTION(http_response_object);

PHP_METHOD(HttpResponse, setHeader);
PHP_METHOD(HttpResponse, getHeader);
PHP_METHOD(HttpResponse, setETag);
PHP_METHOD(HttpResponse, getETag);
PHP_METHOD(HttpResponse, setLastModified);
PHP_METHOD(HttpResponse, getLastModified);
PHP_METHOD(HttpResponse, setContentDisposition);
PHP_METHOD(HttpResponse, getContentDisposition);
PHP_METHOD(HttpResponse, setContentType);
PHP_METHOD(HttpResponse, getContentType);
PHP_METHOD(HttpResponse, guessContentType);
PHP_METHOD(HttpResponse, setCache);
PHP_METHOD(HttpResponse, getCache);
PHP_METHOD(HttpResponse, setCacheControl);
PHP_METHOD(HttpResponse, getCacheControl);
PHP_METHOD(HttpResponse, setGzip);
PHP_METHOD(HttpResponse, getGzip);
PHP_METHOD(HttpResponse, setThrottleDelay);
PHP_METHOD(HttpResponse, getThrottleDelay);
PHP_METHOD(HttpResponse, setBufferSize);
PHP_METHOD(HttpResponse, getBufferSize);
PHP_METHOD(HttpResponse, setData);
PHP_METHOD(HttpResponse, getData);
PHP_METHOD(HttpResponse, setFile);
PHP_METHOD(HttpResponse, getFile);
PHP_METHOD(HttpResponse, setStream);
PHP_METHOD(HttpResponse, getStream);
PHP_METHOD(HttpResponse, send);
PHP_METHOD(HttpResponse, capture);
PHP_METHOD(HttpResponse, redirect);
PHP_METHOD(HttpResponse, status);
PHP_METHOD(HttpResponse, getRequestHeaders);
PHP_METHOD(HttpResponse, getRequestBody);

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

