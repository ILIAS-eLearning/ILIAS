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

#ifndef PHP_HTTP_CACHE_API_H
#define PHP_HTTP_CACHE_API_H

#include "zend_ini.h"

#include "ext/standard/md5.h"
#include "ext/standard/sha1.h"
#include "ext/standard/crc32.h"

#include "php_http_std_defs.h"
#include "php_http.h"
#include "php_http_api.h"
#include "php_http_send_api.h"

#ifdef HTTP_HAVE_MHASH
#	include <mhash.h>
#endif

ZEND_EXTERN_MODULE_GLOBALS(http);

typedef enum {
	HTTP_ETAG_CRC32 = -3,
	HTTP_ETAG_MD5 = -2,
	HTTP_ETAG_SHA1 = -1,
} http_etag_mode;

extern PHP_MINIT_FUNCTION(http_cache);

#ifdef HTTP_HAVE_MHASH
static void *http_etag_alloc_mhash_digest(size_t size)
{
	return emalloc(size);
}
#endif

#define http_etag_digest(d, l) _http_etag_digest((d), (l) TSRMLS_CC)
static inline char *_http_etag_digest(const unsigned char *digest, int len TSRMLS_DC)
{
	int i;
	char *hex = emalloc(len * 2 + 1);
	char *ptr = hex;
	
	/* optimize this --
		look at apache's make_etag */
	for (i = 0; i < len; ++i) {
		sprintf(ptr, "%02x", digest[i]);
		ptr += 2;
	}
	*ptr = '\0';
	
	return hex;
}

#define http_etag_init() _http_etag_init(TSRMLS_C)
static inline void *_http_etag_init(TSRMLS_D)
{
	void *ctx = NULL;
	long mode = HTTP_G(etag).mode;
	
	switch (mode)
	{
		case HTTP_ETAG_CRC32:
			ctx = emalloc(sizeof(uint));
			*((uint *) ctx) = ~0;
		break;
		
		case HTTP_ETAG_SHA1:
			PHP_SHA1Init(ctx = emalloc(sizeof(PHP_SHA1_CTX)));
		break;
		
		case HTTP_ETAG_MD5:
#ifndef HTTP_HAVE_MHASH
		default:
#endif
			PHP_MD5Init(ctx = emalloc(sizeof(PHP_MD5_CTX)));
		break;
		
#ifdef HTTP_HAVE_MHASH
		default:
			if ((mode < 0) || ((ulong)mode > mhash_count()) || (!(ctx = mhash_init(mode)))) {
				http_error_ex(HE_ERROR, HTTP_E_RUNTIME, "Invalid ETag mode: %ld", mode);
			}
		break;
#endif
	}
	
	return ctx;
}

#define http_etag_free(cp) _http_etag_free((cp) TSRMLS_CC)
static inline void _http_etag_free(void **ctx_ptr TSRMLS_DC)
{
	long mode = HTTP_G(etag).mode;
	
	switch (mode)
	{
		case HTTP_ETAG_CRC32:
			if (*((uint **) ctx_ptr)) {
				efree(*((uint **) ctx_ptr));
				*((uint **) ctx_ptr) = NULL;
			}
		break;
		
		case HTTP_ETAG_SHA1:
			if (*((PHP_SHA1_CTX **) ctx_ptr)) {
				efree(*((PHP_SHA1_CTX **) ctx_ptr));
				*((PHP_SHA1_CTX **) ctx_ptr) = NULL;
			}
		break;
		
		case HTTP_ETAG_MD5:
#ifndef HTTP_HAVE_MHASH
		default:
#endif
			if (*((PHP_MD5_CTX **) ctx_ptr)) {
				efree(*((PHP_MD5_CTX **) ctx_ptr));
				*((PHP_MD5_CTX **) ctx_ptr) = NULL;
			}
		break;
		
#ifdef HTTP_HAVE_MHASH
		default:
			/* mhash gets already freed in http_etag_finish() */
			if (*((MHASH *) ctx_ptr)) {
				mhash_deinit(*((MHASH *) ctx_ptr), NULL);
				*((MHASH *) ctx_ptr) = NULL;
			}
		break;
#endif
	}
}

