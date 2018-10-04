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

#include "php_http.h"
#include "php_http_api.h"
#include "php_http_std_defs.h"
#include "php_http_message_object.h"
#include "php_http_exception_object.h"

#include "phpstr/phpstr.h"
#include "missing.h"

ZEND_EXTERN_MODULE_GLOBALS(http);

#define HTTP_BEGIN_ARGS(method, ret_ref, req_args) 	HTTP_BEGIN_ARGS_EX(HttpMessage, method, ret_ref, req_args)
#define HTTP_EMPTY_ARGS(method, ret_ref)			HTTP_EMPTY_ARGS_EX(HttpMessage, method, ret_ref)
#define HTTP_MESSAGE_ME(method, visibility)			PHP_ME(HttpMessage, method, HTTP_ARGS(HttpMessage, method), visibility)

HTTP_BEGIN_ARGS(__construct, 0, 0)
	HTTP_ARG_VAL(message, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(fromString, 1, 1)
	HTTP_ARG_VAL(message, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getBody, 0);
HTTP_BEGIN_ARGS(setBody, 0, 1)
	HTTP_ARG_VAL(body, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getHeaders, 0);
HTTP_BEGIN_ARGS(setHeaders, 0, 1)
	HTTP_ARG_VAL(headers, 0)
HTTP_END_ARGS;

HTTP_BEGIN_ARGS(addHeaders, 0, 1)
	HTTP_ARG_VAL(headers, 0)
	HTTP_ARG_VAL(append, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getType, 0);
HTTP_BEGIN_ARGS(setType, 0, 1)
	HTTP_ARG_VAL(type, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getResponseCode, 0);
HTTP_BEGIN_ARGS(setResponseCode, 0, 1)
	HTTP_ARG_VAL(response_code, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getRequestMethod, 0);
HTTP_BEGIN_ARGS(setRequestMethod, 0, 1)
	HTTP_ARG_VAL(request_method, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getRequestUri, 0);
HTTP_BEGIN_ARGS(setRequestUri, 0, 1)
	HTTP_ARG_VAL(uri, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getHttpVersion, 0);
HTTP_BEGIN_ARGS(setHttpVersion, 0, 1)
	HTTP_ARG_VAL(http_version, 0)
HTTP_END_ARGS;

HTTP_EMPTY_ARGS(getParentMessage, 1);
HTTP_EMPTY_ARGS(send, 0);
HTTP_BEGIN_ARGS(toString, 0, 0)
	HTTP_ARG_VAL(include_parent, 0)
HTTP_END_ARGS;

#define http_message_object_declare_default_properties() _http_message_object_declare_default_properties(TSRMLS_C)
static inline void _http_message_object_declare_default_properties(TSRMLS_D);
#define http_message_object_read_prop _http_message_object_read_prop
static zval *_http_message_object_read_prop(zval *object, zval *member, int type TSRMLS_DC);
#define http_message_object_write_prop _http_message_object_write_prop
static void _http_message_object_write_prop(zval *object, zval *member, zval *value TSRMLS_DC);
#define http_message_object_get_props _http_message_object_get_props
static HashTable *_http_message_object_get_props(zval *object TSRMLS_DC);

zend_class_entry *http_message_object_ce;
zend_function_entry http_message_object_fe[] = {
	HTTP_MESSAGE_ME(__construct, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
	HTTP_MESSAGE_ME(getBody, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setBody, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getHeaders, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setHeaders, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(addHeaders, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getType, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setType, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getResponseCode, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setResponseCode, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getRequestMethod, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setRequestMethod, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getRequestUri, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setRequestUri, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getHttpVersion, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(setHttpVersion, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(getParentMessage, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(send, ZEND_ACC_PUBLIC)
	HTTP_MESSAGE_ME(toString, ZEND_ACC_PUBLIC)

	ZEND_MALIAS(HttpMessage, __toString, toString, HTTP_ARGS(HttpMessage, toString), ZEND_ACC_PUBLIC)

	HTTP_MESSAGE_ME(fromString, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	
	EMPTY_FUNCTION_ENTRY
};
static zend_object_handlers http_message_object_handlers;

PHP_MINIT_FUNCTION(http_message_object)
{
	HTTP_REGISTER_CLASS_EX(HttpMessage, http_message_object, NULL, 0);

	HTTP_LONG_CONSTANT("HTTP_MSG_NONE", HTTP_MSG_NONE);
	HTTP_LONG_CONSTANT("HTTP_MSG_REQUEST", HTTP_MSG_REQUEST);
	HTTP_LONG_CONSTANT("HTTP_MSG_RESPONSE", HTTP_MSG_RESPONSE);

	http_message_object_handlers.clone_obj = _http_message_object_clone_obj;
	http_message_object_handlers.read_property = http_message_object_read_prop;
	http_message_object_handlers.write_property = http_message_object_write_prop;
	http_message_object_handlers.get_properties = http_message_object_get_props;
	http_message_object_handlers.get_property_ptr_ptr = NULL;
	
	return SUCCESS;
}

zend_object_value _http_message_object_new(zend_class_entry *ce TSRMLS_DC)
{
	return http_message_object_new_ex(ce, NULL, NULL);
}

zend_object_value _http_message_object_new_ex(zend_class_entry *ce, http_message *msg, http_message_object **ptr TSRMLS_DC)
{
	zend_object_value ov;
	http_message_object *o;

	o = ecalloc(1, sizeof(http_message_object));
	o->zo.ce = ce;
	
	if (ptr) {
		*ptr = o;
	}

	if (msg) {
		o->message = msg;
		if (msg->parent) {
			o->parent = http_message_object_new_ex(ce, msg->parent, NULL);
		}
	} else {
		o->message = http_message_init(NULL);
	}

	ALLOC_HASHTABLE(OBJ_PROP(o));
	zend_hash_init(OBJ_PROP(o), 0, NULL, ZVAL_PTR_DTOR, 0);

	ov.handle = putObject(http_message_object, o);
	ov.handlers = &http_message_object_handlers;

	return ov;
}

zend_object_value _http_message_object_clone_obj(zval *this_ptr TSRMLS_DC)
{
	getObject(http_message_object, obj);
	return http_message_object_new_ex(Z_OBJCE_P(this_ptr), http_message_dup(obj->message), NULL);
}

static inline void _http_message_object_declare_default_properties(TSRMLS_D)
{
	zend_class_entry *ce = http_message_object_ce;

#ifndef WONKY
	DCL_CONST(long, "TYPE_NONE", HTTP_MSG_NONE);
	DCL_CONST(long, "TYPE_REQUEST", HTTP_MSG_REQUEST);
	DCL_CONST(long, "TYPE_RESPONSE", HTTP_MSG_RESPONSE);
#endif

	DCL_PROP(PROTECTED, long, type, HTTP_MSG_NONE);
	DCL_PROP(PROTECTED, string, body, "");
	DCL_PROP(PROTECTED, string, requestMethod, "");
	DCL_PROP(PROTECTED, string, requestUri, "");
	DCL_PROP(PROTECTED, long, responseCode, 0);
	DCL_PROP_N(PROTECTED, httpVersion);
	DCL_PROP_N(PROTECTED, headers);
	DCL_PROP_N(PROTECTED, parentMessage);
}

void _http_message_object_free(zend_object *object TSRMLS_DC)
{
	http_message_object *o = (http_message_object *) object;

	if (OBJ_PROP(o)) {
		zend_hash_destroy(OBJ_PROP(o));
		FREE_HASHTABLE(OBJ_PROP(o));
	}
	if (o->message) {
		http_message_dtor(o->message);
		efree(o->message);
	}
	efree(o);
}

static zval *_http_message_object_read_prop(zval *object, zval *member, int type TSRMLS_DC)
{
	getObjectEx(http_message_object, obj, object);
	http_message *msg = obj->message;
	zval *return_value;
#ifdef WONKY
	ulong h = zend_get_hash_value(Z_STRVAL_P(member), Z_STRLEN_P(member)+1);
#else
	zend_property_info *pinfo = zend_get_property_info(obj->zo.ce, member, 1 TSRMLS_CC);
	
	if (!pinfo || ACC_PROP_PUBLIC(pinfo->flags)) {
		return zend_get_std_object_handlers()->read_property(object, member, type TSRMLS_CC);
	}
#endif

	if (type == BP_VAR_W) {
		zend_error(E_ERROR, "Cannot access HttpMessage properties by reference or array key/index");
		return NULL;
	}
	
	ALLOC_ZVAL(return_value);
	return_value->refcount = 0;
	return_value->is_ref = 0;

#ifdef WONKY
	switch (h)
#else
	switch (pinfo->h)
#endif
	{
		case HTTP_MSG_PROPHASH_TYPE:
		case HTTP_MSG_CHILD_PROPHASH_TYPE:
			RETVAL_LONG(msg->type);
		break;

		case HTTP_MSG_PROPHASH_HTTP_VERSION:
		case HTTP_MSG_CHILD_PROPHASH_HTTP_VERSION:
			RETVAL_DOUBLE(msg->http.version);
		break;

		case HTTP_MSG_PROPHASH_BODY:
		case HTTP_MSG_CHILD_PROPHASH_BODY:
			phpstr_fix(PHPSTR(msg));
			RETVAL_PHPSTR(PHPSTR(msg), 0, 1);
		break;

		case HTTP_MSG_PROPHASH_HEADERS:
		case HTTP_MSG_CHILD_PROPHASH_HEADERS:
			array_init(return_value);
			zend_hash_copy(Z_ARRVAL_P(return_value), &msg->hdrs, (copy_ctor_func_t) zval_add_ref, NULL, sizeof(zval *));
		break;

		case HTTP_MSG_PROPHASH_PARENT_MESSAGE:
		case HTTP_MSG_CHILD_PROPHASH_PARENT_MESSAGE:
			if (msg->parent) {
				RETVAL_OBJVAL(obj->parent);
			} else {
				RETVAL_NULL();
			}
		break;

		case HTTP_MSG_PROPHASH_REQUEST_METHOD:
		case HTTP_MSG_CHILD_PROPHASH_REQUEST_METHOD:
			if (HTTP_MSG_TYPE(REQUEST, msg) && msg->http.info.request.method) {
				RETVAL_STRING(msg->http.info.request.method, 1);
			} else {
				RETVAL_NULL();
			}
		break;

		case HTTP_MSG_PROPHASH_REQUEST_URI:
		case HTTP_MSG_CHILD_PROPHASH_REQUEST_URI:
			if (HTTP_MSG_TYPE(REQUEST, msg) && msg->http.info.request.URI) {
				RETVAL_STRING(msg->http.info.request.URI, 1);
			} else {
				RETVAL_NULL();
			}
		break;

		case HTTP_MSG_PROPHASH_RESPONSE_CODE:
		case HTTP_MSG_CHILD_PROPHASH_RESPONSE_CODE:
			if (HTTP_MSG_TYPE(RESPONSE, msg)) {
				RETVAL_LONG(msg->http.info.response.code);
			} else {
				RETVAL_NULL();
			}
		break;
		
		case HTTP_MSG_PROPHASH_RESPONSE_STATUS:
		case HTTP_MSG_CHILD_PROPHASH_RESPONSE_STATUS:
			if (HTTP_MSG_TYPE(RESPONSE, msg) && msg->http.info.response.status) {
				RETVAL_STRING(msg->http.info.response.status, 1);
			} else {
				RETVAL_NULL();
			}
		break;
		
		default:
#ifdef WONKY
			return zend_get_std_object_handlers()->read_property(object, member, type TSRMLS_CC);
#else
			RETVAL_NULL();
#endif
		break;
	}

	return return_value;
}

static void _http_message_object_write_prop(zval *object, zval *member, zval *value TSRMLS_DC)
{
	getObjectEx(http_message_object, obj, object);
	http_message *msg = obj->message;
#ifdef WONKY
	ulong h = zend_get_hash_value(Z_STRVAL_P(member), Z_STRLEN_P(member) + 1);
#else
	zend_property_info *pinfo = zend_get_property_info(obj->zo.ce, member, 1 TSRMLS_CC);
	
	if (!pinfo || ACC_PROP_PUBLIC(pinfo->flags)) {
		zend_get_std_object_handlers()->write_property(object, member, value TSRMLS_CC);
		return;
	}
#endif

#ifdef WONKY
	switch (h)
#else
	switch (pinfo->h)
#endif
	{
		case HTTP_MSG_PROPHASH_TYPE:
		case HTTP_MSG_CHILD_PROPHASH_TYPE:
			convert_to_long_ex(&value);
			http_message_set_type(msg, Z_LVAL_P(value));
		break;

		case HTTP_MSG_PROPHASH_HTTP_VERSION:
		case HTTP_MSG_CHILD_PROPHASH_HTTP_VERSION:
			convert_to_double_ex(&value);
			msg->http.version = Z_DVAL_P(value);
		break;

		case HTTP_MSG_PROPHASH_BODY:
		case HTTP_MSG_CHILD_PROPHASH_BODY:
			convert_to_string_ex(&value);
			phpstr_dtor(PHPSTR(msg));
			phpstr_from_string_ex(PHPSTR(msg), Z_STRVAL_P(value), Z_STRLEN_P(value));
		break;

		case HTTP_MSG_PROPHASH_HEADERS:
		case HTTP_MSG_CHILD_PROPHASH_HEADERS:
			convert_to_array_ex(&value);
			zend_hash_clean(&msg->hdrs);
			zend_hash_copy(&msg->hdrs, Z_ARRVAL_P(value), (copy_ctor_func_t) zval_add_ref, NULL, sizeof(zval *));
		break;

		case HTTP_MSG_PROPHASH_PARENT_MESSAGE:
		case HTTP_MSG_CHILD_PROPHASH_PARENT_MESSAGE:
			if (Z_TYPE_P(value) == IS_OBJECT && instanceof_function(Z_OBJCE_P(value), http_message_object_ce TSRMLS_CC)) {
				if (msg->parent) {
					zval tmp;
					tmp.value.obj = obj->parent;
					Z_OBJ_DELREF(tmp);
				}
				Z_OBJ_ADDREF_P(value);
				obj->parent = value->value.obj;
			}
		break;

		case HTTP_MSG_PROPHASH_REQUEST_METHOD:
		case HTTP_MSG_CHILD_PROPHASH_REQUEST_METHOD:
			if (HTTP_MSG_TYPE(REQUEST, msg)) {
				convert_to_string_ex(&value);
				STR_SET(msg->http.info.request.method, estrndup(Z_STRVAL_P(value), Z_STRLEN_P(value)));
			}
		break;

		case HTTP_MSG_PROPHASH_REQUEST_URI:
		case HTTP_MSG_CHILD_PROPHASH_REQUEST_URI:
			if (HTTP_MSG_TYPE(REQUEST, msg)) {
				convert_to_string_ex(&value);
				STR_SET(msg->http.info.request.URI, estrndup(Z_STRVAL_P(value), Z_STRLEN_P(value)));
			}
		break;

		case HTTP_MSG_PROPHASH_RESPONSE_CODE:
		case HTTP_MSG_CHILD_PROPHASH_RESPONSE_CODE:
			if (HTTP_MSG_TYPE(RESPONSE, msg)) {
				convert_to_long_ex(&value);
				msg->http.info.response.code = Z_LVAL_P(value);
			}
		break;
		
		case HTTP_MSG_PROPHASH_RESPONSE_STATUS:
		case HTTP_MSG_CHILD_PROPHASH_RESPONSE_STATUS:
			if (HTTP_MSG_TYPE(RESPONSE, msg)) {
				convert_to_string_ex(&value);
				STR_SET(msg->http.info.response.status, estrndup(Z_STRVAL_P(value), Z_STRLEN_P(value)));
			}
		break;
		
		default:
#ifdef WONKY
			zend_get_std_object_handlers()->write_property(object, member, value TSRMLS_CC);
#endif
		break;
	}
}

static HashTable *_http_message_object_get_props(zval *object TSRMLS_DC)
{
	zval *headers;
	getObjectEx(http_message_object, obj, object);
	http_message *msg = obj->message;
	HashTable *props = OBJ_PROP(obj);
	zval array;
	
	INIT_ZARR(array, props);

#define ASSOC_PROP(array, ptype, name, val) \
	{ \
		char *m_prop_name; \
		int m_prop_len; \
		zend_mangle_property_name(&m_prop_name, &m_prop_len, "*", 1, name, lenof(name), 0); \
		add_assoc_ ##ptype## _ex(&array, m_prop_name, sizeof(name)+4, val); \
		efree(m_prop_name); \
	}
#define ASSOC_STRING(array, name, val) ASSOC_STRINGL(array, name, val, strlen(val))
#define ASSOC_STRINGL(array, name, val, len) \
	{ \
		char *m_prop_name; \
		int m_prop_len; \
		zend_mangle_property_name(&m_prop_name, &m_prop_len, "*", 1, name, lenof(name), 0); \
		add_assoc_stringl_ex(&array, m_prop_name, sizeof(name)+4, val, len, 1); \
		efree(m_prop_name); \
	}

	ASSOC_PROP(array, long, "type", msg->type);
	ASSOC_PROP(array, double, "httpVersion", msg->http.version);

	switch (msg->type)
	{
		case HTTP_MSG_REQUEST:
			ASSOC_PROP(array, long, "responseCode", 0);
			ASSOC_STRINGL(array, "responseStatus", "", 0);
			ASSOC_STRING(array, "requestMethod", msg->http.info.request.method);
			ASSOC_STRING(array, "requestUri", msg->http.info.request.URI);
		break;

		case HTTP_MSG_RESPONSE:
			ASSOC_PROP(array, long, "responseCode", msg->http.info.response.code);
			ASSOC_STRING(array, "responseStatus", msg->http.info.response.status);
			ASSOC_STRINGL(array, "requestMethod", "", 0);
			ASSOC_STRINGL(array, "requestUri", "", 0);
		break;

		case HTTP_MSG_NONE:
		default:
			ASSOC_PROP(array, long, "responseCode", 0);
			ASSOC_STRINGL(array, "responseStatus", "", 0);
			ASSOC_STRINGL(array, "requestMethod", "", 0);
			ASSOC_STRINGL(array, "requestUri", "", 0);
		break;
	}

	MAKE_STD_ZVAL(headers);
	array_init(headers);
	zend_hash_copy(Z_ARRVAL_P(headers), &msg->hdrs, (copy_ctor_func_t) zval_add_ref, NULL, sizeof(zval *));
	ASSOC_PROP(array, zval, "headers", headers);
	ASSOC_STRINGL(array, "body", PHPSTR_VAL(msg), PHPSTR_LEN(msg));

	return OBJ_PROP(obj);
}

/* ### USERLAND ### */

/* {{{ proto void HttpMessage::__construct([string message])
 *
 * Instantiate a new HttpMessage object.
 * 
 * Accepts an optional string parameter containing a single or several 
 * consecutive HTTP messages.  The constructed object will actually 
 * represent the *last* message of the passed string.  If there were
 * prior messages, those can be accessed by HttpMessage::getParentMessage().
 * 
 * Throws HttpMalformedHeaderException.
 */
PHP_METHOD(HttpMessage, __construct)
{
	char *message = NULL;
	int length = 0;
	getObject(http_message_object, obj);

	SET_EH_THROW_HTTP();
	if (SUCCESS == zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s", &message, &length) && message && length) {
		if (obj->message = http_message_parse(message, length)) {
			if (obj->message->parent) {
				obj->parent = http_message_object_new_ex(Z_OBJCE_P(getThis()), obj->message->parent, NULL);
			}
		}
	} else if (!obj->message) {
		obj->message = http_message_new();
	}
	SET_EH_NORMAL();
}
/* }}} */

/* {{{ proto static HttpMessage HttpMessage::fromString(string raw_message)
 *
 * Create an HttpMessage object from a string. Kind of a static constructor.
 * 
 * Expects a string parameter containing a sinlge or several consecutive
 * HTTP messages.
 * 
 * Returns an HttpMessage object on success or NULL on failure.
 * 
 * Throws HttpMalformedHeadersException.
 */
PHP_METHOD(HttpMessage, fromString)
{
	char *string = NULL;
	int length = 0;
	http_message *msg = NULL;

	RETVAL_NULL();
	
	SET_EH_THROW_HTTP();
	if (SUCCESS == zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &string, &length)) {
		if (msg = http_message_parse(string, length)) {
			Z_TYPE_P(return_value) = IS_OBJECT;
			return_value->value.obj = http_message_object_new_ex(http_message_object_ce, msg, NULL);
		}
	}
	SET_EH_NORMAL();
}
/* }}} */

/* {{{ proto string HttpMessage::getBody()
 *
 * Get the body of the parsed HttpMessage.
 * 
 * Returns the message body as string.
 */
PHP_METHOD(HttpMessage, getBody)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);
		RETURN_PHPSTR(&obj->message->body, PHPSTR_FREE_NOT, 1);
	}
}
/* }}} */

/* {{{ proto void HttpMessage::setBody(string body)
 *
 * Set the body of the HttpMessage.
 * NOTE: Don't forget to update any headers accordingly.
 * 
 * Expects a string parameter containing the new body of the message.
 */
PHP_METHOD(HttpMessage, setBody)
{
	char *body;
	int len;
	getObject(http_message_object, obj);
	
	if (SUCCESS == zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &body, &len)) {
		phpstr_dtor(PHPSTR(obj->message));
		phpstr_from_string_ex(PHPSTR(obj->message), body, len);		
	}
}
/* }}} */

/* {{{ proto array HttpMessage::getHeaders()
 *
 * Get Message Headers.
 * 
 * Returns an associative array containing the messages HTTP headers.
 */
PHP_METHOD(HttpMessage, getHeaders)
{
	NO_ARGS;

	IF_RETVAL_USED {
		zval headers;
		getObject(http_message_object, obj);

		INIT_ZARR(headers, &obj->message->hdrs);
		array_init(return_value);
		array_copy(&headers, return_value);
	}
}
/* }}} */

/* {{{ proto void HttpMessage::setHeaders(array headers)
 *
 * Sets new headers.
 * 
 * Expects an associative array as parameter containing the new HTTP headers,
 * which will replace *all* previous HTTP headers of the message.
 */
PHP_METHOD(HttpMessage, setHeaders)
{
	zval *new_headers, old_headers;
	getObject(http_message_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a/", &new_headers)) {
		return;
	}

	zend_hash_clean(&obj->message->hdrs);
	INIT_ZARR(old_headers, &obj->message->hdrs);
	array_copy(new_headers, &old_headers);
}
/* }}} */

/* {{{ proto void HttpMessage::addHeaders(array headers[, bool append = false])
 *
 * Add headers. If append is true, headers with the same name will be separated, else overwritten.
 * 
 * Expects an associative array as parameter containing the additional HTTP headers
 * to add to the messages existing headers.  If the optional bool parameter is true,
 * and a header with the same name of one to add exists already, this respective
 * header will be converted to an array containing both header values, otherwise
 * it will be overwritten with the new header value.
 */
PHP_METHOD(HttpMessage, addHeaders)
{
	zval old_headers, *new_headers;
	zend_bool append = 0;
	getObject(http_message_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a|b", &new_headers, &append)) {
		return;
	}

	INIT_ZARR(old_headers, &obj->message->hdrs);
	if (append) {
		array_append(new_headers, &old_headers);
	} else {
		array_merge(new_headers, &old_headers);
	}
}
/* }}} */

/* {{{ proto int HttpMessage::getType()
 *
 * Get Message Type. (HTTP_MSG_NONE|HTTP_MSG_REQUEST|HTTP_MSG_RESPONSE)
 * 
 * Returns the HttpMessage::TYPE.
 */
PHP_METHOD(HttpMessage, getType)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);
		RETURN_LONG(obj->message->type);
	}
}
/* }}} */

