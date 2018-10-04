/*
** The "printf" code that follows dates from the 1980's.  It is in
** the public domain.  The original comments are included here for
** completeness.  They are very out-of-date but might be useful as
** an historical reference.  Most of the "enhancements" have been backed
** out so that the functionality is now the same as standard printf().
**
**************************************************************************
**
** The following modules is an enhanced replacement for the "printf" subroutines
** found in the standard C library.  The following enhancements are
** supported:
**
**      +  Additional functions.  The standard set of "printf" functions
**         includes printf, fprintf, sprintf, vprintf, vfprintf, and
**         vsprintf.  This module adds the following:
**
**           *  snprintf -- Works like sprintf, but has an extra argument
**                          which is the size of the buffer written to.
**
**           *  mprintf --  Similar to sprintf.  Writes output to memory
**                          obtained from malloc.
**
**           *  xprintf --  Calls a function to dispose of output.
**
**           *  nprintf --  No output, but returns the number of characters
**                          that would have been output by printf.
**
**           *  A v- version (ex: vsnprintf) of every function is also
**              supplied.
**
**      +  A few extensions to the formatting notation are supported:
**
**           *  The "=" flag (similar to "-") causes the output to be
**              be centered in the appropriately sized field.
**
**           *  The %b field outputs an integer in binary notation.
**
**           *  The %c field now accepts a precision.  The character output
**              is repeated by the number of times the precision specifies.
**
**           *  The %' field works like %c, but takes as its character the
**              next character of the format string, instead of the next
**              argument.  For example,  printf("%.78'-")  prints 78 minus
**              signs, the same as  printf("%.78c",'-').
**
**      +  When compiled using GCC on a SPARC, this version of printf is
**         faster than the library printf for SUN OS 4.1.
**
**      +  All functions are fully reentrant.
**
*/
#include "sqliteInt.h"

/*
** Conversion types fall into various categories as defined by the
** following enumeration.
*/
#define etRADIX       1 /* Integer types.  %d, %x, %o, and so forth */
#define etFLOAT       2 /* Floating point.  %f */
#define etEXP         3 /* Exponentional notation. %e and %E */
#define etGENERIC     4 /* Floating or exponential, depending on exponent. %g */
#define etSIZE        5 /* Return number of characters processed so far. %n */
#define etSTRING      6 /* Strings. %s */
#define etDYNSTRING   7 /* Dynamically allocated strings. %z */
#define etPERCENT     8 /* Percent symbol. %% */
#define etCHARX       9 /* Characters. %c */
#define etERROR      10 /* Used to indicate no such conversion type */
/* The rest are extensions, not normally found in printf() */
#define etCHARLIT    11 /* Literal characters.  %' */
#define etSQLESCAPE  12 /* Strings with '\'' doubled.  %q */
#define etSQLESCAPE2 13 /* Strings with '\'' doubled and enclosed in '',
                          NULL pointers replaced by SQL NULL.  %Q */
#define etTOKEN      14 /* a pointer to a Token structure */
#define etSRCLIST    15 /* a pointer to a SrcList */


/*
** An "etByte" is an 8-bit unsigned value.
*/
typedef unsigned char etByte;

/*
** Each builtin conversion character (ex: the 'd' in "%d") is described
** by an instance of the following structure
*/
typedef struct et_info {   /* Information about each format field */
  char fmttype;            /* The format field code letter */
  etByte base;             /* The base for radix conversion */
  etByte flags;            /* One or more of FLAG_ constants below */
  etByte type;             /* Conversion paradigm */
  char *charset;           /* The character set for conversion */
  char *prefix;            /* Prefix on non-zero values in alt format */
} et_info;

/*
** Allowed values for et_info.flags
*/
#define FLAG_SIGNED  1     /* True if the value to convert is signed */
#define FLAG_INTERN  2     /* True if for internal use only */


