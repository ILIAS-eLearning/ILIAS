<?php

/**
* Export of content from ILIAS2 to ILIAS3 using DOMXML
*
* Dependencies:
* 
* @author Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version $Id$
*/

// *** = dirty/buggy --> to be modified/extended

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";

class ILIAS2export
{
	//-----------
	// properties
	//-----------
	
	/**
	* database handle from pear database class
	* 
	* @var string $db
	* @access private
	*/
	var $db;
	
	/**
	* domxml document object ***
	* 
	* @var object $doc
	* @access private 
	*/
	var $doc;
	
	/**
	* ILIAS2 base directory ***
	* 
	* @var string $iliasDir
	* @access private 
	*/
	var $iliasDir;
	
	/**
	* source directory ***
	* 
	* @var string $sourceDir
	* @access private 
	*/
	var $sourceDir;
	
	/**
	* target directory ***
	* 
	* @var string $targetDir
	* @access private 
	*/
	var $targetDir;
	
	//-------
	//methods
	//-------
	
	/**
	* constructor
	* 
	* @param	string	xml version
	* @access	public 
	*/
	function ILIAS2export ($user , $pass, $host, $dbname)
	{
		// build dsn of database connection and connect
		$dsn = "mysql://$user:$pass@$host/$dbname";
		$this->db = DB::connect($dsn, TRUE);
		
		// test for valid connection
		if (DB::isError($this->db))
		{
			die ($this->db->getMessage());
		}
	}
	
	/**
	* destructor
	* 
	* @access	private
	* @return	boolean
	*/
	function _ILIAS2export ()
	{
		// quit connection
		$this->db->disconnect();
	}
	
	// write node using DOMXML (new node will be inserted right before the node $refnode if specified ***) 
	function writeNode ($parent, $tag, $attrs = NULL, $text = NULL, $refnode = Null)
	{
		// create new element node
		$node = $this->doc->create_element($tag);
		
		// set attributes
		if (is_array($attrs))
		{
			foreach ($attrs as $name => $value)
			{
				$node->set_attribute($name, $value);
			}
		}
		
		// create and add a text node to the new element node
		if (is_string($text) or
			is_integer($text))
		{
			$nodeText = $this->doc->create_text_node(utf8_encode($text)); // *** iconv("ISO-8859-1","UTF-8",$text)
			$nodeText = $node->append_child($nodeText);
		}
		
		// add element node at at the end of the children of the parent
		$node = $parent->insert_before($node, $refnode);
		// *** $node = $parent->append_child($node);
		
		return $node;
	}
	
	// select AggregationLevel from type according to concept paper *** verfeinern/verifizieren
	function selectStructure ($type)
	{
		switch ($type) 
		{
			case "le":
				$str = "Hierarchical";
				break;
			
			case "gd":
				$str = "Hierarchical";
				break;
			
			case "pg":
				$str = "Collection";
				break;
			
			case "mc": // *** -> test element ohne eigene Daten (*** gl auch unterbringen)
				$str = "Collection";
				break;
			
			case "el":
				$str = "Atomic"; // *** -> nicht immer Atomic
				break;
			
			case "mm":
				$str = "Atomic"; // *** mm und el metadaten für mm_el
				break;
			
			case "file":
				$str = "Atomic";
				break;
		}
		
		return $str;
	}
	
	// select AggregationLevel from type according to concept paper *** verfeinern/verifizieren
	function selectAggregationLevel ($type)
	{
		switch ($type) 
		{
			case "le":
				$str = "3";
				break;
			
			case "gd":
				$str = "3";
				break;
			
			case "pg":
				$str = "2";
				break;
			
			case "mc": // *** -> test element ohne eigene Daten?
				$str = "2";
				break;
			
			case "el":
				$str = "1"; // *** -> nicht immer 1
				break;
			
			case "mm":
				$str = "1"; // *** mm und el metadaten für mm_el
				break;
			
			case "file":
				$str = "1";
				break;
		}
		
		return $str;
	}
	
	// convert status values ***
	function convertMaterialType ($materialType)
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
	
	// convert status values ***
	function convertStatus ($status)
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
	
	// convert difficulty values ***
	function convertDifficulty ($difficulty)
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
	
	// convert level values ***
	function convertLevel ($level)
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
	
	// convert difficulty values ***
	function convertMaterialLevel ($materialLevel)
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
	
	// convert answer values ***
	function convertAnswer ($answer)
	{
		switch ($answer) 
		{
			case "y":
			case "r":
				$str = "Right";
				break;
			
			case "n":
			case "f":
				$str = "Wrong";
				break;
			
			case "j":
				$str = "Yes";
				break;
			
			case "n":
				$str = "No";
				break;
		}
		
		return $str;
	}
	
	/**
	* fetch all vri tags in a string
	*
	* *** Example: If the string contains "some text <vri=!100!st!20!> a link </vri> some text"
	* vri_fetch($string,"st") returns an array $arr with $arr["inst"]->100, $arr["type"]->"st"
	* and $arr["id"]->20.
	*
	* @param string $data string, that should be searched through
	* @param string $types vri types, that should be searched, separated by "|", e.g. "mm|st"
	* @param boolean $limiter true, if vri in input string doesn´t contain tag limiter "<" and ">" (default TRUE)
	* @param boolean $vri true, if vri in input string doesn´t contain vri string "vri=" (default TRUE)
	*
	* @return	array	array with fields "inst", "type", "id" and "target"; false, if no vri was found
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
		
		/* ***
		echo "<pre>";
		htmlentities(print_r($matches));
		echo "</pre>";
		*/
		
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
		
