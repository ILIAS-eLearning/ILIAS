<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_vbscript extends HFile{
   function HFile_vbscript(){
     $this->HFile();
     
/*************************************/
// Beautifier Highlighting Configuration File 
// VBScript
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown", "gray", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("Sub", "Private Sub", "Public Sub");
$this->unindent          	= array("End Sub");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array(".", "(", ")", ",", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", "	", ">", " ");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("'");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"And" => "1", 
			"As" => "1", 
			"Call" => "1", 
			"Case" => "1", 
			"Class" => "1", 
			"Const" => "1", 
			"Dim" => "1", 
			"Do" => "1", 
			"Each" => "1", 
			"Else" => "1", 
			"ElseIf" => "1", 
			"Empty" => "1", 
			"End" => "1", 
			"Eqv" => "1", 
			"Erase" => "1", 
			"Error" => "1", 
			"Execute" => "1", 
			"Exit" => "1", 
			"Explicit" => "1", 
			"For" => "1", 
			"Function" => "1", 
			"Get" => "1", 
			"Goto" => "1", 
			"If" => "1", 
			"Imp" => "1", 
			"Is" => "1", 
			"Let" => "1", 
			"Loop" => "1", 
			"Mod" => "1", 
			"Next" => "1", 
			"New" => "1", 
			"Not" => "1", 
			"Nothing" => "1", 
			"Null" => "1", 
			"On" => "1", 
			"Option" => "1", 
			"Or" => "1", 
			"Private" => "1", 
			"Property" => "1", 
			"Public" => "1", 
			"Randomize" => "1", 
			"ReDim" => "1", 
			"Rem" => "1", 
			"Resume" => "1", 
			"Select" => "1", 
			"Set" => "1", 
			"Stop" => "1", 
			"Sub" => "1", 
			"Then" => "1", 
			"To" => "1", 
			"Until" => "1", 
			"Wend" => "1", 
			"While" => "1", 
			"With" => "1", 
			"Abs" => "2", 
			"Array" => "2", 
			"Asc" => "2", 
			"Atn" => "2", 
			"CBool" => "2", 
			"CByte" => "2", 
			"CCur" => "2", 
			"CDate" => "2", 
			"CDbl" => "2", 
			"Chr" => "2", 
			"CInt" => "2", 
			"CLng" => "2", 
			"Cos" => "2", 
			"CSng" => "2", 
			"CStr" => "2", 
			"Date" => "2", 
			"DateAddFunction" => "2", 
			"DateDiff" => "2", 
			"DatePart" => "2", 
			"DateSerial" => "2", 
			"DateValue" => "2", 
			"Day" => "2", 
			"Eval" => "2", 
			"Exp" => "2", 
			"Filter" => "2", 
			"Fix" => "2", 
			"FormatCurrency" => "2", 
			"FormatDateTime" => "2", 
			"FormatNumber" => "2", 
			"FormatPercent" => "2", 
			"GetObject" => "2", 
			"GetRef" => "2", 
			"Hex" => "2", 
			"Hour" => "2", 
			"InputBox" => "2", 
			"InStr" => "2", 
			"InStrRev" => "2", 
			"Int" => "2", 
			"IsArray" => "2", 
			"IsDate" => "2", 
			"IsEmpty" => "2", 
			"IsNull" => "2", 
			"IsNumeric" => "2", 
			"IsObject" => "2", 
			"Join" => "2", 
			"LBound" => "2", 
			"LCase" => "2", 
			"Left" => "2", 
			"Len" => "2", 
			"LoadPicture" => "2", 
			"Log" => "2", 
			"LTrim" => "2", 
			"Mid" => "2", 
			"Minute" => "2", 
			"Month" => "2", 
			"MonthName" => "2", 
			"MsgBox" => "2", 
			"Now" => "2", 
			"Oct" => "2", 
			"Replace" => "4", 
			"RGB" => "2", 
			"Right" => "2", 
			"Rnd" => "2", 
			"Round" => "2", 
			"RTrim" => "2", 
			"ScriptEngine" => "2", 
			"ScriptEngineBuildVersion" => "2", 
			"ScriptEngineMajorVersion" => "2", 
			"ScriptEngineMinorVersion" => "2", 
			"Second" => "2", 
			"Sgn" => "2", 
			"Sin" => "2", 
			"Space" => "2", 
			"Split" => "2", 
			"Sqr" => "2", 
			"StrComp" => "2", 
			"String" => "2", 
			"StrReverse" => "2", 
			"Tan" => "2", 
			"Time" => "2", 
			"Timer" => "2", 
			"TimeSerial" => "2", 
			"TimeValue" => "2", 
			"Trim" => "2", 
			"TypeName" => "2", 
			"UBound" => "2", 
			"UCase" => "2", 
			"VarType" => "2", 
			"Weekday" => "2", 
			"WeekdayName" => "2", 
			"Year" => "2", 
			"AccountDisabled" => "3", 
			"AccountExpirationDate" => "3", 
			"Application" => "3", 
			"Arguments" => "3", 
			"AtEndOfLine" => "3", 
			"AtEndOfStream" => "3", 
			"Attributes" => "3", 
			"AutoUnlockInterval" => "3", 
			"AvailableSpace" => "3", 
			"BadPasswordAttempts" => "3", 
			"Column" => "3", 
			"CompareMode" => "3", 
			"ComputerName" => "3", 
			"Count" => "3", 
			"DateCreated" => "3", 
			"DateLastAccessed" => "3", 
			"DateLastModified" => "3", 
			"Description" => "3", 
			"Drive" => "3", 
			"DriveLetter" => "3", 
			"DriveType" => "3", 
			"Drives" => "3", 
			"Environment" => "3", 
			"FileSystem" => "3", 
			"Files" => "3", 
			"FirstIndex" => "3", 
			"FreeSpace" => "3", 
			"FullName" => "3", 
			"Global" => "3", 
			"HelpContext" => "3", 
			"HelpFile" => "3", 
			"HomeDirDrive" => "3", 
			"HomeDirectory" => "3", 
			"HotKey" => "3", 
			"IconLocation" => "3", 
			"IgnoreCase" => "3", 
			"Interactive" => "3", 
			"IsAccountLocked" => "3", 
			"IsReady" => "3", 
			"IsRootFolder" => "3", 
			"Item" => "3", 
			"Key" => "3", 
			"LastLogin" => "3", 
			"LastLogoff" => "3", 
			"Length" => "3", 
			"Line" => "3", 
			"LockoutObservationInterval" => "3", 
			"LoginHours" => "3", 
			"LoginScript" => "3", 
			"LoginWorkstations" => "3", 
			"MaxBadPasswordsAllowed" => "3", 
			"MaxLogins" => "3", 
			"MaxPasswordAge" => "3", 
			"MaxStorage" => "3", 
			"MinPasswordAge" => "3", 
			"MinPasswordLength" => "3", 
			"Name" => "3", 
			"Number" => "3", 
			"ObjectSid" => "3", 
			"Parameters" => "3", 
			"ParentFolder" => "3", 
			"PasswordAge" => "3", 
			"PasswordExpirationDate" => "3", 
			"PasswordExpired" => "3", 
			"PasswordHistoryLength" => "3", 
			"Path" => "3", 
			"Pattern" => "3", 
			"PrimaryGroupID" => "3", 
			"Profile" => "3", 
			"Remove" => "3", 
			"RootFolder" => "3", 
			"ScriptFullName" => "3", 
			"ScriptName" => "3", 
			"SerialNumber" => "3", 
			"ShareName" => "3", 
			"ShortName" => "3", 
			"ShortPath" => "3", 
			"Size" => "3", 
			"Source" => "3", 
			"SpecialFolders" => "3", 
			"Subfolders" => "3", 
			"TargetPath" => "3", 
			"TotalSize" => "3", 
			"Type" => "3", 
			"UserDomain" => "3", 
			"UserFlags" => "3", 
			"UserName" => "3", 
			"Value" => "3", 
			"Version" => "3", 
			"VolumeName" => "3", 
			"WindowStyle" => "3", 
			"WorkingDirectory" => "3", 
			"Add" => "4", 
			"AddPrinterConnection" => "4", 
			"AddWindowsPrinterConnection" => "4", 
			"AppActivate" => "4", 
			"BuildPath" => "4", 
			"Clear" => "4", 
			"Close" => "4", 
			"ConnectObject" => "4", 
			"Copy" => "4", 
			"CopyFile" => "4", 
			"CopyFolder" => "4", 
			"CreateFolder" => "4", 
			"CreateObject" => "4", 
			"CreateShortcut" => "4", 
			"CreateTextFile" => "4", 
			"Delete" => "4", 
			"DeleteFile" => "4", 
			"DeleteFolder" => "4", 
			"DisconnectObject" => "4", 
			"DriveExists" => "4", 
			"Echo" => "4", 
			"EnumNetworkDrives" => "4", 
			"EnumPrinterConnections" => "4", 
			"Exists" => "4", 
			"ExpandEnvironmentStrings" => "4", 
			"FileExists" => "4", 
			"FolderExists" => "4", 
			"GetAbsolutePathName" => "4", 
			"GetBaseName" => "4", 
			"GetDrive" => "4", 
			"GetDriveName" => "4", 
			"GetExtensionName" => "4", 
			"GetFile" => "4", 
			"GetFileName" => "4", 
			"GetFolder" => "4", 
			"GetParentFolderName" => "4", 
			"GetResource" => "4", 
			"GetSpecialFolder" => "4", 
			"GetTempName" => "4", 
			"Items" => "4", 
			"Keys" => "4", 
			"LogEvent" => "4", 
			"MapNetworkDrive" => "4", 
			"Move" => "4", 
			"MoveFile" => "4", 
			"MoveFolder" => "4", 
			"OpenAsTextStream" => "4", 
			"OpenTextFile" => "4", 
			"Popup" => "4", 
			"Put" => "4", 
			"Quit" => "4", 
			"Raise" => "4", 
			"Read" => "4", 
			"ReadAll" => "4", 
			"ReadLine" => "4", 
			"RegDelete" => "4", 
			"RegRead" => "4", 
			"RegWrite" => "4", 
			"RemoveAll" => "4", 
			"RemoveNetworkDrive" => "4", 
			"RemovePrinterConnection" => "4", 
			"Run" => "4", 
			"Save" => "4", 
			"SendKeys" => "4", 
			"SetDefaultPrinter" => "4", 
			"Skip" => "4", 
			"SkipLine" => "4", 
			"Sleep" => "4", 
			"SetInfo" => "4", 
			"Test" => "4", 
			"Write" => "4", 
			"WriteBlankLines" => "4", 
			"WriteLine" => "4", 
			"Dictionary" => "5", 
			"Err" => "5", 
			"File" => "5", 
			"FileSystemObject" => "5", 
			"Folder" => "5", 
			"Match" => "5", 
			"RegExp" => "5", 
			"TextStream" => "5", 
			"Wscript" => "5", 
			"WshNetwork" => "5", 
			"WshShell" => "5", 
			"False" => "6", 
			"FALSE" => "6", 
			"True" => "6", 
			"TRUE" => "6", 
			"vbAbort" => "6", 
			"vbAbortRetryIgnore" => "6", 
			"vbApplicationModal" => "6", 
			"vbArray" => "6", 
			"vbBinaryCompare" => "6", 
			"vbBlack" => "6", 
			"vbBlue" => "6", 
			"vbBoolean" => "6", 
			"vbByte" => "6", 
			"vbCancel" => "6", 
			"vbCr" => "6", 
			"vbCritical" => "6", 
			"vbCrLf" => "6", 
			"vbCurrency" => "6", 
			"vbCyan" => "6", 
			"vbDataObject" => "6", 
			"vbDate" => "6", 
			"vbDecimal" => "6", 
			"vbDefaultButton1" => "6", 
			"vbDefaultButton2" => "6", 
			"vbDefaultButton3" => "6", 
			"vbDefaultButton4" => "6", 
			"vbDouble" => "6", 
			"vbEmpty" => "6", 
			"vbError" => "6", 
			"vbExclamation" => "6", 
			"vbFirstFourDays" => "6", 
			"vbFirstFullWeek" => "6", 
			"vbFirstJan1" => "6", 
			"vbFormFeed" => "6", 
			"vbFriday" => "6", 
			"vbGeneralDate" => "6", 
			"vbGreen" => "6", 
			"vbIgnore" => "6", 
			"vbInformation" => "6", 
			"vbInteger" => "6", 
			"vbLf" => "6", 
			"vbLong" => "6", 
			"vbLongDate" => "6", 
			"vbLongTime" => "6", 
			"vbMagenta" => "6", 
			"vbMonday" => "6", 
			"vbNewLine" => "6", 
			"vbNo" => "6", 
			"vbNull" => "6", 
			"vbNullChar" => "6", 
			"vbNullString" => "6", 
			"vbObject" => "6", 
			"vbObjectError" => "6", 
			"vbOK" => "6", 
			"vbOKCancel" => "6", 
			"vbOKOnly" => "6", 
			"vbQuestion" => "6", 
			"vbRed" => "6", 
			"vbRetry" => "6", 
			"vbRetryCancel" => "6", 
			"vbSaturday" => "6", 
			"vbShortDate" => "6", 
			"vbShortTime" => "6", 
			"vbSingle" => "6", 
			"vbString" => "6", 
			"vbSunday" => "6", 
			"vbSystemModal" => "6", 
			"vbTab" => "6", 
			"vbTextCompare" => "6", 
			"vbThursday" => "6", 
			"vbTuesday" => "6", 
			"vbUseSystem" => "6", 
			"vbUseSystemDayOfWeek" => "6", 
			"vbVariant" => "6", 
			"vbVerticalTab" => "6", 
			"vbWednesday" => "6", 
			"vbWhite" => "6", 
			"vbYellow" => "6", 
			"vbYes" => "6", 
			"vbYesNo" => "6", 
			"vbYesNoCancel" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.



$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing", 
			"3" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");

}



function donothing($keywordin)
{
	return $keywordin;
}

}

?>
