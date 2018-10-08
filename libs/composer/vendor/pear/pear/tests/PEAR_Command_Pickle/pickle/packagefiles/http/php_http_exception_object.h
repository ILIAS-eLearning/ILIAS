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

#ifndef PHP_HTTP_EXCEPTION_OBJECT_H
#define PHP_HTTP_EXCEPTION_OBJECT_H
#ifdef ZEND_ENGINE_2

PHP_MINIT_FUNCTION(http_exception_object);

extern zend_class_entry *http_exception_object_ce;
extern zend_function_entry http_exception_object_fe[];

#define http_exception_get_default _http_exception_get_default
extern zend_class_entry *_http_exception_get_default();

#define http_exception_get_for_code(c) _http_exception_get_for_code(c)
extern zend_class_entry *_http_exception_get_for_code(long code);

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