/*
** The following table is searched linearly, so it is good to put the
** most frequently used conversion types first.
*/
static et_info fmtinfo[] = {
  {  'd', 10, 1, etRADIX,      "0123456789",       0    },
  {  's',  0, 0, etSTRING,     0,                  0    },
  {  'z',  0, 2, etDYNSTRING,  0,                  0    },
  {  'q',  0, 0, etSQLESCAPE,  0,                  0    },
  {  'Q',  0, 0, etSQLESCAPE2, 0,                  0    },
  {  'c',  0, 0, etCHARX,      0,                  0    },
  {  'o',  8, 0, etRADIX,      "01234567",         "0"  },
  {  'u', 10, 0, etRADIX,      "0123456789",       0    },
  {  'x', 16, 0, etRADIX,      "0123456789abcdef", "x0" },
  {  'X', 16, 0, etRADIX,      "0123456789ABCDEF", "X0" },
  {  'f',  0, 1, etFLOAT,      0,                  0    },
  {  'e',  0, 1, etEXP,        "e",                0    },
  {  'E',  0, 1, etEXP,        "E",                0    },
  {  'g',  0, 1, etGENERIC,    "e",                0    },
  {  'G',  0, 1, etGENERIC,    "E",                0    },
  {  'i', 10, 1, etRADIX,      "0123456789",       0    },
  {  'n',  0, 0, etSIZE,       0,                  0    },
  {  '%',  0, 0, etPERCENT,    0,                  0    },
  {  'p', 10, 0, etRADIX,      "0123456789",       0    },
  {  'T',  0, 2, etTOKEN,      0,                  0    },
  {  'S',  0, 2, etSRCLIST,    0,                  0    },
};
#define etNINFO  (sizeof(fmtinfo)/sizeof(fmtinfo[0]))

/*
** If NOFLOATINGPOINT is defined, then none of the floating point
** conversions will work.
*/
#ifndef etNOFLOATINGPOINT
/*
** "*val" is a double such that 0.1 <= *val < 10.0
** Return the ascii code for the leading digit of *val, then
** multiply "*val" by 10.0 to renormalize.
**
** Example:
**     input:     *val = 3.14159
**     output:    *val = 1.4159    function return = '3'
**
** The counter *cnt is incremented each time.  After counter exceeds
** 16 (the number of significant digits in a 64-bit float) '0' is
** always returned.
*/
static int et_getdigit(LONGDOUBLE_TYPE *val, int *cnt){
  int digit;
  LONGDOUBLE_TYPE d;
  if( (*cnt)++ >= 16 ) return '0';
  digit = (int)*val;
  d = digit;
  digit += '0';
  *val = (*val - d)*10.0;
  return digit;
}
#endif

#define etBUFSIZE 1000  /* Size of the output buffer */