/* {{{ proto void HttpMessage::setType(int type)
 *
 * Set Message Type. (HTTP_MSG_NONE|HTTP_MSG_REQUEST|HTTP_MSG_RESPONSE)
 * 
 * Exptects an int parameter, the HttpMessage::TYPE.
 */
PHP_METHOD(HttpMessage, setType)
{
	long type;
	getObject(http_message_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &type)) {
		return;
	}
	http_message_set_type(obj->message, type);
}
/* }}} */

/* {{{ proto int HttpMessage::getResponseCode()
 *
 * Get the Response Code of the Message.
 * 
 * Returns the HTTP response code if the message is of type 
 * HttpMessage::TYPE_RESPONSE, else FALSE.
 */
PHP_METHOD(HttpMessage, getResponseCode)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);
		HTTP_CHECK_MESSAGE_TYPE_RESPONSE(obj->message, RETURN_FALSE);
		RETURN_LONG(obj->message->http.info.response.code);
	}
}
/* }}} */

/* {{{ proto bool HttpMessage::setResponseCode(int code)
 *
 * Set the response code of an HTTP Response Message.
 * 
 * Expects an int parameter with the HTTP response code.
 * 
 * Returns TRUE on success, or FALSE if the message is not of type
 * HttpMessage::TYPE_RESPONSE or the response code is out of range (100-510).
 */
