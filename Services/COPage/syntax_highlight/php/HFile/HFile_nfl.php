<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_nfl extends HFile{
   function HFile_nfl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Notes Formula Language
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "%", "^", "&", "*", "=", "(", ")", "-", "+", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("REM");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"DEFAULT" => "1", 
			"ENVIRONMENT" => "1", 
			"FIELD" => "1", 
			"SELECT" => "1", 
			"@Abs" => "1", 
			"@Abstract" => "1", 
			"@Accessed" => "1", 
			"@Acos" => "1", 
			"@Adjust" => "1", 
			"@All" => "1", 
			"@AllChildren" => "1", 
			"@AllDescendants" => "1", 
			"@Ascii" => "1", 
			"@Asin" => "1", 
			"@Atan" => "1", 
			"@Atan2" => "1", 
			"@AttachmentLengths" => "1", 
			"@AttachmentNames" => "1", 
			"@Attachments" => "1", 
			"@Author" => "1", 
			"@Begins" => "1", 
			"@Certificate" => "1", 
			"@Char" => "1", 
			"@CheckAlarms" => "1", 
			"@ClientType" => "1", 
			"@Command" => "1", 
			"@Contains" => "1", 
			"@Cos" => "1", 
			"@Created" => "1", 
			"@Date" => "1", 
			"@Day" => "1", 
			"@DbColumn" => "1", 
			"@DbCommand" => "1", 
			"@DbExists" => "1", 
			"@DbLookup" => "1", 
			"@DbManager" => "1", 
			"@DbName" => "1", 
			"@DbTitle" => "1", 
			"@DDEExecute" => "1", 
			"@DDEInitiate" => "1", 
			"@DDEPoke" => "1", 
			"@DDETerminate" => "1", 
			"@DeleteDocument" => "1", 
			"@DeleteField" => "1", 
			"@DialogBox" => "1", 
			"@Do" => "1", 
			"@DocChildren" => "1", 
			"@DocDescendants" => "1", 
			"@DocFields" => "1", 
			"@DocLength" => "1", 
			"@DocLevel" => "1", 
			"@DocMark" => "1", 
			"@DocNumber" => "1", 
			"@DocParentNumber" => "1", 
			"@DocSiblings" => "1", 
			"@DocumentUniqueID" => "1", 
			"@Domain" => "1", 
			"@EditECL" => "1", 
			"@EditUserECL" => "1", 
			"@Elements" => "1", 
			"@EnableAlarms" => "1", 
			"@Ends" => "1", 
			"@Environment" => "1", 
			"@Error" => "1", 
			"@Exp" => "1", 
			"@Explode" => "1", 
			"@Failure" => "1", 
			"@False" => "1", 
			"@GetDocField" => "1", 
			"@GetPortsList" => "1", 
			"@GetProfileField" => "1", 
			"@Hour" => "1", 
			"@If" => "1", 
			"@Implode" => "1", 
			"@InheritedDocumentUniqueID" => "1", 
			"@Integer" => "1", 
			"@IsAgentEnabled" => "1", 
			"@IsAvailable" => "1", 
			"@IsCategory" => "1", 
			"@IsDocBeingEdited" => "1", 
			"@IsDocBeingLoaded" => "1", 
			"@IsDocBeingMailed" => "1", 
			"@IsDocBeingRecalculated" => "1", 
			"@IsDocBeingSaved" => "1", 
			"@IsDocTruncated" => "1", 
			"@IsError" => "1", 
			"@IsExpandable" => "1", 
			"@IsMember" => "1", 
			"@IsModalHelp" => "1", 
			"@IsNewDoc" => "1", 
			"@IsNotMember" => "1", 
			"@IsNumber" => "1", 
			"@IsResponseDoc" => "1", 
			"@IsText" => "1", 
			"@IsTime" => "1", 
			"@IsUnavailable" => "1", 
			"@IsValid" => "1", 
			"@Keywords" => "1", 
			"@Left" => "1", 
			"@LeftBack" => "1", 
			"@Length" => "1", 
			"@Like" => "1", 
			"@Ln" => "1", 
			"@Log" => "1", 
			"@LowerCase" => "1", 
			"@MailDbName" => "1", 
			"@MailEncryptSavedPreference" => "1", 
			"@MailEncryptSentPreference" => "1", 
			"@MailSavePreference" => "1", 
			"@MailSend" => "1", 
			"@MailSignPreference" => "1", 
			"@Matches" => "1", 
			"@Max" => "1", 
			"@Member" => "1", 
			"@Middle" => "1", 
			"@MiddleBack" => "1", 
			"@Min" => "1", 
			"@Minute" => "1", 
			"@Modified" => "1", 
			"@Modulo" => "1", 
			"@Month" => "1", 
			"@Name" => "1", 
			"@NewLine" => "1", 
			"@No" => "1", 
			"@NoteID" => "1", 
			"@Now" => "1", 
			"@OptimizeMailAddress" => "1", 
			"@Password" => "1", 
			"@Pi" => "1", 
			"@PickList" => "1", 
			"@Platform" => "1", 
			"@PostedCommand" => "1", 
			"@Power" => "1", 
			"@Prompt" => "1", 
			"@ProperCase" => "1", 
			"@Random" => "1", 
			"@RefreshECL" => "1", 
			"@Repeat" => "1", 
			"@Replace" => "1", 
			"@ReplaceSubstring" => "1", 
			"@Responses" => "1", 
			"@Return" => "1", 
			"@Right" => "1", 
			"@RightBack" => "1", 
			"@Round" => "1", 
			"@Second" => "1", 
			"@Select" => "1", 
			"@Set" => "1", 
			"@SetDocField" => "1", 
			"@SetEnvironment" => "1", 
			"@SetField" => "1", 
			"@SetProfileField" => "1", 
			"@Sign" => "1", 
			"@Sin" => "1", 
			"@Soundex" => "1", 
			"@Sqrt" => "1", 
			"@Subset" => "1", 
			"@Success" => "1", 
			"@Sum" => "1", 
			"@Tan" => "1", 
			"@Text" => "1", 
			"@TextToNumber" => "1", 
			"@TextToTime" => "1", 
			"@Time" => "1", 
			"@Today" => "1", 
			"@Tomorrow" => "1", 
			"@Trim" => "1", 
			"@True" => "1", 
			"@Unavailable" => "1", 
			"@Unique" => "1", 
			"@UpperCase" => "1", 
			"@URLGetHeader" => "1", 
			"@URLHistory" => "1", 
			"@URLOpen" => "1", 
			"@UserAccess" => "1", 
			"@UserName" => "1", 
			"@UserPrivileges" => "1", 
			"@UserRoles" => "1", 
			"@V2If" => "1", 
			"@V3UserName" => "1", 
			"@Version" => "1", 
			"@ViewTitle" => "1", 
			"@Weekday" => "1", 
			"@Word" => "1", 
			"@Year" => "1", 
			"@Yes" => "1", 
			"@Yesterday" => "1", 
			"@Zone" => "1", 
			":" => "2", 
			"=" => "2", 
			"&" => "2");

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