/*
** The root program.  All variations call this core.
**
** INPUTS:
**   func   This is a pointer to a function taking three arguments
**            1. A pointer to anything.  Same as the "arg" parameter.
**            2. A pointer to the list of characters to be output
**               (Note, this list is NOT null terminated.)
**            3. An integer number of characters to be output.
**               (Note: This number might be zero.)
**
**   arg    This is the pointer to anything which will be passed as the
**          first argument to "func".  Use it for whatever you like.
**
**   fmt    This is the format string, as in the usual print.
**
**   ap     This is a pointer to a list of arguments.  Same as in
**          vfprint.
**
** OUTPUTS:
**          The return value is the total number of characters sent to
**          the function "func".  Returns -1 on a error.
**
** Note that the order in which automatic variables are declared below
** seems to make a big difference in determining how fast this beast
** will run.
*/
static int vxprintf(
  void (*func)(void*,const char*,int),     /* Consumer of text */
  void *arg,                         /* First argument to the consumer */
  int useExtended,                   /* Allow extended %-conversions */
  const char *fmt,                   /* Format string */
  va_list ap                         /* arguments */
){
  int c;                     /* Next character in the format string */
  char *bufpt;               /* Pointer to the conversion buffer */
  int precision;             /* Precision of the current field */
  int length;                /* Length of the field */
  int idx;                   /* A general purpose loop counter */
  int count;                 /* Total number of characters output */
  int width;                 /* Width of the current field */
  etByte flag_leftjustify;   /* True if "-" flag is present */
  etByte flag_plussign;      /* True if "+" flag is present */
  etByte flag_blanksign;     /* True if " " flag is present */
  etByte flag_alternateform; /* True if "#" flag is present */
  etByte flag_zeropad;       /* True if field width constant starts with zero */
  etByte flag_long;          /* True if "l" flag is present */
  unsigned long longvalue;   /* Value for integer types */
  LONGDOUBLE_TYPE realvalue; /* Value for real types */
  et_info *infop;            /* Pointer to the appropriate info structure */
  char buf[etBUFSIZE];       /* Conversion buffer */
  char prefix;               /* Prefix character.  "+" or "-" or " " or '\0'. */
  etByte errorflag = 0;      /* True if an error is encountered */
  etByte xtype;              /* Conversion paradigm */
  char *zExtra;              /* Extra memory used for etTCLESCAPE conversions */
  static char spaces[] = "                                                  ";
#define etSPACESIZE (sizeof(spaces)-1)
#ifndef etNOFLOATINGPOINT
  int  exp;                  /* exponent of real numbers */
  double rounder;            /* Used for rounding floating point values */
  etByte flag_dp;            /* True if decimal point should be shown */
  etByte flag_rtz;           /* True if trailing zeros should be removed */
  etByte flag_exp;           /* True to force display of the exponent */
  int nsd;                   /* Number of significant digits returned */
#endif

  count = length = 0;
  bufpt = 0;
  for(; (c=(*fmt))!=0; ++fmt){
    if( c!='%' ){
      int amt;
      bufpt = (char *)fmt;
      amt = 1;
      while( (c=(*++fmt))!='%' && c!=0 ) amt++;
      (*func)(arg,bufpt,amt);
      count += amt;
      if( c==0 ) break;
    }
    if( (c=(*++fmt))==0 ){
      errorflag = 1;
      (*func)(arg,"%",1);
      count++;
      break;
    }
    /* Find out what flags are present */
    flag_leftjustify = flag_plussign = flag_blanksign = 
     flag_alternateform = flag_zeropad = 0;
    do{
      switch( c ){
        case '-':   flag_leftjustify = 1;     c = 0;   break;
        case '+':   flag_plussign = 1;        c = 0;   break;
        case ' ':   flag_blanksign = 1;       c = 0;   break;
        case '#':   flag_alternateform = 1;   c = 0;   break;
        case '0':   flag_zeropad = 1;         c = 0;   break;
        default:                                       break;
      }
    }while( c==0 && (c=(*++fmt))!=0 );
    /* Get the field width */
    width = 0;
    if( c=='*' ){
      width = va_arg(ap,int);
      if( width<0 ){
        flag_leftjustify = 1;
        width = -width;
      }
      c = *++fmt;
    }else{
      while( c>='0' && c<='9' ){
        width = width*10 + c - '0';
        c = *++fmt;
      }
    }
    if( width > etBUFSIZE-10 ){
      width = etBUFSIZE-10;
    }
    /* Get the precision */
    if( c=='.' ){
      precision = 0;
      c = *++fmt;
      if( c=='*' ){
        precision = va_arg(ap,int);
        if( precision<0 ) precision = -precision;
        c = *++fmt;
      }else{
        while( c>='0' && c<='9' ){
          precision = precision*10 + c - '0';
          c = *++fmt;
        }
      }
      /* Limit the precision to prevent overflowing buf[] during conversion */
      if( precision>etBUFSIZE-40 ) precision = etBUFSIZE-40;
    }else{
      precision = -1;
    }
    /* Get the conversion type modifier */
    if( c=='l' ){
      flag_long = 1;
      c = *++fmt;
    }else{
      flag_long = 0;
    }
    /* Fetch the info entry for the field */
    infop = 0;
    xtype = etERROR;
    for(idx=0; idx<etNINFO; idx++){
      if( c==fmtinfo[idx].fmttype ){
        infop = &fmtinfo[idx];
        if( useExtended || (infop->flags & FLAG_INTERN)==0 ){
          xtype = infop->type;
        }
        break;
      }
    }
    zExtra = 0;

    /*
    ** At this point, variables are initialized as follows:
    **
    **   flag_alternateform          TRUE if a '#' is present.
    **   flag_plussign               TRUE if a '+' is present.
    **   flag_leftjustify            TRUE if a '-' is present or if the
    **                               field width was negative.
    **   flag_zeropad                TRUE if the width began with 0.
    **   flag_long                   TRUE if the letter 'l' (ell) prefixed
    **                               the conversion character.
    **   flag_blanksign              TRUE if a ' ' is present.
    **   width                       The specified field width.  This is
    **                               always non-negative.  Zero is the default.
    **   precision                   The specified precision.  The default
    **                               is -1.
    **   xtype                       The class of the conversion.
    **   infop                       Pointer to the appropriate info struct.
    */
    switch( xtype ){
      case etRADIX:
        if( flag_long )  longvalue = va_arg(ap,long);
        else             longvalue = va_arg(ap,int);
#if 1
        /* For the format %#x, the value zero is printed "0" not "0x0".
        ** I think this is stupid. */
        if( longvalue==0 ) flag_alternateform = 0;
#else
        /* More sensible: turn off the prefix for octal (to prevent "00"),
        ** but leave the prefix for hex. */
        if( longvalue==0 && infop->base==8 ) flag_alternateform = 0;
#endif
        if( infop->flags & FLAG_SIGNED ){
          if( *(long*)&longvalue<0 ){
            longvalue = -*(long*)&longvalue;
            prefix = '-';
          }else if( flag_plussign )  prefix = '+';
          else if( flag_blanksign )  prefix = ' ';
          else                       prefix = 0;
        }else                        prefix = 0;
        if( flag_zeropad && precision<width-(prefix!=0) ){
          precision = width-(prefix!=0);
        }
        bufpt = &buf[etBUFSIZE-1];
        {
          register char *cset;      /* Use registers for speed */
          register int base;
          cset = infop->charset;
          base = infop->base;
          do{                                           /* Convert to ascii */
            *(--bufpt) = cset[longvalue%base];
            longvalue = longvalue/base;
          }while( longvalue>0 );
        }
        length = &buf[etBUFSIZE-1]-bufpt;
        for(idx=precision-length; idx>0; idx--){
          *(--bufpt) = '0';                             /* Zero pad */
        }
        if( prefix ) *(--bufpt) = prefix;               /* Add sign */
        if( flag_alternateform && infop->prefix ){      /* Add "0" or "0x" */
          char *pre, x;
          pre = infop->prefix;
          if( *bufpt!=pre[0] ){
            for(pre=infop->prefix; (x=(*pre))!=0; pre++) *(--bufpt) = x;
          }
        }
        length = &buf[etBUFSIZE-1]-bufpt;
        break;
      case etFLOAT:
      case etEXP:
      case etGENERIC:
        realvalue = va_arg(ap,double);
#ifndef etNOFLOATINGPOINT
        if( precision<0 ) precision = 6;         /* Set default precision */
        if( precision>etBUFSIZE-10 ) precision = etBUFSIZE-10;
        if( realvalue<0.0 ){
          realvalue = -realvalue;
          prefix = '-';
        }else{
          if( flag_plussign )          prefix = '+';
          else if( flag_blanksign )    prefix = ' ';
          else                         prefix = 0;
        }
        if( infop->type==etGENERIC && precision>0 ) precision--;
        rounder = 0.0;
#if 0
        /* Rounding works like BSD when the constant 0.4999 is used.  Weird! */
        for(idx=precision, rounder=0.4999; idx>0; idx--, rounder*=0.1);
#else
        /* It makes more sense to use 0.5 */
        for(idx=precision, rounder=0.5; idx>0; idx--, rounder*=0.1);
#endif
        if( infop->type==etFLOAT ) realvalue += rounder;
        /* Normalize realvalue to within 10.0 > realvalue >= 1.0 */
        exp = 0;
        if( realvalue>0.0 ){
          while( realvalue>=1e8 && exp<=350 ){ realvalue *= 1e-8; exp+=8; }
          while( realvalue>=10.0 && exp<=350 ){ realvalue *= 0.1; exp++; }
          while( realvalue<1e-8 && exp>=-350 ){ realvalue *= 1e8; exp-=8; }
          while( realvalue<1.0 && exp>=-350 ){ realvalue *= 10.0; exp--; }
          if( exp>350 || exp<-350 ){
            bufpt = "NaN";
            length = 3;
            break;
          }
        }
        bufpt = buf;
        /*
        ** If the field type is etGENERIC, then convert to either etEXP
        ** or etFLOAT, as appropriate.
        */
        flag_exp = xtype==etEXP;
        if( xtype!=etFLOAT ){
          realvalue += rounder;
          if( realvalue>=10.0 ){ realvalue *= 0.1; exp++; }
        }
        if( xtype==etGENERIC ){
          flag_rtz = !flag_alternateform;
          if( exp<-4 || exp>precision ){
            xtype = etEXP;
          }else{
            precision = precision - exp;
            xtype = etFLOAT;
          }
        }else{
          flag_rtz = 0;
        }
        /*
        ** The "exp+precision" test causes output to be of type etEXP if
        ** the precision is too large to fit in buf[].
        */
        nsd = 0;
        if( xtype==etFLOAT && exp+precision<etBUFSIZE-30 ){
          flag_dp = (precision>0 || flag_alternateform);
          if( prefix ) *(bufpt++) = prefix;         /* Sign */
          if( exp<0 )  *(bufpt++) = '0';            /* Digits before "." */
          else for(; exp>=0; exp--) *(bufpt++) = et_getdigit(&realvalue,&nsd);
          if( flag_dp ) *(bufpt++) = '.';           /* The decimal point */
          for(exp++; exp<0 && precision>0; precision--, exp++){
            *(bufpt++) = '0';
          }
          while( (precision--)>0 ) *(bufpt++) = et_getdigit(&realvalue,&nsd);
          *(bufpt--) = 0;                           /* Null terminate */
          if( flag_rtz && flag_dp ){     /* Remove trailing zeros and "." */
            while( bufpt>=buf && *bufpt=='0' ) *(bufpt--) = 0;
            if( bufpt>=buf && *bufpt=='.' ) *(bufpt--) = 0;
          }
          bufpt++;                            /* point to next free slot */
        }else{    /* etEXP or etGENERIC */
          flag_dp = (precision>0 || flag_alternateform);
          if( prefix ) *(bufpt++) = prefix;   /* Sign */
          *(bufpt++) = et_getdigit(&realvalue,&nsd);  /* First digit */
          if( flag_dp ) *(bufpt++) = '.';     /* Decimal point */
          while( (precision--)>0 ) *(bufpt++) = et_getdigit(&realvalue,&nsd);
          bufpt--;                            /* point to last digit */
          if( flag_rtz && flag_dp ){          /* Remove tail zeros */
            while( bufpt>=buf && *bufpt=='0' ) *(bufpt--) = 0;
            if( bufpt>=buf && *bufpt=='.' ) *(bufpt--) = 0;
          }
          bufpt++;                            /* point to next free slot */
          if( exp || flag_exp ){
            *(bufpt++) = infop->charset[0];
            if( exp<0 ){ *(bufpt++) = '-'; exp = -exp; } /* sign of exp */
            else       { *(bufpt++) = '+'; }
            if( exp>=100 ){
              *(bufpt++) = (exp/100)+'0';                /* 100's digit */
              exp %= 100;
            }
            *(bufpt++) = exp/10+'0';                     /* 10's digit */
            *(bufpt++) = exp%10+'0';                     /* 1's digit */
          }
        }
        /* The converted number is in buf[] and zero terminated. Output it.
        ** Note that the number is in the usual order, not reversed as with
        ** integer conversions. */
        length = bufpt-buf;
        bufpt = buf;

        /* Special case:  Add leading zeros if the flag_zeropad flag is
        ** set and we are not left justified */
        if( flag_zeropad && !flag_leftjustify && length < width){
          int i;
          int nPad = width - length;
          for(i=width; i>=nPad; i--){
            bufpt[i] = bufpt[i-nPad];
          }
          i = prefix!=0;
          while( nPad-- ) bufpt[i++] = '0';
          length = width;
        }
#endif
        break;
      case etSIZE:
        *(va_arg(ap,int*)) = count;
        length = width = 0;
        break;
      case etPERCENT:
        buf[0] = '%';
        bufpt = buf;
        length = 1;
        break;
      case etCHARLIT:
      case etCHARX:
        c = buf[0] = (xtype==etCHARX ? va_arg(ap,int) : *++fmt);
        if( precision>=0 ){
          for(idx=1; idx<precision; idx++) buf[idx] = c;
          length = precision;
        }else{
          length =1;
        }
        bufpt = buf;
        break;
      case etSTRING:
      case etDYNSTRING:
        bufpt = va_arg(ap,char*);
        if( bufpt==0 ){
          bufpt = "";
        }else if( xtype==etDYNSTRING ){
          zExtra = bufpt;
        }
        length = strlen(bufpt);
        if( precision>=0 && precision<length ) length = precision;
        break;
      case etSQLESCAPE:
      case etSQLESCAPE2:
        {
          int i, j, n, c, isnull;
          char *arg = va_arg(ap,char*);
          isnull = arg==0;
          if( isnull ) arg = (xtype==etSQLESCAPE2 ? "NULL" : "(NULL)");
          for(i=n=0; (c=arg[i])!=0; i++){
            if( c=='\'' )  n++;
          }
          n += i + 1 + ((!isnull && xtype==etSQLESCAPE2) ? 2 : 0);
          if( n>etBUFSIZE ){
            bufpt = zExtra = sqliteMalloc( n );
            if( bufpt==0 ) return -1;
          }else{
            bufpt = buf;
          }
          j = 0;
          if( !isnull && xtype==etSQLESCAPE2 ) bufpt[j++] = '\'';
          for(i=0; (c=arg[i])!=0; i++){
            bufpt[j++] = c;
            if( c=='\'' ) bufpt[j++] = c;
          }
          if( !isnull && xtype==etSQLESCAPE2 ) bufpt[j++] = '\'';
          bufpt[j] = 0;
          length = j;
          if( precision>=0 && precision<length ) length = precision;
        }
        break;
      case etTOKEN: {
        Token *pToken = va_arg(ap, Token*);
        (*func)(arg, pToken->z, pToken->n);
        length = width = 0;
        break;
      }
      case etSRCLIST: {
        SrcList *pSrc = va_arg(ap, SrcList*);
        int k = va_arg(ap, int);
        struct SrcList_item *pItem = &pSrc->a[k];
        assert( k>=0 && k<pSrc->nSrc );
        if( pItem->zDatabase && pItem->zDatabase[0] ){
          (*func)(arg, pItem->zDatabase, strlen(pItem->zDatabase));
          (*func)(arg, ".", 1);
        }
        (*func)(arg, pItem->zName, strlen(pItem->zName));
        length = width = 0;
        break;
      }
      case etERROR:
        buf[0] = '%';
        buf[1] = c;
        errorflag = 0;
        idx = 1+(c!=0);
        (*func)(arg,"%",idx);
        count += idx;
        if( c==0 ) fmt--;
        break;
    }/* End switch over the format type */
    /*
    ** The text of the conversion is pointed to by "bufpt" and is
    ** "length" characters long.  The field width is "width".  Do
    ** the output.
    */
    if( !flag_leftjustify ){
      register int nspace;
      nspace = width-length;
      if( nspace>0 ){
        count += nspace;
        while( nspace>=etSPACESIZE ){
          (*func)(arg,spaces,etSPACESIZE);
          nspace -= etSPACESIZE;
        }
        if( nspace>0 ) (*func)(arg,spaces,nspace);
      }
    }
    if( length>0 ){
      (*func)(arg,bufpt,length);
      count += length;
    }
    if( flag_leftjustify ){
      register int nspace;
      nspace = width-length;
      if( nspace>0 ){
        count += nspace;
        while( nspace>=etSPACESIZE ){
          (*func)(arg,spaces,etSPACESIZE);
          nspace -= etSPACESIZE;
        }
        if( nspace>0 ) (*func)(arg,spaces,nspace);
      }
    }
    if( zExtra ){
      sqliteFree(zExtra);
    }
  }/* End for loop over the format string */
  return errorflag ? -1 : count;
} /* End of function */


