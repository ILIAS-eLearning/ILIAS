<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** @defgroup ServicesUtilities Services/Utilities
 */

/**
* fileUtils class
* various functions for zip-archive handling
*
* @author Jan Hippchen <janhippchen@gmx.de>
*
* @ingroup	ServicesUtilities
*/
class ilFileUtils
{
	/**
	 * unzips in given directory and processes uploaded zip for use as single files
	 *
	 * @author Jan Hippchen
	 * @version 1.6.9.07
	 * @param string $a_directory Directory to unzip 
	 * @param string $a_file Filename of archive
	 * @param boolean structure  True if archive structure is to be overtaken
	 * @param integer $ref_id ref_id of parent object, if null, files won�t be included in system (just checked)
	 * @param string containerType object type of created containerobjects (folder or category)
	 * @return integer errorcode errorcode 0 - everything ok, 1 - broken file (not yet supported), 2 - virus found, 3 - flat upload not possible due to same filenames
	 */
	 
	function processZipFile ($a_directory, $a_file, $structure, $ref_id = null, $containerType = null) {

		global $lng;
		include_once("class.ilUtil.php");
		
		$pathinfo = pathinfo($a_file);
		$file = $pathinfo["basename"];

		// Copy zip-file to new directory, unzip and remove it
		// TODO: check archive for broken file
		copy ($a_file, $a_directory . "/" . $file);
		ilUtil::unzip($a_directory . "/" . $file);
		unlink ($a_directory . "/" . $file);

		// Stores filename and paths into $filearray to check for viruses and 
		ilFileUtils::recursive_dirscan($a_directory, $filearray);

		// if there are no files unziped (->broken file!)
		if (empty($filearray)) {
			return 1;
		}

		// virus handling
		foreach ($filearray["file"] as $key => $value)
		{
			$vir = ilUtil::virusHandling($filearray[path][$key], $value);
			if (!$vir[0])
			{
				// Unlink file, send info and return errorcode 2 - virus found
				unlink($a_file);
				ilUtil::sendInfo($lng->txt("file_is_infected")."<br />".
				$vir[1], true);
				return 2;
			}
			else
			{
				if ($vir[1] != "")
				{
					ilUtil::sendInfo($vir[1], true);
				}
			}
		}
		
		// If archive is to be used "flat"
		if (!$structure) 
		{
			foreach (array_count_values($filearray["file"]) as $value)
			{
				// Archive contains same filenames in different directories 
				if ($value != "1") return 3;
			}
		}

		// Everything fine since we got here; so we can store files and folders into the system (if ref_id is given)
		if ($ref_id != null)
		{
			ilFileUtils::createObjects ($a_directory, $structure, $ref_id, $containerType);
		}
		return 0;
		
	}

	/**
	 * Recursively scans a given directory and writes path and filename into referenced array
	 *
	 * @author Jan Hippchen
	 * @version 1.6.9.07 
	 * @param string $dir Directory to start from
	 * @param array &$arr Referenced array which is filled with Filename and path
	 */	
	function recursive_dirscan($dir, &$arr)
	{
		$dirlist = opendir($dir);
	  	while ($file = readdir ($dirlist))
		{
			if ($file != '.' && $file != '..')
			{
	    			$newpath = $dir.'/'.$file;
				$level = explode('/',$newpath);
				if (is_dir($newpath))
				{
					ilFileUtils::recursive_dirscan($newpath, $arr);
				}
				else
				{
					$arr["path"][] = $dir . "/";
					$arr["file"][] = end($level);
	      			}
			}
		}
		closedir($dirlist);		
	}


	/**
	 * Recursively scans a given directory and creates file and folder/category objects
	 *
	 * Calls createContainer & createFile to store objects in tree
	 *
	 * @author Jan Hippchen
	 * @version 1.6.9.07
	 * @param string $dir Directory to start from
	 * @param boolean structure  True if archive structure is to be overtaken (otherwise flat inclusion)
	 * @param integer $ref_id ref_id of parent object, if null, files won�t be included in system (just checked)
	 * @param string containerType object type of created containerobjects (folder or category)
	 * @return integer errorcode
	 */	
	function createObjects($dir, $structure, $ref_id, $containerType)
	{
		$dirlist = opendir($dir);
	  	while ($file = readdir ($dirlist))
		{
			if ($file != '.' && $file != '..')
			{
	    			$newpath = $dir.'/'.$file;
				$level = explode('/',$newpath);
				if (is_dir($newpath))
				{
					if ($structure) 
					{
						$new_ref_id = ilFileUtils::createContainer($file, $ref_id, $containerType);
						ilFileUtils::createObjects($newpath, $structure, $new_ref_id, $containerType);
					}
					else 
					{
						ilFileUtils::createObjects($newpath, $structure, $ref_id, $containerType);
					}
				}
				else
				{
					ilFileUtils::createFile (end($level),$dir,$ref_id);
	      			}
			}
		}
		closedir($dirlist);		
	}
	
	
	/**
	 * Creates and inserts container object (folder/category) into tree
	 *
	 * @author Jan Hippchen
	 * @version 1.6.9.07	
	 * @param string $name Name of the object
	 * @param integer $ref_id ref_id of parent
	 * @param string $containerType Fold or Cat
	 * @return integer ref_id of containerobject
	 */
	function createContainer($name, $ref_id, $containerType) 
	{
		if ($containerType == "Category") 
		{
			include_once("./Modules/Category/classes/class.ilObjCategory.php");
			$newObj = new ilObjCategory();
			$newObj->setType("cat");
		}
		if ($containerType == "Folder")
		{
			include_once("./classes/class.ilObjFolder.php");
			$newObj = new ilObjFolder();
			$newObj->setType("fold");		
		}

		$newObj->setTitle($name);
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($ref_id);
		$newObj->setPermissions($ref_id);
		$newObj->initDefaultRoles();
		
		if ($newObj->getType() == "cat") 
		{
			global $lng;
			$newObj->addTranslation($name,"", $lng->getLangKey(), $lng->getLangKey());
		}
		
		return $newObj->getRefId();
	}
	
	/**
	 * Creates and inserts file object into tree
	 *
	 * @author Jan Hippchen
	 * @version 1.6.9.07	
	 * @param string $filename Name of the object
	 * @param string $path Path to file 
	 * @param integer $ref_id ref_id of parent
	 */
	function createFile ($filename, $path, $ref_id)
	{
		// create and insert file in grp_tree
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType($this->type);
		$fileObj->setTitle(ilUtil::stripSlashes($filename));
		$fileObj->setFileName(ilUtil::stripSlashes($filename));
		
		// better use this, mime_content_type is deprecated
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$fileObj->setFileType(ilObjMediaObject::getMimeType($path. "/" . $filename));
		
		$fileObj->setFileSize(filesize($path. "/" . $filename));
		$fileObj->create();
		$fileObj->createReference();

		$fileObj->putInTree($ref_id);
		$fileObj->setPermissions($ref_id);
	
		// upload file to filesystem

		$fileObj->createDirectory();

		$fileObj->storeUnzipedFile($path. "/" . $filename,ilUtil::stripSlashes($filename));

	}	
	
} // END class.ilFileUtils


?>