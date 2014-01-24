<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_VerityStyle extends HFile{
   function HFile_VerityStyle(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Verity Style File
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"$$" => "1", 
			"$control:" => "1", 
			"$define" => "1", 
			"$endif" => "1", 
			"$include" => "1", 
			"$ifdef" => "1", 
			"$subst:" => "1", 
			"BulkLoad" => "2", 
			"default" => "2", 
			"false" => "2", 
			"FastSearch" => "2", 
			"guess" => "2", 
			"NewsFeedIdx" => "2", 
			"NewsFeedOpt" => "2", 
			"no" => "2", 
			"none" => "2", 
			"ReadOnly" => "2", 
			"skip" => "2", 
			"true" => "2", 
			"yes" => "2", 
			"8859" => "2", 
			"agents:" => "3", 
			"agt:" => "3", 
			"autorec:" => "3", 
			"autoval:" => "3", 
			"collection:" => "3", 
			"config:" => "3", 
			"constant:" => "3", 
			"copy:" => "3", 
			"data-table:" => "3", 
			"dda:" => "3", 
			"default:" => "3", 
			"define:" => "3", 
			"descriptor:" => "3", 
			"dft:" => "3", 
			"dlv:" => "3", 
			"drivers:" => "3", 
			"dispatch:" => "3", 
			"field:" => "3", 
			"fixwidth:" => "3", 
			"gateway:" => "3", 
			"lex:" => "3", 
			"mode:" => "3", 
			"policy:" => "3", 
			"repository:" => "3", 
			"session:" => "3", 
			"table:" => "3", 
			"token:" => "3", 
			"type:" => "3", 
			"types:" => "3", 
			"user:" => "3", 
			"varwidth:" => "3", 
			"vdkwatch:" => "3", 
			"worm:" => "3", 
			"zone-begin:" => "3", 
			"zone-end:" => "3", 
			"date" => "4", 
			"EOS" => "4", 
			"multiRow" => "4", 
			"multiRowOrderBy" => "4", 
			"multiRowCol" => "4", 
			"multiRowFormat" => "4", 
			"NEWLINE" => "4", 
			"PARA" => "4", 
			"PUNCT" => "4", 
			"signed-integer" => "4", 
			"sirepath" => "4", 
			"TAB" => "4", 
			"text" => "4", 
			"unsigned-integer" => "4", 
			"WHITE" => "4", 
			"WORD" => "4", 
			"_hexdata" => "5", 
			"_implied_size" => "5", 
			"action" => "5", 
			"alias" => "5", 
			"batch" => "5", 
			"charset" => "5", 
			"collection" => "5", 
			"config" => "5", 
			"createok" => "5", 
			"def-charset" => "5", 
			"disk_free_interval" => "5", 
			"fill" => "5", 
			"filter" => "5", 
			"format-filter" => "5", 
			"globalstyle" => "5", 
			"goaldocs" => "5", 
			"gwkey" => "5", 
			"gwtable" => "5", 
			"hidden" => "5", 
			"housekeeping_interval" => "5", 
			"indexed" => "5", 
			"inherit" => "5", 
			"instance_ceiling" => "5", 
			"instance_floor" => "5", 
			"lock_retry_count" => "5", 
			"maintenance_interval" => "5", 
			"maxdocs" => "5", 
			"max_new_docs" => "5", 
			"max_new_work" => "5", 
			"max-records" => "5", 
			"merge_fully" => "5", 
			"merge_parts" => "5", 
			"minmax" => "5", 
			"minparts" => "5", 
			"ngram_index" => "5", 
			"num-records" => "5", 
			"offset" => "5", 
			"outlevel" => "5", 
			"packed" => "5", 
			"partition_max_size" => "5", 
			"pidfile" => "5", 
			"post" => "5", 
			"regular" => "5", 
			"spanning_index" => "5", 
			"startupKB" => "5", 
			"statefile" => "5", 
			"xor" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
