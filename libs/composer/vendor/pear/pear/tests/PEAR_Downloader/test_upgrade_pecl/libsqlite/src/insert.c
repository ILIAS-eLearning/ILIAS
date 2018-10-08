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
** This file contains C code routines that are called by the parser
** to handle INSERT statements in SQLite.
**
** $Id$
*/
#include "sqliteInt.h"

/*
** This routine is call to handle SQL of the following forms:
**
**    insert into TABLE (IDLIST) values(EXPRLIST)
**    insert into TABLE (IDLIST) select
**
** The IDLIST following the table name is always optional.  If omitted,
** then a list of all columns for the table is substituted.  The IDLIST
** appears in the pColumn parameter.  pColumn is NULL if IDLIST is omitted.
**
** The pList parameter holds EXPRLIST in the first form of the INSERT
** statement above, and pSelect is NULL.  For the second form, pList is
** NULL and pSelect is a pointer to the select statement used to generate
** data for the insert.
**
** The code generated follows one of three templates.  For a simple
** select with data coming from a VALUES clause, the code executes
** once straight down through.  The template looks like this:
**
**         open write cursor to <table> and its indices
**         puts VALUES clause expressions onto the stack
**         write the resulting record into <table>
**         cleanup
**
** If the statement is of the form
**
**   INSERT INTO <table> SELECT ...
**
** And the SELECT clause does not read from <table> at any time, then
** the generated code follows this template:
**
**         goto B
**      A: setup for the SELECT
**         loop over the tables in the SELECT
**           gosub C
**         end loop
**         cleanup after the SELECT
**         goto D
**      B: open write cursor to <table> and its indices
**         goto A
**      C: insert the select result into <table>
**         return
**      D: cleanup
**
** The third template is used if the insert statement takes its
** values from a SELECT but the data is being inserted into a table
** that is also read as part of the SELECT.  In the third form,
** we have to use a intermediate table to store the results of
** the select.  The template is like this:
**
**         goto B
**      A: setup for the SELECT
**         loop over the tables in the SELECT
**           gosub C
**         end loop
**         cleanup after the SELECT
**         goto D
**      C: insert the select result into the intermediate table
**         return
**      B: open a cursor to an intermediate table
**         goto A
**      D: open write cursor to <table> and its indices
**         loop over the intermediate table
**           transfer values form intermediate table into <table>
**         end the loop
**         cleanup
*/
void sqliteInsert(
  Parse *pParse,        /* Parser context */
  SrcList *pTabList,    /* Name of table into which we are inserting */
  ExprList *pList,      /* List of values to be inserted */
  Select *pSelect,      /* A SELECT statement to use as the data source */
  IdList *pColumn,      /* Column names corresponding to IDLIST. */
  int onError           /* How to handle constraint errors */
){
  Table *pTab;          /* The table to insert into */
  char *zTab;           /* Name of the table into which we are inserting */
  const char *zDb;      /* Name of the database holding this table */
  int i, j, idx;        /* Loop counters */
  Vdbe *v;              /* Generate code into this virtual machine */
  Index *pIdx;          /* For looping over indices of the table */
  int nColumn;          /* Number of columns in the data */
  int base;             /* VDBE Cursor number for pTab */
  int iCont, iBreak;    /* Beginning and end of the loop over srcTab */
  sqlite *db;           /* The main database structure */
  int keyColumn = -1;   /* Column that is the INTEGER PRIMARY KEY */
  int endOfLoop;        /* Label for the end of the insertion loop */
  int useTempTable;     /* Store SELECT results in intermediate table */
  int srcTab;           /* Data comes from this temporary cursor if >=0 */
  int iSelectLoop;      /* Address of code that implements the SELECT */
  int iCleanup;         /* Address of the cleanup code */
  int iInsertBlock;     /* Address of the subroutine used to insert data */
  int iCntMem;          /* Memory cell used for the row counter */
  int isView;           /* True if attempting to insert into a view */

  int row_triggers_exist = 0; /* True if there are FOR EACH ROW triggers */
  int before_triggers;        /* True if there are BEFORE triggers */
  int after_triggers;         /* True if there are AFTER triggers */
  int newIdx = -1;            /* Cursor for the NEW table */

  if( pParse->nErr || sqlite_malloc_failed ) goto insert_cleanup;
  db = pParse->db;

  /* Locate the table into which we will be inserting new information.
  */
  assert( pTabList->nSrc==1 );
  zTab = pTabList->a[0].zName;
  if( zTab==0 ) goto insert_cleanup;
  pTab = sqliteSrcListLookup(pParse, pTabList);
  if( pTab==0 ){
    goto insert_cleanup;
  }
  assert( pTab->iDb<db->nDb );
  zDb = db->aDb[pTab->iDb].zName;
  if( sqliteAuthCheck(pParse, SQLITE_INSERT, pTab->zName, 0, zDb) ){
    goto insert_cleanup;
  }

  /* Ensure that:
  *  (a) the table is not read-only, 
  *  (b) that if it is a view then ON INSERT triggers exist
  */
  before_triggers = sqliteTriggersExist(pParse, pTab->pTrigger, TK_INSERT, 
                                       TK_BEFORE, TK_ROW, 0);
  after_triggers = sqliteTriggersExist(pParse, pTab->pTrigger, TK_INSERT,
                                       TK_AFTER, TK_ROW, 0);
  row_triggers_exist = before_triggers || after_triggers;
  isView = pTab->pSelect!=0;
  if( sqliteIsReadOnly(pParse, pTab, before_triggers) ){
    goto insert_cleanup;
  }
  if( pTab==0 ) goto insert_cleanup;

  /* If pTab is really a view, make sure it has been initialized.
  */
  if( isView && sqliteViewGetColumnNames(pParse, pTab) ){
    goto insert_cleanup;
  }

  /* Allocate a VDBE
  */
  v = sqliteGetVdbe(pParse);
  if( v==0 ) goto insert_cleanup;
  sqliteBeginWriteOperation(pParse, pSelect || row_triggers_exist, pTab->iDb);

  /* if there are row triggers, allocate a temp table for new.* references. */
  if( row_triggers_exist ){
    newIdx = pParse->nTab++;
  }

  /* Figure out how many columns of data are supplied.  If the data
  ** is coming from a SELECT statement, then this step also generates
  ** all the code to implement the SELECT statement and invoke a subroutine
  ** to process each row of the result. (Template 2.) If the SELECT
  ** statement uses the the table that is being inserted into, then the
  ** subroutine is also coded here.  That subroutine stores the SELECT
  ** results in a temporary table. (Template 3.)
  */
  if( pSelect ){
    /* Data is coming from a SELECT.  Generate code to implement that SELECT
    */
    int rc, iInitCode;
    iInitCode = sqliteVdbeAddOp(v, OP_Goto, 0, 0);
    iSelectLoop = sqliteVdbeCurrentAddr(v);
    iInsertBlock = sqliteVdbeMakeLabel(v);
    rc = sqliteSelect(pParse, pSelect, SRT_Subroutine, iInsertBlock, 0,0,0);
    if( rc || pParse->nErr || sqlite_malloc_failed ) goto insert_cleanup;
    iCleanup = sqliteVdbeMakeLabel(v);
    sqliteVdbeAddOp(v, OP_Goto, 0, iCleanup);
    assert( pSelect->pEList );
    nColumn = pSelect->pEList->nExpr;

    /* Set useTempTable to TRUE if the result of the SELECT statement
    ** should be written into a temporary table.  Set to FALSE if each
    ** row of the SELECT can be written directly into the result table.
    **
    ** A temp table must be used if the table being updated is also one
    ** of the tables being read by the SELECT statement.  Also use a 
    ** temp table in the case of row triggers.
    */
    if( row_triggers_exist ){
      useTempTable = 1;
    }else{
      int addr = sqliteVdbeFindOp(v, OP_OpenRead, pTab->tnum);
      useTempTable = 0;
      if( addr>0 ){
        VdbeOp *pOp = sqliteVdbeGetOp(v, addr-2);
        if( pOp->opcode==OP_Integer && pOp->p1==pTab->iDb ){
          useTempTable = 1;
        }
      }
    }

    if( useTempTable ){
      /* Generate the subroutine that SELECT calls to process each row of
      ** the result.  Store the result in a temporary table
      */
      srcTab = pParse->nTab++;
      sqliteVdbeResolveLabel(v, iInsertBlock);
      sqliteVdbeAddOp(v, OP_MakeRecord, nColumn, 0);
      sqliteVdbeAddOp(v, OP_NewRecno, srcTab, 0);
      sqliteVdbeAddOp(v, OP_Pull, 1, 0);
      sqliteVdbeAddOp(v, OP_PutIntKey, srcTab, 0);
      sqliteVdbeAddOp(v, OP_Return, 0, 0);

      /* The following code runs first because the GOTO at the very top
      ** of the program jumps to it.  Create the temporary table, then jump
      ** back up and execute the SELECT code above.
      */
      sqliteVdbeChangeP2(v, iInitCode, sqliteVdbeCurrentAddr(v));
      sqliteVdbeAddOp(v, OP_OpenTemp, srcTab, 0);
      sqliteVdbeAddOp(v, OP_Goto, 0, iSelectLoop);
      sqliteVdbeResolveLabel(v, iCleanup);
    }else{
      sqliteVdbeChangeP2(v, iInitCode, sqliteVdbeCurrentAddr(v));
    }
  }else{
    /* This is the case if the data for the INSERT is coming from a VALUES
    ** clause
    */
    SrcList dummy;
    assert( pList!=0 );
    srcTab = -1;
    useTempTable = 0;
    assert( pList );
    nColumn = pList->nExpr;
    dummy.nSrc = 0;
    for(i=0; i<nColumn; i++){
      if( sqliteExprResolveIds(pParse, &dummy, 0, pList->a[i].pExpr) ){
        goto insert_cleanup;
      }
      if( sqliteExprCheck(pParse, pList->a[i].pExpr, 0, 0) ){
        goto insert_cleanup;
      }
    }
  }

  /* Make sure the number of columns in the source data matches the number
  ** of columns to be inserted into the table.
  */
  if( pColumn==0 && nColumn!=pTab->nCol ){
    sqliteErrorMsg(pParse, 
       "table %S has %d columns but %d values were supplied",
       pTabList, 0, pTab->nCol, nColumn);
    goto insert_cleanup;
  }
  if( pColumn!=0 && nColumn!=pColumn->nId ){
    sqliteErrorMsg(pParse, "%d values for %d columns", nColumn, pColumn->nId);
    goto insert_cleanup;
  }

  /* If the INSERT statement included an IDLIST term, then make sure
  ** all elements of the IDLIST really are columns of the table and 
  ** remember the column indices.
  **
  ** If the table has an INTEGER PRIMARY KEY column and that column
  ** is named in the IDLIST, then record in the keyColumn variable
  ** the index into IDLIST of the primary key column.  keyColumn is
  ** the index of the primary key as it appears in IDLIST, not as
  ** is appears in the original table.  (The index of the primary
  ** key in the original table is pTab->iPKey.)
  */
  if( pColumn ){
    for(i=0; i<pColumn->nId; i++){
      pColumn->a[i].idx = -1;
    }
    for(i=0; i<pColumn->nId; i++){
      for(j=0; j<pTab->nCol; j++){
        if( sqliteStrICmp(pColumn->a[i].zName, pTab->aCol[j].zName)==0 ){
          pColumn->a[i].idx = j;
          if( j==pTab->iPKey ){
            keyColumn = i;
          }
          break;
        }
      }
      if( j>=pTab->nCol ){
        if( sqliteIsRowid(pColumn->a[i].zName) ){
          keyColumn = i;
        }else{
          sqliteErrorMsg(pParse, "table %S has no column named %s",
              pTabList, 0, pColumn->a[i].zName);
          pParse->nErr++;
          goto insert_cleanup;
        }
      }
    }
  }

  /* If there is no IDLIST term but the table has an integer primary
  ** key, the set the keyColumn variable to the primary key column index
  ** in the original table definition.
  */
  if( pColumn==0 ){
    keyColumn = pTab->iPKey;
  }

  /* Open the temp table for FOR EACH ROW triggers
  */
  if( row_triggers_exist ){
    sqliteVdbeAddOp(v, OP_OpenPseudo, newIdx, 0);
  }
    
  /* Initialize the count of rows to be inserted
  */
  if( db->flags & SQLITE_CountRows ){
    iCntMem = pParse->nMem++;
    sqliteVdbeAddOp(v, OP_Integer, 0, 0);
    sqliteVdbeAddOp(v, OP_MemStore, iCntMem, 1);
  }

  /* Open tables and indices if there are no row triggers */
  if( !row_triggers_exist ){
    base = pParse->nTab;
    idx = sqliteOpenTableAndIndices(pParse, pTab, base);
    pParse->nTab += idx;
  }

  /* If the data source is a temporary table, then we have to create
  ** a loop because there might be multiple rows of data.  If the data
  ** source is a subroutine call from the SELECT statement, then we need
  ** to launch the SELECT statement processing.
  */
  if( useTempTable ){
    iBreak = sqliteVdbeMakeLabel(v);
    sqliteVdbeAddOp(v, OP_Rewind, srcTab, iBreak);
    iCont = sqliteVdbeCurrentAddr(v);
  }else if( pSelect ){
    sqliteVdbeAddOp(v, OP_Goto, 0, iSelectLoop);
    sqliteVdbeResolveLabel(v, iInsertBlock);
  }

  /* Run the BEFORE and INSTEAD OF triggers, if there are any
  */
  endOfLoop = sqliteVdbeMakeLabel(v);
  if( before_triggers ){

    /* build the NEW.* reference row.  Note that if there is an INTEGER
    ** PRIMARY KEY into which a NULL is being inserted, that NULL will be
    ** translated into a unique ID for the row.  But on a BEFORE trigger,
    ** we do not know what the unique ID will be (because the insert has
    ** not happened yet) so we substitute a rowid of -1
    */
    if( keyColumn<0 ){
      sqliteVdbeAddOp(v, OP_Integer, -1, 0);
    }else if( useTempTable ){
      sqliteVdbeAddOp(v, OP_Column, srcTab, keyColumn);
    }else if( pSelect ){
      sqliteVdbeAddOp(v, OP_Dup, nColumn - keyColumn - 1, 1);
    }else{
      sqliteExprCode(pParse, pList->a[keyColumn].pExpr);
      sqliteVdbeAddOp(v, OP_NotNull, -1, sqliteVdbeCurrentAddr(v)+3);
      sqliteVdbeAddOp(v, OP_Pop, 1, 0);
      sqliteVdbeAddOp(v, OP_Integer, -1, 0);
      sqliteVdbeAddOp(v, OP_MustBeInt, 0, 0);
    }

    /* Create the new column data
    */
    for(i=0; i<pTab->nCol; i++){
      if( pColumn==0 ){
        j = i;
      }else{
        for(j=0; j<pColumn->nId; j++){
          if( pColumn->a[j].idx==i ) break;
        }
      }
      if( pColumn && j>=pColumn->nId ){
        sqliteVdbeOp3(v, OP_String, 0, 0, pTab->aCol[i].zDflt, P3_STATIC);
      }else if( useTempTable ){
        sqliteVdbeAddOp(v, OP_Column, srcTab, j); 
      }else if( pSelect ){
        sqliteVdbeAddOp(v, OP_Dup, nColumn-j-1, 1);
      }else{
        sqliteExprCode(pParse, pList->a[j].pExpr);
      }
    }
    sqliteVdbeAddOp(v, OP_MakeRecord, pTab->nCol, 0);
    sqliteVdbeAddOp(v, OP_PutIntKey, newIdx, 0);

    /* Fire BEFORE or INSTEAD OF triggers */
    if( sqliteCodeRowTrigger(pParse, TK_INSERT, 0, TK_BEFORE, pTab, 
        newIdx, -1, onError, endOfLoop) ){
      goto insert_cleanup;
    }
  }

  /* If any triggers exists, the opening of tables and indices is deferred
  ** until now.
  */
  if( row_triggers_exist && !isView ){
    base = pParse->nTab;
    idx = sqliteOpenTableAndIndices(pParse, pTab, base);
    pParse->nTab += idx;
  }

  /* Push the record number for the new entry onto the stack.  The
  ** record number is a randomly generate integer created by NewRecno
  ** except when the table has an INTEGER PRIMARY KEY column, in which
  ** case the record number is the same as that column. 
  */
  if( !isView ){
    if( keyColumn>=0 ){
      if( useTempTable ){
        sqliteVdbeAddOp(v, OP_Column, srcTab, keyColumn);
      }else if( pSelect ){
        sqliteVdbeAddOp(v, OP_Dup, nColumn - keyColumn - 1, 1);
      }else{
        sqliteExprCode(pParse, pList->a[keyColumn].pExpr);
      }
      /* If the PRIMARY KEY expression is NULL, then use OP_NewRecno
      ** to generate a unique primary key value.
      */
      sqliteVdbeAddOp(v, OP_NotNull, -1, sqliteVdbeCurrentAddr(v)+3);
      sqliteVdbeAddOp(v, OP_Pop, 1, 0);
      sqliteVdbeAddOp(v, OP_NewRecno, base, 0);
      sqliteVdbeAddOp(v, OP_MustBeInt, 0, 0);
    }else{
      sqliteVdbeAddOp(v, OP_NewRecno, base, 0);
    }

    /* Push onto the stack, data for all columns of the new entry, beginning
    ** with the first column.
    */
    for(i=0; i<pTab->nCol; i++){
      if( i==pTab->iPKey ){
        /* The value of the INTEGER PRIMARY KEY column is always a NULL.
        ** Whenever this column is read, the record number will be substituted
        ** in its place.  So will fill this column with a NULL to avoid
        ** taking up data space with information that will never be used. */
        sqliteVdbeAddOp(v, OP_String, 0, 0);
        continue;
      }
      if( pColumn==0 ){
        j = i;
      }else{
        for(j=0; j<pColumn->nId; j++){
          if( pColumn->a[j].idx==i ) break;
        }
      }
      if( pColumn && j>=pColumn->nId ){
        sqliteVdbeOp3(v, OP_String, 0, 0, pTab->aCol[i].zDflt, P3_STATIC);
      }else if( useTempTable ){
        sqliteVdbeAddOp(v, OP_Column, srcTab, j); 
      }else if( pSelect ){
        sqliteVdbeAddOp(v, OP_Dup, i+nColumn-j, 1);
      }else{
        sqliteExprCode(pParse, pList->a[j].pExpr);
      }
    }

    /* Generate code to check constraints and generate index keys and
    ** do the insertion.
    */
    sqliteGenerateConstraintChecks(pParse, pTab, base, 0, keyColumn>=0,
                                   0, onError, endOfLoop);
    sqliteCompleteInsertion(pParse, pTab, base, 0,0,0,
                            after_triggers ? newIdx : -1);
  }

  /* Update the count of rows that are inserted
  */
  if( (db->flags & SQLITE_CountRows)!=0 ){
    sqliteVdbeAddOp(v, OP_MemIncr, iCntMem, 0);
  }

  if( row_triggers_exist ){
    /* Close all tables opened */
    if( !isView ){
      sqliteVdbeAddOp(v, OP_Close, base, 0);
      for(idx=1, pIdx=pTab->pIndex; pIdx; pIdx=pIdx->pNext, idx++){
        sqliteVdbeAddOp(v, OP_Close, idx+base, 0);
      }
    }

    /* Code AFTER triggers */
    if( sqliteCodeRowTrigger(pParse, TK_INSERT, 0, TK_AFTER, pTab, newIdx, -1, 
          onError, endOfLoop) ){
      goto insert_cleanup;
    }
  }

  /* The bottom of the loop, if the data source is a SELECT statement
  */
  sqliteVdbeResolveLabel(v, endOfLoop);
  if( useTempTable ){
    sqliteVdbeAddOp(v, OP_Next, srcTab, iCont);
    sqliteVdbeResolveLabel(v, iBreak);
    sqliteVdbeAddOp(v, OP_Close, srcTab, 0);
  }else if( pSelect ){
    sqliteVdbeAddOp(v, OP_Pop, nColumn, 0);
    sqliteVdbeAddOp(v, OP_Return, 0, 0);
    sqliteVdbeResolveLabel(v, iCleanup);
  }

  if( !row_triggers_exist ){
    /* Close all tables opened */
    sqliteVdbeAddOp(v, OP_Close, base, 0);
    for(idx=1, pIdx=pTab->pIndex; pIdx; pIdx=pIdx->pNext, idx++){
      sqliteVdbeAddOp(v, OP_Close, idx+base, 0);
    }
  }

  sqliteVdbeAddOp(v, OP_SetCounts, 0, 0);
  sqliteEndWriteOperation(pParse);

  /*
  ** Return the number of rows inserted.
  */
  if( db->flags & SQLITE_CountRows ){
    sqliteVdbeOp3(v, OP_ColumnName, 0, 1, "rows inserted", P3_STATIC);
    sqliteVdbeAddOp(v, OP_MemLoad, iCntMem, 0);
    sqliteVdbeAddOp(v, OP_Callback, 1, 0);
  }

insert_cleanup:
  sqliteSrcListDelete(pTabList);
  if( pList ) sqliteExprListDelete(pList);
  if( pSelect ) sqliteSelectDelete(pSelect);
  sqliteIdListDelete(pColumn);
}

