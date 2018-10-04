/*
** 2001 September 15
**
** The author disclaims copyright to this source code.  In place of
** a legal notice, here is a blessing:
**
**    May you do good and not evil.
**    May you find forgiveness for yourself and forgive others.
**    May you share freely, never taking more than you give.
**
*************************************************************************
** Internal interface definitions for SQLite.
**
** @(#) $Id$
*/
#include "config.h"
#include "sqlite.h"
#include "hash.h"
#include "parse.h"
#include "btree.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <assert.h>

/*
** The maximum number of in-memory pages to use for the main database
** table and for temporary tables.
*/
#define MAX_PAGES   2000
#define TEMP_PAGES   500

/*
** If the following macro is set to 1, then NULL values are considered
** distinct for the SELECT DISTINCT statement and for UNION or EXCEPT
** compound queries.  No other SQL database engine (among those tested) 
** works this way except for OCELOT.  But the SQL92 spec implies that
** this is how things should work.
**
** If the following macro is set to 0, then NULLs are indistinct for
** SELECT DISTINCT and for UNION.
*/
#define NULL_ALWAYS_DISTINCT 0

/*
** If the following macro is set to 1, then NULL values are considered
** distinct when determining whether or not two entries are the same
** in a UNIQUE index.  This is the way PostgreSQL, Oracle, DB2, MySQL,
** OCELOT, and Firebird all work.  The SQL92 spec explicitly says this
** is the way things are suppose to work.
**
** If the following macro is set to 0, the NULLs are indistinct for
** a UNIQUE index.  In this mode, you can only have a single NULL entry
** for a column declared UNIQUE.  This is the way Informix and SQL Server
** work.
*/
#define NULL_DISTINCT_FOR_UNIQUE 1

/*
** The maximum number of attached databases.  This must be at least 2
** in order to support the main database file (0) and the file used to
** hold temporary tables (1).  And it must be less than 256 because
** an unsigned character is used to stored the database index.
*/
#define MAX_ATTACHED 10

/*
** The next macro is used to determine where TEMP tables and indices
** are stored.  Possible values:
**
**   0    Always use a temporary files
**   1    Use a file unless overridden by "PRAGMA temp_store"
**   2    Use memory unless overridden by "PRAGMA temp_store"
**   3    Always use memory
*/
#ifndef TEMP_STORE
# define TEMP_STORE 1
#endif

/*
** When building SQLite for embedded systems where memory is scarce,
** you can define one or more of the following macros to omit extra
** features of the library and thus keep the size of the library to
** a minimum.
*/
/* #define SQLITE_OMIT_AUTHORIZATION  1 */
/* #define SQLITE_OMIT_INMEMORYDB     1 */
/* #define SQLITE_OMIT_VACUUM         1 */
/* #define SQLITE_OMIT_DATETIME_FUNCS 1 */
/* #define SQLITE_OMIT_PROGRESS_CALLBACK 1 */

/*
** Integers of known sizes.  These typedefs might change for architectures
** where the sizes very.  Preprocessor macros are available so that the
** types can be conveniently redefined at compile-type.  Like this:
**
**         cc '-DUINTPTR_TYPE=long long int' ...
*/
#ifndef UINT32_TYPE
# define UINT32_TYPE unsigned int
#endif
#ifndef UINT16_TYPE
# define UINT16_TYPE unsigned short int
#endif
#ifndef UINT8_TYPE
# define UINT8_TYPE unsigned char
#endif
#ifndef INT8_TYPE
# define INT8_TYPE signed char
#endif
#ifndef INTPTR_TYPE
# if SQLITE_PTR_SZ==4
#   define INTPTR_TYPE int
# else
#   define INTPTR_TYPE long long
# endif
#endif
typedef UINT32_TYPE u32;           /* 4-byte unsigned integer */
typedef UINT16_TYPE u16;           /* 2-byte unsigned integer */
typedef UINT8_TYPE u8;             /* 1-byte unsigned integer */
typedef UINT8_TYPE i8;             /* 1-byte signed integer */
typedef INTPTR_TYPE ptr;           /* Big enough to hold a pointer */
typedef unsigned INTPTR_TYPE uptr; /* Big enough to hold a pointer */

/*
** Defer sourcing vdbe.h until after the "u8" typedef is defined.
*/
#include "vdbe.h"

/*
** Most C compilers these days recognize "long double", don't they?
** Just in case we encounter one that does not, we will create a macro
** for long double so that it can be easily changed to just "double".
*/
#ifndef LONGDOUBLE_TYPE
# define LONGDOUBLE_TYPE long double
#endif

/*
** This macro casts a pointer to an integer.  Useful for doing
** pointer arithmetic.
*/
#define Addr(X)  ((uptr)X)

/*
** The maximum number of bytes of data that can be put into a single
** row of a single table.  The upper bound on this limit is 16777215
** bytes (or 16MB-1).  We have arbitrarily set the limit to just 1MB
** here because the overflow page chain is inefficient for really big
** records and we want to discourage people from thinking that 
** multi-megabyte records are OK.  If your needs are different, you can
** change this define and recompile to increase or decrease the record
** size.
**
** The 16777198 is computed as follows:  238 bytes of payload on the
** original pages plus 16448 overflow pages each holding 1020 bytes of
** data.
*/
#define MAX_BYTES_PER_ROW  1048576
/* #define MAX_BYTES_PER_ROW 16777198 */

/*
** If memory allocation problems are found, recompile with
**
**      -DMEMORY_DEBUG=1
**
** to enable some sanity checking on malloc() and free().  To
** check for memory leaks, recompile with
**
**      -DMEMORY_DEBUG=2
**
** and a line of text will be written to standard error for
** each malloc() and free().  This output can be analyzed
** by an AWK script to determine if there are any leaks.
*/
#ifdef MEMORY_DEBUG
# define sqliteMalloc(X)    sqliteMalloc_(X,1,__FILE__,__LINE__)
# define sqliteMallocRaw(X) sqliteMalloc_(X,0,__FILE__,__LINE__)
# define sqliteFree(X)      sqliteFree_(X,__FILE__,__LINE__)
# define sqliteRealloc(X,Y) sqliteRealloc_(X,Y,__FILE__,__LINE__)
# define sqliteStrDup(X)    sqliteStrDup_(X,__FILE__,__LINE__)
# define sqliteStrNDup(X,Y) sqliteStrNDup_(X,Y,__FILE__,__LINE__)
  void sqliteStrRealloc(char**);
#else
# define sqliteRealloc_(X,Y) sqliteRealloc(X,Y)
# define sqliteStrRealloc(X)
#endif

/*
** This variable gets set if malloc() ever fails.  After it gets set,
** the SQLite library shuts down permanently.
*/
extern int sqlite_malloc_failed;

/*
** The following global variables are used for testing and debugging
** only.  They only work if MEMORY_DEBUG is defined.
*/
#ifdef MEMORY_DEBUG
extern int sqlite_nMalloc;       /* Number of sqliteMalloc() calls */
extern int sqlite_nFree;         /* Number of sqliteFree() calls */
extern int sqlite_iMallocFail;   /* Fail sqliteMalloc() after this many calls */
#endif

/*
** Name of the master database table.  The master database table
** is a special table that holds the names and attributes of all
** user tables and indices.
*/
#define MASTER_NAME       "sqlite_master"
#define TEMP_MASTER_NAME  "sqlite_temp_master"

