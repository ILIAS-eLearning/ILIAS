<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Validation of SCORM-XML Files
*
* @author Romeo Kienzler contact@kienzler.biz
* @company 21 LearnLine AG info@21ll.com
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMValidator {
		var $dir,$flag,$summary;

		function validateXML($file)
		{
			exec(ilUtil::getJavaPath()." -jar ".ilUtil::escapeShellArg(ILIAS_ABSOLUTE_PATH."/Modules/ScormAicc/validation/vali.jar")." ".ilUtil::escapeShellArg($file)." 2>&1", $error);
			if (count($error) != 0)
			{
				$this->summary[] = "";
				$this->summary[] = "<b>File: $file</b>";
				foreach($error as $line)
				{
					$this->summary[] = $line;
//echo "<br><b>".$line."</b>";
				}
			}
		}

		function searchDir($dir) {
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if (!eregi("^[\.]{1,2}",$file)) {
							//2DO FIXME regex machen dass nur . und .. erkannt werden und nicht .lala. oder so
							if (is_dir($dir.$file)) {
								// This is commented because subdirecories of my scromexamples contain xml files which aren't valid!
								//$this->searchDir($dir.$file."/");
							}
							if (eregi("(\.xml)$",$file)) {
								
								// we skip index.xml and indexMD.xml which come from the
								// scorm editor and currently do not validate against anything
								if ($file != "index.xml" && $file != "indexMD.xml")
								{
									$this->validateXML($dir.$file);
								}
							}
						}
					}
				}
				closedir($dh);
			}
		}

		function ilObjSCORMValidator($directory) {
			$this->dir = $directory.'/';
		}

		function validate()
		{
			$this->summary = array();
			$this->searchDir($this->dir);
			if(count($this->summary) == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function getSummary()
		{
			$summary = "";

			foreach ($this->summary as $line)
			{
				$summary .= $line."<br>";
			}

			return $summary;
		}
}

?>
