<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_bm extends HFile{
   function HFile_bm(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// BM Scripts
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("*", "(", ")", "-", "+", "=", "/", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ADVANCE" => "1", 
			"AGGREGATE_MEMBER" => "1", 
			"ACTION" => "1", 
			"BASE_MODEL" => "1", 
			"COMMIT" => "1", 
			"COMMIT_ZERO" => "1", 
			"COMPUTED_ITEM" => "1", 
			"COMPUTED_MEMBER" => "1", 
			"CYCLE" => "1", 
			"CONVERSION" => "1", 
			"HANGE_MODEL" => "1", 
			"DIMENSION" => "1", 
			"DYNAMIC_SPAN" => "1", 
			"DECOMMISSION" => "1", 
			"DELETE" => "1", 
			"DELETE_TASK" => "1", 
			"DELETE_JOB" => "1", 
			"END_COMMAND" => "1", 
			"FILES" => "1", 
			"ITEM" => "1", 
			"JOIN_MODEL" => "1", 
			"JOB" => "1", 
			"LOAD_CONVERSION_DATA" => "1", 
			"LOAD_ITEMS" => "1", 
			"LOAD_TIME" => "1", 
			"LOAD_CYCLES" => "1", 
			"LOAD_DIMENSION" => "1", 
			"LOAD_MODEL" => "1", 
			"MEMBER" => "1", 
			"MODEL" => "1", 
			"NULLIFY" => "1", 
			"PERIOD" => "1", 
			"QUEUE_JOB" => "1", 
			"ROUTE_FILE" => "1", 
			"RUN_TASK" => "1", 
			"SPECIALS" => "1", 
			"SPANS" => "1", 
			"START_JOB_QUEUE" => "1", 
			"STOP_JOB_QUEUE" => "1", 
			"SPAN" => "1", 
			"TIME" => "1", 
			"TASK" => "1", 
			"UNIT" => "1", 
			"AGGREGATION" => "2", 
			"AVERAGE" => "2", 
			"AGGREGATE_MEMBERS" => "2", 
			"ADDITIVE_ONLY" => "2", 
			"ASCII_EXTRACTOR" => "2", 
			"ACCUMULATOR" => "2", 
			"BREAK_TIME" => "2", 
			"BAD_OUTPUTS" => "2", 
			"CYCLES" => "2", 
			"CONSOLIDATION" => "2", 
			"COMPUTED_MEMBERS" => "2", 
			"COMMIT_VERSION" => "2", 
			"CYCLE_DIMENSION" => "2", 
			"CONVERTER" => "2", 
			"DISPLAYS" => "2", 
			"DYNAMIC_SPANS" => "2", 
			"DIMENSIONS" => "2", 
			"DEFAULT" => "2", 
			"DIMENSION_ONLY" => "2", 
			"DISPLAY_SET" => "2", 
			"UPLICATOR" => "2", 
			"END_TRANSFORMER" => "2", 
			"EXPANDER" => "2", 
			"EXCLUDE" => "2", 
			"FULL" => "2", 
			"FLATTEN" => "2", 
			"FIXED_EXTRACTOR" => "2", 
			"FIELDER" => "2", 
			"GOOD_OUTPUTS" => "2", 
			"GDL" => "2", 
			"HISTORY" => "2", 
			"HIERARCHIC_SUBTRACTIVE" => "2", 
			"HIERARCHIC_ONLY" => "2", 
			"HOLDER" => "2", 
			"ITEMS" => "2", 
			"ITEM_HIERARCHY" => "2", 
			"INCLUDE_ALL" => "2", 
			"INCLUDE" => "2", 
			"INVERTED" => "2", 
			"INCREMENTAL" => "2", 
			"ITEM_DIMENSION" => "2", 
			"INCYCLE_DIMENSION" => "2", 
			"INCLUDE_ONLY" => "2", 
			"JOINER" => "2", 
			"KEYED" => "2", 
			"LIMITED" => "2", 
			"LOAD_MODEL_INCREMENTAL" => "2", 
			"LOGGER" => "2", 
			"MEMBERS" => "2", 
			"MODELS" => "2", 
			"NAME" => "2", 
			"NORMAL" => "2", 
			"NO" => "2", 
			"NON_KEYED" => "2", 
			"NONE" => "2", 
			"NEW_VERSION" => "2", 
			"NULLIFY_CURRENT" => "2", 
			"NULLIFY_FROM" => "2", 
			"PRECOMPUTE" => "2", 
			"PERIODS" => "2", 
			"PIVOT" => "2", 
			"QUALIFIER" => "2", 
			"RETAINED" => "2", 
			"RESTATED" => "2", 
			"REPEATED" => "2", 
			"RUN_TASK_STEP" => "2", 
			"SYNOYMS" => "2", 
			"SUBTRACTIVE_ONLY" => "2", 
			"SERVICE" => "2", 
			"SQL_EXTRACTOR" => "2", 
			"STRIPPER" => "2", 
			"TARGET" => "2", 
			"TIME_LATEST" => "2", 
			"TIME_DIMENSION" => "2", 
			"TOP_INCYCLE" => "2", 
			"TRANSFORMER" => "2", 
			"TIMER" => "2", 
			"YES" => "2", 
			"ACROSS_FIELD" => "3", 
			"BY_FIELD" => "3", 
			"COPY" => "3", 
			"CYCLE_FIELD" => "3", 
			"DATA" => "3", 
			"DEFAULT_ALLOWED" => "3", 
			"ERROR" => "3", 
			"FILE" => "3", 
			"FIELDS" => "3", 
			"FIELD_OR_UNIT" => "3", 
			"FIELD" => "3", 
			"INPUT_0" => "3", 
			"INPUT_1" => "3", 
			"IGNORE" => "3", 
			"ITEM_SPECIFIER" => "3", 
			"LEVEL" => "3", 
			"META" => "3", 
			"NUMERIC" => "3", 
			"OUTPUTS" => "3", 
			"OUTPUT_0" => "3", 
			"OUTPUT_1" => "3", 
			"PASSWORD" => "3", 
			"PREPEND" => "3", 
			"PERIOD_FIELD" => "3", 
			"QUERY" => "3", 
			"SEPARATOR" => "3", 
			"SHORT_RECORDS" => "3", 
			"SOURCE" => "3", 
			"TO" => "3", 
			"TEXT" => "3", 
			"USER" => "3", 
			"VALUE" => "3", 
			"WARNING" => "3", 
			"BUSINESS" => "4", 
			"END" => "4", 
			"EXCLUSIVE" => "4", 
			"NEW" => "4", 
			"SHARED" => "4", 
			"SP" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
