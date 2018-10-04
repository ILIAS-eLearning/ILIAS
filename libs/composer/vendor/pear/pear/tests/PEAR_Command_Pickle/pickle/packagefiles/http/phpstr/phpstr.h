
/* $Id$ */

#ifndef _PHPSTR_H_
#define _PHPSTR_H_

#include "php.h"

#ifndef PHPSTR_DEFAULT_SIZE
#	define PHPSTR_DEFAULT_SIZE 256
#endif

#ifndef STR_SET
#	define STR_SET(STR, SET) \
	{ \
		STR_FREE(STR); \
		STR = SET; \
	}
#endif

#if defined(PHP_WIN32)
#	if defined(PHPSTR_EXPORTS)
#		define PHPSTR_API __declspec(dllexport)
#	elif defined(COMPILE_DL_PHPSTR)
#		define PHPSTR_API __declspec(dllimport)
#	else
#		define PHPSTR_API
#	endif
#else
#	define PHPSTR_API
#endif

#define PHPSTR(p) ((phpstr *) (p))
#define PHPSTR_VAL(p) (PHPSTR(p))->data
#define PHPSTR_LEN(p) (PHPSTR(p))->used

#define FREE_PHPSTR_PTR(STR) efree(STR)
#define FREE_PHPSTR_VAL(STR) phpstr_dtor(STR)
#define FREE_PHPSTR_ALL(STR) phpstr_free(&(STR))
#define FREE_PHPSTR(free, STR) \
	switch (free) \
	{ \
		case PHPSTR_FREE_NOT:						break; \
		case PHPSTR_FREE_PTR:	efree(STR);			break; \
		case PHPSTR_FREE_VAL:	phpstr_dtor(STR);	break; \
		case PHPSTR_FREE_ALL: \
		{ \
			phpstr *PTR = (STR); \
			phpstr_free(&PTR); \
		} \
		break; \
		default:									break; \
	}

#define RETURN_PHPSTR_PTR(STR) RETURN_PHPSTR((STR), PHPSTR_FREE_PTR, 0)
#define RETURN_PHPSTR_VAL(STR) RETURN_PHPSTR((STR), PHPSTR_FREE_NOT, 0)
#define RETVAL_PHPSTR_PTR(STR) RETVAL_PHPSTR((STR), PHPSTR_FREE_PTR, 0)
#define RETVAL_PHPSTR_VAL(STR) RETVAL_PHPSTR((STR), PHPSTR_FREE_NOT, 0)
/* RETURN_PHPSTR(buf, PHPSTR_FREE_PTR, 0) */
#define RETURN_PHPSTR(STR, free, dup) \
	RETVAL_PHPSTR((STR), (free), (dup)); \
	return;

#define RETVAL_PHPSTR(STR, free, dup) \
	phpstr_fix(STR); \
	RETVAL_STRINGL((STR)->data, (STR)->used, (dup)); \
	FREE_PHPSTR((free), (STR));

typedef struct {
	size_t size;
	char  *data;
	size_t used;
	size_t free;
} phpstr;

typedef enum {
	PHPSTR_FREE_NOT = 0,
	PHPSTR_FREE_PTR,	/* efree() */
	PHPSTR_FREE_VAL,	/* phpstr_dtor() */
	PHPSTR_FREE_ALL		/* phpstr_free() */
} phpstr_free_t;

#define PHPSTR_ALL_FREE(STR) PHPSTR_FREE_ALL,(STR)
#define PHPSTR_PTR_FREE(STR) PHPSTR_FREE_PTR,(STR)
#define PHPSTR_VAL_FREE(STR) PHPSTR_FREE_VAL,(STR)
#define PHPSTR_NOT_FREE(STR) PHPSTR_FREE_NOT,(STR)

/* create a new phpstr */
#define phpstr_new() phpstr_init(NULL)
#define phpstr_init(b) phpstr_init_ex(b, 0, 0)
#define phpstr_clone(phpstr_pointer) phpstr_init_ex(NULL, (phpstr_pointer)->size, 0)
PHPSTR_API phpstr *phpstr_init_ex(phpstr *buf, size_t chunk_size, int pre_alloc);

