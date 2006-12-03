<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_schema extends HFile{
   function HFile_schema(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Schema
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"");
$this->delimiters        	= array("(", ")", "\"", ",", "	", ".", "?", "=", ";");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ALIAS" => "1", 
			"ALLOW_NULL" => "1", 
			"ARRAY_SIZE" => "1", 
			"CHANGE_DATE" => "1", 
			"CMN_DATA_TYPE" => "1", 
			"COMMENT" => "1", 
			"DB_DATA_TYPE" => "1", 
			"DEFAULT" => "1", 
			"FIELDS" => "1", 
			"FIELDS_END" => "1", 
			"FROM" => "1", 
			"GEN_FIELD_ID" => "1", 
			"INDEXES" => "1", 
			"INDEXES_END" => "1", 
			"INV_REL" => "1", 
			"JOINS" => "1", 
			"JOINS_END" => "1", 
			"LOCAL_SCHEMA_REVISION" => "1", 
			"MANDATORY" => "1", 
			"MTM" => "1", 
			"MTO" => "1", 
			"OBJECT" => "1", 
			"OBJECT_END" => "1", 
			"OPTIONAL" => "1", 
			"OTM" => "1", 
			"OTOF" => "1", 
			"OTOP" => "1", 
			"OUTER" => "1", 
			"PREDEFINED" => "1", 
			"RELATIONS" => "1", 
			"RELATIONS_END" => "1", 
			"SCHEMA_REVISION" => "1", 
			"SEARCHABLE" => "1", 
			"SUBJECT" => "1", 
			"UNIQUE" => "1", 
			"USER_DEFINED" => "1", 
			"VIEW" => "1", 
			"VIEW_END" => "1", 
			"," => "2", 
			";" => "2", 
			"=" => "2");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
