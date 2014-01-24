<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_fas extends HFile{
   function HFile_fas(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Flash ActionScript
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

$this->indent            	= array("else\"\")\"\"(\"\"1\"\"2\"\"3\"\"4\"\"5\"\"6\"\"7\"\"8\"\"9\"\"0\"\"\"");
$this->unindent          	= array("end", "else", "#");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"all" => "1", 
			"begin" => "1", 
			"Comment" => "1", 
			"Call" => "1", 
			"Clip" => "1", 
			"Command" => "1", 
			"Duplicate" => "1", 
			"Drag" => "2", 
			"End" => "1", 
			"Else" => "1", 
			"FS" => "1", 
			"Frame" => "1", 
			"get" => "1", 
			"go" => "1", 
			"Getproperty" => "1", 
			"High" => "2", 
			"If" => "1", 
			"Loop" => "1", 
			"Load" => "1", 
			"Loaded" => "1", 
			"Movie" => "1", 
			"Next" => "1", 
			"Play" => "1", 
			"Property" => "1", 
			"Previous" => "1", 
			"Quality" => "1", 
			"Remove" => "1", 
			"Set" => "1", 
			"Start" => "1", 
			"Stop" => "1", 
			"Sounds" => "1", 
			"sp" => "1", 
			"sv" => "1", 
			"Trace" => "1", 
			"Toggle" => "1", 
			"tell" => "1", 
			"Target" => "1", 
			"to" => "1", 
			"URL" => "1", 
			"Variable" => "1", 
			"While" => "1", 
			"Alpha" => "2", 
			"B=" => "2", 
			"buffer" => "2", 
			"Over" => "2", 
			"Out" => "2", 
			"focus" => "2", 
			"Key:" => "2", 
			"L=" => "2", 
			"lockcenter" => "2", 
			"Name" => "2", 
			"Outside" => "2", 
			"Press" => "2", 
			"Position" => "2", 
			"Rotation" => "2", 
			"R" => "2", 
			"Release" => "2", 
			"Roll" => "2", 
			"rectangle" => "2", 
			"Show" => "2", 
			"Sound" => "2", 
			"Scale" => "2", 
			"T=" => "2", 
			"time" => "2", 
			"vars" => "2", 
			"visibility" => "2", 
			"window" => "2", 
			"X" => "2", 
			"Y" => "2", 
			"_currentframe" => "3", 
			"_x" => "3", 
			"_y" => "3", 
			"_width" => "3", 
			"_height" => "3", 
			"_rotation" => "3", 
			"_target" => "3", 
			"_name" => "3", 
			"_xscale" => "3", 
			"_yscale" => "3", 
			"_droptarget" => "3", 
			"_visible" => "3", 
			"_alpha" => "3", 
			"_framesloaded" => "3", 
			"_totalframes" => "3", 
			"and" => "3", 
			"Chr" => "3", 
			"Eval" => "3", 
			"eq" => "3", 
			"False" => "3", 
			"GetTimer" => "3", 
			"ge" => "3", 
			"gt" => "3", 
			"Int" => "3", 
			"Length" => "3", 
			"le" => "3", 
			"lt" => "3", 
			"Newline" => "3", 
			"ne" => "3", 
			"not" => "3", 
			"Ord" => "3", 
			"or" => "3", 
			"Random" => "3", 
			"Substring" => "3", 
			"True" => "3");

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