/* This structure is used to store state information about the
** write to memory that is currently in progress.
*/
struct sgMprintf {
  char *zBase;     /* A base allocation */
  char *zText;     /* The string collected so far */
  int  nChar;      /* Length of the string so far */
  int  nTotal;     /* Output size if unconstrained */
  int  nAlloc;     /* Amount of space allocated in zText */
  void *(*xRealloc)(void*,int);  /* Function used to realloc memory */
};

/* 
** This function implements the callback from vxprintf. 
**
** This routine add nNewChar characters of text in zNewText to
** the sgMprintf structure pointed to by "arg".
*/
static void mout(void *arg, const char *zNewText, int nNewChar){
  struct sgMprintf *pM = (struct sgMprintf*)arg;
  pM->nTotal += nNewChar;
  if( pM->nChar + nNewChar + 1 > pM->nAlloc ){
    if( pM->xRealloc==0 ){
      nNewChar =  pM->nAlloc - pM->nChar - 1;
    }else{
      pM->nAlloc = pM->nChar + nNewChar*2 + 1;
      if( pM->zText==pM->zBase ){
        pM->zText = pM->xRealloc(0, pM->nAlloc);
        if( pM->zText && pM->nChar ){
          memcpy(pM->zText, pM->zBase, pM->nChar);
        }
      }else{
        pM->zText = pM->xRealloc(pM->zText, pM->nAlloc);
      }
    }
  }
  if( pM->zText && nNewChar>0 ){
    memcpy(&pM->zText[pM->nChar], zNewText, nNewChar);
    pM->nChar += nNewChar;
    pM->zText[pM->nChar] = 0;
  }
}

