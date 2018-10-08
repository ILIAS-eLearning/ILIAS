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
#include "php_streams.h"
#include "ext/standard/php_lcg.h"

#include "php_http.h"
#include "php_http_std_defs.h"
#include "php_http_api.h"
#include "php_http_date_api.h"
#include "php_http_send_api.h"
#include "php_http_headers_api.h"
#include "php_http_date_api.h"
#include "php_http_cache_api.h"
#include "php_http_encoding_api.h"

ZEND_EXTERN_MODULE_GLOBALS(http);

#define http_flush(d, l) _http_flush((d), (l) SRMLS_CC)
/* {{{ static inline void http_flush() */
static inline void _http_flush(const char *data, size_t data_len TSRMLS_DC)
{
	PHPWRITE(data, data_len);
	php_end_ob_buffer(1, 1 TSRMLS_CC);
	sapi_flush(TSRMLS_C);
	
#define HTTP_MSEC(s) (s * 1000)
#define HTTP_USEC(s) (HTTP_MSEC(s) * 1000)
#define HTTP_NSEC(s) (HTTP_USEC(s) * 1000)
#define HTTP_NANOSEC (1000 * 1000 * 1000)
#define HTTP_DIFFSEC (0.001)

	if (HTTP_G(send).throttle_delay >= HTTP_DIFFSEC) {
#if defined(PHP_WIN32)
		Sleep((DWORD) HTTP_MSEC(HTTP_G(send).throttle_delay));
#elif defined(HAVE_USLEEP)
		usleep(HTTP_USEC(HTTP_G(send).throttle_delay));
#elif defined(HAVE_NANOSLEEP)
		struct timespec req, rem;

		req.tv_sec = (time_t) HTTP_G(send).throttle_delay;
		req.tv_nsec = HTTP_NSEC(HTTP_G(send).throttle_delay) % HTTP_NANOSEC;

		while (nanosleep(&req, &rem) && (errno == EINTR) && (HTTP_NSEC(rem.tv_sec) + rem.tv_nsec) > HTTP_NSEC(HTTP_DIFFSEC))) {
			req.tv_sec = rem.tv_sec;
			req.tv_nsec = rem.tv_nsec;
		}
#endif
	}
}
/* }}} */

/* {{{ http_send_response_start */
#define http_send_response_start(b, cl) _http_send_response_start((b), (cl) TSRMLS_CC)
static inline void _http_send_response_start(void **buffer, size_t content_length TSRMLS_DC)
{
	if (http_encoding_response_start(content_length)) {
#ifdef HTTP_HAVE_ZLIB
		char *encoded;
		size_t encoded_len;
		http_encoding_stream *s = emalloc(sizeof(http_encoding_stream));
		
		http_encoding_stream_init(s, HTTP_G(send).gzip_encoding == HTTP_ENCODING_GZIP, -1, &encoded, &encoded_len);
		phpstr_chunked_output(&s->storage, encoded, encoded_len, HTTP_G(send).buffer_size, _http_flush TSRMLS_CC);
		STR_FREE(encoded);
		*buffer = s;
#endif
	}
}
/* }}} */

/* {{{ http_send_response_data_plain */
#define http_send_response_data_plain(b, d, dl) _http_send_response_data_plain((b), (d), (dl) TSRMLS_CC)
static inline void _http_send_response_data_plain(void **buffer, const char *data, size_t data_len TSRMLS_DC)
{
	if (HTTP_G(send).gzip_encoding) {
#ifdef HTTP_HAVE_ZLIB
		char *encoded;
		size_t encoded_len;
		http_encoding_stream *s = *((http_encoding_stream **) buffer);
		
		http_encoding_stream_update(s, data, data_len, &encoded, &encoded_len);
		phpstr_chunked_output(&s->storage, encoded, encoded_len, HTTP_G(send).buffer_size, _http_flush TSRMLS_CC);
		efree(encoded);
#else
		http_error(HE_ERROR, HTTP_E_RESPONSE, "Attempt to send GZIP response despite being able to do so; please report this bug");
#endif
	} else {
		phpstr_chunked_output((phpstr **) buffer, data, data_len, HTTP_G(send).buffer_size, _http_flush TSRMLS_CC);
	}
}
/* }}} */

