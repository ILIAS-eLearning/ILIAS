<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_perl extends HFile{
   function HFile_perl(){
     $this->HFile();

/*************************************/
// Beautifier Highlighting Configuration File 
// Perl
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "1";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", "	", ",", ".", "?", "/", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"-A" => "1", 
			"-B" => "1", 
			"-C" => "1", 
			"-M" => "1", 
			"-O" => "1", 
			"-R" => "1", 
			"-S" => "1", 
			"-T" => "1", 
			"-W" => "1", 
			"-X" => "1", 
			"-b" => "1", 
			"-c" => "1", 
			"-d" => "1", 
			"-e" => "1", 
			"-f" => "1", 
			"-g" => "1", 
			"-k" => "1", 
			"-l" => "1", 
			"-o" => "1", 
			"-p" => "1", 
			"-r" => "1", 
			"-s" => "1", 
			"-t" => "1", 
			"-u" => "1", 
			"-w" => "1", 
			"-x" => "1", 
			"-z" => "1", 
			"__DATA__" => "1", 
			"__END__" => "1", 
			"__FILE__" => "1", 
			"__LINE__" => "1", 
			"continue" => "1", 
			"do" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"for" => "1", 
			"foreach" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"last" => "1", 
			"local" => "1", 
			"my" => "1", 
			"next" => "1", 
			"no" => "1", 
			"package" => "1", 
			"redo" => "1", 
			"return" => "1", 
			"require" => "1", 
			"sub" => "1", 
			"until" => "1", 
			"unless" => "1", 
			"use" => "1", 
			"while" => "1", 
			"accept" => "2", 
			"alarm" => "2", 
			"atan2" => "2", 
			"bind" => "2", 
			"binmode" => "2", 
			"bless" => "2", 
			"caller" => "2", 
			"chdir" => "2", 
			"chmod" => "2", 
			"chomp" => "2", 
			"chop" => "2", 
			"chown" => "2", 
			"chr" => "2", 
			"chroot" => "2", 
			"close" => "2", 
			"closedir" => "2", 
			"connect" => "2", 
			"cos" => "2", 
			"crypt" => "2", 
			"dbmclose" => "2", 
			"dbmopen" => "2", 
			"defined" => "2", 
			"delete" => "2", 
			"die" => "2", 
			"dump" => "2", 
			"each" => "2", 
			"endgrent" => "2", 
			"endhostent" => "2", 
			"endnetent" => "2", 
			"endprotoent" => "2", 
			"endpwent" => "2", 
			"endservent" => "2", 
			"eof" => "2", 
			"eval" => "2", 
			"exec" => "2", 
			"exit" => "2", 
			"exp" => "2", 
			"exists" => "2", 
			"fcntl" => "2", 
			"fileno" => "2", 
			"flock" => "2", 
			"fork" => "2", 
			"formline" => "2", 
			"format" => "2", 
			"getc" => "2", 
			"getgrent" => "2", 
			"getgrgid" => "2", 
			"getgrname" => "2", 
			"gethostbyaddr" => "2", 
			"gethostbyname" => "2", 
			"gethostent" => "2", 
			"getlogin" => "2", 
			"getnetbyaddr" => "2", 
			"getnetbyname" => "2", 
			"getnetent" => "2", 
			"getpeername" => "2", 
			"getpgrp" => "2", 
			"getppid" => "2", 
			"getpriority" => "2", 
			"getprotobyname" => "2", 
			"getprotobynumber" => "2", 
			"getprotoent" => "2", 
			"getpwent" => "2", 
			"getpwnam" => "2", 
			"getpwuid" => "2", 
			"getservbyname" => "2", 
			"getservbyport" => "2", 
			"getservent" => "2", 
			"getsockname" => "2", 
			"getsockopt" => "2", 
			"glob" => "2", 
			"gmtime" => "2", 
			"grep" => "2", 
			"hex" => "2", 
			"index" => "2", 
			"int" => "2", 
			"ioctl" => "2", 
			"join" => "2", 
			"keys" => "2", 
			"kill" => "2", 
			"lc" => "2", 
			"lcfirst" => "2", 
			"length" => "2", 
			"link" => "2", 
			"listen" => "2", 
			"localtime" => "2", 
			"log" => "2", 
			"lstat" => "2", 
			"map" => "2", 
			"mkdir" => "2", 
			"msgctl" => "2", 
			"msgget" => "2", 
			"msgrcv" => "2", 
			"msgsnd" => "2", 
			"oct" => "2", 
			"open" => "2", 
			"opendir" => "2", 
			"ord" => "2", 
			"pack" => "2", 
			"pipe" => "2", 
			"pop" => "2", 
			"pos" => "2", 
			"print" => "2", 
			"printf" => "2", 
			"push" => "2", 
			"quotemeta" => "2", 
			"rand" => "2", 
			"read" => "2", 
			"readdir" => "2", 
			"readline" => "2", 
			"readlink" => "2", 
			"recv" => "2", 
			"ref" => "2", 
			"rename" => "2", 
			"reset" => "2", 
			"reverse" => "2", 
			"rewinddir" => "2", 
			"rindex" => "2", 
			"rmdir" => "2", 
			"scalar" => "2", 
			"seek" => "2", 
			"seekdir" => "2", 
			"select" => "2", 
			"semctl" => "2", 
			"semgett" => "2", 
			"semop" => "2", 
			"send" => "2", 
			"setgrent" => "2", 
			"sethostent" => "2", 
			"setnetent" => "2", 
			"setpgrp" => "2", 
			"setpriority" => "2", 
			"setprotoent" => "2", 
			"setpwent" => "2", 
			"setservent" => "2", 
			"setsockopt" => "2", 
			"shift" => "2", 
			"shmctl" => "2", 
			"shmget" => "2", 
			"shmread" => "2", 
			"shmwrite" => "2", 
			"shutdown" => "2", 
			"sin" => "2", 
			"sleep" => "2", 
			"socket" => "2", 
			"socketpair" => "2", 
			"sort" => "2", 
			"splice" => "2", 
			"split" => "2", 
			"sprintf" => "2", 
			"sqrt" => "2", 
			"srand" => "2", 
			"stat" => "2", 
			"study" => "2", 
			"substr" => "2", 
			"symlink" => "2", 
			"syscall" => "2", 
			"sysopen" => "2", 
			"sysread" => "2", 
			"system" => "2", 
			"syswrite" => "2", 
			"tell" => "2", 
			"telldir" => "2", 
			"tie" => "2", 
			"tied" => "2", 
			"time" => "2", 
			"times" => "2", 
			"truncate" => "2", 
			"uc" => "2", 
			"ucfirst" => "2", 
			"umask" => "2", 
			"undef" => "2", 
			"unlink" => "2", 
			"unpack" => "2", 
			"unshift" => "2", 
			"utime" => "2", 
			"untie" => "2", 
			"values" => "2", 
			"vec" => "2", 
			"wait" => "2", 
			"waitpid" => "2", 
			"wantarray" => "2", 
			"warn" => "2", 
			"write" => "2", 
			"AUTOLOAD" => "3", 
			"and" => "3", 
			"BEGIN" => "3", 
			"CORE" => "3", 
			"cmp" => "3", 
			"DESTROY" => "3", 
			"eq" => "3", 
			"END" => "3", 
			"ge" => "3", 
			"gt" => "3", 
			"le" => "3", 
			"lt" => "3", 
			"ne" => "3", 
			"not" => "3", 
			"m" => "3", 
			"or" => "3", 
			"q" => "3", 
			"qq" => "3", 
			"qw" => "3", 
			"qx" => "3", 
			"SUPER" => "3", 
			"s" => "3", 
			"tr" => "3", 
			"UNIVERSAL" => "3", 
			"x" => "3", 
			"xor" => "3", 
			"y" => "3", 
			"$@" => "4", 
			"@" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.



$this->linkscripts    	= array(
			"2" => "dofunction", 
			"1" => "donothing",
			"3" => "donothing",
			"4" => "donothing");
}

function donothing($keywordin)
{
	return $keywordin;
}

function dofunction($keywordin)
{
        $outlink = "http://perldoc.perl.org/functions/".$keywordin.".html";
        return "<a href=\"$outlink\">$keywordin</a>";
}


}

?>