/*
** The name of the schema table.
*/
#define SCHEMA_TABLE(x)  (x?TEMP_MASTER_NAME:MASTER_NAME)

/*
** A convenience macro that returns the number of elements in
** an array.
*/
#define ArraySize(X)    (sizeof(X)/sizeof(X[0]))

/*
** Forward references to structures
*/
typedef struct Column Column;
typedef struct Table Table;
typedef struct Index Index;
typedef struct Instruction Instruction;
typedef struct Expr Expr;
typedef struct ExprList ExprList;
typedef struct Parse Parse;
typedef struct Token Token;
typedef struct IdList IdList;
typedef struct SrcList SrcList;
typedef struct WhereInfo WhereInfo;
typedef struct WhereLevel WhereLevel;
typedef struct Select Select;
typedef struct AggExpr AggExpr;
typedef struct FuncDef FuncDef;
typedef struct Trigger Trigger;
typedef struct TriggerStep TriggerStep;
typedef struct TriggerStack TriggerStack;
typedef struct FKey FKey;
typedef struct Db Db;
typedef struct AuthContext AuthContext;

/*
** Each database file to be accessed by the system is an instance
** of the following structure.  There are normally two of these structures
** in the sqlite.aDb[] array.  aDb[0] is the main database file and
** aDb[1] is the database file used to hold temporary tables.  Additional
** databases may be attached.
*/
struct Db {
  char *zName;         /* Name of this database */
  Btree *pBt;          /* The B*Tree structure for this database file */
  int schema_cookie;   /* Database schema version number for this file */
  Hash tblHash;        /* All tables indexed by name */
  Hash idxHash;        /* All (named) indices indexed by name */
  Hash trigHash;       /* All triggers indexed by name */
  Hash aFKey;          /* Foreign keys indexed by to-table */
  u8 inTrans;          /* 0: not writable.  1: Transaction.  2: Checkpoint */
  u16 flags;           /* Flags associated with this database */
  void *pAux;          /* Auxiliary data.  Usually NULL */
  void (*xFreeAux)(void*);  /* Routine to free pAux */
};

/*
** These macros can be used to test, set, or clear bits in the 
** Db.flags field.
*/
#define DbHasProperty(D,I,P)     (((D)->aDb[I].flags&(P))==(P))
#define DbHasAnyProperty(D,I,P)  (((D)->aDb[I].flags&(P))!=0)
#define DbSetProperty(D,I,P)     (D)->aDb[I].flags|=(P)
#define DbClearProperty(D,I,P)   (D)->aDb[I].flags&=~(P)

/*
** Allowed values for the DB.flags field.
**
** The DB_Locked flag is set when the first OP_Transaction or OP_Checkpoint
** opcode is emitted for a database.  This prevents multiple occurrences
** of those opcodes for the same database in the same program.  Similarly,
** the DB_Cookie flag is set when the OP_VerifyCookie opcode is emitted,
** and prevents duplicate OP_VerifyCookies from taking up space and slowing
** down execution.
**
** The DB_SchemaLoaded flag is set after the database schema has been
** read into internal hash tables.
**
** DB_UnresetViews means that one or more views have column names that
** have been filled out.  If the schema changes, these column names might
** changes and so the view will need to be reset.
*/
#define DB_Locked          0x0001  /* OP_Transaction opcode has been emitted */
#define DB_Cookie          0x0002  /* OP_VerifyCookie opcode has been emiited */
#define DB_SchemaLoaded    0x0004  /* The schema has been loaded */
#define DB_UnresetViews    0x0008  /* Some views have defined column names */


/*
** Each database is an instance of the following structure.
**
** The sqlite.file_format is initialized by the database file
** and helps determines how the data in the database file is
** represented.  This field allows newer versions of the library
** to read and write older databases.  The various file formats
** are as follows:
**
**     file_format==1    Version 2.1.0.
**     file_format==2    Version 2.2.0. Add support for INTEGER PRIMARY KEY.
**     file_format==3    Version 2.6.0. Fix empty-string index bug.
**     file_format==4    Version 2.7.0. Add support for separate numeric and
**                       text datatypes.
**
** The sqlite.temp_store determines where temporary database files
** are stored.  If 1, then a file is created to hold those tables.  If
** 2, then they are held in memory.  0 means use the default value in
** the TEMP_STORE macro.
**
** The sqlite.lastRowid records the last insert rowid generated by an
** insert statement.  Inserts on views do not affect its value.  Each
** trigger has its own context, so that lastRowid can be updated inside
** triggers as usual.  The previous value will be restored once the trigger
** exits.  Upon entering a before or instead of trigger, lastRowid is no
** longer (since after version 2.8.12) reset to -1.
**
** The sqlite.nChange does not count changes within triggers and keeps no
** context.  It is reset at start of sqlite_exec.
** The sqlite.lsChange represents the number of changes made by the last
** insert, update, or delete statement.  It remains constant throughout the
** length of a statement and is then updated by OP_SetCounts.  It keeps a
** context stack just like lastRowid so that the count of changes
** within a trigger is not seen outside the trigger.  Changes to views do not
** affect the value of lsChange.
** The sqlite.csChange keeps track of the number of current changes (since
** the last statement) and is used to update sqlite_lsChange.
*/
struct sqlite {
  int nDb;                      /* Number of backends currently in use */
  Db *aDb;                      /* All backends */
  Db aDbStatic[2];              /* Static space for the 2 default backends */
  int flags;                    /* Miscellanous flags. See below */
  u8 file_format;               /* What file format version is this database? */
  u8 safety_level;              /* How aggressive at synching data to disk */
  u8 want_to_close;             /* Close after all VDBEs are deallocated */
  u8 temp_store;                /* 1=file, 2=memory, 0=compile-time default */
  u8 onError;                   /* Default conflict algorithm */
  int next_cookie;              /* Next value of aDb[0].schema_cookie */
  int cache_size;               /* Number of pages to use in the cache */
  int nTable;                   /* Number of tables in the database */
  void *pBusyArg;               /* 1st Argument to the busy callback */
  int (*xBusyCallback)(void *,const char*,int);  /* The busy callback */
  void *pCommitArg;             /* Argument to xCommitCallback() */   
  int (*xCommitCallback)(void*);/* Invoked at every commit. */
  Hash aFunc;                   /* All functions that can be in SQL exprs */
  int lastRowid;                /* ROWID of most recent insert (see above) */
  int priorNewRowid;            /* Last randomly generated ROWID */
  int magic;                    /* Magic number for detect library misuse */
  int nChange;                  /* Number of rows changed (see above) */
  int lsChange;                 /* Last statement change count (see above) */
  int csChange;                 /* Current statement change count (see above) */
  struct sqliteInitInfo {       /* Information used during initialization */
    int iDb;                       /* When back is being initialized */
    int newTnum;                   /* Rootpage of table being initialized */
    u8 busy;                       /* TRUE if currently initializing */
  } init;
  struct Vdbe *pVdbe;           /* List of active virtual machines */
  void (*xTrace)(void*,const char*);     /* Trace function */
  void *pTraceArg;                       /* Argument to the trace function */
#ifndef SQLITE_OMIT_AUTHORIZATION
  int (*xAuth)(void*,int,const char*,const char*,const char*,const char*);
                                /* Access authorization function */
  void *pAuthArg;               /* 1st argument to the access auth function */
#endif
#ifndef SQLITE_OMIT_PROGRESS_CALLBACK
  int (*xProgress)(void *);     /* The progress callback */
  void *pProgressArg;           /* Argument to the progress callback */
  int nProgressOps;             /* Number of opcodes for progress callback */
#endif
};

