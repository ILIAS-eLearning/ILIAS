<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_mysql extends HFile{
   function HFile_mysql(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// MySQL
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("!", "&", "*", "(", ")", "+", "=", "|", "/", ";", "\"", "'", "<", ">", " ", "	", ",", ".", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"add" => "1", 
			"all" => "1", 
			"alter" => "1", 
			"and" => "1", 
			"as" => "1", 
			"asc" => "1", 
			"auto_increment" => "1", 
			"between" => "1", 
			"binary" => "1", 
			"both" => "1", 
			"by" => "1", 
			"change" => "1", 
			"check" => "1", 
			"column" => "1", 
			"columns" => "1", 
			"create" => "1", 
			"cross" => "1", 
			"data" => "1", 
			"database" => "1", 
			"databases" => "1", 
			"default" => "1", 
			"delayed" => "1", 
			"delete" => "1", 
			"desc" => "1", 
			"describe" => "1", 
			"distinct" => "1", 
			"drop" => "1", 
			"enclosed" => "1", 
			"escaped" => "1", 
			"exists" => "1", 
			"explain" => "1", 
			"field" => "1", 
			"fields" => "1", 
			"flush" => "1", 
			"for" => "1", 
			"foreign" => "1", 
			"from" => "1", 
			"function" => "1", 
			"grant" => "1", 
			"group" => "1", 
			"having" => "1", 
			"identified" => "1", 
			"if" => "1", 
			"ignore" => "1", 
			"index" => "1", 
			"insert" => "1", 
			"infile" => "1", 
			"into" => "1", 
			"join" => "1", 
			"key" => "1", 
			"keys" => "1", 
			"kill" => "1", 
			"leading" => "1", 
			"left" => "1", 
			"like" => "1", 
			"limit" => "1", 
			"lines" => "1", 
			"load" => "1", 
			"local" => "1", 
			"lock" => "1", 
			"low_priority" => "1", 
			"modify" => "1", 
			"natural" => "1", 
			"not" => "1", 
			"null" => "1", 
			"on" => "1", 
			"optimize" => "1", 
			"option" => "1", 
			"optionally" => "1", 
			"or" => "1", 
			"order" => "1", 
			"outer" => "1", 
			"outfile" => "1", 
			"primary" => "1", 
			"proceedure" => "1", 
			"read" => "1", 
			"references" => "1", 
			"regexp" => "1", 
			"rename" => "1", 
			"replace" => "1", 
			"returns" => "1", 
			"revoke" => "1", 
			"rlike" => "1", 
			"select" => "1", 
			"set" => "1", 
			"show" => "1", 
			"soname" => "1", 
			"status" => "1", 
			"straight_join" => "1", 
			"table" => "1", 
			"tables" => "1", 
			"teminated" => "1", 
			"to" => "1", 
			"trailing" => "1", 
			"unique" => "1", 
			"unlock" => "1", 
			"unsigned" => "1", 
			"update" => "1", 
			"use" => "1", 
			"using" => "1", 
			"values" => "1", 
			"variables" => "1", 
			"where" => "1", 
			"with" => "1", 
			"write" => "1", 
			"zerofill" => "1", 
			"xor" => "1", 
			"abs" => "2", 
			"acos" => "2", 
			"adddate" => "2", 
			"ascii" => "2", 
			"asin" => "2", 
			"atan" => "2", 
			"atan2" => "2", 
			"avg" => "2", 
			"bin" => "2", 
			"bit_and" => "2", 
			"bit_count" => "2", 
			"bit_or" => "2", 
			"ceiling" => "2", 
			"char_lengh" => "2", 
			"character_length" => "2", 
			"concat" => "2", 
			"conv" => "2", 
			"cos" => "2", 
			"cot" => "2", 
			"count" => "2", 
			"curdate" => "2", 
			"curtime" => "2", 
			"current_time" => "2", 
			"current_timestamp" => "2", 
			"date_add" => "2", 
			"date_format" => "2", 
			"date_sub" => "2", 
			"dayname" => "2", 
			"dayofmonth" => "2", 
			"dayofweek" => "2", 
			"dayofyear" => "2", 
			"degrees" => "2", 
			"elt" => "2", 
			"encrypt" => "2", 
			"exp" => "2", 
			"find_in_set" => "2", 
			"floor" => "2", 
			"format" => "2", 
			"from_days" => "2", 
			"from_unixtime" => "2", 
			"get_lock" => "2", 
			"greatest" => "2", 
			"hex" => "2", 
			"hour" => "2", 
			"ifnull" => "2", 
			"instr" => "2", 
			"isnull" => "2", 
			"interval" => "2", 
			"last_insert_id" => "2", 
			"lcase" => "2", 
			"lower" => "2", 
			"least" => "2", 
			"length" => "2", 
			"locate" => "2", 
			"log" => "2", 
			"log10" => "2", 
			"lpad" => "2", 
			"ltrim" => "2", 
			"max" => "2", 
			"mid" => "2", 
			"min" => "2", 
			"minute" => "2", 
			"mod" => "2", 
			"month" => "2", 
			"monthname" => "2", 
			"now" => "2", 
			"oct" => "2", 
			"octet_length" => "2", 
			"password" => "2", 
			"period_add" => "2", 
			"period_diff" => "2", 
			"pi" => "2", 
			"position" => "2", 
			"pow" => "2", 
			"quarter" => "2", 
			"radians" => "2", 
			"rand" => "2", 
			"release_lock" => "2", 
			"repeat" => "2", 
			"reverse" => "2", 
			"right" => "2", 
			"round" => "2", 
			"rpad" => "2", 
			"rtrim" => "2", 
			"second" => "2", 
			"sec_to_time" => "2", 
			"session_user" => "2", 
			"sign" => "2", 
			"sin" => "2", 
			"soundex" => "2", 
			"space" => "2", 
			"sqrt" => "2", 
			"strcmp" => "2", 
			"substring" => "2", 
			"substring_index" => "2", 
			"sysdate" => "2", 
			"system_user" => "2", 
			"std" => "2", 
			"sum" => "2", 
			"tan" => "2", 
			"time_format" => "2", 
			"time_to_sec" => "2", 
			"to_days" => "2", 
			"trim" => "2", 
			"truncate" => "2", 
			"ucase" => "2", 
			"unix_timestamp" => "2", 
			"user" => "2", 
			"version" => "2", 
			"week" => "2", 
			"weekday" => "2", 
			"year" => "2", 
			"bigint" => "3", 
			"blob" => "3", 
			"char" => "3", 
			"date" => "3", 
			"datetime" => "3", 
			"decimal" => "3", 
			"double" => "3", 
			"doubleprecision" => "3", 
			"enum" => "3", 
			"float" => "3", 
			"float4" => "3", 
			"float8" => "3", 
			"int" => "3", 
			"int1" => "3", 
			"int2" => "3", 
			"int3" => "3", 
			"int4" => "3", 
			"int8" => "3", 
			"integer" => "3", 
			"long" => "3", 
			"longblob" => "3", 
			"longtext" => "3", 
			"mediumblob" => "3", 
			"mediumint" => "3", 
			"mediumtext" => "3", 
			"middleint" => "3", 
			"numeric" => "3", 
			"real" => "3", 
			"smallint" => "3", 
			"text" => "3", 
			"time" => "3", 
			"timestamp" => "3", 
			"tinyint" => "3", 
			"tinytext" => "3", 
			"tinyblob" => "3", 
			"varbinary" => "3", 
			"varchar" => "3", 
			"varying" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
