<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_6502 extends HFile{
   function HFile_6502(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "1";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "brown", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array('"');
$this->delimiters        	= array(" ", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"adc" => "1", 
			"and" => "1", 
			"asl" => "1", 
			"bcc" => "1", 
			"bcs" => "1", 
			"beq" => "1", 
			"bit" => "1", 
			"bmi" => "1", 
			"bne" => "1", 
			"bpl" => "1", 
			"brk" => "1", 
			"bvc" => "1", 
			"bvs" => "1", 
			"clc" => "1", 
			"cld" => "1", 
			"cli" => "1", 
			"clv" => "1", 
			"cmp" => "1", 
			"cpx" => "1", 
			"cpy" => "1", 
			"dec" => "1", 
			"dex" => "1", 
			"dey" => "1", 
			"eor" => "1", 
			"inc" => "1", 
			"inx" => "1", 
			"iny" => "1", 
			"jmp" => "1", 
			"jsr" => "1", 
			"lda" => "1", 
			"ldx" => "1", 
			"ldy" => "1", 
			"lsr" => "1", 
			"nop" => "1", 
			"ora" => "1", 
			"pha" => "1", 
			"php" => "1", 
			"pla" => "1", 
			"plp" => "1", 
			"rol" => "1", 
			"ror" => "1", 
			"rti" => "1", 
			"rts" => "1", 
			"sbc" => "1", 
			"sec" => "1", 
			"sed" => "1", 
			"sei" => "1", 
			"sta" => "1", 
			"stx" => "1", 
			"sty" => "1", 
			"tax" => "1", 
			"tay" => "1", 
			"tsx" => "1", 
			"txa" => "1", 
			"txs" => "1", 
			"tya" => "1", 
			".byte" => "2", 
			".text" => "2", 
			".word" => "2", 
			".asc" => "2", 
			".scrl" => "2", 
			".scru" => "2", 
			".include" => "2", 
			".incbin" => "2", 
			".label" => "2", 
			".goto" => "2", 
			".if" => "2", 
			".end" => "2", 
			".enrty" => "2", 
			".opt" => "2", 
			"*=" => "2");

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
