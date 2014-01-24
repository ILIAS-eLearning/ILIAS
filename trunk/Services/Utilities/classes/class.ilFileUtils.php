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
include_once 'Services/Utilities/classes/class.ilFileUtilsException.php';


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
	 * @param integer $ref_id ref_id of parent object, if null, files wont be included in system (just checked)
	 * @param string containerType object type of created containerobjects (folder or category)
	 * @throws ilFileUtilsException
	 */
	 
	function processZipFile ($a_directory, $a_file, $structure, $ref_id = null, $containerType = null, $tree = null, $access_handler = null) {

		global $lng;
		include_once("Services/Utilities/classes/class.ilUtil.php");
				
		$pathinfo = pathinfo($a_file);
		$file = $pathinfo["basename"];

		// Copy zip-file to new directory, unzip and remove it
		// TODO: check archive for broken file
		//copy ($a_file, $a_directory . "/" . $file);
		move_uploaded_file($a_file, $a_directory . "/" . $file);
		ilUtil::unzip($a_directory . "/" . $file);
		unlink ($a_directory . "/" . $file);
//echo "-".$a_directory . "/" . $file."-";
		// Stores filename and paths into $filearray to check for viruses 
		// Checks if filenames can be read, else -> throw exception and leave
		ilFileUtils::recursive_dirscan($a_directory, $filearray);
		
		// if there are no files unziped (->broken file!)
		if (empty($filearray)) {
			throw new ilFileUtilsException($lng->txt("archive_broken"), ilFileUtilsException::$BROKEN_FILE);
			break;
		}

		// virus handling
		foreach ($filearray["file"] as $key => $value)
		{
			// remove "invisible" files
			if(substr($value, 0, 1) == "." || stristr($filearray["path"][$key], "/__MACOSX/"))
			{				
				unlink($filearray["path"][$key].$value);
				unset($filearray["path"][$key]);
				unset($filearray["file"][$key]);				
				continue;
			}			
			
			$vir = ilUtil::virusHandling($filearray["path"][$key], $value);
			if (!$vir[0])
			{
				// Unlink file and throw exception
				unlink($filearray[path][$key]);
				throw new ilFileUtilsException($lng->txt("file_is_infected")."<br />".$vir[1], ilFileUtilsException::$INFECTED_FILE);
				break;
			}
			else
			{
				if ($vir[1] != "")
				{
					throw new ilFileUtilsException($vir[1], ilFileUtilsException::$INFECTED_FILE);
					break;
				}
			}			
		}
		
		// If archive is to be used "flat"
		if (!$structure) 
		{	
			foreach (array_count_values($filearray["file"]) as $key => $value)
			{
				// Archive contains same filenames in different directories 
				if ($value != "1") 
				{	
					$doublettes .= " '" . ilFileUtils::utf8_encode($key) . "'";
					
				}	
			}
			if (isset($doublettes))
			{
				throw new ilFileUtilsException($lng->txt("exc_upload_error") . "<br />" . $lng->txt("zip_structure_error") . $doublettes , 
								ilFileUtilsException::$DOUBLETTES_FOUND);
				break;
			}
		}
		else
		{			
			$mac_dir = $a_directory."/__MACOSX";
			if(file_exists($mac_dir))
			{
				ilUtil::delDir($mac_dir);
			}		
		}

		// Everything fine since we got here; so we can store files and folders into the system (if ref_id is given)
		if ($ref_id != null)
		{
			ilFileUtils::createObjects ($a_directory, $structure, $ref_id, $containerType, $tree, $access_handler);
		}
		
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
		global $lng;

		$dirlist = opendir($dir);
	  	while (false !== ($file = readdir ($dirlist)))
		{
			if (!is_file($dir . "/" .  $file) && !is_dir($dir . "/" . $file)) 
			{
				throw new ilFileUtilsException($lng->txt("filenames_not_supported"), ilFileUtilsException::$BROKEN_FILE);
			}

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
	function createObjects($dir, $structure, $ref_id, $containerType, $tree = null, $access_handler = null)
	{
		$dirlist = opendir($dir);
		
	  	while (false !== ($file = readdir ($dirlist)))
		{
			if (!is_file($dir . "/" . $file) && !is_dir($dir . "/" . $file)) 
			{
				throw new ilFileUtilsException($lng->txt("filenames_not_supported") , ilFileUtilsException::$BROKEN_FILE);
			}		
			if ($file != '.' && $file != '..')
			{
	    			$newpath = $dir.'/'.$file;
				$level = explode('/',$newpath);
				if (is_dir($newpath))
				{
					if ($structure) 
					{
					  	$new_ref_id = ilFileUtils::createContainer(ilFileUtils::utf8_encode($file), $ref_id, $containerType, $tree, $access_handler);						
						ilFileUtils::createObjects($newpath, $structure, $new_ref_id, $containerType, $tree, $access_handler);
					}
					else 
					{
						ilFileUtils::createObjects($newpath, $structure, $ref_id, $containerType, $tree, $access_handler);
					}
				}
				else
				{
					ilFileUtils::createFile (end($level), $dir, $ref_id, $tree, $access_handler);
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
	function createContainer($name, $ref_id, $containerType, $tree = null, $access_handler = null) 
	{
		switch($containerType)
		{
			case "Category":		
				include_once("./Modules/Category/classes/class.ilObjCategory.php");
				$newObj = new ilObjCategory();
				$newObj->setType("cat");
				break;
			
			case "Folder":		
				include_once("./Modules/Folder/classes/class.ilObjFolder.php");
				$newObj = new ilObjFolder();
				$newObj->setType("fold");		
				break;
			
			case "WorkspaceFolder":
				include_once("./Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolder.php");
				$newObj = new ilObjWorkspaceFolder();		
				break;
		}

		$newObj->setTitle($name);
		$newObj->create();
		
		// repository
		if(!$access_handler)
		{
			$newObj->createReference();
			$newObj->putInTree($ref_id);
			$newObj->setPermissions($ref_id);
			
			if ($newObj->getType() == "cat") 
			{
				global $lng;
				$newObj->addTranslation($name,"", $lng->getLangKey(), $lng->getLangKey());
			}

			return $newObj->getRefId();
		}
		// workspace
		else
		{
			$node_id = $tree->insertObject($ref_id, $newObj->getId());
			$access_handler->setPermissions($ref_id, $node_id);
			
			return $node_id;
		}
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
	function createFile ($filename, $path, $ref_id, $tree = null, $access_handler = null)
	{
		global $rbacsystem;	
		
		if(!$access_handler)
		{
			$permission = $rbacsystem->checkAccess("create", $ref_id, "file");
		}
		else
		{
			$permission = $access_handler->checkAccess("create", "", $ref_id, "file");
		}
		if ($permission) {
	
			// create and insert file in grp_tree
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$fileObj = new ilObjFile();
			$fileObj->setType($this->type);
			$fileObj->setTitle(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
			$fileObj->setFileName(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
		
			// better use this, mime_content_type is deprecated
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$fileObj->setFileType(ilObjMediaObject::getMimeType($path. "/" . $filename));			
			$fileObj->setFileSize(filesize($path. "/" . $filename));
			$fileObj->create();
			
			// repository
			if(!$access_handler)
			{
				$fileObj->createReference();	
				$fileObj->putInTree($ref_id);
				$fileObj->setPermissions($ref_id);
			}
			else
			{
				$node_id = $tree->insertObject($ref_id, $fileObj->getId());
				$access_handler->setPermissions($ref_id, $node_id);
			}
		
			// upload file to filesystem	
			$fileObj->createDirectory();	
			$fileObj->storeUnzipedFile($path. "/" . $filename,ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
		}
		else {
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
	}
	
	/**
	 * utf8-encodes string if it is not a valid utf8-string.
	 *
	 * @author Jan Hippchen
	 * @version 1.12.3.08	
	 * @param string $string String to encode
	 * @return string utf-8-encoded string
	 */
	function utf8_encode($string) {
	   
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		return (preg_match('%^(?:
			[\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)*$%xs', $string))? $string : utf8_encode($string);
	}
	
	
	/**
	*	decodes base encoded file row by row to prevent memory exhaust
	* @param string $filename	name of file to read
	* @param string $fileout name where to put decoded file
	*/
	function fastBase64Decode ($filein, $fileout) 
	{
		$fh = fopen($filein, 'rb');
		$fh2= fopen($fileout, 'wb');
		stream_filter_append($fh2, 'convert.base64-decode');

		while (!feof($fh)){
		    $chunk = fgets($fh);
		    if ($chunk === false)
		        break;
		    fwrite ($fh2, $chunk);
		}
		fclose ($fh);
		fclose ($fh2);
		return true;
	}

	/**
	*	decodes base encoded file row by row to prevent memory exhaust
	* @param string $filename	name of file to read
	* @return string base decoded content
	*/
	function fastBase64Encode ($filein, $fileout) 
	{
		$fh = fopen($filein, 'rb');
		$fh2= fopen($fileout, 'wb');
		stream_filter_append($fh2, 'convert.base64-encode');
		
		while (feof ($fh)) {
			$chunk = fgets($fh,76);
			if ($chunk === false) 
			{
				break;
			}
			fwrite ($fh2, $chunk);			
		}
		fclose ($fh);
		fclose ($fh2);
	}			
	
	/**
	*
  * fast compressing the file with the zlib-extension without memory consumption
	*
  * @param string $in filename
  * @param string $out filename
  * @param string $level compression level from 1 to 9 
  * @return bool
	*/
	function fastGZip ($in, $out, $level="9")
	{
    if (!file_exists ($in) || !is_readable ($in))
        return false;
    if ((!file_exists ($out) && !is_writable (dirname ($out)) || (file_exists($out) && !is_writable($out)) ))
        return false;
    
    $in_file = fopen ($in, "rb");
    if (!$out_file = gzopen ($out, "wb".$param)) {
        return false;
    }
    
    while (!feof ($in_file)) {
        $buffer = fgets ($in_file, 4096);
        gzwrite ($out_file, $buffer, 4096);
    }

    fclose ($in_file);
    gzclose ($out_file);
    
    return true;
	}

	/**
	 * fast uncompressing the file with the zlib-extension without memory consumption
	 *	 
	 * @param string $in filename
	 * @param string $out filename
	 * @return bool
	 * 
	*/
	function fastGunzip ($in, $out)
	{
    if (!file_exists ($in) || !is_readable ($in))
        return false;
    if ((!file_exists ($out) && !is_writable (dirname ($out)) || (file_exists($out) && !is_writable($out)) ))
        return false;

    $in_file = gzopen ($in, "rb");
    $out_file = fopen ($out, "wb");

    while (!gzeof ($in_file)) {
        $buffer = gzread ($in_file, 4096);
        fwrite ($out_file, $buffer, 4096);
    }
 
    gzclose ($in_file);
    fclose ($out_file);
    
    return true;
	}
  
	/**
	 * @param string file absolute path to file
	 */
	public static function _lookupMimeType($a_file)
	{
		if(!file_exists($a_file) or !is_readable($a_file))
		{
			return false;
		}
		
		if(class_exists('finfo'))
		{
			$finfo = new finfo(FILEINFO_MIME);
			return $finfo->buffer(file_get_contents($a_file));
		}
		if(function_exists('mime_content_type'))
		{
			return mime_content_type($a_file);
		}
		return 'application/octet-stream';
	}
	
} // END class.ilFileUtils


?>