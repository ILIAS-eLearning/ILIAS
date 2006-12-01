<?php
	/*
		+-----------------------------------------------------------------------------+
		| ILIAS open source                                                           |
		+-----------------------------------------------------------------------------+
		| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
		|                                                                             |
		| This program is free software; you can redistribute it and/or               |
		| modify it under the terms of the GNU General Public License                 |
		| as published by the Free Software Foundation; either version 2              |
		| of the License, or (at your option) any later version.                      |
		|                                                                             |
		| This program is distributed in the hope that it will be useful,             |
		| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
		| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
		| GNU General Public License for more details.                                |
		|                                                                             |
		| You should have received a copy of the GNU General Public License           |
		| along with this program; if not, write to the Free Software                 |
		| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
		+-----------------------------------------------------------------------------+
	*/


/**
* Validation of SCORM-XML Files
*
* @author Romeo Kienzler contact@kienzler.biz
* @company 21 LearnLine AG info@21ll.com
*
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
								$this->validateXML($dir.$file);
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
