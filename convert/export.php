<?php

/**
* Export of content from ILIAS2 to ILIAS3 using DOMXML
*
* Dependencies:
* 
* @author	Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version	$Id$
*/

// *** = dirty/buggy --> to be modified/extended

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";

// connection data
$user = "mysql";
$pass = "lqsym";
$host = "localhost";
$dbname = "ilias";

// build dsn of database connection and connect
$dsn = "mysql://$user:$pass@$host/$dbname";
$db = DB::connect($dsn, true);

// test for valid connection
if (DB::isError($db))
{
	die ($db->getMessage());
}

// test ***
$exp = new export;
$exp->output();

// quit connection
$db->disconnect();

class export 
{
	//-----------
	// properties
	//-----------
	
	/**
	 * domxml document object ***
	 * 
	 * @var object doc
	 * @access public 
	 */
	var $doc;
	
	//-------
	//methods
	//-------
	
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
	function metadata ($id, $type)
	{
		// ***
		global $db;
		
		//-------------------------
		// get data from db tables:
		//-------------------------
		
		// table 'meta'
		$sql =	"SELECT id, inst, typ, title, lang, description, diff, level, status, ".
				"material_level, last_modified_date, publisher, publish_date ".
				"FROM meta ".
				"WHERE id = $id ".
				"AND typ = '$type';";
		
		$result = $db->query($sql);		
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
		
		$result = $db->query($sql);		
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
		
		$result = $db->query($sql);		
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
				
				$result2 = $db->query($sql2);		
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
		
		$result = $db->query($sql);		
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
				
				$result2 = $db->query($sql2);		
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
		
		$result = $db->query($sql);		
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
		
		$result = $db->query($sql);		
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
		
		$result = $db->query($sql);		
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
		
		// MetaData *** ggf. ein vereinfachtes Konstrukt
		$MetaData = $this->doc->create_element("MetaData");
		
		// 1 MetaData..General
		$attrs = array(	"Structure" => $this->selectStructure($meta["typ"]),
						"AggregationLevel" => $this->selectAggregationLevel($meta["typ"]));
		$General = $this->writeNode($MetaData, "General", $attrs);
		
		// 1.1 ..General..Identifier
		$attrs = array(	"Catalog" => "ILIAS2 ".$meta["inst"],
						"Entry" => $meta["id"]);
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
		
		//-----------------
		// free db records:
		//-----------------
		unset($sql, $sql2, $row, $row2, $meta, $keyword, $contrib, $mtype, $attrs);
		
		//----------------------------------------
		// return MetaData subtree:
		// (must be appended to become persistent)
		//----------------------------------------
		return $MetaData;
	}
	
	// ILIAS2 Element --> ILIAS3 LearningObject Level 1 or 2 (depends on type)
	function element ($id, $del = "")
	{
		// ***
		global $db;
		
		// element's metadata *** erst am Ende ???
		
		// set deleted value for the sql query
		if ($del)
		{	
			$sqlDel = "";
		}
		else 
		{
			$sqlDel = "AND deleted = '0000-00-00 00:00:00'";
		}
		
		// get data from table 'element'
		$sql =	"SELECT * ".
				"FROM element ".
				"WHERE id = $id $sqlDel;";
		
		// select the element's type ***
		switch($el["typ"]) 
		{
			// text element
			case 1:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_text ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// image element (bild)
			case 2:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_bild ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// copy (image) files
				break;
			
			// title element
			case 3:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_titel ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// table element
			case 4:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_table ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// table's cell information
				$sql =	"SELECT DISTINCT * ".
						"FROM table_cell ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// table's "rowcol" information
				$sql =	"SELECT DISTINCT * ".
						"FROM table_rowcol ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// imagemap element
			case 5:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_map ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// copy (imagemap) files
				
				// imagemap areas
				$sql =	"SELECT DISTINCT * ".
						"FROM maparea ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// multiple choice element
			case 6:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_mc ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// answer possibilities for flexible questiontype
				$sql =	"SELECT DISTINCT * ".
						"FROM mc_answer ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// multimedia element
			case 7:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_multimedia ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// table multimedia is treated separatly
				break;
			
			// filelist
			case 8:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_filelist ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				// filelist_entry (filelist entries)
				$sql =	"SELECT DISTINCT * ".
						"FROM filelist_entry ".
						"WHERE el_id = '$id' ".
						"AND el_inst = '$inst';";
				
				// table file is treated separatly
				break;
			
			// sourcecode
			case 9:
				$sql =	"SELECT DISTINCT * ".
						"FROM el_sourcecode ".
						"WHERE id = '$id' ".
						"AND inst = '$inst';";
				
				break;
			
			// survey
			case 10:
				// el_survey
				break;
		}
	}
	
	// ILIAS2 Lerneinheit --> ILIAS3 LearningObject Level 4
	function lerneinheit ($id, $del = "")
	{
		// ***
		global $db;
		
		// set deleted value for the sql query
		if ($del)
		{	
			$sqlDel = "";
		}
		else 
		{
			$sqlDel = "AND deleted = '0000-00-00 00:00:00'";
		}
		
		// get data from table 'lerneinheit'
		$sql =	"SELECT * ".
				"FROM lerneinheit ".
				"WHERE id = $id $sqlDel;";
	}
	
	// create xml output
	function output ()
	{
		//-------------------------
		// create new xml document:
		//-------------------------
		
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlStr =	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
					"<!DOCTYPE LearningObject SYSTEM \"http://127.0.0.1/ilias_lo_.dtd\">".
					"<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlStr); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		// create Learning Object AggregationLevel 4 (root element) *** nur Test
		$LearningObject = $this->doc->create_element("LearningObject");
		$LearningObject = $this->doc->append_child($LearningObject);
		
		// create MetaData ***
		$MetaData = $this->metadata(200, "le");
		$MetaData = $LearningObject->append_child($MetaData);
		
		// dump xml document on the screen ***
		echo "<PRE>";
		echo htmlentities($this->doc->dump_mem(true));
		echo "</PRE>";
	}
}

?>
