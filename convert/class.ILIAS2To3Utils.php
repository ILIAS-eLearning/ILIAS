<?php

/**
* ILIAS 2 to ILIAS 3 content-converting utility class
* 
* Utility functions for ILIAS 2 to ILIAS 3 content-converting class
* 
* @author	Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version	$Id$
*/

class ILIAS2To3Utils
{
	/**
	* constructor 
	* @access	public
	*/
	function ILIAS2To3Utils ()
	{
		// no operation
	}
	
	/**
	* destructor 
	* @access	public
	*/
	function _ILIAS2To3Utils ()
	{
		// no operation
	}
	
	/**
	* Selects value for attribute Structure in element General
	* according to ILIAS 2 object type
	* @param	string	object type [le|st|pg|mm|file|el|test|mc|glos|gl]
	* @return	string	attribute value
	* @access	public
	*/
	function selectStructure ($type)
	{
		switch ($type) 
		{
			case "le":
				$str = "Hierarchical";
				break;
			
			case "st":
				$str = "Hierarchical";
				break;
			
			case "pg":
				$str = "Collection";
				break;
			
			case "mm":
				$str = "Atomic";
				break;
			
			case "file":
				$str = "Atomic";
				break;
			
			case "el":
				$str = "Atomic";
				break;
			
			case "test":
				$str = "Collection";
				break;
			
			case "mc":
				$str = "Collection";
				break;
			
			case "glos":
				$str = "Collection";
				break;
			
			case "gl":
				$str = "Collection";
				break;
		}
		return $str;
	}
	
	/**
	* Selects value for attribute Type in element IntLink
	* according to ILIAS 2 object type
	* @param	string	object type [pg|mm|mc|gl]
	* @return	string	attribute value
	* @access	public
	*/
	function selectTargetType ($type)
	{
		switch ($type) 
		{
			case "pg":
				$str = "PageObject";
				break;
			
			case "mm":
				$str = "MediaObject";
				break;
			
			case "mc":
				$str = "TestItem";
				break;
			
			case "gl":
				$str = "GlossaryItem";
				break;
		}
		return $str;
	}
	
	/**
	* Selects value for attribute LearningResourceType in element Educational
	* according to ILIAS 2 material type
	* @param	string	material type [1-8]
	* @return	string	attribute value
	* @access	public
	*/
	function selectMaterialType ($materialType)
	{
		switch ($materialType) 
		{
			case 1: // Standardtext
				$str = "NarrativeText";
				break;
			
			case 2: // Einleitung
				$str = "NarrativeText";
				break;
			
			case 3: // Zusammenfassung
				$str = "NarrativeText";
				break;
			
			case 4: // Beispiel
				$str = "NarrativeText";
				break;
			
			case 5: // Fallstudie
				$str = "NarrativeText";
				break;
			
			case 6: // Glossar
				$str = "NarrativeText";
				break;
			
			case 7: // Übung
				$str = "Exercise";
				break;
			
			case 8: // Simulation
				$str = "Simulation";
				break;
			
			default: // all exceptions
				$str = "NarrativeText";
		}
		return $str;
	}
	
	/**
	* Selects value for attribute Status in element Lifecycle
	* according to ILIAS 2 status type
	* @param	string	status type [draft|final|revised]
	* @return	string	attribute value
	* @access	public
	*/
	function selectStatus ($status)
	{
		switch ($status) 
		{
			case "draft": // offline
				$str = "Draft";
				break;
			
			case "final": // online
				$str = "Final";
				break;
			
			case "revised": // ?
				$str = "Revised";
				break;
			
			case "": // unavailable
				$str = "Unavailable";
				break;
			
			default: // all exceptions
				$str = "Unavailable";
		}
		return $str;
	}
	
	/**
	* Selects value for attribute Difficulty in element Educational
	* according to ILIAS 2 difficulty type
	* @param	string	status type [0-4]
	* @return	string	attribute value
	* @access	public
	*/
	function selectDifficulty ($difficulty)
	{
		switch ($difficulty) 
		{
			case 0:
				$str = "VeryEasy";
				break;
			
			case 1:
				$str = "Easy";
				break;
			
			case 2:
				$str = "Medium";
				break;
			
			case 3:
				$str = "Difficult";
				break;
			
			case 4:
				$str = "VeryDifficult";
				break;
			
			default: // all exceptions
				$str = "Medium";
		}
		return $str;
	}
	
