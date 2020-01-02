<?php
$BEAUT_PATH = realpath(".") . "/Services/COPage/syntax_highlight/php";
if (!isset($BEAUT_PATH)) {
    return;
}
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_foxpro extends HFile
  {
      public function HFile_foxpro()
      {
          $this->HFile();
          /*************************************/
          // Beautifier Highlighting Configuration File
          // FoxPro
          /*************************************/
          // Flags

          $this->nocase            	= "1";
          $this->notrim            	= "0";
          $this->perl              	= "0";

          // Colours

          $this->colours        	= array("blue", "gray", "purple");
          $this->quotecolour       	= "blue";
          $this->blockcommentcolour	= "green";
          $this->linecommentcolour 	= "green";

          // Indent Strings

          $this->indent            	= array();
          $this->unindent          	= array();

          // String characters and delimiters

          $this->stringchars       	= array();
          $this->delimiters        	= array();
          $this->escchar           	= "";

          // Comment settings

          $this->linecommenton     	= array("*");
          $this->blockcommenton    	= array("&&");
          $this->blockcommentoff   	= array("");

          // Keywords (keyword mapping to colour number)

          $this->keywords          	= array(
            "ACCEPT" => "1",
            "ACTIVATE" => "1",
            "ALTERNATIVE" => "1",
            "AMERICAN" => "1",
            "AND" => "3",
            "ANSI" => "1",
            "APPEND" => "1",
            "ARRAY" => "1",
            "AUTOSAVE" => "1",
            "AVERAGE" => "1",
            "BAR" => "2",
            "BELL" => "1",
            "BLANK" => "1",
            "BLICK" => "1",
            "BLOCKSIZE" => "1",
            "BOX" => "1",
            "BORDER" => "1",
            "BROWSE" => "1",
            "BRSTATUS" => "1",
            "BUILD" => "1",
            "EXE" => "1",
            "PROJECT" => "1",
            "CALCULATE" => "1",
            "CALL" => "1",
            "CANCEL" => "1",
            "CARRY" => "1",
            "CASE" => "1",
            "CENTURY" => "1",
            "CHANGE" => "1",
            "CLEAR" => "1",
            "CLOCK" => "1",
            "CLOSE" => "1",
            "COLLATE" => "1",
            "COLOR" => "1",
            "COMMAND" => "1",
            "COMPILE" => "1",
            "COMPATABLE" => "1",
            "CONFIRM" => "1",
            "CONSOLE" => "1",
            "CONTINUE" => "1",
            "COPY" => "1",
            "COUNT" => "1",
            "CPCOMPILE" => "1",
            "CPDIALOG" => "1",
            "CREATE" => "1",
            "CURRENCY" => "1",
            "CURSOR" => "1",
            "DATABASES" => "1",
            "DATE" => "2",
            "DEACTIVATE" => "1",
            "DEBUG" => "1",
            "DECIMALS" => "1",
            "DECLARE" => "1",
            "DEFAULT" => "1",
            "DEFINE" => "1",
            "DELETE" => "1",
            "DELETED" => "2",
            "DELIMITERS" => "1",
            "DEVELOPMENT" => "1",
            "DEVICE" => "1",
            "DIMENSION" => "1",
            "DIR" => "1",
            "DIRECTORY" => "1",
            "DISPLAY" => "1",
            "DO" => "1",
            "DOHISTORY" => "1",
            "ECHO" => "1",
            "EDIT" => "1",
            "EJECT" => "1",
            "ELSE" => "1",
            "ENDCASE" => "1",
            "ENDDO" => "1",
            "ENDFOR" => "1",
            "ENDSCAN" => "1",
            "ENDTEXT" => "1",
            "ENDIF" => "1",
            "ENDPRINTJOB" => "1",
            "ERASE" => "1",
            "ERROR" => "2",
            "ESCAPE" => "1",
            "EXACT" => "1",
            "EXCLUSIVE" => "1",
            "EXIT" => "1",
            "EXPORT" => "1",
            "EXTENDED" => "1",
            "EXTERNAL" => "1",
            "FIELDS" => "1",
            "FILES" => "1",
            "FILER" => "1",
            "FILL" => "1",
            "FILTER" => "2",
            "FIND" => "1",
            "FIXED" => "1",
            "FORMAT" => "1",
            "FLUSH" => "1",
            "FOR" => "2",
            "FROM" => "1",
            "FULLPATH" => "2",
            "FUNCTION" => "1",
            "GATHER" => "1",
            "GENERAL" => "1",
            "GET" => "1",
            "GETS" => "1",
            "GETEXPR" => "1",
            "GO" => "1",
            "GOTO" => "1",
            "HEADINGS" => "1",
            "HELP" => "1",
            "HELPFILTER" => "1",
            "HIDE" => "1",
            "HOURS" => "1",
            "IF" => "1",
            "IMPORT" => "1",
            "INDEX" => "1",
            "INDEXES" => "1",
            "INPUT" => "1",
            "INSERT" => "1",
            "INTENSITY" => "1",
            "JOIN" => "1",
            "KEY" => "2",
            "KEYBOARD" => "1",
            "KEYCOMP" => "1",
            "LABEL" => "1",
            "LIBRARY" => "1",
            "LIST" => "1",
            "LOAD" => "1",
            "LOCATE" => "1",
            "LOCK" => "2",
            "LOGERRORS" => "1",
            "MACKEY" => "1",
            "MACRO" => "1",
            "MARGIN" => "1",
            "MARK" => "1",
            "MEMO" => "1",
            "MEMOWIDTH" => "1",
            "MENU" => "2",
            "MESSAGE" => "2",
            "MODIFY" => "1",
            "MODULE" => "1",
            "MOUSE" => "1",
            "MOVE" => "1",
            "MULTILOCKS" => "1",
            "NEAR" => "1",
            "NOCPTRANS" => "1",
            "NORMALIZE" => "1",
            "NOTE" => "1",
            "NOTIFY" => "1",
            "OBJECT" => "1",
            "ODOMETER" => "1",
            "OFF" => "1",
            "ON" => "2",
            "OPTIMIZE" => "1",
            "ORDER" => "2",
            "OTHERWISE" => "1",
            "PAD" => "2",
            "PAGE" => "1",
            "PALETTE" => "1",
            "PARAMETERS" => "2",
            "PATH" => "1",
            "PDSETUP" => "1",
            "PICTURE" => "1",
            "PLAY" => "1",
            "POINT" => "1",
            "POP" => "1",
            "POPUP" => "2",
            "PRINTER" => "1",
            "PRINTJOB" => "1",
            "PRIVATE" => "1",
            "PROCEDURE" => "1",
            "PROMPT" => "2",
            "PUBLIC" => "1",
            "PUSH" => "1",
            "QUERY" => "1",
            "READ" => "1",
            "READBORDER" => "1",
            "READERROR" => "1",
            "RECALL" => "1",
            "REGIONAL" => "1",
            "REINDEX" => "1",
            "RELEASE" => "1",
            "RENAME" => "1",
            "REPLACE" => "1",
            "REPORT" => "1",
            "RESTORE" => "1",
            "RESUME" => "1",
            "RETRY" => "1",
            "RETURN" => "1",
            "REFRESH" => "1",
            "RELATION" => "2",
            "REPROCESS" => "1",
            "RESOURCE" => "1",
            "RUN" => "1",
            "SAFETY" => "1",
            "SAVE" => "1",
            "SAY" => "1",
            "SCAN" => "1",
            "SCATTER" => "1",
            "SCHEME" => "2",
            "SCOREBOARD" => "1",
            "SCREEN" => "1",
            "SEEK" => "2",
            "SELECT" => "2",
            "SELECTION" => "1",
            "SEPERATOR" => "1",
            "SET" => "2",
            "SHADOWS" => "1",
            "SHOW" => "1",
            "SHUTDOWN" => "1",
            "SIZE" => "2",
            "SKIP" => "1",
            "SORT" => "1",
            "STATUS" => "1",
            "STICKY" => "1",
            "STEP" => "1",
            "STORE" => "1",
            "STRUCTURE" => "1",
            "SUM" => "1",
            "SUSPEND" => "1",
            "SYSMENU" => "1",
            "SQL" => "1",
            "TABLE" => "1",
            "TAG" => "2",
            "TALK" => "1",
            "TEXTMERGE" => "1",
            "TEXT" => "1",
            "TO" => "1",
            "TOPIC" => "1",
            "TOTAL" => "1",
            "TRBETWEEN" => "1",
            "TYPE" => "2",
            "TYPEAHEAD" => "1",
            "UDFPARAMS" => "1",
            "UNIQUE" => "1",
            "UNLOCK" => "1",
            "UPDATE" => "1",
            "USE" => "1",
            "VIEW" => "1",
            "WAIT" => "1",
            "WHILE" => "1",
            "WINDOW" => "1",
            "WITH" => "1",
            "ZAP" => "1",
            "ZOOM" => "1",
            "ABS" => "2",
            "ACOPY" => "2",
            "ACOS" => "2",
            "ADEL" => "2",
            "ADIR" => "2",
            "AELEMENT" => "2",
            "AFIELDS" => "2",
            "AFONT" => "2",
            "AINS" => "2",
            "ALEN" => "2",
            "ALIAS" => "2",
            "ALLTRIM" => "2",
            "ANSITOOEM" => "2",
            "ASC" => "2",
            "ASCAN" => "2",
            "ASIN" => "2",
            "ASORT" => "2",
            "ASUBSCRIPT" => "2",
            "AT" => "2",
            "ATAN" => "2",
            "ATC" => "2",
            "ATCLINE" => "2",
            "ATLINE" => "2",
            "ATN2" => "2",
            "BETWEEN" => "2",
            "BOF" => "2",
            "CAPSLOCK" => "2",
            "CDOW" => "2",
            "CDX" => "2",
            "CEILING" => "2",
            "CHR" => "2",
            "CHRSAW" => "2",
            "CHRTRAN" => "2",
            "CMONTH" => "2",
            "CNTBAR" => "2",
            "CNTPAD" => "2",
            "COL" => "2",
            "COS" => "2",
            "CPCONVERT" => "2",
            "CPCURRENT" => "2",
            "CPDBF" => "2",
            "CTOD" => "2",
            "CURDIR" => "2",
            "DAY" => "2",
            "DBF" => "2",
            "DDE" => "2",
            "DDEAbortTrans" => "2",
            "DDEAdvise" => "2",
            "DDEEnabled" => "2",
            "DDEExecute" => "2",
            "DDEInitiate" => "2",
            "DDELastError" => "2",
            "DDEPoke" => "2",
            "DDERequest" => "2",
            "DDESetOption" => "2",
            "DDESetService" => "2",
            "DDESetTopic" => "2",
            "DDETerminate" => "2",
            "DESCENDING" => "2",
            "DIFFERENCE" => "2",
            "DISKSPACE" => "2",
            "DMY" => "2",
            "DOW" => "2",
            "DTOC" => "2",
            "DTOR" => "2",
            "DTOS" => "2",
            "EMPTY" => "2",
            "EOF" => "2",
            "EVALUATE" => "2",
            "EXP" => "2",
            "FCHSIZE" => "2",
            "FCLOSE" => "2",
            "FCOUNT" => "2",
            "FCREATE" => "2",
            "FEOF" => "2",
            "FERROR" => "2",
            "FFLUSH" => "2",
            "FGETS" => "2",
            "FIELD" => "2",
            "FILE" => "2",
            "FKLABEL" => "2",
            "FKMAX" => "2",
            "FLOCK" => "2",
            "FLOOR" => "2",
            "FONTMETRIC" => "2",
            "FOPEN" => "2",
            "FOUND" => "2",
            "FPUTS" => "2",
            "FREAD" => "2",
            "FSEEK" => "2",
            "FSIZE" => "2",
            "FV" => "2",
            "FWRITE" => "2",
            "GETBAR" => "2",
            "GETDIR" => "2",
            "GETENV" => "2",
            "GETFILE" => "2",
            "GETFONT" => "2",
            "GETPAD" => "2",
            "GOMONTH" => "2",
            "HEADER" => "2",
            "HOME" => "2",
            "IDXCOLLATE" => "2",
            "IIF" => "2",
            "INKEY" => "2",
            "INLIST" => "2",
            "INSMODE" => "2",
            "INT" => "2",
            "ISALPHA" => "2",
            "ISBLANK" => "2",
            "ISCOLOR" => "2",
            "ISDIGIT" => "2",
            "ISLOWER" => "2",
            "ISREADONLY" => "2",
            "ISUPPER" => "2",
            "KEYMATCH" => "2",
            "LASTKEY" => "2",
            "LEFT" => "2",
            "LEN" => "2",
            "LIKE" => "2",
            "LINENO" => "2",
            "LOCFILE" => "2",
            "LOG" => "2",
            "LOG10" => "2",
            "LOOKUP" => "2",
            "LOWER" => "2",
            "LTRIM" => "2",
            "LUPDATE" => "2",
            "MAX" => "2",
            "MCOL" => "2",
            "MDOWN" => "2",
            "MDX" => "2",
            "MDY" => "2",
            "MEMLINES" => "2",
            "MEMORY" => "2",
            "MIN" => "2",
            "MLINE" => "2",
            "MOD" => "2",
            "MONTH" => "2",
            "MRKBAR" => "2",
            "MRKPAD" => "2",
            "MROW(" => "2",
            "MWINDOW" => "2",
            "NDX" => "2",
            "NUMLOCK" => "2",
            "OBJNUM" => "2",
            "OBJVAR" => "2",
            "OCCURS" => "2",
            "OEMTOANSI" => "2",
            "OS" => "2",
            "PACK" => "2",
            "PADC" => "2",
            "PADL" => "2",
            "PADR" => "2",
            "PAYMENT" => "2",
            "PCOL" => "2",
            "PI" => "2",
            "PRINTSTATUS" => "2",
            "PRMBAR" => "2",
            "PRMPAD" => "2",
            "PROGRAM" => "2",
            "PROPER" => "2",
            "PROW" => "2",
            "PRTINFO�PUTFILE" => "2",
            "PV" => "2",
            "QUIT" => "2",
            "RAND" => "2",
            "RAT" => "2",
            "RATLINE" => "2",
            "RDLEVEL" => "2",
            "READKEY" => "2",
            "RECCOUNT" => "2",
            "RECNO" => "2",
            "RECSIZE" => "2",
            "REPLICATE" => "2",
            "RGBSCHEME" => "2",
            "RIGHT" => "2",
            "RLOCK" => "2",
            "ROUND" => "2",
            "ROW" => "2",
            "RTOD" => "2",
            "RTRIM" => "2",
            "SCOLS" => "2",
            "SCROLL" => "2",
            "SECONDS" => "2",
            "SIGN" => "2",
            "SIN" => "2",
            "SKPBAR" => "2",
            "SKPPAD" => "2",
            "SOUNDEX" => "2",
            "SPACE" => "2",
            "SQRT" => "2",
            "SROWS" => "2",
            "STR" => "2",
            "STRTRAN" => "2",
            "STUFF" => "2",
            "SUBSTR" => "2",
            "SYS" => "2",
            "SYS(0)" => "2",
            "SYS(1)" => "2",
            "SYS(2)" => "2",
            "SYS(3)" => "2",
            "SYS(5)" => "2",
            "SYS(6)" => "2",
            "SYS(7)" => "2",
            "SYS(9)" => "2",
            "SYS(10)" => "2",
            "SYS(11)" => "2",
            "SYS(12)" => "2",
            "SYS(13)" => "2",
            "SYS(14)" => "2",
            "SYS(15)" => "2",
            "SYS(16)" => "2",
            "SYS(17)" => "2",
            "SYS(18)" => "2",
            "SYS(20)" => "2",
            "SYS(21)" => "2",
            "SYS(22)" => "2",
            "SYS(23)" => "2",
            "SYS(24)" => "2",
            "SYS(100)" => "2",
            "SYS(101)" => "2",
            "SYS(102)" => "2",
            "SYS(103)" => "2",
            "SYS(1001)" => "2",
            "SYS(1016)" => "2",
            "SYS(1037)" => "2",
            "SYS(2000)" => "2",
            "SYS(2001)" => "2",
            "SYS(2002)" => "2",
            "SYS(2003)" => "2",
            "SYS(2004)" => "2",
            "SYS(2005)" => "2",
            "SYS(2006)" => "2",
            "SYS(2007)" => "2",
            "SYS(2008)" => "2",
            "SYS(2009)" => "2",
            "SYS(2010)" => "2",
            "SYS(2011)" => "2",
            "SYS(2012)" => "2",
            "SYS(2013)" => "2",
            "SYS(2014)" => "2",
            "SYS(2015)" => "2",
            "SYS(2016)" => "2",
            "SYS(2017)" => "2",
            "SYS(2018)" => "2",
            "SYS(2019)" => "2",
            "SYS(2020)" => "2",
            "SYS(2021)" => "2",
            "SYS(2022)" => "2",
            "SYS(2023)" => "2",
            "SYSMETRIC(" => "2",
            "TAN" => "2",
            "TARGET" => "2",
            "TIME" => "2",
            "TRANSFORM" => "2",
            "TRIM" => "2",
            "TXTWIDTH" => "2",
            "UPDATED" => "2",
            "UPPER" => "2",
            "USED" => "2",
            "VAL" => "2",
            "VALID" => "2",
            "VARREAD" => "2",
            "VERSION" => "2",
            "WBORDER" => "2",
            "WCHILD" => "2",
            "WCOLS" => "2",
            "WEXIST" => "2",
            "WFONT" => "2",
            "WLAST" => "2",
            "WLCOL" => "2",
            "WLROW" => "2",
            "WMAXIMUM" => "2",
            "WMINIMUM" => "2",
            "WONTOP" => "2",
            "WOUTPUT" => "2",
            "WPARENT" => "2",
            "WREAD" => "2",
            "WROWS" => "2",
            "WTITLE" => "2",
            "WVISIBLE" => "2",
            "YEAR" => "2",
            ".AND." => "3",
            ".F." => "3",
            ".NOT." => "3",
            ".OR." => "3",
            ".T." => "3",
            "NOT" => "3",
            "OR" => "3");

          // Special extensions

          // Each category can specify a PHP function that returns an altered
          // version of the keyword.
        
        

          $this->linkscripts    	= array(
            "1" => "donothing",
            "3" => "donothing",
            "2" => "donothing");
      }


      public function donothing($keywordin)
      {
          return $keywordin;
      }
  }
