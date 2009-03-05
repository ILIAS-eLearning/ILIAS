<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* This is the global ILIAS test suite. It searches automatically for
* components test suites by scanning all Modules/.../test and
* Services/.../test directories for test suite files.
*
* Test suite files are identified automatically, if they are named
* "ilServices[ServiceName]Suite.php" or ilModules[ModuleName]Suite.php".
*
* @author	<alex.killing@gmx.de>
*/
class ilGlobalSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilGlobalSuite();

		echo "\n";
		
		// scan Modules and Services directories
		$basedirs = array("Services", "Modules");
		
		foreach ($basedirs as $basedir)
		{
			// read current directory
			$dir = opendir($basedir);

			while($file = readdir($dir))
			{
				if ($file != "." && $file != ".." && is_dir($basedir."/".$file))
				{
					$suite_path =
						$basedir."/".$file."/test/il".$basedir.$file."Suite.php";
					if (is_file($suite_path))
					{
						include_once($suite_path);
						
						$name = "il".$basedir.$file."Suite";
						$s = new $name();
						echo "Adding Suite: ".$name."\n";
						$suite->addTest($s->suite());
						//$suite->addTestSuite("ilSettingTest");
					}
				}
			}
		}
		echo "\n";

        return $suite;
    }
}
?>
