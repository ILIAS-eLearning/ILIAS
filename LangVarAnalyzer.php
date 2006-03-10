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



// language variables analyzer
// a.killing, ilias opensource


class ilLangVarAnalyzer
{
	var $dirs;
	var $ftypes;
	var $langvar;
	var $common;
	var $others;

	function setDirectories($a_dirs)
	{
		$this->dirs = $a_dirs;
	}

	function setFileTypes($a_ftypes)
	{
		$this->ftypes = $a_ftypes;
	}

	function getSuffix($a_file)
	{
		$dotpos = strrpos($a_file, ".");
		return substr($a_file, $dotpos, strlen($a_file) - $dotpos);
	}

	function parseFiles()
	{
		foreach ($this->dirs as $dir)
		{
			if (is_dir($dir))
			{
				$dirh = opendir($dir);
				while (false !== ($file = readdir($dirh)))
				{
					if (in_array($this->getSuffix($file), $this->ftypes))
					{
						$fullname = $dir."/".$file;
						//if($fullname == "./login.php")
						$this->parseCode($fullname);
					}
				}
			}
		}
	}

	function parseCode($a_file)
	{
		$fileh = fopen($a_file, "r");
		$code = fread($fileh, filesize($a_file));
		//if (eregi("lng->txt/(\"(([0-9]|_|[a-z]|[A-Z])*)\"/)", $code, $found))
		while (eregi("lng->txt\(\"([^\)]*)\"\)", $code, $found))
		{
			$this->langvar[$found[1]][] = $a_file;
			$code = str_replace($found[0], "", $code);
		}

		fclose($fileh);
	}

	function printVars()
	{
		ksort($this->langvar);
		reset($this->langvar);
		foreach ($this->langvar as $langvar => $files)
		{
			echo $langvar." <b>".count($files)."</b> ".implode(", ", $files)."<br>";
		}
	}

	function catVars()
	{
		reset($this->langvar);
		foreach ($this->langvar as $langvar => $files)
		{
			$cnt = count($files);
			if ($cnt == 1)
				$this->others[$files[0]][] = $langvar;
			else
				$this->common[$langvar] = $cnt;
		}
		arsort($this->common);
		ksort($this->others);
	}

	function printCommons()
	{
		reset($this->common);
		foreach ($this->common as $langvar => $cnt)
		{
			echo $langvar." <b>".$cnt."</b> ".implode(", ", $this->langvar[$langvar])."<br>";
		}
	}


	function printOthers()
	{
		reset($this->others);
		foreach ($this->others as $file => $langvars)
		{
			echo $langvar." <b>".$file." (".count($langvars).")</b>: ".implode(", ", $langvars)."<br>";
		}
	}

	//function get

}

exit;

$analyzer = new ilLangVarAnalyzer();
$analyzer->setDirectories(array(".", "./classes", "./include"));
$analyzer->setFileTypes(array(".php"));
//$analyzer->setLangFile("./lang/ilias_en.lang");
$analyzer->parseFiles();
$analyzer->catVars();
//$analyzer->printCommons();
$analyzer->printOthers();

?>