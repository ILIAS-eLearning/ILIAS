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

	var $id;
	var $type;
	var $technicals;

	var $nested;

	var $import_id;			// +, array

	var $meta;
	var $section;

	var $obj;

	// attributes of the "General" Section
	var $identifier;		// +, array
	var $title;				// 1, array
	var $language;			// +, array
	var $description;		// +, array
	var $keyword;			// +, array
	var $coverage;			// ?, array
	var $structure;			// "Atomic" | "Collection" | "Networked" | "Hierarchical" | "Linear"

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
		$this->description = "";
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

	function setObject(&$a_obj)
	{
		$this->obj =& $a_obj;
	}

	function read()
	{
		/* Get meta data from nested set */
		if ($this->getType() == "pg" ||
			$this->getType() == "st" ||
			$this->getType() == "lm" ||
			$this->getType() == "glo" ||
			$this->getType() == "gdf" ||
			$this->getType() == "dbk") 
		{
			include_once("./classes/class.ilNestedSetXML.php");
			$this->nested = new ilNestedSetXML();
#			echo "ID: " . $this->getID() . ", Type: " . $this->getType() . "<br>";
			$this->nested->init($this->id, $this->getType());
			if ( !$this->nested->initDom() )
			{
				/* No meta data found --> Create default meta data dataset */
				$xml = '
					<MetaData>
						<General Structure="Hierarchical">
							<Identifier Catalog="ILIAS" Entry="' . substr(md5(uniqid(rand())), 0, 6) . '"></Identifier>
							<Title Language="' . $this->ilias->account->getLanguage() . '"></Title>
							<Description Language="' . $this->ilias->account->getLanguage() . '"></Description>
							<Keyword Language="' . $this->ilias->account->getLanguage() . '"></Keyword>
						</General>
					</MetaData>
				';
				/* To do: Add default meta data for other sections like "Lifecycle", "Technical"... */
				$this->nested->import($xml, $this->id, $this->getType());
			}

			if ( $this->nested->initDom() ) {
				$meta_rec["title"] = $this->nested->getFirstDomContent("//MetaData/General/Title");
				$meta_rec["description"] = $this->nested->getFirstDomContent("//MetaData/General/Description");
			}

		} 
		
		$this->setTitle($meta_rec["title"]);
		$this->setDescription($meta_rec["description"]);
	}

	/**
	* set identifier catalog value
	* note: only one value implemented currently
	*/
	function setElement($a_name, $a_data)
	{
		$this->$a_name = $a_data;
	}

	/**
	* get identifier catalog value
	* note: only one value implemented currently
	*/
	function getElement($a_name, $a_path = "")
	{
		$p = "//MetaData/";
		if ($a_path != "")
		{
			$p .= $a_path . "/";
		}
		$p .= $a_name;
		$this->setElement($a_name, $this->nested->getDomContent($p));
		return $this->$a_name;
	}

	/**
	* delete meta data node
	*/
	function delete($a_name, $a_path, $a_index)
	{
		if ($a_name != "")
		{
			$p = "//MetaData/";
			if ($a_path != "")
			{
				$p .= $a_path . "/";
			}
			$p .= $a_name;
			$this->nested->deleteDomNode($p, $a_index);
			$this->nested->updateFromDom();
		}
	}

	/**
	* add meta data node
	*/
	function add($a_name, $a_path)
	{
		$p = "//MetaData";
		if ($a_path != "")
		{
			$p .= "/" . $a_path;
		}
		$attributes = array();
		switch ($a_name)
		{
			case "Relation"		:	$xml = '
										<Relation>
											<Resource>
												<Identifier_ Language="' . $this->ilias->account->getLanguage() . '"/>
												<Description Language="' . $this->ilias->account->getLanguage() . '"/>
											</Resource>
										</Relation>
									';
									$this->nested->addXMLNode($p, $xml);
									break;
			case "Identifier"	:	;
			case "Identifier_"	:	$value = "";
									$attributes[0] = array("name" => "Catalog", "value" => "");
									$attributes[1] = array("name" => "Entry", "value" => "");
									$this->nested->addDomNode($p, $a_name, $value, $attributes);
									break;
			case "Language"		:	$value = $this->ilias->account->getLanguage();
									$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
									$this->nested->addDomNode($p, $a_name, $value, $attributes);
									break;
			case "Title"		:	;
			case "Description"	:	;
			case "Keyword"		:	$value = "";
									$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
									$this->nested->addDomNode($p, $a_name, $value, $attributes);
									break;
		}
		$this->nested->updateFromDom();
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
		if ($a_title == "")
		{
			$a_title = "NO TITLE";
		}

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
	* set (posted) meta data
	*/
	function setMeta($a_data)
	{
		$this->meta = $a_data;
	}

	/**
	* get meta data
	*/
	function getMeta()
	{
		return $this->meta;
	}

	/**
	* set chosen meta data section
	*/
	function setSection($a_section)
	{
		$this->section = $a_section;
	}

	/**
	* get chosen meta data section
	*/
	function getSection()
	{
		return $this->section;
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
//echo "<b>reading tech</b><br>";
		ilMetaTechnical::readTechnicalSections($this);
	}

	/**
	* get technical section number $a_nr (starting with 1!)
	*/
	function &getTechnicalSection($a_nr = 1)
	{
//echo "counttech:".count($this->technicals).":<br>";
		if ($a_nr > count($this->technicals))
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
		include_once("./classes/class.ilNestedSetXML.php");
		$this->nested = new ilNestedSetXML();
		$this->nested->init($this->id, $this->getType());
		if ( !$this->nested->initDom() )
		{
			$xml = '
				<MetaData>
					<General Structure="Hierarchical">
						<Identifier Catalog="ILIAS" Entry="' . substr(md5(uniqid(rand())), 0, 6) . '"></Identifier>
						<Title Language="' . $this->ilias->account->getLanguage() . '">' . $this->obj->getTitle() . '</Title>
						<Description Language="' . $this->ilias->account->getLanguage() . '">' . $this->obj->getDescription() . '</Description>
						<Keyword Language="' . $this->ilias->account->getLanguage() . '"></Keyword>
					</General>
				</MetaData>
			';
			$this->nested->import($xml, $this->getID(), $this->getType());
		}
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
		
		if ($this->getType() == "pg" || $this->getType() == "st" || $this->getType() == "lm"
			|| $this->getType() == "glo" || $this->getType() == "gdf" || $this->getType() == "dbk")
		{
#			echo "Section: " . $this->section . "<br>\n";
			$p = "//MetaData";
			if ($this->section != "")
				$p .= "/" . $this->section;
			$this->nested->updateDomNode($p, $this->meta);
			$this->nested->updateFromDom();
			if ($this->getType() == "lm" ||
				$this->getType() == "glo" ||
				$this->getType() == "dbk")
			{
				$this->setTitle($this->meta["title_value"]);
				$this->setDescription($this->meta["description_value"][0]);
			}
			if ($this->getType() == "lm" ||
					 $this->getType() == "dbk" ||
					 $this->getType() == "glo" ||
					 $this->getType() == "st" ||
					 $this->getType() == "pg")
			{
				$query = "UPDATE lm_data SET title = '".$this->meta["title_value"]."' WHERE ".
						 "obj_id = '" . $this->getID() . "'";
#				echo $query;
				$this->ilias->db->query($query);
			}
		}
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

/*	function delete()
	{
		$query = "DELETE FROM meta_data  WHERE obj_id = '".$this->getId()
			."' AND obj_type = '".$this->getType()."'";
		$this->ilias->db->query($query);

		$query = "DELETE FROM meta_keyword  WHERE obj_id = '".$this->getId()
			."' AND obj_type = '".$this->getType()."'";
		$this->ilias->db->query($query);
	}*/

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

	/**
	* set xml content of MetaData, start with <MetaData...>,
	* end with </MetaDatat>, comply with ILIAS DTD, use utf-8!
	*
	* @param	string		$a_xml			xml content
	* @param	string		$a_encoding		encoding of the content (here is no conversion done!
	*										it must be already utf-8 encoded at the time)
	*/
	function setXMLContent($a_xml, $a_encoding = "UTF-8")
	{
		$this->encoding = $a_encoding;
		$this->xml = $a_xml;
	}


	/**
	* append xml content to MetaData
	* setXMLContent must be called before and the same encoding must be used
	*
	* @param	string		$a_xml			xml content
	*/
	function appendXMLContent($a_xml)
	{
		$this->xml.= $a_xml;
	}


	/**
	* get xml content of MetaData
	*/
	function getXMLContent()/*$a_incl_head = false*/
	{
        return $this->xml;
	}
    
}
?>
