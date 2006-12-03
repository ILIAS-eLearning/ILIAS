<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_epld extends HFile{
   function HFile_epld(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// EPLD
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

$this->stringchars       	= array();
$this->delimiters        	= array("!", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("\"");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ASYNC_RESET" => "1", 
			"Buffer" => "1", 
			"Collapse" => "1", 
			"Case" => "1", 
			"Com" => "1", 
			"DECLARATIONS" => "1", 
			"Dc" => "1", 
			"Declarations" => "1", 
			"Device" => "1", 
			"EQUATIONS" => "1", 
			"End" => "1", 
			"Equations" => "1", 
			"Else" => "1", 
			"Equations." => "1", 
			"FUNCTIONAL_BLOCK" => "1", 
			"FUSES" => "1", 
			"Functional_block" => "1", 
			"Fuses" => "1", 
			"Goto" => "1", 
			"INTERFACE" => "1", 
			"If-Then-Else" => "1", 
			"In" => "1", 
			"Interface" => "1", 
			"Istype" => "1", 
			"If" => "1", 
			"Invert" => "1", 
			"Keep" => "1", 
			"Library" => "1", 
			"MACRO" => "1", 
			"Macro" => "1", 
			"Module" => "1", 
			"Node" => "1", 
			"Neg" => "1", 
			"Output" => "1", 
			"Pin" => "1", 
			"Property" => "1", 
			"PIN" => "1", 
			"Pos" => "1", 
			"Reg" => "1", 
			"Reg_D" => "1", 
			"Reg_G" => "1", 
			"Reg_JK" => "1", 
			"Reg_SR" => "1", 
			"Reg_T" => "1", 
			"Retain" => "1", 
			"STATE_DIAGRAM" => "1", 
			"STATE_REGISTER" => "1", 
			"SYNC_RESET" => "1", 
			"State" => "1", 
			"State_Diagram" => "1", 
			"State_register" => "1", 
			"Sync_reset" => "1", 
			"Signal" => "1", 
			"TEST_VECTORS" => "1", 
			"TITLE" => "1", 
			"TRUTH_TABLE" => "1", 
			"Test_Vectors" => "1", 
			"Title" => "1", 
			"Trace" => "1", 
			"Truth_Table" => "1", 
			"Test_vectors" => "1", 
			"Then" => "1", 
			"When-Then-Else" => "1", 
			"With" => "1", 
			"XOR" => "1", 
			"XOR_FACTORS" => "1", 
			"XOR_Factors" => "1", 
			"Xor" => "1", 
			"@Alternate" => "2", 
			"@Carry" => "2", 
			"@Const" => "2", 
			"@Dcset" => "2", 
			"@Dcstate" => "2", 
			"@Exit" => "2", 
			"@Expr" => "2", 
			"@If" => "2", 
			"@Ifb" => "2", 
			"@Ifdef" => "2", 
			"@Ifiden" => "2", 
			"@Ifnb" => "2", 
			"@Ifndef" => "2", 
			"@Ifniden" => "2", 
			"@Include" => "2", 
			"@Irp" => "2", 
			"@Irpc" => "2", 
			"@Message" => "2", 
			"@Onset" => "2", 
			"@Page" => "2", 
			"@Radix" => "2", 
			"@Repeat" => "2", 
			"@Setsize" => "2", 
			"@Standard" => "2", 
			".ACLR" => "3", 
			".ASET" => "3", 
			".AP" => "3", 
			".AR" => "3", 
			".CE" => "3", 
			".CLK" => "3", 
			".CLR" => "3", 
			".SET" => "3", 
			".COM" => "3", 
			".D" => "3", 
			".FB" => "3", 
			".FC" => "3", 
			".J" => "3", 
			".K" => "3", 
			".LD" => "3", 
			".LE" => "3", 
			".LH" => "3", 
			".OE" => "3", 
			".PIN" => "3", 
			".PR" => "3", 
			".Q" => "3", 
			".R" => "3", 
			".RE" => "3", 
			".S" => "3", 
			".SP" => "3", 
			".SR" => "3");

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
