<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_gedcom extends HFile{
   function HFile_gedcom(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Gedcom
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("@");
$this->delimiters        	= array(",", "/", "#", " ", "!", ">", "<", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ABBR" => "1", 
			"ADDR" => "1", 
			"ADR1" => "1", 
			"ADR2" => "1", 
			"ADOP" => "1", 
			"AFN" => "1", 
			"AGE" => "1", 
			"AGNC" => "1", 
			"ALIA" => "1", 
			"ANCE" => "1", 
			"ANCI" => "1", 
			"ANUL" => "1", 
			"ASSO" => "1", 
			"AUTH" => "1", 
			"BAPL" => "1", 
			"BAPM" => "1", 
			"BARM" => "1", 
			"BASM" => "1", 
			"BIRT" => "1", 
			"BLES" => "1", 
			"BLOB" => "1", 
			"BURI" => "1", 
			"CALN" => "1", 
			"CAST" => "1", 
			"CAUS" => "1", 
			"CENS" => "1", 
			"CHAN" => "1", 
			"CHAR" => "1", 
			"CHIL" => "1", 
			"CHR" => "1", 
			"CHRA" => "1", 
			"CITY" => "1", 
			"CONC" => "1", 
			"CONF" => "1", 
			"CONL" => "1", 
			"CONT" => "1", 
			"COPR" => "1", 
			"CORP" => "1", 
			"CREM" => "1", 
			"CTRY" => "1", 
			"DATA" => "1", 
			"DATE" => "1", 
			"DEAT" => "1", 
			"DESC" => "1", 
			"DESI" => "1", 
			"DEST" => "1", 
			"DIV" => "1", 
			"DIVF" => "1", 
			"DSCR" => "1", 
			"EDUC" => "1", 
			"EMIG" => "1", 
			"ENDL" => "1", 
			"ENGA" => "1", 
			"EVEN" => "1", 
			"FAM" => "1", 
			"FAMC" => "1", 
			"FAMF" => "1", 
			"FAMS" => "1", 
			"FCOM" => "1", 
			"FILE" => "1", 
			"FORM" => "1", 
			"GEDC" => "1", 
			"GIVN" => "1", 
			"GRAD" => "1", 
			"HEAD" => "1", 
			"HUSB" => "1", 
			"IDNO" => "1", 
			"IMMI" => "1", 
			"INDI" => "1", 
			"LANG" => "1", 
			"LEGA" => "1", 
			"MARB" => "1", 
			"MARC" => "1", 
			"MARL" => "1", 
			"MARR" => "1", 
			"MARS" => "1", 
			"MEDI" => "1", 
			"NAME" => "1", 
			"NATI" => "1", 
			"NATU" => "1", 
			"NCHI" => "1", 
			"NICK" => "1", 
			"NMR" => "1", 
			"NOTE" => "1", 
			"NPFX" => "1", 
			"NSFX" => "1", 
			"OBJE" => "1", 
			"OCCU" => "1", 
			"ORDI" => "1", 
			"ORDN" => "1", 
			"PAGE" => "1", 
			"PEDI" => "1", 
			"PHON" => "1", 
			"PLAC" => "1", 
			"POST" => "1", 
			"PROB" => "1", 
			"PROP" => "1", 
			"PUBL" => "1", 
			"QUAY" => "1", 
			"REFN" => "1", 
			"RELA" => "1", 
			"RELI" => "1", 
			"REPO" => "1", 
			"RESI" => "1", 
			"RESN" => "1", 
			"RETI" => "1", 
			"RFN" => "1", 
			"RIN" => "1", 
			"ROLE" => "1", 
			"SEX" => "1", 
			"SLGC" => "1", 
			"SLGS" => "1", 
			"SOUR" => "1", 
			"SPFX" => "1", 
			"SSN" => "1", 
			"STAE" => "1", 
			"STAT" => "1", 
			"SUBM" => "1", 
			"SUBN" => "1", 
			"SURN" => "1", 
			"TEMP" => "1", 
			"TEXT" => "1", 
			"TIME" => "1", 
			"TITL" => "1", 
			"TRLR" => "1", 
			"TYPE" => "1", 
			"VERS" => "1", 
			"WIFE" => "1", 
			"WILL" => "1", 
			"APR" => "2", 
			"AUG" => "2", 
			"bmp" => "2", 
			"DEC" => "2", 
			"FEB" => "2", 
			"gif" => "2", 
			"jpeg" => "2", 
			"JAN" => "2", 
			"JUL" => "2", 
			"JUN" => "2", 
			"MAR" => "2", 
			"MAY" => "2", 
			"NOV" => "2", 
			"OCT" => "2", 
			"ole" => "2", 
			"pcx" => "2", 
			"SEP" => "2", 
			"tiff" => "2", 
			"wav" => "2", 
			"adopted" => "3", 
			"birth" => "3", 
			"foster" => "3", 
			"sealing" => "3", 
			"ABT" => "3", 
			"AFT" => "3", 
			"AND" => "3", 
			"BEF" => "3", 
			"BET" => "3", 
			"BIC" => "3", 
			"BOTH" => "3", 
			"CAL" => "3", 
			"CANCELED" => "3", 
			"CHILD" => "3", 
			"CLEARED" => "3", 
			"COMPLETED" => "3", 
			"EST" => "3", 
			"FROM" => "3", 
			"INFANT" => "3", 
			"INT" => "3", 
			"PRE-1970" => "3", 
			"QUALIFIED" => "3", 
			"STILLBORN" => "3", 
			"SUBMITTED" => "3", 
			"TO" => "3", 
			"UNCLEARED" => "3");

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