/*
** This routine is a wrapper around xprintf() that invokes mout() as
** the consumer.  
*/
static char *base_vprintf(
  void *(*xRealloc)(void*,int),   /* Routine to realloc memory. May be NULL */
  int useInternal,                /* Use internal %-conversions if true */
  char *zInitBuf,                 /* Initially write here, before mallocing */
  int nInitBuf,                   /* Size of zInitBuf[] */
  const char *zFormat,            /* format string */
  va_list ap                      /* arguments */
){
  struct sgMprintf sM;
  sM.zBase = sM.zText = zInitBuf;
  sM.nChar = sM.nTotal = 0;
  sM.nAlloc = nInitBuf;
  sM.xRealloc = xRealloc;
  vxprintf(mout, &sM, useInternal, zFormat, ap);
  if( xRealloc ){
    if( sM.zText==sM.zBase ){
      sM.zText = xRealloc(0, sM.nChar+1);
      memcpy(sM.zText, sM.zBase, sM.nChar+1);
    }else if( sM.nAlloc>sM.nChar+10 ){
      sM.zText = xRealloc(sM.zText, sM.nChar+1);
    }
  }
  return sM.zText;
}

/*
** Realloc that is a real function, not a macro.
*/
static void *printf_realloc(void *old, int size){
  return sqliteRealloc(old,size);
}

