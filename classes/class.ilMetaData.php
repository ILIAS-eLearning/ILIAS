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
require_once ("classes/class.ilNestedSetXML.php");

/**
* Class ilMetaData
*
* Handles Meta Data of ILIAS Learning Objects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Databay AG <jc@databay.de>
* @version $Id$
*
* @package application
*/
class ilMetaData
{
	var $ilias;

	var $id;
	var $type;

	var $nested_obj;

	var $meta;
	var $section;

	var $obj;

	var $import_id;			// ?
	var $title;				// 1
	var $language;			// ?, array
	var $description;		// ?
	var $technicals;		// ?, array

	/**
	* Constructor
	* @access	public
	*/
	function ilMetaData($a_type = "", $a_id = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;

		$this->import_id = "";
		$this->title = "";
		$this->language = array();
		$this->description = "";
		$this->technicals = array();	// technical sections
		$this->type = $a_type;
		$this->id = $a_id;
		if($a_id != 0)
		{
			$this->read();
		}
	}
	
	function clean($a_data)
	{
		$a_data = preg_replace("/&(?!amp;|lt;|gt;|quot;)/","&amp;",$a_data);
		$a_data = preg_replace("/</","lt&;",$a_data);
		$a_data = preg_replace("/>/","gt&;",$a_data);

		return $a_data;
	}

	function setObject(&$a_obj)
	{
		$this->obj =& $a_obj;
	}

	function __initNestedSet()
	{
		include_once("classes/class.ilNestedSetXML.php");

		$this->nested_obj =& new ilNestedSetXML();
		$this->nested_obj->init($this->getID(), $this->getType());
		return $this->nested_obj->initDom();
	}

	function read()
	{
		/* Get meta data from nested set */
		if ($this->getType() == "pg" ||
			$this->getType() == "st" ||
			$this->getType() == "lm" ||
			$this->getType() == "glo" ||
			$this->getType() == "mob" ||
			$this->getType() == "crs" ||
			$this->getType() == "sahs" ||
			$this->getType() == "htlm" ||
			$this->getType() == "tst" ||
			$this->getType() == "qpl" ||
			$this->getType() == "svy" ||
			$this->getType() == "spl" ||
			$this->getType() == "gdf" ||
			$this->getType() == "dbk")
		{
			if ( !$this->__initNestedSet() )
			{
				$metaData = $this->create();
			}
			else
			{
				$metaData["title"] = $this->nested_obj->getFirstDomContent("//MetaData/General/Title");
				$metaData["description"] = $this->nested_obj->getFirstDomContent("//MetaData/General/Description");
			}

			$this->setTitle($metaData["title"]);
			$this->setDescription($metaData["description"]);
		}

	}

	/**
	* create meta data object in db
	*/
	function create()
	{
#echo "<b>meta create()</b><br>";
		$this->__initNestedSet();
		if (is_object($this->obj))
		{
			$metaData["title"] = $this->obj->getTitle();
			$metaData["description"] = $this->obj->getDescription();
		}
		else
		{
			$metaData["title"] = "NO TITLE";
			$metaData["description"] = "";
		}

		// SUBSTITUTE '&' => '&amp;'
		$metaData["title"] = $this->clean($metaData["title"]);
		$metaData["description"] = $this->clean($metaData["description"]);

		$xml = '
			<MetaData>
				<General Structure="Hierarchical">
					<Identifier Catalog="ILIAS" Entry="il__' . $this->getType() . '_' . $this->getId() . '"></Identifier>
					<Title Language="' . $this->ilias->account->getLanguage() . '">' . $metaData["title"] . '</Title>
					<Description Language="' . $this->ilias->account->getLanguage() . '">' . $metaData["description"] . '</Description>
					<Keyword Language="' . $this->ilias->account->getLanguage() . '"></Keyword>
				</General>
			</MetaData>
		';
		$this->nested_obj->import($xml, $this->getID(), $this->getType());
		return $metaData;
	}