	/**
	* Selects value for attribute Context in element Educational
	* according to ILIAS 2 level type
	* @param	string	level type [0-11]
	* @return	string	attribute value
	* @access	public
	*/
	function selectLevel ($level)
	{
		switch ($level)
		{
			case 0: // not available
				$str = "Other";
				break;
			
			case 1: // UniversityFirstCycle
				$str = "HigherEducation";
				break;
			
			case 2: // UniversitySecondCycle
				$str = "HigherEducation";
				break;
			
			case 3: // UniversityPostgrade
				$str = "HigherEducation";
				break;
			
			case 4: // PrimaryEducation
				$str = "School";
				break;
			
			case 5: // SecondaryEducation
				$str = "School";
				break;
			
			case 6: // HigherEducation
				$str = "HigherEducation";
				break;
			
			case 7: // TechnicalSchoolFirstCycle
				$str = "HigherEducation";
				break;
			
			case 8: // TechnicalSchoolSecondCycle
				$str = "HigherEducation";
				break;
			
			case 9: // ProfessionalFormation
				$str = "Other";
				break;
			
			case 10: // ContinousFormation
				$str = "Other";
				break;
			
			case 11: // VocationalFormation
				$str = "Other";
				break;
			
			default: // all exceptions
				$str = "Other";
		}
		return $str;
	}
	
	/**
	* Selects value for attribute LearningResourceType in element Educational
	* according to ILIAS 2 material level type
	* @param	string	material level type [0|5]
	* @return	string	attribute value
	* @access	public
	*/
	function selectMaterialLevel ($materialLevel)
	{
		switch ($materialLevel) 
		{
			case 0:
				$str = "Basic Knowledge";
				break;
			
			case 5:
				$str = "In-depth Knowledge";
				break;
			
			default: // all exceptions
				$str = "Unknown";
		}
		return $str;
	}
	
	/**
	* Selects answer value according to ILIAS 2 answer
	* @param	string	answer [y|r|n|f|j]
	* @return	string	answer value [Right|Wrong]
	* @access	public
	*/
	function selectAnswer ($answer)
	{
		switch ($answer) 
		{
			case "y":
			case "r":
			case "j":
				$str = "Right";
				break;
			
			case "f":
			case "n":
				$str = "Wrong";
				break;
		}
		return $str;
	}
	
	/**
	* Selects boolean value according to ILIAS 2 data
	* @param	string	answer [w|r|y|j|n|f]
	* @return	string	boolean value
	* @access	public
	*/
	function selectBool ($data)
	{
		switch ($data) 
		{
			case "w":
			case "r":
			case "y":
			case "j":
				$str = TRUE;
				break;
			
			case "n":
			case "f":
				$str = FALSE;
				break;
			
			default:
				$str = FALSE;
		}
		return $str;
	}
	
	/**
	* Selects alignment values ***
	*//*
	function selectAlignment ($align)
	{
		switch ($align)
		{
			/*
			case 0: // left, wrapped by text
			case 1: // left
			case 2: // right, wrapped by text
			case 3: // right
			case 4: // center
				$str = "";
				break;
						
			case 5: // citation
				$str = "Citation";
				break;
			
			case 6: // mnemonic
				$str = "Mnemonic";
				break;
			
			case 7: // pointed list
				$str = "";
				break;
			
			case 8: // numerical list
				$str = "";
				break;
			
			case 9: // alphabetic list
				$str = "";
				break;
			
			case 10: // list with roman numerals
				$str = "";
				break;
			
			default: // all exceptions
				$str = "";
		}
		return $str;
	}
	*/
	
	/**
	* Selects value for attribute Shape in element MapArea
	* according to ILIAS 2 shape type
	* @param	string	material level type [rect|circle|poly]
	* @return	string	attribute value
	* @access	public
	*/
	function selectShape ($shape)
	{
		switch ($shape) 
		{
			case "rect":
				$str = "Rect";
				break;
			
			case "circle":
				$str = "Circle";
				break;
			
			case "poly":
				$str = "Poly";
				break;
			default:
				$str = "Rect";
		}
		return $str;
	}
	