#define HTTP_CHUNK_AVAIL(len, cs) ((len -= cs) >= 0)
/* {{{ http_send_response_data_fetch */
#define http_send_response_data_fetch(b, d, l, m, s, e) _http_send_response_data_fetch((b), (d), (l), (m), (s), (e) TSRMLS_CC)
static inline void _http_send_response_data_fetch(void **buffer, const void *data, size_t data_len, http_send_mode mode, size_t begin, size_t end TSRMLS_DC)
{
	long len = end - begin, chunk_size = 40960;
	
	switch (mode)
	{
		case SEND_RSRC:
		{
			php_stream *s = (php_stream *) data;

			if (SUCCESS == php_stream_seek(s, begin, SEEK_SET)) {
				char *buf = emalloc(chunk_size);

				while (HTTP_CHUNK_AVAIL(len, chunk_size)) {
					http_send_response_data_plain(buffer, buf, php_stream_read(s, buf, chunk_size));
				}
				/* read & write left over */
				if (len) {
					http_send_response_data_plain(buffer, buf, php_stream_read(s, buf, chunk_size + len));
				}
	
				efree(buf);
			}
		}
		break;

		case SEND_DATA:
		{
			char *s = (char *) data + begin;

			while (HTTP_CHUNK_AVAIL(len, chunk_size)) {
				http_send_response_data_plain(buffer, s, chunk_size);
				s += chunk_size;
			}
			/* write left over */
			if (len) {
				http_send_response_data_plain(buffer, s, chunk_size + len);
			}
		}
		break;

		EMPTY_SWITCH_DEFAULT_CASE();
	}
}
/* }}} */

/* {{{ http_send_response_finish */
#define http_send_response_finish(b) _http_send_response_finish((b) TSRMLS_CC)
static inline void _http_send_response_finish(void **buffer TSRMLS_DC)
{
	if (HTTP_G(send).gzip_encoding) {
#ifdef HTTP_HAVE_ZLIB
		char *encoded = NULL;
		size_t encoded_len = 0;
		http_encoding_stream *s = *((http_encoding_stream **) buffer);
		
		http_encoding_stream_finish(s, &encoded, &encoded_len);
		phpstr_chunked_output(&s->storage, encoded, encoded_len, 0, _http_flush TSRMLS_CC);
		STR_FREE(encoded);
		efree(s);
#else
		http_error(HE_ERROR, HTTP_E_RESPONSE, "Attempt to send GZIP response despite being able to do so; please report this bug");
#endif
	} else {
		phpstr_chunked_output((phpstr **) buffer, NULL, 0, 0, _http_flush TSRMLS_CC);
	}
}
/* }}} */


/* {{{ STATUS http_send_header(char *, char *, zend_bool) */
PHP_HTTP_API STATUS _http_send_header_ex(const char *name, size_t name_len, const char *value, size_t value_len, zend_bool replace, char **sent_header TSRMLS_DC)
{
	STATUS ret;
	size_t header_len = sizeof(": ") + name_len + value_len + 1;
	char *header = emalloc(header_len + 1);

	header[header_len] = '\0';
	header_len = snprintf(header, header_len, "%s: %s", name, value);
	ret = http_send_header_string_ex(header, header_len, replace);
	if (sent_header) {
		*sent_header = header;
	} else {
		efree(header);
	}
	return ret;
}
/* }}} */

/* {{{ STATUS http_send_status_header(int, char *) */
PHP_HTTP_API STATUS _http_send_status_header_ex(int status, const char *header, size_t header_len, zend_bool replace TSRMLS_DC)
{
	STATUS ret;
	sapi_header_line h = {(char *) header, header_len, status};
	if (SUCCESS != (ret = sapi_header_op(replace ? SAPI_HEADER_REPLACE : SAPI_HEADER_ADD, &h TSRMLS_CC))) {
		http_error_ex(HE_WARNING, HTTP_E_HEADER, "Could not send header: %s (%d)", header, status);
	}
	return ret;
}
/* }}} */

/* {{{ STATUS http_send_last_modified(int) */
PHP_HTTP_API STATUS _http_send_last_modified_ex(time_t t, char **sent_header TSRMLS_DC)
{
	STATUS ret;
	char *date = http_date(t);

	if (!date) {
		return FAILURE;
	}

	ret = http_send_header_ex("Last-Modified", lenof("Last-Modified"), date, strlen(date), 1, sent_header);
	efree(date);

	/* remember */
	HTTP_G(send).last_modified = t;

	return ret;
}
/* }}} */

