<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
	require_once("$BEAUT_PATH/Beautifier/HFile.php");

	class HFile_MFL extends HFile
	{
		function HFile_MFL()
		{
     			$this->HFile(); // Call the HFile constructor.
			$this->colours = array("blue", "brown", "red");
			$this->delimiters = array("	", " ", "(", ")", ".");
			$this->keywords = array(
				"import" 	=> "1",
				"function" 	=> "1",
				"forall" 	=> "1",
				"return" 	=> "2",
				"print"		=> "2",
				"+" 		=> "3",
				"=" 		=> "3"
			);
	#		$this->stringchars = array("\"", "'");
	#		$this->escchar = "\\";
		}
	}
?>
