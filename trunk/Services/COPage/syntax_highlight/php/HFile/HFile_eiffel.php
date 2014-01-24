<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");

class HFile_eiffel extends HFile
{

function HFile_eiffel()
{

$this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Eiffel
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("do");
$this->unindent          	= array("end");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "%", "^", "&", "(", ")", "|", "{", "}", "[", "]", ";", "\"", "'", "<", ">", "	", ",", " ", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("--");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"alias" => "1", 
			"all" => "1", 
			"and" => "1", 
			"as" => "1", 
			"check" => "1", 
			"class" => "1", 
			"creation" => "1", 
			"debug" => "1", 
			"deferred" => "1", 
			"do" => "1", 
			"else" => "1", 
			"elseif" => "1", 
			"end" => "1", 
			"expanded" => "1", 
			"export" => "1", 
			"external" => "1", 
			"feature" => "1", 
			"from" => "1", 
			"frozen" => "1", 
			"if" => "1", 
			"implies" => "1", 
			"indexing" => "1", 
			"infix" => "1", 
			"inherit" => "1", 
			"inspect" => "1", 
			"is" => "1", 
			"like" => "1", 
			"local" => "1", 
			"loop" => "1", 
			"not" => "1", 
			"obsolete" => "1", 
			"old" => "1", 
			"once" => "1", 
			"or" => "1", 
			"prefix" => "1", 
			"redefine" => "1", 
			"rename" => "1", 
			"rescue" => "1", 
			"retry" => "1", 
			"select" => "1", 
			"separate" => "1", 
			"strip" => "1", 
			"then" => "1", 
			"true" => "1", 
			"undefine" => "1", 
			"unique" => "1", 
			"until" => "1", 
			"when" => "1", 
			"xor" => "1", 
			"," => "1", 
			"." => "1", 
			";" => "1", 
			":" => "1", 
			"Result" => "2", 
			"Current" => "2", 
			"False" => "2", 
			"True" => "2", 
			"Void" => "2", 
			"@" => "2", 
			"!" => "2", 
			"!!" => "2", 
			"=" => "2", 
			":=" => "2", 
			"\\" => "2", 
			"\\\\" => "2", 
			"-" => "2", 
			"(" => "2", 
			")" => "2", 
			"[" => "2", 
			"]" => "2", 
			"{" => "2", 
			"}" => "2", 
			"<" => "2", 
			"<=" => "2", 
			">" => "2", 
			">=" => "2", 
			"?" => "2", 
			"'" => "2", 
			"`" => "2", 
			"+" => "2", 
			"$" => "2", 
			"%" => "2", 
			"//" => "2", 
			"/" => "2", 
			"/=" => "2", 
			"ANY" => "3", 
			"ARGUMENTS" => "3", 
			"ARRAY" => "3", 
			"BIT" => "3", 
			"BOOLEAN" => "3", 
			"BOOLEAN_REF" => "3", 
			"CHARACTER" => "3", 
			"CHARACTER_REF" => "3", 
			"COMPARABLE" => "3", 
			"DOUBLE" => "3", 
			"DOUBLE_REF" => "3", 
			"EXCEPTIONS" => "3", 
			"INTEGER" => "3", 
			"INTEGER_REF" => "3", 
			"MEMORY" => "3", 
			"NONE" => "3", 
			"NUMERIC" => "3", 
			"POINTER" => "3", 
			"PLATFORM" => "3", 
			"POINTER_REF" => "3", 
			"REAL" => "3", 
			"REAL_REF" => "3", 
			"STRING" => "3", 
			"STD_FILES" => "3", 
			"STORABLE" => "3", 
			"ensure" => "4", 
			"interface" => "4", 
			"invariant" => "4", 
			"require" => "4", 
			"variant" => "4", 
			"io" => "5", 
			"i" => "5", 
			"j" => "5", 
			"k" => "5", 
			"y" => "5", 
			"x" => "5", 
			"abs" => "6", 
			"add_first" => "6", 
			"add_last" => "6", 
			"append" => "6", 
			"append_in" => "6", 
			"binary_to_integer" => "6", 
			"blank" => "6", 
			"clear" => "6", 
			"compare" => "6", 
			"copy" => "6", 
			"digit" => "6", 
			"even" => "6", 
			"fill_blank" => "6", 
			"fill_with" => "6", 
			"first" => "6", 
			"gcd" => "6", 
			"has" => "6", 
			"has_prefix" => "6", 
			"has_string" => "6", 
			"has_suffix" => "6", 
			"head" => "6", 
			"index_of" => "6", 
			"index_of_string" => "6", 
			"insert" => "6", 
			"is_bit" => "6", 
			"is_equal" => "6", 
			"is_integer" => "6", 
			"item" => "6", 
			"last" => "6", 
			"left_adjust" => "6", 
			"log" => "6", 
			"lower" => "6", 
			"occurrences" => "6", 
			"occurrences_of" => "6", 
			"odd" => "6", 
			"precede" => "6", 
			"prepend" => "6", 
			"put" => "6", 
			"put_boolean" => "6", 
			"put_integer" => "6", 
			"put_new_line" => "6", 
			"put_string" => "6", 
			"remove" => "6", 
			"remove_all_occurrences" => "6", 
			"remove_between" => "6", 
			"remove_first" => "6", 
			"remove_last" => "6", 
			"remove_prefix" => "6", 
			"remove_suffix" => "6", 
			"reverse" => "6", 
			"right_adjust" => "6", 
			"same_as" => "6", 
			"set_last" => "6", 
			"shrink" => "6", 
			"split" => "6", 
			"split_in" => "6", 
			"sqrt" => "6", 
			"substring" => "6", 
			"substring_index" => "6", 
			"swap" => "6", 
			"tail" => "6", 
			"to_bit" => "6", 
			"to_boolean" => "6", 
			"to_double" => "6", 
			"to_integer" => "6", 
			"to_lower" => "6", 
			"to_octal" => "6", 
			"to_real" => "6", 
			"to_string" => "6", 
			"to_upper" => "6", 
			"upper" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.



$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");

}



function donothing($keywordin)
{
	return $keywordin;
}

}
?>
