<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ahdl extends HFile{
   function HFile_ahdl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AHDL
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

$this->indent            	= array("BEGIN");
$this->unindent          	= array("END");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", ".", "?", "(", ")", "	", "~", "!", "@", "$", "^", "&", "*", "+", "=", "|", "\\", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("-- ");
$this->blockcommenton    	= array("%");
$this->blockcommentoff   	= array("%");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"and" => "1", 
			"assert" => "1", 
			"begin" => "1", 
			"bidir" => "1", 
			"bits" => "1", 
			"buried" => "1", 
			"case" => "1", 
			"ceil" => "1", 
			"clique" => "1", 
			"connected_pins" => "1", 
			"constant" => "1", 
			"defaults" => "1", 
			"define" => "1", 
			"design" => "1", 
			"device" => "1", 
			"div" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"end" => "1", 
			"for" => "1", 
			"function" => "1", 
			"generate" => "1", 
			"gnd" => "1", 
			"help_id" => "1", 
			"if" => "1", 
			"include" => "1", 
			"input" => "1", 
			"is" => "1", 
			"log2" => "1", 
			"machine" => "1", 
			"mod" => "1", 
			"nand" => "1", 
			"node" => "1", 
			"nor" => "1", 
			"not" => "1", 
			"of" => "1", 
			"options" => "1", 
			"or" => "1", 
			"others" => "1", 
			"output" => "1", 
			"parameters" => "1", 
			"report" => "1", 
			"returns" => "1", 
			"segments" => "1", 
			"severity" => "1", 
			"states" => "1", 
			"subdesign" => "1", 
			"table" => "1", 
			"then" => "1", 
			"title" => "1", 
			"to" => "1", 
			"tri_state_node" => "1", 
			"variable" => "1", 
			"vcc" => "1", 
			"when" => "1", 
			"with" => "1", 
			"xnor" => "1", 
			"xor" => "1", 
			"carry" => "2", 
			"cascade" => "2", 
			"dff" => "2", 
			"dffe" => "2", 
			"exp" => "2", 
			"floor" => "2", 
			"global" => "2", 
			"jkff" => "2", 
			"jkffe" => "2", 
			"latch" => "2", 
			"lcell" => "2", 
			"mcell" => "2", 
			"memory" => "2", 
			"opendrn" => "2", 
			"soft" => "2", 
			"srff" => "2", 
			"srffe" => "2", 
			"tff" => "2", 
			"tffe" => "2", 
			"tri" => "2", 
			"used" => "2", 
			"wire" => "2", 
			"altdpram" => "3", 
			"busmux" => "3", 
			"csdpram" => "3", 
			"csfifo" => "3", 
			"dcfifo" => "3", 
			"divide" => "3", 
			"lpm_abs" => "3", 
			"lpm_add_sub" => "3", 
			"lpm_and" => "3", 
			"lpm_bustri" => "3", 
			"lpm_clshift" => "3", 
			"lpm_compare" => "3", 
			"lpm_constant" => "3", 
			"lpm_counter" => "3", 
			"lpm_decode" => "3", 
			"lpm_dff" => "3", 
			"lpm_divide" => "3", 
			"lpm_ff" => "3", 
			"lpm_fifo" => "3", 
			"lpm_fifo_dc" => "3", 
			"lpm_inv" => "3", 
			"lpm_latch" => "3", 
			"lpm_mult" => "3", 
			"lpm_mux" => "3", 
			"lpm_or" => "3", 
			"lpm_ram_dp" => "3", 
			"lpm_ram_dq" => "3", 
			"lpm_ram_io" => "3", 
			"lpm_rom" => "3", 
			"lpm_shiftreg" => "3", 
			"lpm_tff" => "3", 
			"lpm_xor" => "3", 
			"mux" => "3", 
			"ntsc" => "3", 
			"scfifo" => "3");

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
