<?php

/**
* PEAR
* @include
*/
require_once "PEAR.php";

/**
* PEAR DB
* @include
*/
require_once "DB.php";

/**
* convert utilities
* @include
*/
require_once "class.ILIAS2To3Utils.php";

/**
* XML writer
* @include
*/
require_once "class.XmlWriter.php";

/**
* ILIAS 2 to ILIAS 3 content-converting class
* 
* Class to convert ILIAS 2 learningunits to ILIAS 3 LearningModules.
* Generates a valid (ILIAS 3) ilias_lm.dtd XML file 
* out of database dataset of an ILIAS 2 learningunit.
* The XML file and the raw data files belonging to the LearningModule 
* are packaged into a sigle zip file.
* 
* @author	Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version	$Id$
*/
class ILIAS2To3Converter
{
	/**
	* database handle from pear database class 
	* @var		object
	* @access	private
	*/
	var $db;
	
	/**
	* object handle from utility class 
	* @var		object
	* @access	private
	*/
	var $utils;
	
	/**
	* object hangle from XML writer class 
	* @var		object
	* @access	private 
	*/
	var $xml;
	
	/**
	* learninigunit id 
	* @var		integer
	* @access	private 
	*/
	var $luId;
	
	/**
	* ILIAS 2 base directory 
	* @var		string
	* @access	private 
	*/
	var $iliasDir;
	
	/**
	* source directory 
	* @var		string
	* @access	private 
	*/
	var $sourceDir;
	
	/**
	* target directory 
	* @var		string
	* @access	private 
	*/
	var $targetDir;
	
	/**
	* directory name
	* @var		string
	* @access	private 
	*/
	var $dir;
	
	/**
	* file name
	* @var		string
	* @access	private 
	*/
	var $file;
	
	/**
	* current language 
	* @var		string
	* @access	private 
	*/
	var $curLang;
	
	/**
	* metadata set (element General) 
	* @var		array
	* @access	private 
	*/
	var $metaData;
	