PHP_METHOD(HttpMessage, setResponseCode)
{
	long code;
	getObject(http_message_object, obj);

	HTTP_CHECK_MESSAGE_TYPE_RESPONSE(obj->message, RETURN_FALSE);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &code)) {
		RETURN_FALSE;
	}
	if (code < 100 || code > 510) {
		http_error_ex(HE_WARNING, HTTP_E_INVALID_PARAM, "Invalid response code (100-510): %ld", code);
		RETURN_FALSE;
	}

	obj->message->http.info.response.code = code;
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpMessage::getRequestMethod()
 *
 * Get the Request Method of the Message.
 * 
 * Returns the request method name on success, or FALSE if the message is
 * not of type HttpMessage::TYPE_REQUEST.
 */
PHP_METHOD(HttpMessage, getRequestMethod)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);
		HTTP_CHECK_MESSAGE_TYPE_REQUEST(obj->message, RETURN_FALSE);
		RETURN_STRING(obj->message->http.info.request.method, 1);
	}
}
/* }}} */

/* {{{ proto bool HttpMessage::setRequestMethod(string method)
 *
 * Set the Request Method of the HTTP Message.
 * 
 * Expects a string parameter containing the request method name.
 * 
 * Returns TRUE on success, or FALSE if the message is not of type
 * HttpMessage::TYPE_REQUEST or an invalid request method was supplied. 
 */