/*
** Possible values for the sqlite.flags and or Db.flags fields.
**
** On sqlite.flags, the SQLITE_InTrans value means that we have
** executed a BEGIN.  On Db.flags, SQLITE_InTrans means a statement
** transaction is active on that particular database file.
*/
#define SQLITE_VdbeTrace      0x00000001  /* True to trace VDBE execution */
#define SQLITE_Initialized    0x00000002  /* True after initialization */
#define SQLITE_Interrupt      0x00000004  /* Cancel current operation */
#define SQLITE_InTrans        0x00000008  /* True if in a transaction */
#define SQLITE_InternChanges  0x00000010  /* Uncommitted Hash table changes */
#define SQLITE_FullColNames   0x00000020  /* Show full column names on SELECT */
#define SQLITE_ShortColNames  0x00000040  /* Show short columns names */
#define SQLITE_CountRows      0x00000080  /* Count rows changed by INSERT, */
                                          /*   DELETE, or UPDATE and return */
                                          /*   the count using a callback. */
#define SQLITE_NullCallback   0x00000100  /* Invoke the callback once if the */
                                          /*   result set is empty */
#define SQLITE_ReportTypes    0x00000200  /* Include information on datatypes */
                                          /*   in 4th argument of callback */

/*
** Possible values for the sqlite.magic field.
** The numbers are obtained at random and have no special meaning, other
** than being distinct from one another.
*/
#define SQLITE_MAGIC_OPEN     0xa029a697  /* Database is open */
#define SQLITE_MAGIC_CLOSED   0x9f3c2d33  /* Database is closed */
#define SQLITE_MAGIC_BUSY     0xf03b7906  /* Database currently in use */
#define SQLITE_MAGIC_ERROR    0xb5357930  /* An SQLITE_MISUSE error occurred */

/*
** Each SQL function is defined by an instance of the following
** structure.  A pointer to this structure is stored in the sqlite.aFunc
** hash table.  When multiple functions have the same name, the hash table
** points to a linked list of these structures.
*/
struct FuncDef {
  void (*xFunc)(sqlite_func*,int,const char**);  /* Regular function */
  void (*xStep)(sqlite_func*,int,const char**);  /* Aggregate function step */
  void (*xFinalize)(sqlite_func*);           /* Aggregate function finializer */
  signed char nArg;         /* Number of arguments.  -1 means unlimited */
  signed char dataType;     /* Arg that determines datatype.  -1=NUMERIC, */
                            /* -2=TEXT. -3=SQLITE_ARGS */
  u8 includeTypes;          /* Add datatypes to args of xFunc and xStep */
  void *pUserData;          /* User data parameter */
  FuncDef *pNext;           /* Next function with same name */
};

/*
** information about each column of an SQL table is held in an instance
** of this structure.
*/
struct Column {
  char *zName;     /* Name of this column */
  char *zDflt;     /* Default value of this column */
  char *zType;     /* Data type for this column */
  u8 notNull;      /* True if there is a NOT NULL constraint */
  u8 isPrimKey;    /* True if this column is part of the PRIMARY KEY */
  u8 sortOrder;    /* Some combination of SQLITE_SO_... values */
  u8 dottedName;   /* True if zName contains a "." character */
};

/*
** The allowed sort orders.
**
** The TEXT and NUM values use bits that do not overlap with DESC and ASC.
** That way the two can be combined into a single number.
*/
#define SQLITE_SO_UNK       0  /* Use the default collating type.  (SCT_NUM) */
#define SQLITE_SO_TEXT      2  /* Sort using memcmp() */
#define SQLITE_SO_NUM       4  /* Sort using sqliteCompare() */
#define SQLITE_SO_TYPEMASK  6  /* Mask to extract the collating sequence */
#define SQLITE_SO_ASC       0  /* Sort in ascending order */
#define SQLITE_SO_DESC      1  /* Sort in descending order */
#define SQLITE_SO_DIRMASK   1  /* Mask to extract the sort direction */

/*
** Each SQL table is represented in memory by an instance of the
** following structure.
**
** Table.zName is the name of the table.  The case of the original
** CREATE TABLE statement is stored, but case is not significant for
** comparisons.
**
** Table.nCol is the number of columns in this table.  Table.aCol is a
** pointer to an array of Column structures, one for each column.
**
** If the table has an INTEGER PRIMARY KEY, then Table.iPKey is the index of
** the column that is that key.   Otherwise Table.iPKey is negative.  Note
** that the datatype of the PRIMARY KEY must be INTEGER for this field to
** be set.  An INTEGER PRIMARY KEY is used as the rowid for each row of
** the table.  If a table has no INTEGER PRIMARY KEY, then a random rowid
** is generated for each row of the table.  Table.hasPrimKey is true if
** the table has any PRIMARY KEY, INTEGER or otherwise.
**
** Table.tnum is the page number for the root BTree page of the table in the
** database file.  If Table.iDb is the index of the database table backend
** in sqlite.aDb[].  0 is for the main database and 1 is for the file that
** holds temporary tables and indices.  If Table.isTransient
** is true, then the table is stored in a file that is automatically deleted
** when the VDBE cursor to the table is closed.  In this case Table.tnum 
** refers VDBE cursor number that holds the table open, not to the root
** page number.  Transient tables are used to hold the results of a
** sub-query that appears instead of a real table name in the FROM clause 
** of a SELECT statement.
*/
struct Table {
  char *zName;     /* Name of the table */
  int nCol;        /* Number of columns in this table */
  Column *aCol;    /* Information about each column */
  int iPKey;       /* If not less then 0, use aCol[iPKey] as the primary key */
  Index *pIndex;   /* List of SQL indexes on this table. */
  int tnum;        /* Root BTree node for this table (see note above) */
  Select *pSelect; /* NULL for tables.  Points to definition if a view. */
  u8 readOnly;     /* True if this table should not be written by the user */
  u8 iDb;          /* Index into sqlite.aDb[] of the backend for this table */
  u8 isTransient;  /* True if automatically deleted when VDBE finishes */
  u8 hasPrimKey;   /* True if there exists a primary key */
  u8 keyConf;      /* What to do in case of uniqueness conflict on iPKey */
  Trigger *pTrigger; /* List of SQL triggers on this table */
  FKey *pFKey;       /* Linked list of all foreign keys in this table */
};