		/* ***
		echo "<pre>";
		htmlentities(print_r($vriSet));
		echo "</pre>";
		*/
	}
	
	/**
	* fetch all text parts between vri tags in a string
	*
	* *** Example: If the string contains "some text  some text <vri=!100!st!20!> a link </vri> some text"
	* fetchText($string) returns an array $arr with $arr[0]->"some text" , $arr[1]->"some text"
	* vri tags must contain limiter and vri string
	*
	* @param string $data string, that should be searched through
	*
	* @return	array	array with text parts; false, otherwise ***
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
		
		/* ***
		echo "<pre>";
		htmlentities(print_r($matches));
		echo "</pre>";
		*/
		
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
	* convert text to paragraph and vri to reference ***
	*/
	function convertVri ($data, $parent)
	{
		// fetch vri (array)
		if ($vri = $this->fetchVri($data,"st|ab|pg|mm"))
		{
		    // fetch text (array)
			$text = $this->fetchText($data);
			
			// ***
			for ($i = 0; $i < count($text); $i++)
			{
				// *** test ob leer
				if (!empty($text[$i]))
				{
					// Paragraph ***
					$attrs = array("Language" => "de"); // *** aus ... holen
					$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text[$i]);
					
					// *** $ret .= "<p>".$text[$i]."</p>";
				}
				
				// ***
				if (isset($vri[$i]))
				{
					// *** legacy
					if ($vri[$i]["type"] == "ab")
					{
						$vri[$i]["type"] == "pg";
					}
					
					// Paragraph..Reference *** target einbauen !!!
					$attrs = array(	"Reference_to" => $vri[$i]["type"]."_".$vri[$i]["id"],
									"Type" => "LearningObject"); // ***
					$Reference = $this->writeNode($parent, "Reference", $attrs, $vri[$i]["content"]);
					
					// *** $ret .= "vri_link".$i;
				}
			}		
		}
		else
		{
			// Paragraph ***
			$attrs = array("Language" => "de"); // *** aus ... holen
			$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $data);
		}
		// *** return $ret;
	}
	
	// get mimetype for a file *** takes full path to a lokal file
	function getMimeType ($file)
	{
		// get mimetype
		$mime = str_replace("/", "-", @mime_content_type($file));
		
		// set default if mimety setting failed
		if (empty($mime))
		{
			$mime = "application-octet-stream";
		}
		
		return $mime;
	}
	
	/**
	*  *** creates a directory, if it doesn't exist
	*
	* @param string $dir directory name and path
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
	* *** copies content of a directory $sDir recursively to a directory $tDir
	*
	* @param string $sDir source directory
	* @param string $tDir target directory
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
	
					if (!rCopy($sDir."/".$file,$tDir."/".$file))
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
	
	function getImageNames ($sDir, $imageId, $imageName = "")
	{
		// initialize arrays ***
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
			// files (*** el_filelist)
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
	
	// ILIAS2 Metadata --> ILIAS3 MetaData
	function exportMetadata ($id, $type, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'meta'
		$sql =	"SELECT inst, title, lang, description, diff, level, status, ".
				"material_level, last_modified_date, publisher, publish_date ".
				"FROM meta ".
				"WHERE id = $id ".
				"AND typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row
		$meta = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// set Contribute data in an array
		$contrib[] = array(	"Role" => "Publisher",
							"Entity" => $meta["publisher"],
							"Date" => $meta["publish_date"]);
		
		// set Classification data in an array
		$class[] = array(	"Purpose" => "EducationalLevel",
							"SourceLanguage" => $meta["lang"],
							"Source" => "ILIAS2 ".$meta["inst"],
							"TaxonLanguage" => "none", // default, due to convert function
							"Taxon" => $this->convertMaterialLevel($meta["material_level"]));
		
		// table 'meta_keyword'
		$sql =	"SELECT DISTINCT keyword ".
				"FROM meta_keyword ".
				"WHERE id = $id ".
				"AND typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$keyword[] = $row["keyword"];
		}
		// free result set
		$result->free();
		
		// table 'meta_author'
		$sql =	"SELECT DISTINCT author_firstname, author_surname ".
				"FROM meta_author ".
				"WHERE id = $id ".
				"AND typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["author_fristname"] == "" and
				$row["author_surname"] == "")
			{
				$sql2 =	"SELECT b.vorname AS author_firstname, b.nachname AS author_surname ".
						"FROM meta_author AS ma, benutzer AS b ".
						"WHERE ma.author_local_id = b.id ".
						"AND ma.id = $id ".
						"AND ma.typ = '$type';";
				
				$result2 = $this->db->query($sql2);		
				// check $result for error
				if (DB::isError($result2))
				{
					die ($result2->getMessage());
				}
				// get row
				$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result2->free();
				
				// set Contribute data in an array ***
				$contrib[] = array(	"Role" => "Author",
									"Entity" => $row2["author_firstname"]." ".$row2["author_surname"],
									"Date" => $meta["last_modified_date"]);
			}
			else
			{
				// set Contribute data in an array ***
				$contrib[] = array(	"Role" => "Author",
									"Entity" => $row["author_firstname"]." ".$row["author_surname"],
									"Date" => $meta["last_modified_date"]);
			}
		}
		// free result set
		$result->free();
		
		// table 'meta_contrib'
		$sql =	"SELECT DISTINCT contrib_firstname, contrib_surname ".
				"FROM meta_contrib ".
				"WHERE id = $id ".
				"AND typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["contrib_fristname"] == "" and
				$row["contrib_surname"] == "")
			{
				$sql2 =	"SELECT b.vorname AS contrib_firstname, b.nachname AS contrib_surname ".
						"FROM meta_contrib AS mc, benutzer AS b ".
						"WHERE mc.contrib_local_id = b.id ".
						"AND mc.typ = '$type' ".
						"AND mc.id = $id;";
				
				$result2 = $this->db->query($sql2);		
				// check $result for error
				if (DB::isError($result2))
				{
					die ($result2->getMessage());
				}
				// get row
				$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result2->free();
				
				// set Contribute data in an array ***
				$contrib[] = array(	"Role" => "TechnicalImplementer",
									"Entity" => $row2["contrib_firstname"]." ".$row2["contrib_surname"],
									"Date" => $meta["last_modified_date"]);
			}
			else
			{
				// set Contribute data in an array ***
				$contrib[] = array(	"Role" => "TechnicalImplementer",
									"Entity" => $row["contrib_firstname"]." ".$row["contrib_surname"],
									"Date" => $meta["last_modified_date"]);
			}
		}
		// free result set
		$result->free();
		
		// table 'meta_mtype'
		$sql =	"SELECT m.mtype AS mtype ".
				"FROM meta_mtype AS mt, materialtype AS m ".
				"WHERE mt.mtype = m.id ". 
				"AND mt.id = $id ".
				"AND mt.typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row
		$mtype = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// table 'meta_discipline'
		$sql =	"SELECT DISTINCT d.disc AS discipline ".
				"FROM meta_discipline AS md, discipline AS d ".
				"WHERE md.disc = d.id ".
				"AND md.id = $id ".
				"AND md.typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// set Classification data in an array ***
			$class[] = array(	"Purpose" => "Discipline",
								"SourceLanguage" => $meta["lang"],
								"Source" => "ILIAS2 ".$meta["inst"],
								"TaxonLanguage" => $meta["lang"],
								"Taxon" => $row["discipline"]);
		}
		// free result set
		$result->free();
		
		// table 'meta_subdiscipline'
		$sql =	"SELECT DISTINCT s.subdisc AS subdiscipline ".
				"FROM meta_subdiscipline AS ms, subdiscipline AS s ".
				"WHERE ms.disc = s.id ".
				"AND ms.id = $id ".
				"AND ms.typ = '$type';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// set Classification data in an array ***
			$class[] = array(	"Purpose" => "Discipline",
								"SourceLanguage" => $meta["lang"],
								"Source" => "ILIAS2 ".$meta["inst"],
								"TaxonLanguage" => $meta["lang"],
								"Taxon" => $row["subdiscipline"]);
		}
		// free result set
		$result->free();
		
		// table 'meta_ibo_kat' --> information not used in ILIAS3
				
		// table 'meta_ibo_right' --> information not used in ILIAS3
		
		//-------------------------
		// create MetaData subtree:
		// *** Reihenfolge und Validität beachten: defaultvalues, falls records leer, aber requiered
		//-------------------------
		
		// MetaData ***
		$MetaData = $this->writeNode($parent, "MetaData");
		
		// 1 MetaData..General
		$attrs = array(	"Structure" => $this->selectStructure($type),
						"AggregationLevel" => $this->selectAggregationLevel($type));
		$General = $this->writeNode($MetaData, "General", $attrs);
		
		// 1.1 ..General..Identifier
		$attrs = array(	"Catalog" => "ILIAS2 ".$meta["inst"],
						"Entry" => $type."_".$id);
		$Identifier = $this->writeNode($General, "Identifier", $attrs);
		
		// 1.2 ..General..Title
		$attrs = array(	"Language" => $meta["lang"]);
		$Title = $this->writeNode($General, "Title", $attrs, $meta["title"]);
		
		// 1.3 ..General..Language
		$Language = $this->writeNode($General, "Language", NULL, $meta["lang"]);
		
		// 1.4 ..General..Description
		$attrs = array(	"Language" => $meta["lang"]);
		$Description = $this->writeNode($General, "Description", $attrs, $meta["description"]);
		
		// 1.5 ..General..Keyword
		if (is_array($keyword))
		{
			foreach ($keyword as $value) 
			{
				$attrs = array(	"Language" => $meta["lang"]);
				$Keyword = $this->writeNode($General, "Keyword", $attrs, $value);
			}
		}
		else
		{
			$attrs = array(	"Language" => $meta["lang"]);
			$Keyword = $this->writeNode($General, "Keyword", $attrs, "Not available"); // default
		}
		
		// 1.6 ..General..Covarage --> unavailable in ILIAS2
		
		// 2 MetaData..Lifecycle
		$attrs = array(	"Status" => $this->convertStatus($meta["status"]));
		$Lifecycle = $this->writeNode($MetaData, "Lifecycle", $attrs);
		
		// 2.1 ..Lifecycle..Version
		$attrs = array(	"Language" => $meta["lang"]);
		$Version = $this->writeNode($Lifecycle, "Version", $attrs, "Not available"); // default
		
		// 2.3 ..Lifecycle..Contribute
		if (is_array($contrib))
		{
			foreach ($contrib as $value) 
			{
				$attrs = array(	"Role" => $value["Role"]);
				$Contribute = $this->writeNode($Lifecycle, "Contribute", $attrs);
				
				// 2.3.2 ..Lifecycle..Contribute..Entity
				$Entity = $this->writeNode($Contribute, "Entity", NULL, $value["Entity"]);
				
				// 2.3.3 ..Lifecycle..Contribute..Date
				$Date = $this->writeNode($Contribute, "Date", NULL, $value["Date"]);
			}
		}
		
		// 3 MetaData..Meta-Metadata  --> unavailable in ILIAS2
		
		// 4 MetaData..Technical ***
		
		// 5 MetaData..Educational
		$attrs = array(	"InteractivityType" => "Expositive", // default
						"LearningResourceType" => $this->convertMaterialType($mtype["mtype"]),
						"InteractivityLevel" => "Medium", // default
						"SemanticDensity" => "Medium", // default
						"IntendedEndUserRole" => "Learner", // default
						"Context" => $this->convertLevel($meta["level"]),
						"Difficulty" => $this->convertDifficulty($meta["diff"]));
		$Educational = $this->writeNode($MetaData, "Educational", $attrs);
		
		// 5.7 ..Educational..TypicalAgeRange
		$attrs = array(	"Language" => $meta["lang"]);
		$TypicalAgeRange = $this->writeNode($Educational, "TypicalAgeRange", $attrs, "Not available"); // default
		
		// 5.9 ..Educational..TypicalLearningTime
		$TypicalLearningTime = $this->writeNode($Educational, "TypicalLearningTime", NULL, "00:00:00"); // default
		
		// 6 MetaData..Rights ***
		
		// 7 MetaData..Relation ***
		
		// 8 MetaData..Annotation ***
		
		// 9 MetaData..Classification
		if (is_array($class))
		{
			foreach ($class as $value) 
			{
				$attrs = array(	"Purpose" => $value["Purpose"]);
				$Classification = $this->writeNode($MetaData, "Classification", $attrs);
				
				// 9.2 ..Classification..TaxonPath
				$TaxonPath = $this->writeNode($Classification, "TaxonPath");
				
				// 9.2.1 ..Classification..TaxonPath..Source
				$attrs = array(	"Language" => $value["SourceLanguage"]);
				$Source = $this->writeNode($TaxonPath, "Source", $attrs, $value["Source"]);
				
				// 9.2.2 ..Classification..TaxonPath.Taxon
				$attrs = array(	"Language" => $value["TaxonLanguage"]);
				$Taxon = $this->writeNode($TaxonPath, "Taxon", $attrs, $value["Taxon"]);
				
				// ..Classification..Description
				$attrs = array(	"Language" => $meta["lang"]);
				$Description = $this->writeNode($Classification, "Description", $attrs, "Not available"); // default
				
				// ..Classification..Keyword
				$attrs = array(	"Language" => $meta["lang"]);
				$Keyword = $this->writeNode($Classification, "Keyword", $attrs, "Not available"); // default
			}
		}
		
		//-------------
		// free memory:
		//-------------
		unset($sql, $sql2, $row, $row2, $meta, $keyword, $contrib, $mtype, $attrs);
		
		//-------------------------
		// return MetaData subtree:
		//-------------------------
		return $MetaData;
	}
	
	// ILIAS2 Multimedia (only undeleted) --> ILIAS3 LearningObject AggregationLevel 1
	function exportMultimedia ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'multimedia'
		$sql =	"SELECT st_type, file, verweis, typ, defparam, width, height ". // *** some are unsed yet
				"FROM multimedia ".
				"WHERE id = $id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// check if local object or reference
		if ($mm["st_type"] == "file")
		{
			// set full path of the main multimedia file ***
			$mmName = $this->iliasDir."objects/mm".$id."/".$mm["file"];
			
			// proceed only if at least one file was found, else no tree will be created ***
			if (file_exists($mmName))
			{
				// get multimedia file size and mimetype ***
				$size = filesize($mmName);
				$mimetype = $this->getMimeType($mmName);
				
				//-----------------------------------------------
				// create LearningObject AggregationLevel 1 tree:
				//-----------------------------------------------
				
				// LearningObject
				$LearningObject = $this->writeNode($parent, "LearningObject");
				
				// LearningObject..MetaData ***
				$MetaData = $this->exportMetadata($id, "mm", $LearningObject);
				
				// complete Metadata:
				
				// get position within the metadata tree to insert the additional information to
				$elements = $MetaData->get_elements_by_tagname("Educational");
				$refnode = $elements[0];
				
				// 4 MetaData..Technical ***
				$attrs = array(	"Format" => $mimetype);
				$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
				
				// 4.2 ..Technical..Size
				$Size = $this->writeNode($Technical, "Size", Null, $size);
				
				// 4.3 ..Technical..Location
				$Location = $this->writeNode($Technical, "Location", Null, "./objects/mm".$id."/".$mm["file"]); // ***
				
				// 4.4 ..Technical..(Requirement | OrComposite) ***
				
				// 4.5 ..Technical..InstallationRemarks ***
				
				// 4.6 ..Technical..OtherPlatformRequirements ***
				
				// 4.7 ..Technical..Duration ***
				
				// LearningObject..Layout --> unavailable for file
				
				// LearningObject..Parameter --> unavailable for file
				
				// LearningObject..Content --> unavailable for AggregationLevel 1
				
				// LearningObject..Test --> unavailable for AggregationLevel 1
				
				// LearningObject..Glossary --> unavailable for AggregationLevel 1
				
				// LearningObject..Bibliography --> unavailable for AggregationLevel 1
				
				// *** copy file(s)
				$this->copyObjectFiles ($this->iliasDir."objects/", $this->targetDir."objects/", $id, "mm");
			}
		}
		elseif ($mm["st_type"] == "reference")
		{
			// set full path of the main file ***
			$mmName = $mm["verweis"];
			
			// proceed only if reference not empty, else no tree will be created ***
			if (!empty($mmName))
			{
				// get multimedia mimetype ***
				$mimetype = $this->getMimeType($mmName);
				
				//-----------------------------------------------
				// create LearningObject AggregationLevel 1 tree:
				//-----------------------------------------------
				
				// LearningObject
				$LearningObject = $this->writeNode($parent, "LearningObject");
				
				// LearningObject..MetaData ***
				$MetaData = $this->exportMetadata($id, "mm", $LearningObject);
				
				// complete Metadata:
				
				// get position within the metadata tree to insert the additional information to
				$elements = $MetaData->get_elements_by_tagname("Educational");
				$refnode = $elements[0];
				
				// 4 MetaData..Technical ***
				$attrs = array(	"Format" => $mimetype);
				$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
				
				// 4.2 ..Technical..Size --> unavailable for remote files
				
				// 4.3 ..Technical..Location
				$Location = $this->writeNode($Technical, "Location", Null, $mmName); // ***
				
				// 4.4 ..Technical..(Requirement | OrComposite) ***
				
				// 4.5 ..Technical..InstallationRemarks ***
				
				// 4.6 ..Technical..OtherPlatformRequirements ***
				
				// 4.7 ..Technical..Duration ***
				
				// LearningObject..Layout --> unavailable for file
				
				// LearningObject..Parameter --> unavailable for file
				
				// LearningObject..Content --> unavailable for AggregationLevel 1
				
				// LearningObject..Test --> unavailable for AggregationLevel 1
				
				// LearningObject..Glossary --> unavailable for AggregationLevel 1
				
				// LearningObject..Bibliography --> unavailable for AggregationLevel 1
			}
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $mm, $mmName, $size, $mimetype, $elements, $attrs, $refnode);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningObject;
	}
	
	// ILIAS2 File (only undeleted) --> ILIAS3 LearningObject AggregationLevel 1
	function exportFile ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'file'
		$sql =	"SELECT file, version ".
				"FROM file ".
				"WHERE id = $id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		$file = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// set full path of the main file ***
		$fileName = $this->sourceDir."files/file".$id."/".$file["file"];
		
		// proceed only if at least one file was found, else no tree will be created ***
		if (file_exists($fileName))
		{
			// get (image) file size and mimetype ***
			$size = filesize($fileName);
			$mimetype = $this->getMimeType($fileName);
			
			//-----------------------------------------------
			// create LearningObject AggregationLevel 1 tree:
			//-----------------------------------------------
			
			// LearningObject
			$LearningObject = $this->writeNode($parent, "LearningObject");
			
			// LearningObject..MetaData ***
			$MetaData = $this->exportMetadata($id, "file", $LearningObject);
			
			// complete Metadata:
			
			// get position within the metadata tree to insert the additional information to
			$elements = $MetaData->get_elements_by_tagname("Educational");
			$refnode = $elements[0];
			
			// 4 MetaData..Technical ***
			$attrs = array(	"Format" => $mimetype);
			$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
			
			// 4.2 ..Technical..Size
			$Size = $this->writeNode($Technical, "Size", Null, $size);
			
			// 4.3 ..Technical..Location
			$Location = $this->writeNode($Technical, "Location", Null, "./objects/file".$id."/".$file["file"]); // ***
			
			// 4.4 ..Technical..(Requirement | OrComposite) ***
			
			// 4.5 ..Technical..InstallationRemarks ***
			
			// 4.6 ..Technical..OtherPlatformRequirements ***
			
			// 4.7 ..Technical..Duration ***
			
			// LearningObject..Layout --> unavailable for file
			
			// LearningObject..Parameter --> unavailable for file
			
			// LearningObject..Content --> unavailable for AggregationLevel 1
			
			// LearningObject..Test --> unavailable for AggregationLevel 1
			
			// LearningObject..Glossary --> unavailable for AggregationLevel 1
			
			// LearningObject..Bibliography --> unavailable for AggregationLevel 1
			
			// *** copy file(s)
			$this->copyObjectFiles ($this->sourceDir."files/", $this->targetDir."objects/", $id, "file");
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $file, $fileName, $size, $mimetype, $elements, $attrs, $refnode);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningObject;
	}
	
	// ILIAS2 Element (only undeleted) --> ILIAS3 LearningObject AggregationLevel 1 or 2 or Text (depends on type)
	function exportElement ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'element'
		$sql =	"SELECT typ, page, nr, src, bsp ".
				"FROM element ".
				"WHERE id = $id ".
				"AND deleted = '0000-00-00 00:00:00'";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row
		$element = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// select tables according to element's type
		switch($element["typ"]) 
		{
			case 1: // text
				
				// table 'el_text'
				$sql =	"SELECT text, align ".
						"FROM el_text ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//--------------------------
				// create Paragraph subtree:
				// *** (convert VRIs, HTML and Layout (alignment))
				//--------------------------
				
				// MetaData *** (Parent LearningObjet already has MetaData) Unterschlagen???
				
				/* ***
				// Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Example"); // *** aus bsp holen
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text["text"]);
				*/
				
				// ***
				$this->convertVRI($text["text"], $parent);
				
				break;
			
			// image (bild)
			case 2:
				// table 'el_bild'
				$sql =	"SELECT datei, align ".
						"FROM el_bild ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row(s)
				$image = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// check for orginal file name and set it to id if empty
				if ($image["datei"] == "")
				{
					$image["datei"] = $id;
				}
				
				// get names (of existing image files)
				$names = $this->getImageNames ($this->iliasDir."bilder/", $id, $image["datei"]);
				
				// proceed only if at least one file was found, else no subtree will be created ***
				if (is_array($names))
				{
					// set full path of the main file ***
					$fileName = $this->iliasDir."bilder/".key($names); // *** old filename
					
					// get (image) file size and mimetype ***
					$fileSize = filesize($fileName);
					$mimetype = $this->getMimeType($fileName);
					
					//-------------------------------------------------
					// create LearningObject AggregationLevel 1 subtree:
					//-------------------------------------------------
					
					// LearningObject
					$LearningObject = $this->writeNode($parent, "LearningObject");
					
					// LearningObject..MetaData ***
					$MetaData = $this->exportMetadata($id, "el", $LearningObject);
					
					// complete Metadata:
					
					// get position within the metadata tree to insert the additional information to
					$elements = $MetaData->get_elements_by_tagname("Educational");
					$refnode = $elements[0];
					
					// 4 MetaData..Technical ***
					$attrs = array(	"Format" => $mimetype);
					$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
					
					// 4.2 ..Technical..Size
					$Size = $this->writeNode($Technical, "Size", Null, $fileSize);
					
					// 4.3 ..Technical..Location
					$Location = $this->writeNode($Technical, "Location", Null, "./objects/image".$id."/".$names[key($names)]); // *** new filename
					
					// 4.4 ..Technical..(Requirement | OrComposite) ***
					
					// 4.5 ..Technical..InstallationRemarks ***
					
					// 4.6 ..Technical..OtherPlatformRequirements ***
					
					// 4.7 ..Technical..Duration ***
					
					// LearningObject..Layout --> unavailable for file
					
					// LearningObject..Parameter --> unavailable for file
					
					// LearningObject..Content --> unavailable for AggregationLevel 1
					
					// LearningObject..Test --> unavailable for AggregationLevel 1
					
					// LearningObject..Glossary --> unavailable for AggregationLevel 1
					
					// LearningObject..Bibliography --> unavailable for AggregationLevel 1
					
					// *** copy file(s)
					$this->copyObjectFiles ($this->iliasDir."bilder/", $this->targetDir."objects/", $id, "img", $image["datei"]);
				}
				
				break;
			
			// title
			case 3:
				// table 'el_title'
				$sql =	"SELECT text ".
						"FROM el_titel ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//--------------------------
				// create Paragraph subtree:
				// *** (convert VRIs, HTML and Layout (alignment))
				//--------------------------
				
				// MetaData *** (Parent LearningObject already has MetaData) Unterschlagen???
				
				// Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Headline"); // *** mit bsp vergeleichen
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text["text"]);
				
				break;
			
			// table
			case 4:
				
				// table 'el_table'
				$sql =	"SELECT rows, border, caption, capalign, width ". // *** auf weiter checken
						"FROM el_table ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$table = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// table 'table_cell' and 'table_rowcol'
				$sql =	"SELECT tc.row, tc.text, tc.textform, tr.width ". // *** textform not implemented yet
						"FROM table_cell AS tc, table_rowcol AS tr ".
						"WHERE tc.id = $id ".
						"AND tc.id = tr.id ".
						"AND tr.rowcol = 'c' ".
						"AND tc.col = tr.nr ".
						"ORDER BY row, col;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$data[] = $row;
				}
				// free result set
				$result->free();
				
				//--------------------------
				// create Paragraph subtree:
				// *** (convert VRIs, HTML and Layout (alignment))
				//--------------------------
				
				// MetaData *** (Parent LearningObjet already has MetaData) Unterschlagen???
				
				// Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Example"); // *** aus bsp holen
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
				
				// Paragraph..Table ***
				$attrs = array(	"Id" => "tb_".$id,
								"Width" => $table["width"], // auf empty prüfen, Hight not available
								"Border" => $table["border"]);
				$Table = $this->writeNode($Paragraph, "Table", $attrs);
				
				// ..Table..Title *** aus Metadata
				
				// ..Table..HeaderCaption ***
				if ($table["capalign"] == 0 and
					$table["caption"] <> "")
				{
					$HeaderCaption = $this->writeNode($Table, "HeaderCaption",Null,$table["caption"]);
				}
				
				// ..Table..FooterCaption ***
				if ($table["capalign"] == 1 and
					$table["caption"] <> "")
				{
					$FooterCaption = $this->writeNode($Table, "FooterCaption",Null,$table["caption"]);
				}
				
				// ..Table..Summary  --> unavailable in ILIAS2
				
				// ..Table..TableRow ***
				if (is_array($data))
				{
					for ($i = 1; $i <= $table["rows"]; $i++)
					{
						$TableRow = $this->writeNode($Table, "TableRow");
						
						foreach ($data as $value) 
						{
							if ($value["row"] == $i)
							{
								// ..TableRow..TableData ***
								if (!empty($value["width"]))
								{
									$attrs = array(	"Width" => $value["width"]);
								}
								else
								{
									$attrs = Null;
								}
								$TableData = $this->writeNode($TableRow, "TableData", $attrs, $value["text"]);
								break;
							}
						}
					}
				}
				
				break;
			
			// imagemap
			case 5:
				// table 'el_map'
				$sql =	"SELECT align, borderspace, type ".
						"FROM el_map ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$map = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// table 'maparea'
				$sql =	"SELECT shape, coords, href, alt ".
						"FROM maparea ".
						"WHERE id = $id ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$maparea[] = $row;
				}
				// free result set
				$result->free();
				
				// set full path of the main file ***
				$fileName = $this->iliasDir."imagemaps/".$id.".".$map["type"];
				
				// proceed only if at least one file was found, else no subtrewill be created ***
				if (file_exists($fileName))
				{
					// get (image) file size and mimetype ***
					$fileSize = filesize($fileName);
					$mimetype = $this->getMimeType($fileName);
				
					//--------------------------------------------------
					// create LearningObject AggregationLevel 1 subtree:
					//--------------------------------------------------
					
					// LearningObject
					$LearningObject = $this->writeNode($parent, "LearningObject");
					
					// LearningObject..MetaData ***
					$MetaData = $this->exportMetadata($id, "el", $LearningObject);
					
					// complete Metadata:
					
					// get position within the metadata tree to insert the additional information to
					$elements = $MetaData->get_elements_by_tagname("Educational");
					$refnode = $elements[0];
					
					// 4 MetaData..Technical ***
					$attrs = array(	"Format" => $mimetype);
					$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
					
					// 4.2 ..Technical..Size
					$Size = $this->writeNode($Technical, "Size", Null, $fileSize);
					
					// 4.3 ..Technical..Location
					$Location = $this->writeNode($Technical, "Location", Null, "./objects/imagemap".$id."/".$id.".".$map["type"]);
					
					// 4.4 ..Technical..(Requirement | OrComposite) ***
					
					// 4.5 ..Technical..InstallationRemarks ***
					
					// 4.6 ..Technical..OtherPlatformRequirements ***
					
					// 4.7 ..Technical..Duration ***
					
					// LearningObject..Layout --> unavailable for file
					
					// LearningObject..Parameter VRI-Links
					if (is_array($maparea))
					{
						$Parameter = $this->writeNode($LearningObject, "Parameter");
						
						foreach ($maparea as $value)
						{
							// ..ParameterName
							$ParameterName = $this->writeNode($Parameter, "ParameterName", Null, "Maparea");
							
							// ..ParameterValue
							$ParameterValue = $this->writeNode($Parameter, "ParameterValue", Null, "<area shape=\"".$value["shape"]."\" coords=\"".$value["coords"]."\" href=\"".$value["href"]."\" alt=\"".$value["alt"]."\"");
						}
					}
					
					// LearningObject..Content --> unavailable for AggregationLevel 1
					
					// LearningObject..Test --> unavailable for AggregationLevel 1
					
					// LearningObject..Glossary --> unavailable for AggregationLevel 1
					
					// LearningObject..Bibliography --> unavailable for AggregationLevel 1
					
					// *** copy file(s)
					$this->copyObjectFiles ($this->iliasDir."imagemaps/", $this->targetDir."objects/", $id, "imap", $id.".".$map["type"]);
				}
				
				break;
			
			// multiple choice
			case 6:
				
				// table 'el_mc'
				$sql =	"SELECT type, text, answer, vristr ".
						"FROM el_mc ".
						"WHERE id = $id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$mc = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// answer possibilities for flexible questiontype
				if ($mc["type"] == "mul")
				{
					// table 'mc_answer'
					$sql =	"SELECT text, mright ".
							"FROM mc_answer ".
							"WHERE id = $id ".
							"ORDER BY nr;";
					
					$result = $this->db->query($sql);		
					// check $result for error
					if (DB::isError($result))
					{
						die ($result->getMessage());
					}
					// get row(s)
					while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
					{
						$answer[] = $row;
					}
					// free result set
					$result->free();
				}
				
				//---------------------
				// create Test subtree:
				//---------------------
				
				// MetaData *** checken
				
				// TestItem..Question ***
				$Question = $this->writeNode($parent, "Question");
				
				// ..Question..Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Example"); // *** aus bsp holen
				$Paragraph = $this->writeNode($Question, "Paragraph", $attrs, $mc["text"]);
				
				// TestItem..Answer ***
				if ($mc["type"] == "mul" and
					is_array($answer))
				{
					foreach ($answer as $value) 
					{
						$attrs = array(	"Solution" => $this->convertAnswer($value["mright"]));
						$Answer = $this->writeNode($parent, "Answer", $attrs);
						
						// ..Answer..Paragraph ***
						$attrs = array(	"Language" => "de", // *** aus meta holen
										"Characteristic" => "Example"); // *** aus bsp holen
						$Paragraph = $this->writeNode($Answer, "Paragraph", $attrs, $value["text"]);
					}
				}
				else
				{
					$Answer = $this->writeNode($parent, "Answer");
					
					// ..Answer..Paragraph ***
					$attrs = array(	"Language" => "de", // *** aus meta holen
									"Characteristic" => "Example"); // *** aus bsp holen
					$Paragraph = $this->writeNode($Answer, "Paragraph", $attrs, $this->convertAnswer($mc["answer"]));
				}
				
				// TestItem..Hint ***
				// *** falls vorhanden VRI auflösen, sonst nicht vorhanden
				
				// *** !!!!!!!!! return einbauen
				
				break;
			
			// multimedia
			case 7:
				
				// table 'el_multimedia' and 'multimedia'
				$sql =	"SELECT el.mm_id, el.align, mm.st_type, mm.file, mm.verweis ".
						"FROM el_multimedia AS el, multimedia AS mm ".
						"WHERE el.id = $id ".
						"AND el.mm_id = mm.id;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row
				$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// get filename or reference *** um Test ob vorhanden ergänzen
				if ($mm["st_type"] == "file")
				{
					$refText = $mm["file"];
				}
				elseif ($mm["st_type"] == "reference")
				{
					$refText = $mm["verweis"];
				}
				
				//--------------------------
				// create Paragraph subtree:
				//--------------------------
				
				// MetaData *** (Parent LearningObject already has MetaData) Unterschlagen???
				
				// Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Example"); // *** aus bsp holen
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
				
				// Paragraph..Reference ***
				$attrs = array(	"Reference_to" => "mm_".$mm["mm_id"],
								"Type" => "LearningObject");
				$Reference = $this->writeNode($Paragraph, "Reference", $attrs, $refText);
				
				break;
			
			case 8: // filelist
				
				// table 'el_filelist' 
				/* *** not needed
				$sql =	"SELECT sort ".
						"FROM el_filelist ".
						"WHERE id = $id;";
				*/
				
				// table 'filelist_entry' and 'file'
				$sql =	"SELECT fe.file_id, f.file ".
						"FROM filelist_entry AS fe, file AS f ".
						"WHERE fe.el_id = $id ".
						"AND fe.file_id = f.id ".
						"ORDER BY fe.nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$entry[] = $row;
				}
				// free result set
				$result->free();
				
				//--------------------------
				// create Paragraph subtree:
				//--------------------------
				
				// MetaData *** (Parent LearningObject already has MetaData) Unterschlagen???
				
				// Paragraph ***
				$attrs = array(	"Language" => "de", // *** aus meta holen
								"Characteristic" => "Example"); // *** aus bsp holen
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
				
				// Paragraph..Reference ***
				if (is_array($entry))
				{
					foreach ($entry as $value) 
					{
						$attrs = array(	"Reference_to" => "file_".$value["file_id"],
										"Type" => "LearningObject");
						$Reference = $this->writeNode($Paragraph, "Reference", $attrs, $value["file"]);
					}
				}
				
				break;
			
			/*
			// sourcecode ***
			case 9:
			
			// survey ***
			case 10:
			*/
			
			// temporary dummy ***
			default: 
				
				// Paragraph
				$attrs = array(	"Language" => "en");
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, "Object not supported yet.");
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $element, $text, $image, $names, $fileName, $fileSize, $mimetype, $table, $i, $data, $attrs, $map, $maparea, $mc, $answer, $mm, $refText);
		
		//---------------------------------------------
		// return (Paragraph | LearningObject) subtree: ***
		//---------------------------------------------
		if (is_null($LearningObject))
		{
			return $Paragraph;
		}
		else
		{
			return $LearningObject;
		}
	}
	
	// ILIAS2 Page (only undeleted) --> ILIAS3 LearningObject AggregationLevel 2 or 3 or Test or Glossary
	function exportPage ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'page'
		$sql =	"SELECT pg_typ, aktiv, lerneinheit ".
				"FROM page ".
				"WHERE id = $id ".
				"AND deleted = '0000-00-00 00:00:00'";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row
		$page = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// select tables according to page's type
		switch($page["pg_typ"]) 
		{
			case "le": // Lerneinheit
				
				//-------------------------------------------------------
				// create LearningObject AggregationLevel 2 or 3 subtree:
				//-------------------------------------------------------
				
				// LearningObject
				$LearningObject = $this->writeNode($parent, "LearningObject");
				
				// LearningObject..MetaData ***
				$MetaData = $this->exportMetadata($id, "pg", $LearningObject);
				
				// LearningObject..Layout ***
				
				// LearningObject..Parameter ***
				
				// LearningObject..Content ***
				$sql =	"SELECT id, typ ". // *** typ needed?
						"FROM element ".
						"WHERE page = $id ".
						"AND deleted = '0000-00-00 00:00:00' ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// check row number
				if ($result->numRows() > 0)
				{
					$Content = $this->writeNode($LearningObject, "Content");
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					// ..Content.. ***
					/* ***
					switch ($row["typ"]) 
					{
						case 1:
						// *** case 3:
						// *** case 4:
							// ..Content..Text ***
							$Text = $this->writeNode($Content, "Text");
							
							// ..Content..Text.. ***
							$Element = $this->exportElement($row["id"], $Text);
							break;
							
						default:
							$Element = $this->exportElement($row["id"], $Content);
					}
					*/
					
					$Element = $this->exportElement($row["id"], $Content);
				}
				// free result set
				$result->free();
				
				// LearningObject..Test ***
				
				// LearningObject..Glossary ***
				
				// LearningObject..Bibliography --> unavailable in ILIAS2
				
				break;
			
			// Glossary *** (Metadata for Glossary unavailable in ILIAS3)
			case "gl":
				
				//---------------------------
				// create Definition subtree:
				//---------------------------
				
				// Glossary..GlossaryItem..Definition *** nur Textelemente filtern?
				$sql =	"SELECT id ".
						"FROM element ".
						"WHERE page = $id ".
						"AND deleted = '0000-00-00 00:00:00' ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// check row number
				if ($result->numRows() > 0)
				{
					$attrs = array(	"Id" => "pg_".$id); // *** gl_page id
					$Definition = $this->writeNode($parent, "Definition", $attrs);
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					// ..Definition.. ***
					$Element = $this->exportElement($row["id"], $Definition);
				}
				// free result set
				$result->free();
				
				break;
			
			// Multiple Choice (Test) ***
			case "mc":
				
				//-------------------------
				// create TestItem subtree:
				//-------------------------
				
				// Test..TestItem
				$sql =	"SELECT id ".
						"FROM element ".
						"WHERE page = $id ".
						// "AND typ = 6 ". // *** nur mc elemente filtern? oder Item anders veranktern
						"AND deleted = '0000-00-00 00:00:00' ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage());
				}
				// check row number
				if ($result->numRows() > 0)
				{
					$attrs = array(	"Id" => "mc_".$id); // *** mc_page id Problem, wenn >=2 el_mc pro mc_page
					$TestItem = $this->writeNode($parent, "TestItem", $attrs);
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					// ..TestItem.. ***
					$Element = $this->exportElement($row["id"], $TestItem);
				}
				// free result set
				$result->free();
				
				// free result set
				$result->free();
				
				// *** --> mit Hilfe von Referenz realisieren! Wohin damit?
				// table 'page_frage'
				/*
				$sql =	"SELECT * ".
						"FROM page_frage ".
						"WHERE mc_id = $id;";
				*/
				
				break;
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $page, $attrs);
		
		//---------------------------------------------------------
		// return (LearningObject | Definition | TestItem) subtree: ***
		//---------------------------------------------------------
		if (!is_null($LearningObject))
		{
			return $LearningObject;
		}
		elseif (!is_null($Definition))
		{
			return $Definition;
		}
		elseif (!is_null($TestItem))
		{
			return $TestItem;
		}
	}
	
	// ILIAS2 Glossar --> ILIAS3 GlossaryItem
	function exportGlossary ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'glossar'
		$sql =	"SELECT page, autor, begriff ".
				"FROM glossar ".
				"WHERE id = $id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		$glossar = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		//-----------------------------
		// create GlossaryItem subtree:
		//-----------------------------
		
		// GlossaryItem
		$attrs = array(	"Id" => "gl_".$id); // *** gl id
		$GlossaryItem = $this->writeNode($parent, "GlossaryItem", $attrs);
		
		// GlossaryItem..GlossaryTerm
		$attrs = array(	"Definition" => "pg_".$glossar["page"]); // *** gl_page id
		$GlossaryTerm = $this->writeNode($GlossaryItem, "GlossaryTerm", $attrs, $glossar["begriff"]);
		
		// Glossary..Definition *** mit text..paragraph statt paragraph!
		$Definition = $this->exportPage($glossar["page"], $GlossaryItem);
		
		// free result set
		$result->free();
		
		// *** --> mit Hilfe von Referenz realisieren! Wohin damit?
		// table 'page_glossar'
		/*
		$sql =	"SELECT * ".
				"FROM page_glossar ".
				"WHERE glossar = $id;";
		*/
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs, $glossar);
		
		//-----------------------------
		// return GlossaryItem subtree:
		//-----------------------------
		return $GlossaryItem;
	}
	
	/* *** obsolete!!!
	// ILIAS2 Multiple Choice --> ILIAS3 TestItem
	function exportTest ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// not needed *** ggf. wieder bei exportLearnunit eingliedern!
		
		//-------------------------
		// create TestItem subtree:
		//-------------------------
		
		// TestItem
		$attrs = array(	"Id" => "mc_".$id);
		$TestItem = $this->writeNode($parent, "TestItem", $attrs);
		
		
		$TestItem = $this->exportPage($id, $parent);
		
		// TestItem..Hint ***
		
		// free result set
		$result->free();
		
		// *** --> mit Hilfe von Referenz realisieren! Wohin damit?
		// table 'page_frage'
		/*
		$sql =	"SELECT * ".
				"FROM page_frage ".
				"WHERE mc_id = $id;";
		*//*
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs);
		
		//-----------------------------
		// return GlossaryItem subtree:
		//-----------------------------
		return $TestItem;
	}
	*/
	
	// ILIAS2 Chapter --> ILIAS3 LearningObject AggregationLevel 3
	function exportChapter ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'gliederung'
		$sql =	"SELECT inst, titel, utime, see_me, sichtbar ". // *** obsolete weg
				"FROM gliederung ".
				"WHERE id = $id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		$gliederung = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		//-----------------------------------------------
		// create LearningObject AggregationLevel 3 tree:
		//-----------------------------------------------
		
		// LearningObject
		$LearningObject = $this->writeNode($parent, "LearningObject");
		
		// LearningObject..MetaData ***
		// *** Problem: Kapitel besitzen keine expliziten Metadaten in ILIAS2
		// MetaData --> ausgliedern in: $MetaData = $this->exportMetadata($id, "gd", $LearningObject);
		$MetaData = $this->writeNode($LearningObject, "MetaData");
		
		// 1 MetaData..General
		$attrs = array(	"Structure" => $this->selectStructure("gd"),
						"AggregationLevel" => $this->selectAggregationLevel("gd"));
		$General = $this->writeNode($MetaData, "General", $attrs);
		
		// 1.1 ..General..Identifier
		$attrs = array(	"Catalog" => "ILIAS2 ".$gliederung["inst"],
						"Entry" => "gd_".$id);
		$Identifier = $this->writeNode($General, "Identifier", $attrs);
		
		// 1.2 ..General..Title
		$attrs = array(	"Language" => "de"); // *** von le nehmen
		$Title = $this->writeNode($General, "Title", $attrs, $gliederung["titel"]);
		
		// 1.3 ..General..Language
		$Language = $this->writeNode($General, "Language", NULL, "de"); // *** von le nehmen
		
		// 1.4 ..General..Description
		$attrs = array(	"Language" => "de"); // *** von le nehmen
		$Description = $this->writeNode($General, "Description", $attrs, "Not available"); // default
		
		// 1.5 ..General..Keyword
		$attrs = array(	"Language" => "de"); // *** von le nehmen
		$Keyword = $this->writeNode($General, "Keyword", $attrs, "Not available"); // default
		
		// 1.6 ..General..Covarage --> unavailable in ILIAS2
		
		// LearningObject..Layout --> unavailabel for ILIAS2 chapter
		
		// LearningObject..Parameter ***
		
		// LearningObject..Content ***
		// *** nur verlinkte Seiten (vom Typ 'le'), nutzt tabele 'struktur'
		// *** Problem: 1 Page ggf. mehrmals verlinkt -> muss referenziert werden --> anpassen!!!
		$sql =	"SELECT st.id AS id , st.page AS page ".
				"FROM struktur AS st, page AS pg ".
				"WHERE st.page = pg.id ".
				"And st.gliederung = $id ".
				// *** obsolete "AND pg.pg_typ = 'le' ".
				"AND pg.deleted = '0000-00-00 00:00:00' ";
				"ORDER BY st.nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0)
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Content.. ***
			$Page = $this->exportPage($row["page"], $Content);
		}
		// free result set
		$result->free();
		
		// LearningObject..Test --> unavailabel for ILIAS2 chapter
		
		// LearningObject..Glossary --> unavailabel for ILIAS2 chapter
		
		// LearningObject..Bibliography --> unavailable in ILIAS2
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs, $gliederung);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningObject;
	}
	
	// ILIAS2 Lerneinheit --> ILIAS3 LearningObject AggregationLevel 4
	function exportLearningunit ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'lerneinheit'
		/*
		$sql =	"SELECT * ".
				"FROM lerneinheit ".
				"WHERE id = $id ".
				"AND deleted = '0000-00-00 00:00:00'";
		*/
		
		//-----------------------------------------------
		// create LearningObject AggregationLevel 4 tree:
		//-----------------------------------------------
		
		// LearningObject
		$LearningObject = $this->writeNode($this->doc, "LearningObject");
		
		// LearningObject..MetaData ***
		$MetaData = $this->exportMetadata($id, "le", $LearningObject);
		
		// LearningObject..Layout ***
		
		// LearningObject..Content ***
		
		// (Chapters)
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = $id ".
				"ORDER BY prefix;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0)
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Content.. ***
			$Chapter = $this->exportChapter($row["id"], $Content);
		}
		// free result set
		$result->free();
		
		// (unlinked Pages)
		$sql =	"SELECT p.id AS id ".
				"FROM page AS p ".
				"LEFT JOIN struktur AS s ON p.id = s.page ".
				"WHERE p.lerneinheit = $id ".
				"AND p.pg_typ = 'le' ".
				"AND s.page is NULL ".
				"AND p.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0 and
			!is_object($Content))
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Content.. ***
			$Page = $this->exportPage($row["id"], $Content);
		}
		// free result set
		$result->free();
		
		// (multimedia objects of multimedia elements)
		$sql =	"SELECT DISTINCT mm.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, el_multimedia AS el_mm, multimedia AS mm ".
				"WHERE le.id = $id ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND el_mm.id = el.id ".
				"AND mm.id = el_mm.mm_id ".
				"AND mm.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0 and
			!is_object($Content))
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Content.. ***
			$Multimedia = $this->exportMultimedia($row["id"], $Content);
			
			// fill the test array used to avoid possible double entries
			$test[] = $row["id"];
		}
		// free result set
		$result->free();
		
		// (multimedia objects of vri_links)
		$sql =	"SELECT DISTINCT mm.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, vri_link AS vl, multimedia AS mm ".
				"WHERE le.id = $id ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND vl.el_id = el.id ".
				"AND vl.vri_type = 'mm' ".
				"AND mm.id = vl.vri_id ".
				"AND mm.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0 and
			!is_object($Content))
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// avoiding possible double entries
			if (!in_array($row["id"],$test,strict))
			{
				// ..Content.. ***
				$Multimedia = $this->exportMultimedia($row["id"], $Content);
			}
		}
		// free result set
		$result->free();
		
		// (files)
		$sql =	"SELECT DISTINCT fi.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, filelist_entry AS fl_en, file AS fi ".
				"WHERE le.id = $id ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND fl_en.el_id = el.id ".
				"AND fi.id = fl_en.file_id ".				
				"AND fi.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0 and
			!is_object($Content))
		{
			$Content = $this->writeNode($LearningObject, "Content");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Content.. ***
			$File = $this->exportFile($row["id"], $Content);
		}
		// free result set
		$result->free();
		
		// LearningObject..Test ***
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = $id ".
				"AND pg_typ = 'mc' ".
				"AND deleted = '0000-00-00 00:00:00' ";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0)
		{
			$Test = $this->writeNode($LearningObject, "Test");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Test.. ***
			$TestItem = $this->exportPage($row["id"], $Test);
		}
		// free result set
		$result->free();
		
		// LearningObject..Glossary ***
		$sql =	"SELECT id ".
				"FROM glossar ".
				"WHERE lerneinheit = $id ".
				"AND deleted = '0000-00-00 00:00:00' ";
				"ORDER BY begriff;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// check row number
		if ($result->numRows() > 0)
		{
			$Glossary = $this->writeNode($LearningObject, "Glossary");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Glossary.. ***
			$GlossaryItem = $this->exportGlossary($row["id"], $Glossary);
		}
		// free result set
		$result->free();
		
		// LearningObject..Bibliography --> unavailable in ILIAS2
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs, $test);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningObject;
	}
	
	// create xml output
	function outputFile ($leId, $path)
	{
		//-------------------------
		// create new xml document:
		//-------------------------
		
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlStr =	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					"<!DOCTYPE LearningObject SYSTEM \"http://127.0.0.1/ilias3/xml/ilias_lo.dtd\">".
					"<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlStr); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		// create ILIAS3 LearningObject out of ILIAS2 Lerneinheit ***
		$LearningObject = $this->exportLearningunit($leId);
		
		// dump xml document on the screen ***
		echo "<PRE>";
		echo htmlentities($this->doc->dump_mem(TRUE));
		echo "</PRE>";
		
		// dump xml document into a file ***
		$this->doc->dump_file($path, FALSE, TRUE);
		
		// call destructor
		$this->_ilias2export();
	}
}

