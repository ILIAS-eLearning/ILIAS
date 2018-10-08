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
#include "zend_ini.h"
#include "php_output.h"
#include "ext/standard/url.h"

#include "php_http.h"
#include "php_http_api.h"
#include "php_http_url_api.h"
#include "php_http_std_defs.h"

#include "phpstr/phpstr.h"

#ifdef PHP_WIN32
#	include <winsock2.h>
#elif defined(HAVE_NETDB_H)
#	include <netdb.h>
#endif

ZEND_EXTERN_MODULE_GLOBALS(http);

/* {{{ char *http_absolute_url(char *) */
PHP_HTTP_API char *_http_absolute_url_ex(
	const char *url,	size_t url_len,
	const char *proto,	size_t proto_len,
	const char *host,	size_t host_len,
	unsigned port TSRMLS_DC)
{
#if defined(PHP_WIN32) || defined(HAVE_NETDB_H)
	struct servent *se;
#endif
	php_url *purl = NULL, furl;
	size_t full_len = 0;
	zval *zhost = NULL;
	char *scheme = NULL, *uri, *URL;

	if ((!url || !url_len) && (
			(!(url = SG(request_info).request_uri)) ||
			(!(url_len = strlen(SG(request_info).request_uri))))) {
		http_error(HE_WARNING, HTTP_E_RUNTIME, "Cannot build an absolute URI if supplied URL and REQUEST_URI is empty");
		return NULL;
	}

	URL = ecalloc(1, HTTP_URI_MAXLEN + 1);
	uri = estrndup(url, url_len);
	if (!(purl = php_url_parse(uri))) {
		http_error_ex(HE_WARNING, HTTP_E_URL, "Could not parse supplied URL: %s", url);
		return NULL;
	}

	furl.user		= purl->user;
	furl.pass		= purl->pass;
	furl.path		= purl->path;
	furl.query		= purl->query;
	furl.fragment	= purl->fragment;

	if (proto && proto_len) {
		furl.scheme = scheme = estrdup(proto);
	} else if (purl->scheme) {
		furl.scheme = purl->scheme;
#if defined(PHP_WIN32) || defined(HAVE_NETDB_H)
	} else if (port && (se = getservbyport(port, "tcp"))) {
		furl.scheme = (scheme = estrdup(se->s_name));
#endif
	} else {
		furl.scheme = "http";
	}

	if (port) {
		furl.port = port;
	} else if (purl->port) {
		furl.port = purl->port;
	} else if (strncmp(furl.scheme, "http", 4)) {
#if defined(PHP_WIN32) || defined(HAVE_NETDB_H)
		if (se = getservbyname(furl.scheme, "tcp")) {
			furl.port = se->s_port;
		}
#endif
	} else {
		furl.port = (furl.scheme[4] == 's') ? 443 : 80;
	}

	if (host) {
		furl.host = (char *) host;
	} else if (purl->host) {
		furl.host = purl->host;
	} else if (	(zhost = http_get_server_var("HTTP_HOST")) ||
				(zhost = http_get_server_var("SERVER_NAME"))) {
		furl.host = Z_STRVAL_P(zhost);
	} else {
		furl.host = "localhost";
	}

#define HTTP_URI_STRLCATS(URL, full_len, add_string) HTTP_URI_STRLCAT(URL, full_len, add_string, sizeof(add_string)-1)
#define HTTP_URI_STRLCATL(URL, full_len, add_string) HTTP_URI_STRLCAT(URL, full_len, add_string, strlen(add_string))
#define HTTP_URI_STRLCAT(URL, full_len, add_string, add_len) \
	if ((full_len += add_len) > HTTP_URI_MAXLEN) { \
		http_error_ex(HE_NOTICE, HTTP_E_URL, \
			"Absolute URI would have exceeded max URI length (%d bytes) - " \
			"tried to add %d bytes ('%s')", \
			HTTP_URI_MAXLEN, add_len, add_string); \
		if (scheme) { \
			efree(scheme); \
		} \
		php_url_free(purl); \
		efree(uri); \
		return URL; \
	} else { \
		strcat(URL, add_string); \
	}

	HTTP_URI_STRLCATL(URL, full_len, furl.scheme);
	HTTP_URI_STRLCATS(URL, full_len, "://");

	if (furl.user) {
		HTTP_URI_STRLCATL(URL, full_len, furl.user);
		if (furl.pass) {
			HTTP_URI_STRLCATS(URL, full_len, ":");
			HTTP_URI_STRLCATL(URL, full_len, furl.pass);
		}
		HTTP_URI_STRLCATS(URL, full_len, "@");
	}

	HTTP_URI_STRLCATL(URL, full_len, furl.host);

	if (	(!strcmp(furl.scheme, "http") && (furl.port != 80)) ||
			(!strcmp(furl.scheme, "https") && (furl.port != 443))) {
		char port_string[8] = {0};
		snprintf(port_string, 7, ":%u", furl.port);
		HTTP_URI_STRLCATL(URL, full_len, port_string);
	}

	if (furl.path) {
		if (furl.path[0] != '/') {
			HTTP_URI_STRLCATS(URL, full_len, "/");
		}
		HTTP_URI_STRLCATL(URL, full_len, furl.path);
	} else {
		HTTP_URI_STRLCATS(URL, full_len, "/");
	}

	if (furl.query) {
		HTTP_URI_STRLCATS(URL, full_len, "?");
		HTTP_URI_STRLCATL(URL, full_len, furl.query);
	}

	if (furl.fragment) {
		HTTP_URI_STRLCATS(URL, full_len, "#");
		HTTP_URI_STRLCATL(URL, full_len, furl.fragment);
	}

	if (scheme) {
		efree(scheme);
	}
	php_url_free(purl);
	efree(uri);

	return URL;
}
/* }}} */