PHP_METHOD(HttpMessage, setRequestMethod)
{
	char *method;
	int method_len;
	getObject(http_message_object, obj);

	HTTP_CHECK_MESSAGE_TYPE_REQUEST(obj->message, RETURN_FALSE);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &method, &method_len)) {
		RETURN_FALSE;
	}
	if (method_len < 1) {
		http_error(HE_WARNING, HTTP_E_INVALID_PARAM, "Cannot set HttpMessage::requestMethod to an empty string");
		RETURN_FALSE;
	}
	if (SUCCESS != http_check_method(method)) {
		http_error_ex(HE_WARNING, HTTP_E_REQUEST_METHOD, "Unknown request method: %s", method);
		RETURN_FALSE;
	}

	STR_SET(obj->message->http.info.request.method, estrndup(method, method_len));
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpMessage::getRequestUri()
 *
 * Get the Request URI of the Message.
 * 
 * Returns the request uri as string on success, or FALSE if the message
 * is not of type HttpMessage::TYPE_REQUEST. 
 */
PHP_METHOD(HttpMessage, getRequestUri)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);
		HTTP_CHECK_MESSAGE_TYPE_REQUEST(obj->message, RETURN_FALSE);
		RETURN_STRING(obj->message->http.info.request.URI, 1);
	}
}
/* }}} */

