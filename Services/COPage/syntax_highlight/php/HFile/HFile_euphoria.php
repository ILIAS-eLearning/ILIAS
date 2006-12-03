<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_euphoria extends HFile{
   function HFile_euphoria(){
     $this->HFile();
/*************************************/
// Beautifier Highlighting Configuration File 
// Euphoria
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

$this->indent            	= array("do", "global function", "if");
$this->unindent          	= array("end if", "end while", "end for", "end function", "end procedure", "end type");

// String characters and delimiters

$this->stringchars       	= array("\"");
$this->delimiters        	= array("~", "@", "%", "^", "&", "*", "(", ")", "-", "+", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", " ");
$this->escchar           	= "\\";

// Comment settings

$this->linecommenton     	= array("--");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"and" => "1", 
			"by" => "1", 
			"constant" => "1", 
			"do" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"end" => "1", 
			"exit" => "1", 
			"for" => "1", 
			"function" => "1", 
			"global" => "1", 
			"if" => "1", 
			"include" => "1", 
			"not" => "1", 
			"or" => "1", 
			"procedure" => "1", 
			"return" => "1", 
			"then" => "1", 
			"to" => "1", 
			"type" => "1", 
			"while" => "1", 
			"with" => "1", 
			"without" => "1", 
			"xor" => "1", 
			"atom" => "2", 
			"integer" => "2", 
			"object" => "2", 
			"sequence" => "2", 
			"?" => "3", 
			"append" => "3", 
			"arcsin" => "3", 
			"arccos" => "3", 
			"arctan" => "3", 
			"and_bits" => "3", 
			"allow_break" => "3", 
			"abort" => "3", 
			"all_palette" => "3", 
			"allocate" => "3", 
			"allocate_low" => "3", 
			"allocate_string" => "3", 
			"atom_to_float64" => "3", 
			"atom_to_float32" => "3", 
			"bk_color" => "3", 
			"bytes_to_int" => "3", 
			"bits_to_int" => "3", 
			"compare" => "3", 
			"custom_sort" => "3", 
			"cos" => "3", 
			"close" => "3", 
			"current_dir" => "3", 
			"chdir" => "3", 
			"check_break" => "3", 
			"command_line" => "3", 
			"clear_screen" => "3", 
			"cursor" => "3", 
			"call" => "3", 
			"crash_file" => "3", 
			"crash_message" => "3", 
			"call_proc" => "3", 
			"call_func" => "3", 
			"c_proc" => "3", 
			"c_func" => "3", 
			"call_back" => "3", 
			"dir" => "3", 
			"date" => "3", 
			"display_text_image" => "3", 
			"draw_line" => "3", 
			"display_image" => "3", 
			"dos_interrupt" => "3", 
			"define_c_proc" => "3", 
			"define_c_func" => "3", 
			"define_c_var" => "3", 
			"equal" => "3", 
			"ellipse" => "3", 
			"find" => "3", 
			"floor" => "3", 
			"flush" => "3", 
			"free" => "3", 
			"free_low" => "3", 
			"float64_to_atom" => "3", 
			"float32_to_atom" => "3", 
			"free_console" => "3", 
			"get" => "3", 
			"getc" => "3", 
			"gets" => "3", 
			"get_bytes" => "3", 
			"get_key" => "3", 
			"get_mouse" => "3", 
			"getenv" => "3", 
			"get_position" => "3", 
			"graphics_mode" => "3", 
			"get_all_palette" => "3", 
			"get_active_page" => "3", 
			"get_display_page" => "3", 
			"get_screen_char" => "3", 
			"get_pixel" => "3", 
			"get_vector" => "3", 
			"int_to_bytes" => "3", 
			"int_to_bits" => "3", 
			"instance" => "3", 
			"length" => "3", 
			"lower" => "3", 
			"log" => "3", 
			"lock_file" => "3", 
			"lock_memory" => "3", 
			"match" => "3", 
			"mouse_events" => "3", 
			"mouse_pointer" => "3", 
			"machine_func" => "3", 
			"machine_proc" => "3", 
			"mem_copy" => "3", 
			"mem_set" => "3", 
			"message_box" => "3", 
			"not_bits" => "3", 
			"or_bits" => "3", 
			"open" => "3", 
			"open_dll" => "3", 
			"PI" => "3", 
			"prepend" => "3", 
			"power" => "3", 
			"print" => "3", 
			"printf" => "3", 
			"puts" => "3", 
			"prompt_string" => "3", 
			"prompt_number" => "3", 
			"platform" => "3", 
			"profile" => "3", 
			"position" => "3", 
			"palette" => "3", 
			"put_screen_char" => "3", 
			"pixel" => "3", 
			"polygon" => "3", 
			"peek" => "3", 
			"peek4s" => "3", 
			"peek4u" => "3", 
			"poke" => "3", 
			"poke4" => "3", 
			"rand" => "3", 
			"repeat" => "3", 
			"reverse" => "3", 
			"remainder" => "3", 
			"read_bitmap" => "3", 
			"register_block" => "3", 
			"routine_id" => "3", 
			"sin" => "3", 
			"sort" => "3", 
			"sqrt" => "3", 
			"sprintf" => "3", 
			"seek" => "3", 
			"system" => "3", 
			"system_exec" => "3", 
			"sleep" => "3", 
			"scroll" => "3", 
			"save_bitmap" => "3", 
			"set_active_page" => "3", 
			"set_display_page" => "3", 
			"sound" => "3", 
			"save_text_image" => "3", 
			"save_screen" => "3", 
			"save_image" => "3", 
			"set_vector" => "3", 
			"set_rand" => "3", 
			"tan" => "3", 
			"time" => "3", 
			"tick_rate" => "3", 
			"trace" => "3", 
			"text_color" => "3", 
			"text_rows" => "3", 
			"upper" => "3", 
			"unlock_file" => "3", 
			"unregister_block" => "3", 
			"use_vesa" => "3", 
			"value" => "3", 
			"video_config" => "3", 
			"wildcard_match" => "3", 
			"wildcard_file" => "3", 
			"wait_key" => "3", 
			"where" => "3", 
			"walk_dir" => "3", 
			"wrap" => "3", 
			"xor_bits" => "3", 
			"<" => "4", 
			"<=" => "4", 
			">" => "4", 
			">=" => "4", 
			"=" => "4", 
			"!=" => "4", 
			"+" => "4", 
			"+=" => "4", 
			"-" => "4", 
			"-=" => "4", 
			"*" => "4", 
			"*=" => "4", 
			"//" => "4", 
			"/" => "4", 
			"/=" => "4", 
			".." => "4", 
			"&" => "4", 
			"&=" => "4");

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

}

?>