/* {{{ STATUS http_urlencode_hash_ex(HashTable *, zend_bool, char *, size_t, char **, size_t *) */
PHP_HTTP_API STATUS _http_urlencode_hash_ex(HashTable *hash, zend_bool override_argsep,
	char *pre_encoded_data, size_t pre_encoded_len,
	char **encoded_data, size_t *encoded_len TSRMLS_DC)
{
	char *arg_sep;
	phpstr *qstr = phpstr_new();

	if (override_argsep || !strlen(arg_sep = INI_STR("arg_separator.output"))) {
		arg_sep = HTTP_URL_ARGSEP;
	}

	if (pre_encoded_len && pre_encoded_data) {
		phpstr_append(qstr, pre_encoded_data, pre_encoded_len);
	}

	if (SUCCESS != http_urlencode_hash_implementation(hash, qstr, arg_sep)) {
		phpstr_free(&qstr);
		return FAILURE;
	}

	phpstr_data(qstr, encoded_data, encoded_len);
	phpstr_free(&qstr);

	return SUCCESS;
}
/* }}} */

/* {{{ http_urlencode_hash_implementation
	Original Author: Sara Golemon <pollita@php.net> */
PHP_HTTP_API STATUS _http_urlencode_hash_implementation_ex(
				HashTable *ht, phpstr *formstr, char *arg_sep,
				const char *num_prefix, int num_prefix_len,
				const char *key_prefix, int key_prefix_len,
				const char *key_suffix, int key_suffix_len,
				zval *type TSRMLS_DC)
{
	char *key = NULL, *ekey, *newprefix, *p;
	int arg_sep_len, ekey_len, key_type, newprefix_len;
	uint key_len;
	ulong idx;
	zval **zdata = NULL, *copyzval;

	if (!ht || !formstr) {
		http_error(HE_WARNING, HTTP_E_INVALID_PARAM, "Invalid parameters");
		return FAILURE;
	}

	if (ht->nApplyCount > 0) {
		/* Prevent recursion */
		return SUCCESS;
	}

	if (!arg_sep || !strlen(arg_sep)) {
		arg_sep = HTTP_URL_ARGSEP;
	}
	arg_sep_len = strlen(arg_sep);

	for (zend_hash_internal_pointer_reset(ht);
		(key_type = zend_hash_get_current_key_ex(ht, &key, &key_len, &idx, 0, NULL)) != HASH_KEY_NON_EXISTANT;
		zend_hash_move_forward(ht)
	) {
		if (key_type == HASH_KEY_IS_STRING && key_len && key[key_len-1] == '\0') {
			/* We don't want that trailing NULL */
			key_len -= 1;
		}

#ifdef ZEND_ENGINE_2
		/* handling for private & protected object properties */
		if (key && *key == '\0' && type != NULL) {
			char *tmp;

			zend_object *zobj = zend_objects_get_address(type TSRMLS_CC);
			if (zend_check_property_access(zobj, key TSRMLS_CC) != SUCCESS) {
				/* private or protected property access outside of the class */
				continue;
			}
			zend_unmangle_property_name(key, &tmp, &key);
			key_len = strlen(key);
		}
#endif

		if (zend_hash_get_current_data_ex(ht, (void **)&zdata, NULL) == FAILURE || !zdata || !(*zdata)) {
			http_error(HE_WARNING, HTTP_E_ENCODING, "Error traversing form data array.");
			return FAILURE;
		}
		if (Z_TYPE_PP(zdata) == IS_ARRAY || Z_TYPE_PP(zdata) == IS_OBJECT) {
			if (key_type == HASH_KEY_IS_STRING) {
				ekey = php_url_encode(key, key_len, &ekey_len);
				newprefix_len = key_suffix_len + ekey_len + key_prefix_len + 1;
				newprefix = emalloc(newprefix_len + 1);
				p = newprefix;

				if (key_prefix) {
					memcpy(p, key_prefix, key_prefix_len);
					p += key_prefix_len;
				}

				memcpy(p, ekey, ekey_len);
				p += ekey_len;
				efree(ekey);

				if (key_suffix) {
					memcpy(p, key_suffix, key_suffix_len);
					p += key_suffix_len;
				}

				*(p++) = '[';
				*p = '\0';
			} else {
				/* Is an integer key */
				ekey_len = spprintf(&ekey, 12, "%ld", idx);
				newprefix_len = key_prefix_len + num_prefix_len + ekey_len + key_suffix_len + 1;
				newprefix = emalloc(newprefix_len + 1);
				p = newprefix;

				if (key_prefix) {
					memcpy(p, key_prefix, key_prefix_len);
					p += key_prefix_len;
				}

				memcpy(p, num_prefix, num_prefix_len);
				p += num_prefix_len;

				memcpy(p, ekey, ekey_len);
				p += ekey_len;
				efree(ekey);

				if (key_suffix) {
					memcpy(p, key_suffix, key_suffix_len);
					p += key_suffix_len;
				}
				*(p++) = '[';
				*p = '\0';
			}
			ht->nApplyCount++;
			http_urlencode_hash_implementation_ex(HASH_OF(*zdata), formstr, arg_sep,
				NULL, 0, newprefix, newprefix_len, "]", 1, (Z_TYPE_PP(zdata) == IS_OBJECT ? *zdata : NULL));
			ht->nApplyCount--;
			efree(newprefix);
		} else if (Z_TYPE_PP(zdata) == IS_NULL || Z_TYPE_PP(zdata) == IS_RESOURCE) {
			/* Skip these types */
			continue;
		} else {
			if (formstr->used) {
				phpstr_append(formstr, arg_sep, arg_sep_len);
			}
			/* Simple key=value */
			phpstr_append(formstr, key_prefix, key_prefix_len);
			if (key_type == HASH_KEY_IS_STRING) {
				ekey = php_url_encode(key, key_len, &ekey_len);
				phpstr_append(formstr, ekey, ekey_len);
				efree(ekey);
			} else {
				/* Numeric key */
				if (num_prefix) {
					phpstr_append(formstr, num_prefix, num_prefix_len);
				}
				ekey_len = spprintf(&ekey, 12, "%ld", idx);
				phpstr_append(formstr, ekey, ekey_len);
				efree(ekey);
			}
			phpstr_append(formstr, key_suffix, key_suffix_len);
			phpstr_appends(formstr, "=");
			switch (Z_TYPE_PP(zdata)) {
				case IS_STRING:
					ekey = php_url_encode(Z_STRVAL_PP(zdata), Z_STRLEN_PP(zdata), &ekey_len);
					break;
				case IS_LONG:
				case IS_BOOL:
					ekey_len = spprintf(&ekey, 12, "%ld", Z_LVAL_PP(zdata));
					break;
				case IS_DOUBLE:
					ekey_len = spprintf(&ekey, 48, "%.*G", (int) EG(precision), Z_DVAL_PP(zdata));
					break;
				default:
					/* fall back on convert to string */
					MAKE_STD_ZVAL(copyzval);
					*copyzval = **zdata;
					zval_copy_ctor(copyzval);
					convert_to_string_ex(&copyzval);
					ekey = php_url_encode(Z_STRVAL_P(copyzval), Z_STRLEN_P(copyzval), &ekey_len);
					zval_ptr_dtor(&copyzval);
			}
			phpstr_append(formstr, ekey, ekey_len);
			efree(ekey);
		}
	}

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