#define http_etag_finish(c) _http_etag_finish((c) TSRMLS_CC)
static inline char *_http_etag_finish(void **ctx_ptr TSRMLS_DC)
{
	char *etag = NULL;
	unsigned char digest[20];
	long mode = HTTP_G(etag).mode;
	
	switch (mode)
	{
		case HTTP_ETAG_CRC32:
			**((uint **) ctx_ptr) = ~**((uint **) ctx_ptr);
			etag = http_etag_digest(*((const unsigned char **) ctx_ptr), sizeof(uint));
		break;
		
		case HTTP_ETAG_SHA1:
			PHP_SHA1Final(digest, *((PHP_SHA1_CTX **) ctx_ptr));
			etag = http_etag_digest(digest, 20);
		break;
		
		case HTTP_ETAG_MD5:
#ifndef HTTP_HAVE_MHASH
		default:
#endif
			PHP_MD5Final(digest, *((PHP_MD5_CTX **) ctx_ptr));
			etag = http_etag_digest(digest, 16);
		break;
		
#ifdef HTTP_HAVE_MHASH
		default:
		{
			unsigned char *mhash_digest = mhash_end_m(*((MHASH *) ctx_ptr), http_etag_alloc_mhash_digest);
			etag = http_etag_digest(mhash_digest, mhash_get_block_size(mode));
			efree(mhash_digest);
			/* avoid double free */
			*((MHASH *) ctx_ptr) = NULL;
		}
		break;
#endif
	}
	
	http_etag_free(ctx_ptr);
	
	return etag;
}

#define http_etag_update(c, d, l) _http_etag_update((c), (d), (l) TSRMLS_CC)
static inline void _http_etag_update(void *ctx, const char *data_ptr, size_t data_len TSRMLS_DC)
{
	switch (INI_INT("http.etag_mode"))
	{
		case HTTP_ETAG_CRC32:
		{
			uint i, c = *((uint *) ctx);
			
			for (i = 0; i < data_len; ++i) {
				c = CRC32(c, data_ptr[i]);
			}
			*((uint *)ctx) = c;
		}
		break;
		
		case HTTP_ETAG_SHA1:
			PHP_SHA1Update(ctx, (const unsigned char *) data_ptr, data_len);
		break;
		
		case HTTP_ETAG_MD5:
#ifndef HTTP_HAVE_MHASH
		default:
#endif
			PHP_MD5Update(ctx, (const unsigned char *) data_ptr, data_len);
		break;
		
#ifdef HTTP_HAVE_MHASH
		default:
			mhash(ctx, data_ptr, data_len);
		break;
#endif
	}
}

#define http_ob_etaghandler(o, l, ho, hl, m) _http_ob_etaghandler((o), (l), (ho), (hl), (m) TSRMLS_CC)
extern void _http_ob_etaghandler(char *output, uint output_len, char **handled_output, uint *handled_output_len, int mode TSRMLS_DC);

#define http_etag(p, l, m) _http_etag((p), (l), (m) TSRMLS_CC)
PHP_HTTP_API char *_http_etag(const void *data_ptr, size_t data_len, http_send_mode data_mode TSRMLS_DC);

#define http_last_modified(p, m) _http_last_modified((p), (m) TSRMLS_CC)
PHP_HTTP_API time_t _http_last_modified(const void *data_ptr, http_send_mode data_mode TSRMLS_DC);

#define http_match_last_modified(entry, modified) _http_match_last_modified_ex((entry), (modified), 1 TSRMLS_CC)
#define http_match_last_modified_ex(entry, modified, ep) _http_match_last_modified_ex((entry), (modified), (ep) TSRMLS_CC)
PHP_HTTP_API zend_bool _http_match_last_modified_ex(const char *entry, time_t t, zend_bool enforce_presence TSRMLS_DC);

#define http_match_etag(entry, etag) _http_match_etag_ex((entry), (etag), 1 TSRMLS_CC)
#define http_match_etag_ex(entry, etag, ep) _http_match_etag_ex((entry), (etag), (ep) TSRMLS_CC)
PHP_HTTP_API zend_bool _http_match_etag_ex(const char *entry, const char *etag, zend_bool enforce_presence TSRMLS_DC);

#define http_cache_last_modified(l, s, cc, ccl) _http_cache_last_modified((l), (s), (cc), (ccl) TSRMLS_CC)
PHP_HTTP_API STATUS _http_cache_last_modified(time_t last_modified, time_t send_modified, const char *cache_control, size_t cc_len TSRMLS_DC);

#define http_cache_etag(e, el, cc, ccl) _http_cache_etag((e), (el), (cc), (ccl) TSRMLS_CC)
PHP_HTTP_API STATUS _http_cache_etag(const char *etag, size_t etag_len, const char *cache_control, size_t cc_len TSRMLS_DC);

#define http_start_ob_etaghandler() _http_start_ob_etaghandler(TSRMLS_C)
PHP_HTTP_API STATUS _http_start_ob_etaghandler(TSRMLS_D);
#define http_interrupt_ob_etaghandler() _http_interrupt_ob_etaghandler(TSRMLS_C)
PHP_HTTP_API zend_bool _http_interrupt_ob_etaghandler(TSRMLS_D);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