	/**
	* constructor 
	* @param	string	user
	* @param	string	password
	* @param	string	host
	* @param	string	database
	* @param	string	complete path to zip command
	* @param	string	complete path to ILIAS 2 directory
	* @param	string	complete path to ILIAS 2 data directory
	* @param	string	complete path to target directory
	* @access	public
	*/
	function ILIAS2To3Converter ($user, $pass, $host, $dbname, $zipCmd, $iliasDir, $sDir, $tDir)
	{
		// set member vars
		$this->iliasDir = $iliasDir;
		$this->sourceDir = $sDir;
		$this->targetDir = $tDir;
		
		// build dsn of database connection and connect
		$dsn = "mysql://".$user.":".$pass."@".$host."/".$dbname;
		$this->db = DB::connect($dsn, TRUE);
		
		// test for valid connection
		if (DB::isError($this->db))
		{
			// display error message if an error occured
			die ($this->db->getMessage()." in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
		
		// create utility object
		$this->utils = new ILIAS2To3Utils($zipCmd);
	}
	
	/**
	* destructor 
	* @access	public
	*/
	function _ILIAS2To3Converter ()
	{
		// quit connection
		$this->db->disconnect();
		
		// destroy utility object
		$this->utils->_ILIAS2To3Utils;
	}
	
	/**
	* DB query wrapper
	* @param	string	sql statement
	* @param	string	calling script (to fill with __FILE__)
	* @param	string	calling script line (to fill with __LINE__)
	* @access	private
	*/
	function dbQuery ($sql, $file = "", $line  = "")
	{
		// get result of query
		$result = $this->db->query($sql);		
		
		// check result for error
		if (DB::isError($result))
		{
			// display error message if an error occured
			die ($result->getMessage().": \"".$sql."\" in <b>".$file."</b> on line <b>".$line."</b><br />");
		}
		else
		{
			return $result;
		}
	}
	
	/**
	* Exports ILIAS 2 Metadata to ILIAS3 MetaData
	* @param	integer	object id
	* @param	string	object type [le|st|pg|img|imap|mm|file|el|test|mc|glos|gl]
	* @access	private
	*/
	function exportMetadata ($id, $type)
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
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				$glied = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// there is not dedicated metadata for chapters in ILIAS 2
				// set array with the minimum data needed in ILIAS 3
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
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				$image = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// check for orginal file name and set it to id if empty
				if ($image["datei"] == "")
				{
					$image["datei"] = $id;
				}
				
				// set array with mimetype, size and location for the image file
				$tech[] = $this->utils->getTechInfo($this->dir, "objects/image".$id."/".$image["datei"]);
				break;
			
			case "imap":
				// reset type (imap -> el)
				// imagemaps are treated as a particular element type 
				$type = "el";
				
				// table 'el_map'
				$sql =	"SELECT type ".
						"FROM el_map ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				$map = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set array with mimetype, size and location for the image file
				$tech[] = $this->utils->getTechInfo($this->dir, "objects/imagemap".$id."/".$id.".".$map["type"]);
				break;
			
			case "mm":
				// table 'multimedia'
				$sql =	"SELECT st_type, file, verweis, startklasse ".
								" full_view, full_type, full_file, full_ref ".
						"FROM multimedia ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// Todo: resolve 'startklasse'
				
				// object's standard view, local file (object)
				if ($mm["st_type"] == "file")
				{
					// set array with mimetype, size and location for the image file
					$tech[] = $this->utils->getTechInfo($this->dir, "objects/mm".$id."/".$mm["file"]);
				}
				else // referenced file (object)
				{
					// set array with mimetype, size and location for the image file
					$tech[] = $this->utils->getTechInfo($mm["verweis"]);
				}
				
				// object's full view 
				if ($mm["full_view"] == "y")
				{
					// local file (object)
					if ($mm["full_type"] == "file")
					{
						// set array with mimetype, size and location for the image file
						$tech[] = $this->utils->getTechInfo($this->dir, "objects/mm".$id."/".$mm["full_file"]);
					}
					else // referenced file (object)
					{
						// set array with mimetype, size and location for the image file
						$tech[] = $this->utils->getTechInfo($mm["full_ref"]);
					}
				}
				break;
			
			case "file":
				// table 'file'
				$sql =	"SELECT file ".
						"FROM file ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				$file = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set array with mimetype, size and location for the image file
				$tech[] = $this->utils->getTechInfo($this->dir, "objects/file".$id."/".$file["file"]);
				break;
			
			case "el":
				// no additional data available
				break;
			
			case "test": // Test
				// there is not dedicated metadata for test (as a whole) in ILIAS 2
				// set array with the minimum data needed in ILIAS 3
				$gen = $this->metaData;
				// reset identifier entry
				$gen["Entry"]		= $type;
				break;
			
			case "mc":  // TestItem
				// no additional data available
				break;
			
			case "glos": // Glossary
				// there is not dedicated metadata for glossary (as a whole) in ILIAS 2
				// set array with the minimum data needed in ILIAS 3
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
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// set array with Lifecycle (Contribute) data
				$cont["Role"]			= "Author";
				$cont["Entity"]			= $row["firstname"]." ".$row["surname"];
				$cont["Date"]			= $row["utime"];
				$life["Contribute"][]	= $cont;
				break;
		}
		
		// proceed only for object having metadata in ILIAS 2
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
			// get row
			$meta = $result->fetchRow(DB_FETCHMODE_ASSOC);
			// free result set
			$result->free();
			
			// save current language in the dedicated object var
			// (used while objects processing)
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
			else // only save current language
			{
				if ($meta["lang"] <> "")
				{
			    	$this->curLang = $meta["lang"];
			    }
				else
				{
					$meta["lang"] = $this->curLang;
				}
			}
			
			// set General data in an array
			$gen["Catalog"]		= "ILIAS2 ".$meta["inst"];
			$gen["Entry"]		= $type."_".$id;
			$gen["Title"]		= $meta["title"];
			$gen["Language"]	= $meta["lang"];
			$gen["Description"]	= $meta["description"];
			
			// set array with Lifecycle data
			$life["Status"]			= $this->utils->selectStatus($meta["status"]);
			$life["Version"]		= "Not available"; // default
			$cont["Role"]			= "Publisher";
			$cont["Entity"]			= $meta["publisher"];
			$cont["Date"]			= $meta["publish_date"];
			$life["Contribute"][]	= $cont;
			
			// set array with Educational data
			$edu["InteractivityType"]		= "Expositive"; // default
			$edu["LearningResourceType"]	= $this->utils->selectMaterialType($mtype["mtype"]);
			$edu["InteractivityLevel"]		= "Medium"; // default
			$edu["SemanticDensity"]			= "Medium"; // default
			$edu["IntendedEndUserRole"]		= "Learner"; // default
			$edu["Context"]					= $this->utils->selectLevel($meta["level"]);
			$edu["Difficulty"]				= $this->utils->selectDifficulty($meta["diff"]);
			$edu["TypicalAgeRange"]			= "Not available"; // default
			$edu["TypicalLearningTime"]		= "00:00:00"; // default
			
			// set array with Classification data
			$tax["Purpose"]	= "EducationalLevel";
			$tax["Taxon"]	= $this->utils->selectMaterialLevel($meta["material_level"]);
			$class[]		= $tax;
			
			// table 'meta_keyword'
			$sql =	"SELECT DISTINCT keyword ".
					"FROM meta_keyword ".
					"WHERE id = ".$id." ".
					"AND typ = '".$type."';";
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
					// get db result
					$result2 = $this->dbQuery($sql2, __FILE__, __LINE__);
					// get row
					$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
					// free result set
					$result2->free();
					
					// set array with Lifecycle (Contribute) data
					$cont["Role"]			= "Author";
					$cont["Entity"]			= $row2["author_firstname"]." ".$row2["author_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
				else
				{
					// set array with Lifecycle (Contribute) data
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
					// get db result
					$result2 = $this->dbQuery($sql2, __FILE__, __LINE__);
					// get row
					$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
					// free result set
					$result2->free();
					
					// set array with Lifecycle (Contribute) data
					$cont["Role"]			= "TechnicalImplementer";
					$cont["Entity"]			= $row2["contrib_firstname"]." ".$row2["contrib_surname"];
					$cont["Date"]			= $meta["last_modified_date"];
					$life["Contribute"][]	= $cont;
				}
				else
				{
					// set array with Lifecycle (Contribute) data
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// set array with Classification data
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
			// get db result
			$result = $this->dbQuery($sql, __FILE__, __LINE__);
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// set array with Classification data
				$tax["Purpose"]	= "Discipline";
				$tax["Taxon"]	= $row["subdiscipline"];
				$class[]		= $tax;
			}
			// free result set
			$result->free();
			
			// table 'meta_ibo_kat' --> information not used in ILIAS 3
					
			// table 'meta_ibo_right' --> information not used in ILIAS 3
			
			// save minimal metadata (MetaData..General) in the dedicated object var
			// (used in objects with no metadata in ILIAS 2)
			if ($type == "le")
			{
			    $this->metaData = $gen;
			}
		}
		
		//----------------------
		// create MetaData tree:
		//----------------------
		// MetaData (starttag)
		$this->xml->xmlStartTag("MetaData");
		
		// 1 MetaData..General (starttag)
		$attrs = array();
		$attrs["Structure"] = $this->utils->selectStructure($type);
		$this->xml->xmlStartTag("General", $attrs);
		
		// 1.1 MetaData..General..Identifier
		$attrs = array();
		$attrs["Catalog"]	= $gen["Catalog"];
		$attrs["Entry"]		= $gen["Entry"];
		$this->xml->xmlElement("Identifier", $attrs);
		
		// 1.2 MetaData..General..Title
		$attrs = array();
		$attrs["Language"] = $gen["Language"];
		$this->xml->xmlElement("Title", $attrs, $gen["title"]);
		
		// 1.3 MetaData..General..Language
		$this->xml->xmlElement("Language", NULL, $gen["Language"]);
		
		// 1.4 MetaData..General..Description
		$attrs = array();
		$attrs["Language"] = $gen["Language"];
		$this->xml->xmlElement("Description", $attrs, $gen["Description"]);
		
		// 1.5 MetaData..General..Keyword
		foreach ($gen["Keyword"] as $value) 
		{
			$attrs = array();
			$attrs["Language"] = $gen["Language"];
			$this->xml->xmlElement("Keyword", $attrs, $value);
		}
		
		// 1 MetaData..General (endtag)
		$this->xml->xmlEndTag("General");
		
		// 1.6 ..General..Covarage --> unavailable in ILIAS 2
		
		// 2 MetaData..Lifecycle
		if (is_array($life))
		{
			// (starttag)
			$attrs = array();
			$attrs["Status"] = $life["Status"];
			$this->xml->xmlStartTag("Lifecycle", $attrs);
			
			// 2.1 MetaData..Lifecycle..Version
			$attrs = array();
			$attrs["Language"] = $gen["Language"];
			$this->xml->xmlElement("Version", $attrs, $life["Version"]);
			
			// 2.3 MetaData..Lifecycle..Contribute
			foreach ($life["Contribute"] as $value) 
			{
				// (starttag)
				$attrs = array();
				$attrs["Role"] = $value["Role"];
				$this->xml->xmlStartTag("Contribute", $attrs);
				
				// 2.3.2 MetaData..Lifecycle..Contribute..Entity
				$this->xml->xmlElement("Entity", NULL, $value["Entity"]);
				
				// 2.3.3 MetaData..Lifecycle..Contribute..Date
				$this->xml->xmlElement("Date", NULL, $value["Date"]);
				
				// (endtag)
				$this->xml->xmlEndTag("Contribute");
			}
			
			// (endtag)
			$this->xml->xmlEndTag("Lifecycle");
		}
		
		// 3 MetaData..Meta-Metadata  --> unavailable in ILIAS 2
		
		// 4 MetaData..Technical
		if (is_array($tech))
		{
			foreach ($tech as $value) 
			{
				// (starttag)
				$attrs = array();
				$attrs["Format"] = $value["Format"];
				$this->xml->xmlStartTag("Technical", $attrs);
				
				// 4.2 MetaData..Technical..Size
				$this->xml->xmlElement("Size", NULL, $value["Size"]);
				
				// 4.3 MetaData..Technical..Location
				$this->xml->xmlElement("Location", NULL, $value["Location"]);
				
				// 4.4 MetaData..Technical..(Requirement | OrComposite) --> unavailable in ILIAS 2
				
				// 4.5 MetaData..Technical..InstallationRemarks --> unavailable in ILIAS 2
				
				// 4.6 MetaData..Technical..OtherPlatformRequirements --> unavailable in ILIAS 2
				
				// 4.7 MetaData..Technical..Duration --> unavailable in ILIAS 2
				
				// (endtag)
				$this->xml->xmlEndTag("Technical");
			}
		}
		
		// 5 MetaData..Educational
		if (is_array($edu))
		{
			// (starttag)
			$attrs = array();
			$attrs["InteractivityType"]		= $edu["InteractivityType"];
			$attrs["LearningResourceType"]	= $edu["LearningResourceType"];
			$attrs["InteractivityLevel"]	= $edu["InteractivityLevel"];
			$attrs["SemanticDensity"]		= $edu["SemanticDensity"];
			$attrs["IntendedEndUserRole"]	= $edu["IntendedEndUserRole"];
			$attrs["Context"]				= $edu["Context"];
			$attrs["Difficulty"]			= $edu["Difficulty"];
			$this->xml->xmlStartTag("Educational", $attrs);
			
			// 5.7 MetaData..Educational..TypicalAgeRange
			$attrs = array();
			$attrs["Language"] = $gen["Language"];
			$this->xml->xmlElement("TypicalAgeRange", $attrs, $edu["TypicalAgeRange"]);
			
			// 5.9 MetaData..Educational..TypicalLearningTime
			$this->xml->xmlElement("TypicalLearningTime", NULL, $edu["TypicalLearningTime"]);
			
			// (endtag)
			$this->xml->xmlEndTag("Educational");
		}
		
		// 6 MetaData..Rights --> unavailable in ILIAS 2
		
		// 7 MetaData..Relation --> unavailable in ILIAS 2
		
		// 8 MetaData..Annotation --> unavailable in ILIAS 2
		
		// 9 MetaData..Classification
		if (is_array($class))
		{
			foreach ($class as $value) 
			{
				// (starttag)
				$attrs = array();
				$attrs["Purpose"] = $value["Purpose"];
				$this->xml->xmlStartTag("Classification", $attrs);
				
				// 9.2 MetaData..Classification..TaxonPath (starttag)
				$this->xml->xmlStartTag("TaxonPath");
				
				// 9.2.1 MetaData..Classification..TaxonPath..Source
				$attrs = array();
				$attrs["Language"] = $gen["Language"];
				$this->xml->xmlElement("Source", $attrs, $gen["Catalog"]);
				
				// 9.2.2 MetaData..Classification..TaxonPath.Taxon
				$attrs = array();
				$attrs["Language"] = $gen["Language"];
				$this->xml->xmlElement("Taxon", $attrs, $value["Taxon"]);
				
				// 9.2 MetaData..Classification..TaxonPath (starttag)
				$this->xml->xmlEndTag("TaxonPath");
				
				// MetaData..Classification..Description
				$attrs = array();
				$attrs["Language"] = $gen["Language"];
				$this->xml->xmlElement("Description", $attrs, "No description"); // default
				
				// MetaData..Classification..Keyword
				$attrs = array();
				$attrs["Language"] = $gen["Language"];
				$this->xml->xmlElement("Keyword", $attrs, "No keywords"); // default
				
				// (endtag)
				$this->xml->xmlEndTag("Classification");
			}
		}
		
		// MetaData (endtag)
		$this->xml->xmlEndTag("MetaData");
	}
	
