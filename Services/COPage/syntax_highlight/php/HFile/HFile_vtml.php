<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_vtml extends HFile{
   function HFile_vtml(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// VTML
/*************************************/
// Flags

$this->nocase            	= "1";
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

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"<ATTRIB" => "1", 
			"<ATTRIBUTES>" => "1", 
			"</ATTRIBUTES>" => "1", 
			"<CONTAINER" => "1", 
			"</CONTAINER>" => "1", 
			"<CONTROL" => "1", 
			"<EDITORLAYOUT" => "1", 
			"</EDITORLAYOUT>" => "1", 
			"<TAG>" => "1", 
			"<TAG" => "1", 
			"</TAG>" => "1", 
			"<TAGLAYOUT>" => "1", 
			"</TAGLAYOUT>" => "1", 
			"<WIZBREAK>" => "1", 
			"<WIZCONTINUE>" => "1", 
			"<WIZIF" => "1", 
			"</WIZIF>" => "1", 
			"<WIZELSE>" => "1", 
			"<WIZLOOP" => "1", 
			"</WIZLOOP>" => "1", 
			"<WZISET" => "1", 
			"ALIGN=" => "2", 
			"ALLOWDECIMALPOINT=" => "2", 
			"ALLOWNEGATIVE=" => "2", 
			"ANCHOR=" => "2", 
			"ATTRIBUTES" => "2", 
			"AUTOSELECT=" => "2", 
			"AUTOSIZE=" => "2", 
			"BODYEDITING=" => "2", 
			"CAPTION=" => "2", 
			"CENTER=" => "2", 
			"CHARCASE=" => "2", 
			"CHECKED=" => "2", 
			"CONDITION=" => "2", 
			"CONTROL=" => "2", 
			"CORNER=" => "2", 
			"DEFAULT=" => "2", 
			"DIRONLY=" => "2", 
			"DOWN=" => "2", 
			"DSNAMECONTROL=" => "2", 
			"EDITABLE=" => "2", 
			"FILENAMEONLY=" => "2", 
			"FILEPATH=" => "2", 
			"FILTER=" => "2", 
			"FROM=" => "2", 
			"HEIGHT=" => "2", 
			"HORIZRESIZE=" => "2", 
			"INDEX=" => "2", 
			"LFHEIGHT=" => "2", 
			"LFWIDTH=" => "2", 
			"LIST=" => "2", 
			"MAXHEIGHTPADDING=" => "2", 
			"MAXLENGTH=" => "2", 
			"MAXWIDTHPADDING=" => "2", 
			"MULTILINE=" => "2", 
			"NAME=" => "2", 
			"PASSWORDCHAR=" => "2", 
			"QUERYNAMECONTROL=" => "2", 
			"RELATIVE=" => "2", 
			"RIGHT=" => "2", 
			"SCROLLBAR=" => "2", 
			"SELECTED=" => "2", 
			"STEP=" => "2", 
			"TO=" => "2", 
			"TRANSPARENT=" => "2", 
			"TYPE=" => "2", 
			"VALIGN=" => "2", 
			"VALUE=" => "2", 
			"VERTRESIZE=" => "2", 
			"WIDTH=" => "2", 
			"WRAP=" => "2");

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