/*
** Each foreign key constraint is an instance of the following structure.
**
** A foreign key is associated with two tables.  The "from" table is
** the table that contains the REFERENCES clause that creates the foreign
** key.  The "to" table is the table that is named in the REFERENCES clause.
** Consider this example:
**
**     CREATE TABLE ex1(
**       a INTEGER PRIMARY KEY,
**       b INTEGER CONSTRAINT fk1 REFERENCES ex2(x)
**     );
**
** For foreign key "fk1", the from-table is "ex1" and the to-table is "ex2".
**
** Each REFERENCES clause generates an instance of the following structure
** which is attached to the from-table.  The to-table need not exist when
** the from-table is created.  The existence of the to-table is not checked
** until an attempt is made to insert data into the from-table.
**
** The sqlite.aFKey hash table stores pointers to this structure
** given the name of a to-table.  For each to-table, all foreign keys
** associated with that table are on a linked list using the FKey.pNextTo
** field.
*/
struct FKey {
  Table *pFrom;     /* The table that constrains the REFERENCES clause */
  FKey *pNextFrom;  /* Next foreign key in pFrom */
  char *zTo;        /* Name of table that the key points to */
  FKey *pNextTo;    /* Next foreign key that points to zTo */
  int nCol;         /* Number of columns in this key */
  struct sColMap {  /* Mapping of columns in pFrom to columns in zTo */
    int iFrom;         /* Index of column in pFrom */
    char *zCol;        /* Name of column in zTo.  If 0 use PRIMARY KEY */
  } *aCol;          /* One entry for each of nCol column s */
  u8 isDeferred;    /* True if constraint checking is deferred till COMMIT */
  u8 updateConf;    /* How to resolve conflicts that occur on UPDATE */
  u8 deleteConf;    /* How to resolve conflicts that occur on DELETE */
  u8 insertConf;    /* How to resolve conflicts that occur on INSERT */
};

/*
** SQLite supports many different ways to resolve a contraint
** error.  ROLLBACK processing means that a constraint violation
** causes the operation in process to fail and for the current transaction
** to be rolled back.  ABORT processing means the operation in process
** fails and any prior changes from that one operation are backed out,
** but the transaction is not rolled back.  FAIL processing means that
** the operation in progress stops and returns an error code.  But prior
** changes due to the same operation are not backed out and no rollback
** occurs.  IGNORE means that the particular row that caused the constraint
** error is not inserted or updated.  Processing continues and no error
** is returned.  REPLACE means that preexisting database rows that caused
** a UNIQUE constraint violation are removed so that the new insert or
** update can proceed.  Processing continues and no error is reported.
**
** RESTRICT, SETNULL, and CASCADE actions apply only to foreign keys.
** RESTRICT is the same as ABORT for IMMEDIATE foreign keys and the
** same as ROLLBACK for DEFERRED keys.  SETNULL means that the foreign
** key is set to NULL.  CASCADE means that a DELETE or UPDATE of the
** referenced table row is propagated into the row that holds the
** foreign key.
** 
** The following symbolic values are used to record which type
** of action to take.
*/
#define OE_None     0   /* There is no constraint to check */
#define OE_Rollback 1   /* Fail the operation and rollback the transaction */
#define OE_Abort    2   /* Back out changes but do no rollback transaction */
#define OE_Fail     3   /* Stop the operation but leave all prior changes */
#define OE_Ignore   4   /* Ignore the error. Do not do the INSERT or UPDATE */
#define OE_Replace  5   /* Delete existing record, then do INSERT or UPDATE */

#define OE_Restrict 6   /* OE_Abort for IMMEDIATE, OE_Rollback for DEFERRED */
#define OE_SetNull  7   /* Set the foreign key value to NULL */
#define OE_SetDflt  8   /* Set the foreign key value to its default */
#define OE_Cascade  9   /* Cascade the changes */

#define OE_Default  99  /* Do whatever the default action is */

/*
** Each SQL index is represented in memory by an
** instance of the following structure.
**
** The columns of the table that are to be indexed are described
** by the aiColumn[] field of this structure.  For example, suppose
** we have the following table and index:
**
**     CREATE TABLE Ex1(c1 int, c2 int, c3 text);
**     CREATE INDEX Ex2 ON Ex1(c3,c1);
**
** In the Table structure describing Ex1, nCol==3 because there are
** three columns in the table.  In the Index structure describing
** Ex2, nColumn==2 since 2 of the 3 columns of Ex1 are indexed.
** The value of aiColumn is {2, 0}.  aiColumn[0]==2 because the 
** first column to be indexed (c3) has an index of 2 in Ex1.aCol[].
** The second column to be indexed (c1) has an index of 0 in
** Ex1.aCol[], hence Ex2.aiColumn[1]==0.
**
** The Index.onError field determines whether or not the indexed columns
** must be unique and what to do if they are not.  When Index.onError=OE_None,
** it means this is not a unique index.  Otherwise it is a unique index
** and the value of Index.onError indicate the which conflict resolution 
** algorithm to employ whenever an attempt is made to insert a non-unique
** element.
*/
struct Index {
  char *zName;     /* Name of this index */
  int nColumn;     /* Number of columns in the table used by this index */
  int *aiColumn;   /* Which columns are used by this index.  1st is 0 */
  Table *pTable;   /* The SQL table being indexed */
  int tnum;        /* Page containing root of this index in database file */
  u8 onError;      /* OE_Abort, OE_Ignore, OE_Replace, or OE_None */
  u8 autoIndex;    /* True if is automatically created (ex: by UNIQUE) */
  u8 iDb;          /* Index in sqlite.aDb[] of where this index is stored */
  Index *pNext;    /* The next index associated with the same table */
};

/*
** Each token coming out of the lexer is an instance of
** this structure.  Tokens are also used as part of an expression.
**
** Note if Token.z==0 then Token.dyn and Token.n are undefined and
** may contain random values.  Do not make any assuptions about Token.dyn
** and Token.n when Token.z==0.
*/
struct Token {
  const char *z;      /* Text of the token.  Not NULL-terminated! */
  unsigned dyn  : 1;  /* True for malloced memory, false for static */
  unsigned n    : 31; /* Number of characters in this token */
};

/*
** Each node of an expression in the parse tree is an instance
** of this structure.
**
** Expr.op is the opcode.  The integer parser token codes are reused
** as opcodes here.  For example, the parser defines TK_GE to be an integer
** code representing the ">=" operator.  This same integer code is reused
** to represent the greater-than-or-equal-to operator in the expression
** tree.
**
** Expr.pRight and Expr.pLeft are subexpressions.  Expr.pList is a list
** of argument if the expression is a function.
**
** Expr.token is the operator token for this node.  For some expressions
** that have subexpressions, Expr.token can be the complete text that gave
** rise to the Expr.  In the latter case, the token is marked as being
** a compound token.
**
** An expression of the form ID or ID.ID refers to a column in a table.
** For such expressions, Expr.op is set to TK_COLUMN and Expr.iTable is
** the integer cursor number of a VDBE cursor pointing to that table and
** Expr.iColumn is the column number for the specific column.  If the
** expression is used as a result in an aggregate SELECT, then the
** value is also stored in the Expr.iAgg column in the aggregate so that
** it can be accessed after all aggregates are computed.
**
** If the expression is a function, the Expr.iTable is an integer code
** representing which function.  If the expression is an unbound variable
** marker (a question mark character '?' in the original SQL) then the
** Expr.iTable holds the index number for that variable.
**
** The Expr.pSelect field points to a SELECT statement.  The SELECT might
** be the right operand of an IN operator.  Or, if a scalar SELECT appears
** in an expression the opcode is TK_SELECT and Expr.pSelect is the only
** operand.
*/
struct Expr {
  u8 op;                 /* Operation performed by this node */
  u8 dataType;           /* Either SQLITE_SO_TEXT or SQLITE_SO_NUM */
  u8 iDb;                /* Database referenced by this expression */
  u8 flags;              /* Various flags.  See below */
  Expr *pLeft, *pRight;  /* Left and right subnodes */
  ExprList *pList;       /* A list of expressions used as function arguments
                         ** or in "<expr> IN (<expr-list)" */
  Token token;           /* An operand token */
  Token span;            /* Complete text of the expression */
  int iTable, iColumn;   /* When op==TK_COLUMN, then this expr node means the
                         ** iColumn-th field of the iTable-th table. */
  int iAgg;              /* When op==TK_COLUMN and pParse->useAgg==TRUE, pull
                         ** result from the iAgg-th element of the aggregator */
  Select *pSelect;       /* When the expression is a sub-select.  Also the
                         ** right side of "<expr> IN (<select>)" */
};

