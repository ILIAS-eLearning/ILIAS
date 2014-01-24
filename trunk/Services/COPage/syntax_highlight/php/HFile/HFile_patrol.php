<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_patrol extends HFile{
   function HFile_patrol(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Patrol Scripting
/*************************************/
// Flags

$this->nocase            	= "0";
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
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"{" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"exit" => "1", 
			"export" => "1", 
			"foreach" => "1", 
			"function" => "1", 
			"if" => "1", 
			"last" => "1", 
			"local" => "1", 
			"main" => "1", 
			"next" => "1", 
			"requires" => "1", 
			"return" => "1", 
			"switch" => "1", 
			"while" => "1", 
			"}" => "1", 
			"acos" => "2", 
			"annotate" => "2", 
			"annotate_get" => "2", 
			"asctime" => "2", 
			"asin" => "2", 
			"atan" => "2", 
			"blackout" => "2", 
			"cat" => "2", 
			"ceil" => "2", 
			"chan_exists" => "2", 
			"change_state" => "2", 
			"close" => "2", 
			"cond_signal" => "2", 
			"cond_wait" => "2", 
			"console_type" => "2", 
			"convert_date" => "2", 
			"cos" => "2", 
			"cosh" => "2", 
			"create" => "2", 
			"date" => "2", 
			"debugger" => "2", 
			"destroy" => "2", 
			"destroy_lock" => "2", 
			"difference" => "2", 
			"event_archive" => "2", 
			"event_catalog_get" => "2", 
			"event_check" => "2", 
			"event_query" => "2", 
			"event_range_manage" => "2", 
			"event_range_query" => "2", 
			"event_report" => "2", 
			"event_schedule" => "2", 
			"event_trigger" => "2", 
			"event_trigger2" => "2", 
			"execute" => "2", 
			"exists" => "2", 
			"exp" => "2", 
			"fabs" => "2", 
			"file" => "2", 
			"floor" => "2", 
			"fmod" => "2", 
			"fopen" => "2", 
			"fseek" => "2", 
			"ftell" => "2", 
			"full_discovery" => "2", 
			"get" => "2", 
			"get_chan_info" => "2", 
			"getenv" => "2", 
			"getpid" => "2", 
			"get_ranges" => "2", 
			"get_vars" => "2", 
			"grep" => "2", 
			"history" => "2", 
			"history_get_retention" => "2", 
			"index" => "2", 
			"int" => "2", 
			"internal" => "2", 
			"intersection" => "2", 
			"in_transition" => "2", 
			"isnumber" => "2", 
			"is_var" => "2", 
			"kill" => "2", 
			"length" => "2", 
			"lines" => "2", 
			"lock" => "2", 
			"log" => "2", 
			"loge" => "2", 
			"log10" => "2", 
			"ntharg" => "2", 
			"nthargf" => "2", 
			"nthline" => "2", 
			"nthlinef" => "2", 
			"num_consoles" => "2", 
			"pconfig" => "2", 
			"popen" => "2", 
			"pow" => "2", 
			"print" => "2", 
			"printf" => "2", 
			"proc_exists" => "2", 
			"process" => "2", 
			"Pslexecute" => "2", 
			"random" => "2", 
			"read" => "2", 
			"readln" => "2", 
			"refresh_parameters" => "2", 
			"remote_close" => "2", 
			"remote_event_query" => "2", 
			"remote_event_trigger" => "2", 
			"remote_file_send" => "2", 
			"remote_open" => "2", 
			"response" => "2", 
			"response_get_value" => "2", 
			"rindex" => "2", 
			"set" => "2", 
			"share" => "2", 
			"sin" => "2", 
			"sinh" => "2", 
			"sleep" => "2", 
			"snmp_agent_config" => "2", 
			"snmp_agent_stop" => "2", 
			"snmp_close" => "2", 
			"snmp_config" => "2", 
			"_snmp_debug" => "2", 
			"snmp_get" => "2", 
			"snmp_get_next" => "2", 
			"snmp_h_get" => "2", 
			"snmp__h_get_next" => "2", 
			"snmp_h_set" => "2", 
			"snmp_open" => "2", 
			"snmp_set" => "2", 
			"snmp_trap_ignore" => "2", 
			"snmp_trap_listen" => "2", 
			"snmp_trap_raise_std_trap" => "2", 
			"snmp_trap_receive" => "2", 
			"snmp_trap_register_im" => "2", 
			"snmp_trap_send" => "2", 
			"snmp_walk" => "2", 
			"sort" => "2", 
			"sprintf" => "2", 
			"sqrt" => "2", 
			"srandom" => "2", 
			"subset" => "2", 
			"substr" => "2", 
			"system" => "2", 
			"tail" => "2", 
			"tanh" => "2", 
			"time" => "2", 
			"tmpnam" => "2", 
			"tolower" => "2", 
			"toupper" => "2", 
			"trim" => "2", 
			"union" => "2", 
			"unique" => "2", 
			"unlock" => "2", 
			"unset" => "2", 
			"va_arg" => "2", 
			"va_start" => "2", 
			"write" => "2");

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
