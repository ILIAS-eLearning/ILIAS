<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_jg_mac extends HFile{
   function HFile_jg_mac(){
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

$this->colours        	= array("");
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
			"This" => "", 
			"document" => "", 
			"describes" => "", 
			"the" => "", 
			"UltraEdit" => "", 
			"macros" => "", 
			"contained" => "", 
			"in" => "", 
			"file" => "", 
			"JG_Mac10.mac." => "", 
			"The" => "", 
			"were" => "", 
			"developed" => "", 
			"under" => "", 
			"version" => "", 
			"5.21." => "", 
			"macro" => "", 
			"library" => "", 
			"can" => "", 
			"be" => "", 
			"loaded" => "", 
			"by" => "", 
			"using" => "", 
			"Macro->Load" => "", 
			"or" => "", 
			"and" => "", 
			"Append" => "", 
			"to" => "", 
			"Existing" => "", 
			"menu" => "", 
			"options" => "", 
			"UltraEdit." => "", 
			"default" => "", 
			"hot" => "", 
			"key" => "", 
			"assignments" => "", 
			"are" => "", 
			"provided" => "", 
			"parentheses" => "", 
			"easily" => "", 
			"changed" => "", 
			"Macro->Delete" => "", 
			"Macro/Modify" => "", 
			"Hot" => "", 
			"Key" => "", 
			"option." => "", 
			"These" => "", 
			"strictly" => "", 
			"as" => "", 
			"an" => "", 
			"educational" => "", 
			"exercise" => "", 
			"no" => "", 
			"warranty" => "", 
			"is" => "", 
			"with" => "", 
			"respect" => "", 
			"their" => "", 
			"usability" => "", 
			"integrity" => "", 
			"(i.e.," => "", 
			"use" => "", 
			"at" => "", 
			"your" => "", 
			"own" => "", 
			"risk!)." => "", 
			"Any" => "", 
			"problems," => "", 
			"comments," => "", 
			"suggestions" => "", 
			"welcome" => "", 
			"e-mailed" => "", 
			"john.goodman@qwest.net." => "", 
			"Enjoy..." => "", 
			"John" => "", 
			"D." => "", 
			"Goodman" => "", 
			"JoinLines" => "", 
			"(F8)" => "", 
			"joins" => "", 
			"line" => "", 
			"following" => "", 
			"current" => "", 
			"end" => "", 
			"of" => "", 
			"a" => "", 
			"single" => "", 
			"space" => "", 
			"between" => "", 
			"them." => "", 
			"PosFirstNonWhite" => "", 
			"(F7)" => "", 
			"moves" => "", 
			"cursor" => "", 
			"first" => "", 
			"non-whitespace" => "", 
			"(non-space" => "", 
			"-tab)" => "", 
			"character" => "", 
			"on" => "", 
			"line." => "", 
			"Note:" => "", 
			"What" => "", 
			"I" => "", 
			"really" => "", 
			"wanted" => "", 
			"do" => "", 
			"(and" => "", 
			"will" => "", 
			"keep" => "", 
			"working" => "", 
			"on)" => "", 
			"was" => "", 
			"develop" => "", 
			"\"intelligent\"" => "", 
			"Home" => "", 
			"that" => "", 
			"would" => "", 
			"work" => "", 
			"follows:" => "", 
			"if" => "", 
			"column" => "", 
			"1" => "", 
			"move" => "", 
			"else" => "", 
			"Some" => "", 
			"people" => "", 
			"prefer:" => "", 
			"LineAlignPrev" => "", 
			"(Ctrl+F7)" => "", 
			"aligns" => "", 
			"beginning" => "", 
			"above" => "", 
			"it." => "", 
			"might" => "", 
			"prefer" => "", 
			"add" => "", 
			"DOWN" => "", 
			"ARROW" => "", 
			"command" => "", 
			"macro." => "", 
			"allow" => "", 
			"you" => "", 
			"align" => "", 
			"series" => "", 
			"lines" => "", 
			"repeatedly" => "", 
			"hitting" => "", 
			"key." => "", 
			"LineAlignCursor" => "", 
			"(Ctrl+F8)" => "", 
			"position." => "", 
			"For" => "", 
			"example," => "", 
			"20," => "", 
			"shifted" => "", 
			"begin" => "", 
			"20." => "", 
			"(best" => "", 
			"used" => "", 
			"Allow" => "", 
			"Positioning" => "", 
			"Beyond" => "", 
			"Line" => "", 
			"End" => "", 
			"option" => "", 
			"set" => "", 
			"on)." => "", 
			"LineRight1" => "", 
			"(Shift+F8)" => "", 
			"shifts" => "", 
			"right" => "", 
			"one" => "", 
			"(by" => "", 
			"adding" => "", 
			"1)." => "", 
			"LineLeft1" => "", 
			"(Shift+F7)" => "", 
			"left" => "", 
			"deleting" => "", 
			"CmmtThisLine" => "", 
			"(Alt+F7)" => "", 
			"\"comments-out\"" => "", 
			"in-line" => "", 
			"comment" => "", 
			"string" => "", 
			"(\"//" => "", 
			"\"" => "", 
			"default)" => "", 
			"Indentation" => "", 
			"preserved." => "", 
			"example:" => "", 
			"before" => "", 
			"invoking" => "", 
			"macro:" => "", 
			"........GrandTotal" => "", 
			"+=" => "", 
			"SubTotal" => "", 
			"after" => "", 
			"........//" => "", 
			"GrandTotal" => "", 
			"CmmtAbove" => "", 
			"(Alt+F8)" => "", 
			"adds" => "", 
			"blank" => "", 
			"facilitate" => "", 
			"encourage!)" => "", 
			"writing" => "", 
			"description" => "", 
			"what" => "", 
			"does." => "", 
			"aligned" => "", 
			"original" => "", 
			"positioned" => "", 
			"just" => "", 
			"string." => "", 
			"Lines" => "", 
			"(cursor" => "", 
			"position" => "", 
			"=" => "", 
			"^):" => "", 
			"^" => "", 
			"RemLeadingWhite" => "", 
			"(Ctrl+Shift+F7)" => "", 
			"deletes" => "", 
			"leading" => "", 
			"whitespace" => "", 
			"characters" => "", 
			"(spaces" => "", 
			"tabs)" => "", 
			"from" => "", 
			"RemTrailingWhite" => "", 
			"(Ctrl+Shift+F8)" => "", 
			"trailing" => "");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