/*
** The following are the meanings of bits in the Expr.flags field.
*/
#define EP_FromJoin     0x0001  /* Originated in ON or USING clause of a join */

/*
** These macros can be used to test, set, or clear bits in the 
** Expr.flags field.
*/
#define ExprHasProperty(E,P)     (((E)->flags&(P))==(P))
#define ExprHasAnyProperty(E,P)  (((E)->flags&(P))!=0)
#define ExprSetProperty(E,P)     (E)->flags|=(P)
#define ExprClearProperty(E,P)   (E)->flags&=~(P)

/*
** A list of expressions.  Each expression may optionally have a
** name.  An expr/name combination can be used in several ways, such
** as the list of "expr AS ID" fields following a "SELECT" or in the
** list of "ID = expr" items in an UPDATE.  A list of expressions can
** also be used as the argument to a function, in which case the a.zName
** field is not used.
*/
struct ExprList {
  int nExpr;             /* Number of expressions on the list */
  int nAlloc;            /* Number of entries allocated below */
  struct ExprList_item {
    Expr *pExpr;           /* The list of expressions */
    char *zName;           /* Token associated with this expression */
    u8 sortOrder;          /* 1 for DESC or 0 for ASC */
    u8 isAgg;              /* True if this is an aggregate like count(*) */
    u8 done;               /* A flag to indicate when processing is finished */
  } *a;                  /* One entry for each expression */
};

/*
** An instance of this structure can hold a simple list of identifiers,
** such as the list "a,b,c" in the following statements:
**
**      INSERT INTO t(a,b,c) VALUES ...;
**      CREATE INDEX idx ON t(a,b,c);
**      CREATE TRIGGER trig BEFORE UPDATE ON t(a,b,c) ...;
**
** The IdList.a.idx field is used when the IdList represents the list of
** column names after a table name in an INSERT statement.  In the statement
**
**     INSERT INTO t(a,b,c) ...
**
** If "a" is the k-th column of table "t", then IdList.a[0].idx==k.
*/
struct IdList {
  int nId;         /* Number of identifiers on the list */
  int nAlloc;      /* Number of entries allocated for a[] below */
  struct IdList_item {
    char *zName;      /* Name of the identifier */
    int idx;          /* Index in some Table.aCol[] of a column named zName */
  } *a;
};

/*
** The following structure describes the FROM clause of a SELECT statement.
** Each table or subquery in the FROM clause is a separate element of
** the SrcList.a[] array.
**
** With the addition of multiple database support, the following structure
** can also be used to describe a particular table such as the table that
** is modified by an INSERT, DELETE, or UPDATE statement.  In standard SQL,
** such a table must be a simple name: ID.  But in SQLite, the table can
** now be identified by a database name, a dot, then the table name: ID.ID.
*/
struct SrcList {
  u16 nSrc;        /* Number of tables or subqueries in the FROM clause */
  u16 nAlloc;      /* Number of entries allocated in a[] below */
  struct SrcList_item {
    char *zDatabase;  /* Name of database holding this table */
    char *zName;      /* Name of the table */
    char *zAlias;     /* The "B" part of a "A AS B" phrase.  zName is the "A" */
    Table *pTab;      /* An SQL table corresponding to zName */
    Select *pSelect;  /* A SELECT statement used in place of a table name */
    int jointype;     /* Type of join between this table and the next */
    int iCursor;      /* The VDBE cursor number used to access this table */
    Expr *pOn;        /* The ON clause of a join */
    IdList *pUsing;   /* The USING clause of a join */
  } a[1];             /* One entry for each identifier on the list */
};

/*
** Permitted values of the SrcList.a.jointype field
*/
#define JT_INNER     0x0001    /* Any kind of inner or cross join */
#define JT_NATURAL   0x0002    /* True for a "natural" join */
#define JT_LEFT      0x0004    /* Left outer join */
#define JT_RIGHT     0x0008    /* Right outer join */
#define JT_OUTER     0x0010    /* The "OUTER" keyword is present */
#define JT_ERROR     0x0020    /* unknown or unsupported join type */

/*
** For each nested loop in a WHERE clause implementation, the WhereInfo
** structure contains a single instance of this structure.  This structure
** is intended to be private the the where.c module and should not be
** access or modified by other modules.
*/
struct WhereLevel {
  int iMem;            /* Memory cell used by this level */
  Index *pIdx;         /* Index used */
  int iCur;            /* Cursor number used for this index */
  int score;           /* How well this indexed scored */
  int brk;             /* Jump here to break out of the loop */
  int cont;            /* Jump here to continue with the next loop cycle */
  int op, p1, p2;      /* Opcode used to terminate the loop */
  int iLeftJoin;       /* Memory cell used to implement LEFT OUTER JOIN */
  int top;             /* First instruction of interior of the loop */
  int inOp, inP1, inP2;/* Opcode used to implement an IN operator */
  int bRev;            /* Do the scan in the reverse direction */
};

/*
** The WHERE clause processing routine has two halves.  The
** first part does the start of the WHERE loop and the second
** half does the tail of the WHERE loop.  An instance of
** this structure is returned by the first half and passed
** into the second half to give some continuity.
*/
struct WhereInfo {
  Parse *pParse;
  SrcList *pTabList;   /* List of tables in the join */
  int iContinue;       /* Jump here to continue with next record */
  int iBreak;          /* Jump here to break out of the loop */
  int nLevel;          /* Number of nested loop */
  int savedNTab;       /* Value of pParse->nTab before WhereBegin() */
  int peakNTab;        /* Value of pParse->nTab after WhereBegin() */
  WhereLevel a[1];     /* Information about each nest loop in the WHERE */
};