/* create a phpstr from a zval or c-string */
#define phpstr_from_zval(z) phpstr_from_string(Z_STRVAL(z), Z_STRLEN(z))
#define phpstr_from_zval_ex(b, z) phpstr_from_string_ex(b, Z_STRVAL(z), Z_STRLEN(z))
#define phpstr_from_string(s, l) phpstr_from_string_ex(NULL, (s), (l))
PHPSTR_API phpstr *phpstr_from_string_ex(phpstr *buf, const char *string, size_t length);

/* usually only called from within the internal functions */
#define phpstr_resize(b, s) phpstr_resize_ex((b), (s), 0)
PHPSTR_API void phpstr_resize_ex(phpstr *buf, size_t len, size_t override_size);

/* append data to the phpstr */
#define phpstr_appends(b, a) phpstr_append((b), (a), sizeof(a)-1)
#define phpstr_appendl(b, a) phpstr_append((b), (a), strlen(a))
PHPSTR_API size_t phpstr_append(phpstr *buf, const char *append, size_t append_len);
PHPSTR_API size_t phpstr_appendf(phpstr *buf, const char *format, ...) PHP_ATTRIBUTE_FORMAT(printf, 2, 3);

/* insert data at a specific position of the phpstr */
#define phpstr_inserts(b, i, o) phpstr_insert((b), (i), sizeof(i)-1, (o))
#define phpstr_insertl(b, i, o) phpstr_insert((b), (i), strlen(i), (o))
PHPSTR_API size_t phpstr_insert(phpstr *buf, const char *insert, size_t insert_len, size_t offset);
PHPSTR_API size_t phpstr_insertf(phpstr *buf, size_t offset, const char *format, ...) PHP_ATTRIBUTE_FORMAT(printf, 3, 4);

/* prepend data */
#define phpstr_prepends(b, p) phpstr_prepend((b), (p), sizeof(p)-1)
#define phpstr_prependl(b, p) phpstr_prepend((b), (p), strlen(p))
PHPSTR_API size_t phpstr_prepend(phpstr *buf, const char *prepend, size_t prepend_len);
PHPSTR_API size_t phpstr_prependf(phpstr *buf, const char *format, ...) PHP_ATTRIBUTE_FORMAT(printf, 2, 3);

/* get a zero-terminated string */
PHPSTR_API char *phpstr_data(const phpstr *buf, char **into, size_t *len);

/* get a part of the phpstr */
#define phpstr_mid(b, o, l) phpstr_sub((b), (o), (l))
#define phpstr_left(b, l) phpstr_sub((b), 0, (l))
PHPSTR_API phpstr *phpstr_right(const phpstr *buf, size_t length);
PHPSTR_API phpstr *phpstr_sub(const phpstr *buf, size_t offset, size_t len);

/* remove a substring */
PHPSTR_API size_t phpstr_cut(phpstr *buf, size_t offset, size_t length);

/* get a complete phpstr duplicate */
PHPSTR_API phpstr *phpstr_dup(const phpstr *buf);

/* merge several phpstr objects
   use like:

	phpstr *final = phpstr_merge(3,
		PHPSTR_NOT_FREE(&keep),
		PHPSTR_ALL_FREE(middle_ptr),
		PHPSTR_VAL_FREE(&local);
*/
PHPSTR_API phpstr *phpstr_merge(unsigned argc, ...);
PHPSTR_API phpstr *phpstr_merge_ex(phpstr *buf, unsigned argc, ...);
PHPSTR_API phpstr *phpstr_merge_va(phpstr *buf, unsigned argc, va_list argv);

/* sets a trailing NUL byte */
PHPSTR_API void phpstr_fix(phpstr *buf);

/* free a phpstr objects contents */
PHPSTR_API void phpstr_dtor(phpstr *buf);

/* free a phpstr object completely */
PHPSTR_API void phpstr_free(phpstr **buf);

/* stores data in a phpstr until it reaches chunk_size */
PHPSTR_API size_t phpstr_chunk_buffer(phpstr **s, const char *data, size_t data_len, char **chunk, size_t chunk_size);

/* wrapper around phpstr_chunk_buffer, which passes available chunks to passthru() */
PHPSTR_API void phpstr_chunked_output(phpstr **s, const char *data, size_t data_len, size_t chunk_size, void (*passthru)(const char *, size_t TSRMLS_DC) TSRMLS_DC);

#endif


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
