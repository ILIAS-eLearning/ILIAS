<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once ("classes/class.ilMetaTechnical.php");
require_once ("classes/class.ilMetaTechnicalRequirement.php");
require_once ("classes/class.ilMetaTechnicalRequirementSet.php");

/**
* Class ilMetaData
*
* Handles Meta Data of ILIAS Learning Objects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilMetaData
{
	var $ilias;

	// attributes of the "General" Section
	var $import_id;			// +, array
	var $title;				// 1
	var $language;			// string
	var $description;		// string
	var $keyword;			// array
	var $coverage;			// ?, optional
	var $structure;			// "Atomic" | "Collection" | "Networked" | "Hierarchical" | "Linear"
	var $id;
	var $type;
	var $technicals;

	/**
	* Constructor
	* @access	public
	*/
	function ilMetaData($a_type = "", $a_id = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;

		$this->import_id = array();
		$this->title = "";
		$this->language = array();
		$this->description = array();
		$this->keyword = array();
		$this->technicals = array();	// technical sections
		$this->coverage = "";
		$this->structure = "";
		$this->type = $a_type;
		$this->id = $a_id;

		if($a_id != 0)
		{
			$this->read();
		}
	}

	function read()
	{
//echo "<b>".$this->id."</b><br>";
		$query = "SELECT * FROM meta_data ".
			"WHERE obj_id = '".$this->id."' AND obj_type='".$this->type."'";
		$meta_set = $this->ilias->db->query($query);
		$meta_rec = $meta_set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setTitle($meta_rec["title"]);
		$this->setDescription($meta_rec["description"]);
		$this->setLanguage($meta_rec["language"]);
		$this->readKeywords();
		$this->readTechnicalSections();
	}

	/**
	* set identifier catalog value
	* note: only one ID implemented currently
	*/
	function setImportIdentifierCatalog($a_cdata)
	{
		$this->import_id[0]["catalog"] = $a_data;
	}

	/**
	* set identifier entry ID
	* note: only one ID implemented currently
	*/
	function setImportIdentifierEntryID($a_id)
	{
		$this->import_id[0]["entry_id"] = $a_id;
	}

	/**
	* get identifier catalog value
	* note: only one ID implemented currently
	*/
	function getImportIdentifierCatalog()
	{
		return $this->import_id[0]["catalog"];
	}

	/**
	* get identifier entry ID
	* note: only one ID implemented currently
	*/
	function getImportIdentifierEntryID()
	{
		return $this->import_id[0]["entry_id"];
	}

	/**
	* set title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* get title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* set id
	*/
	function setID($a_id)
	{
		$this->id = $a_id;
	}

	function getID()
	{
		return $this->id;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getType()
	{
		return $this->type;
	}

	// GENERAL: Language
	function setLanguage($a_lang)
	{
		$this->language = $a_lang;
	}

	function getLanguage()
	{
		return $this->language;
	}

	// GENERAL: Description
	function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}

	function getDescription()
	{
		return $this->description;
	}

	// GENERAL: Keyword
	function addKeyword($a_lang, $a_key)
	{
		$this->keyword[] = array("language" => $a_lang, "keyword" => $a_key);
	}

	function getKeywords()
	{
		return $this->keyword;
	}

	/**
	* Technical section
	*
	* @param	array	$a_tech		object (of class ilMetaTechnical)
	*/
	function addTechnicalSection(&$a_tech)
	{
//echo "1) ilMetaData::addingTechnicalSection<br>";
//echo "type:".$this->getType().":id:".$this->getId().":<br>";
		$this->technicals[] =& $a_tech;
//echo "count TechnicalSections:".count($this->technicals).":<br>";
	}

	function &getTechnicalSections()
	{
		return $this->technicals;
	}

	function updateTechnicalSections()
	{
//echo "count TechnicalSections:".count($this->technicals).":<br>";
//echo "type:".$this->getType().":id:".$this->getId().":<br>";
		ilMetaTechnical::delete($this->getId(), $this->getType());
		foreach($this->technicals as $technical)
		{
//echo "technicalcreate<br>";
			$technical->create();
		}
	}

	function readTechnicalSections()
	{
		ilMetaTechnical::readTechnicalSections($this);
	}

	/**
	* get technical section number $a_nr (starting with 1!)
	*/
	function &getTechnicalSection($a_nr)
	{
//echo "counttech:".count($this->technicals).":<br>";
		if ($a_nr < count($this->technicals))
		{
			return false;
		}
		else
		{
			return $this->technicals[$a_nr - 1];
		}
	}


	/**
	* create meta data object in db
	*/
	function create()
	{
		$query = "INSERT INTO meta_data (obj_id, obj_type, title,".
			"language, description) VALUES ".
			"('".$this->getId()."','".$this->getType()."','".$this->getTitle()."',".
			"'".$this->getLanguage()."','".$this->getDescription()."')";
		$this->ilias->db->query($query);
		$this->updateKeywords();
		$this->updateTechnicalSections();
	}


	/**
	* update everything
	*/
	function update()
	{
		$query = "REPLACE INTO meta_data (obj_id, obj_type, title,".
			"language, description) VALUES ".
			"('".$this->getId()."','".$this->getType()."','".$this->getTitle()."',".
			"'".$this->getLanguage()."','".$this->getDescription."')";
		$this->ilias->db->query($query);
		$this->updateKeywords();
		$this->updateTechnicalSections();
	}


	/**
	* update / create keywords from object into db
	*/
	function updateKeywords()
	{
		$query = "DELETE FROM meta_keyword ".
			"WHERE obj_id ='".$this->getId()."' ".
			"AND obj_type = '".$this->getType()."'";
		$this->ilias->db->query($query);
		reset($this->keyword);
		foreach ($this->keyword as $keyword)
		{
			$query = "INSERT INTO meta_keyword (obj_id, obj_type, language, keyword) ".
				"VALUES ('".$this->getId()."','".$this->getType().
				"','".$keyword["language"]."','".$keyword["keyword"]."')";
			$this->ilias->db->query($query);
		}
	}

	/**
	* read keywords form db into object
	*/
	function readKeywords()
	{
		$query = "SELECT * FROM meta_keyword ".
			"WHERE obj_id = '".$this->id."' AND obj_type='".$this->type."'";
		$keyword_set = $this->ilias->db->query($query);
		while ($key_rec = $keyword_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->addKeyword($key_rec["language"], $key_rec["keyword"]);
		}
	}

	function delete()
	{
		$query = "DELETE FROM meta_data  WHERE obj_id = '".$this->getId()
			."' AND obj_type = '".$this->getType()."'";
		$this->ilias->db->query($query);

		$query = "DELETE FROM meta_keyword  WHERE obj_id = '".$this->getId()
			."' AND obj_type = '".$this->getType()."'";
		$this->ilias->db->query($query);
	}

	function getCountries()
	{
		global $lng;

		$lng->loadLanguageModule("meta");

		$cntcodes = array ("DE","ES","FR","GB","AT","CH","AF","AL","DZ","AS","AD","AO",
			"AI","AQ","AG","AR","AM","AW","AU","AT","AZ","BS","BH","BD","BB","BY",
			"BE","BZ","BJ","BM","BT","BO","BA","BW","BV","BR","IO","BN","BG","BF",
			"BI","KH","CM","CA","CV","KY","CF","TD","CL","CN","CX","CC","CO","KM",
			"CG","CK","CR","CI","HR","CU","CY","CZ","DK","DJ","DM","DO","TP","EC",
			"EG","SV","GQ","ER","EE","ET","FK","FO","FJ","FI","FR","FX","GF","PF",
			"TF","GA","GM","GE","DE","GH","GI","GR","GL","GD","GP","GU","GT","GN",
			"GW","GY","HT","HM","HN","HU","IS","IN","ID","IR","IQ","IE","IL","IT",
			"JM","JP","JO","KZ","KE","KI","KP","KR","KW","KG","LA","LV","LB","LS",
			"LR","LY","LI","LT","LU","MO","MK","MG","MW","MY","MV","ML","MT","MH",
			"MQ","MR","MU","YT","MX","FM","MD","MC","MN","MS","MA","MZ","MM","NA",
			"NR","NP","NL","AN","NC","NZ","NI","NE","NG","NU","NF","MP","NO","OM",
			"PK","PW","PA","PG","PY","PE","PH","PN","PL","PT","PR","QA","RE","RO",
			"RU","RW","KN","LC","VC","WS","SM","ST","SA","CH","SN","SC","SL","SG",
			"SK","SI","SB","SO","ZA","GS","ES","LK","SH","PM","SD","SR","SJ","SZ",
			"SE","SY","TW","TJ","TZ","TH","TG","TK","TO","TT","TN","TR","TM","TC",
			"TV","UG","UA","AE","GB","UY","US","UM","UZ","VU","VA","VE","VN","VG",
			"VI","WF","EH","YE","ZR","ZM","ZW");
		$cntrs = array();
		foreach($cntcodes as $cntcode)
		{
			$cntrs[$cntcode] = $lng->txt("meta_c_".$cntcode);
		}
		asort($cntrs);
		return $cntrs;

	}

	/**
	* get iso conform languages
	* see http://www.oasis-open.org/cover/iso639a.html
	*/
	function getLanguages()
	{
		global $lng;

		$lng->loadLanguageModule("meta");

		$lngcodes = array("aa","ab","af","am","ar","as","ay","az","ba","be","bg","bh",
			"bi","bn","bo","br","ca","co","cs","cy","da","de","dz","el","en","eo",
			"es","et","eu","fa","fi","fj","fo","fr","fy","ga","gd","gl","gn","gu",
			"ha","he","hi","hr","hu","hy","ia","ie","ik","id","is","it","iu","ja",
			"jv","ka","kk","kl","km","kn","ko","ks","ku","ky","la","ln","ru","rw",
			"sa","sd","sg","sh","si","sk","sl","sm","sn","so","sq","sr","ss","st",
			"su","sv","sw","ta","te","tg","th","ti","tk","tl","tn","to","tr","ts",
			"tt","tw","ug","uk","ur","uz","vi","vo","wo","xh","yi","yo","za","zh",
			"zu");
		$langs = array();
		foreach($lngcodes as $lngcode)
		{
			$langs[$lngcode] = $lng->txt("meta_l_".$lngcode);
		}
		asort($langs);
		return $langs;
	}

}
?>
