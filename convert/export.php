<?php

//pear DB abstraction layer
require_once ("DB.php");

/**
* Export of content from ILIAS2 to ILIAS3 using DOMXML
*
* Dependencies:
* 
* @author	Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version	$Id$
*/

// *** = dirty/buggy --> to be modified/extended

class ILIAS2export 
{
	//-----------
	// properties
	//-----------
	
	/**
	* database handle from pear database class
	* 
	* @var string
	* @access private
	*/
	var $db;
	
	/**
	* domxml document object ***
	* 
	* @var object doc
	* @access private 
	*/
	var $doc;
	
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
		$this->db = DB::connect($dsn, true);
		
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
	
	// write node using DOMXML
	function writeNode ($parent, $tag, $attrs = NULL, $text = NULL)
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
		if (is_string($text))
		{
			$nodeText = $this->doc->create_text_node($text);
			$nodeText = $node->append_child($nodeText);
		}
		
		// add element node at at the end of the children of the parent
		$node = $parent->append_child($node);
		
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
			case "pg":
				$str = "Collection";
				break;			
			case "mc": // *** -> test element ohne eigene Daten
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
				$str = "4";
				break;			
			case "pg":
				$str = "2";
				break;			
			case "mc": // *** -> test element ohne eigene Daten
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
				$str = "Easy";
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
							"TaxonLanguage" => "en", // default, due to convert function
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
		
		// 1.6 ..General..Description --> unavailable in ILIAS2
		
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
			case 1: // text element
				
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
				// create Text subtree:
				// *** (convert VRIs, HTML and Layout (alignment))
				//--------------------------
				
				// MetaData *** (Parent LearningObjet already has MetaData)
				
				// Text ***
				$Text = $this->writeNode($parent, "Text");
				
				// Text..Paragraph ***
				$attrs = array(	"Language" => "test", // *** aus meta holen
								"Characteristic" => "test"); // *** aus bsp holen
				$Paragraph = $this->writeNode($Text, "Paragraph", $attrs, $text["text"]);
				
				break;
			
			default: // temporary dummy ***
				
				// Text
				$Text = $this->writeNode($parent, "Text");
				
				// Text..Paragraph
				$attrs = array(	"Language" => "dummy",
								"Characteristic" => "dummy");
				$Paragraph = $this->writeNode($Text, "Paragraph", $attrs, "dummy");
				
				break;
			
