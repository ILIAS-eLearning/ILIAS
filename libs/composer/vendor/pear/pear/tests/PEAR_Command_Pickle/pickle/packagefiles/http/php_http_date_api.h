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

#ifndef PHP_HTTP_DATE_API_H
#define PHP_HTTP_DATE_API_H

#include "php_http_std_defs.h"

#define http_date(t) _http_date((t) TSRMLS_CC)
PHP_HTTP_API char *_http_date(time_t t TSRMLS_DC);

#define http_parse_date(d) _http_parse_date((d))
PHP_HTTP_API time_t _http_parse_date(const char *date);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