/* {{{ proto bool HttpMessage::setRequestUri(string URI)
 *
 * Set the Request URI of the HTTP Message.
 * 
 * Expects a string parameters containing the request uri.
 * 
 * Returns TRUE on success, or FALSE if the message is not of type
 * HttpMessage::TYPE_REQUEST or supplied URI was empty.
 */
PHP_METHOD(HttpMessage, setRequestUri)
{
	char *URI;
	int URIlen;
	getObject(http_message_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &URI, &URIlen)) {
		RETURN_FALSE;
	}
	HTTP_CHECK_MESSAGE_TYPE_REQUEST(obj->message, RETURN_FALSE);
	if (URIlen < 1) {
		http_error(HE_WARNING, HTTP_E_INVALID_PARAM, "Cannot set HttpMessage::requestUri to an empty string");
		RETURN_FALSE;
	}

	STR_SET(obj->message->http.info.request.URI, estrndup(URI, URIlen));
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto string HttpMessage::getHttpVersion()
 *
 * Get the HTTP Protocol Version of the Message.
 * 
 * Returns the HTTP protocol version as string.
 */
PHP_METHOD(HttpMessage, getHttpVersion)
{
	NO_ARGS;

	IF_RETVAL_USED {
		char ver[4] = {0};
		getObject(http_message_object, obj);

		sprintf(ver, "%1.1lf", obj->message->http.version);
		RETURN_STRINGL(ver, 3, 1);
	}
}
/* }}} */

