<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_dibol extends HFile{
   function HFile_dibol(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// DiBoL
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "gray", "purple", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", "\"", "'", "<", ">", " ", ",", " ", "?", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AP" => "", 
			"AR" => "", 
			"DEF" => "", 
			"RBP" => "", 
			".align" => "1", 
			".define" => "1", 
			".else" => "1", 
			".end" => "1", 
			".endc" => "1", 
			".function" => "1", 
			".ifdef" => "1", 
			".ifndef" => "1", 
			".include" => "1", 
			".proc" => "1", 
			".subroutine" => "1", 
			"accept" => "1", 
			"begin" => "1", 
			"begincase" => "1", 
			"byte" => "1", 
			"call" => "1", 
			"case" => "1", 
			"clear" => "1", 
			"close" => "1", 
			"common" => "1", 
			"decr" => "1", 
			"delet" => "3", 
			"delete" => "1", 
			"display" => "1", 
			"do" => "1", 
			"else" => "1", 
			"end" => "1", 
			"endcase" => "1", 
			"endglobal" => "1", 
			"endgroup" => "1", 
			"endusing" => "1", 
			"exit" => "1", 
			"exitloop" => "1", 
			"find" => "1", 
			"flush" => "1", 
			"for" => "1", 
			"forever" => "1", 
			"forms" => "1", 
			"freturn" => "1", 
			"from" => "1", 
			"function" => "1", 
			"get" => "1", 
			"gets" => "1", 
			"global" => "1", 
			"goto" => "1", 
			"group" => "1", 
			"if" => "1", 
			"incr" => "1", 
			"locase" => "1", 
			"long" => "1", 
			"lpque" => "1", 
			"merge" => "1", 
			"nextloop" => "1", 
			"nop" => "1", 
			"of" => "1", 
			"offerror" => "1", 
			"on" => "1", 
			"onerror" => "1", 
			"open" => "1", 
			"proc" => "1", 
			"purge" => "1", 
			"put" => "1", 
			"puts" => "1", 
			"quad" => "1", 
			"range" => "1", 
			"read" => "1", 
			"reads" => "1", 
			"record" => "1", 
			"renam" => "3", 
			"repeat" => "1", 
			"return" => "1", 
			"send" => "1", 
			"set" => "1", 
			"sleep" => "1", 
			"sort" => "1", 
			"stop" => "1", 
			"store" => "1", 
			"subroutine" => "1", 
			"then" => "1", 
			"thru" => "1", 
			"unlock" => "1", 
			"until" => "1", 
			"upcase" => "1", 
			"using" => "1", 
			"while" => "1", 
			"word" => "1", 
			"write" => "1", 
			"writes" => "1", 
			"xcall" => "1", 
			"xreturn" => "1", 
			".and." => "2", 
			".band." => "2", 
			".bnand." => "2", 
			".bnot." => "2", 
			".bor." => "2", 
			".bxor." => "2", 
			".eq." => "2", 
			".eqs." => "2", 
			".ge." => "2", 
			".ges." => "2", 
			".gt." => "2", 
			".gts." => "2", 
			".le." => "2", 
			".les." => "2", 
			".lt." => "2", 
			".lts." => "2", 
			".ne." => "2", 
			".nes." => "2", 
			".not." => "2", 
			".or." => "2", 
			".xor." => "2", 
			"%abs" => "3", 
			"%atrim" => "3", 
			"%bkstr" => "3", 
			"%char" => "3", 
			"%chopen" => "3", 
			"%date" => "3", 
			"%datecompiled" => "3", 
			"%datetime" => "3", 
			"%decml" => "3", 
			"%erlin" => "3", 
			"%ernum" => "3", 
			"%error" => "3", 
			"%false" => "3", 
			"%instr" => "3", 
			"%int" => "3", 
			"%integer" => "3", 
			"%len" => "3", 
			"%line" => "3", 
			"%rdlen" => "3", 
			"%recnum" => "3", 
			"%round" => "3", 
			"%rsize" => "3", 
			"%rvstr" => "3", 
			"%string" => "3", 
			"%syserr" => "3", 
			"%tnmbr" => "3", 
			"%trim" => "3", 
			"%true" => "3", 
			"%zoned" => "3", 
			"ascii" => "3", 
			"atrim" => "3", 
			"cmdlin" => "3", 
			"date" => "3", 
			"decml" => "3", 
			"envrn" => "3", 
			"error" => "3", 
			"ertxt" => "3", 
			"exec" => "3", 
			"execute" => "3", 
			"fatal" => "3", 
			"fill" => "3", 
			"filnm" => "3", 
			"flags" => "3", 
			"free" => "3", 
			"getlog" => "3", 
			"instr" => "3", 
			"isamc" => "3", 
			"isclr" => "3", 
			"iskey" => "3", 
			"issts" => "3", 
			"len" => "3", 
			"randm" => "3", 
			"shell" => "3", 
			"size" => "3", 
			"spawn" => "3", 
			"time" => "3", 
			"tnmbr" => "3", 
			"trim" => "3", 
			"versn" => "3", 
			"wait" => "3", 
			"wkday" => "3", 
			"w_area" => "3", 
			"w_brdr" => "3", 
			"w_caption" => "3", 
			"w_disp" => "3", 
			"w_exit" => "3", 
			"w_flds" => "3", 
			"w_info" => "3", 
			"w_init" => "3", 
			"w_proc" => "3", 
			"w_restore" => "3", 
			"w_save" => "3", 
			"w_updt" => "3", 
			"^a" => "3", 
			"^d" => "3", 
			"^defined" => "3", 
			"^i" => "3", 
			"^len" => "3", 
			"^passed" => "3", 
			"^size" => "3", 
			"input" => "4", 
			"lpoff" => "4", 
			"lpon" => "4", 
			"lpout" => "4", 
			"mesag" => "4", 
			"outpt" => "4", 
			"rdate" => "4", 
			"terid" => "4", 
			"vim_bad_stknum_ok" => "4", 
			"vim_close" => "4", 
			"vim_comp_id" => "4", 
			"vim_delete" => "4", 
			"vim_find" => "4", 
			"vim_get_file" => "4", 
			"vim_get_stknum" => "4", 
			"vim_init" => "4", 
			"vim_open" => "4", 
			"vim_override_cost" => "4", 
			"vim_read" => "4", 
			"vim_reads" => "4", 
			"vim_set_prompt" => "4", 
			"vim_squeeze_key" => "4", 
			"vim_store" => "4", 
			"vim_unlock" => "4", 
			"vim_vendor" => "4", 
			"vim_write" => "4", 
			"vim_writes" => "4", 
			"wate" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing", 
			"3" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