	/**
	* Fetches all vri tags in a string
	*
	* Example: If the string contains "some text <vri=!100!st!20!> a link </vri> some text"
	* vri_fetch($string,"st") returns an array $arr with $arr["inst"]->100, $arr["type"]->"st"
	* and $arr["id"]->20.
	*
	* @param	string	string, that should be searched through
	* @param	string	vri types, that should be searched, separated by "|", e.g. "mm|st"
	* @param	boolean	true, if vri in input string doesn´t contain tag limiter "<" and ">"
	* @param	boolean	true, if vri in input string doesn´t contain vri string "vri="
	* @return	array	array with fields "inst", "type", "id" and "target"; FALSE, if no vri was found
	* @access	public
	*/
	function fetchVri ($data, $types, $limiter = TRUE, $vri = TRUE)
	{
		// set limiter strings
		if($limiter)
		{
			$lt = "<";
			$gt = ">";
		}
		else
		{
			$lt = $gt = "";
		}
		
		// set vri string
		if ($vri)
		{
			$vri = "vri[\s]*=[\s]*";
		}
		else
		{
			$vri = "";
		}
		
		// set content and end tag string
		if($limiter and $vri)
		{
			$end = "(.*?)<\/vri>";
		}
		else
		{
			$end = "";
		}
		
		// set regular expressiion for vri tag
		$vriTag = "/".$lt.$vri."!([^>]*?)!(".$types.")!([\d]+)![\s]*(type[\s]*=[\s]*(media|glossary|faq|new))?[\s]*(\/)?".$gt."(?(6)|".$end.")/is";
		
		// get all vri tags
		preg_match_all($vriTag, $data, $matches, PREG_SET_ORDER);
		
		if (is_array($matches))
		{
			// fill vri array	
			foreach ($matches as $key => $value)
			{
				$vriSet[$key] = array(	"inst" => $value[1],
										"type" => $value[2],
										"id" => $value[3],
										"target" => $value[5],
										"content" => $value[7]);
			}
			return $vriSet;	
		}
		else
		{
			return FALSE;	
		}
	}
	
	/**
	* Fetches all text parts between vri tags in a string
	*
	* Example: If the string contains "some text  some text <vri=!100!st!20!> a link </vri> some text"
	* fetchText($string) returns an array $arr with $arr[0]->"some text" , $arr[1]->"some text"
	* vri tags must contain limiter and vri string
	*
	* @param	string	string, that should be searched through
	* @return	array	array with text parts; FALSE, otherwise
	* @access	public
	*/
	function fetchText ($data)
	{
		// set types
		$types = "st|ab|pg|mm";
		
		// set limiter strings
		$lt = "<";
		$gt = ">";
		
		// set vri string
		$vri = "vri[\s]*=[\s]*";
		
		// set content and end tag string
		$end = "(.*?)<\/vri>";
		
		// set regular expressiion for vri tag
		$vriTag = "/".$lt.$vri."!([^>]*?)!(".$types.")!([\d]+)![\s]*(type[\s]*=[\s]*(media|glossary|faq|new))?[\s]*(\/)?".$gt."(?(6)|".$end.")/is";
		
		// get all text parts splitted by vri tags
		$matches = preg_split($vriTag, $data);
		
		if (is_array($matches))
		{
			return $matches;	
		}
		else
		{
			return FALSE;	
		}
	}
	
	/**
	* Fetches parameters for multimedia objects in ILIAS 2
	* @param	string	string, that should be searched through
	* @return	array	2-dimensional array with parameters; FALSE, otherwise
	* @access	public
	*/
	function fetchParams ($data)
	{
		// set regular expressiion for parameter
		$regExp = "/[\s]*(.*?)[\s]*=[\s]*\"[\s]*(.*?)[\s]*\"(,|)[\s]*/is";
		
		// get all parameters
		preg_match_all($regExp, $data, $matches, PREG_SET_ORDER);
		
		if (is_array($matches))
		{
			// fill paramters array	
			foreach ($matches as $key => $value)
			{
				$params[$key] = array(	"Name" => $value[1],
										"Value" => $value[2]);
			}
			return $params;	
		}
		else
		{
			return FALSE;	
		}
	}
	
	/**
	* Gets size of a (local) file
	* @param	string	full path to a local file
	* @return	integer	size in byte
	* @access	public
	*/
	function getFileSize ($file)
	{
		// get mimetype
		$size = @filesize($file);
		
		// set default if size detection failed (e.g. remote file)
		if (empty($size))
		{
			$size = 0;
		}
		return $size;
	}
	
	/**
	* Gets mimetype of a (local) file
	* 
	* ! needs special entry in php.ini !
	* 
	* @param	string	full path to a local file
	* @return	string	mimetype (formatted to fit in the DTD)
	* @access	public
	*/
	function getMimeType ($file)
	{
		// get mimetype
		$mime = str_replace("/", "-", @mime_content_type($file));
		
		// set default if mimetype detection failed (e.g. remote file)
		if (empty($mime))
		{
			$mime = "application-octet-stream";
		}
		return $mime;
	}
	
