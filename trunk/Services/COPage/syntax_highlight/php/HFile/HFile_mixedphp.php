<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");

class HFile_plain extends HFile{

 function HFile_plain(){

    $this->HFile();	


/*************************************/
// Beautifier Highlighting Configuration File 
// Plain with PHP
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

$this->zones			= array(
					array("<?", "?>", "HFile_php3")
				);
}
}
?>