/* {{{ STATUS http_send_etag(char *, size_t) */
PHP_HTTP_API STATUS _http_send_etag_ex(const char *etag, size_t etag_len, char **sent_header TSRMLS_DC)
{
	STATUS status;
	char *etag_header;

	if (!etag_len){
		http_error_ex(HE_WARNING, HTTP_E_HEADER, "Attempt to send empty ETag (previous: %s)\n", HTTP_G(send).unquoted_etag);
		return FAILURE;
	}

	/* remember */
	STR_SET(HTTP_G(send).unquoted_etag, estrndup(etag, etag_len));

	etag_len = spprintf(&etag_header, 0, "ETag: \"%s\"", etag);
	status = http_send_header_string_ex(etag_header, etag_len, 1);
	
	if (sent_header) {
		*sent_header = etag_header;
	} else {
		efree(etag_header);
	}
	
	return status;
}
/* }}} */

/* {{{ STATUS http_send_content_type(char *, size_t) */
PHP_HTTP_API STATUS _http_send_content_type(const char *content_type, size_t ct_len TSRMLS_DC)
{
	HTTP_CHECK_CONTENT_TYPE(content_type, return FAILURE);

	/* remember for multiple ranges */
	STR_FREE(HTTP_G(send).content_type);
	HTTP_G(send).content_type = estrndup(content_type, ct_len);

	return http_send_header_ex("Content-Type", lenof("Content-Type"), content_type, ct_len, 1, NULL);
}
/* }}} */

/* {{{ STATUS http_send_content_disposition(char *, size_t, zend_bool) */
PHP_HTTP_API STATUS _http_send_content_disposition(const char *filename, size_t f_len, zend_bool send_inline TSRMLS_DC)
{
	STATUS status;
	char *cd_header;

	if (send_inline) {
		cd_header = ecalloc(1, sizeof("Content-Disposition: inline; filename=\"\"") + f_len);
		sprintf(cd_header, "Content-Disposition: inline; filename=\"%s\"", filename);
	} else {
		cd_header = ecalloc(1, sizeof("Content-Disposition: attachment; filename=\"\"") + f_len);
		sprintf(cd_header, "Content-Disposition: attachment; filename=\"%s\"", filename);
	}

	status = http_send_header_string(cd_header);
	efree(cd_header);
	return status;
}
/* }}} */