/*
** An instance of the following structure contains all information
** needed to generate code for a single SELECT statement.
**
** The zSelect field is used when the Select structure must be persistent.
** Normally, the expression tree points to tokens in the original input
** string that encodes the select.  But if the Select structure must live
** longer than its input string (for example when it is used to describe
** a VIEW) we have to make a copy of the input string so that the nodes
** of the expression tree will have something to point to.  zSelect is used
** to hold that copy.
**
** nLimit is set to -1 if there is no LIMIT clause.  nOffset is set to 0.
** If there is a LIMIT clause, the parser sets nLimit to the value of the
** limit and nOffset to the value of the offset (or 0 if there is not
** offset).  But later on, nLimit and nOffset become the memory locations
** in the VDBE that record the limit and offset counters.
*/
struct Select {
  ExprList *pEList;      /* The fields of the result */
  u8 op;                 /* One of: TK_UNION TK_ALL TK_INTERSECT TK_EXCEPT */
  u8 isDistinct;         /* True if the DISTINCT keyword is present */
  SrcList *pSrc;         /* The FROM clause */
  Expr *pWhere;          /* The WHERE clause */
  ExprList *pGroupBy;    /* The GROUP BY clause */
  Expr *pHaving;         /* The HAVING clause */
  ExprList *pOrderBy;    /* The ORDER BY clause */
  Select *pPrior;        /* Prior select in a compound select statement */
  int nLimit, nOffset;   /* LIMIT and OFFSET values.  -1 means not used */
  int iLimit, iOffset;   /* Memory registers holding LIMIT & OFFSET counters */
  char *zSelect;         /* Complete text of the SELECT command */
};

/*
** The results of a select can be distributed in several ways.
*/
#define SRT_Callback     1  /* Invoke a callback with each row of result */
#define SRT_Mem          2  /* Store result in a memory cell */
#define SRT_Set          3  /* Store result as unique keys in a table */
#define SRT_Union        5  /* Store result as keys in a table */
#define SRT_Except       6  /* Remove result from a UNION table */
#define SRT_Table        7  /* Store result as data with a unique key */
#define SRT_TempTable    8  /* Store result in a trasient table */
#define SRT_Discard      9  /* Do not save the results anywhere */
#define SRT_Sorter      10  /* Store results in the sorter */
#define SRT_Subroutine  11  /* Call a subroutine to handle results */

/*
** When a SELECT uses aggregate functions (like "count(*)" or "avg(f1)")
** we have to do some additional analysis of expressions.  An instance
** of the following structure holds information about a single subexpression
** somewhere in the SELECT statement.  An array of these structures holds
** all the information we need to generate code for aggregate
** expressions.
**
** Note that when analyzing a SELECT containing aggregates, both
** non-aggregate field variables and aggregate functions are stored
** in the AggExpr array of the Parser structure.
**
** The pExpr field points to an expression that is part of either the
** field list, the GROUP BY clause, the HAVING clause or the ORDER BY
** clause.  The expression will be freed when those clauses are cleaned
** up.  Do not try to delete the expression attached to AggExpr.pExpr.
**
** If AggExpr.pExpr==0, that means the expression is "count(*)".
*/
struct AggExpr {
  int isAgg;        /* if TRUE contains an aggregate function */
  Expr *pExpr;      /* The expression */
  FuncDef *pFunc;   /* Information about the aggregate function */
};

/*
** An SQL parser context.  A copy of this structure is passed through
** the parser and down into all the parser action routine in order to
** carry around information that is global to the entire parse.
*/
struct Parse {
  sqlite *db;          /* The main database structure */
  int rc;              /* Return code from execution */
  char *zErrMsg;       /* An error message */
  Token sErrToken;     /* The token at which the error occurred */
  Token sFirstToken;   /* The first token parsed */
  Token sLastToken;    /* The last token parsed */
  const char *zTail;   /* All SQL text past the last semicolon parsed */
  Table *pNewTable;    /* A table being constructed by CREATE TABLE */
  Vdbe *pVdbe;         /* An engine for executing database bytecode */
  u8 colNamesSet;      /* TRUE after OP_ColumnName has been issued to pVdbe */
  u8 explain;          /* True if the EXPLAIN flag is found on the query */
  u8 nameClash;        /* A permanent table name clashes with temp table name */
  u8 useAgg;           /* If true, extract field values from the aggregator
                       ** while generating expressions.  Normally false */
  int nErr;            /* Number of errors seen */
  int nTab;            /* Number of previously allocated VDBE cursors */
  int nMem;            /* Number of memory cells used so far */
  int nSet;            /* Number of sets used so far */
  int nAgg;            /* Number of aggregate expressions */
  int nVar;            /* Number of '?' variables seen in the SQL so far */
  AggExpr *aAgg;       /* An array of aggregate expressions */
  const char *zAuthContext; /* The 6th parameter to db->xAuth callbacks */
  Trigger *pNewTrigger;     /* Trigger under construct by a CREATE TRIGGER */
  TriggerStack *trigStack;  /* Trigger actions being coded */
};

/*
** An instance of the following structure can be declared on a stack and used
** to save the Parse.zAuthContext value so that it can be restored later.
*/
struct AuthContext {
  const char *zAuthContext;   /* Put saved Parse.zAuthContext here */
  Parse *pParse;              /* The Parse structure */
};

/*
** Bitfield flags for P2 value in OP_PutIntKey and OP_Delete
*/
#define OPFLAG_NCHANGE   1    /* Set to update db->nChange */
#define OPFLAG_LASTROWID 2    /* Set to update db->lastRowid */
#define OPFLAG_CSCHANGE  4    /* Set to update db->csChange */

/*
 * Each trigger present in the database schema is stored as an instance of
 * struct Trigger. 
 *
 * Pointers to instances of struct Trigger are stored in two ways.
 * 1. In the "trigHash" hash table (part of the sqlite* that represents the 
 *    database). This allows Trigger structures to be retrieved by name.
 * 2. All triggers associated with a single table form a linked list, using the
 *    pNext member of struct Trigger. A pointer to the first element of the
 *    linked list is stored as the "pTrigger" member of the associated
 *    struct Table.
 *
 * The "step_list" member points to the first element of a linked list
 * containing the SQL statements specified as the trigger program.
 */
struct Trigger {
  char *name;             /* The name of the trigger                        */
  char *table;            /* The table or view to which the trigger applies */
  u8 iDb;                 /* Database containing this trigger               */
  u8 iTabDb;              /* Database containing Trigger.table              */
  u8 op;                  /* One of TK_DELETE, TK_UPDATE, TK_INSERT         */
  u8 tr_tm;               /* One of TK_BEFORE, TK_AFTER */
  Expr *pWhen;            /* The WHEN clause of the expression (may be NULL) */
  IdList *pColumns;       /* If this is an UPDATE OF <column-list> trigger,
                             the <column-list> is stored here */
  int foreach;            /* One of TK_ROW or TK_STATEMENT */
  Token nameToken;        /* Token containing zName. Use during parsing only */

  TriggerStep *step_list; /* Link list of trigger program steps             */
  Trigger *pNext;         /* Next trigger associated with the table */
};

/*
 * An instance of struct TriggerStep is used to store a single SQL statement
 * that is a part of a trigger-program. 
 *
 * Instances of struct TriggerStep are stored in a singly linked list (linked
 * using the "pNext" member) referenced by the "step_list" member of the 
 * associated struct Trigger instance. The first element of the linked list is
 * the first step of the trigger-program.
 * 
 * The "op" member indicates whether this is a "DELETE", "INSERT", "UPDATE" or
 * "SELECT" statement. The meanings of the other members is determined by the 
 * value of "op" as follows:
 *
 * (op == TK_INSERT)
 * orconf    -> stores the ON CONFLICT algorithm
 * pSelect   -> If this is an INSERT INTO ... SELECT ... statement, then
 *              this stores a pointer to the SELECT statement. Otherwise NULL.
 * target    -> A token holding the name of the table to insert into.
 * pExprList -> If this is an INSERT INTO ... VALUES ... statement, then
 *              this stores values to be inserted. Otherwise NULL.
 * pIdList   -> If this is an INSERT INTO ... (<column-names>) VALUES ... 
 *              statement, then this stores the column-names to be
 *              inserted into.
 *
 * (op == TK_DELETE)
 * target    -> A token holding the name of the table to delete from.
 * pWhere    -> The WHERE clause of the DELETE statement if one is specified.
 *              Otherwise NULL.
 * 
 * (op == TK_UPDATE)
 * target    -> A token holding the name of the table to update rows of.
 * pWhere    -> The WHERE clause of the UPDATE statement if one is specified.
 *              Otherwise NULL.
 * pExprList -> A list of the columns to update and the expressions to update
 *              them to. See sqliteUpdate() documentation of "pChanges"
 *              argument.
 * 
 */