/*
** Print into memory obtained from sqliteMalloc().  Use the internal
** %-conversion extensions.
*/
char *sqliteVMPrintf(const char *zFormat, va_list ap){
  char zBase[1000];
  return base_vprintf(printf_realloc, 1, zBase, sizeof(zBase), zFormat, ap);
}

/*
** Print into memory obtained from sqliteMalloc().  Use the internal
** %-conversion extensions.
*/
char *sqliteMPrintf(const char *zFormat, ...){
  va_list ap;
  char *z;
  char zBase[1000];
  va_start(ap, zFormat);
  z = base_vprintf(printf_realloc, 1, zBase, sizeof(zBase), zFormat, ap);
  va_end(ap);
  return z;
}

/*
** Print into memory obtained from malloc().  Do not use the internal
** %-conversion extensions.  This routine is for use by external users.
*/
char *sqlite_mprintf(const char *zFormat, ...){
  va_list ap;
  char *z;
  char zBuf[200];

  va_start(ap,zFormat);
  z = base_vprintf((void*(*)(void*,int))realloc, 0, 
                   zBuf, sizeof(zBuf), zFormat, ap);
  va_end(ap);
  return z;
}

/* This is the varargs version of sqlite_mprintf.  
*/
char *sqlite_vmprintf(const char *zFormat, va_list ap){
  char zBuf[200];
  return base_vprintf((void*(*)(void*,int))realloc, 0,
                      zBuf, sizeof(zBuf), zFormat, ap);
}

