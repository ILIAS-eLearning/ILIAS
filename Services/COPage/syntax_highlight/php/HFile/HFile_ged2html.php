<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ged2html extends HFile{
   function HFile_ged2html(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// GED2HTML
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

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("&", "(", ")", "*", "+", ",", "-", ".", "/", ";", "<", "=", ">", "|", "[", "]", "%", "~", " ", "^", " ", " ", "{", "}");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"define" => "1", 
			"do" => "1", 
			"else" => "1", 
			"elseif" => "1", 
			"end" => "1", 
			"for" => "1", 
			"if" => "1", 
			"in" => "1", 
			"include" => "1", 
			"local" => "1", 
			"print" => "1", 
			"set" => "1", 
			"then" => "1", 
			"to" => "1", 
			"while" => "1", 
			"do_families" => "2", 
			"do_gendex" => "2", 
			"do_index" => "2", 
			"do_individuals" => "2", 
			"do_initialize" => "2", 
			"do_notes" => "2", 
			"do_sources" => "2", 
			"do_surnames" => "2", 
			"abbreviation" => "3", 
			"address" => "3", 
			"adoption" => "3", 
			"afn" => "3", 
			"age" => "3", 
			"agency" => "3", 
			"aliases" => "3", 
			"anci" => "3", 
			"associations" => "3", 
			"attributes" => "3", 
			"author" => "3", 
			"birth" => "3", 
			"caln" => "3", 
			"cause" => "3", 
			"change" => "3", 
			"children" => "3", 
			"conc" => "3", 
			"cont" => "3", 
			"copr" => "3", 
			"data" => "3", 
			"date" => "3", 
			"day" => "3", 
			"death" => "3", 
			"description" => "3", 
			"desi" => "3", 
			"event" => "3", 
			"events" => "3", 
			"exact" => "3", 
			"families" => "3", 
			"family" => "3", 
			"father" => "3", 
			"filename" => "3", 
			"first" => "3", 
			"fullname" => "3", 
			"gedcom" => "3", 
			"husband" => "3", 
			"index" => "3", 
			"isfemale" => "3", 
			"ismale" => "3", 
			"last" => "3", 
			"living" => "3", 
			"lower" => "3", 
			"marriages" => "3", 
			"month" => "3", 
			"mother" => "3", 
			"name" => "3", 
			"names" => "3", 
			"nchildren" => "3", 
			"next" => "3", 
			"note" => "3", 
			"notes" => "3", 
			"number" => "3", 
			"objects" => "3", 
			"ordinances" => "3", 
			"page" => "3", 
			"parent" => "3", 
			"phone" => "3", 
			"place" => "3", 
			"pred" => "3", 
			"prev" => "3", 
			"publication" => "3", 
			"quay" => "3", 
			"refns" => "3", 
			"repository" => "3", 
			"rfn" => "3", 
			"rin" => "3", 
			"serial" => "3", 
			"sources" => "3", 
			"status" => "3", 
			"string" => "3", 
			"submitters" => "3", 
			"succ" => "3", 
			"surname" => "3", 
			"tagcode" => "3", 
			"tagname" => "3", 
			"temple" => "3", 
			"text" => "3", 
			"texts" => "3", 
			"title" => "3", 
			"type" => "3", 
			"upper" => "3", 
			"url" => "3", 
			"value" => "3", 
			"wife" => "3", 
			"xref" => "3", 
			"year" => "3", 
			"HEADER" => "4", 
			"NUMBER_OF_FAMILIES" => "4", 
			"NUMBER_OF_NOTES" => "4", 
			"NUMBER_OF_SOURCES" => "4", 
			"OSTYPE" => "4", 
			"PATH_TO_ROOT" => "4", 
			"PERSONS_URL" => "4", 
			"SURNAMES_URL" => "4", 
			"TODAY" => "4", 
			"VERSION" => "4", 
			"CASE_FOLD_LINKS" => "5", 
			"CHARACTER_SET" => "5", 
			"DESTINATION_DIRECTORY" => "5", 
			"DOS_CODE_PAGE" => "5", 
			"ERROR_FILE" => "5", 
			"FILENAME_TEMPLATE" => "5", 
			"FILES_PER_DIRECTORY" => "5", 
			"GENERATE_GENDEX" => "5", 
			"GENERATE_INDEX" => "5", 
			"GENERATE_INDIVIDUALS" => "5", 
			"GENERATE_NOTES" => "5", 
			"GENERATE_SOURCES" => "5", 
			"INDEX_WIDTH" => "5", 
			"INDIVIDUALS_PER_FILE" => "5", 
			"LANGUAGE" => "5", 
			"LIVING_CUTOFF_YEAR" => "5", 
			"LIVING_IGNORE_DEATH" => "5", 
			"LOCALE" => "5", 
			"NOTES_PER_FILE" => "5", 
			"NUMBER_OF_DIRECTORIES" => "5", 
			"OUTPUT_PROGRAM" => "5", 
			"PEDIGREE_DEPTH" => "5", 
			"SOURCES_PER_FILE" => "5", 
			"STABLE_FILENAMESSURNAME_WIDTH" => "5", 
			"UPPER_CASE_SURNAMES" => "5", 
			"USE_LOCAL_TIME" => "5", 
			"BACKGROUND_COLOR" => "6", 
			"BACKGROUND_IMAGE" => "6", 
			"CONT_MEANS_BREAK" => "6", 
			"FAMILY_GROUPS" => "6", 
			"HOMEPAGE" => "6", 
			"INLINE_NOTES" => "6", 
			"INLINE_SOURCES" => "6", 
			"LINK_COLOR" => "6", 
			"MAILTO" => "6", 
			"NO_ALPHABET_TABS" => "6", 
			"OMIT_META" => "6", 
			"TEXT_COLOR" => "6", 
			"VISITED_COLOR" => "6");

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