/* {{{ proto bool HttpMessage::setHttpVersion(string version)
 *
 * Set the HTTP Protocol version of the Message.
 * 
 * Expects a string parameter containing the HTTP protocol version.
 * 
 * Returns TRUE on success, or FALSE if supplied version is out of range (1.0/1.1).
 */
PHP_METHOD(HttpMessage, setHttpVersion)
{
	char v[4];
	zval *zv;
	getObject(http_message_object, obj);

	if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z/", &zv)) {
		return;
	}

	convert_to_double(zv);
	sprintf(v, "%1.1lf", Z_DVAL_P(zv));
	if (strcmp(v, "1.0") && strcmp(v, "1.1")) {
		http_error_ex(HE_WARNING, HTTP_E_INVALID_PARAM, "Invalid HTTP protocol version (1.0 or 1.1): %s", v);
		RETURN_FALSE;
	}

	obj->message->http.version = Z_DVAL_P(zv);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto HttpMessage HttpMessage::getParentMessage()
 *
 * Get parent Message.
 * 
 * Returns the parent HttpMessage on success, or NULL if there's none.
 */
PHP_METHOD(HttpMessage, getParentMessage)
{
	NO_ARGS;

	IF_RETVAL_USED {
		getObject(http_message_object, obj);

		if (obj->message->parent) {
			RETVAL_OBJVAL(obj->parent);
		} else {
			RETVAL_NULL();
		}
	}
}
/* }}} */