struct TriggerStep {
  int op;              /* One of TK_DELETE, TK_UPDATE, TK_INSERT, TK_SELECT */
  int orconf;          /* OE_Rollback etc. */
  Trigger *pTrig;      /* The trigger that this step is a part of */

  Select *pSelect;     /* Valid for SELECT and sometimes 
			  INSERT steps (when pExprList == 0) */
  Token target;        /* Valid for DELETE, UPDATE, INSERT steps */
  Expr *pWhere;        /* Valid for DELETE, UPDATE steps */
  ExprList *pExprList; /* Valid for UPDATE statements and sometimes 
			   INSERT steps (when pSelect == 0)         */
  IdList *pIdList;     /* Valid for INSERT statements only */

  TriggerStep * pNext; /* Next in the link-list */
};

/*
 * An instance of struct TriggerStack stores information required during code
 * generation of a single trigger program. While the trigger program is being
 * coded, its associated TriggerStack instance is pointed to by the
 * "pTriggerStack" member of the Parse structure.
 *
 * The pTab member points to the table that triggers are being coded on. The 
 * newIdx member contains the index of the vdbe cursor that points at the temp
 * table that stores the new.* references. If new.* references are not valid
 * for the trigger being coded (for example an ON DELETE trigger), then newIdx
 * is set to -1. The oldIdx member is analogous to newIdx, for old.* references.
 *
 * The ON CONFLICT policy to be used for the trigger program steps is stored 
 * as the orconf member. If this is OE_Default, then the ON CONFLICT clause 
 * specified for individual triggers steps is used.
 *
 * struct TriggerStack has a "pNext" member, to allow linked lists to be
 * constructed. When coding nested triggers (triggers fired by other triggers)
 * each nested trigger stores its parent trigger's TriggerStack as the "pNext" 
 * pointer. Once the nested trigger has been coded, the pNext value is restored
 * to the pTriggerStack member of the Parse structure and coding of the parent
 * trigger continues.
 *
 * Before a nested trigger is coded, the linked list pointed to by the 
 * pTriggerStack is scanned to ensure that the trigger is not about to be coded
 * recursively. If this condition is detected, the nested trigger is not coded.
 */
struct TriggerStack {
  Table *pTab;         /* Table that triggers are currently being coded on */
  int newIdx;          /* Index of vdbe cursor to "new" temp table */
  int oldIdx;          /* Index of vdbe cursor to "old" temp table */
  int orconf;          /* Current orconf policy */
  int ignoreJump;      /* where to jump to for a RAISE(IGNORE) */
  Trigger *pTrigger;   /* The trigger currently being coded */
  TriggerStack *pNext; /* Next trigger down on the trigger stack */
};

/*
** The following structure contains information used by the sqliteFix...
** routines as they walk the parse tree to make database references
** explicit.  
*/
typedef struct DbFixer DbFixer;
struct DbFixer {
  Parse *pParse;      /* The parsing context.  Error messages written here */
  const char *zDb;    /* Make sure all objects are contained in this database */
  const char *zType;  /* Type of the container - used for error messages */
  const Token *pName; /* Name of the container - used for error messages */
};

/*
 * This global flag is set for performance testing of triggers. When it is set
 * SQLite will perform the overhead of building new and old trigger references 
 * even when no triggers exist
 */
extern int always_code_trigger_setup;

/*
** Internal function prototypes
*/
int sqliteStrICmp(const char *, const char *);
int sqliteStrNICmp(const char *, const char *, int);
int sqliteHashNoCase(const char *, int);
int sqliteIsNumber(const char*);
int sqliteCompare(const char *, const char *);
int sqliteSortCompare(const char *, const char *);
void sqliteRealToSortable(double r, char *);
#ifdef MEMORY_DEBUG
  void *sqliteMalloc_(int,int,char*,int);
  void sqliteFree_(void*,char*,int);
  void *sqliteRealloc_(void*,int,char*,int);
  char *sqliteStrDup_(const char*,char*,int);
  char *sqliteStrNDup_(const char*, int,char*,int);
  void sqliteCheckMemory(void*,int);
#else
  void *sqliteMalloc(int);
  void *sqliteMallocRaw(int);
  void sqliteFree(void*);
  void *sqliteRealloc(void*,int);
  char *sqliteStrDup(const char*);
  char *sqliteStrNDup(const char*, int);
# define sqliteCheckMemory(a,b)
#endif
char *sqliteMPrintf(const char*, ...);
char *sqliteVMPrintf(const char*, va_list);
void sqliteSetString(char **, const char *, ...);
void sqliteSetNString(char **, ...);
void sqliteErrorMsg(Parse*, const char*, ...);
void sqliteDequote(char*);
int sqliteKeywordCode(const char*, int);
int sqliteRunParser(Parse*, const char*, char **);
void sqliteExec(Parse*);
Expr *sqliteExpr(int, Expr*, Expr*, Token*);
void sqliteExprSpan(Expr*,Token*,Token*);
Expr *sqliteExprFunction(ExprList*, Token*);
void sqliteExprDelete(Expr*);
ExprList *sqliteExprListAppend(ExprList*,Expr*,Token*);
void sqliteExprListDelete(ExprList*);
int sqliteInit(sqlite*, char**);
void sqlitePragma(Parse*,Token*,Token*,int);
void sqliteResetInternalSchema(sqlite*, int);
void sqliteBeginParse(Parse*,int);
void sqliteRollbackInternalChanges(sqlite*);
void sqliteCommitInternalChanges(sqlite*);
Table *sqliteResultSetOfSelect(Parse*,char*,Select*);
void sqliteOpenMasterTable(Vdbe *v, int);
void sqliteStartTable(Parse*,Token*,Token*,int,int);
void sqliteAddColumn(Parse*,Token*);
void sqliteAddNotNull(Parse*, int);
void sqliteAddPrimaryKey(Parse*, IdList*, int);
void sqliteAddColumnType(Parse*,Token*,Token*);
void sqliteAddDefaultValue(Parse*,Token*,int);
int sqliteCollateType(const char*, int);
void sqliteAddCollateType(Parse*, int);
void sqliteEndTable(Parse*,Token*,Select*);
void sqliteCreateView(Parse*,Token*,Token*,Select*,int);
int sqliteViewGetColumnNames(Parse*,Table*);
void sqliteDropTable(Parse*, Token*, int);
void sqliteDeleteTable(sqlite*, Table*);
void sqliteInsert(Parse*, SrcList*, ExprList*, Select*, IdList*, int);
IdList *sqliteIdListAppend(IdList*, Token*);
int sqliteIdListIndex(IdList*,const char*);
SrcList *sqliteSrcListAppend(SrcList*, Token*, Token*);
void sqliteSrcListAddAlias(SrcList*, Token*);
void sqliteSrcListAssignCursors(Parse*, SrcList*);
void sqliteIdListDelete(IdList*);
void sqliteSrcListDelete(SrcList*);
void sqliteCreateIndex(Parse*,Token*,SrcList*,IdList*,int,Token*,Token*);
void sqliteDropIndex(Parse*, SrcList*);
void sqliteAddKeyType(Vdbe*, ExprList*);
void sqliteAddIdxKeyType(Vdbe*, Index*);
int sqliteSelect(Parse*, Select*, int, int, Select*, int, int*);
Select *sqliteSelectNew(ExprList*,SrcList*,Expr*,ExprList*,Expr*,ExprList*,
                        int,int,int);
