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
* 
* @ingroup	ServicesMigration
*/
class ilUpdateUtilsMailMigration
{	
	public static function removeTrailingPathSeparators($path)
	{
		$path = preg_replace("/[\/\\\]+$/", "", $path);
		return $path;
	}

	public static function getWebspaceDir($mode = "filesystem")
	{
		global $ilias;

		if ($mode == "filesystem")
		{
			return "./".ILIAS_WEB_DIR."/".$ilias->client_id;
		}
		else
		{
			if (defined("ILIAS_MODULE"))
			{
				return "../".ILIAS_WEB_DIR."/".$ilias->client_id;
			}
			else
			{
				return "./".ILIAS_WEB_DIR."/".$ilias->client_id;
			}
		}

		//return $ilias->ini->readVariable("server","webspace_dir");
	}

	public static function getDataDir()
	{
		return CLIENT_DATA_DIR;
	}

	public static function makeDir($a_dir)
	{
		$a_dir = trim($a_dir);

		// remove trailing slash (bugfix for php 4.2.x)
		if (substr($a_dir,-1) == "/")
		{
			$a_dir = substr($a_dir,0,-1);
		}

		// check if a_dir comes with a path
		if (!($path = substr($a_dir,0, strrpos($a_dir,"/") - strlen($a_dir))))
		{
			$path = ".";
		}

		// create directory with file permissions of parent directory
		umask(0000);
		return @mkdir($a_dir,fileperms($path));
	}

	public static function makeDirParents($a_dir)
	{
		$dirs = array($a_dir);
		$a_dir = dirname($a_dir);
		$last_dirname = '';
		while($last_dirname != $a_dir)
		{
			array_unshift($dirs, $a_dir);
			$last_dirname = $a_dir;
			$a_dir = dirname($a_dir);
		}

		// find the first existing dir
		$reverse_paths = array_reverse($dirs, TRUE);
		$found_index = -1;
		foreach ($reverse_paths as $key => $value)
		{
			if ($found_index == -1)
			{
				if (is_dir($value))
				{
					$found_index = $key;
				}
			}
		}

		umask(0000);
		foreach ($dirs as $dirindex => $dir)
		{
			// starting with the longest existing path
			if ($dirindex >= $found_index)
			{
				if (! file_exists($dir))
				{
					if (strcmp(substr($dir,strlen($dir)-1,1),"/") == 0)
					{
						// on some systems there is an error when there is a slash
						// at the end of a directory in mkdir, see Mantis #2554
						$dir = substr($dir,0,strlen($dir)-1);
					}
					if (! mkdir($dir, $umask))
					{
						error_log("Can't make directory: $dir");
						return false;
					}
				}
				elseif (! is_dir($dir))
				{
					error_log("$dir is not a directory");
					return false;
				}
				else
				{
					// get umask of the last existing parent directory
					$umask = fileperms($dir);
				}
			}
		}
		return true;
	}

	public static function delDir($a_dir)
	{
		if (!is_dir($a_dir) || is_int(strpos($a_dir, "..")))
		{
			return;
		}

		$current_dir = opendir($a_dir);

		$files = array();

		// this extra loop has been necessary because of a strange bug
		// at least on MacOS X. A looped readdir() didn't work
		// correctly with larger directories
		// when an unlink happened inside the loop. Getting all files
		// into the memory first solved the problem.
		while($entryname = readdir($current_dir))
		{
			$files[] = $entryname;
		}

		foreach($files as $file)
		{
			if(is_dir($a_dir."/".$file) and ($file != "." and $file!=".."))
			{
				self::delDir(${a_dir}."/".${file});
			}
			elseif ($file != "." and $file != "..")
			{
				unlink(${a_dir}."/".${file});
			}
		}

		closedir($current_dir);
		@rmdir(${a_dir});
	}

	public static function getDir($a_dir)
	{
		$current_dir = opendir($a_dir);

		$dirs = array();
		$files = array();
		while($entry = readdir($current_dir))
		{
			if(is_dir($a_dir."/".$entry))
			{
				$dirs[$entry] = array("type" => "dir", "entry" => $entry);
			}
			else
			{
				if ($entry != "." && $entry != "..")
				{
					$size = filesize($a_dir."/".$entry);
					$files[$entry] = array("type" => "file", "entry" => $entry,
					"size" => $size);
				}
			}
		}
		ksort($dirs);
		ksort($files);

		return array_merge($dirs, $files);
	}
	
	public static function ilTempnam()
	{
		$temp_path = self::getDataDir() . "/temp";
		if (!is_dir($temp_path))
		{
			self::makeDir($temp_path);
		}
		$temp_name = tempnam($temp_path, "tmp");
		// --->
		// added the following line because tempnam creates a backslash on some
		// Windows systems which leads to problems, because the "...\tmp..." can be
		// interpreted as "...{TAB-CHARACTER}...". The normal slash works fine
		// even under windows (Helmut Schottmüller, 2005-08-31)
		$temp_name = str_replace("\\", "/", $temp_name);
		// --->
		unlink($temp_name);
		return $temp_name;
	}
	
	public static function rename($source, $target)
	{
		return @rename($source, $target);
	}
}
?>