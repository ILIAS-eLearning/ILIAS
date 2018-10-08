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

#ifndef PHP_HTTP_UTIL_OBJECT_H
#define PHP_HTTP_UTIL_OBJECT_H
#ifdef ZEND_ENGINE_2

extern zend_class_entry *http_util_object_ce;
extern zend_function_entry http_util_object_fe[];

extern PHP_MINIT_FUNCTION(http_util_object);

PHP_METHOD(HttpUtil, date);
PHP_METHOD(HttpUtil, absoluteUri);
PHP_METHOD(HttpUtil, negotiateLanguage);
PHP_METHOD(HttpUtil, negotiateCharset);
PHP_METHOD(HttpUtil, matchModified);
PHP_METHOD(HttpUtil, matchEtag);
PHP_METHOD(HttpUtil, parseHeaders);
PHP_METHOD(HttpUtil, parseMessage);
PHP_METHOD(HttpUtil, chunkedDecode);
PHP_METHOD(HttpUtil, gzEncode);
PHP_METHOD(HttpUtil, gzDecode);
PHP_METHOD(HttpUtil, deflate);
PHP_METHOD(HttpUtil, inflate);
PHP_METHOD(HttpUtil, compress);
PHP_METHOD(HttpUtil, uncompress);
PHP_METHOD(HttpUtil, support);

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