	/**
	* update everything
	*/
	function update()
	{
#echo "<b>meta update()</b><br>";
		$query = "REPLACE INTO meta_data (obj_id, obj_type, title, language, description) VALUES (".
				 "'".$this->getId()."', ".
				 "'".$this->getType()."', ".
				 "'".ilUtil::prepareDBString($this->getTitle())."', ".
				 "'".$this->getLanguage()."', ".
				 "'".ilUtil::prepareDBString($this->getDescription())."')";
		$this->ilias->db->query($query);
		$this->updateTechnicalSections();

		if ($this->getType() == "pg" ||
			$this->getType() == "st" ||
			$this->getType() == "lm" ||
			$this->getType() == "crs" ||
			$this->getType() == "glo" ||
			$this->getType() == "gdf" ||
			$this->getType() == "dbk" ||
			$this->getType() == "tst" ||
			$this->getType() == "qpl" ||
			$this->getType() == "svy" ||
			$this->getType() == "spl" ||
			$this->getType() == "mob" ||
			$this->getType() == "htlm" ||
			$this->getType() == "sahs")
		{
#			echo "Section: " . $this->section . "<br>\n";
			if ( $this->__initNestedSet() )
			{
				$p = "//MetaData";
				if ($this->section != "")
				{
					$p .= "/" . $this->section;
				}

				$this->nested_obj->updateDomNode($p, $this->meta);
				$this->nested_obj->updateFromDom();

				/* editing meta data with editor: new title */
				if (isset($this->meta["Title"]["Value"]))
				{
					$this->setTitle(ilUtil::stripSlashes($this->meta["Title"]["Value"]));
					$this->setDescription(ilUtil::stripSlashes($this->meta["Description"][0]["Value"]));
				}
			}
		}
	}

	function updateTitleAndDescription($title, $description)
	{
		if ( $this->__initNestedSet() )
		{
			$p = "//MetaData/General";
			if ($this->section != "")
			{
				$p .= "/" . $this->section;
			}
			$this->nested_obj->replaceDomContent($p, "Title", 0, array("value" => $title));
			$this->nested_obj->replaceDomContent($p, "Description", 0, array("value" => $description));
			$this->nested_obj->updateFromDom();
		}
		$this->setTitle($title);
		$this->setDescription($description);
		$this->update();
	}
	
	/**
	* delete meta data node
	*/
	function delete($a_name, $a_path, $a_index)
	{
		if ($a_name != "")
		{
			if ( $this->__initNestedSet() )
			{
				$p = "//MetaData";
				if ($a_path != "")
				{
					$p .= "/" . $a_path;
				}
				$this->nested_obj->deleteDomNode($p, $a_name, $a_index);
				$this->nested_obj->updateFromDom();
			}
		}
	}

	/**
	* add meta data node
	*/
	function add($a_name, $a_path, $a_index = 0)
	{
		if ( $this->__initNestedSet() )
		{
			$p = "//MetaData";
			if ($a_path != "")
			{
				$p .= "/" . $a_path;
			}
			$attributes = array();
#		echo "Index: " . $a_index . " | Path: " . $a_path . " | Name: " . $a_name . "<br>\n";
			switch ($a_name)
			{
				case "Relation"		:	$xml = '
											<Relation>
												<Resource>
													<Identifier_ Catalog="ILIAS" Entry=""/>
													<Description Language="' . $this->ilias->account->getLanguage() . '"/>
												</Resource>
											</Relation>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Lifecycle":	$xml = '
											<Lifecycle Status="Draft">
												<Version Language="' . $this->ilias->account->getLanguage() . '"></Version>
												<Contribute Role="Author">
													<Entity/>
													<Data/>
												</Contribute>
											</Lifecycle>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Meta-Metadata":	$xml = '
											<Meta-Metadata MetadataScheme="LOM v 1.0" Language="' . $this->ilias->account->getLanguage() . '">
												<Identifier Catalog="ILIAS" Entry=""/>
												<Contribute Role="Author">
													<Entity/>
													<Data/>
												</Contribute>
											</Meta-Metadata>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Technical"	:	$xml = '
											<Technical/>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Educational"	:	$xml = '
											<Educational InteractivityType="Active" LearningResourceType="Exercise" InteractivityLevel="Medium" SemanticDensity="Medium" IntendedEndUserRole="Learner" Context="Other" Difficulty="Medium">
												<TypicalAgeRange></TypicalAgeRange>
												<TypicalLearningTime></TypicalLearningTime>
											</Educational>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Rights"		:	$xml = '
											<Rights Cost="No" CopyrightAndOtherRestrictions="No">
												<Description Language="' . $this->ilias->account->getLanguage() . '"/>
											</Rights>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Annotation"	:	$xml = '
											<Annotation>
												<Entity/>
												<Date/>
												<Description Language="' . $this->ilias->account->getLanguage() . '"/>
											</Annotation>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Classification":	$xml = '
											<Classification Purpose="Idea">
												<TaxonPath>
													<Source Language="' . $this->ilias->account->getLanguage() . '"/>
													<Taxon Language="' . $this->ilias->account->getLanguage() . '" Id=""/>
												</TaxonPath>
												<Description Language="' . $this->ilias->account->getLanguage() . '"/>
												<Keyword Language="' . $this->ilias->account->getLanguage() . '"/>
											</Classification>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Contribute"	:	$xml = '
											<Contribute Role="Author">
												<Entity/>
												<Data/>
											</Contribute>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Requirement"	:	$xml = '
											<Requirement>
												<Type>
													<Browser Name="Any" MinimumVersion="" MaximumVersion=""/>
												</Type>
											</Requirement>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "OrComposite"	:	$xml = '
											<OrComposite>
												<Requirement>
													<Type>
														<Browser Name="Any" MinimumVersion="" MaximumVersion=""/>
													</Type>
												</Requirement>
											</OrComposite>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "TaxonPath"	:	$xml = '
											<TaxonPath>
												<Source Language="' . $this->ilias->account->getLanguage() . '"/>
												<Taxon Language="' . $this->ilias->account->getLanguage() . '" Id=""/>
											</TaxonPath>
										';
										$this->nested_obj->addXMLNode($p, $xml, $a_index);
										break;
				case "Taxon"		:	$value = "";
										$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
										$attributes[1] = array("name" => "Id", value => "");
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
				case "Identifier"	:	;
				case "Identifier_"	:	$value = "";
										$attributes[0] = array("name" => "Catalog", "value" => "");
										$attributes[1] = array("name" => "Entry", "value" => "");
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
				case "Language"		:	$value = "";
										$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
				case "InstallationRemarks"	:	;
				case "OtherPlattformRequirements"	:	;
				case "TypicalAgeRange"	:	;
				case "Title"		:	;
				case "Description"	:	;
				case "Coverage"		:	;
				case "Keyword"		:	$value = "";
										$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
				case "Location"		:	$value = "";
										$attributes[0] = array("name" => "Type", value => "LocalFile");
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
				default				:	$value = "";
										$attributes = "";
										$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
										break;
			}
			$this->nested_obj->updateFromDom();
		}
	}