/*
** Generate code to do a constraint check prior to an INSERT or an UPDATE.
**
** When this routine is called, the stack contains (from bottom to top)
** the following values:
**
**    1.  The recno of the row to be updated before the update.  This
**        value is omitted unless we are doing an UPDATE that involves a
**        change to the record number.
**
**    2.  The recno of the row after the update.
**
**    3.  The data in the first column of the entry after the update.
**
**    i.  Data from middle columns...
**
**    N.  The data in the last column of the entry after the update.
**
** The old recno shown as entry (1) above is omitted unless both isUpdate
** and recnoChng are 1.  isUpdate is true for UPDATEs and false for
** INSERTs and recnoChng is true if the record number is being changed.
**
** The code generated by this routine pushes additional entries onto
** the stack which are the keys for new index entries for the new record.
** The order of index keys is the same as the order of the indices on
** the pTable->pIndex list.  A key is only created for index i if 
** aIdxUsed!=0 and aIdxUsed[i]!=0.
**
** This routine also generates code to check constraints.  NOT NULL,
** CHECK, and UNIQUE constraints are all checked.  If a constraint fails,
** then the appropriate action is performed.  There are five possible
** actions: ROLLBACK, ABORT, FAIL, REPLACE, and IGNORE.
**
**  Constraint type  Action       What Happens
**  ---------------  ----------   ----------------------------------------
**  any              ROLLBACK     The current transaction is rolled back and
**                                sqlite_exec() returns immediately with a
**                                return code of SQLITE_CONSTRAINT.
**
**  any              ABORT        Back out changes from the current command
**                                only (do not do a complete rollback) then
**                                cause sqlite_exec() to return immediately
**                                with SQLITE_CONSTRAINT.
**
**  any              FAIL         Sqlite_exec() returns immediately with a
**                                return code of SQLITE_CONSTRAINT.  The
**                                transaction is not rolled back and any
**                                prior changes are retained.
**
**  any              IGNORE       The record number and data is popped from
**                                the stack and there is an immediate jump
**                                to label ignoreDest.
**
**  NOT NULL         REPLACE      The NULL value is replace by the default
**                                value for that column.  If the default value
**                                is NULL, the action is the same as ABORT.
**
**  UNIQUE           REPLACE      The other row that conflicts with the row
**                                being inserted is removed.
**
**  CHECK            REPLACE      Illegal.  The results in an exception.
**
** Which action to take is determined by the overrideError parameter.
** Or if overrideError==OE_Default, then the pParse->onError parameter
** is used.  Or if pParse->onError==OE_Default then the onError value
** for the constraint is used.
**
** The calling routine must open a read/write cursor for pTab with
** cursor number "base".  All indices of pTab must also have open
** read/write cursors with cursor number base+i for the i-th cursor.
** Except, if there is no possibility of a REPLACE action then
** cursors do not need to be open for indices where aIdxUsed[i]==0.
**
** If the isUpdate flag is true, it means that the "base" cursor is
** initially pointing to an entry that is being updated.  The isUpdate
** flag causes extra code to be generated so that the "base" cursor
** is still pointing at the same entry after the routine returns.
** Without the isUpdate flag, the "base" cursor might be moved.
*/
void sqliteGenerateConstraintChecks(
  Parse *pParse,      /* The parser context */
  Table *pTab,        /* the table into which we are inserting */
  int base,           /* Index of a read/write cursor pointing at pTab */
  char *aIdxUsed,     /* Which indices are used.  NULL means all are used */
  int recnoChng,      /* True if the record number will change */
  int isUpdate,       /* True for UPDATE, False for INSERT */
  int overrideError,  /* Override onError to this if not OE_Default */
  int ignoreDest      /* Jump to this label on an OE_Ignore resolution */
){
  int i;
  Vdbe *v;
  int nCol;
  int onError;
  int addr;
  int extra;
  int iCur;
  Index *pIdx;
  int seenReplace = 0;
  int jumpInst1, jumpInst2;
  int contAddr;
  int hasTwoRecnos = (isUpdate && recnoChng);

  v = sqliteGetVdbe(pParse);
  assert( v!=0 );
  assert( pTab->pSelect==0 );  /* This table is not a VIEW */
  nCol = pTab->nCol;

  /* Test all NOT NULL constraints.
  */
  for(i=0; i<nCol; i++){
    if( i==pTab->iPKey ){
      continue;
    }
    onError = pTab->aCol[i].notNull;
    if( onError==OE_None ) continue;
    if( overrideError!=OE_Default ){
      onError = overrideError;
    }else if( pParse->db->onError!=OE_Default ){
      onError = pParse->db->onError;
    }else if( onError==OE_Default ){
      onError = OE_Abort;
    }
    if( onError==OE_Replace && pTab->aCol[i].zDflt==0 ){
      onError = OE_Abort;
    }
    sqliteVdbeAddOp(v, OP_Dup, nCol-1-i, 1);
    addr = sqliteVdbeAddOp(v, OP_NotNull, 1, 0);
    switch( onError ){
      case OE_Rollback:
      case OE_Abort:
      case OE_Fail: {
        char *zMsg = 0;
        sqliteVdbeAddOp(v, OP_Halt, SQLITE_CONSTRAINT, onError);
        sqliteSetString(&zMsg, pTab->zName, ".", pTab->aCol[i].zName,
                        " may not be NULL", (char*)0);
        sqliteVdbeChangeP3(v, -1, zMsg, P3_DYNAMIC);
        break;
      }
      case OE_Ignore: {
        sqliteVdbeAddOp(v, OP_Pop, nCol+1+hasTwoRecnos, 0);
        sqliteVdbeAddOp(v, OP_Goto, 0, ignoreDest);
        break;
      }
      case OE_Replace: {
        sqliteVdbeOp3(v, OP_String, 0, 0, pTab->aCol[i].zDflt, P3_STATIC);
        sqliteVdbeAddOp(v, OP_Push, nCol-i, 0);
        break;
      }
      default: assert(0);
    }
    sqliteVdbeChangeP2(v, addr, sqliteVdbeCurrentAddr(v));
  }

  /* Test all CHECK constraints
  */
  /**** TBD ****/

  /* If we have an INTEGER PRIMARY KEY, make sure the primary key
  ** of the new record does not previously exist.  Except, if this
  ** is an UPDATE and the primary key is not changing, that is OK.
  */
  if( recnoChng ){
    onError = pTab->keyConf;
    if( overrideError!=OE_Default ){
      onError = overrideError;
    }else if( pParse->db->onError!=OE_Default ){
      onError = pParse->db->onError;
    }else if( onError==OE_Default ){
      onError = OE_Abort;
    }
    
    if( isUpdate ){
      sqliteVdbeAddOp(v, OP_Dup, nCol+1, 1);
      sqliteVdbeAddOp(v, OP_Dup, nCol+1, 1);
      jumpInst1 = sqliteVdbeAddOp(v, OP_Eq, 0, 0);
    }
    sqliteVdbeAddOp(v, OP_Dup, nCol, 1);
    jumpInst2 = sqliteVdbeAddOp(v, OP_NotExists, base, 0);
    switch( onError ){
      default: {
        onError = OE_Abort;
        /* Fall thru into the next case */
      }
      case OE_Rollback:
      case OE_Abort:
      case OE_Fail: {
        sqliteVdbeOp3(v, OP_Halt, SQLITE_CONSTRAINT, onError,
                         "PRIMARY KEY must be unique", P3_STATIC);
        break;
      }
      case OE_Replace: {
        sqliteGenerateRowIndexDelete(pParse->db, v, pTab, base, 0);
        if( isUpdate ){
          sqliteVdbeAddOp(v, OP_Dup, nCol+hasTwoRecnos, 1);
          sqliteVdbeAddOp(v, OP_MoveTo, base, 0);
        }
        seenReplace = 1;
        break;
      }
      case OE_Ignore: {
        assert( seenReplace==0 );
        sqliteVdbeAddOp(v, OP_Pop, nCol+1+hasTwoRecnos, 0);
        sqliteVdbeAddOp(v, OP_Goto, 0, ignoreDest);
        break;
      }
    }
    contAddr = sqliteVdbeCurrentAddr(v);
    sqliteVdbeChangeP2(v, jumpInst2, contAddr);
    if( isUpdate ){
      sqliteVdbeChangeP2(v, jumpInst1, contAddr);
      sqliteVdbeAddOp(v, OP_Dup, nCol+1, 1);
      sqliteVdbeAddOp(v, OP_MoveTo, base, 0);
    }
  }

  /* Test all UNIQUE constraints by creating entries for each UNIQUE
  ** index and making sure that duplicate entries do not already exist.
  ** Add the new records to the indices as we go.
  */
  extra = -1;
  for(iCur=0, pIdx=pTab->pIndex; pIdx; pIdx=pIdx->pNext, iCur++){
    if( aIdxUsed && aIdxUsed[iCur]==0 ) continue;  /* Skip unused indices */
    extra++;

    /* Create a key for accessing the index entry */
    sqliteVdbeAddOp(v, OP_Dup, nCol+extra, 1);
    for(i=0; i<pIdx->nColumn; i++){
      int idx = pIdx->aiColumn[i];
      if( idx==pTab->iPKey ){
        sqliteVdbeAddOp(v, OP_Dup, i+extra+nCol+1, 1);
      }else{
        sqliteVdbeAddOp(v, OP_Dup, i+extra+nCol-idx, 1);
      }
    }
    jumpInst1 = sqliteVdbeAddOp(v, OP_MakeIdxKey, pIdx->nColumn, 0);
    if( pParse->db->file_format>=4 ) sqliteAddIdxKeyType(v, pIdx);

    /* Find out what action to take in case there is an indexing conflict */
    onError = pIdx->onError;
    if( onError==OE_None ) continue;  /* pIdx is not a UNIQUE index */
    if( overrideError!=OE_Default ){
      onError = overrideError;
    }else if( pParse->db->onError!=OE_Default ){
      onError = pParse->db->onError;
    }else if( onError==OE_Default ){
      onError = OE_Abort;
    }
    if( seenReplace ){
      if( onError==OE_Ignore ) onError = OE_Replace;
      else if( onError==OE_Fail ) onError = OE_Abort;
    }
    

    /* Check to see if the new index entry will be unique */
    sqliteVdbeAddOp(v, OP_Dup, extra+nCol+1+hasTwoRecnos, 1);
    jumpInst2 = sqliteVdbeAddOp(v, OP_IsUnique, base+iCur+1, 0);

    /* Generate code that executes if the new index entry is not unique */
    switch( onError ){
      case OE_Rollback:
      case OE_Abort:
      case OE_Fail: {
        int j, n1, n2;
        char zErrMsg[200];
        strcpy(zErrMsg, pIdx->nColumn>1 ? "columns " : "column ");
        n1 = strlen(zErrMsg);
        for(j=0; j<pIdx->nColumn && n1<sizeof(zErrMsg)-30; j++){
          char *zCol = pTab->aCol[pIdx->aiColumn[j]].zName;
          n2 = strlen(zCol);
          if( j>0 ){
            strcpy(&zErrMsg[n1], ", ");
            n1 += 2;
          }
          if( n1+n2>sizeof(zErrMsg)-30 ){
            strcpy(&zErrMsg[n1], "...");
            n1 += 3;
            break;
          }else{
            strcpy(&zErrMsg[n1], zCol);
            n1 += n2;
          }
        }
        strcpy(&zErrMsg[n1], 
            pIdx->nColumn>1 ? " are not unique" : " is not unique");
        sqliteVdbeOp3(v, OP_Halt, SQLITE_CONSTRAINT, onError, zErrMsg, 0);
        break;
      }
      case OE_Ignore: {
        assert( seenReplace==0 );
        sqliteVdbeAddOp(v, OP_Pop, nCol+extra+3+hasTwoRecnos, 0);
        sqliteVdbeAddOp(v, OP_Goto, 0, ignoreDest);
        break;
      }
      case OE_Replace: {
        sqliteGenerateRowDelete(pParse->db, v, pTab, base, 0);
        if( isUpdate ){
          sqliteVdbeAddOp(v, OP_Dup, nCol+extra+1+hasTwoRecnos, 1);
          sqliteVdbeAddOp(v, OP_MoveTo, base, 0);
        }
        seenReplace = 1;
        break;
      }
      default: assert(0);
    }
    contAddr = sqliteVdbeCurrentAddr(v);
#if NULL_DISTINCT_FOR_UNIQUE
    sqliteVdbeChangeP2(v, jumpInst1, contAddr);
#endif
    sqliteVdbeChangeP2(v, jumpInst2, contAddr);
  }
}

