<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_sysedge extends HFile{
   function HFile_sysedge(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Empire SystemEdge
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("'", "\"");
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"community" => "1", 
			"extension" => "1", 
			"emphistory" => "1", 
			"monitor" => "1", 
			"no_authen_traps" => "1", 
			"no_usergroup_table" => "1", 
			"no_who_table" => "1", 
			"no_remoteshell_group" => "1", 
			"no_serial_status" => "1", 
			"no_stat_nfs_filesystems" => "1", 
			"no_process_sets" => "1", 
			"no_actions" => "1", 
			"no_stat_floppy" => "1", 
			"no_process_group" => "1", 
			"no_probe_disks" => "1", 
			"ntregperf" => "1", 
			"syscontact" => "1", 
			"sysdescr" => "1", 
			"sysedge_plugin" => "1", 
			"syslocation" => "1", 
			"sysedge_debug" => "1", 
			"syslog_facility" => "1", 
			"syslog_logfile" => "1", 
			"watch" => "1", 
			"application" => "2", 
			"counter" => "2", 
			"filesystem" => "2", 
			"gauge" => "2", 
			"integer" => "2", 
            "ipaddress" => "2", 
            "internalipaddress" => "2", 
            "externalipaddress" => "2", 
			"key" => "2", 
			"logfile" => "2", 
			"ntevent" => "2", 
			"octetstring" => "2", 
			"objectid" => "2", 
			"oid" => "2", 
			"object" => "2", 
			"process" => "2", 
			"perfinstance" => "2", 
			"performance" => "2", 
			"read-write" => "2", 
			"read-only" => "2", 
			"readwrite" => "2", 
			"readonly" => "2", 
			"registry" => "2", 
			"security" => "2", 
			"system" => "2", 
			"traps" => "2", 
			"timeticks" => "2", 
			"value" => "2", 
			"absolute" => "3", 
			"all" => "3", 
			"error" => "3", 
			"failure" => "3", 
			"information" => "3", 
			"delta" => "3", 
			"devdevice" => "3", 
			"devmntpt" => "3", 
			"devbsize" => "3", 
			"devtblks" => "3", 
			"devfblks" => "3", 
			"devtfiles" => "3", 
			"devffiles" => "3", 
			"devmaxnamelen" => "3", 
			"devtype" => "3", 
			"devfsid" => "3", 
			"devunmount" => "3", 
			"devcapacity" => "3", 
			"devinodecapacity" => "3", 
			"success" => "3", 
			"warning" => "3", 
			"**" => "3", 
			"0x" => "3", 
			"nop" => "4", 
			"ne" => "4", 
			"gt" => "4", 
			"ge" => "4", 
			"le" => "4", 
			"lt" => "4", 
			"eq" => "4", 
			"=" => "4", 
			">" => "4", 
			">=" => "4", 
			"<" => "4", 
			"<=" => "4", 
			"!" => "4", 
			"!=" => "4", 
			"reg_dword" => "5", 
			"reg_sz" => "5", 
			"reg_expand_sz" => "5", 
			"reg_mult_sz" => "5", 
			"perf_counter_counter" => "5", 
			"perf_counter_rawcount" => "5", 
			"perf_counter_rawcount_hex" => "5", 
			"perf_sample_counter" => "5", 
			"perf_counter_timer" => "5", 
			"perf_counter_bulk_count" => "5", 
			"perf_counter_large_rawcount" => "5", 
			"perf_counter_large_rawcount_hex" => "5", 
			"perf_counter_timer_inv" => "5", 
			"perf_average_bulk" => "5", 
			"perf_100nsec_timer" => "5", 
			"perf_100nsec_timer_inv" => "5", 
			"perf_counter_multi_timer" => "5", 
			"perf_counter_multi_timer_inv" => "5", 
			"perf_100nsec_multi_timer" => "5", 
			"perf_100nsec_multi_timer_inv" => "5", 
			"perf_elapsed_time" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
