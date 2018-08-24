<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_netcdf extends HFile{
   function HFile_netcdf(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// netCDF CDL 3.3
/*************************************/
// Flags

$this->nocase            	= "0";
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

$this->stringchars       	= array("\"");
$this->delimiters        	= array("(", ")", "=", " ", "{", "}", ":", ";", "\"", "<", ">", "	", ",");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"data" => "1", 
			"dimensions" => "1", 
			"netcdf" => "1", 
			"variables" => "1", 
			"byte" => "2", 
			"char" => "2", 
			"double" => "2", 
			"float" => "2", 
			"int" => "2", 
			"long" => "2", 
			"real" => "2", 
			"short" => "2", 
			"C_format" => "3", 
			"Conventions" => "3", 
			"add_offset" => "3", 
			"history" => "3", 
			"long_name" => "3", 
			"missing_value" => "3", 
			"scale_factor" => "3", 
			"signedness" => "3", 
			"title" => "3", 
			"units" => "3", 
			"valid_max" => "3", 
			"valid_min" => "3", 
			"valid_range" => "3", 
			"_FillValue" => "3");

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