	/**
	* Exports ILIAS 2 Image (element) to ILIAS 3 MediaObject
	* @param	integer	image (element) id
	* @access	private
	*/
	function exportImage ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element' not needed at all!
		
		// table 'el_bild'
		$sql =	"SELECT datei, align ".
				"FROM el_bild ".
				"WHERE id = ".$id.";";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		$image = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// check for orginal file name and set it to id if empty
		if ($image["datei"] == "")
		{
			$image["datei"] = $id;
		}
		
		// Todo: resolve 'align' (image-alignment)
		
		//--------------
		// copy file(s):
		//--------------
		$this->utils->copyObjectFiles ($this->iliasDir."bilder/", $this->dir."objects/", $id, "img", $image["datei"]);
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject (starttag)
		$this->xml->xmlStartTag("MediaObject");
		
		// MediaObject..MetaData
		$this->exportMetadata($id, "img");
		
		// MediaObject..Layout ***
		
		// MediaObject..Parameter --> unavailable for images in ILIAS 2
		
		// MediaObject (endtag)
		$this->xml->xmlEndTag("MediaObject");
	}
	
	/**
	* Exports ILIAS 2 Imagemap (element) to ILIAS 3 MediaObject
	* @param	integer	imagemap (element) id
	* @access	private
	*/
	function exportImagemap ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element' not needed at all!
		
		// table 'el_map'
		$sql =	"SELECT align, type ".
				"FROM el_map ".
				"WHERE id = ".$id.";";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row
		$map = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// Todo: resolve 'align' (imagemap-alignment)
		
		//--------------
		// copy file(s):
		//--------------
		$this->utils->copyObjectFiles ($this->iliasDir."imagemaps/", $this->dir."objects/", $id, "imap", $id.".".$map["type"]);
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject (starttag)
		$this->xml->xmlStartTag("MediaObject");
		
		// MediaObject..MetaData
		$this->exportMetadata($id, "imap");
		
		// MediaObject..Layout ***
		
		// MediaObject..Parameter --> unavailable for imagemaps in ILIAS 2
		
		// MediaObject (endtag)
		$this->xml->xmlEndTag("MediaObject");
	}
	
	/**
	* Exports ILIAS 2 Multimedia object to ILIAS 3 MediaObject
	* @param	integer	multimedia object id
	* @access	private
	*/
	function exportMultimedia ($id)
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		//--------------
		// copy file(s):
		//--------------
		// if kept locally
		if ($mm["st_type"] == "file" or
			$mm["full_type"] == "file")
		{
			$this->utils->copyObjectFiles ($this->iliasDir."objects/", $this->dir."objects/", $id, "mm");
		}
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject (starttag)
		$this->xml->xmlStartTag("MediaObject");
		
		// MediaObject..MetaData
		$this->exportMetadata($id, "mm");
		
		// MediaObject..Layout
		// = special size
		if (!$this->utils->selectBool($mm["org_size"]))
		{
			$attrs = array();
			$attrs["Width"]		= $mm["width"];
			$attrs["Height"]	= $mm["height"];
			$this->xml->xmlElement("Layout", $attrs);
		}
		
		// MediaObject..Parameter
		// = full special size
		if (!$this->utils->selectBool($mm["full_org_size"]))
		{
			$attrs = array();
			$attrs["Name"]	= "full_width";
			$attrs["Value"]	= $mm["full_width"];
			$this->xml->xmlElement("Parameter", $attrs);
			
			$attrs = array();
			$attrs["Name"]	= "full_height";
			$attrs["Value"]	= $mm["full_height"];
			$this->xml->xmlElement("Parameter", $attrs);
		}
		
		// MediaObject..Parameter
		// = caption
		if (!empty($mm["caption"]))
		{
			$attrs = array();
			$attrs["Name"]	= "caption";
			$attrs["Value"]	= $mm["caption"];
			$this->xml->xmlElement("Parameter", $attrs);
		}
		
		// MediaObject..Parameter
		// = parameters
		if ($params = $this->utils->fetchParams($mm["defparam"]))
		{
		    foreach ($params as $value)
			{
				$attrs = array();
				$attrs["Name"]	= $value["Name"];
				$attrs["Value"]	= $value["Value"];
				$this->xml->xmlElement("Parameter", $attrs);
			}
		}
		
		// MediaObject (endtag)
		$this->xml->xmlEndTag("MediaObject");
	}
	
	/**
	* Exports ILIAS 2 File object to ILIAS 3 MediaObject
	* @param	integer	file objects id
	* @access	private
	*/
	function exportFile ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'file' not needed at all!
		
		//--------------
		// copy file(s):
		//--------------
		$this->utils->copyObjectFiles ($this->sourceDir."files/", $this->dir."objects/", $id, "file");
		
		//-------------------------
		// create MediaObject tree:
		//-------------------------		
		// MediaObject (starttag)
		$this->xml->xmlStartTag("MediaObject");
		
		// MediaObject..MetaData
		$this->exportMetadata($id, "file");
		
		// MediaObject..Layout --> unavailable for files in ILIAS 2
		
		// MediaObject..Parameter --> unavailable for files in ILIAS 2
		
		// MediaObject (endtag)
		$this->xml->xmlEndTag("MediaObject");
	}
	
	/**
	* Exports ILIAS 2 Text to ILIAS 3 Paragraph
	* @param	string	text data
	* @param	string	value for attribute Characteristic of element Paragraph
	* @param	boolean	markup the data as Code (TRUE) or not (FALSE)
	* @access	private
	*/
	function exportText ($data, $character = "", $code = FALSE)
	{
		// fetch vri (array)
		if ($vri = $this->utils->fetchVri($data,"st|ab|pg|mm"))
		{
		    // fetch text (array)
			$text = $this->utils->fetchText($data);
			
			// ***
			for ($i = 0; $i < count($text); $i++)
			{
				// *** test if empty
				if (!empty($text[$i]))
				{
					// ***
					if ($code)
					{
						// Paragraph (starttag)
						$attrs = array();
						$attrs["Language"] = $this->curLang;
						if ($character <> "")
						{
							$attrs["Characteristic"] = $character;
						}
						$this->xml->xmlStartTag("Paragraph", $attrs);
						
						// Paragraph..Code
						$this->xml->xmlElement("Code", NULL, $text[$i]);
						
						// Paragraph (endtag)
						$this->xml->xmlEndTag("Paragraph");
					}
					else
					{
						// Paragraph
						$attrs = array();
						$attrs["Language"] = $this->curLang;
						if ($character <> "")
						{
							$attrs["Characteristic"] = $character;
						}
						$this->xml->xmlElement("Paragraph", $attrs, $text[$i]);
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
				// Paragraph (starttag)
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($character <> "")
				{
					$attrs["Characteristic"] = $character;
				}
				$this->xml->xmlStartTag("Paragraph", $attrs);
				
				// Paragraph..Code
				$this->xml->xmlElement("Code", NULL, $data);
				
				// Paragraph (endtag)
				$this->xml->xmlEndTag("Paragraph");
			}
			else
			{
				// Paragraph
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($character <> "")
				{
					$attrs["Characteristic"] = $character;
				}
				$this->xml->xmlElement("Paragraph", $attrs, $data);
			}
		}
	}
	
	/**
	* Exports ILIAS 2 vri Link to ILIAS 3 IntLink
	* @param	array	vri link array with fields "inst", "type", "id" and "target"
	* @access	private
	*/
	function exportVri ($vri)
	{
		// initialize switch
		$resolve = TRUE;
		
		// resolve vri Link to IntLink
		switch ($vri["type"]) 
		{
			case "st":
				// get page corresponding to structure 
				// and check if it is a part of the learnining unit 
				$sql =	"SELECT st.page AS page ".
						"FROM struktur AS st, page AS pg ".
						"WHERE st.page = pg.id ".
						"AND st.id = ".$vri["id"]." ".
						"AND pg.lerneinheit = ".$this->luId." ".
						"AND pg.pg_typ = 'le' ".
						"AND pg.deleted = '0000-00-00 00:00:00';";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// check row number
				if ($result->numRows() > 0)
				{
					// get row
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					
					// reset link data
					$vri["id"] = $row["page"];
					$vri["type"] = "pg";
					$type = "pg";
				}
				else
				{
					$resolve = FALSE;
				}
				// free result set
				$result->free();
				break;
			
			case "pg":
			case "ab":
				// check if page (type = 'le|gl|mc' is a part of the learnining unit 
				$sql =	"SELECT id, pg_typ ".
						"FROM page ".
						"WHERE id = ".$vri["id"]." ".
						"AND lerneinheit = ".$this->luId." ".
						"AND deleted = '0000-00-00 00:00:00';";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// check row number
				if ($result->numRows() > 0)
				{
					// get row
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					
					// reset link data
					if ($row["pg_typ"] == "le")
					{
						$vri["type"] = "pg";
						$type = "pg";
					}
					if ($row["pg_typ"] == "gl")
					{
						$vri["type"] = "gl";
						$type = "pg";
					}
					if ($row["pg_typ"] == "mc")
					{
						$vri["type"] = "mc";
						$type = "mc";
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
				// no test needed
				
				// reset link data
				$type = "mm";
				break;
		}
		
		if ($resolve)
		{
			// Paragraph (starttag)
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$this->xml->xmlStartTag("Paragraph", $attrs);
			
			// Paragraph..IntLink
			$attrs = array();
			$attrs["Target"]	= $type."_".$vri["id"];
			$attrs["Type"]		= $this->utils->selectTargetType($vri["type"]);
			if ($vri["target"] <> "")
			{
				$attrs["TargetFrame"] = $vri["target"];
			}
			$this->xml->xmlElement("IntLink", $attrs, $vri["content"]);
			
			// Paragraph (endtag)
			$this->xml->xmlEndTag("Paragraph");
		}
		else
		{
			// Paragraph
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$text = "Link could not be resolved - Target object is not a part of current learningunit. ";
			$text .= "[".$vri["content"]." ";
			$text .= "Target=".$vri["id"]." ";
			$text .= "Type=".$vri["type"];
			if ($vri["target"] <> "")
			{
				$text .= " TargetFrame=".$vri["target"]."]";
			}
			$this->xml->xmlElement("Paragraph", $attrs, $text);
		}
	}
	
	/**
	* Exports ILIAS 2 Element to ILIAS 3 Paragraph or MediaObject (depends on type)
	* @param	integer	element id
	* @access	private
	*/
	function exportElement ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'element'
		$sql =	"SELECT typ, src, bsp ".
				"FROM element ".
				"WHERE id = ".$id.";";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row
		$element = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// set code and expample flags (TRUE or FALSE)
		$code = $this->utils->selectBool($element["src"]);
		$expl = $this->utils->selectBool($element["bsp"]);
		
		// select tables according to element's type
		switch($element["typ"]) 
		{
			case 1: // text
				// table 'el_text'
				$sql =	"SELECT text, align ".
						"FROM el_text ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// Todo: resolve 'align' (text-alignment and List(s))
				
				// get value for attribute Characteristic
				if ($text["align"] == 5)
				{
				    $char = "Citation";
				}
				elseif ($text["align"] == 6)
				{
				    $char = "Mnemonic";
				}
				else
				{
					if ($expl)
					{
				    	$char = "Example";
				    }
					else
					{
				    	$char = "";
				    }
				}
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
				// Paragraph(s)
				$this->exportText($text["text"], $parent, $char, $code);
				break;
			
			// image (bild)
			case 2:
				// table 'el_bild' not needed at all!
				
				//-------------------------
				// create MediaObject tree:
				//-------------------------		
				// MediaObject (starttag)
				$this->xml->xmlStartTag("MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array();
				$attrs["OriginId"] = "el_".$id;
				$this->xml->xmlElement("MediaAlias", $attrs);
				
				// MediaObject..Layout --> default used
				
				// MediaObject..Parameter --> default used
				
				// MediaObject (endtag)
				$this->xml->xmlEndTag("MediaObject");
				break;
			
			// title
			case 3:
				// table 'el_title'
				$sql =	"SELECT text ".
						"FROM el_titel ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$text = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
				// Paragraph
				$this->exportText($text["text"], $parent, "Headline", $code);
				break;
				
			// table
			case 4:
				// table 'el_table' and 'meta'
				$sql =	"SELECT t.rows, t.border, t.caption, t.capalign, t.width, ".
							"t.align, m.title ".
						"FROM el_table AS t, meta AS m ".
						"WHERE t.id = m.id ".
						"AND t.id = ".$id." ".
						"AND m.typ = 'el';";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$table = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// Todo: resolve 'align' (table-alignment)
				
				// table 'table_cell' and 'table_rowcol'
				$sql =	"SELECT tc.row, tc.text, tc.textform, tr.width ".
						"FROM table_cell AS tc, table_rowcol AS tr ".
						"WHERE tc.id = ".$id." ".
						"AND tc.id = tr.id ".
						"AND tr.rowcol = 'c' ".
						"AND tc.col = tr.nr ".
						"ORDER BY row, col;";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$data[] = $row;
				}
				// free result set
				$result->free();
				
				// Todo: resolve vri-links in 'text' (IntLink)
				// Todo: resolve 'textform' (text markup)
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------
				// Paragraph (starttag)
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($expl)
				{
					$attrs["Characteristic"] = "Example";
				}
				$this->xml->xmlStartTag("Paragraph", $attrs);
				
				// Paragraph..Table (starttag)
				$attrs = array();
				$attrs["Id"] = "tb_".$id;
				if ($table["width"] <> "")
				{
					$attrs["Width"] = $table["width"];
				}
				$attrs["Border"] = $table["border"];
				$this->xml->xmlStartTag("Table", $attrs);
				
				// Paragraph..Table..Title
				$attrs = array();
				$attrs["Language"]	= $this->curLang;
				$this->xml->xmlElement("Title", $attrs, $table["title"]);
				
				// Paragraph..Table..HeaderCaption
				if ($table["capalign"] == 0 and
					$table["caption"] <> "")
				{
					$this->xml->xmlElement("HeaderCaption", NULL,$table["caption"]);
				}
				
				// Paragraph..Table..FooterCaption
				if ($table["capalign"] == 1 and
					$table["caption"] <> "")
				{
					$this->xml->xmlElement("FooterCaption", NULL,$table["caption"]);
				}
				
				// Paragraph..Table..Summary  --> unavailable in ILIAS 2
				
				// Paragraph..Table..TableRow
				if (is_array($data))
				{
					for ($i = 1; $i <= $table["rows"]; $i++)
					{
						// (starttag)
						$this->xml->xmlStartTag("TableRow");
						
						foreach ($data as $value) 
						{
							if ($value["row"] == $i)
							{
								// Paragraph..Table..TableRow..TableData
								if ($value["width"] <> "")
								{
									$attrs = array();
									$attrs["Width"] = $value["width"];
								}
								else
								{
									$attrs = Null;
								}
								$this->xml->xmlElement("TableData", $attrs, $value["text"]);
								break;
							}
						}
						
						// (endtag)
						$this->xml->xmlEndTag("TableRow");
					}
				}
				
				// Paragraph..Table (endtag)
				$this->xml->xmlEndTag("Table");
				
				// Paragraph (endtag)
				$this->xml->xmlEndTag("Paragraph");
				break;
			
			// imagemap
			case 5:
				// table 'el_map' not needed at all!
				
				// table 'maparea'
				$sql =	"SELECT shape, coords, href, alt ".
						"FROM maparea ".
						"WHERE id = ".$id." ".
						"ORDER BY nr;";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$area[] = $row;
				}
				// free result set
				$result->free();
				
				// Todo: resolve vri-links in 'href' (IntLink)
				
				//-----------------------
				// create Paragraph tree:
				//-----------------------		
				// Paragraph (starttag)
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($expl)
				{
					$attrs["Characteristic"] = "Example";
				}
				$this->xml->xmlStartTag("Paragraph", $attrs);
				
				// Paragraph..ImageMap (starttag)
				$attrs = array();
				$attrs["Id"]		= "map_".$id;
				$attrs["ImageId"]	= "el_".$id;
				$this->xml->xmlStartTag("ImageMap", $attrs);
				
				// Paragraph..ImageMap..MapArea
				if (is_array($area))
				{
					foreach ($area as $value)
					{
						$attrs = array();
						$attrs["Shape"]		= $this->utils->selectShape($value["shape"]);
						$attrs["Coords"]	= $value["coords"];
						$attrs["Href"]		= $value["href"];
						$attrs["Alt"]		= $value["alt"];
						$this->xml->xmlElement("MapArea", $attrs);
					}
				}
				else // default
				{
					$attrs = array();
					$attrs["Shape"]		= "Rect";
					$attrs["Coords"]	= "0,0,0,0";
					$attrs["Href"]		= "";
					$attrs["Alt"]		= "Area of an imagemap.";
					$this->xml->xmlElement("MapArea", $attrs);
				}
				
				// Paragraph..ImageMap (endtag)
				$this->xml->xmlEndTag("ImageMap");
				
				// Paragraph (endtag)
				$this->xml->xmlEndTag("Paragraph");
				break;
			
			// multiple choice
			case 6:
				// table 'el_mc'
				$sql =	"SELECT type, text, answer, vristr ".
						"FROM el_mc ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$mc = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// Todo: resolve vri-links in 'text' and 'vristr' (IntLink)
				
				// answer possibilities for flexible questiontype
				if ($mc["type"] == "mul")
				{
					// table 'mc_answer'
					$sql =	"SELECT text, mright ".
							"FROM mc_answer ".
							"WHERE id = ".$id." ".
							"ORDER BY nr;";
					// get db result
					$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
				// TestItem..Question (starttag)
				$this->xml->xmlStartTag("Question");
				
				// TestItem..Question..Paragraph
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($expl)
				{
					$attrs["Characteristic"] = "Example";
				}
				$this->xml->xmlElement("Paragraph", $attrs, $mc["text"]);
				
				// TestItem..Question (endtag)
				$this->xml->xmlEndTag("Question");
				
				// TestItem..Answer
				if ($mc["type"] == "mul" and
					is_array($answer))
				{
					foreach ($answer as $value) 
					{
						// (starttag)
						$attrs = array();
						$attrs["Solution"] = $this->utils->selectAnswer($value["mright"]);
						$this->xml->xmlStartTag("Answer", $attrs);
						
						// TestItem..Answer..Paragraph
						$attrs = array();
						$attrs["Language"] = $this->curLang;
						if ($expl)
						{
							$attrs["Characteristic"] = "Example";
						}
						$this->xml->xmlElement("Paragraph", $attrs, $value["text"]);
						
						// (endtag)
						$this->xml->xmlEndTag("Answer");
					}
				}
				else
				{
					// (starttag)
					$this->xml->xmlStartTag("Answer");
					
					// TestItem..Answer..Paragraph
					$attrs = array();
					$attrs["Language"] = $this->curLang;
					if ($expl)
					{
						$attrs["Characteristic"] = "Example";
					}
					$this->xml->xmlElement("Paragraph", $attrs, $this->utils->selectAnswer($mc["answer"]));
					
					// (endtag)
					$this->xml->xmlEndTag("Answer");
				}
				
				// TestItem..Hint ***
				if ($mc["vristr"] <> "")
				{
					// Todo: resolve possible vri links
				}
				break;
			
			// multimedia
			case 7:
				// table 'el_multimedia'
				$sql =	"SELECT mm_id, align, ".
								"derive_size, width, height, ".
								"derive_full_size, full_width, full_height, ".
								"derive_defparam, paras, ".
								"derive_caption, caption ".
						"FROM el_multimedia ".
						"WHERE id = ".$id.";";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
				// get row
				$mm = $result->fetchRow(DB_FETCHMODE_ASSOC);
				// free result set
				$result->free();
				
				// Todo: resolve 'align' (mm-alignment)
				
				//-------------------------
				// create MediaObject tree:
				//-------------------------		
				// MediaObject (starttag)
				$this->xml->xmlStartTag("MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array();
				$attrs["OriginId"] = "mm_".$mm["mm_id"];
				$this->xml->xmlElement("MediaAlias", $attrs);
				
				// MediaObject..Layout
				// = special size
				if (!$this->utils->selectBool($mm["derive_size"]))
				{
					$attrs = array();
					$attrs["Width"]		= $mm["width"];
					$attrs["Height"]	= $mm["height"];
					$this->xml->xmlElement("Layout", $attrs);
				}
				
				// MediaObject..Parameter
				// = full special size
				if (!$this->utils->selectBool($mm["derive_full_size"]))
				{
					$attrs = array();
					$attrs["Name"]	= "full_width";
					$attrs["Value"]	= $mm["full_width"];
					$this->xml->xmlElement("Parameter", $attrs);
					
					$attrs = array();
					$attrs["Name"]	= "full_height";
					$attrs["Value"]	= $mm["full_height"];
					$this->xml->xmlElement("Parameter", $attrs);
				}
				
				// MediaObject..Parameter
				// = caption
				if (!$this->utils->selectBool($mm["derive_caption"]))
				{
					$attrs = array();
					$attrs["Name"]	= "caption";
					$attrs["Value"]	= $mm["caption"];
					$this->xml->xmlElement("Parameter", $attrs);
				}
				
				// MediaObject..Parameter
				// = parameters
				if (!$this->utils->selectBool($mm["derive_defparam"]))
				{
					if ($params = $this->utils->fetchParams($mm["paras"]))
					{
					    foreach ($params as $value)
						{
							$attrs = array();
							$attrs["Name"]	= $value["Name"];
							$attrs["Value"]	= $value["Value"];
							$this->xml->xmlElement("Parameter", $attrs);
						}
					}
				}
				
				// MediaObject (endtag)
				$this->xml->xmlEndTag("MediaObject");
				break;
			
			case 8: // filelist
				// table 'el_filelist' not needed at all!
				
				// table 'filelist_entry'
				$sql =	"SELECT file_id ".
						"FROM filelist_entry ".
						"WHERE el_id = ".$id." ".
						"ORDER BY nr;";
				// get db result
				$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
				// MediaObject (starttag)
				$this->xml->xmlStartTag("MediaObject");
				
				// MediaObject..MediaAlias
				$attrs = array();
				$attrs["OriginId"] = "file_".$value["file_id"];
				$this->xml->xmlElement("MediaAlias", $attrs);
				
				// MediaObject..Layout --> default used
				
				// MediaObject..Parameter --> default used
				
				// MediaObject (endtag)
				$this->xml->xmlEndTag("MediaObject");
				break;
			
			// temporary dummy for not supported element-types
			default:
				// Paragraph
				$attrs = array();
				$attrs["Language"] = $this->curLang;
				if ($expl)
				{
					$attrs["Characteristic"] = "Example";
				}
				$this->xml->xmlElement("Paragraph", $attrs, "Object not supported yet.");
		}
	}
	
	/**
	* Exports ILIAS 2 Glossar to ILIAS 3 GlossaryItem
	* @param	integer	glossary id
	* @access	private
	*/
	function exportGlossary ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'glossar'
		$sql =	"SELECT page, begriff ".
				"FROM glossar ".
				"WHERE id = ".$id.";";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		$gloss = $result->fetchRow(DB_FETCHMODE_ASSOC);
		// free result set
		$result->free();
		
		// table 'page' (type = 'gl') not needed at all!
		
		//--------------------------
		// create GlossaryItem tree:
		//--------------------------		
		// GlossaryItem (starttag)
		$this->xml->xmlStartTag("GlossaryItem");
		
		// GlossaryItem..MetaData
		$this->exportMetadata($gloss["page"], "gl");
		
		// GlossaryItem..GlossaryTerm
		$this->xml->xmlStartTag("GlossaryTerm", NULL, $gloss["begriff"]);
		
		// GlossaryItem..Definition (starttag)
		$this->xml->xmlStartTag("Definition");
		
		// GlossaryItem..Definition..(Paragraph | MediaObject)
		// = elements
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$gloss["page"]." ".
				"AND deleted = '0000-00-00 00:00:00' ".
				"ORDER BY nr;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportElement($row["id"]);
		}
		// free result set
		$result->free();
		
		// GlossaryItem..Definition (endtag)
		$this->xml->xmlEndTag("Definition");
		
		// GlossaryItem (endtag)
		$this->xml->xmlEndTag("GlossaryItem");
	}
	
	/**
	* Exports ILIAS 2 Multiple Choice Test to ILIAS 3 TestItem
	* @param	integer	test (page) id (type = 'mc')
	* @access	private
	*/
	function exportTest ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'page' not needed at all!
		
		//----------------------
		// create TestItem tree:
		//----------------------
		// TestItem (starttag)
		$this->xml->xmlStartTag("TestItem");
		
		// TestItem..MetaData
		$this->exportMetadata($id, "mc");
		
		// TestItem..(Question, Answer, [Hint]) 
		// = element of type 6 (el_mc) ***
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$id." ".
				"AND typ = 6 ".
				"AND deleted = '0000-00-00 00:00:00';";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$this->exportElement($row["id"]);
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportElement($row["id"]);
		}
		// free result set
		$result->free();
		*/
		
		// TestItem (endtag)
		$this->xml->xmlEndTag("TestItem");
	}
	
	/**
	* Exports ILIAS 2 Page to ILIAS 3 PageObject
	* @param	integer	page id (type = 'le')
	* @access	private
	*/
	function exportPage ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'page' not needed at all!
		
		// table 'page', 'glossar' and 'page_glossar'
		$sql =	"SELECT p.id AS page, g.begriff AS term ".
				"FROM page AS p, glossar AS g, page_glossar AS pg ".
				"WHERE p.id = g.page ".
				"AND g.id = pg.glossar ".
				"AND pg.page = ".$id." ".
				"ORDER BY pg.id;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$gloss[] = $row;
		}
		// free result set
		$result->free();
		
		// table 'page_frage'
		$sql =	"SELECT mc_id, nr ".
				"FROM page_frage ".
				"WHERE pg_id = ".$id." ". 
				"ORDER BY nr;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
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
		// PageObject (starttag)
		$this->xml->xmlStartTag("PageObject");
		
		// PageObject..MetaData
		$this->exportMetadata($id, "pg");
		
		// PageObject..(Paragraph | MediaObject) 
		// = elements
		$sql =	"SELECT id ".
				"FROM element ".
				"WHERE page = ".$id." ".
				"AND deleted = '0000-00-00 00:00:00' ".
				"ORDER BY nr;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportElement($row["id"]);
		}
		// free result set
		$result->free();
		
		// PageObject..Paragraph
		// = page's glossary items
		if (is_array($gloss))
		{
			// (starttag)
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$attrs["Characteristic"] = "Additional";
			$this->xml->xmlStartTag("Paragraph", $attrs);
			
			// PageObject..Paragraph..IntLink (-> GlossaryItem)
			foreach ($gloss as $value)
			{
				$attrs = array();
				$attrs["Target"] = "pg_".$value["page"];
				$attrs["Type"] = $this->utils->selectTargetType("gl");
				$this->xml->xmlElement("IntLink", $attrs, $value["term"]);
			}
			
			// (endtag)
			$this->xml->xmlEndTag("Paragraph");
		}
		
		// PageObject..Paragraph 
		// = page's links
		if (is_array($link))
		{
			// (starttag)
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$attrs["Characteristic"] = "Additional";
			$this->xml->xmlStartTag("Paragraph", $attrs);
			
			// PageObject..Paragraph..ExtLink (-> URL)
			foreach ($link as $value)
			{
				$attrs = array();
				$attrs["Href"] = $value["url"];
				$this->xml->xmlElement("ExtLink", $attrs, $value["titel"]);
			}
			
			// (endtag)
			$this->xml->xmlEndTag("Paragraph");
		}
		
		// PageObject..Paragraph
		// = page's mc questions
		if (is_array($mc))
		{
			// (starttag)
			$attrs = array();
			$attrs["Language"] = $this->curLang;
			$attrs["Characteristic"] = "Additional";
			$this->xml->xmlStartTag("Paragraph", $attrs);
			
			// PageObject..Paragraph..IntLink (-> TestItem)
			foreach ($mc as $value)
			{
				$attrs = array();
				$attrs["Target"] = "mc_".$value["mc_id"];
				$attrs["Type"] = $this->utils->selectTargetType("mc");
				$this->xml->xmlElement("IntLink", $attrs, $value["nr"]);
			}
			
			// (endtag)
			$this->xml->xmlEndTag("Paragraph");
		}
		
		// PageObject..Layout --> unavailable for pages in ILIAS 2
		
		// PageObject (endtag)
		$this->xml->xmlEndTag("PageObject");
	}
	
	/**
	* Exports Structure 2 to ILIAS 3 StructureObject
	* @param	integer	structure (gliederung) id
	* @access	private
	*/
	function exportStructure ($id)
	{
		//-------------------------
		// get data from db tables:
		//-------------------------		
		// table 'gliederung' not needed at all!
		
		//-----------------------------
		// create StructureObject tree:
		//-----------------------------		
		// StructureObject (starttag)
		$this->xml->xmlStartTag("StructureObject");
		
		// StructureObject..MetaData
		$this->exportMetadata($id, "st");
		
		// StructureObject..StructureObject(s)
		// = recursion for subchapters
		$sql =	"SELECT gd.id ".
				"FROM gliederung AS gd, gliederung AS mt ".
				"WHERE gd.mutter = ".$id." ".
				"AND mt.mutter <> -1 ".
				"AND gd.mutter = mt.id ".				
				"ORDER BY gd.prefix;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportStructure($row["id"]);
		}
		// free result set
		$result->free();
		
		// StructureObject..PageObject(s)
		// = linked pages of type 'le'
		$sql =	"SELECT st.page AS page ".
				"FROM struktur AS st, page AS pg ".
				"WHERE st.page = pg.id ".
				"AND st.gliederung = ".$id." ".
				"AND pg.pg_typ = 'le' ".
				"AND pg.deleted = '0000-00-00 00:00:00' ";
				"ORDER BY st.nr;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			//-------------------------
			// create PageObject tree:
			//-------------------------		
			// PageObject (starttag)
			$this->xml->xmlStartTag("PageObject");
			
			// PageObject..PageAlias
			$attrs = array();
			$attrs["OriginId"] = "pg_".$row["page"];
			$this->xml->xmlElement("PageAlias", $attrs);
			
			// PageObject..Layout --> unavailable for pages in ILIAS 2
			
			// PageObject (endtag)
			$this->xml->xmlEndTag("PageObject");
		}
		// free result set
		$result->free();
		
		// StructureObject..Layout --> unavailable for structure in ILIAS 2
		
		// StructureObject (endtag)
		$this->xml->xmlEndTag("StructureObject");
	}
	
	/**
	* Exports Learningunit 2 to ILIAS 3 LearningModule
	* @param	integer	learningunit id
	* @access	private
	*/
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// check row number
		if ($result->numRows() == 0)
		{
			die ("ERROR: No Learningunit with the id ".$id." available.");
		}
		$result->free();
		
		//----------------------------
		// create LearningModule tree:
		//----------------------------		
		// LearningModule (starttag)
		$this->xml->xmlStartTag("LearningModule");
		
		// LearningModule..MetaData
		$this->exportMetadata($id, "le");
		
		// LearningModule..StructureObject
		// = "startpage" of an ILIAS 2 Learningunit
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = -1;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$this->exportStructure($row["id"]);
		// free result set
		$result->free();
		
		// LearningModule..StructureObject(s)
		// = all chapters beeing childern of "startpage" above
		$sql =	"SELECT id ".
				"FROM gliederung ".
				"WHERE lerneinheit = ".$id." ".
				"AND mutter = ".$row["id"]." ".
				"ORDER BY prefix;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportStructure($row["id"]);
		}
		// free result set
		$result->free();
		
		// LearningModule..PageObject(s) 
		// = all linked and dangling pages of type 'le'
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = ".$id." ".
				"AND pg_typ = 'le' ".
				"AND deleted = '0000-00-00 00:00:00';";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportPage($row["id"]);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) 
		// = image elements
		$sql =	"SELECT DISTINCT el.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el ".
				"WHERE le.id = ".$id." ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND el.typ = 2 ".
				"AND el.deleted = '0000-00-00 00:00:00';";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportImage($row["id"]);
		}
		// free result set
		$result->free();
		
		// LearningModule..MediaObject(s) 
		// = imagemap elements
		$sql =	"SELECT DISTINCT el.id AS id ".
				"FROM lerneinheit AS le, page AS pg, element AS el ".
				"WHERE le.id = ".$id." ".
				"AND pg.lerneinheit = le.id ".
				"AND el.page = pg.id ".
				"AND el.typ = 5 ".
				"AND el.deleted = '0000-00-00 00:00:00';";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportImagemap($row["id"]);
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportMultimedia($row["id"]);
			
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// avoiding possible double entries
			if (!in_array($row["id"],$test,strict))
			{
				$this->exportMultimedia($row["id"]);
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
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// get row(s)
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->exportFile($row["id"]);
		}
		// free result set
		$result->free();
		
		// LearningModule..Test
		// = all pages of type 'mc'
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = ".$id." ".
				"AND pg_typ = 'mc' ".
				"AND deleted = '0000-00-00 00:00:00' ";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// check row number
		if ($result->numRows() > 0)
		{
			//------------------
			// create Test tree:
			//------------------
			// Test (starttag)
			$this->xml->xmlStartTag("Test");
			
			// Test..MetaData
			$this->exportMetadata($row["id"], "test");
			
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// Test..TestItem
				$this->exportTest($row["id"]);
			}
			
			// Test (endtag)
			$this->xml->xmlEndTag("Test");
		}
		// free result set
		$result->free();
		
		// LearningModule..Glossary
		// = all glossary terms and their corresponding pages of type 'le'
		$sql =	"SELECT id ".
				"FROM glossar ".
				"WHERE lerneinheit = ".$id." ".
				"AND deleted = '0000-00-00 00:00:00' ";
				"ORDER BY begriff;";
		// get db result
		$result = $this->dbQuery($sql, __FILE__, __LINE__);
		// check row number
		if ($result->numRows() > 0)
		{
			//----------------------
			// create Glossary tree:
			//----------------------
			// Glossary (starttag)
			$this->xml->xmlStartTag("Glossary");
			
			// Glossary..MetaData
			$this->exportMetadata($row["id"], "glos");
			
			// get row(s)
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// Glossary..GlossaryItem
				$this->exportGlossary($row["id"]);
			}
			
			// Glossary (endtag)
			$this->xml->xmlEndTag("Glossary");
		}
		// free result set
		$result->free();
		
		// LearningModule..Bibliography --> unavailable for learningunits in ILIAS 2
		
		// LearningModule..Layout --> unavailable in for learningunits ILIAS 2
		
		// LearningModule (endtag)
		$this->xml->xmlEndTag("LearningModule");
	}
	
	/**
	* Outputs ILIAS 3 LearninigModule and corresponding raw data files into a zip file
	* @param	integer	learningunit id
	* @param	boolean	indent text (TRUE) or not (FALSE)
	* @access	public
	*/
	function dumpLearningModuleFile ($luId, $format = TRUE)
	{
		// set member var for Learningunit
		$this->luId = $luId;
		
		// get timestamp for names
		$date = time();
		
		// set dir and file names *** an ilias2 export anpassen inkl. inst
		$this->dir = $this->targetDir.$date."__lm__le_".$this->luId."/";
		$this->file = $this->dir.$date."__lm__le_".$this->luId.".xml";
		
		// create dir
		$this->utils->makeDir($this->dir);
		
		//-------------------------
		// create new xml document:
		//-------------------------
		// initialize writer object (use default values)
		$this->xml = new XmlWriter;
		
		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE LearningModule SYSTEM \"http://127.0.0.1/ilias3/xml/ilias_lm.dtd\">");
		
		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS 2 learningunit nr ".$this->luId." to ILIAS 3 LearningModule");
		
		// set xml header
		$this->xml->xmlHeader();
		
		// create ILIAS 3 LearningModule out of ILIAS 2 Learningunit
		$this->exportLearningunit($this->luId);
		
		// dump xml document to screen ***
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		
		// dump xml document to file ***
		$this->xml->xmlDumpFile($this->file, $format);
		
		// destroy writer object
		$this->xml->_XmlWriter;
		
		// chage to target dir
		chdir($this->targetDir);
		
		// zip whole stuff (xml file and the copied object files)
		$this->utils->zipDir(basename($this->dir));
	}
}

?>