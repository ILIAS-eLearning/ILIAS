<?php

/**
* Content-converting from ILIAS2 to ILIAS3 using DOMXML
*
* Dependencies:
* 
* @author Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version $Id$
*/

// *** = dirty/buggy --> to be modified/extended + Kommentare anpassen

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
	* learninigunit id ***
	* 
	* @var integer $luId
	* @access private 
	*/
	var $luId;
	
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
	
	/**
	* file ***
	* 
	* @var string $file
	* @access private 
	*/
	var $file;
	
	/**
	* current language ***
	* 
	* @var string $curLang
	* @access private 
	*/
	var $curLang;
	
	/**
	* metadata set ***
	* 
	* @var array $metaData
	* @access private 
	*/
	var $metaData;
	
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
			case "le":
				// no additional data available
				break;
			
			case "st":
				// table 'gliederung'
				$sql =	"SELECT inst, titel ".
						"FROM gliederung ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				$glied = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// there is not dedicated metadata for chapters in ILIAS 2
				// set the minimum data needed in ILIAS 3´in an array
				$gen = $this->metaData;
				// reset catalog, identifier entry and title
				$gen["Catalog"]		= "ILIAS2 ".$glied["inst"];
				$gen["Entry"]		= $type."_".$id;
				$gen["Title"]		= $glied["titel"];
				break;
			
			case "pg":
				// no additional data available
				break;
			
			case "img":
				// reset type (img -> el)
				// images are treated as a particular element type 
				$type = "el";
				
				// table 'el_bild'
				$sql =	"SELECT datei ".
						"FROM el_bild ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
				
				// set mimetype, size and location for the image file into an array
				$tech[] = $this->utils->getTechInfo($this->targetDir, "objects/image".$id."/".$image["datei"]);
				break;
			
			case "imap":
				// reset type (imap -> el)
				// imagemaps are treated as a particular element type 
				$type = "el";
				
				// table 'el_map'
				$sql =	"SELECT type ".
						"FROM el_map ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				$map = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set mimetype, size and location for the imagemap file into an array
				$tech[] = $this->utils->getTechInfo($this->targetDir, "objects/imagemap".$id."/".$id.".".$map["type"]);
				break;
			
			case "mm":
				// table 'multimedia'
				$sql =	"SELECT st_type, file, verweis, startklasse ". // *** startklasse unsed yet
								" full_view, full_type, full_file, full_ref ".
						"FROM multimedia ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// object's standard view, local file (object)
				if ($mm["st_type"] == "file")
				{
					// set mimetype, size and location for the multimedia file into an array
					$tech[] = $this->utils->getTechInfo($this->targetDir, "objects/mm".$id."/".$mm["file"]);
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
						$tech[] = $this->utils->getTechInfo($this->targetDir, "objects/mm".$id."/".$mm["full_file"]);
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
				$sql =	"SELECT file, version ". // *** version unsed yet
						"FROM file ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				$file = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set mimetype, size and location for the multimedia file into an array
				$tech[] = $this->utils->getTechInfo($this->targetDir, "objects/file".$id."/".$file["file"]);
				break;
			
			case "el":
				// no additional data available ***
				break;
			
			case "test": // Test
				// there is not dedicated metadata for test (as a whole) in ILIAS 2
				// set the minimum data needed in ILIAS 3´in an array ***
				$gen = $this->metaData;
				// reset identifier entry
				$gen["Entry"]		= $type;
				break;
			
			case "mc":  // TestItem
				// no additional data available
				break;
			
			case "glos": // Glossary
				// there is not dedicated metadata for glossary (as a whole) in ILIAS 2
				// set the minimum data needed in ILIAS 3´in an array ***
				$gen = $this->metaData;
				// reset identifier entry
				$gen["Entry"]		= $type;
				break;
			
			case "gl": // GlossaryItem
				// reset type (gl -> pg)
				// glossary items are treated as a particular page type 
				$type = "pg";
				
				// table 'glossar' and 'benutzer'
				$sql =	"SELECT b.vorname AS firstname, b.nachname AS surname, g.utime ".
						"FROM glossar AS g, benutzer AS b ".
						"WHERE g.autor = b.id ".
						"AND g.id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set Lifecycle (Contribute) data in an array ***
				$cont["Role"]			= "Author";
				$cont["Entity"]			= $row["firstname"]." ".$row["surname"];
				$cont["Date"]			= $row["utime"];
				$life["Contribute"][]	= $cont;
				break;
		}
		
		// ***
		if ($type <> "st" and
			$type <> "test" and
			$type <> "glos")
		{
			// table 'meta'
			$sql =	"SELECT inst, title, lang, description, diff, level, status, ".
						"material_level, last_modified_date, publisher, publish_date ".
					"FROM meta ".
					"WHERE id = ".$id." ".
					"AND typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
			}
			// get row
			$meta = $result->fetchRow(DB_FETCHMODE_ASSOC);
			// free result set
			$result->free();
			
			// save current language in the dedicated object var
			// (used in objects) ***
			if ($type == "le")
			{
			    if ($meta["lang"] <> "")
				{
			    	$this->curLang = $meta["lang"];
			    }
				else
				{
					$this->curLang = "none"; // default
					$meta["lang"] = $this->curLang;
				}
				
			}
			else // only current language
			{
				if ($meta["lang"] <> "")
				{
			    	$this->curLang = $meta["lang"];
			    }
				else
				{
					$meta["lang"] = $this->curLang; // ***
				}
			}
			
			// set General data in an array
			$gen["Catalog"]		= "ILIAS2 ".$meta["inst"];
			$gen["Entry"]		= $type."_".$id;
			$gen["Title"]		= $meta["title"];
			$gen["Language"]	= $meta["lang"];
			$gen["Description"]	= $meta["description"];
			
			// set Lifecycle data in an array ***
			$life["Status"]			= $this->utils->selectStatus($meta["status"]);
			$life["Version"]		= "Not available"; // default
			$cont["Role"]			= "Publisher";
			$cont["Entity"]			= $meta["publisher"];
			$cont["Date"]			= $meta["publish_date"];
			$life["Contribute"][]	= $cont;
			
			// set Educational data in an array ***
			$edu["InteractivityType"]		= "Expositive"; // default
			$edu["LearningResourceType"]	= $this->utils->selectMaterialType($mtype["mtype"]);
			$edu["InteractivityLevel"]		= "Medium"; // default
			$edu["SemanticDensity"]			= "Medium"; // default
			$edu["IntendedEndUserRole"]		= "Learner"; // default
			$edu["Context"]					= $this->utils->selectLevel($meta["level"]);
			$edu["Difficulty"]				= $this->utils->selectDifficulty($meta["diff"]);
			$edu["TypicalAgeRange"]			= "Not available"; // default
			$edu["TypicalLearningTime"]		= "00:00:00"; // default
			
			// set Classification data in an array ***
			$tax["Purpose"]	= "EducationalLevel";
			$tax["Taxon"]	= $this->utils->selectMaterialLevel($meta["material_level"]);
			$class[]		= $tax;
			
			// table 'meta_keyword'
			$sql =	"SELECT DISTINCT keyword ".
					"FROM meta_keyword ".
					"WHERE id = ".$id." ".
					"AND typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
			}
			// check row number
			if ($result->numRows() > 0)
			{
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$gen["Keyword"][] = $row["keyword"];
				}
			}
			else
			{
				$gen["Keyword"][] = "No keywords"; // default
			}
			
			// free result set
			$result->free();
			
			// table 'meta_author'
			$sql =	"SELECT DISTINCT author_firstname, author_surname ".
					"FROM meta_author ".
					"WHERE id = ".$id." ".
					"AND typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
							"AND ma.id = ".$id." ".
							"AND ma.typ = '".$type."';";
					
					$result2 = $this->db->query($sql2);		
					// check $result for error
					if (DB::isError($result2))
					{
						die ($result2->getMessage()."<br><font size=-1>SQL: ".$sql2."</font>");
					}
					// get row
					$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
					// free result set
					$result2->free();
					
					// set Lifecycle (Contribute) data in an array ***
					$cont["Role"]			= "Author";
					$cont["Entity"]			= $row2["author_firstname"]." ".$row2["author_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
				else
				{
					// set Lifecycle (Contribute) data in an array ***
					$cont["Role"]			= "Author";
					$cont["Entity"]			= $row["author_firstname"]." ".$row["author_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
			}
			// free result set
			$result->free();
			
			// table 'meta_contrib'
			$sql =	"SELECT DISTINCT contrib_firstname, contrib_surname ".
					"FROM meta_contrib ".
					"WHERE id = ".$id." ".
					"AND typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
							"AND mc.typ = '".$type."' ".
							"AND mc.id = ".$id.";";
					
					$result2 = $this->db->query($sql2);		
					// check $result for error
					if (DB::isError($result2))
					{
						die ($result2->getMessage()."<br><font size=-1>SQL: ".$sql2."</font>");
					}
					// get row
					$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
					// free result set
					$result2->free();
					
					// set Lifecycle (Contribute) data in an array ***
					$cont["Role"]			= "TechnicalImplementer";
					$cont["Entity"]			= $row2["contrib_firstname"]." ".$row2["contrib_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
				else
				{
					// set Lifecycle (Contribute) data in an array ***
					$cont["Role"]			= "TechnicalImplementer";
					$cont["Entity"]			= $row["contrib_firstname"]." ".$row["contrib_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
			}
			// free result set
			$result->free();
			
			// table 'meta_mtype'
			$sql =	"SELECT m.mtype AS mtype ".
					"FROM meta_mtype AS mt, materialtype AS m ".
					"WHERE mt.mtype = m.id ". 
					"AND mt.id = ".$id." ".
					"AND mt.typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
			}
			// get row
			$mtype = $result->fetchRow(DB_FETCHMODE_ASSOC);
			// free result set
			$result->free();
			
			// table 'meta_discipline'
			$sql =	"SELECT DISTINCT d.disc AS discipline ".
					"FROM meta_discipline AS md, discipline AS d ".
					"WHERE md.disc = d.id ".
					"AND md.id = ".$id." ".
					"AND md.typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
			}
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// set Classification data in an array ***
				$tax["Purpose"]	= "Discipline";
				$tax["Taxon"]	= $row["discipline"];
				$class[]		= $tax;
			}
			// free result set
			$result->free();
			
			// table 'meta_subdiscipline'
			$sql =	"SELECT DISTINCT s.subdisc AS subdiscipline ".
					"FROM meta_subdiscipline AS ms, subdiscipline AS s ".
					"WHERE ms.disc = s.id ".
					"AND ms.id = ".$id." ".
					"AND ms.typ = '".$type."';";
			
			$result = $this->db->query($sql);		
			// check $result for error
			if (DB::isError($result))
			{
				die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
			}
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// set Classification data in an array ***
				$tax["Purpose"]	= "Discipline";
				$tax["Taxon"]	= $row["subdiscipline"];
				$class[]		= $tax;
			}
			// free result set
			$result->free();
			
			// table 'meta_ibo_kat' --> information not used in ILIAS 3
					
			// table 'meta_ibo_right' --> information not used in ILIAS 3
			
			// save minimal metadata (MetaData..General) in the dedicated object var
			// (used in object with no metadata in ILIAS 2) ***
			if ($type == "le")
			{
			    $this->metaData = $gen;
			}
		}
		
		//----------------------
		// create MetaData tree:
		//----------------------
		// MetaData
		$MetaData = $this->writeNode($parent, "MetaData");
		
		// 1 MetaData..General
		$attrs = array();
		$attrs["Structure"] = $this->utils->selectStructure($type);
		$General = $this->writeNode($MetaData, "General", $attrs);
		
		// 1.1 ..General..Identifier
		$attrs = array();
		$attrs["Catalog"]	= $gen["Catalog"];
		$attrs["Entry"]		= $gen["Entry"];
		$Identifier = $this->writeNode($General, "Identifier", $attrs);
		
		// 1.2 ..General..Title
		$attrs = array();
		$attrs["Language"]	= $gen["Language"];
		$Title = $this->writeNode($General, "Title", $attrs, $gen["title"]);
		
		// 1.3 ..General..Language
		$Language = $this->writeNode($General, "Language", NULL, $gen["Language"]);
		
		// 1.4 ..General..Description
		$attrs = array();
		$attrs["Language"]	= $gen["Language"];
		$Description = $this->writeNode($General, "Description", $attrs, $gen["Description"]);
		
		// 1.5 ..General..Keyword
		foreach ($gen["Keyword"] as $value) 
		{
			$attrs = array();
			$attrs["Language"]	= $gen["Language"];
			$Keyword = $this->writeNode($General, "Keyword", $attrs, $value);
		}
		
		// 1.6 ..General..Covarage --> unavailable in ILIAS 2
		
		// 2 MetaData..Lifecycle ***
		if (is_array($life))
		{
			$attrs = array();
			$attrs["Status"]	= $life["Status"];
			$Lifecycle = $this->writeNode($MetaData, "Lifecycle", $attrs);
			
			// 2.1 ..Lifecycle..Version
			$attrs = array();
			$attrs["Language"]	= $gen["Language"];
			$Version = $this->writeNode($Lifecycle, "Version", $attrs, $life["Version"]);
			
			// 2.3 ..Lifecycle..Contribute
			foreach ($life["Contribute"] as $value) 
			{
				$attrs = array();
				$attrs["Role"]	= $value["Role"];
				$Contribute = $this->writeNode($Lifecycle, "Contribute", $attrs);
				
				// 2.3.2 ..Lifecycle..Contribute..Entity
				$Entity = $this->writeNode($Contribute, "Entity", NULL, $value["Entity"]);
				
				// 2.3.3 ..Lifecycle..Contribute..Date
				$Date = $this->writeNode($Contribute, "Date", NULL, $value["Date"]);
			}
		}
		
		// 3 MetaData..Meta-Metadata  --> unavailable in ILIAS 2
		
		// 4 MetaData..Technical ***
		if (is_array($tech))
		{
			foreach ($tech as $value) 
			{
				$attrs = array();
				$attrs["Format"]	= $value["Format"];
				$Technical = $this->writeNode($MetaData, "Technical", $attrs, NULL, $refnode);
				
				// 4.2 ..Technical..Size
				$Size = $this->writeNode($Technical, "Size", NULL, $value["Size"]);
				
				// 4.3 ..Technical..Location
				$Location = $this->writeNode($Technical, "Location", NULL, $value["Location"]);
				
				// 4.4 ..Technical..(Requirement | OrComposite) --> unavailable in ILIAS 2
				
				// 4.5 ..Technical..InstallationRemarks --> unavailable in ILIAS 2
				
				// 4.6 ..Technical..OtherPlatformRequirements --> unavailable in ILIAS 2
				
				// 4.7 ..Technical..Duration --> unavailable in ILIAS 2
			}
		}
		
		// 5 MetaData..Educational
		if (is_array($edu))
		{
			$attrs = array();
			$attrs["InteractivityType"]		= $edu["InteractivityType"];
			$attrs["LearningResourceType"]	= $edu["LearningResourceType"];
			$attrs["InteractivityLevel"]	= $edu["InteractivityLevel"];
			$attrs["SemanticDensity"]		= $edu["SemanticDensity"];
			$attrs["IntendedEndUserRole"]	= $edu["IntendedEndUserRole"];
			$attrs["Context"]				= $edu["Context"];
			$attrs["Difficulty"]			= $edu["Difficulty"];
			$Educational = $this->writeNode($MetaData, "Educational", $attrs);
			
			// 5.7 ..Educational..TypicalAgeRange
			$attrs = array();
			$attrs["Language"]	= $gen["Language"];
			$TypicalAgeRange = $this->writeNode($Educational, "TypicalAgeRange", $attrs, $edu["TypicalAgeRange"]);
			
			// 5.9 ..Educational..TypicalLearningTime
			$TypicalLearningTime = $this->writeNode($Educational, "TypicalLearningTime", NULL, $edu["TypicalLearningTime"]);
		}
		
		// 6 MetaData..Rights --> unavailable in ILIAS 2
		
		// 7 MetaData..Relation --> unavailable in ILIAS 2
		
		// 8 MetaData..Annotation --> unavailable in ILIAS 2
		
		// 9 MetaData..Classification ***
		if (is_array($class))
		{
			foreach ($class as $value) 
			{
				$attrs = array();
				$attrs["Purpose"]	= $value["Purpose"];
				$Classification = $this->writeNode($MetaData, "Classification", $attrs);
				
				// 9.2 ..Classification..TaxonPath
				$TaxonPath = $this->writeNode($Classification, "TaxonPath");
				
				// 9.2.1 ..Classification..TaxonPath..Source
				$attrs = array();
				$attrs["Language"]	= $gen["Language"];
				$Source = $this->writeNode($TaxonPath, "Source", $attrs, $gen["Catalog"]);
				
				// 9.2.2 ..Classification..TaxonPath.Taxon
				$attrs = array();
				$attrs["Language"]	= $gen["Language"];
				$Taxon = $this->writeNode($TaxonPath, "Taxon", $attrs, $value["Taxon"]);
				
				// ..Classification..Description
				$attrs = array();
				$attrs["Language"]	= $gen["Language"];
				$Description = $this->writeNode($Classification, "Description", $attrs, "No description"); // default
				
				// ..Classification..Keyword
				$attrs = array();
				$attrs["Language"]	= $gen["Language"];
				$Keyword = $this->writeNode($Classification, "Keyword", $attrs, "No keywords"); // default
			}
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $sql2, $row, $row2, $meta, $keyword, $contrib, $mtype, $attrs);
		
		//----------------------
		// return MetaData tree:
		//----------------------
		return $MetaData;
	}
	
	// ILIAS 2 Image (element) --> ILIAS 3 MediaObject
	function exportImage ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element' not needed at all!
		
		// table 'el_bild'
		$sql =	"SELECT datei, align ".  // aling -> layout
				"FROM el_bild ".
				"WHERE id = ".$id.";";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
		
		//--------------
		// copy file(s): ***
		//--------------
		$this->utils->copyObjectFiles ($this->iliasDir."bilder/", $this->targetDir."objects/", $id, "img", $image["datei"]);
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "img", $MediaObject);
		
		// MediaObject..Layout ***
		// *** align
		
		// MediaObject..Parameter --> unavailable for images in ILIAS 2
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $image);
		
		//-------------------------
		// return MediaObject tree:
		//-------------------------
		return $MediaObject;
	}
	
	// ILIAS 2 Imagemap (element) --> ILIAS 3 MediaObject
	function exportImagemap ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element' not needed at all!
		
		// table 'el_map'
		$sql =	"SELECT align, borderspace, type ". // *** layout
				"FROM el_map ".
				"WHERE id = ".$id.";";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row
		$map = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		//--------------
		// copy file(s): ***
		//--------------
		$this->utils->copyObjectFiles ($this->iliasDir."imagemaps/", $this->targetDir."objects/", $id, "imap", $id.".".$map["type"]);
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "imap", $MediaObject);
		
		// MediaObject..Layout ***
		// *** align, ...
		
		// MediaObject..Parameter --> unavailable for imagemaps in ILIAS 2
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $map);
		
		//-------------------------
		// return MediaObject tree:
		//-------------------------
		return $MediaObject;
	}
	
	// ILIAS 2 Multimedia --> ILIAS 3 MediaObject
	function exportMultimedia ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'multimedia'
		$sql =	"SELECT st_type, orig_size, width, height, ".
						"full_type, full_orig_size, full_width, full_height, ".
						"defparam, caption ".
				"FROM multimedia ".
				"WHERE id = ".$id.";";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		//--------------
		// copy file(s): ***
		//--------------
		// if kept locally
		if ($mm["st_type"] == "file" or
			$mm["full_type"] == "file")
		{
			// *** copy file(s)
			$this->utils->copyObjectFiles ($this->iliasDir."objects/", $this->targetDir."objects/", $id, "mm");
		}
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "mm", $MediaObject);
		
		// MediaObject..Layout (special size)
		if (!$this->utils->selectBool($mm["org_size"]))
		{
			$attrs = array();
			$attrs["Width"]		= $mm["width"];
			$attrs["Height"]	= $mm["height"];
			$Layout = $this->writeNode($MediaObject, "Layout", $attrs);
		}
		
		// MediaObject..Parameter (= full special size)
		if (!$this->utils->selectBool($mm["full_org_size"]))
		{
			$attrs = array();
			$attrs["Name"]	= "full_width";
			$attrs["Value"]	= $mm["full_width"];
			$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
			
			$attrs = array();
			$attrs["Name"]	= "full_height";
			$attrs["Value"]	= $mm["full_height"];
			$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
		}
		
		// MediaObject..Parameter (= caption)
		if (!empty($mm["caption"]))
		{
			$attrs = array();
			$attrs["Name"]	= "caption";
			$attrs["Value"]	= $mm["caption"];
			$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
		}
		
		// MediaObject..Parameter (= parameters)
		if ($params = $this->utils->fetchParams($mm["defparam"]))
		{
		    foreach ($params as $value)
			{
				$attrs = array();
				$attrs["Name"]	= $value["Name"];
				$attrs["Value"]	= $value["Value"];
				$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
			}
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
	
	// ILIAS 2 File --> ILIAS 3 MediaObject
	function exportFile ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'file' not needed at all!
		
		//--------------
		// copy file(s): ***
		//--------------
		$this->utils->copyObjectFiles ($this->sourceDir."files/", $this->targetDir."objects/", $id, "file");
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject
		$MediaObject = $this->writeNode($parent, "MediaObject");
		
		// MediaObject..MetaData
		$MetaData = $this->exportMetadata($id, "file", $MediaObject);
		
		// MediaObject..Layout --> unavailable for files in ILIAS 2
		
		// MediaObject..Parameter --> unavailable for files in ILIAS 2
		
		//-------------
		// free memory: ***
		//-------------
		
		//-------------------------
		// return MediaObject tree:
		//-------------------------
		return $MediaObject;
	}
	
	/**
	* convert text to paragraph and vri to IntLink ***
	*/
	function exportText ($data, $parent, $example = FALSE, $code = FALSE)
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
					// ***
					if ($code)
					{
						// Paragraph
						$attrs = array();
						$attrs["Language"] = $this->curLang;
						if ($example)
						{
							$attrs["Characteristic"] = "Example";
						}
						$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
						
						// Paragraph..Code
						$attrs = array();
						$attrs["Id"] = ""; // *** id generieren
						$Code = $this->writeNode($Paragraph, "Code", $attrs, $text[$i]);
					}
					else
					{
						// Paragraph
						$attrs = array();
						$attrs["Language"] = $this->curLang;
						if ($example)
						{
							$attrs["Characteristic"] = "Example";
						}
						$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text[$i]);
					}
				}
				
				// ***
				if (isset($vri[$i]))
				{
					// ***
					$this->exportVri($vri[$i], $parent);
				}
			}		
		}
		else
		{
			// ***
			if ($code)
			{
				// Paragraph
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($example)
				{
					$attrs["Characteristic"] = "Example";
				}
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
				
				// Paragraph..Code
				$attrs = array();
				$attrs["Id"] = ""; // *** id generieren
				$Code = $this->writeNode($Paragraph, "Code", $attrs, $data);
			}
			else
			{
				// Paragraph
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($example)
				{
					$attrs["Characteristic"] = "Example";
				}
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $data);
			}
		}
		// *** return $ret;
	}
	
	/**
	* convert vri to IntLink ***
	* $vri array
	*/
	function exportVri ($vri, $parent)
	{
		// initialize switch
		$resolve = TRUE;
		
		// resolve VRI-Link to IntLink
		switch ($vri["type"]) 
		{
			case "st":
				// *** get page corresponding to structure 
				// and check if it is a part of the learnining unit 
				$sql =	"SELECT st.page AS page ".
						"FROM struktur AS st, page AS pg ".
						"WHERE st.page = pg.id ".
						"AND st.id = ".$vri["id"]." ".
						"AND pg.lerneinheit = ".$this->luId." ".
						"AND pg.pg_typ = 'le' ".
						"AND pg.deleted = '0000-00-00 00:00:00';";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// check row number
				if ($result->numRows() > 0)
				{
					// get row
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					
					// reset link data
					$vri["id"] = $row["page"];
					$vri["type"] = "pg";
					$type = "pg"; // ***
				}
				else
				{
					$resolve = FALSE;
				}
				// free result set
				$result->free();
				break;
			
			case "pg": // *** le, gl, mc???
			case "ab":
				// *** check if page (type = 'le|gl|mc' is a part of the learnining unit 
				$sql =	"SELECT id, pg_typ ".
						"FROM page ".
						"WHERE id = ".$vri["id"]." ".
						"AND lerneinheit = ".$this->luId." ".
						"AND deleted = '0000-00-00 00:00:00';";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// check row number
				if ($result->numRows() > 0)
				{
					// get row
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					
					// reset link data
					if ($row["pg_typ"] == "le")
					{
						$vri["type"] = "pg";
						$type = "pg"; // ***
					}
					if ($row["pg_typ"] == "gl")
					{
						$vri["type"] = "gl";
						$type = "pg"; // ***
					}
					if ($row["pg_typ"] == "mc")
					{
						$vri["type"] = "mc";
						$type = "mc"; // ***
					}
				}
				else
				{
					$resolve = "FALSE";
				}
				// free result set
				$result->free();
				break;
			
			case "mm":
				// *** is der test hier nötig
				$type = "mm"; // ***
				break;
		}
		
		if ($resolve)
		{
			// Paragraph
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			if ($example)
			{
				$attrs["Characteristic"] = "Example";
			}
			$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
			
			// Paragraph..IntLink
			$attrs = array();
			$attrs["Target"]	= $type."_".$vri["id"];
			$attrs["Type"]		= $this->utils->selectTargetType($vri["type"]);
			if ($vri["target"] <> "")
			{
				$attrs["TargetFrame"] = $vri["target"];
			}
			$IntLink = $this->writeNode($Paragraph, "IntLink", $attrs, $vri["content"]);
		}
		else
		{
			// Paragraph
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$text = "VRI-Link could not be resolved - Target object is not a part of current learningunit. ";
			$text .= "[".$vri["content"]." ";
			$text .= "Target=".$vri["id"]." ";
			$text .= "Type=".$vri["type"];
			if ($vri["target"] <> "")
			{
				$text .= " TargetFrame=".$vri["target"]."]";
			}
			$Paragraph = $this->writeNode($parent, "Paragraph", $attrs, $text);
		}
		// *** return $ret;
	}
	
	// ILIAS 2 Element --> ILIAS 3 Paragraph or MediaObject (depends on type) ***
	// metadata 'el' wird bis auf image und imagemap unterschlagen ***
	function exportElement ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element'
		$sql =	"SELECT typ, src, bsp ".
				"FROM element ".
				"WHERE id = ".$id.";";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row
		$element = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// ***
		$code = $this->utils->selectBool($element["src"]);
		$expl = $this->utils->selectBool($element["bsp"]);
		
		// select tables according to element's type
		switch($element["typ"]) 
		{
			case 1: // text
				// table 'el_text'
				$sql =	"SELECT text, align ". // *** align auflösen!!!
						"FROM el_text ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
				// ***
				$this->exportText($text["text"], $parent, $expl, $code);
				break;
			
			// image (bild)
			case 2:
				// table 'el_bild' not needed at all!
				//-------------------------
				// create MediaObject tree:
				//-------------------------		
				// MediaObject
				$MediaObject = $this->writeNode($parent, "MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array();
				$attrs["OriginId"] = "el_".$id;
				$MediaAlias = $this->writeNode($MediaObject, "MediaAlias", $attrs);
				
				// MediaObject..Layout --> default used
				
				// MediaObject..Parameter --> default used
				break;
			
			// title
			case 3:
				// table 'el_title'
				$sql =	"SELECT text ".
						"FROM el_titel ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
				// *** "Characteristic" => "Headline" ***
				$this->exportText($text["text"], $parent, $expl, $code);
				break;
			
			// table
			case 4:
				// table 'el_table'
				$sql =	"SELECT rows, border, caption, capalign, width ".
						"FROM el_table ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row
				$table = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// table 'table_cell' and 'table_rowcol'
				$sql =	"SELECT tc.row, tc.text, tc.textform, tr.width ". // *** textform not implemented yet
						"FROM table_cell AS tc, table_rowcol AS tr ".
						"WHERE tc.id = ".$id." ".
						"AND tc.id = tr.id ".
						"AND tr.rowcol = 'c' ".
						"AND tc.col = tr.nr ".
						"ORDER BY row, col;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$data[] = $row;
				}
				// free result set
				$result->free();
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
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
					$HeaderCaption = $this->writeNode($Table, "HeaderCaption", NULL,$table["caption"]);
				}
				
				// ..Table..FooterCaption ***
				if ($table["capalign"] == 1 and
					$table["caption"] <> "")
				{
					$FooterCaption = $this->writeNode($Table, "FooterCaption", NULL,$table["caption"]);
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
				// table 'el_map' not needed at all!
				
				// table 'maparea'
				$sql =	"SELECT shape, coords, href, alt ".
						"FROM maparea ".
						"WHERE id = ".$id." ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$area[] = $row;
				}
				// free result set
				$result->free();
				
				//-------------------------
				// create Paragraph tree:
				//-------------------------		
				// Paragraph
				$attrs = array(); // ***
				$attrs["Language"] = "de"; // *** aus meta
				$Paragraph = $this->writeNode($parent, "Paragraph", $attrs);
				
				// Paragraph..ImageMap
				$attrs = array(); // ***
				$attrs["Id"]		= "map_".$id;
				$attrs["ImageId"]	= "el_".$id;  // exported earlier in the process ***
				$ImageMap = $this->writeNode($Paragraph, "ImageMap", $attrs);
				
				// Paragraph..ImageMap..MapArea *** fetch VRI for href
				if (is_array($area))
				{
					foreach ($area as $value)
					{
						$attrs = array(); // ***
						$attrs["Shape"]		= $this->utils->selectShape($value["shape"]);
						$attrs["Coords"]	= $value["coords"];
						$attrs["Href"]		= $value["href"];
						$attrs["Alt"]		= $value["alt"];
						$MapArea = $this->writeNode($ImageMap, "MapArea", $attrs);
					}
				}
				else // default ***
				{
					// to be competetd ***
				}
				break;
			
			// multiple choice
			case 6:
				// table 'el_mc'
				$sql =	"SELECT type, text, answer, vristr ".
						"FROM el_mc ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
							"WHERE id = ".$id." ".
							"ORDER BY nr;";
					
					$result = $this->db->query($sql);		
					// check $result for error
					if (DB::isError($result))
					{
						die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
					}
					// get row(s)
					while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
					{
						$answer[] = $row;
					}
					// free result set
					$result->free();
				}
				
				//--------------------------------------
				// create Question, Answer, [Hint] tree:
				//--------------------------------------
				// TestItem..Question ***
				$Question = $this->writeNode($parent, "Question");
				
				// TestItem..Question..Paragraph ***
				$attrs = array();
				$attrs["Language"]			= "de"; // *** aus meta holen
				$attrs["Characteristic"]	= "Example"; // *** aus bsp holen
				$Paragraph = $this->writeNode($Question, "Paragraph", $attrs, $mc["text"]);
				
				// TestItem..Answer ***
				if ($mc["type"] == "mul" and
					is_array($answer))
				{
					foreach ($answer as $value) 
					{
						$attrs = array();
						$attrs["Solution"] = $this->utils->selectAnswer($value["mright"]);
						$Answer = $this->writeNode($parent, "Answer", $attrs);
						
						// TestItem..Answer..Paragraph ***
						$attrs = array();
						$attrs["Language"]			= "de"; // *** aus meta holen
						$attrs["Characteristic"]	= "Example"; // *** aus bsp holen
						$Paragraph = $this->writeNode($Answer, "Paragraph", $attrs, $value["text"]);
					}
				}
				else
				{
					$Answer = $this->writeNode($parent, "Answer");
					
					// TestItem..Answer..Paragraph ***
					$attrs = array();
					$attrs["Language"]			= "de"; // *** aus meta holen
					$attrs["Characteristic"]	= "Example"; // *** aus bsp holen
					$Paragraph = $this->writeNode($Answer, "Paragraph", $attrs, $this->utils->selectAnswer($mc["answer"]));
				}
				
				// TestItem..Hint ***
				// *** falls vorhanden VRI auflösen, sonst nicht vorhanden
				if ($mc["vristr"] <> "")
				{
					
				}
				
				// *** !!!!!!!!! return einbauen
				
				break;
			
			// multimedia
			case 7:
				// table 'el_multimedia'
				$sql =	"SELECT mm_id, align, ". // *** align
								"derive_size, width, height, ".
								"derive_full_size, full_width, full_height, ".
								"derive_defparam, paras, ".
								"derive_caption, caption ".
						"FROM el_multimedia ".
						"WHERE id = ".$id.";";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row
				$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//-------------------------
				// create MediaObject tree:
				//-------------------------		
				// MediaObject (ILIAS 2 MetaData is not used here!)
				$MediaObject = $this->writeNode($parent, "MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array();
				$attrs["OriginId"] = "mm_".$mm["mm_id"];
				$MediaAlias = $this->writeNode($MediaObject, "MediaAlias", $attrs);
				
				// MediaObject..Layout (special size)
				if (!$this->utils->selectBool($mm["derive_size"]))
				{
					$attrs = array();
					$attrs["Width"]		= $mm["width"];
					$attrs["Height"]	= $mm["height"];
					$Layout = $this->writeNode($MediaObject, "Layout", $attrs);
				}
				
				// MediaObject..Parameter (= full special size)
				if (!$this->utils->selectBool($mm["derive_full_size"]))
				{
					$attrs = array();
					$attrs["Name"]	= "full_width";
					$attrs["Value"]	= $mm["full_width"];
					$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
					
					$attrs = array();
					$attrs["Name"]	= "full_height";
					$attrs["Value"]	= $mm["full_height"];
					$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
				}
				
				// MediaObject..Parameter (= caption)
				if (!$this->utils->selectBool($mm["derive_caption"]))
				{
					$attrs = array();
					$attrs["Name"]	= "caption";
					$attrs["Value"]	= $mm["caption"];
					$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
				}
				
				// MediaObject..Parameter (= parameters)
				if (!$this->utils->selectBool($mm["derive_defparam"]))
				{
					if ($params = $this->utils->fetchParams($mm["paras"]))
					{
					    foreach ($params as $value)
						{
							$attrs = array();
							$attrs["Name"]	= $value["Name"];
							$attrs["Value"]	= $value["Value"];
							$Parameter = $this->writeNode($MediaObject, "Parameter", $attrs);
						}
					}
				}
				break;
			
			case 8: // filelist
				// table 'el_filelist' not needed at all!
				
				// table 'filelist_entry'
				$sql =	"SELECT file_id ".
						"FROM filelist_entry ".
						"WHERE el_id = ".$id." ".
						"ORDER BY nr;";
				
				$result = $this->db->query($sql);		
				// check $result for error
				if (DB::isError($result))
				{
					die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$entry[] = $row;
				}
				// free result set
				$result->free();
				
				//-------------------------
				// create MediaObject tree:
				//-------------------------		
				// MediaObject (ILIAS 2 MetaData is not used here!)
				$MediaObject = $this->writeNode($parent, "MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array(); // ***
				$attrs["OriginId"] = "file_".$value["file_id"];
				$MediaAlias = $this->writeNode($MediaObject, "MediaAlias", $attrs);
				
				// MediaObject..Layout --> default used
				
				// MediaObject..Parameter --> default used
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
				$Paragraph = $this->writeNode($parent, "Paragraph", NULL, "Object not supported yet.");
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $element, $text, $image, $names, $fileName, $fileSize, $mimetype, $table, $i, $data, $attrs, $area, $mc, $answer, $mm, $refText);
		
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
	
	// ILIAS 2 Glossar --> ILIAS 3 GlossaryItem
	function exportGlossary ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'glossar'
		$sql =	"SELECT page, begriff ".
				"FROM glossar ".
				"WHERE id = ".$id.";";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		$gloss = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// table 'page' (type = 'gl') not needed at all!
		
		//--------------------------
		// create GlossaryItem tree:
		//--------------------------		
		// GlossaryItem
		$GlossaryItem = $this->writeNode($parent, "GlossaryItem");
		
		// GlossaryItem..MetaData
		$MetaData = $this->exportMetadata($gloss["page"], "gl", $GlossaryItem);
		
		// GlossaryItem..GlossaryTerm
		$GlossaryTerm = $this->writeNode($GlossaryItem, "GlossaryTerm", NULL, $gloss["begriff"]);
		
		// GlossaryItem..Definition
		$Definition = $this->writeNode($GlossaryItem, "Definition");
		
		// GlossaryItem..Definition..(Paragraph | MediaObject)
		// = elements
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$gloss["page"]." ".
				"AND deleted = '0000-00-00 00:00:00' ".
				"ORDER BY nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$Element = $this->exportElement($row["id"], $Definition);
		}
		// free result set
		$result->free();
		
		//-------------
		// free memory:
		//-------------
		unset($sql, $row, $gloss);
		
		//--------------------------
		// return GlossaryItem tree:
		//--------------------------
		return $GlossaryItem;
	}
	
	// ILIAS 2 multiple choice Test --> ILIAS 3 TestItem
	function exportTest ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'page' not needed at all!
		
		//----------------------
		// create TestItem tree:
		//----------------------
		// TestItem
		$TestItem = $this->writeNode($parent, "TestItem");
		
		// TestItem..MetaData
		$MetaData = $this->exportMetadata($id, "mc", $TestItem);
		
		// TestItem..(Question, Answer, [Hint]) 
		// = element of type 6 (el_mc) ***
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$id." ".
				"AND typ = 6 ".
				"AND deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$Element = $this->exportElement($row["id"], $TestItem);
		
		// free result set
		$result->free();
		
		// = element other types ***
		/* ***
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$id." ".
				"AND typ <> 6 ".
				"AND deleted = '0000-00-00 00:00:00' ".
				"ORDER BY nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$Element = $this->exportElement($row["id"], $TestItem);
		}
		// free result set
		$result->free();
		*/
		
		//-------------
		// free memory:
		//-------------
		unset($sql, $row);
		
		//----------------------
		// return TestItem tree:
		//----------------------
		return $TestItem;
	}
	
	// ILIAS 2 Page --> ILIAS 3 PageObject
	function exportPage ($id, $parent)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'page' not needed at all!
		
		// table 'page_glossar'
		/* ***
		$sql =	"SELECT glossar ".
				"FROM page_glossar ".
				"WHERE page = ".$id." ". 
				"ORDER BY id;";
		*/
		
		$sql =	"SELECT p.id AS page, g.begriff AS term ".
				"FROM page AS p, glossar AS g, page_glossar AS pg ".
				"WHERE p.id = g.page ".
				"AND g.id = pg.glossar ".
				"AND pg.page = ".$id." ".
				"ORDER BY pg.id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$gloss[] = $row;
		}
		// free result set
		$result->free();
		
		// table 'page_frage' // *** ggf. content für link
		$sql =	"SELECT mc_id ".
				"FROM page_frage ".
				"WHERE pg_id = ".$id." ". 
				"ORDER BY nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$mc[] = $row;
		}
		// free result set
		$result->free();
		
		// table 'page_link'
		$sql =	"SELECT titel, url ".
				"FROM page_link ".
				"WHERE page = ".$id." ". 
				"ORDER BY id;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$link[] = $row;
		}
		// free result set
		$result->free();
		
		//------------------------
		// create PageObject tree:
		//------------------------ 				
		// PageObject
		$PageObject = $this->writeNode($parent, "PageObject");
		
		// PageObject..MetaData
		$MetaData = $this->exportMetadata($id, "pg", $PageObject);
		
		// PageObject..(Paragraph | MediaObject) 
		// = elements  *** auf leere Seiten prüfen
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$id." ".
				"AND deleted = '0000-00-00 00:00:00' ".
				"ORDER BY nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$Element = $this->exportElement($row["id"], $PageObject);
		}
		// free result set
		$result->free();
		
		// PageObject..Paragraph
		// = page's glossary items
		if (is_array($gloss))
		{
			foreach ($gloss as $value)
			{
				$attrs = array();
				$attrs["Language"] = "de"; // *** aus ... holen
				$attrs["Characteristic"] = "Additional";
				$Paragraph = $this->writeNode($PageObject, "Paragraph", $attrs);
				
				// ..IntLink (-> GlossaryItem)
				$attrs = array();
				$attrs["Target"] = "pg_".$value["page"];
				$attrs["Type"] = $this->utils->selectTargetType("gl");
				$IntLink = $this->writeNode($Paragraph, "IntLink", $attrs, $value["term"]);
			}
		}
		
		// PageObject..Paragraph 
		// = page's links
		if (is_array($link))
		{
			foreach ($link as $value)
			{
				$attrs = array();
				$attrs["Language"] = "de"; // *** aus ... holen
				$attrs["Characteristic"] = "Additional";
				$Paragraph = $this->writeNode($PageObject, "Paragraph", $attrs);
				
				// ..ExtLink (-> URL)
				$attrs = array();
				$attrs["Href"] = $value["url"];
				$ExtLink = $this->writeNode($Paragraph, "ExtLink", $attrs, $value["titel"]);
			}
		}
		
		// PageObject..Paragraph 
		// = page's mc questions
		if (is_array($mc))
		{
			foreach ($mc as $value)
			{
				$attrs = array();
				$attrs["Language"] = "de"; // *** aus ... holen
				$attrs["Characteristic"] = "Additional";
				$Paragraph = $this->writeNode($PageObject, "Paragraph", $attrs);
				
				// ..IntLink (-> TestItem)
				$attrs = array();
				$attrs["Target"] = "mc_".$value["mc_id"];
				$attrs["Type"] = $this->utils->selectTargetType("mc");
				$IntLink = $this->writeNode($Paragraph, "IntLink", $attrs);
			}
		}
		
		// PageObject..Layout --> unavailable for pages in ILIAS 2
		
		//-------------
		// free memory:
		//-------------
		unset($sql, $row, $gloss, $link, $mc, $value, $attrs);
		
		//------------------
		// return PageObject
		//------------------
		return $PageObject;
	}
	
	// ILIAS 2 Structure (chapter) --> ILIAS 3 StructureObject
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
		$MetaData = $this->exportMetadata($id, "st", $StructureObject);
		
		// StructureObject..StructureObject(s) (recursion for subchapters)
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$SubStructureObject = $this->exportStructure($row["id"], $StructureObject); // *** Bezeichner ändern
		}
		// free result set
		$result->free();
		
		// StructureObject..PageObject(s) (= linked pages of type 'le')
		$sql =	"SELECT st.page AS page ".
				"FROM struktur AS st, page AS pg ".
				"WHERE st.page = pg.id ".
				"AND st.gliederung = ".$id." ".
				"AND pg.pg_typ = 'le' ".
				"AND pg.deleted = '0000-00-00 00:00:00' ";
				"ORDER BY st.nr;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			//-------------------------
			// create PageObject tree:
			//-------------------------		
			// PageObject
			$PageObject = $this->writeNode($StructureObject, "PageObject");
			
			// PageObject..PageAlias
			$attrs = array();
			$attrs["OriginId"] = "pg_".$row["page"];
			$PageAlias = $this->writeNode($PageObject, "PageAlias", $attrs);
			
			// PageObject..Layout --> unavailable for pages in ILIAS 2
		}
		// free result set
		$result->free();
		
		// StructureObject..Layout --> unavailable for structure in ILIAS 2
		
		//-------------
		// free memory:
		//-------------
		unset($sql, $row, $attrs);
		
		//-----------------------------
		// return StructureObject tree:
		//-----------------------------
		return $StructureObject;
	}
	
	// ILIAS 2 Learningunit --> ILIAS 3 LearningModule
	function exportLearningunit ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'lerneinheit'
		$sql =	"SELECT id ".
				"FROM lerneinheit ".
				"WHERE id = ".$id." ".
				"AND deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// check row number
		if ($result->numRows() == 0)
		{
			die ("ERROR: No Learningunit with the id ".$id." available."); // ***
		}
		$result->free();
		
		//----------------------------
		// create LearningModule tree:
		//----------------------------		
		// LearningModule
		$LearningModule = $this->writeNode($this->doc, "LearningModule");
		
		// LearningModule..MetaData
		$MetaData = $this->exportMetadata($id, "le", $LearningModule);
		
		// LearningModule..StructureObject
		// = "startpage" of an ILIAS 2 Learningunit
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = -1;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$StructureObject = $this->exportStructure($row["id"], $LearningModule);
		
		// free result set
		$result->free();
		
		// LearningModule..StructureObject(s)
		// = all chapters beeing childern of "startpage" above
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = ".$row["id"]." ".
				"ORDER BY prefix;";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$StructureObject = $this->exportStructure($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		/* ***
		// LearningModule..PageObject(s) 
		// = unlinked/dangling pages
		$sql =	"SELECT p.id AS id ".
				"FROM page AS p ".
				"LEFT JOIN struktur AS s ON p.id = s.page ".
				"WHERE p.lerneinheit = ".$id." ".
				"AND p.pg_typ = 'le' ".
				"AND s.page is NULL ".
				"AND p.deleted = '0000-00-00 00:00:00';";
		*/
		
		// LearningModule..PageObject(s) 
		// = all linked and dangling pages of type 'le'
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = ".$id." ".
				"AND pg_typ = 'le' ".
				"AND deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$PageObject = $this->exportPage($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) 
		// = image elements ***
		$sql =	"SELECT DISTINCT el.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el ".
				"WHERE le.id = ".$id." ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND el.typ = 2 ".
				"AND el.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$MediaObject = $this->exportImage($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) 
		// = imagemap elements ***
		$sql =	"SELECT DISTINCT el.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el ".
				"WHERE le.id = ".$id." ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND el.typ = 5 ".
				"AND el.deleted = '0000-00-00 00:00:00';";
		
		$result = $this->db->query($sql);		
		// check $result for error
		if (DB::isError($result))
		{
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$MediaObject = $this->exportImagemap($row["id"], $LearningModule);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) 
		// = multimedia objects of multimedia elements
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
		
		// LearningModule..MediaObject(s) 
		// = multimedia objects of vri links
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
		
		// LearningModule..MediaObject(s) 
		// = files
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// check row number
		if ($result->numRows() > 0)
		{
			//------------------
			// create Test tree:
			//------------------
			// Test
			$Test = $this->writeNode($LearningModule, "Test");
			
			// Test..MetaData
			$MetaData = $this->exportMetadata($row["id"], "test", $Test);
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// Test..TestItem
			$TestItem = $this->exportTest($row["id"], $Test);
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
			die ($result->getMessage()."<br><font size=-1>SQL: ".$sql."</font>");
		}
		// check row number
		if ($result->numRows() > 0)
		{
			//----------------------
			// create Glossary tree:
			//----------------------
			// Glossary
			$Glossary = $this->writeNode($LearningModule, "Glossary");
			
			// Glossary..MetaData
			$MetaData = $this->exportMetadata($row["id"], "glos", $Glossary);
		}
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// Glossary..GlossaryItem ***
			$GlossaryItem = $this->exportGlossary($row["id"], $Glossary);
		}
		// free result set
		$result->free();
		
		// LearningModule..Bibliography --> unavailable for learningunits in ILIAS 2
		
		// LearningModule..Layout --> unavailable in for learningunits ILIAS 2
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs, $test);
		
		//----------------------------
		// return LearningModule tree:
		//----------------------------
		return $LearningModule;
	}
	
	// create xml output
	function dumpFile ()
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
		$LearningModule = $this->exportLearningunit($this->luId);
		
		// dump xml document on the screen ***
		echo "<PRE>";
		echo htmlentities($this->doc->dump_mem(TRUE));
		echo "</PRE>";
		
		// dump xml document into a file ***
		$this->doc->dump_file($this->targetDir.$this->file, FALSE, TRUE);
		
		// call destructor
		$this->_ILIAS2To3Converter();
	}
}

?>