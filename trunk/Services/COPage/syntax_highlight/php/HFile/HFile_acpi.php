<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_acpi extends HFile{
   function HFile_acpi(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// ASL
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"Break" => "1", 
			"BreakPoint" => "1", 
			"Continue" => "1", 
			"Else" => "1", 
			"ElseIf" => "1", 
			"Fatal" => "1", 
			"If" => "1", 
			"Load" => "1", 
			"Noop" => "1", 
			"Notify" => "1", 
			"Release" => "1", 
			"Reset" => "1", 
			"Return" => "1", 
			"Signal" => "1", 
			"Sleep" => "1", 
			"Stall" => "1", 
			"Switch" => "1", 
			"Unload" => "1", 
			"While" => "1", 
			"BankField" => "2", 
			"CreateBitField" => "2", 
			"CreateByteField" => "2", 
			"CreateDWordField" => "2", 
			"CreateField" => "2", 
			"CreateQWordField" => "2", 
			"CreateWordField" => "2", 
			"DataTableRegion" => "2", 
			"Device" => "2", 
			"Event" => "2", 
			"Field" => "2", 
			"IndexField" => "2", 
			"Method" => "2", 
			"Mutex" => "2", 
			"OperationRegion" => "2", 
			"PowerResource" => "2", 
			"Processor" => "2", 
			"ThermalZone" => "2", 
			"CMOS" => "3", 
			"EmbeddedControl" => "3", 
			"PCI_Config" => "3", 
			"PciBarTarget" => "3", 
			"SMBus" => "3", 
			"SystemIO" => "3", 
			"SystemMemory" => "3", 
			"AnyAcc" => "4", 
			"BufferAcc" => "4", 
			"ByteAcc" => "4", 
			"DWordAcc" => "4", 
			"QWordAcc" => "4", 
			"WordAcc" => "4", 
			"Alias" => "5", 
			"Name" => "5", 
			"Scope" => "5", 
			"Acquire" => "6", 
			"Add" => "6", 
			"And" => "6", 
			"Buff" => "6", 
			"Concatenate" => "6", 
			"ConcatenateResTemplate" => "6", 
			"CondRefOf" => "6", 
			"Decrement" => "6", 
			"DecStr" => "6", 
			"DerefOf" => "6", 
			"Divide" => "6", 
			"FindSetLeftBit" => "6", 
			"FindSetRightBit" => "6", 
			"FromBCD" => "6", 
			"HexStr" => "6", 
			"Increment" => "6", 
			"Index" => "6", 
			"Int" => "6", 
			"LAnd" => "6", 
			"LEqual" => "6", 
			"LGreater" => "6", 
			"LGreaterEqual" => "6", 
			"LLess" => "6", 
			"LLessEqual" => "6", 
			"LNot" => "6", 
			"LNotEqual" => "6", 
			"LoadTable" => "6", 
			"LOr" => "6", 
			"Match" => "6", 
			"Mid" => "6", 
			"Mod" => "6", 
			"Multiply" => "6", 
			"NAnd" => "6", 
			"NOr" => "6", 
			"Not" => "6", 
			"ObjectType" => "6", 
			"Or" => "6", 
			"RefOf" => "6", 
			"ShiftLeft" => "6", 
			"ShiftRight" => "6", 
			"SizeOf" => "6", 
			"Store" => "6", 
			"String" => "6", 
			"Subtract" => "6", 
			"ToBCD" => "6", 
			"Wait" => "6", 
			"Xor" => "6", 
			"Integer" => "7", 
			"Sting" => "7", 
			"Buffer" => "7");

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
			"7" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
