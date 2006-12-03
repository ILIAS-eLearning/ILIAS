<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_palmrsc extends HFile{
   function HFile_palmrsc(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Palm Pilot Resource Script
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "gray", "purple", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("BEGIN");
$this->unindent          	= array("END");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("%", "*", "(", ")", "-", "+", "\\", "\"", "'", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ALERT" => "1", 
			"APPLICATION" => "1", 
			"APPLICATIONICONNAME" => "1", 
			"AT" => "1", 
			"BEGIN" => "1", 
			"BITMAP" => "1", 
			"BITMAPCOLOR" => "1", 
			"BITMAPFAMILY" => "1", 
			"BITMAPGREY" => "1", 
			"BITMAPGREY16" => "1", 
			"CATEGORIES" => "1", 
			"END" => "1", 
			"FONT" => "3", 
			"FORM" => "1", 
			"ICON" => "1", 
			"ICONFAMILY" => "1", 
			"ID" => "1", 
			"MENU" => "1", 
			"SMALLICON" => "1", 
			"SMALLICONFAMILY" => "1", 
			"STRING" => "1", 
			"TRANSLATION" => "1", 
			"TRAP" => "1", 
			"VERSION" => "1", 
			"BUTTON" => "2", 
			"BUTTONS" => "2", 
			"CHECKBOX" => "2", 
			"FIELD" => "2", 
			"FORMBITMAP" => "2", 
			"GADGET" => "2", 
			"GRAFFITISTATEINDICATOR" => "2", 
			"LABEL" => "2", 
			"LIST" => "2", 
			"MESSAGE" => "2", 
			"POPUPLIST" => "2", 
			"POPUPTRIGGER" => "2", 
			"PUSHBUTTON" => "2", 
			"REPEATBUTTON" => "2", 
			"SCROLLBAR" => "2", 
			"SELECTORTRIGGER" => "2", 
			"TABLE" => "2", 
			"TITLE" => "2", 
			"AUTO" => "3", 
			"AUTOSHIFT" => "3", 
			"BOLDFRAME" => "3", 
			"BOTTOM" => "3", 
			"CENTER" => "3", 
			"CHECKED" => "3", 
			"COLUMNS" => "3", 
			"COLUMNWIDTHS" => "3", 
			"DISABLED" => "3", 
			"DYNAMICSIZE" => "3", 
			"DEFAULTBUTTON" => "3", 
			"EDITABLE" => "3", 
			"ERROR" => "3", 
			"FRAME" => "3", 
			"GROUP" => "3", 
			"HASSCROLLBAR" => "3", 
			"LEFTALIGN" => "3", 
			"LEFTANCHOR" => "3", 
			"MAX" => "3", 
			"MAXCHARS" => "3", 
			"MIN" => "3", 
			"MULTIPLELINES" => "3", 
			"MODAL" => "3", 
			"NOFRAME" => "3", 
			"NONEDITABLE" => "3", 
			"NONUSABLE" => "3", 
			"NUMERIC" => "3", 
			"PAGESIZE" => "3", 
			"PREVBOTTOM" => "3", 
			"PREVHEIGHT" => "3", 
			"PREVLEFT" => "3", 
			"PREVRIGHT" => "3", 
			"PREVTOP" => "3", 
			"PREVWIDTH" => "3", 
			"RIGHT@" => "3", 
			"RIGHTALIGN" => "3", 
			"RIGHTANCHOR" => "3", 
			"ROWS" => "3", 
			"SINGLELINE" => "3", 
			"TRANSPARENCY" => "3", 
			"UNDERLINED" => "3", 
			"USABLE" => "3", 
			"VALUE" => "3", 
			"VISIBLEITEMS" => "3", 
			"MENUID" => "4", 
			"HELPID" => "4", 
			"AUTOID" => "4", 
			"@" => "4", 
			"+" => "4", 
			"-" => "4", 
			"*" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"3" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
