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

require_once ("classes/class.ilMetaData.php");

/**
* Class ilBibItem
*
* Handles Bib-Items of ILIAS DigiLib-Books (see ILIAS DTD)
*
* @author Databay AG <ay@databay.de>
* @version $Id$
*
* @package application
*/
class ilBibItem
{
	var $nested_obj;
	var $content_obj;
	var $xml;

	var $bibliography_attr;
	var $abstract;

	var $id = 0;
	var $type = "bib";
	
	var $meta;

	/**
	* Constructor
	* @access	public
	*/
	function ilBibItem($content_obj = 0)
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

		$this->content_obj =& $content_obj;
		if(is_object($content_obj))
		{
			$this->setID($this->content_obj->getId());
			$this->readXML();
#			$this->read();
		}
	}

	// SET METHODS
	/**
	* @param array e.g array("version" => 1,...)
	* @see ilias_co.dtd
	* @access	public
	*/
	function setBibliographyAttributes($a_data)
	{
		$this->bibliography_attr = $a_data;
	}

	function setAbstract($a_data)
	{
		$this->abstract = $a_data;
	}

	function setBibItemData($a_key,$a_value,$a_bib_item_nr)
	{
		$this->bib_item_data[$a_bib_item_nr]["$a_key"] = $a_value;
		
		return true;
	}
	function appendBibItemData($a_key,$a_value,$a_bib_item_nr)
	{
		$this->bib_item_data[$a_bib_item_nr]["$a_key"] = array_merge($this->bib_item_data[$a_bib_item_nr]["$a_key"],array($a_value));
	}

	// GET MEHODS
	function getBibItemData()
	{
		return $this->bib_item_data;
	}

	/**
	* @return array e.g array("version" => 1,...)
	* @see ilias_co.dtd
	* @access	public
	*/
	function getBibliographyAttributes()
	{
		return $this->bibliography_attr ? $this->bibliography_attr : array();
	}
	/**
	* @return string
	* @see ilias_co.dtd
	* @access	public
	*/
	function getAbstract()
	{
		return $this->abstract;
	}

	function getTitle()
	{
		return $this->title;
	}

	function getXML()
	{
		return $this->xml;
	}


	function readXML()
	{
		if(!$this->__initNestedSet())
		{
			return false;
		}
		$this->xml = $this->nested_obj->export($this->content_obj->getId(),"bib");
	}


#	function read()
#	{
#		if(!$this->__initNestedSet())
#		{
#			return false;
#		}
#		return;
		/*
		$this->dom = domxml_open_mem($this->nested_obj->export($this->content_obj->getId(),"bib"));
		
		// PARSE BIBLIOGRAPHY TAG
		$root = $this->dom->document_element();
		
		// Bibliography attributes
		foreach($root->attributes() as $key => $value)
		{
			$tmp_arr[$value->name] = $value->value;
		}
		$this->setBibliographyAttributes($tmp_arr);
		
		// READ Abstracts
		if($abstract = $this->dom->get_elements_by_tagname("Abstract"))
		{
			$this->setAbstract($abstract[0]->get_content());
		}

		// READ ALL BibItems
		$bib_arr = $this->dom->get_elements_by_tagname("BibItem");
		if(!is_array($bib_arr))
		{
			return false;
		}
		$counter = 0;
		foreach($bib_arr as $bib_elem)
		{
			// READ BIB ITEM ATTRIBUTES
			foreach($bib_elem->attributes() as $value)
			{
				$this->setBibItemData($value->name,$value->value,$counter);
			}
			
			// GET ALL CHILD ELEMENTS
			$bib_child = $bib_elem->first_child();
			while($bib_child)
			{
				switch($bib_child->tagname)
				{
					case "Identifier":
						unset($tmp_arr);
						foreach($bib_child->attributes() as $value)
						{
							$tmp_arr[$value->name] = $value->value;
						}
						$this->setBibItemData("Identifier",$tmp_arr,$counter);
						break;

					case "Language":
						foreach($bib_child->attributes() as $value)
						{
							$value = $value->value;
						}
						$this->appendBibItemData("Language",$value,$counter);
						break;
				}

				$bib_child = $bib_child->next_sibling();
				++$counter;
			}
		}
		*/

#		$res = $this->nested_obj->getFirstDomNode("//Bibliography/BibItem/Language");
		#$this->title = $res[0]["value"];
		#var_dump("<pre>",$res,"</pre");

