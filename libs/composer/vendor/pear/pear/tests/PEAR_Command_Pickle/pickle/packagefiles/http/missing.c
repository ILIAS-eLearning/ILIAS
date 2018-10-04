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

#include "php.h"
#include "missing.h"

#ifdef WONKY
int zend_declare_property_double(zend_class_entry *ce, char *name, int name_length, double value, int access_type TSRMLS_DC)
{
	zval *property = pemalloc(sizeof(zval), ce->type & ZEND_INTERNAL_CLASS);
	INIT_PZVAL(property);
	ZVAL_DOUBLE(property, value);
	return zend_declare_property(ce, name, name_length, property, access_type TSRMLS_CC);
}

void zend_update_property_double(zend_class_entry *scope, zval *object, char *name, int name_length, double value TSRMLS_DC)
{
	zval *tmp = ecalloc(1, sizeof(zval));
	ZVAL_DOUBLE(tmp, value);
	zend_update_property(scope, object, name, name_length, tmp TSRMLS_CC);
}

int zend_declare_property_bool(zend_class_entry *ce, char *name, int name_length, long value, int access_type TSRMLS_DC)
{
	zval *property = pemalloc(sizeof(zval), ce->type & ZEND_INTERNAL_CLASS);
	INIT_PZVAL(property);
	ZVAL_BOOL(property, value);
	return zend_declare_property(ce, name, name_length, property, access_type TSRMLS_CC);
}

void zend_update_property_bool(zend_class_entry *scope, zval *object, char *name, int name_length, long value TSRMLS_DC)
{
	zval *tmp = ecalloc(1, sizeof(zval));
	ZVAL_BOOL(tmp, value);
	zend_update_property(scope, object, name, name_length, tmp TSRMLS_CC);
}

void zend_update_property_stringl(zend_class_entry *scope, zval *object, char *name, int name_length, char *value, int value_len TSRMLS_DC)
{
	zval *tmp;
	
	ALLOC_ZVAL(tmp);
	tmp->is_ref = 0;
	tmp->refcount = 0;
	ZVAL_STRINGL(tmp, value, value_len, 1);
	zend_update_property(scope, object, name, name_length, tmp TSRMLS_CC);
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