	/**
	* Sets an array with the minimum data
	* (mimetype, size and location of a file)
	* needed for element Technical in ILIAS 3
	*
	* @param	string	full path to target directory
	* @param	string	target file name
	* @return	array	Technical data
	* @access	public
	*/
	function getTechInfo ($tDir, $tFile = "")
	{
		// set absolute and relative path to the file
		$path = $tDir.$tFile;
		$relPath = "./".$tFile;
		
		// *** proceed only if a file was found
		// if (file_exists($path))
		// {
				// set technical information
				$arr["Format"]		= $this->getMimeType($path);
				$arr["Size"]		= $this->getFileSize($path);
				$arr["Location"]	= $relPath;
		// }
		return $arr;
	}
	
	/**
	* Creates a directory, if it doesn't exist
	* @param	string	directory name and path
	* @access	public
	*/
	function makeDir ($dir)
	{
		if (!@is_dir($dir))
		{
			mkdir($dir, 0770);
			chmod($dir, 0770);
		}
	}
	
	/**
	* Copies content of a directory $sDir recursively to a directory $tDir
	* @param	string	source directory
	* @param	string	target directory
	* @return	boolean	TRUE for sucess, FALSE otherwise
	* @access	public
	*/
	function rCopy ($sDir, $tDir)
	{
		// check if arguments are directories
		if (!@is_dir($sDir) or 
			!@is_dir($tDir))
		{
			return FALSE;
		}
		
		// read sdir, copy files and copy directories recursively
		$dir = opendir($sDir);
	
		while($file = readdir($dir))
		{
	    	if ($file != "." and
				$file != "..")
			{
				// directories
	         	if (@is_dir($sDir."/".$file))
				{
					if (!@is_dir($tDir."/".$file))
					{
						if (!mkdir($tDir."/".$file, 0770))
							return FALSE;
	
						chmod($tDir."/".$file, 0770);
					}
	
					if (!$this->rCopy($sDir."/".$file,$tDir."/".$file))
					{
						return FALSE;
					}
				}
				
				// files
				if (@is_file($sDir."/".$file))
				{
	            	if (!copy($sDir."/".$file,$tDir."/".$file))
					{
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}
	
	/**
	* Gets names of all image files corresponding to a image element in ILIAS 2
	* @param	string	full path to source directory
	* @param	integer	image id
	* @param	string	image name
	* @return	array	names
	* @access	public
	*/
	function getImageNames ($sDir, $imageId, $imageName)
	{
		// initialize array
		$types = array("", ".gif", ".jpg", "-s.gif", "-s.jpg");
		
		foreach ($types as $type) 
		{
			if (file_exists($sDir.$imageId.$type))
			{
				if ($type <> "")
				{
					$names[$imageId.$type] = $imageId.$type;
				}
				else
				{
					$names[$imageId.$type] = $imageName;
				}
			}
		}
		return $names;
	}
	
	/**
	* Copies object files from a source to a target directory
	* @param	string	full path to source directory
	* @param	string	full path to target directory
	* @param	integer	object id
	* @param	strning	object type [img|imap|mm|file]
	* @param	strning	target file name (only for [img|imap])
	* @access	public
	*/
	function copyObjectFiles ($sDir, $tDir, $id, $type, $tName = Null)
	{
		switch ($type) 
		{
			// image files
			case "img":
				// build target directories
				$this->makeDir($tDir);
				// set and build target subdirectory
				$tDir = $tDir."image".$id."/";
				$this->makeDir($tDir);
				
				// get filenames
				$names = $this->getImageNames ($sDir, $id, $tName);
				
				// copy files
				if (is_array($names))
				{
					foreach ($names as $key => $value) 
					{
						copy($sDir.$key, $tDir.$value);
					}
				}
				break;
			
			// imagemap files
			case "imap":
				// build target directories
				$this->makeDir($tDir);
				// set and build target subdirectory
				$tDir = $tDir."imagemap".$id."/";
				$this->makeDir($tDir);
				
				// copy files
				copy($sDir.$tName, $tDir.$tName);
				break;
			
			// files of multimedia objects
			case "mm":
			// files
			case "file":
				// build target directory
				$this->makeDir($tDir);
											
				// copy files				
				if (@is_dir($sDir.$type.$id))
				{
					// build file directory
					$this->makeDir($tDir.$type.$id);
					// copy recursively
					$this->rCopy($sDir.$type.$id, $tDir.$type.$id);
				}
				break;
		}
	}
}

?>