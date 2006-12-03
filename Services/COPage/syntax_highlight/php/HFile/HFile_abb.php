<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_abb extends HFile{
   function HFile_abb(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// ABB Rapid Command
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("ELSE", "ELSEIF", "THEN", "DO");
$this->unindent          	= array("ENDIF", "ELSE", "ENDFOR", "ENDPROC", "ENDMODULE", "ENDWHILE", "ENDFOR", "ENDTEST");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("!");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AccSet" => "1", 
			"Color1" => "1", 
			"DOutput" => "1", 
			"ERROR" => "1", 
			"GInput" => "1", 
			"GripLoad" => "1", 
			"MoveAbsJ" => "1", 
			"MoveC" => "1", 
			"MoveJ" => "1", 
			"MoveJDO" => "1", 
			"MoveL" => "1", 
			"MoveLDO" => "1", 
			"MotionSup" => "1", 
			"OpMode" => "1", 
			"PDispOff" => "1", 
			"PDispOn" => "1", 
			"PulseDO" => "1", 
			"Reset" => "1", 
			"SearchL" => "1", 
			"Set" => "1", 
			"SetGO" => "1", 
			"SpotL" => "1", 
			"SpotL_P1" => "1", 
			"SpotL_Z1" => "1", 
			"SpotL_Z2" => "1", 
			"TPErase" => "1", 
			"TPReadFK" => "1", 
			"TPWrite" => "1", 
			"TPWriteP1" => "1", 
			"TriggIO" => "1", 
			"TriggJ" => "1", 
			"TriggL" => "1", 
			"WaitTime" => "1", 
			"WaitUntil" => "1", 
			"Color2" => "2", 
			"Color3" => "3", 
			"bool" => "4", 
			"Color4" => "4", 
			"errnum" => "4", 
			"extjoint" => "4", 
			"jointtarget" => "4", 
			"loaddata" => "4", 
			"num" => "4", 
			"robjoint" => "4", 
			"robtarget" => "4", 
			"string" => "4", 
			"tooldata" => "4", 
			"triggdata" => "4", 
			"Color5" => "5", 
			"EXIT" => "5", 
			"GOTO" => "5", 
			"RETURN" => "5", 
			"RETRY" => "5", 
			"Stop" => "5", 
			"TRYNEXT" => "5", 
			"AND" => "6", 
			"CASE" => "6", 
			"Color6" => "6", 
			"CONST" => "6", 
			"DEFAULT" => "6", 
			"DO" => "6", 
			"ELSE" => "6", 
			"ELSEIF" => "6", 
			"ENDFOR" => "6", 
			"ENDIF" => "6", 
			"ENDMODULE" => "6", 
			"ENDPROC" => "6", 
			"ENDTEST" => "6", 
			"ENDWHILE" => "6", 
			"FOR" => "6", 
			"FROM" => "6", 
			"IF" => "6", 
			"MODULE" => "6", 
			"OR" => "6", 
			"PERS" => "6", 
			"PROC" => "6", 
			"TEST" => "6", 
			"THEN" => "6", 
			"TO" => "6", 
			"VAR" => "6", 
			"WHILE" => "6", 
			"Color7" => "7", 
			"Color8" => "8", 
			"*" => "8", 
			"#" => "8", 
			";" => "8", 
			"," => "8", 
			"+" => "8", 
			"-" => "8", 
			"=" => "8", 
			"//" => "8", 
			"/" => "8", 
			"%" => "8", 
			"&" => "8", 
			">" => "8", 
			"<" => "8", 
			"^" => "8", 
			"!" => "8", 
			"|" => "8");

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
			"7" => "donothing", 
			"8" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
