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
			$this->readXML();
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


	function read()
	{
		if(!$this->__initNestedSet())
		{
			return false;
		}
		return;
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

		$res = $this->nested_obj->getFirstDomNode("//Bibliography/BibItem/Language");
		#$this->title = $res[0]["value"];
		#var_dump("<pre>",$res,"</pre");

	}
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
		$this->nested_obj->init($this->content_obj->getId(),"bib");

		return $this->nested_obj->initDom();
	}
}
?>