/*
** This routine generates code to finish the INSERT or UPDATE operation
** that was started by a prior call to sqliteGenerateConstraintChecks.
** The stack must contain keys for all active indices followed by data
** and the recno for the new entry.  This routine creates the new
** entries in all indices and in the main table.
**
** The arguments to this routine should be the same as the first six
** arguments to sqliteGenerateConstraintChecks.
*/
void sqliteCompleteInsertion(
  Parse *pParse,      /* The parser context */
  Table *pTab,        /* the table into which we are inserting */
  int base,           /* Index of a read/write cursor pointing at pTab */
  char *aIdxUsed,     /* Which indices are used.  NULL means all are used */
  int recnoChng,      /* True if the record number will change */
  int isUpdate,       /* True for UPDATE, False for INSERT */
  int newIdx          /* Index of NEW table for triggers.  -1 if none */
){
  int i;
  Vdbe *v;
  int nIdx;
  Index *pIdx;

  v = sqliteGetVdbe(pParse);
  assert( v!=0 );
  assert( pTab->pSelect==0 );  /* This table is not a VIEW */
  for(nIdx=0, pIdx=pTab->pIndex; pIdx; pIdx=pIdx->pNext, nIdx++){}
  for(i=nIdx-1; i>=0; i--){
    if( aIdxUsed && aIdxUsed[i]==0 ) continue;
    sqliteVdbeAddOp(v, OP_IdxPut, base+i+1, 0);
  }
  sqliteVdbeAddOp(v, OP_MakeRecord, pTab->nCol, 0);
  if( newIdx>=0 ){
    sqliteVdbeAddOp(v, OP_Dup, 1, 0);
    sqliteVdbeAddOp(v, OP_Dup, 1, 0);
    sqliteVdbeAddOp(v, OP_PutIntKey, newIdx, 0);
  }
  sqliteVdbeAddOp(v, OP_PutIntKey, base,
    (pParse->trigStack?0:OPFLAG_NCHANGE) |
    (isUpdate?0:OPFLAG_LASTROWID) | OPFLAG_CSCHANGE);
  if( isUpdate && recnoChng ){
    sqliteVdbeAddOp(v, OP_Pop, 1, 0);
  }
}

/*
** Generate code that will open write cursors for a table and for all
** indices of that table.  The "base" parameter is the cursor number used
** for the table.  Indices are opened on subsequent cursors.
**
** Return the total number of cursors opened.  This is always at least
** 1 (for the main table) plus more for each cursor.
*/
int sqliteOpenTableAndIndices(Parse *pParse, Table *pTab, int base){
  int i;
  Index *pIdx;
  Vdbe *v = sqliteGetVdbe(pParse);
  assert( v!=0 );
  sqliteVdbeAddOp(v, OP_Integer, pTab->iDb, 0);
  sqliteVdbeOp3(v, OP_OpenWrite, base, pTab->tnum, pTab->zName, P3_STATIC);
  for(i=1, pIdx=pTab->pIndex; pIdx; pIdx=pIdx->pNext, i++){
    sqliteVdbeAddOp(v, OP_Integer, pIdx->iDb, 0);
    sqliteVdbeOp3(v, OP_OpenWrite, i+base, pIdx->tnum, pIdx->zName, P3_STATIC);
  }
  return i;
}