#	}
	//fbo:
	
	/**
	* set xml content of BibItem, start with <BibItem...>,
	* end with </BibItemt>, comply with ILIAS DTD, use utf-8!
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
	* append xml content to BibItem
	* setXMLContent must be called before and the same encoding must be used
	*
	* @param	string		$a_xml			xml content
	*/
	function appendXMLContent($a_xml)
	{
		$this->xml.= $a_xml;
	}


	/**
	* get xml content of BibItem
	*/
	function getXMLContent()/*$a_incl_head = false*/
	{

        return $this->xml;
	}

	// PRIVATE METHODS
	function __initNestedSet()
	{
		include_once("classes/class.ilNestedSetXML.php");

		$this->nested_obj =& new ilNestedSetXML();
		$this->nested_obj->init($this->getID(), $this->getType());

		return $this->nested_obj->initDom();
	}

	function setBooktitle($a_booktitle)
	{
		if ($a_booktitle == "")
		{
			$a_booktitle = "NO TITLE";
		}

		$this->booktitle = $a_booktitle;
	}

	function getBooktitle()
	{
		return $this->booktitle;
	}

	function setEdition($a_edition)
	{
		$this->edition = $a_edition;
	}

	function getEdition()
	{
		return $this->edition;
	}

	function setPublisher($a_publisher)
	{
		$this->publisher = $a_publisher;
	}

	function getPublisher()
	{
		return $this->publisher;
	}

	function setYear($a_year)
	{
		$this->year = $a_year;
	}

	function getYear()
	{
		return $this->year;
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
	function getElement($a_name, $a_path = "", $a_index = 0)
	{
		if(!$this->__initNestedSet())
		{
			return false;
		}

		$p = "//Bibliography";
		if ($a_path != "")
		{
			$p .= "/" . $a_path;
		}
		$nodes = $this->nested_obj->getDomContent($p, $a_name, $a_index);
		$this->setElement($a_name, $nodes);
/*		if ($a_name == "Author" ||
			$a_name == "FirstName" ||
			$a_name == "MiddleName" ||
			$a_name == "LastName")
		{
			echo "Index: " . $a_index . " | Path: " . $p . " | Name: " . $a_name . "<br>\n";
			vd($this->$a_name);
		}
*/
		return $this->$a_name;
	}

	function read()
	{
		if(!$this->__initNestedSet())
		{
			return false;
		}

		/* Get bib data from nested set */
		if ($this->getType() == "lm" ||
			$this->getType() == "dbk") 
		{
			include_once("./classes/class.ilNestedSetXML.php");
			$this->nested_obj = new ilNestedSetXML();
#			echo "ID: " . $this->getID() . ", Type: " . $this->getType() . "<br>";
			$this->nested_obj->init($this->id, $this->getType());
			if ( !$this->nested_obj->initDom() )
			{
				/* No bib data found --> Create default bibliography dataset */
				$xml = '
					<Bibliography>
						<BibItem Type="" Label="">
							<Identifier Catalog="ILIAS" Entry="il__' . $this->getType() . '_' . $this->getID() . '"></Identifier>
							<Language Language="' . $this->ilias->account->getLanguage() . '"></Language>
							<Booktitle Language="' . $this->ilias->account->getLanguage() . '">NO TITLE</Booktitle>
							<Edition>N/A</Edition>
							<HowPublished Type=""></HowPublished>
							<Publisher></Publisher>
							<Year>N/A</Year>
							<URL></URL>
						</BibItem>
					</Bibliography>
				';
				$this->nested->import($xml, $this->id, $this->getType());

				$bibData["booktitle"] = "NO TITLE";
				$bibData["edition"] = "";
				$bibData["publisher"] = "";
				$bibData["year"] = "";
			}
			else
			{
				$bibData["booktitle"] = $this->nested->getFirstDomContent("//Bibliography/BibItem/Booktitle");
				$bibData["edition"] = $this->nested->getFirstDomContent("//Bibliography/BibItem/Edition");
				$bibData["publisher"] = $this->nested->getFirstDomContent("//Bibliography/BibItem/Publisher");
				$bibData["year"] = $this->nested->getFirstDomContent("//Bibliography/BibItem/Year");
			}

		} 
		
		$this->setBooktitle($bibData["booktitle"]);
		$this->setEdition($bibData["edition"]);
		$this->setPublisher($bibData["publisher"]);
		$this->setYear($bibData["year"]);
	}

	/**
	* delete meta data node
	*/
	function delete($a_name, $a_path, $a_index)
	{
		if ($a_name != "")
		{
			$p = "//Bibliography";
			if ($a_path != "")
			{
				$p .= "/" . $a_path;
			}
			$this->nested_obj->deleteDomNode($p, $a_name, $a_index);
			$this->nested_obj->updateFromDom();
		}
	}

	/**
	* add meta data node
	*/
	function add($a_name, $a_path, $a_index = 0)
	{
		$p = "//Bibliography";
		if ($a_path != "")
		{
			$p .= "/" . $a_path;
		}
		$attributes = array();
#		echo "Index: " . $a_index . " | Path: " . $p . " | Name: " . $a_name . "<br>\n";
		switch ($a_name)
		{
			case "BibItem"		:	$xml = '
										<BibItem Type="" Label="">
											<Identifier Catalog="ILIAS" Entry="il__' . $this->getType() . '_' . $this->getID() . '"></Identifier>
											<Language Language="' . $this->ilias->account->getLanguage() . '"></Language>
											<Booktitle Language="' . $this->ilias->account->getLanguage() . '">NO TITLE</Booktitle>
											<Edition>N/A</Edition>
											<HowPublished Type=""></HowPublished>
											<Publisher></Publisher>
											<Year>N/A</Year>
											<URL></URL>
										</BibItem>
									';
									$this->nested_obj->addXMLNode($p, $xml, $a_index);
									break;
			case "Identifier"	:	$value = "";
									$attributes[0] = array("name" => "Catalog", "value" => "");
									$attributes[1] = array("name" => "Entry", "value" => "");
									$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
									break;
			case "Keyword"		:	;
			case "Booktitle"	:	;
			case "Language"		:	$value = "";
									$attributes[0] = array("name" => "Language", value => $this->ilias->account->getLanguage());
									$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
									break;
			case "Author"		:	$xml = '
										<Author>
											<Lastname></Lastname>
										</Author>
									';
									$this->nested_obj->addXMLNode($p, $xml, $a_index);
									break;
			case "HowPublished"	:	$value = "";
									$attributes[0] = array("name" => "Type", value => "");
									$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
									break;
			case "Series"		:	$xml = '
										<Series>
											<SeriesTitle></SeriesTitle>
										</Series>
									';
									$this->nested_obj->addXMLNode($p, $xml, $a_index);
									break;
			default				:	$value = "";
									$attributes = "";
									$this->nested_obj->addDomNode($p, $a_name, $value, $attributes, $a_index);
									break;
		}
		$this->nested_obj->updateFromDom();
	}

	/**
	* create bib data object in db
	*/
	function create()
	{
//echo "<b>meta create()</b><br>";
		include_once("./classes/class.ilNestedSetXML.php");
		$this->nested = new ilNestedSetXML();
		$this->nested->init($this->id, $this->getType());
		if ( !$this->nested->initDom() )
		{
			
			if (is_object($this->obj)) 
			{
				$booktitle = $this->obj->getBooktitle();
				$edition = $this->obj->getEdition();
				$publisher = $this->obj->getPublisher();
				$year = $this->obj->getYear();
			}
			
			$xml = '
				<Bibliography>
					<BibItem Type="" Label="">
						<Identifier Catalog="ILIAS" Entry="il__' . $this->getType() . '_' . $this->getID() . '"></Identifier>
						<Language Language="' . $this->ilias->account->getLanguage() . '"></Language>
						<Booktitle Language="' . $this->ilias->account->getLanguage() . '">NO TITLE</Booktitle>
						<Edition>' . $edition . '</Edition>
						<HowPublished Type=""></HowPublished>
						<Publisher>' . $publisher . '</Publisher>
						<Year>' . $year . '</Year>
						<URL></URL>
					</BibItem>
				</Bibliography>
			';
			$this->nested->import($xml, $this->getID(), $this->getType());
		}
	}

	/**
	* update everything
	*/
	function update()
	{
		$query = "REPLACE INTO bib_data ";
		$query .= "(obj_id, obj_type, booktitle, edition, publisher, year, language) VALUES (";
		$query .= "'" . $this->getId() . "', ";
		$query .= "'" . $this->getType() . "', ";
		$query .= "'" . ilUtil::prepareDBString($this->getBooktitle()) . "', ";
		$query .= "'" . ilUtil::prepareDBString($this->getEdition()) . "', ";
		$query .= "'" . ilUtil::prepareDBString($this->getPublisher()) . "', ";
		$query .= "'" . ilUtil::prepareDBString($this->getYear()) . "', ";
		$query .= "'" . $this->getLanguage() . "')";
		$this->ilias->db->query($query);

		if ($this->getType() == "lm" ||
			$this->getType() == "dbk")
		{
			$p = "//Bibliography";
			$this->nested->updateDomNode($p, $this->meta);
			$this->nested->updateFromDom();
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
