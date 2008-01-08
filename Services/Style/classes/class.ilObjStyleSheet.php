<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


require_once "./classes/class.ilObject.php";

/**
* Class ilObjStyleSheet
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObject
*/
class ilObjStyleSheet extends ilObject
{
	var $style;


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjStyleSheet($a_id = 0, $a_call_by_reference = false)
	{
		$this->type = "sty";
		$this->style = array();
		if($a_call_by_reference)
		{
			$this->ilias->raiseError("Can't instantiate style object via reference id.",$this->ilias->error_obj->FATAL);
		}

		parent::ilObject($a_id, false);
	}

	function setRefId()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::setRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function getRefId()
	{
		return "";
		//$this->ilias->raiseError("Operation ilObjStyleSheet::getRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function putInTree()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::putInTree() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function createReference()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::createReference() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function setUpToDate($a_up_to_date = true)
	{
		$this->up_to_date = $a_up_to_date;
	}
	
	function getUpToDate()
	{
		return $this->up_to_date;
	}

	function setScope($a_scope)
	{
		$this->scope = $a_scope;
	}
	
	function getScope()
	{
		return $this->scope;
	}

	function _writeUpToDate($a_id, $a_up_to_date)
	{
		global $ilDB;

		$q = "UPDATE style_data SET uptodate = ".$ilDB->quote((int) $a_up_to_date).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* 
	*/
	function _lookupUpToDate($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM style_data ".
			" WHERE id = ".$ilDB->quote($a_id);
		$res = $ilDB->query($q);
		$sty = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		return (boolean) $sty["uptodate"];
	}

	/**
	* write standard flag
	*/
	function _writeStandard($a_id, $a_std)
	{
		global $ilDB;

		$q = "UPDATE style_data SET standard = ".$ilDB->quote((int) $a_std).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* write standard flag
	*/
	function _writeScope($a_id, $a_scope)
	{
		global $ilDB;

		$q = "UPDATE style_data SET category = ".$ilDB->quote((int) $a_scope).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* lookup standard flag
	*/
	function _lookupStandard($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM style_data ".
			" WHERE id = ".$ilDB->quote($a_id);
		$res = $ilDB->query($q);
		$sty = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		return (boolean) $sty["standard"];
	}

	/**
	* write active flag
	*/
	function _writeActive($a_id, $a_active)
	{
		global $ilDB;

		$q = "UPDATE style_data SET active = ".$ilDB->quote((int) $a_active).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* lookup active flag
	*/
	function _lookupActive($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM style_data ".
			" WHERE id = ".$ilDB->quote($a_id);
		$res = $ilDB->query($q);
		$sty = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		return (boolean) $sty["active"];
	}

	/**
	* get standard styles
	*/
	function _getStandardStyles($a_exclude_default_style = false,
		$a_include_deactivated = false, $a_scope = 0)
	{
		global $ilDB, $ilias, $tree;
		
		$default_style = $ilias->getSetting("default_content_style_id");
		
		$and_str = "";
		if (!$a_include_deactivated)
		{
			$and_str = " AND active = 1";
		}
		
		$q = "SELECT * FROM style_data ".
			" WHERE standard = 1".$and_str;
		$res = $ilDB->query($q);
		$styles = array();
		while($sty = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!$a_exclude_default_style || $default_style != $sty["id"])
			{
				// check scope
				if ($a_scope > 0 && $sty["category"] > 0)
				{
					if ($tree->isInTree($sty["category"]) &&
						$tree->isInTree($a_scope))
					{
						$path = $tree->getPathId($a_scope);
						if (!in_array($sty["category"], $path))
						{
							continue;
						}
					}
				}
				$styles[$sty["id"]] = ilObject::_lookupTitle($sty["id"]);
			}
		}
		
		return $styles;
	}
	
	
	/**
	* get all clonable styles (active standard styles and individual learning
	* module styles with write permission)
	*
	*/
	function _getClonableContentStyles()
	{
		global $ilAccess, $ilDB;
		
		$clonable_styles = array();
		
		$q = "SELECT * FROM style_data, object_data ".
			" WHERE object_data.obj_id = style_data.id ";
		$style_set = $ilDB->query($q);
		while($style_rec = $style_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$clonable = false;
			if ($style_rec["standard"] == 1)
			{
				if ($style_rec["active"] == 1)
				{
					$clonable = true;
				}
			}
			else
			{
				include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
				$obj_ids = ilObjContentObject::_lookupContObjIdByStyleId($style_rec["id"]);
				foreach($obj_ids as $id)
				{
					$ref = ilObject::_getAllReferences($id);
					foreach($ref as $ref_id)
					{
						if ($ilAccess->checkAccess("write", "", $ref_id))
						{
							$clonable = true;
						}
					}
				}
			}
			if ($clonable)
			{
				$clonable_styles[$style_rec["id"]] =
					$style_rec["title"];
			}
		}
		return $clonable_styles;
	}

	/**
	* assign meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}

	function create($a_from_style = 0)
	{
		global $ilDB;
		
		parent::create();

		if ($a_from_style == 0)
		{
			$def = array(
				array("tag" => "div", "class" => "PageTitle", "parameter" => "margin-top" ,"value" => "5px"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "margin-bottom" ,"value" => "20px"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "font-size" ,"value" => "140%"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "padding-bottom" ,"value" => "3px"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "border-bottom-width" ,"value" => "1px"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "border-bottom-style" ,"value" => "solid"),
				array("tag" => "div", "class" => "PageTitle", "parameter" => "border-color" ,"value" => "#000000"),
	
				array("tag" => "span", "class" => "Strong", "parameter" => "font-weight" ,"value" => "bold"),
				array("tag" => "span", "class" => "Emph", "parameter" => "font-style" ,"value" => "italic"),
				array("tag" => "span", "class" => "Comment", "parameter" => "color" ,"value" => "green"),
				array("tag" => "span", "class" => "Quotation", "parameter" => "color" ,"value" => "brown"),
				array("tag" => "span", "class" => "Quotation", "parameter" => "font-style" ,"value" => "italic"),
	
				array("tag" => "a", "class" => "FootnoteLink", "parameter" => "color" ,"value" => "blue"),
				array("tag" => "a", "class" => "FootnoteLink", "parameter" => "font-weight" ,"value" => "normal"),
				array("tag" => "a", "class" => "FootnoteLink:hover", "parameter" => "color" ,"value" => "#000000"),
				array("tag" => "div", "class" => "Footnote", "parameter" => "margin-top" ,"value" => "5px"),
				array("tag" => "div", "class" => "Footnote", "parameter" => "margin-bottom" ,"value" => "5px"),
				array("tag" => "div", "class" => "Footnote", "parameter" => "font-style" ,"value" => "italic"),
	
				array("tag" => "a", "class" => "IntLink", "parameter" => "color" ,"value" => "blue"),
				array("tag" => "a", "class" => "IntLink:visited", "parameter" => "color" ,"value" => "blue"),
				array("tag" => "a", "class" => "IntLink", "parameter" => "font-weight" ,"value" => "normal"),
				array("tag" => "a", "class" => "IntLink", "parameter" => "text-decoration" ,"value" => "underline"),
				array("tag" => "a", "class" => "IntLink:hover", "parameter" => "color" ,"value" => "#000000"),
	
				array("tag" => "a", "class" => "ExtLink", "parameter" => "color" ,"value" => "blue"),
				array("tag" => "a", "class" => "ExtLink:visited", "parameter" => "color" ,"value" => "blue"),
				array("tag" => "a", "class" => "ExtLink", "parameter" => "font-weight" ,"value" => "normal"),
				array("tag" => "a", "class" => "ExtLink", "parameter" => "text-decoration" ,"value" => "underline"),
				array("tag" => "a", "class" => "ExtLink:hover", "parameter" => "color" ,"value" => "#000000"),
	
				array("tag" => "div", "class" => "LMNavigation", "parameter" => "background-color" ,"value" => "#EEEEEE"),
				array("tag" => "div", "class" => "LMNavigation", "parameter" => "border-style" ,"value" => "outset"),
				array("tag" => "div", "class" => "LMNavigation", "parameter" => "border-color" ,"value" => "#EEEEEE"),
				array("tag" => "div", "class" => "LMNavigation", "parameter" => "border-width" ,"value" => "1px"),
				array("tag" => "div", "class" => "Page", "parameter" => "background-color" ,"value" => "#FFFFFF"),
				array("tag" => "div", "class" => "Page", "parameter" => "padding" ,"value" => "0px"),
				array("tag" => "div", "class" => "Page", "parameter" => "margin" ,"value" => "0px"),
				array("tag" => "td", "class" => "Cell1", "parameter" => "background-color" ,"value" => "#FFCCCC"),
				array("tag" => "td", "class" => "Cell2", "parameter" => "background-color" ,"value" => "#CCCCFF"),
				array("tag" => "td", "class" => "Cell3", "parameter" => "background-color" ,"value" => "#CCFFCC"),
				array("tag" => "td", "class" => "Cell4", "parameter" => "background-color" ,"value" => "#FFFFCC"),
	
				array("tag" => "p", "class" => "Standard", "parameter" => "margin-top" ,"value" => "10px"),
				array("tag" => "p", "class" => "Standard", "parameter" => "margin-bottom" ,"value" => "10px"),
	
				array("tag" => "p", "class" => "List", "parameter" => "margin-top" ,"value" => "3px"),
				array("tag" => "p", "class" => "List", "parameter" => "margin-bottom" ,"value" => "3px"),
	
				array("tag" => "p", "class" => "Headline1", "parameter" => "margin-top" ,"value" => "20px"),
				array("tag" => "p", "class" => "Headline1", "parameter" => "margin-bottom" ,"value" => "10px"),
				array("tag" => "p", "class" => "Headline1", "parameter" => "font-size" ,"value" => "140%"),
	
				array("tag" => "p", "class" => "Headline2", "parameter" => "margin-top" ,"value" => "20px"),
				array("tag" => "p", "class" => "Headline2", "parameter" => "margin-bottom" ,"value" => "10px"),
				array("tag" => "p", "class" => "Headline2", "parameter" => "font-size" ,"value" => "130%"),
	
				array("tag" => "p", "class" => "Headline3", "parameter" => "margin-top" ,"value" => "20px"),
				array("tag" => "p", "class" => "Headline3", "parameter" => "margin-bottom" ,"value" => "10px"),
				array("tag" => "p", "class" => "Headline3", "parameter" => "font-size" ,"value" => "120%"),
	
				array("tag" => "p", "class" => "Example", "parameter" => "padding-left" ,"value" => "20px"),
				array("tag" => "p", "class" => "Example", "parameter" => "border-left" ,"value" => "3px"),
				array("tag" => "p", "class" => "Example", "parameter" => "border-left-style" ,"value" => "solid"),
				array("tag" => "p", "class" => "Example", "parameter" => "border-left-color" ,"value" => "blue"),
	
				array("tag" => "p", "class" => "Citation", "parameter" => "color" ,"value" => "brown"),
				array("tag" => "p", "class" => "Citation", "parameter" => "font-style" ,"value" => "italic"),
	
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "margin-left" ,"value" => "20px"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "margin-right" ,"value" => "20px"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "color" ,"value" => "red"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "padding" ,"value" => "10px"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "border" ,"value" => "1px"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "border-style" ,"value" => "solid"),
				array("tag" => "p", "class" => "Mnemonic", "parameter" => "border-color" ,"value" => "red"),
	
				array("tag" => "p", "class" => "Additional", "parameter" => "padding" ,"value" => "10px"),
				array("tag" => "p", "class" => "Additional", "parameter" => "border" ,"value" => "1px"),
				array("tag" => "p", "class" => "Additional", "parameter" => "border-style" ,"value" => "solid"),
				array("tag" => "p", "class" => "Additional", "parameter" => "border-color" ,"value" => "blue"),
	
				array("tag" => "p", "class" => "Remark", "parameter" => "padding" ,"value" => "10px"),
				array("tag" => "p", "class" => "Remark", "parameter" => "border" ,"value" => "1px"),
				array("tag" => "p", "class" => "Remark", "parameter" => "border-style" ,"value" => "solid"),
				array("tag" => "p", "class" => "Remark", "parameter" => "border-color" ,"value" => "#909090"),
				array("tag" => "p", "class" => "Remark", "parameter" => "background-color" ,"value" => "#D0D0D0"),
				array("tag" => "p", "class" => "Remark", "parameter" => "text-align" ,"value" => "right"),
	
				array("tag" => "p", "class" => "TableContent", "parameter" => "margin-left" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "margin-right" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "margin-top" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "margin-bottom" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "padding-left" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "padding-right" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "padding-top" ,"value" => "0px"),
				array("tag" => "p", "class" => "TableContent", "parameter" => "padding-bottom" ,"value" => "0px"),
	
				array("tag" => "table", "class" => "Media", "parameter" => "background-color" ,"value" => "#F5F5F5"),
				array("tag" => "table", "class" => "Media", "parameter" => "padding" ,"value" => "0px"),
				array("tag" => "table", "class" => "Media", "parameter" => "margin" ,"value" => "10px"),
	
				array("tag" => "td", "class" => "MediaCaption", "parameter" => "padding" ,"value" => "5px")
			);
		}
		else
		{
			$def = array();
			$q = "SELECT * FROM style_parameter WHERE style_id = ".$ilDB->quote($a_from_style);
			$par_set = $ilDB->query($q);
			while($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$def[] = array("tag" => $par_rec["tag"], "class" => $par_rec["class"],
					"parameter" => $par_rec["parameter"] ,"value" => $par_rec["value"]);
			}
		}

		// default style settings
		foreach ($def as $sty)
		{
			$q = "INSERT INTO style_parameter (style_id, tag, class, parameter, value) VALUES ".
				"(".$ilDB->quote($this->getId()).",".
				$ilDB->quote($sty["tag"]).",".
				$ilDB->quote($sty["class"]).",".
				$ilDB->quote($sty["parameter"]).",".
				$ilDB->quote($sty["value"]).")";
			$ilDB->query($q);
		}
		
		// add style_data record
		$q = "INSERT INTO style_data (id, uptodate, category) VALUES ".
			"(".$ilDB->quote($this->getId()).", 0,".
			$ilDB->quote($this->getScope()).")";
		$ilDB->query($q);

		$this->read();
		$this->writeCSSFile();
	}
	
	/**
	* clone style sheet (note: styles have no ref ids and return an object id)
	* 
	* @access	public
	* @return	integer		new obj id
	*/
	function ilClone()
	{
		global $log;
		
		$new_obj = new ilObjStyleSheet();
		$new_obj->setTitle($this->getTitle());
		$new_obj->setType($this->getType());
		$new_obj->setDescription($this->getDescription());
		$new_obj->create($this->getId());
		
		return $new_obj->getId();
	}


	/**
	* write style parameter to db
	*
	* @param	string		$a_tag		tag name		(tag.class, e.g. "p.Mnemonic")
	* @param	string		$a_par		tag parameter	(e.g. "margin-left")	
	*/
	function addParameter($a_tag, $a_par)
	{
		global $ilDB;
		
		$avail_params = $this->getAvailableParameters();
		$tag = explode(".", $a_tag);
		$value = $avail_params[$a_par][0];
		$q = "INSERT INTO style_parameter (style_id, tag, class, parameter, value) VALUES ".
			"(".$ilDB->quote($this->getId()).",".$ilDB->quote($tag[0]).",".
			$ilDB->quote($tag[1]).
			",".$ilDB->quote($a_par).",".$ilDB->quote($value).")";
		$this->ilias->db->query($q);
		$this->read();
		$this->writeCSSFile();
	}

	/**
	* delete style parameter
	*
	* @param	int		$a_id		style parameter id
	*/
	function deleteParameter($a_id)
	{
		global $ilDB;
		
		$q = "DELETE FROM style_parameter WHERE id = ".$ilDB->quote($a_id);
		$this->ilias->db->query($q);
	}

	/**
	* delete style parameter by tag/class/parameter
	*
	*/
	function deleteStylePar($a_tag, $a_class, $a_par)
	{
		global $ilDB;
		
		$q = "DELETE FROM style_parameter WHERE ".
			" style_id = ".$ilDB->quote($this->getId())." AND ".
			" tag = ".$ilDB->quote($a_tag)." AND ".
			" class = ".$ilDB->quote($a_class)." AND ".
			" parameter = ".$ilDB->quote($a_par);

		$this->ilias->db->query($q);
	}

	/**
	* delete style object
	*/
	function delete()
	{
		global $ilDB;
		
		// delete object
		parent::delete();
		
		// check whether this style is global default
		$def_style = $this->ilias->getSetting("default_content_style_id");		
		if ($def_style == $this->getId())
		{
			$this->ilias->deleteSetting("default_content_style_id");
		}

		// check whether this style is global fixed
		$fixed_style = $this->ilias->getSetting("fixed_content_style_id");		
		if ($fixed_style == $this->getId())
		{
			$this->ilias->deleteSetting("fixed_content_style_id");
		}

		// delete style parameter
		$q = "DELETE FROM style_parameter WHERE style_id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);
		
		// delete style file
		$css_file_name = ilUtil::getWebspaceDir()."/css/style_".$this->getId().".css";
		if (is_file($css_file_name))
		{
			unlink($css_file_name);
		}
		
		// delete entries in learning modules
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		ilObjContentObject::_deleteStyleAssignments($this->getId());
		
		// delete style data record
		$q = "DELETE FROM style_data WHERE id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);

	}


	/**
	* read style properties
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM style_parameter WHERE style_id = ".
			$ilDB->quote($this->getId())." ORDER BY tag, class ";
		$style_set = $this->ilias->db->query($q);
		$ctag = "";
		$cclass = "";
		$this->style = array();
		while($style_rec = $style_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($style_rec["tag"] != $ctag || $style_rec["class"] != $cclass)
			{
				// add current tag array to style array
				if(is_array($tag))
				{
					$this->style[] = $tag;
				}
				$tag = array();
			}
			$ctag = $style_rec["tag"];
			$cclass = $style_rec["class"];
			$tag[] = $style_rec;
		}
		if(is_array($tag))
		{
			$this->style[] = $tag;
		}
		
		$q = "SELECT * FROM style_data WHERE id = ".$ilDB->quote($this->getId());
		$res = $ilDB->query($q);
		$sty = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setUpToDate((boolean) $sty["uptodate"]);
		$this->setScope($sty["category"]);
		
	}

	/**
	* write css file to webspace directory
	*/
	function writeCSSFile($a_target_file = "")
	{
		$style = $this->getStyle();

		if ($a_target_file == "")
		{
			$css_file_name = ilUtil::getWebspaceDir()."/css/style_".$this->getId().".css";
		}
		else
		{
			$css_file_name = $a_target_file;
		}
		$css_file = fopen($css_file_name, "w");
		
		$page_background = "";

		foreach ($style as $tag)
		{
			fwrite ($css_file, $tag[0]["tag"].".ilc_".$tag[0]["class"]."\n");
			fwrite ($css_file, "{\n");

			foreach($tag as $par)
			{
				fwrite ($css_file, "\t".$par["parameter"].": ".$par["value"].";\n");
				
				// save page background
				if ($tag[0]["tag"] == "div" && $tag[0]["class"] == "Page"
					&& $par["parameter"] == "background-color")
				{
					$page_background = $par["value"];
				}
			}
			fwrite ($css_file, "}\n");
			fwrite ($css_file, "\n");
		}
		
		if ($page_background != "")
		{
			fwrite ($css_file, "td.ilc_Page\n");
			fwrite ($css_file, "{\n");
			fwrite ($css_file, "\t"."background-color: ".$page_background.";\n");
			fwrite ($css_file, "}\n");
		}
		fclose($css_file);
		
		$this->setUpToDate(true);
		$this->_writeUpToDate($this->getId(), true);
	}


	/**
	* get content style path
	*
	* static (to avoid full reading)
	*/
	function getContentStylePath($a_style_id)
	{
		global $ilias;
		
		$rand = rand(1,999999);
		
		
		// check global fixed content style
		$fixed_style = $ilias->getSetting("fixed_content_style_id");
		if ($fixed_style > 0)
		{
			$a_style_id = $fixed_style;
		}

		// check global default style
		if ($a_style_id <= 0)
		{
			$a_style_id = $ilias->getSetting("default_content_style_id");
		}

		if ($a_style_id > 0 && ilObject::_exists($a_style_id))
		{
			// check whether file is up to date
			if (!ilObjStyleSheet::_lookupUpToDate($a_style_id))
			{
				$style = new ilObjStyleSheet($a_style_id);
				$style->writeCSSFile();
			}
			
			return ilUtil::getWebspaceDir("output").
				"/css/style_".$a_style_id.".css?dummy=$rand";
		}
		else		// todo: work this out
		{
			return "./Services/COPage/css/content.css";
		}
	}

	/**
	* get content print style
	*
	* static
	*/
	function getContentPrintStyle()
	{
		return "./Services/COPage/css/print_content.css";
	}

	/**
	* get syntax style path
	*
	* static
	*/
	function getSyntaxStylePath()
	{
		return "./Services/COPage/css/syntaxhighlight.css";
	}

	function update()
	{
		global $ilDB;
		
		parent::update();
		$this->read();				// this could be done better
		$this->writeCSSFile();
		
		$q = "UPDATE style_data ".
			"SET category = ".$ilDB->quote($this->getScope());
		$ilDB->query($q);
	}

	/**
	* update style parameter per id
	*
	* @param	int		$a_id		style parameter id
	* @param	int		$a_id		style parameter value
	*/
	function updateStyleParameter($a_id, $a_value)
	{
		global $ilDB;
		
		$q = "UPDATE style_parameter SET VALUE=".
			$ilDB->quote($a_value)." WHERE id = ".
			$ilDB->quote($a_id);
		$style_set = $this->ilias->db->query($q);
	}
	
	/**
	* update style parameter per tag/class/parameter
	*
	*/
	function replaceStylePar($a_tag, $a_class, $a_par, $a_val)
	{
		global $ilDB;
		
		$q = "SELECT * FROM style_parameter WHERE ".
			" style_id = ".$ilDB->quote($this->getId())." AND ".
			" tag = ".$ilDB->quote($a_tag)." AND ".
			" class = ".$ilDB->quote($a_class)." AND ".
			" parameter = ".$ilDB->quote($a_par);
		
		$set = $ilDB->query($q);
		
		if ($rec = $set->fetchRow())
		{
			$q = "UPDATE style_parameter SET ".
				" value = ".$ilDB->quote($a_val)." WHERE ".
				" style_id = ".$ilDB->quote($this->getId())." AND ".
				" tag = ".$ilDB->quote($a_tag)." AND ".
				" class = ".$ilDB->quote($a_class)." AND ".
				" parameter = ".$ilDB->quote($a_par);

			$ilDB->query($q);
		}
		else
		{
			$q = "INSERT INTO style_parameter (value, style_id, tag,  class, parameter) VALUES ".
				" (".$ilDB->quote($a_val).",".
				" ".$ilDB->quote($this->getId()).",".
				" ".$ilDB->quote($a_tag).",".
				" ".$ilDB->quote($a_class).",".
				" ".$ilDB->quote($a_par).")";

			$ilDB->query($q);
		}
	}


	/**
	* todo: bad style! should return array of objects, not multi-dim-arrays
	*/
	function getStyle()
	{
		return $this->style;
	}
	
	/**
	* set styles
	*/
	function setStyle($a_style)
	{
		$this->style = $a_style;
	}
	
	
	/**
	* get xml representation of style object
	*/
	function getXML()
	{
		$xml.= "<StyleSheet>";
		$xml.= "<Title>".$this->getTitle()."</Title>";
		$xml.= "<Description>".$this->getDescription()."</Description>";
		foreach($this->style as $style)
		{
			$xml.= "<Style Tag=\"".$style[0]["tag"]."\" Class=\"".$style[0]["class"]."\">";
			foreach($style as $tag)
			{
				$xml.="<StyleParameter Name=\"".$tag["parameter"]."\" Value=\"".$tag["value"]."\"/>";
			}
			$xml.= "</Style>";
		}
		$xml.= "</StyleSheet>";
		
		return $xml;
	}
	
	
	/**
	* export style xml file to directory
	*/
	function exportXML($a_dir)
	{
		$file = $a_dir."/style.xml";
		
		// open file
		if (!($fp = @fopen($file,"w")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
		
		// set file permissions
		chmod($file, 0770);
		
		// write xml data into the file
		fwrite($fp, $this->getXML());
		
		// close file
		fclose($fp);

	}

	/**
	* create style from xml file
	*/
	function createFromXMLFile($a_file)
	{
		global $ilDB;
		
		parent::create();		
		include_once("./Services/Style/classes/class.ilStyleImportParser.php");
		$importParser = new ilStyleImportParser($a_file, $this);
		$importParser->startParsing();
		
		// store style parameter
		foreach ($this->style as $style)
		{
			foreach($style as $tag)
			{
				$q = "INSERT INTO style_parameter (style_id, tag, class, parameter, value) VALUES ".
					"(".$ilDB->quote($this->getId()).",".
					$ilDB->quote($tag["tag"]).",".
					$ilDB->quote($tag["class"]).
					",".$ilDB->quote($tag["parameter"]).",".
					$ilDB->quote($tag["value"]).")";
				$this->ilias->db->query($q);
			}
		}
		
		// add style_data record
		$q = "INSERT INTO style_data (id, uptodate) VALUES ".
			"(".$ilDB->quote($this->getId()).", 0)";
		$ilDB->query($q);
		
		$this->update();
		$this->read();
		$this->writeCSSFile();
	}
	
	/**
	* get all available tags in an array
	*/
	function getAvailableTags()
	{
		$tags = array("a.FootnoteLink", "a.FootnoteLink:hover", "a.IntLink", "a.IntLink:hover",
 			"a.IntLink:visited", "a.IntLink:active",
			"a.ExtLink", "a.ExtLink:hover", "a.ExtLink:visited", "a.ExtLink:active",
			"div.Footnote", "div.LMNavigation", "div.Page", "div.PageTitle", "span.Comment",
			"span.Emph", "span.Quotation", "span.Strong",
			"td.Cell1", "td.Cell2", "td.Cell3", "td.Cell4",
			"p.Standard", "p.List", "p.Headline1", "p.Headline2", "p.Headline3",
			"p.Example", "p.Citation", "p.Mnemonic", "p.Additional", "p.Remark",
			"p.TableContent",
			"table.Media", "td.MediaCaption");

		return $tags;
	}

	function getAvailableParameters()
	{
		$pars = array(
			"font-family" => array(),
			"font-style" => array("italic", "oblique", "normal"),
			"font-variant" => array("small-caps", "normal"),
			"font-weight" => array("bold", "normal", "bolder", "lighter"),
			"font-stretch" => array("wider", "narrower", "condensed", "semi-condensed",
					"extra-condensed", "ultra-condensed", "expanded", "semi-expanded",
					"extra-expanded", "ultra-expanded", "normal"),
			"font-size" => array(),
			"word-spacing" => array(),
			"letter-spacing" => array(),
			"text-decoration" => array("underline", "overline", "line-through", "blink", "none"),
			"text-transform" => array("capitalize", "uppercase", "lowercase", "none"),
			"color" => array(),

			"text-indent" => array(),
			"line-height" => array(),
			"vertical-align" => array("top", "middle", "bottom", "baseline", "sub", "super",
				"text-top", "text-bottom"),
			"text-align" => array("left", "center", "right", "justify"),
			"white-space" => array("normal", "pre", "nowrap"),

			"margin" => array(),
			"margin-top" => array(),
			"margin-bottom" => array(),
			"margin-left" => array(),
			"margin-right" => array(),

			"padding" => array(),
			"padding-top" => array(),
			"padding-bottom" => array(),
			"padding-left" => array(),
			"padding-right" => array(),

			"border-width" => array(),
			"border-width-top" => array(),
			"border-width-bottom" => array(),
			"border-width-left" => array(),
			"border-width-right" => array(),

			"border-color" => array(),
			"border-top-color" => array(),
			"border-bottom-color" => array(),
			"border-left-color" => array(),
			"border-right-color" => array(),

			"border-style" => array("none", "hidden", "dotted", "dashed", "solid", "double",
				"groove", "ridge", "inset", "outset"),
			"border-top-style" => array("none", "hidden", "dotted", "dashed", "solid", "double",
				"groove", "ridge", "inset", "outset"),
			"border-bottom-style" => array("none", "hidden", "dotted", "dashed", "solid", "double",
				"groove", "ridge", "inset", "outset"),
			"border-left-style" => array("none", "hidden", "dotted", "dashed", "solid", "double",
				"groove", "ridge", "inset", "outset"),
			"border-right-style" => array("none", "hidden", "dotted", "dashed", "solid", "double",
				"groove", "ridge", "inset", "outset"),

			"background-color" => array(),
			"background-image" => array(),
			"background-repeat" => array("repeat", "repeat-x", "repeat-y", "no-repeat"),
			"background-attachment" => array("fixed", "scroll"),
			"background-position" => array("top", "center", "middle", "bottom", "left", "right"),

			"cursor" => array("auto", "default", "crosshair", "pointer", "move",
				"n-resize", "ne-resize", "e-resize", "se-resize", "s-resize", "sw-resize",
				"w-resize", "nw-resize", "text", "wait", "help"),
			"clear" => array ("non","left","right","both")
		);

		return $pars;
	}

} // END class.ilObjStyleSheet
?>
