<?php

/**
* Content-converting from ILIAS2 to ILIAS3 using DOMXML
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

// include other files
require_once "class.ILIAS2To3Utils.php";

class ILIAS2To3Converter
{
	//-----------
	// properties
	//-----------
	
	/**
	* database handle from pear database class
	* 
	* @var object $db
	* @access private
	*/
	var $db;
	
	/**
	* object handle from utility class
	* 
	* @var object $util
	* @access private
	*/
	var $utils;
	
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
	function ILIAS2To3Converter ($user , $pass, $host, $dbname)
	{
		// build dsn of database connection and connect
		$dsn = "mysql://$user:$pass@$host/$dbname";
		$this->db = DB::connect($dsn, TRUE);
		
		// test for valid connection
		if (DB::isError($this->db))
		{
			die ($this->db->getMessage());
		}
		
		// create utility object ***
		$this->utils = new ILIAS2To3Utils;
	}
	
	/**
	* destructor
	* 
	* @access	private
	* @return	boolean
	*/
	function _ILIAS2To3Converter ()
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
	
	// ILIAS2 Metadata --> ILIAS3 MetaData
	function exportMetadata ($id, $type, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// type-specific tables
		switch ($type) 
		{
			case "mm":
				
				// table 'multimedia'
				$sql =	"SELECT caption, startklasse, st_type, file, verweis, ". // *** some are unsed yet
								" full_view, full_type, full_file, full_ref ".
						"FROM multimedia ".
						"WHERE id = ".$id.";";
				
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
				
				// object's standard view 
				// local file (object)
				if ($mm["st_type"] == "file")
				{
					// set mimetype, size and location for the multimedia file into an array
					$tech[] = $this->utils->getTechInfo("./objects/mm".$id."/".$mm["file"], 
														$this->iliasDir."objects/mm".$id."/".$mm["file"]);
				}
				else // referenced file (object)
				{
					// set mimetype, size and location for the multimedia file into an array
					$tech[] = $this->utils->getTechInfo($mm["verweis"]);
				}
				
				// object's full view 
				if ($mm["full_view"] == "y")
				{
					// local file (object)
					if ($mm["full_type"] == "file")
					{
						// set mimetype, size and location for the multimedia file into an array
						$tech[] = $this->utils->getTechInfo("./objects/mm".$id."/".$mm["full_file"], 
															$this->iliasDir."objects/mm".$id."/".$mm["full_file"]);
					}
					else // referenced file (object)
					{
						// set mimetype, size and location for the multimedia file into an array
						$tech[] = $this->utils->getTechInfo($mm["full_ref"]);
					}
				}
				break;
			
			case "file":
				
				// table 'file'
				$sql =	"SELECT file, version ". // *** some are unsed yet
						"FROM file ".
						"WHERE id = ".$id.";";
				
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
				
				// set mimetype, size and location for the multimedia file into an array
				$tech[] = $this->utils->getTechInfo("./objects/file".$id."/".$file["file"], 
													$this->sourceDir."files/file".$id."/".$file["file"]);
				
				// ***
				echo "<pre>";
				print_r($tech);
				echo "</pre>";
				break;
			
			case "gd": // *** Problem: Kapitel besitzen keine expliziten Metadaten in ILIAS2
				
				// table 'gliederung'
				$sql =	"SELECT inst, titel, utime ".
						"FROM gliederung ".
						"WHERE id = ".$id.";";
				
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
				
				// *** workaraound
				$meta["inst"] = $gliederung["inst"];
				$meta["lang"] = "none";
				$meta["title"] = $gliederung["titel"];
				$meta["description"] = "Not available"; // default
				$meta["last_modified_date"] = $gliederung["utime"];
				break;
			
			/* ***
			case "le":
				$str = "3";
				break;
			
			case "pg":
				$str = "2";
				break;
			
			case "mc":
				$str = "2";
				break;
			
			case "el":
				$str = "1";
				break;
			*/
		}
		
		// ***
		if ($type <> "gd")
		{
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
								"Taxon" => $this->utils->selectMaterialLevel($meta["material_level"]));
			
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
		}
		
		//-------------------------
		// create MetaData subtree:
		// *** Reihenfolge und Validität beachten: defaultvalues, falls records leer, aber requiered
		//-------------------------
		
		// MetaData ***
		$MetaData = $this->writeNode($parent, "MetaData");
		
		// 1 MetaData..General
		$attrs = array(	"Structure" => $this->utils->selectStructure($type));
		$General = $this->writeNode($MetaData, "General", $attrs);
		
		// 1.1 ..General..Identifier
		$attrs = array(	"Catalog" => "ILIAS2 ".$meta["inst"],
						"Entry" => $type."_".$id); // *** checken
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
		$attrs = array(	"Status" => $this->utils->selectStatus($meta["status"]));
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
		if (is_array($tech))
		{
			foreach ($tech as $value) 
			{
				$attrs = array(	"Format" => $value["Format"]);
				$Technical = $this->writeNode($MetaData, "Technical", $attrs, Null, $refnode);
				
				// 4.2 ..Technical..Size
				$Size = $this->writeNode($Technical, "Size", Null, $value["Size"]);
				
				// 4.3 ..Technical..Location
				$Location = $this->writeNode($Technical, "Location", Null, $value["Location"]);
				
				// 4.4 ..Technical..(Requirement | OrComposite) ***
				
				// 4.5 ..Technical..InstallationRemarks ***
				
				// 4.6 ..Technical..OtherPlatformRequirements ***
				
				// 4.7 ..Technical..Duration ***
			}
		}
		
		// 5 MetaData..Educational
		$attrs = array(	"InteractivityType" => "Expositive", // default
						"LearningResourceType" => $this->utils->selectMaterialType($mtype["mtype"]),
						"InteractivityLevel" => "Medium", // default
						"SemanticDensity" => "Medium", // default
						"IntendedEndUserRole" => "Learner", // default
						"Context" => $this->utils->selectLevel($meta["level"]),
						"Difficulty" => $this->utils->selectDifficulty($meta["diff"]));
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
	
	// ILIAS2 Multimedia (only undeleted) --> ILIAS3 MediaObject
	function exportMultimedia ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'multimedia'
		$sql =	"SELECT st_type, full_type, width, height, defparam ".
				"FROM multimedia ".
				"WHERE id = ".$id.";";
		
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
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "mm", $MediaObject);
		
		// MediaObject..Layout ***
		$attrs = array(	"Width" => $mm["width"],
						"Height" => $mm["height"]);
		$Layout = $this->writeNode($MediaObject, "Layout", $attrs);
		
		// MediaObject..Parameter
		if ($params = $this->utils->fetchParams($mm["defparam"]))
		{
		    foreach ($params as $value)
			{
				$attrs = array(	"Name" => $value["Name"],
								"Value" => $value["Value"]);
				$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
			}
		}
		
		// *** copy file(s) if kept locally
		if ($mm["st_type"] == "file" or
			$mm["full_type"] == "file")
		{
			// *** copy file(s)
			$this->utils->copyObjectFiles ($this->iliasDir."objects/", $this->targetDir."objects/", $id, "mm");
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $mm, $attrs, $params);
		
		//-------------------------
		// return MediaObject tree:
		//-------------------------
		return $MediaObject;
	}
	
	// ILIAS2 File (only undeleted) --> ILIAS3 MediaObject
	function exportFile ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'file' not needed at all!
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "file", $MediaObject);
		
		// MediaObject..Layout --> unavailable in ILIAS2 for file
		
		// MediaObject..Parameter --> unavailable in ILIAS2 for file
		
		// *** copy file(s)
		$this->utils->copyObjectFiles ($this->sourceDir."files/", $this->targetDir."objects/", $id, "file");
		
		//-------------
		// free memory: ***
		//-------------
		
		//-------------------------
		// return MediaObject tree:
		//-------------------------
		return $MediaObject;
	}
	
	/**
	* convert text to paragraph and vri to reference ***
	*/
	function exportTextWithVri ($data, $parent)
	{
		// fetch vri (array)
		if ($vri = $this->utils->fetchVri($data,"st|ab|pg|mm"))
		{
		    // fetch text (array)
			$text = $this->utils->fetchText($data);
			
			// ***
			for ($i = 0; $i < count($text); $i++)
			{
				// *** test ob leer
				if (!empty($text[$i]))
				{
					// Paragraph ***
					$attrs = array("Language" => "de"); // *** aus ... holen
					$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text[$i]);
				}
				
				// ***
				if (isset($vri[$i]))
				{
					// Paragraph ***
					$attrs = array("Language" => "de"); // *** aus ... holen
					$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
					
					// *** auf empty target testen!!!!
					
					// Paragraph..IntLink ***
					$attrs = array(	"Target" => $vri[$i]["type"]."_".$vri[$i]["id"],
									"Type" => $this->utils->selectTargetType($vri[$i]["type"]),
									"TargetFrame" => $vri[$i]["target"]);
					$IntLink = $this->writeNode($Paragraph, "IntLink", $attrs, $vri[$i]["content"]);
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
				$this->exportTextWithVri($text["text"], $parent);
				
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
				$names = $this->utils->getImageNames ($this->iliasDir."bilder/", $id, $image["datei"]);
				
				// proceed only if at least one file was found, else no subtree will be created ***
				if (is_array($names))
				{
					// set full path of the main file ***
					$fileName = $this->iliasDir."bilder/".key($names); // *** old filename
					
					// get (image) file size and mimetype ***
					$fileSize = filesize($fileName);
					$mimetype = $this->utils->getMimeType($fileName);
					
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
					$this->utils->copyObjectFiles ($this->iliasDir."bilder/", $this->targetDir."objects/", $id, "img", $image["datei"]);
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
					$mimetype = $this->utils->getMimeType($fileName);
				
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
					$this->utils->copyObjectFiles ($this->iliasDir."imagemaps/", $this->targetDir."objects/", $id, "imap", $id.".".$map["type"]);
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
						$attrs = array(	"Solution" => $this->utils->selectAnswer($value["mright"]));
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
					$Paragraph = $this->writeNode($Answer, "Paragraph", $attrs, $this->utils->selectAnswer($mc["answer"]));
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
	
	// ILIAS2 Page (only undeleted) --> ILIAS3 PageObject or Test or Glossary ***
	function exportPage ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'page'
		$sql =	"SELECT pg_typ ".
				"FROM page ".
				"WHERE id = ".$id.";";
		
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
				
				//-------------------------
				// create PageObject tree:
				//-------------------------				
				// PageObject
				$PageObject = $this->writeNode($parent, "PageObject");
				
				// PageObject..MetaData
				$MetaData = $this->exportMetadata($id, "pg", $PageObject);
				
				// PageObject..(Paragraph | MediaObject) ***
				$sql =	"SELECT id ".
						"FROM element ".
						"WHERE page = ".$id." ".
						"AND deleted = '0000-00-00 00:00:00' ".
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
					$Element = $this->exportElement($row["id"], $PageObject);
				}
				// free result set
				$result->free();
				
				// PageObject..Layout --> unavailable in ILIAS2 for pages				
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
	
	// ILIAS2 Chapter --> ILIAS3 StructureObject
	function exportStructure ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'gliederung' not needed at all!
		
		//-----------------------------
		// create StructureObject tree:
		//-----------------------------		
		// StructureObject
		$StructureObject = $this->writeNode($parent, "StructureObject");
		
		// StructureObject..MetaData ***
		$MetaData = $this->exportMetadata($id, "gd", $StructureObject);
		
		// StructureObject..StructureObject(s) (recursion for subchapters) ***
		$sql =	"SELECT gd.id ".
				"FROM gliederung AS gd, gliederung AS mt ".
				"WHERE gd.mutter = ".$id." ".
				"AND mt.mutter <> -1 ".
				"AND gd.mutter = mt.id ".				
				"ORDER BY gd.prefix;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$SubStructureObject = $this->exportStructure($row["id"], $StructureObject); // *** Bezeichner ändern
		}
		// free result set
		$result->free();
		
		// StructureObject..PageObject(s) ***
		// only linked Pages of type 'le', using table 'struktur'
		// *** Problem: 1 Page ggf. mehrmals verlinkt -> ID doppelt -> referenzieren???
		$sql =	"SELECT st.id AS id , st.page AS page ".
				"FROM struktur AS st, page AS pg ".
				"WHERE st.page = pg.id ".
				"And st.gliederung = ".$id." ".
				"AND pg.pg_typ = 'le' ".
				"AND pg.deleted = '0000-00-00 00:00:00' ";
				"ORDER BY st.nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$PageObject = $this->exportPage($row["page"], $StructureObject);
		}
		// free result set
		$result->free();
		
		// StructureObject..Layout ***
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs);
		
		//-----------------------------
		// return StructureObject tree:
		//-----------------------------
		return $StructureObject;
	}
	
	// ILIAS2 Learningunit --> ILIAS3 LearningModule
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
		
		//----------------------------
		// create LearningModule tree:
		//----------------------------
		
		// LearningModule
		$LearningModule = $this->writeNode($this->doc, "LearningModule");
		
		// LearningModule..MetaData
		$MetaData = $this->exportMetadata($id, "le", $LearningModule);
		
		// LearningModule..StructureObject (= startpage of an ILIAS2 Learningunit)
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = -1;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$StructureObject = $this->exportStructure($row["id"], $LearningModule);
		
		// free result set
		$result->free();
		
		// LearningModule..StructureObject(s) (all chapters beeing childern of startpage above)
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = ".$row["id"]." ".
				"ORDER BY prefix;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$StructureObject = $this->exportStructure($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..PageObject(s) (= unlinked/dangling Pages)
		$sql =	"SELECT p.id AS id ".
				"FROM page AS p ".
				"LEFT JOIN struktur AS s ON p.id = s.page ".
				"WHERE p.lerneinheit = ".$id." ".
				"AND p.pg_typ = 'le' ".
				"AND s.page is NULL ".
				"AND p.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage());
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$PageObject = $this->exportPage($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) (= multimedia objects of multimedia elements)
		$sql =	"SELECT DISTINCT mm.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, el_multimedia AS el_mm, multimedia AS mm ".
				"WHERE le.id = ".$id." ".
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
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$MediaObject = $this->exportMultimedia($row["id"], $LearningModule);
			
			// fill the test array used to avoid possible double entries
			$test[] = $row["id"];
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) (= multimedia objects of vri links)
		$sql =	"SELECT DISTINCT mm.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, vri_link AS vl, multimedia AS mm ".
				"WHERE le.id = ".$id." ".
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
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// avoiding possible double entries
			if (!in_array($row["id"],$test,strict))
			{
				$MediaObject = $this->exportMultimedia($row["id"], $LearningModule);
			}
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) (= files)
		$sql =	"SELECT DISTINCT fi.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el, filelist_entry AS fl_en, file AS fi ".
				"WHERE le.id = ".$id." ".
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
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$MediaObject = $this->exportFile($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..Test ***
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = ".$id." ".
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
			$Test = $this->writeNode($LearningModule, "Test");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Test.. ***
			$TestItem = $this->exportPage($row["id"], $Test);
		}
		// free result set
		$result->free();
		
		// LearningModule..Glossary ***
		$sql =	"SELECT id ".
				"FROM glossar ".
				"WHERE lerneinheit = ".$id." ".
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
			$Glossary = $this->writeNode($LearningModule, "Glossary");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// ..Glossary.. ***
			$GlossaryItem = $this->exportGlossary($row["id"], $Glossary);
		}
		// free result set
		$result->free();
		
		// LearningModule..Bibliography --> unavailable in ILIAS2
		
		// LearningModule..Layout ***
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs, $test);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningModule;
	}
	
	// create xml output
	function dumpFile ($leId, $path)
	{
		//-------------------------
		// create new xml document:
		//-------------------------
		
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlStr =	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					"<!DOCTYPE LearningModule SYSTEM \"http://127.0.0.1/ilias3/xml/ilias_lm.dtd\">".
					"<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlStr); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		// create ILIAS3 LearningObject out of ILIAS2 Lerneinheit ***
		$LearningModule = $this->exportLearningunit($leId);
		
		// dump xml document on the screen ***
		echo "<PRE>";
		echo htmlentities($this->doc->dump_mem(TRUE));
		echo "</PRE>";
		
		// dump xml document into a file ***
		$this->doc->dump_file($path, FALSE, TRUE);
		
		// call destructor
		$this->_ILIAS2To3Converter();
	}
}

?>