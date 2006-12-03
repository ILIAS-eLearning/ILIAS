<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_python extends HFile{
   function HFile_python(){
     $this->HFile();
     
/*************************************/
// Beautifier Highlighting Configuration File 
// Python
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "1";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("[", "]", "{", "}", "(", ")", "<", ">", "=", "\"", "'", ".", ",", ":", "+", " ", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"and" => "1", 
			"assert" => "1", 
			"break" => "1", 
			"class" => "1", 
			"continue" => "1", 
			"def" => "1", 
			"del" => "1", 
			"elif" => "1", 
			"else" => "1", 
			"except" => "1", 
			"exec" => "1", 
			"finally" => "1", 
			"for" => "1", 
			"from" => "1", 
			"global" => "1", 
			"if" => "1", 
			"import" => "1", 
			"in" => "1", 
			"is" => "1", 
			"lambda" => "1", 
			"map" => "1", 
			"not" => "1", 
			"None" => "1", 
			"or" => "1", 
			"pass" => "1", 
			"print" => "1", 
			"raise" => "1", 
			"range" => "1", 
			"return" => "1", 
			"try" => "1", 
			"while" => "1", 
			"abs" => "2", 
			"apply" => "2", 
			"callable" => "2", 
			"chr" => "2", 
			"cmp" => "2", 
			"coerce" => "2", 
			"compile" => "2", 
			"complex" => "2", 
			"delattr" => "2", 
			"dir" => "2", 
			"divmod" => "2", 
			"eval" => "2", 
			"execfile" => "2", 
			"filter" => "2", 
			"float" => "2", 
			"getattr" => "2", 
			"globals" => "2", 
			"group" => "2", 
			"hasattr" => "2", 
			"hash" => "2", 
			"hex" => "2", 
			"id" => "2", 
			"input" => "2", 
			"int" => "2", 
			"intern" => "2", 
			"isinstance" => "2", 
			"issubclass" => "2", 
			"joinfields" => "2", 
			"len" => "2", 
			"list" => "2", 
			"local" => "2", 
			"long" => "2", 
			"max" => "2", 
			"min" => "2", 
			"match" => "2", 
			"oct" => "2", 
			"open" => "2", 
			"ord" => "2", 
			"pow" => "2", 
			"raw_input" => "2", 
			"reduce" => "2", 
			"reload" => "2", 
			"repr" => "2", 
			"round" => "2", 
			"search" => "2", 
			"setattr" => "2", 
			"slice" => "2", 
			"str" => "2", 
			"splitfields" => "2", 
			"tuple" => "2", 
			"type" => "2", 
			"vars" => "2", 
			"xrange" => "2", 
			"__import__" => "2", 
			"__abs__" => "3", 
			"__add__" => "3", 
			"__and__" => "3", 
			"__call__" => "3", 
			"__cmp__" => "3", 
			"__coerce__" => "3", 
			"__del__" => "3", 
			"__delattr__" => "3", 
			"__delitem__" => "3", 
			"__delslice__" => "3", 
			"__div__" => "3", 
			"__divmod__" => "3", 
			"__float__" => "3", 
			"__getattr__" => "3", 
			"__getitem__" => "3", 
			"__getslice__" => "3", 
			"__hash__" => "3", 
			"__hex__" => "3", 
			"__invert__" => "3", 
			"__int__" => "3", 
			"__init__" => "3", 
			"__len__" => "3", 
			"__long__" => "3", 
			"__lshift__" => "3", 
			"__mod__" => "3", 
			"__mul__" => "3", 
			"__neg__" => "3", 
			"__nonzero__" => "3", 
			"__oct__" => "3", 
			"__or__" => "3", 
			"__pos__" => "3", 
			"__pow__" => "3", 
			"__radd__" => "3", 
			"__rdiv__" => "3", 
			"__rdivmod__" => "3", 
			"__rmod__" => "3", 
			"__rpow__" => "3", 
			"__rlshift__" => "3", 
			"__rrshift__" => "3", 
			"__rshift__" => "3", 
			"__rsub__" => "3", 
			"__rmul__" => "3", 
			"__repr__" => "3", 
			"__rand__" => "3", 
			"__rxor__" => "3", 
			"__ror__" => "3", 
			"__setattr__" => "3", 
			"__setitem__" => "3", 
			"__setslice__" => "3", 
			"__str__" => "3", 
			"__sub__" => "3", 
			"__xor__" => "3", 
			"__bases__" => "4", 
			"__class__" => "4", 
			"__dict__" => "4", 
			"__methods__" => "4", 
			"__members__" => "4", 
			"__name__" => "4", 
			"__version__" => "4", 
			"ArithmeticError" => "5", 
			"AssertionError" => "5", 
			"AttributeError" => "5", 
			"EOFError" => "5", 
			"Exception" => "5", 
			"FloatingPointError" => "5", 
			"IOError" => "5", 
			"ImportError" => "5", 
			"IndexError" => "5", 
			"KeyError" => "5", 
			"KeyboardInterrupt" => "5", 
			"LookupError" => "5", 
			"MemoryError" => "5", 
			"NameError" => "5", 
			"OverflowError" => "5", 
			"RuntimeError" => "5", 
			"StandardError" => "5", 
			"SyntaxError" => "5", 
			"SystemError" => "5", 
			"SystemExit" => "5", 
			"TypeError" => "5", 
			"ValueError" => "5", 
			"ZeroDivisionError" => "5", 
			"AST" => "6", 
			"BaseHTTPServer" => "6", 
			"Bastion" => "6", 
			"cmd" => "6", 
			"commands" => "6", 
			"compileall" => "6", 
			"copy" => "6", 
			"CGIHTTPServer" => "6", 
			"Complex" => "6", 
			"dbhash" => "6", 
			"dircmp" => "6", 
			"dis" => "6", 
			"dospath" => "6", 
			"dumbdbm" => "6", 
			"emacs" => "6", 
			"find" => "6", 
			"fmt" => "6", 
			"fnmatch" => "7", 
			"ftplib" => "6", 
			"getopt" => "6", 
			"glob" => "6", 
			"gopherlib" => "6", 
			"grep" => "6", 
			"htmllib" => "6", 
			"httplib" => "6", 
			"ihooks" => "6", 
			"imghdr" => "6", 
			"linecache" => "6", 
			"lockfile" => "6", 
			"macpath" => "6", 
			"macurl2path" => "6", 
			"mailbox" => "6", 
			"mailcap" => "6", 
			"mimetools" => "6", 
			"mimify" => "6", 
			"mutex" => "6", 
			"math" => "6", 
			"Mimewriter" => "6", 
			"newdir" => "6", 
			"ni" => "6", 
			"nntplib" => "6", 
			"ntpath" => "6", 
			"nturl2path" => "6", 
			"os" => "6", 
			"ospath" => "6", 
			"pdb" => "6", 
			"pickle" => "6", 
			"pipes" => "6", 
			"poly" => "6", 
			"popen2" => "6", 
			"posixfile" => "6", 
			"posixpath" => "6", 
			"profile" => "6", 
			"pstats" => "6", 
			"pyclbr" => "6", 
			"Para" => "6", 
			"quopri" => "6", 
			"Queue" => "6", 
			"rand" => "6", 
			"random" => "6", 
			"regex" => "6", 
			"regsub" => "6", 
			"rfc822" => "6", 
			"sched" => "6", 
			"sgmllib" => "6", 
			"shelve" => "6", 
			"site" => "6", 
			"sndhdr" => "6", 
			"string" => "6", 
			"sys" => "6", 
			"snmp" => "6", 
			"SimpleHTTPServer" => "6", 
			"StringIO" => "6", 
			"SocketServer" => "6", 
			"tb" => "6", 
			"tempfile" => "6", 
			"toaiff" => "6", 
			"token" => "6", 
			"tokenize" => "6", 
			"traceback" => "6", 
			"tty" => "6", 
			"types" => "6", 
			"tzparse" => "6", 
			"Tkinter" => "6", 
			"urllib" => "6", 
			"urlparse" => "6", 
			"util" => "6", 
			"uu" => "6", 
			"UserDict" => "6", 
			"UserList" => "6", 
			"wave" => "6", 
			"whatsound" => "6", 
			"whichdb" => "6", 
			"whrandom" => "6", 
			"xdrlib" => "6", 
			"zmod" => "6", 
			"array" => "7", 
			"struct" => "7", 
			"self" => "7");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.



$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing");

}



function donothing($keywordin)
{
	return $keywordin;
}

}

?>
