<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_uemacro extends HFile{
   function HFile_uemacro(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// UE MACRO
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("Else", "IfCharIs", "IfColNum", "IfEof", "IfFound", "IfNotFound", "IfSel", "Loop", "StartSelect");
$this->unindent          	= array("Else", "EndIf", "EndLoop", "EndSelect");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "_", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", ".", "?", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("*");
$this->blockcommenton    	= array("*");
$this->blockcommentoff   	= array("*");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AnsiToOem" => "1", 
			"Bottom" => "1", 
			"CloseFile" => "1", 
			"ColumnModeOff" => "1", 
			"ColumnModeOn" => "1", 
			"ColumnCut" => "1", 
			"ColumnDelete" => "1", 
			"ColumnInsert" => "1", 
			"ColumnInsertNum" => "1", 
			"Copy" => "1", 
			"Cut" => "1", 
			"Delete" => "1", 
			"DeleteLine" => "1", 
			"DeleteToEndOfLine" => "1", 
			"DeleteToStartOfLine" => "1", 
			"DosToMac" => "1", 
			"DosToUnix" => "1", 
			"ExitMacro" => "1", 
			"GetString" => "1", 
			"GetValue" => "1", 
			"GotoBookMark" => "1", 
			"GotoLine" => "1", 
			"GotoPage" => "1", 
			"HexDelete" => "1", 
			"HexInsert" => "1", 
			"HexOff" => "1", 
			"HexOn" => "1", 
			"InsertMode" => "1", 
			"InsertPageBreak" => "1", 
			"InvertCase" => "1", 
			"NextWindow" => "1", 
			"NewFile" => "1", 
			"OemToAnsi" => "1", 
			"Open" => "1", 
			"OverStrikeMode" => "1", 
			"Paste" => "1", 
			"PlayMacro" => "1", 
			"PreviousWindow" => "1", 
			"ReturnToWarp" => "1", 
			"Save" => "1", 
			"SaveAs" => "1", 
			"SelectAll" => "1", 
			"SelectToBottom" => "1", 
			"SelectToTop" => "1", 
			"SelectWord" => "1", 
			"SpacesToTabs" => "1", 
			"SpacesToTabsAll" => "1", 
			"TabsToSpaces" => "1", 
			"TimeDate" => "1", 
			"ToCaps" => "1", 
			"ToggleBookMark" => "1", 
			"ToLower" => "1", 
			"Top" => "1", 
			"ToUpper" => "1", 
			"TrimTrailingSpaces" => "1", 
			"UnixMactoDos" => "1", 
			"WrapToReturn" => "1", 
			"Template" => "2", 
			"Else" => "3", 
			"EndIf" => "3", 
			"EndLoop" => "3", 
			"EndSelect" => "3", 
			"ExitLoop" => "3", 
			"IfCharIs" => "3", 
			"IfColNum" => "3", 
			"IfEof" => "3", 
			"IfFound" => "3", 
			"IfNotFound" => "3", 
			"IfSel" => "3", 
			"Loop" => "3", 
			"StartSelect" => "3", 
			"**" => "4", 
			"Ctrl+" => "4", 
			"All" => "4", 
			"ARROW" => "4", 
			"AllFiles" => "4", 
			"Backspace" => "4", 
			"DEL" => "4", 
			"DOWN" => "4", 
			"END" => "4", 
			"HOME" => "4", 
			"IgnoreCase" => "5", 
			"Key" => "4", 
			"LEFT" => "4", 
			"PGDN" => "4", 
			"PGUP" => "4", 
			"RemoveDup" => "5", 
			"RIGHT" => "4", 
			"Selected" => "4", 
			"Text" => "4", 
			"UP" => "4", 
			"SortAsc" => "5", 
			"SortDes" => "5", 
			"Find" => "6", 
			"MatchCase" => "6", 
			"MatchWord" => "6", 
			"RegExp" => "6", 
			"Replace" => "6", 
			"Select" => "6", 
			"SelectText" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