//------
// main:
//------

// Sicherheitsabfragen fehlen ***
if ($_REQUEST["ok"] == "ok")
{
	// connection data
	$user = $_REQUEST["user"];
	$pass = $_REQUEST["pass"];
	$host = $_REQUEST["host"];
	$dbname = $_REQUEST["dbname"];
	
	// Learnunit id, source directory, target directory, filename
	$leId = (integer) $_REQUEST["leId"];
	$file = $_REQUEST["file"];
	$iliasDir = $_REQUEST["iliasDir"];
	$sDir = $_REQUEST["sDir"];
	$tDir = $_REQUEST["tDir"];
	
	// test run ***
	if (is_integer($leId) and
		is_string($file) and
		is_string($iliasDir) and
		is_string($sDir) and
		is_string($tDir))
	{
		$exp = new ilias2export($user, $pass, $host, $dbname);
		$exp->iliasDir = $iliasDir;
		$exp->sourceDir = $sDir;
		$exp->targetDir = $tDir;
		$exp->outputFile($leId, $exp->targetDir.$file);
	}
	else
	{
		echo "Fill all fields, please.";
	}
}
else
{
	echo "<html>\n".
			"<head>\n".
				"<title>ILIAS2export (experimental)</title>\n".
			"</head>\n".
			"<body>\n".
				"Export of ILIAS2 'Lerneinheiten' in ILIAS3 LearningObjects (experimental)<br /><br />\n".
				"<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\" enctype=\"multipart/form-data\">\n".
					"ILIAS2 Databaseconnection:<br /><br />\n".
					"user:<br /><input type=\"text\" name=\"user\" maxlengh=\"30\" size=\"20\" value=\"mysql\"><br />\n".
					"pass:<br /><input type=\"password\" name=\"pass\" maxlengh=\"30\" size=\"20\" value=\"\"><br />\n".
					"host:<br /><input type=\"text\" name=\"host\" maxlengh=\"30\" size=\"20\" value=\"localhost\"><br />\n".
					"dbname:<br /><input type=\"text\" name=\"dbname\" maxlengh=\"30\" size=\"20\" value=\"ilias\"><br /><br />\n".
					"Id of the 'Lerneinheit' to be exported:<br /><br />\n".
					"<input type=\"text\" name=\"leId\" maxlengh=\"10\" size=\"10\" value=\"5\"><br /><br />\n".
					"Full path of the ILIAS2 base directory:<br /><br />\n".
					"<input type=\"text\" name=\"iliasDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Full path of the source directory containing the raw data files:<br /><br />\n".
					"<input type=\"text\" name=\"sDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Full path of the target directory to copy the XML file and  the raw data files to:<br /><br />\n".
					"<input type=\"text\" name=\"tDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Filename for the generated XML file:<br /><br />\n".
					"<input type=\"text\" name=\"file\" maxlengh=\"50\" size=\"40\" value=\"lo.xml\"><br /><br />\n".
					"<input type=\"submit\" name=\"ok\" value=\"ok\">\n".
				"</form>\n".
			"</body>\n".
		"</html>\n";
}

?>