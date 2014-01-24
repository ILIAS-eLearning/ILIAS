<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_mincdsl extends HFile{
   function HFile_mincdsl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"/L10" => "", 
			"\"MINC" => "", 
			"DSL\"" => "", 
			"Line" => "", 
			"Comment" => "", 
			"=" => "", 
			"\"" => "", 
			"Block" => "", 
			"On" => "", 
			"'" => "", 
			"Off" => "", 
			";" => "", 
			"File" => "", 
			"Extensions" => "", 
			"SRC" => "", 
			"PI" => "", 
			"STM" => "", 
			"OPI" => "", 
			"NPI" => "", 
			"SIM" => "", 
			"AND" => "1", 
			"BIN" => "1", 
			"BIPUT" => "1", 
			"BLOWN" => "1", 
			"CASE" => "1", 
			"CLOCK_ENABLED_BY" => "1", 
			"CLOCKED_BY" => "1", 
			"CLOCKF" => "1", 
			"COMP_OFF" => "1", 
			"COMP_ON" => "1", 
			"D_FLOP" => "1", 
			"D_LATCH" => "1", 
			"DEC" => "1", 
			"DEFAULT" => "1", 
			"DEFAULT_TO" => "1", 
			"DEMORGAN_SYNTH" => "1", 
			"DEVICE" => "1", 
			"DISABLED_ONLY_FOR_TEST" => "1", 
			"DO" => "1", 
			"ELSE" => "1", 
			"ELSIF" => "1", 
			"ENABLED_BY" => "1", 
			"END" => "1", 
			"FF_SYNTH" => "1", 
			"FIT_WITH" => "1", 
			"FIXED" => "1", 
			"FOOTPRINT" => "1", 
			"FOR" => "1", 
			"FUNKTION" => "1", 
			"GOTO" => "1", 
			"GRAY_CODE" => "1", 
			"GROUP" => "1", 
			"SECTIONS" => "1", 
			"HEX" => "1", 
			"HIGH_VALUE" => "1", 
			"IF" => "1", 
			"INCLUDE" => "1", 
			"INITIAL" => "1", 
			"INITIAL_TO" => "1", 
			"INPUT" => "1", 
			"INTACT" => "1", 
			"JK_FLOP" => "1", 
			"LAST_VALUE" => "1", 
			"LATCHED_BY" => "1", 
			"LOW_POWER" => "1", 
			"LOW_TRUE" => "1", 
			"LOW_VALUE" => "1", 
			"MACRO" => "1", 
			"MAX_PTERMS" => "1", 
			"MAX_SYMBOLS" => "1", 
			"MAX_FOR_PTERMS" => "1", 
			"MESSAGE" => "1", 
			"MOD" => "1", 
			"NAME" => "1", 
			"NO_COLLAPSE" => "1", 
			"NO_CONNECT" => "1", 
			"NO_REDUCE" => "1", 
			"NODE" => "1", 
			"NOT" => "1", 
			"OCT" => "1", 
			"ONE_HOT" => "1", 
			"OR" => "1", 
			"OUTPUT" => "1", 
			"PART_NUMBER" => "1", 
			"PHYSICAL" => "1", 
			"POLARITY_CONTROL" => "1", 
			"PRESET_BY" => "1", 
			"POWER=" => "1", 
			"RETURN" => "1", 
			"RESET_BY" => "1", 
			"SECTION" => "1", 
			"SET" => "1", 
			"SIMULATION" => "1", 
			"SR_FLOP" => "1", 
			"STATE" => "1", 
			"STATE_BITS" => "1", 
			"STATE_MACHINE" => "1", 
			"STATE_VALUE" => "1", 
			"STEP" => "1", 
			"SYSTEM_TEST" => "1", 
			"T_FLOP" => "1", 
			"TARGET" => "1", 
			"TEMPLATE" => "1", 
			"TEST_VECTORS" => "1", 
			"THEN" => "1", 
			"TO" => "1", 
			"TRACE" => "1", 
			"TRUTH" => "1", 
			"TRUTH_TABLE" => "1", 
			"USE" => "1", 
			"VAR" => "1", 
			"VIRTUAL" => "1", 
			"WHEN" => "1", 
			"WHILE" => "1", 
			"WIRED_BUS" => "1", 
			"XOR_POLARITY_CONTROL" => "1", 
			"XOR_TO_SOP_SYNTH" => "1");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