/*
** sqlite_snprintf() works like snprintf() except that it ignores the
** current locale settings.  This is important for SQLite because we
** are not able to use a "," as the decimal point in place of "." as
** specified by some locales.
*/
char *sqlite_snprintf(int n, char *zBuf, const char *zFormat, ...){
  char *z;
  va_list ap;

  va_start(ap,zFormat);
  z = base_vprintf(0, 0, zBuf, n, zFormat, ap);
  va_end(ap);
  return z;
}

/*
** The following four routines implement the varargs versions of the
** sqlite_exec() and sqlite_get_table() interfaces.  See the sqlite.h
** header files for a more detailed description of how these interfaces
** work.
**
** These routines are all just simple wrappers.
*/
int sqlite_exec_printf(
  sqlite *db,                   /* An open database */
  const char *sqlFormat,        /* printf-style format string for the SQL */
  sqlite_callback xCallback,    /* Callback function */
  void *pArg,                   /* 1st argument to callback function */
  char **errmsg,                /* Error msg written here */
  ...                           /* Arguments to the format string. */
){
  va_list ap;
  int rc;

  va_start(ap, errmsg);
  rc = sqlite_exec_vprintf(db, sqlFormat, xCallback, pArg, errmsg, ap);
  va_end(ap);
  return rc;
}
int sqlite_exec_vprintf(
  sqlite *db,                   /* An open database */
  const char *sqlFormat,        /* printf-style format string for the SQL */
  sqlite_callback xCallback,    /* Callback function */
  void *pArg,                   /* 1st argument to callback function */
  char **errmsg,                /* Error msg written here */
  va_list ap                    /* Arguments to the format string. */
){
  char *zSql;
  int rc;

  zSql = sqlite_vmprintf(sqlFormat, ap);
  rc = sqlite_exec(db, zSql, xCallback, pArg, errmsg);
  free(zSql);
  return rc;
}
int sqlite_get_table_printf(
  sqlite *db,            /* An open database */
  const char *sqlFormat, /* printf-style format string for the SQL */
  char ***resultp,       /* Result written to a char *[]  that this points to */
  int *nrow,             /* Number of result rows written here */
  int *ncol,             /* Number of result columns written here */
  char **errmsg,         /* Error msg written here */
  ...                    /* Arguments to the format string */
){
  va_list ap;
  int rc;

  va_start(ap, errmsg);
  rc = sqlite_get_table_vprintf(db, sqlFormat, resultp, nrow, ncol, errmsg, ap);
  va_end(ap);
  return rc;
}
int sqlite_get_table_vprintf(
  sqlite *db,            /* An open database */
  const char *sqlFormat, /* printf-style format string for the SQL */
  char ***resultp,       /* Result written to a char *[]  that this points to */
  int *nrow,             /* Number of result rows written here */
  int *ncolumn,          /* Number of result columns written here */
  char **errmsg,         /* Error msg written here */
  va_list ap             /* Arguments to the format string */
){
  char *zSql;
  int rc;

  zSql = sqlite_vmprintf(sqlFormat, ap);
  rc = sqlite_get_table(db, zSql, resultp, nrow, ncolumn, errmsg);
  free(zSql);
  return rc;
}
