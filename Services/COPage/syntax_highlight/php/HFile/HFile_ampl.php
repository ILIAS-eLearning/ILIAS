<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ampl extends HFile{
   function HFile_ampl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AMPL
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

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "*", "(", ")", "+", "=", "\\", "|", "&", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"and" => "1", 
			"arc" => "1", 
			"by" => "1", 
			"check" => "1", 
			"cross" => "1", 
			"close" => "1", 
			"diff" => "1", 
			"difference" => "1", 
			"div" => "1", 
			"data" => "1", 
			"display" => "1", 
			"drop" => "1", 
			"else" => "1", 
			"exists" => "1", 
			"end" => "1", 
			"forall" => "1", 
			"fix" => "1", 
			"function" => "1", 
			"if" => "1", 
			"in" => "1", 
			"inter" => "1", 
			"intersection" => "1", 
			"interval" => "1", 
			"include" => "1", 
			"less" => "1", 
			"let" => "1", 
			"maximize" => "1", 
			"minimize" => "1", 
			"min" => "1", 
			"max" => "1", 
			"mod" => "1", 
			"model" => "1", 
			"node" => "1", 
			"not" => "1", 
			"or" => "1", 
			"objective" => "1", 
			"option" => "1", 
			"param" => "1", 
			"prod" => "1", 
			"product" => "1", 
			"print" => "1", 
			"printf" => "1", 
			"quit" => "1", 
			"reset" => "1", 
			"restore" => "1", 
			"set" => "1", 
			"setof" => "1", 
			"subject" => "1", 
			"subj" => "1", 
			"s.t." => "1", 
			"symdiff" => "1", 
			"sum" => "1", 
			"shell" => "1", 
			"solution" => "1", 
			"then" => "1", 
			"to" => "1", 
			"union" => "1", 
			"update" => "1", 
			"unfix" => "1", 
			"var" => "1", 
			"write" => "1", 
			"binary" => "2", 
			"circular" => "2", 
			"coeff" => "2", 
			"coef" => "2", 
			"cover" => "2", 
			"dimen" => "2", 
			"dimension" => "2", 
			"default" => "2", 
			"display_1col" => "2", 
			"display_eps" => "2", 
			"display_max_2d_cols" => "2", 
			"display_precison" => "2", 
			"display_round" => "2", 
			"display_transpose" => "2", 
			"display_width" => "2", 
			"from" => "2", 
			"gutter_width" => "2", 
			"integer" => "2", 
			"Infinity" => "2", 
			"ordered" => "2", 
			"obj" => "2", 
			"objective_precision" => "2", 
			"omit_zero_cols" => "2", 
			"omit_zero_rows" => "2", 
			"output_precision" => "2", 
			"print_precision" => "2", 
			"print_round" => "2", 
			"print_seperator" => "2", 
			"symbolic" => "2", 
			"within" => "2", 
			"abs" => "3", 
			"acos" => "3", 
			"acosh" => "3", 
			"alias" => "3", 
			"asin" => "3", 
			"asinh" => "3", 
			"atan" => "3", 
			"atan2" => "3", 
			"atanh" => "3", 
			"Beta" => "3", 
			"ceil" => "3", 
			"cos" => "3", 
			"card" => "3", 
			"Cauchy" => "3", 
			"exp" => "3", 
			"Exponential" => "3", 
			"floor" => "3", 
			"first" => "3", 
			"Gamma" => "3", 
			"Irand224" => "3", 
			"int" => "3", 
			"log" => "3", 
			"log10" => "3", 
			"last" => "3", 
			"member" => "3", 
			"Normal" => "3", 
			"next" => "3", 
			"nextw" => "3", 
			"ord" => "3", 
			"ord0" => "3", 
			"Poisson" => "3", 
			"precision" => "3", 
			"prev" => "3", 
			"prevw" => "3", 
			"round" => "3", 
			"sin" => "3", 
			"sinh" => "3", 
			"sqrt" => "3", 
			"tan" => "3", 
			"tanh" => "3", 
			"trunc" => "3", 
			"Uniform" => "3", 
			"Uniform01" => "3");

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