/* {{{ STATUS http_send(void *, size_t, http_send_mode) */
PHP_HTTP_API STATUS _http_send_ex(const void *data_ptr, size_t data_size, http_send_mode data_mode, zend_bool no_cache TSRMLS_DC)
{
	void *s = NULL;
	HashTable ranges;
	http_range_status range_status;
	int cache_etag = http_interrupt_ob_etaghandler();
	
	if (!data_ptr) {
		return FAILURE;
	}
	if (!data_size) {
		return SUCCESS;
	}
	
	/* enable partial dl and resume */
	http_send_header_string("Accept-Ranges: bytes");

	zend_hash_init(&ranges, 0, NULL, ZVAL_PTR_DTOR, 0);
	range_status = http_get_request_ranges(&ranges, data_size);

	if (range_status == RANGE_ERR) {
		zend_hash_destroy(&ranges);
		http_send_status(416);
		return FAILURE;
	}
	
	switch (range_status)
	{
		case RANGE_OK:
		{
			/* Range Request - only send ranges if entity hasn't changed */
			if (	http_match_etag_ex("HTTP_IF_MATCH", HTTP_G(send).unquoted_etag, 0) &&
					http_match_last_modified_ex("HTTP_IF_UNMODIFIED_SINCE", HTTP_G(send).last_modified, 0) &&
					http_match_last_modified_ex("HTTP_UNLESS_MODIFIED_SINCE", HTTP_G(send).last_modified, 0)) {
				
				if (zend_hash_num_elements(&ranges) == 1) {
					/* single range */
					zval **range, **begin, **end;
					
					if (	SUCCESS == zend_hash_index_find(&ranges, 0, (void **) &range) &&
							SUCCESS == zend_hash_index_find(Z_ARRVAL_PP(range), 0, (void **) &begin) &&
							SUCCESS == zend_hash_index_find(Z_ARRVAL_PP(range), 1, (void **) &end)) {
						char range_header_str[256];
						size_t range_header_len;
						
						range_header_len = snprintf(range_header_str, lenof(range_header_str), "Content-Range: bytes %ld-%ld/%lu", Z_LVAL_PP(begin), Z_LVAL_PP(end), (ulong) data_size);
						http_send_status_header_ex(206, range_header_str, range_header_len, 1);
						http_send_response_start(&s, Z_LVAL_PP(end)-Z_LVAL_PP(begin)+1);
						http_send_response_data_fetch(&s, data_ptr, data_size, data_mode, Z_LVAL_PP(begin), Z_LVAL_PP(end) + 1);
						http_send_response_finish(&s);
						zend_hash_destroy(&ranges);
						return SUCCESS;
					}
				} else {
					/* multi range */
					zval **range, **begin, **end;
					const char *content_type = HTTP_G(send).content_type;
					char boundary_str[32], range_header_str[256];
					size_t boundary_len, range_header_len;
					
					boundary_len = snprintf(boundary_str, lenof(boundary_str), "%lu%0.9f", (ulong) time(NULL), (float) php_combined_lcg(TSRMLS_C));
					range_header_len = snprintf(range_header_str, lenof(range_header_str), "Content-Type: multipart/byteranges; boundary=%s", boundary_str);
					
					http_send_status_header_ex(206, range_header_str, range_header_len, 1);
					http_send_response_start(&s, 0);
					
					if (!content_type) {
						content_type = "application/x-octetstream";
					}
					
					FOREACH_HASH_VAL(&ranges, range) {
						if (	SUCCESS == zend_hash_index_find(Z_ARRVAL_PP(range), 0, (void **) &begin) &&
								SUCCESS == zend_hash_index_find(Z_ARRVAL_PP(range), 1, (void **) &end)) {
							char preface_str[512];
							size_t preface_len;

#define HTTP_RANGE_PREFACE \
	HTTP_CRLF "--%s" \
	HTTP_CRLF "Content-Type: %s" \
	HTTP_CRLF "Content-Range: bytes %ld-%ld/%lu" \
	HTTP_CRLF HTTP_CRLF
							
							preface_len = snprintf(preface_str, lenof(preface_str), HTTP_RANGE_PREFACE, boundary_str, content_type, Z_LVAL_PP(begin), Z_LVAL_PP(end), data_size);
							http_send_response_data_plain(&s, preface_str, preface_len);
							http_send_response_data_fetch(&s, data_ptr, data_size, data_mode, Z_LVAL_PP(begin), Z_LVAL_PP(end) + 1);
						}
					}
					
					http_send_response_data_plain(&s, HTTP_CRLF "--", lenof(HTTP_CRLF "--"));
					http_send_response_data_plain(&s, boundary_str, boundary_len);
					http_send_response_data_plain(&s, "--", lenof("--"));
					
					http_send_response_finish(&s);
					zend_hash_destroy(&ranges);
					return SUCCESS;
				}
			}
		}
		case RANGE_NO:
		{
			zend_hash_destroy(&ranges);
			
			/* send 304 Not Modified if etag matches - DON'T return on ETag generation failure */
			if (!no_cache && cache_etag) {
				char *etag = NULL;
				
				if (etag = http_etag(data_ptr, data_size, data_mode)) {
					char *sent_header = NULL;
					
					http_send_etag_ex(etag, strlen(etag), &sent_header);
					if (http_match_etag("HTTP_IF_NONE_MATCH", etag)) {
						return http_exit_ex(304, sent_header, NULL, 0);
					} else {
						STR_FREE(sent_header);
					}
					efree(etag);
				}
			}
		
			/* send 304 Not Modified if last modified matches */
			if (!no_cache && http_match_last_modified("HTTP_IF_MODIFIED_SINCE", HTTP_G(send).last_modified)) {
				char *sent_header = NULL;
				http_send_last_modified_ex(HTTP_G(send).last_modified, &sent_header);
				return http_exit_ex(304, sent_header, NULL, 0);
			}
			
			/* send full response */
			http_send_response_start(&s, data_size);
			http_send_response_data_fetch(&s, data_ptr, data_size, data_mode, 0, data_size);
			http_send_response_finish(&s);
			return SUCCESS;
		}
	}
	return FAILURE;
}
/* }}} */

/* {{{ STATUS http_send_stream(php_stream *) */
PHP_HTTP_API STATUS _http_send_stream_ex(php_stream *file, zend_bool close_stream, zend_bool no_cache TSRMLS_DC)
{
	STATUS status;
	php_stream_statbuf ssb;

	if ((!file) || php_stream_stat(file, &ssb)) {
		return FAILURE;
	}

	status = http_send_ex(file, ssb.sb.st_size, SEND_RSRC, no_cache);

	if (close_stream) {
		php_stream_close(file);
	}

	return status;
}
/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */

