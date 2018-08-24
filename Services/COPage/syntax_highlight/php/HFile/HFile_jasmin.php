<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_jasmin extends HFile{
   function HFile_jasmin(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Jasmin
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"catch" => "1", 
			"class" => "1", 
			"end" => "1", 
			"field" => "1", 
			"implements" => "1", 
			"interface" => "1", 
			"limit" => "1", 
			"line" => "1", 
			"method" => "1", 
			"source" => "1", 
			"super" => "1", 
			"throws" => "1", 
			"var" => "1", 
			"abstract" => "2", 
			"default" => "2", 
			"final" => "2", 
			"from" => "2", 
			"is" => "2", 
			"lookupswitch" => "2", 
			"native" => "2", 
			"private" => "2", 
			"protected" => "2", 
			"public" => "2", 
			"static" => "2", 
			"synchronized" => "2", 
			"tableswitch" => "2", 
			"to" => "2", 
			"transient" => "2", 
			"using" => "2", 
			"volatile" => "2", 
			"aaload" => "3", 
			"aastore" => "3", 
			"aconst_null" => "3", 
			"aload" => "3", 
			"aload_0" => "3", 
			"aload_1" => "3", 
			"aload_2" => "3", 
			"aload_3" => "3", 
			"anewarray" => "3", 
			"areturn" => "3", 
			"arraylength" => "3", 
			"astore" => "3", 
			"astore_0" => "3", 
			"astore_1" => "3", 
			"astore_2" => "3", 
			"astore_3" => "3", 
			"athrow" => "3", 
			"baload" => "3", 
			"bastore" => "3", 
			"bipush" => "3", 
			"breakpoint" => "3", 
			"caload" => "3", 
			"castore" => "3", 
			"checkcast" => "3", 
			"d2f" => "3", 
			"d2i" => "3", 
			"d2l" => "3", 
			"dadd" => "3", 
			"daload" => "3", 
			"dastore" => "3", 
			"dcmpg" => "3", 
			"dcmpl" => "3", 
			"dconst_0" => "3", 
			"dconst_1" => "3", 
			"ddiv" => "3", 
			"dead" => "3", 
			"dload" => "3", 
			"dload_0" => "3", 
			"dload_1" => "3", 
			"dload_2" => "3", 
			"dload_3" => "3", 
			"dmul" => "3", 
			"dneg" => "3", 
			"drem" => "3", 
			"dreturn" => "3", 
			"dstore" => "3", 
			"dstore_0" => "3", 
			"dstore_1" => "3", 
			"dstore_2" => "3", 
			"dstore_3" => "3", 
			"dsub" => "3", 
			"dup" => "3", 
			"dup2" => "3", 
			"dup2_x1" => "3", 
			"dup2_x2" => "3", 
			"dup_x1" => "3", 
			"dup_x2" => "3", 
			"f2d" => "3", 
			"f2i" => "3", 
			"f2l" => "3", 
			"fadd" => "3", 
			"faload" => "3", 
			"fastore" => "3", 
			"fcmpg" => "3", 
			"fcmpl" => "3", 
			"fconst_0" => "3", 
			"fconst_1" => "3", 
			"fconst_2" => "3", 
			"fdiv" => "3", 
			"fload" => "3", 
			"fload_0" => "3", 
			"fload_1" => "3", 
			"fload_2" => "3", 
			"fload_3" => "3", 
			"fmul" => "3", 
			"fneg" => "3", 
			"frem" => "3", 
			"freturn" => "3", 
			"fstore" => "3", 
			"fstore_0" => "3", 
			"fstore_1" => "3", 
			"fstore_2" => "3", 
			"fstore_3" => "3", 
			"fsub" => "3", 
			"getfield" => "3", 
			"getstatic" => "3", 
			"goto" => "3", 
			"goto_w" => "3", 
			"i2b" => "3", 
			"i2c" => "3", 
			"i2d" => "3", 
			"i2f" => "3", 
			"i2l" => "3", 
			"i2s" => "3", 
			"iadd" => "3", 
			"iaload" => "3", 
			"iand" => "3", 
			"iastore" => "3", 
			"iconst_0" => "3", 
			"iconst_1" => "3", 
			"iconst_2" => "3", 
			"iconst_3" => "3", 
			"iconst_4" => "3", 
			"iconst_5" => "3", 
			"iconst_m1" => "3", 
			"idiv" => "3", 
			"if_acmpeq" => "3", 
			"if_acmpne" => "3", 
			"if_icmpeq" => "3", 
			"if_icmpge" => "3", 
			"if_icmpgt" => "3", 
			"if_icmple" => "3", 
			"if_icmplt" => "3", 
			"if_icmpne" => "3", 
			"ifeq" => "3", 
			"ifge" => "3", 
			"ifgt" => "3", 
			"ifle" => "3", 
			"iflt" => "3", 
			"ifne" => "3", 
			"ifnonnull" => "3", 
			"ifnull" => "3", 
			"iinc" => "3", 
			"iload" => "3", 
			"iload_0" => "3", 
			"iload_1" => "3", 
			"iload_2" => "3", 
			"iload_3" => "3", 
			"imul" => "3", 
			"ineg" => "3", 
			"instanceof" => "3", 
			"int2byte" => "3", 
			"int2char" => "3", 
			"int2short" => "3", 
			"invokeinterface" => "3", 
			"invokenonvirtual" => "3", 
			"invokespecial" => "3", 
			"invokestatic" => "3", 
			"invokevirtual" => "3", 
			"ior" => "3", 
			"irem" => "3", 
			"ireturn" => "3", 
			"ishl" => "3", 
			"ishr" => "3", 
			"istore" => "3", 
			"istore_0" => "3", 
			"istore_1" => "3", 
			"istore_2" => "3", 
			"istore_3" => "3", 
			"isub" => "3", 
			"iushr" => "3", 
			"ixor" => "3", 
			"jsr" => "3", 
			"jsr_w" => "3", 
			"l2d" => "3", 
			"l2f" => "3", 
			"l2i" => "3", 
			"label" => "3", 
			"ladd" => "3", 
			"laload" => "3", 
			"land" => "3", 
			"lastore" => "3", 
			"lcmp" => "3", 
			"lconst_0" => "3", 
			"lconst_1" => "3", 
			"ldc" => "3", 
			"ldc2_w" => "3", 
			"ldc_w" => "3", 
			"ldiv" => "3", 
			"lload" => "3", 
			"lload_0" => "3", 
			"lload_1" => "3", 
			"lload_2" => "3", 
			"lload_3" => "3", 
			"lmul" => "3", 
			"lneg" => "3", 
			"lor" => "3", 
			"lrem" => "3", 
			"lreturn" => "3", 
			"lshl" => "3", 
			"lshr" => "3", 
			"lstore" => "3", 
			"lstore_0" => "3", 
			"lstore_1" => "3", 
			"lstore_2" => "3", 
			"lstore_3" => "3", 
			"lsub" => "3", 
			"lushr" => "3", 
			"lxor" => "3", 
			"monitorenter" => "3", 
			"monitorexit" => "3", 
			"multianewarray" => "3", 
			"new" => "3", 
			"newarray" => "3", 
			"nop" => "3", 
			"pop" => "3", 
			"pop2" => "3", 
			"putfield" => "3", 
			"putstatic" => "3", 
			"ret" => "3", 
			"return" => "3", 
			"saload" => "3", 
			"sastore" => "3", 
			"sipush" => "3", 
			"swap" => "3", 
			"try" => "3", 
			"wide" => "3", 
			"L;" => "4", 
			"B" => "4", 
			"C" => "4", 
			"D" => "4", 
			"F" => "4", 
			"I" => "4", 
			"J" => "4", 
			"S" => "4", 
			"V" => "4", 
			"Z" => "4", 
			"[" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