			/*
			// image element (bild)
			case 2:
				$sql =	"SELECT * ".
						"FROM el_bild ".
						"WHERE id = $id;";
				
				// copy (image) files
				break;
			
			// title element
			case 3:
				$sql =	"SELECT * ".
						"FROM el_titel ".
						"WHERE id = $id;";
				
				break;
			
			// table element
			case 4:
				$sql =	"SELECT * ".
						"FROM el_table ".
						"WHERE id = $id;";
				
				// table's cell information
				$sql =	"SELECT DISTINCT * ".
						"FROM table_cell ".
						"WHERE id = $id;";
				
				// table's "rowcol" information
				$sql =	"SELECT DISTINCT * ".
						"FROM table_rowcol ".
						"WHERE id = $id;";
				
				break;
			
			// imagemap element
			case 5:
				$sql =	"SELECT * ".
						"FROM el_map ".
						"WHERE id = $id;";
				
				// copy (imagemap) files
				
				// imagemap areas
				$sql =	"SELECT DISTINCT * ".
						"FROM maparea ".
						"WHERE id = $id;";
				
				break;
			
			// multiple choice element
			case 6:
				$sql =	"SELECT * ".
						"FROM el_mc ".
						"WHERE id = $id;";
				
				// answer possibilities for flexible questiontype
				$sql =	"SELECT DISTINCT * ".
						"FROM mc_answer ".
						"WHERE id = $id;";
				
				break;
			
			// multimedia element
			case 7:
				$sql =	"SELECT * ".
						"FROM el_multimedia ".
						"WHERE id = $id;";
				
				// table multimedia is treated separatly
				break;
			
			// filelist
			case 8:
				$sql =	"SELECT * ".
						"FROM el_filelist ".
						"WHERE id = $id;";
				
				// filelist_entry (filelist entries)
				$sql =	"SELECT DISTINCT * ".
						"FROM filelist_entry ".
						"WHERE el_id = $id;";
				
				// table file is treated separatly
				break;
			
			// sourcecode
			case 9:
				$sql =	"SELECT * ".
						"FROM el_sourcecode ".
						"WHERE id = $id;";
				
				break;
			
			// survey
			case 10:
				// el_survey
				break;
			*/
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $element, $text, $attrs);
		
		//----------------------------------------
		// return (Text | LearningObject) subtree: ***
		//----------------------------------------
		if (is_null($LearningObject))
		{
			return $Text;
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
			//-------------------------------------------------------
			// create LearningObject AggregationLevel 2 or 3 subtree:
			//-------------------------------------------------------
			
			case "le": // Lerneinheit
				
				// LearningObject
				$LearningObject = $this->writeNode($parent, "LearningObject");
				
				// LearningObject..MetaData ***
				$MetaData = $this->exportMetadata($id, "pg", $LearningObject);
				
				// LearningObject..Layout ***
				
				// LearningObject..Content ***
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
					$Content = $this->writeNode($LearningObject, "Content");
				}
				// get row(s)
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					// ..Content.. ***
					$Element = $this->exportElement($row["id"], $Content);
				}
				// free result set
				$result->free();
				
				// LearningObject..Test ***
				
				// LearningObject..Glossary ***
				
				// LearningObject..Bibliography ***
				
				break;
			
			/*
			// Glossary ***
			case "gl":
				break;
			
			// Multiple Choice ***
			case "mc":
				break;
			*/
		}
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $page, $attrs);
		
		//-------------------------------
		// return LearningObject subtree:
		//-------------------------------
		return $LearningObject;
	}
	
	// ILIAS2 Lerneinheit --> ILIAS3 LearningObject AggregationLevel 4
	function exportLerneinheit ($id)
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
		// Es fehlt die Schicht der Gliederungspunkte (LO 3) ***
		// Stattdessen werden Pages eingefügt LO 2/3.
		// Muss später angepasst werden.			
		$sql =	"SELECT id ".
				"FROM page ".
				"WHERE lerneinheit = $id ".
				"AND deleted = '0000-00-00 00:00:00';";
		
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
			$Page = $this->exportPage($row["id"], $Content);
		}
		// free result set
		$result->free();
		
		// LearningObject..Test ***
		
		// LearningObject..Glossary ***
		
		// LearningObject..Bibliography ***
		
		//-------------
		// free memory: ***
		//-------------
		unset($sql, $row, $attrs);
		
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
		$xmlStr =	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
					"<!DOCTYPE LearningObject SYSTEM \"http://localhost/ilias3/convert/ilias_lo.dtd\">".
					"<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlStr); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		// create ILIAS3 LearningObject out of ILIAS2 Lerneinheit ***
		$LearningObject = $this->exportLerneinheit($leId);
		
		// dump xml document on the screen ***
		echo "<PRE>";
		echo htmlentities($this->doc->dump_mem(true));
		echo "</PRE>";
		
		// dump xml document into a file ***
		$this->doc->dump_file($path, false, true);
		
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
	
	// LE id, path
	$leId = $_REQUEST["leId"];
	$path = $_REQUEST["path"];
	
	// test run ***
	$exp = new ilias2export($user, $pass, $host, $dbname);
	$exp->outputFile($leId, $path);
}
else
{
	echo "<html>".
			"<head>".
				"<title>ILIAS2export (experimental)</title>".
			"</head>".
			"<body>".
				"Export of ILIAS2 'Lerneinheiten' in ILIAS3 LearningObjects (experimental)<br /><br />".
				"<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\" enctype=\"multipart/form-data\">".
					"ILIAS2 Databaseconnection:<br /><br />".
					"user:<br /><input type=\"text\" name=\"user\" maxlengh=\"30\" size=\"20\" value=\"mysql\"><br />".
					"pass:<br /><input type=\"password\" name=\"pass\" maxlengh=\"30\" size=\"20\" value=\"\"><br />".
					"host:<br /><input type=\"text\" name=\"host\" maxlengh=\"30\" size=\"20\" value=\"localhost\"><br />".
					"dbname:<br /><input type=\"text\" name=\"dbname\" maxlengh=\"30\" size=\"20\" value=\"ilias\"><br /><br />".
					"Id of the 'Lerneinheit' to be exported:<br /><br />".
					"<input type=\"text\" name=\"leId\" maxlengh=\"10\" size=\"10\" value=\"5\"><br /><br />".
					"Full Path and Filename for the generated XML File:<br /><br />".
					"<input type=\"text\" name=\"path\" maxlengh=\"50\" size=\"40\" value=\"/Temp/LO.xml\"><br /><br />".
					"<input type=\"submit\" name=\"ok\" value=\"ok\">".
				"</form>".
			"</body>".
		"</html>";
}

?>