	function getDom()
	{
		if ( $this->__initNestedSet() )
		{
			return $this->nested_obj->initDom();
		}
	}

	/**
	* buffer value of one element
	*/
	function setElement($a_name, $a_data)
	{
		$this->$a_name = $a_data;
	}

	/**
	* get value of one element of the ILIAS meta data structure
	*/
	function getElement($a_name, $a_path = "", $a_index = 0)
	{
		if ( $this->__initNestedSet() )
		{
			$p = "//MetaData";
			if ($a_path != "")
			{
				$p .= "/" . $a_path;
			}
#		echo "Index: " . $a_index . " | Path: " . $p . " | Name: " . $a_name . "<br>\n";
			$nodes = $this->nested_obj->getDomContent($p, $a_name, $a_index);
			$this->setElement($a_name, $nodes);
#		vd($this->$a_name);
			return $this->$a_name;
		}
	}

	/**
	* set identifier entry ID
	*/
	function setImportIdentifierEntryID($a_id)
	{
		$this->import_id = $a_id;
	}

	/**
	* get identifier entry ID
	*/
	function getImportIdentifierEntryID()
	{
		return $this->import_id;
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
	* set object id
	*/
	function setID($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* get object id
	*/
	function getID()
	{
		return $this->id;
	}

	/**
	* set object type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* get object type
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* set language
	*/
	function setLanguage($a_lang)
	{
		$this->language = $a_lang;
	}

	/**
	* get language
	*/
	function getLanguage()
	{
		return $this->language;
	}

	/**
	* set description
	*/
	function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}

	/**
	* get description
	*/
	function getDescription()
	{
		return $this->description;
	}

	/**
	* Technical section
	*
	* @param	array	$a_tech		object (of class ilMetaTechnical)
	*/
	function addTechnicalSection(&$a_tech)
	{
		$this->technicals[] =& $a_tech;
	}

	function &getTechnicalSections()
	{
		return $this->technicals;
	}

	function updateTechnicalSections()
	{
		ilMetaTechnical::delete($this->getId(), $this->getType());
		foreach($this->technicals as $technical)
		{
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
	function &getTechnicalSection($a_nr = 1)
	{
		if ($a_nr > count($this->technicals))
		{
			return false;
		}
		else
		{
			return $this->technicals[$a_nr - 1];
		}
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
			"jv","ka","kk","kl","km","kn","ko","ks","ku","ky","la","ln",
			"lo","lt","lv","mg","mi","mk","ml","mn","mo","mr","ms","mt",
			"my","na","ne","nl","no","oc","om","or","pa","pl","ps","pt",
			"qu","rm","rn","ro",
			"ru","rw",
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
	* end with </MetaData>, comply with ILIAS DTD, use utf-8!
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