/* {{{ proto bool HttpMessage::send()
 *
 * Send the Message according to its type as Response or Request.
 * This provides limited functionality compared to HttpRequest and HttpResponse.
 * 
 * Returns TRUE on success, or FALSE on failure.
 */
PHP_METHOD(HttpMessage, send)
{
	getObject(http_message_object, obj);

	NO_ARGS;

	RETURN_SUCCESS(http_message_send(obj->message));
}
/* }}} */

/* {{{ proto string HttpMessage::toString([bool include_parent = false])
 *
 * Get the string representation of the Message.
 * 
 * Accepts a bool parameter which specifies whether the returned string
 * should also contain any parent messages.
 * 
 * Returns the full message as string.
 */
PHP_METHOD(HttpMessage, toString)
{
	IF_RETVAL_USED {
		char *string;
		size_t length;
		zend_bool include_parent = 0;
		getObject(http_message_object, obj);

		if (SUCCESS != zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|b", &include_parent)) {
			RETURN_FALSE;
		}

		if (include_parent) {
			http_message_serialize(obj->message, &string, &length);
		} else {
			http_message_tostring(obj->message, &string, &length);
		}
		RETURN_STRINGL(string, length, 0);
	}
}
/* }}} */

#endif /* ZEND_ENGINE_2 */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