void sqliteSelectDelete(Select*);
void sqliteSelectUnbind(Select*);
Table *sqliteSrcListLookup(Parse*, SrcList*);
int sqliteIsReadOnly(Parse*, Table*, int);
void sqliteDeleteFrom(Parse*, SrcList*, Expr*);
void sqliteUpdate(Parse*, SrcList*, ExprList*, Expr*, int);
WhereInfo *sqliteWhereBegin(Parse*, SrcList*, Expr*, int, ExprList**);
void sqliteWhereEnd(WhereInfo*);
void sqliteExprCode(Parse*, Expr*);
int sqliteExprCodeExprList(Parse*, ExprList*, int);
void sqliteExprIfTrue(Parse*, Expr*, int, int);
void sqliteExprIfFalse(Parse*, Expr*, int, int);
Table *sqliteFindTable(sqlite*,const char*, const char*);
Table *sqliteLocateTable(Parse*,const char*, const char*);
Index *sqliteFindIndex(sqlite*,const char*, const char*);
void sqliteUnlinkAndDeleteIndex(sqlite*,Index*);
void sqliteCopy(Parse*, SrcList*, Token*, Token*, int);
void sqliteVacuum(Parse*, Token*);
int sqliteRunVacuum(char**, sqlite*);
int sqliteGlobCompare(const unsigned char*,const unsigned char*);
int sqliteLikeCompare(const unsigned char*,const unsigned char*);
char *sqliteTableNameFromToken(Token*);
int sqliteExprCheck(Parse*, Expr*, int, int*);
int sqliteExprType(Expr*);
int sqliteExprCompare(Expr*, Expr*);
int sqliteFuncId(Token*);
int sqliteExprResolveIds(Parse*, SrcList*, ExprList*, Expr*);
int sqliteExprAnalyzeAggregates(Parse*, Expr*);
Vdbe *sqliteGetVdbe(Parse*);
void sqliteRandomness(int, void*);
void sqliteRollbackAll(sqlite*);
void sqliteCodeVerifySchema(Parse*, int);
void sqliteBeginTransaction(Parse*, int);
void sqliteCommitTransaction(Parse*);
void sqliteRollbackTransaction(Parse*);
int sqliteExprIsConstant(Expr*);
int sqliteExprIsInteger(Expr*, int*);
int sqliteIsRowid(const char*);
void sqliteGenerateRowDelete(sqlite*, Vdbe*, Table*, int, int);
void sqliteGenerateRowIndexDelete(sqlite*, Vdbe*, Table*, int, char*);
void sqliteGenerateConstraintChecks(Parse*,Table*,int,char*,int,int,int,int);
void sqliteCompleteInsertion(Parse*, Table*, int, char*, int, int, int);
int sqliteOpenTableAndIndices(Parse*, Table*, int);
void sqliteBeginWriteOperation(Parse*, int, int);
void sqliteEndWriteOperation(Parse*);
Expr *sqliteExprDup(Expr*);
void sqliteTokenCopy(Token*, Token*);
ExprList *sqliteExprListDup(ExprList*);
SrcList *sqliteSrcListDup(SrcList*);
IdList *sqliteIdListDup(IdList*);
Select *sqliteSelectDup(Select*);
FuncDef *sqliteFindFunction(sqlite*,const char*,int,int,int);
void sqliteRegisterBuiltinFunctions(sqlite*);
void sqliteRegisterDateTimeFunctions(sqlite*);
int sqliteSafetyOn(sqlite*);
int sqliteSafetyOff(sqlite*);
int sqliteSafetyCheck(sqlite*);
void sqliteChangeCookie(sqlite*, Vdbe*);
void sqliteBeginTrigger(Parse*, Token*,int,int,IdList*,SrcList*,int,Expr*,int);
void sqliteFinishTrigger(Parse*, TriggerStep*, Token*);
void sqliteDropTrigger(Parse*, SrcList*);
void sqliteDropTriggerPtr(Parse*, Trigger*, int);
int sqliteTriggersExist(Parse* , Trigger* , int , int , int, ExprList*);
int sqliteCodeRowTrigger(Parse*, int, ExprList*, int, Table *, int, int, 
                         int, int);
void sqliteViewTriggers(Parse*, Table*, Expr*, int, ExprList*);
void sqliteDeleteTriggerStep(TriggerStep*);
TriggerStep *sqliteTriggerSelectStep(Select*);
TriggerStep *sqliteTriggerInsertStep(Token*, IdList*, ExprList*, Select*, int);
TriggerStep *sqliteTriggerUpdateStep(Token*, ExprList*, Expr*, int);
TriggerStep *sqliteTriggerDeleteStep(Token*, Expr*);
void sqliteDeleteTrigger(Trigger*);
int sqliteJoinType(Parse*, Token*, Token*, Token*);
void sqliteCreateForeignKey(Parse*, IdList*, Token*, IdList*, int);
void sqliteDeferForeignKey(Parse*, int);
#ifndef SQLITE_OMIT_AUTHORIZATION
  void sqliteAuthRead(Parse*,Expr*,SrcList*);
  int sqliteAuthCheck(Parse*,int, const char*, const char*, const char*);
  void sqliteAuthContextPush(Parse*, AuthContext*, const char*);
  void sqliteAuthContextPop(AuthContext*);
#else
# define sqliteAuthRead(a,b,c)
# define sqliteAuthCheck(a,b,c,d,e)    SQLITE_OK
# define sqliteAuthContextPush(a,b,c)
# define sqliteAuthContextPop(a)  ((void)(a))
#endif
void sqliteAttach(Parse*, Token*, Token*, Token*);
void sqliteDetach(Parse*, Token*);
int sqliteBtreeFactory(const sqlite *db, const char *zFilename,
                       int mode, int nPg, Btree **ppBtree);
int sqliteFixInit(DbFixer*, Parse*, int, const char*, const Token*);
int sqliteFixSrcList(DbFixer*, SrcList*);
int sqliteFixSelect(DbFixer*, Select*);
int sqliteFixExpr(DbFixer*, Expr*);
int sqliteFixExprList(DbFixer*, ExprList*);
int sqliteFixTriggerStep(DbFixer*, TriggerStep*);
double sqliteAtoF(const char *z, const char **);
char *sqlite_snprintf(int,char*,const char*,...);
int sqliteFitsIn32Bits(const char *);
