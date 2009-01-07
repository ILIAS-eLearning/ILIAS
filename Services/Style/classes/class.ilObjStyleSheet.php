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

	public static $num_unit = array("px", "em", "ex", "%", "pt", "pc", "in", "mm", "cm");
	public static $num_unit_no_perc = array("px", "em", "ex", "pt", "pc", "in", "mm", "cm");
	
	// css parameters and their attribute values, input type and group
	public static $parameter = array(
		"font-size" => array(
						"values" => array("xx-small", "x-small", "small", "medium", "large", "x-large", "xx-large", "smaller", "larger"),
						"input" => "fontsize",
						"group" => "text"),
		"font-family" => array(
						"values" => array(),
						"input" => "text",
						"group" => "text"),
		"font-style" => array(
						"values" => array("italic", "oblique", "normal"),
						"input" => "select",
						"group" => "text"),
		"font-weight" => array(
						"values" => array("bold", "normal", "bolder", "lighter"),
						"input" => "select",
						"group" => "text"),
		"font-variant" => array(
						"values" => array("small-caps", "normal"),
						"input" => "select",
						"group" => "text"),
		"font-stretch" => array(
						"values" => array("wider", "narrower", "condensed", "semi-condensed",
							"extra-condensed", "ultra-condensed", "expanded", "semi-expanded",
							"extra-expanded", "ultra-expanded", "normal"),
						"input" => "select",
						"group" => "text"),
		"word-spacing" => array(
						"values" => array(),
						"input" => "numeric_no_perc",
						"group" => "text"),
		"letter-spacing" => array(
						"values" => array(),
						"input" => "numeric_no_perc",
						"group" => "text"),
		"text-decoration" => array(
						"values" => array("underline", "overline", "line-through", "blink", "none"),
						"input" => "select",
						"group" => "text"),
		"text-transform" => array(
						"values" => array("capitalize", "uppercase", "lowercase", "none"),
						"input" => "select",
						"group" => "text"),
		"color" => array(
						"values" => array(),
						"input" => "color",
						"group" => "text"),
		"text-indent" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "text"),
		"line-height" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "text"),
		"vertical-align" => array(
						"values" => array("top", "middle", "bottom", "baseline", "sub", "super",
							"text-top", "text-bottom"),
						"input" => "select",
						"group" => "text"),
		"text-align" => array(
						"values" => array("left", "center", "right", "justify"),
						"input" => "select",
						"group" => "text"),
		"white-space" => array(
						"values" => array("normal", "pre", "nowrap"),
						"input" => "select",
						"group" => "text"),
		"margin" => array(
						"values" => array(),
						"input" => "trbl_numeric",
						"subpar" => array("margin", "margin-top", "margin-right",
							"margin-bottom", "margin-left"),
						"group" => "margin_and_padding"),
		"padding" => array(
						"values" => array(),
						"input" => "trbl_numeric",
						"subpar" => array("padding", "padding-top", "padding-right",
							"padding-bottom", "padding-left"),
						"group" => "margin_and_padding"),
		"border-width" => array(
						"values" => array("thin", "medium", "thick"),
						"input" => "border_width",
						"subpar" => array("border-width", "border-top-width", "border-right-width",
							"border-bottom-width", "border-left-width"),
						"group" => "border"),
		"border-color" => array(
						"values" => array(),
						"input" => "trbl_color",
						"subpar" => array("border-color", "border-top-color", "border-right-color",
							"border-bottom-color", "border-left-color"),
						"group" => "border"),
		"border-style" => array(
						"values" => array("none", "hidden", "dotted", "dashed", "solid", "double",
							"groove", "ridge", "inset", "outset"),
						"input" => "border_style",
						"subpar" => array("border-style", "border-top-style", "border-right-style",
							"border-bottom-style", "border-left-style"),
						"group" => "border"),
						
		"background-color" => array(
						"values" => array(),
						"input" => "color",
						"group" => "background"),
		"background-image" => array(
						"values" => array(),
						"input" => "background_image",
						"group" => "background"),
		"background-repeat" => array(
						"values" => array("repeat", "repeat-x", "repeat-y", "no-repeat"),
						"input" => "select",
						"group" => "background"),
		"background-attachment" => array(
						"values" => array("fixed", "scroll"),
						"input" => "select",
						"group" => "background"),
		"background-position" => array(
						"values" => array("horizontal" => array("left", "center", "right"),
							"vertical" => array("top", "center", "bottom")),
						"input" => "background_position",
						"group" => "background"),
						
		"position" => array(
						"values" => array("absolute", "fixed", "relative", "static"),
						"input" => "select",
						"group" => "positioning"),
		"top" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"bottom" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"left" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"right" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"width" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"height" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"min-height" => array(
						"values" => array(),
						"input" => "numeric",
						"group" => "positioning"),
		"float" => array(
						"values" => array("left", "right", "none"),
						"input" => "select",
						"group" => "positioning"),
		"overflow" => array(
						"values" => array("visible", "hidden", "scroll", "auto"),
						"input" => "select",
						"group" => "positioning"),

		"opacity" => array(
						"values" => array(),
						"input" => "percentage",
						"group" => "special"),
		"cursor" => array(
						"values" => array("auto", "default", "crosshair", "pointer", "move",
							"n-resize", "ne-resize", "e-resize", "se-resize", "s-resize", "sw-resize",
							"w-resize", "nw-resize", "text", "wait", "help"),
						"input" => "select",
						"group" => "special"),
		"clear" => array(
						"values" => array ("both","left","right","none"),
						"input" => "select",
						"group" => "special"),
						
		"list-style-type.ol" => array(
						"values" => array ("decimal","lower-roman","upper-roman",
							"lower-alpha", "upper-alpha", "lower-greek", "hebrew",
							"decimal-leading-zero", "cjk-ideographic", "hiragana",
							"katakana", "hiragana-iroha", "katakana-iroha", "none"),
						"input" => "select",
						"group" => "ol"),
		"list-style-type.ul" => array(
						"values" => array ("disc","circle","square",
							"none"),
						"input" => "select",
						"group" => "ul"),
		"list-style-image.ul" => array(
						"values" => array(),
						"input" => "background_image",
						"group" => "ul"),
		"list-style-position.ol" => array(
						"values" => array ("inside","outside"),
						"input" => "select",
						"group" => "ol"),
		"list-style-position.ul" => array(
						"values" => array ("inside","outside"),
						"input" => "select",
						"group" => "ul"
						),
		"border-collapse" => array(
						"values" => array ("collapse","separate"),
						"input" => "select",
						"group" => "table"
						),
		);

	// filter groups of properties that should only be
	// displayed with matching tag (group -> tags)
	public static $filtered_groups =
			array("ol" => array("ol"), "ul" => array("ul"),
				"table" => array("table"), "positioning" => array("div", "img"));

	// style types and their super type
	public static $style_super_types = array(
		"text_block" => array("text_block"),
		"text_inline" => array("text_inline"),
		"section" => array("section"),
		"link" => array("link"),
		"table" => array("table", "table_cell"),
		"list" => array("list_o", "list_u", "list_item"),
		"flist" => array("flist_cont", "flist_head", "flist", "flist_li"),
		"media" => array("media_cont", "media_caption"),
		"question" => array("question", "qtitle", "qanswer", "qinput", "qsubmit", "qfeedr", "qfeedw"),
		"page" => array("page_frame", "page_cont", "page_title", "page_fn",
			"page_tnav", "page_bnav", "page_lnav", "page_rnav", "page_lnavlink", "page_rnavlink",
			"page_lnavimage", "page_rnavimage"),
		"sco" => array("sco_title", "sco_keyw", "sco_desc", "sco_obj")
		);

	// these types are expandable, i.e. the user can define new style classes
	public static $expandable_types = array (
			"text_block", "section", "media_cont", "table", "table_cell", "flist_li"
		);
		
	// tag that are used by style types
	public static $assigned_tags = array (
		"text_block" => "div",
		"text_inline" => "span",
		"section" => "div",
		"link" => "a",
		"table" => "table",
		"table_cell" => "td",
		"media_cont" => "table",
		"media_caption" => "div",
		"sco_title" => "div",
		"sco_keyw" => "div",
		"sco_desc" => "div",
		"sco_obj" => "div",
		"list_o" => "ol",
		"list_u" => "ul",
		"list_item" => "li",
		"flist_cont" => "div",
		"flist_head" => "div",
		"flist" => "ul",
		"flist_li" => "li",
		"question" => "div",
		"qtitle" => "div",
		"qanswer" => "div",
		"qinput" => "input",
		"qsubmit" => "input",
		"qfeedr" => "div",
		"qfeedw" => "div",
		"page_frame" => "table",
		"page_cont" => "table",
		"page_fn" => "div",
		"page_tnav" => "div",
		"page_bnav" => "div",
		"page_lnav" => "div",
		"page_rnav" => "div",
		"page_lnavlink" => "a",
		"page_rnavlink" => "a",
		"page_lnavimage" => "img",
		"page_rnavimage" => "img",
		"page_title" => "div"
		);
		
	// core styles these styles MUST exists
	public static $core_styles = array(
			array("type" => "text_block", "class" => "Standard"),
			array("type" => "text_block", "class" => "List"),
			array("type" => "text_block", "class" => "TableContent"),
			array("type" => "text_block", "class" => "Headline1"),
			array("type" => "text_block", "class" => "Headline2"),
			array("type" => "text_block", "class" => "Headline3"),
			array("type" => "text_inline", "class" => "Comment"),
			array("type" => "text_inline", "class" => "Emph"),
			array("type" => "text_inline", "class" => "Quotation"),
			array("type" => "text_inline", "class" => "Strong"),
			array("type" => "link", "class" => "IntLink"),
			array("type" => "link", "class" => "ExtLink"),
			array("type" => "link", "class" => "FootnoteLink"),
			array("type" => "media_cont", "class" => "MediaContainer"),
			array("type" => "table", "class" => "StandardTable"),
			array("type" => "media_caption", "class" => "MediaCaption"),
			array("type" => "page_frame", "class" => "PageFrame"),
			array("type" => "page_cont", "class" => "PageContainer"),
			array("type" => "page_tnav", "class" => "TopNavigation"),
			array("type" => "page_bnav", "class" => "BottomNavigation"),
			array("type" => "page_lnav", "class" => "LeftNavigation"),
			array("type" => "page_rnav", "class" => "RightNavigation"),
			array("type" => "page_lnavlink", "class" => "LeftNavigationLink"),
			array("type" => "page_rnavlink", "class" => "RightNavigationLink"),
			array("type" => "page_lnavimage", "class" => "LeftNavigationImage"),
			array("type" => "page_rnavimage", "class" => "RightNavigationImage"),
			array("type" => "page_fn", "class" => "Footnote"),
			array("type" => "page_title", "class" => "PageTitle"),
			array("type" => "sco_title", "class" => "Title"),
			array("type" => "sco_desc", "class" => "Description"),
			array("type" => "sco_keyw", "class" => "Keywords"),
			array("type" => "sco_obj", "class" => "Objective"),
			array("type" => "list_o", "class" => "NumberedList"),
			array("type" => "list_u", "class" => "BulletedList"),
			array("type" => "list_item", "class" => "StandardListItem"),
			array("type" => "question", "class" => "Standard"),
			array("type" => "question", "class" => "SingleChoice"),
			array("type" => "question", "class" => "MultipleChoice"),
			array("type" => "question", "class" => "TextQuestion"),
			array("type" => "question", "class" => "OrderingQuestion"),
			array("type" => "question", "class" => "MatchingQuestion"),
			array("type" => "question", "class" => "ImagemapQuestion"),
			array("type" => "question", "class" => "ClozeTest"),
			array("type" => "qtitle", "class" => "Title"),
			array("type" => "qanswer", "class" => "Answer"),
			array("type" => "qinput", "class" => "Input"),
			array("type" => "qsubmit", "class" => "Submit"),
			array("type" => "qfeedr", "class" => "FeedbackRight"),
			array("type" => "qfeedw", "class" => "FeedbackWrong"),
			array("type" => "flist_cont", "class" => "FileListContainer"),
			array("type" => "flist_head", "class" => "FileListHeading"),
			array("type" => "flist", "class" => "FileList"),
			array("type" => "flist_li", "class" => "FileListItem")
		);
	
	// basic style xml file, image directory and dom
	protected static $basic_style_file = "./Services/Style/basic_style/style.xml";
	protected static $basic_style_image_dir = "./Services/Style/basic_style/images";
	protected static $basic_style_dom;
	
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

	/**
	* Set ref id (show error message, since styles do not use ref ids)
	*/
	function setRefId()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::setRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	/**
	* Get ref id (show error message, since styles do not use ref ids)
	*/
	function getRefId()
	{
		return "";
		//$this->ilias->raiseError("Operation ilObjStyleSheet::getRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	/**
	* Put in tree (show error message, since styles do not use ref ids)
	*/
	function putInTree()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::putInTree() not allowed.",$this->ilias->error_obj->FATAL);
	}

	/**
	* Create a reference (show error message, since styles do not use ref ids)
	*/
	function createReference()
	{
		$this->ilias->raiseError("Operation ilObjStyleSheet::createReference() not allowed.",$this->ilias->error_obj->FATAL);
	}

	/**
	* Set style up to date (false + update will trigger css generation next time)
	*/
	function setUpToDate($a_up_to_date = true)
	{
		$this->up_to_date = $a_up_to_date;
	}
	
	/**
	* Get up to date
	*/
	function getUpToDate()
	{
		return $this->up_to_date;
	}

	/**
	* Set scope
	*/
	function setScope($a_scope)
	{
		$this->scope = $a_scope;
	}
	
	/**
	* Get scope
	*/
	function getScope()
	{
		return $this->scope;
	}

	/**
	* Write up to date
	*/
	function _writeUpToDate($a_id, $a_up_to_date)
	{
		global $ilDB;

		$q = "UPDATE style_data SET uptodate = ".$ilDB->quote((int) $a_up_to_date).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* Looup up to date
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
	* Write standard flag
	*/
	function _writeStandard($a_id, $a_std)
	{
		global $ilDB;

		$q = "UPDATE style_data SET standard = ".$ilDB->quote((int) $a_std).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* Write scope
	*/
	function _writeScope($a_id, $a_scope)
	{
		global $ilDB;

		$q = "UPDATE style_data SET category = ".$ilDB->quote((int) $a_scope).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* Lookup standard flag
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
	* Write active flag
	*/
	function _writeActive($a_id, $a_active)
	{
		global $ilDB;

		$q = "UPDATE style_data SET active = ".$ilDB->quote((int) $a_active).
			" WHERE id = ".$ilDB->quote($a_id);
		$ilDB->query($q);
	}

	/**
	* Lookup active flag
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
	* Get standard styles
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
	* Get all clonable styles (active standard styles and individual learning
	* module styles with write permission).
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
	* Get basic style dom
	*/
	static function _getBasicStyleDom()
	{
		global $ilBench;

		if (!is_object(self::$basic_style_dom))
		{
			self::$basic_style_dom = new DOMDocument();
			self::$basic_style_dom->load(self::$basic_style_file);
		}

		return self::$basic_style_dom;
	}

	/**
	* get meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}

	/**
	* Create a new style
	*/
	function create($a_from_style = 0)
	{
		global $ilDB;
		
		parent::create();

		if ($a_from_style == 0)
		{
			// copy styles from basic style
			$this->createFromXMLFile(self::$basic_style_file, true);
			
			// copy images from basic style
			$this->createImagesDirectory();
			ilUtil::rCopy(self::$basic_style_image_dir,
				$this->getImagesDirectory());
		}
		else
		{
			// get style parameter records
			$def = array();
			$q = "SELECT * FROM style_parameter WHERE style_id = ".$ilDB->quote($a_from_style);
			$par_set = $ilDB->query($q);
			while($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$def[] = array("tag" => $par_rec["tag"], "class" => $par_rec["class"],
					"parameter" => $par_rec["parameter"], "value" => $par_rec["value"],
					"type" => $par_rec["type"]);
			}
			
			// get style characteristics records
			$chars = array();
			$q = "SELECT * FROM style_char WHERE style_id = ".$ilDB->quote($a_from_style);
			$par_set = $ilDB->query($q);
			while($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$chars[] = array("type" => $par_rec["type"], "characteristic" => $par_rec["characteristic"]);
			}

			// default style settings
			foreach ($def as $sty)
			{
				$q = "INSERT INTO style_parameter (style_id, tag, class, parameter, value, type) VALUES ".
					"(".$ilDB->quote($this->getId()).",".
					$ilDB->quote($sty["tag"]).",".
					$ilDB->quote($sty["class"]).",".
					$ilDB->quote($sty["parameter"]).",".
					$ilDB->quote($sty["value"]).",".
					$ilDB->quote($sty["type"]).")";
				$ilDB->query($q);
			}
			
			// insert style characteristics
			foreach ($chars as $char)
			{
				$q = "INSERT INTO style_char (style_id, type, characteristic) VALUES ".
					"(".$ilDB->quote($this->getId()).",".
					$ilDB->quote($char["type"]).",".
					$ilDB->quote($char["characteristic"]).")";
				$ilDB->query($q);
			}
			
			// add style_data record
			$q = "INSERT INTO style_data (id, uptodate, category) VALUES ".
				"(".$ilDB->quote($this->getId()).", 0,".
				$ilDB->quote($this->getScope()).")";
			$ilDB->query($q);
			
			// copy images
			$from_style = new ilObjStyleSheet($a_from_style);
			$this->createImagesDirectory();
			ilUtil::rCopy($from_style->getImagesDirectory(),
				$this->getImagesDirectory());
		}

		$this->read();
		$this->writeCSSFile();
	}
	
	/**
	* Delete Characteristic
	*/
	function deleteCharacteristic($a_type, $a_tag, $a_class)
	{
		global $ilDB;
		
		// check, if characteristic is not a core style
		$core_styles = ilObjStyleSheet::_getCoreStyles();
		if (empty($core_styles[$a_type.".".$a_tag.".".$a_class]))
		{
			// delete characteristic record
			$st = $ilDB->prepareManip("DELETE FROM style_char WHERE style_id = ? AND type = ? AND characteristic = ?",
				array("integer", "text", "text"));
			$ilDB->execute($st, array($this->getId(), $a_type, $a_class));
			
			// delete parameter records
			$st = $ilDB->prepareManip("DELETE FROM style_parameter WHERE style_id = ? AND tag = ? AND type = ? AND class = ?",
				array("integer", "text", "text", "text"));
			$ilDB->execute($st, array($this->getId(), $a_tag, $a_type, $a_class));
		}
		
		$this->setUpToDate(false);
		$this->_writeUpToDate($this->getId(), false);
	}
	
	/**
	* Check whether characteristic exists
	*/
	function characteristicExists($a_char, $a_style_type)
	{
		global $ilDB;
		
		// delete characteristic record
		$st = $ilDB->prepare("SELECT * FROM style_char WHERE style_id = ? AND characteristic = ? AND type = ?",
			array("integer", "text", "text"));
		$set = $ilDB->execute($st, array($this->getId(), $a_char, $a_style_type));
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}
	
	/**
	* Check whether characteristic exists
	*/
	function addCharacteristic($a_type, $a_char)
	{
		global $ilDB;
		
		// delete characteristic record
		$st = $ilDB->prepareManip("INSERT INTO style_char (style_id, type, characteristic)".
			" VALUES (?,?,?) ", array("integer", "text", "text"));
		$ilDB->execute($st, array($this->getId(), $a_type, $a_char));
		
		$this->setUpToDate(false);
		$this->_writeUpToDate($this->getId(), false);
	}

	/**
	* Get characteristics
	*/
	function getCharacteristics($a_type = "")
	{
		if ($a_type == "")
		{
			return $this->chars;
		}
		return $this->chars_by_type[$a_type];
	}
	
	/**
	* Set characteristics
	*/
	function setCharacteristics($a_chars)
	{
		$this->chars = $a_chars;
		// $this->chars_by_type[$a_type];
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
	* @param	string		$a_tag		tag name		(tag.class, e.g. "div.Mnemonic")
	* @param	string		$a_par		tag parameter	(e.g. "margin-left")
	* @param	string		$a_type		style type		(e.g. "section")	
	*/
	function addParameter($a_tag, $a_par, $a_type)
	{
		global $ilDB;
		
		$avail_params = $this->getAvailableParameters();
		$tag = explode(".", $a_tag);
		$value = $avail_params[$a_par][0];
		$q = "INSERT INTO style_parameter (style_id, type, tag, class, parameter, value) VALUES ".
			"(".$ilDB->quote($this->getId()).",".$ilDB->quote($a_type).",".$ilDB->quote($tag[0]).",".
			$ilDB->quote($tag[1]).
			",".$ilDB->quote($a_par).",".$ilDB->quote($value).")";
		$this->ilias->db->query($q);
		$this->read();
		$this->writeCSSFile();
	}

	/**
	* Create images directory
	* <data_dir>/sty/sty_<id>/images
	*/
	function createImagesDirectory()
	{
		return ilObjStyleSheet::_createImagesDirectory($this->getId());
	}
	
	/**
	* Create images directory
	* <data_dir>/sty/sty_<id>/images
	*/
	static function _createImagesDirectory($a_style_id)
	{
		global $ilErr;
		
		$sty_data_dir = ilUtil::getWebspaceDir()."/sty";
		ilUtil::makeDir($sty_data_dir);
		if(!is_writable($sty_data_dir))
		{
			$ilErr->raiseError("Style data directory (".$sty_data_dir
				.") not writeable.", $ilErr->FATAL);
		}
 
		$style_dir = $sty_data_dir."/sty_".$a_style_id;
		ilUtil::makeDir($style_dir);
		if(!@is_dir($style_dir))
		{
			$ilErr->raiseError("Creation of style directory failed (".
				$style_dir.").",$ilErr->FATAL);
		}

		// create images subdirectory
		$im_dir = $style_dir."/images";
		ilUtil::makeDir($im_dir);
		if(!@is_dir($im_dir))
		{
			$ilErr->raiseError("Creation of Import Directory failed (".
				$im_dir.").", $ilErr->FATAL);
		}

		// create thumbnails directory
		$thumb_dir = $style_dir."/images/thumbnails";
		ilUtil::makeDir($thumb_dir);
		if(!@is_dir($thumb_dir))
		{
			$ilErr->raiseError("Creation of Import Directory failed (".
				$thumb_dir.").", $ilErr->FATAL);
		}
	}
	
	/**
	* Get images directory
	*/
	function getImagesDirectory()
	{
		return ilObjStyleSheet::_getImagesDirectory($this->getId());
	}

	/**
	* Get images directory
	*/
	static function _getImagesDirectory($a_style_id)
	{
		return ilUtil::getWebspaceDir()."/sty/sty_".$a_style_id.
			"/images";
	}

	/**
	* Get thumbnails directory
	*/
	function getThumbnailsDirectory()
	{
		return $this->getImagesDirectory().
			"/thumbnails";
	}

	/**
	* Get images of style
	*/
	function getImages()
	{
		$dir = $this->getImagesDirectory();
		$images = array();
		if (is_dir($dir))
		{
			$entries = ilUtil::getDir($dir);
			foreach($entries as $entry)
			{
				if (($entry["entry"] == ".") || ($entry["entry"] == ".."))
				{
					continue;
				}
				if ($entry["type"] != "dir")
				{
					$images[] = $entry;
				}
			}
		}
		
		return $images;
	}
	
	/**
	* Upload image
	*/
	function uploadImage($a_file)
	{
		$this->createImagesDirectory();
		@ilUtil::moveUploadedFile($a_file["tmp_name"], $a_file["name"],
			$this->getImagesDirectory()."/".$a_file["name"]);
		@ilUtil::resizeImage($this->getImagesDirectory()."/".$a_file["name"],
			$this->getThumbnailsDirectory()."/".$a_file["name"], 75, 75);
	}
	
	/**
	* Delete an image
	*/
	function deleteImage($a_file)
	{
		if (is_file($this->getImagesDirectory()."/".$a_file))
		{
			unlink($this->getImagesDirectory()."/".$a_file);
		}
		if (is_file($this->getThumbnailsDirectory()."/".$a_file))
		{
			unlink($this->getThumbnailsDirectory()."/".$a_file);
		}
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
	function deleteStylePar($a_tag, $a_class, $a_par, $a_type)
	{
		global $ilDB;
		
		$q = "DELETE FROM style_parameter WHERE ".
			" style_id = ".$ilDB->quote($this->getId())." AND ".
			" tag = ".$ilDB->quote($a_tag)." AND ".
			" class = ".$ilDB->quote($a_class)." AND ".
			" type = ".$ilDB->quote($a_type)." AND ".
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
			$ilDB->quote($this->getId())." ORDER BY tag, class, type ";
		$style_set = $this->ilias->db->query($q);
		$ctag = "";
		$cclass = "";
		$ctype = "";
		$this->style = array();
		while($style_rec = $style_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($style_rec["tag"] != $ctag || $style_rec["class"] != $cclass
				|| $style_rec["type"] != $ctype)
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
			$ctype = $style_rec["type"];
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
		
		// get style characteristics records
		$this->chars = array();
		$this->chars_by_type = array();
		$q = "SELECT * FROM style_char WHERE style_id = ".$ilDB->quote($this->getId()).
			" ORDER BY type ASC, characteristic ASC";
		$par_set = $ilDB->query($q);
		while($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->chars[] = array("type" => $par_rec["type"], "class" => $par_rec["characteristic"]);
			$this->chars_by_type[$par_rec["type"]][] = $par_rec["characteristic"];
		}
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
			fwrite ($css_file, $tag[0]["tag"].".ilc_".$tag[0]["type"]."_".$tag[0]["class"]."\n");
			fwrite ($css_file, "{\n");

			// collect table border attributes
			$t_border = array();

			foreach($tag as $par)
			{
				$cur_par = $par["parameter"];
				$cur_val = $par["value"];
				
				if ($tag[0]["type"] == "table" && is_int(strpos($par["parameter"], "border")))
				{
					$t_border[$cur_par] = $cur_val;
				}
				
				if (in_array($cur_par, array("background-image", "list-style-image")))
				{
					if (is_int(strpos($cur_val, "/")))	// external
					{
						$cur_val = "url(".$cur_val.")";
					}
					else		// internal
					{
						$cur_val = "url(../sty/sty_".$this->getId()."/images/".$cur_val.")";
					}
				}
				
				if ($cur_par == "opacity")
				{
					$cur_val = ((int) $cur_val) / 100;
				}
				
				fwrite ($css_file, "\t".$cur_par.": ".$cur_val.";\n");
				
				// IE6 fix for minimum height
				if ($cur_par == "min-height")
				{
					fwrite ($css_file, "\t"."height".": "."auto !important".";\n");
					fwrite ($css_file, "\t"."height".": ".$cur_val.";\n");
				}
				
				// opacity fix
				if ($cur_par == "opacity")
				{
					fwrite ($css_file, "\t".'-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity='.($cur_val * 100).')"'.";\n");
					fwrite ($css_file, "\t".'filter: alpha(opacity='.($cur_val * 100).')'.";\n");
					fwrite ($css_file, "\t".'-moz-opacity: '.$cur_val.";\n");
				}
				
				// save page background
				if ($tag[0]["tag"] == "div" && $tag[0]["class"] == "Page"
					&& $cur_par == "background-color")
				{
					$page_background = $cur_val;
				}
			}
			fwrite ($css_file, "}\n");
			fwrite ($css_file, "\n");
			
			// use table border attributes for th td as well
			if ($tag[0]["type"] == "table")
			{
				if (count($t_border) > 0)
				{
					fwrite ($css_file, $tag[0]["tag"].".ilc_".$tag[0]["type"]."_".$tag[0]["class"]." th,".
						$tag[0]["tag"].".ilc_".$tag[0]["type"]."_".$tag[0]["class"]." td\n");
					fwrite ($css_file, "{\n");
					foreach ($t_border as $p => $v)
					{
//						fwrite ($css_file, "\t".$p.": ".$v.";\n");
					}
					fwrite ($css_file, "}\n");
					fwrite ($css_file, "\n");
				}
			}
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
	function replaceStylePar($a_tag, $a_class, $a_par, $a_val, $a_type)
	{
		global $ilDB;
		
//echo "<br>A*$a_type*";
		
		$q = "SELECT * FROM style_parameter WHERE ".
			" style_id = ".$ilDB->quote($this->getId())." AND ".
			" tag = ".$ilDB->quote($a_tag)." AND ".
			" class = ".$ilDB->quote($a_class)." AND ".
			" type = ".$ilDB->quote($a_type)." AND ".
			" parameter = ".$ilDB->quote($a_par);
		
		$set = $ilDB->query($q);
		
		if ($rec = $set->fetchRow())
		{
			$q = "UPDATE style_parameter SET ".
				" value = ".$ilDB->quote($a_val)." WHERE ".
				" style_id = ".$ilDB->quote($this->getId())." AND ".
				" tag = ".$ilDB->quote($a_tag)." AND ".
				" class = ".$ilDB->quote($a_class)." AND ".
				" type = ".$ilDB->quote($a_type)." AND ".
				" parameter = ".$ilDB->quote($a_par);

			$ilDB->query($q);
		}
		else
		{
			$q = "INSERT INTO style_parameter (value, style_id, tag,  class, type, parameter) VALUES ".
				" (".$ilDB->quote($a_val).",".
				" ".$ilDB->quote($this->getId()).",".
				" ".$ilDB->quote($a_tag).",".
				" ".$ilDB->quote($a_class).",".
				" ".$ilDB->quote($a_type).",".
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
		$xml.= "<StyleSheet>\n";
		$xml.= "<Title>".$this->getTitle()."</Title>";
		$xml.= "<Description>".$this->getDescription()."</Description>\n";
		foreach($this->chars as $char)
		{
			$xml.= "<Style Tag=\"".ilObjStyleSheet::_determineTag($char["type"]).
				"\" Type=\"".$char["type"]."\" Class=\"".$char["class"]."\">\n";
			foreach($this->style as $style)
			{
				if ($style[0]["type"] == $char["type"] && $style[0]["class"] == $char["class"])
				{
					foreach($style as $tag)
					{
						$xml.="<StyleParameter Name=\"".$tag["parameter"]."\" Value=\"".$tag["value"]."\"/>\n";
					}
				}
			}
			$xml.= "</Style>\n";
		}
		$xml.= "</StyleSheet>";
//echo "<pre>".htmlentities($xml)."</pre>";
		return $xml;
	}
	
	
	/**
	* Create export directory
	*/
	function createExportDirectory()
	{
		$sty_data_dir = ilUtil::getDataDir()."/sty";
		ilUtil::makeDir($sty_data_dir);
		if(!is_writable($sty_data_dir))
		{
			$this->ilias->raiseError("Style data directory (".$sty_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
 
		$style_dir = $sty_data_dir."/sty_".$this->getId();
		ilUtil::makeDir($style_dir);
		if(!@is_dir($style_dir))
		{
			$this->ilias->raiseError("Creation of style directory failed (".
				$style_dir.").",$this->ilias->error_obj->FATAL);
		}

		// create export subdirectory
		$ex_dir = $style_dir."/export";
		ilUtil::makeDir($ex_dir);
		if(!@is_dir($ex_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed (".
				$ex_dir.").",$this->ilias->error_obj->FATAL);
		}
		
		return $ex_dir;
	}
	
	/**
	* Create export directory
	*/
	function createExportSubDirectory()
	{
		$ex_dir = $this->createExportDirectory();
		$ex_sub_dir = $ex_dir."/".$this->getExportSubDir();
		ilUtil::makeDir($ex_sub_dir);
		if(!is_writable($ex_sub_dir))
		{
			$this->ilias->raiseError("Style data directory (".$ex_sub_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		$ex_sub_images_dir = $ex_sub_dir."/images";
		ilUtil::makeDir($ex_sub_images_dir);
		if(!is_writable($ex_sub_images_dir))
		{
			$this->ilias->raiseError("Style data directory (".$ex_sub_images_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
	}
	
	/**
	* The local directory, that will be included within the zip file
	*/
	function getExportSubDir()
	{
		return "sty_".$this->getId();
	}
	
	/**
	* Create export file
	*
	* @return	string		local file name of export file
	*/
	function export()
	{
		$ex_dir = $this->createExportDirectory();
		$this->createExportSubDirectory();
		$this->exportXML($ex_dir."/".$this->getExportSubDir());
//echo "-".$this->getImagesDirectory()."-".$ex_dir."/".$this->getExportSubDir()."/images"."-";
		ilUtil::rCopy($this->getImagesDirectory(),
			$ex_dir."/".$this->getExportSubDir()."/images");
		if (is_file($ex_dir."/".$this->getExportSubDir().".zip"))
		{
			unlink($ex_dir."/".$this->getExportSubDir().".zip");
		}
		ilUtil::zip($ex_dir."/".$this->getExportSubDir(),
			$ex_dir."/".$this->getExportSubDir().".zip");

		return $ex_dir."/".$this->getExportSubDir().".zip";
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
	* Create import directory
	*/
	function createImportDirectory()
	{
		$sty_data_dir = ilUtil::getDataDir()."/sty";
		ilUtil::makeDir($sty_data_dir);
		if(!is_writable($sty_data_dir))
		{
			$this->ilias->raiseError("Style data directory (".$sty_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
 
		$style_dir = $sty_data_dir."/sty_".$this->getId();
		ilUtil::makeDir($style_dir);
		if(!@is_dir($style_dir))
		{
			$this->ilias->raiseError("Creation of style directory failed (".
				$style_dir.").",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory
		$im_dir = $style_dir."/import";
		ilUtil::makeDir($im_dir);
		if(!@is_dir($im_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed (".
				$im_dir.").",$this->ilias->error_obj->FATAL);
		}

		return $im_dir;
	}

	/**
	* Import 
	*/
	function import($a_file)
	{
		parent::create();
		
		$im_dir = $this->createImportDirectory();
		$file = pathinfo($a_file["name"]);
		ilUtil::moveUploadedFile($a_file["tmp_name"],
			$a_file["name"], $im_dir."/".$a_file["name"]);

		// unzip file
		ilUtil::unzip($im_dir."/".$a_file["name"]);

		// load information from xml file
		$subdir = basename($file["basename"],".".$file["extension"]);
		$xml_file = $im_dir."/".$subdir."/style.xml";
		$this->createFromXMLFile($xml_file, true);
		
		// copy images
		$this->createImagesDirectory();
		ilUtil::rCopy($im_dir."/".$subdir."/images",
			$this->getImagesDirectory());

	}
	
	/**
	* create style from xml file
	*/
	function createFromXMLFile($a_file, $a_skip_parent_create = false)
	{
		global $ilDB;
		
		if (!$a_skip_parent_create)
		{
			parent::create();
		}
		include_once("./Services/Style/classes/class.ilStyleImportParser.php");
		$importParser = new ilStyleImportParser($a_file, $this);
		$importParser->startParsing();
		
		// store style parameter
		foreach ($this->style as $style)
		{
			foreach($style as $tag)
			{
				$q = "INSERT INTO style_parameter (style_id, tag, class, parameter, type, value) VALUES ".
					"(".$ilDB->quote($this->getId()).",".
					$ilDB->quote($tag["tag"]).",".
					$ilDB->quote($tag["class"]).
					",".$ilDB->quote($tag["parameter"]).",".
					$ilDB->quote($tag["type"]).",".
					$ilDB->quote($tag["value"]).")";
				$this->ilias->db->query($q);
			}
		}
		
		// store characteristics
		foreach ($this->chars as $char)
		{
			$q = "INSERT INTO style_char (style_id, type, characteristic) VALUES ".
				"(".$ilDB->quote($this->getId()).",".
				$ilDB->quote($char["type"]).",".
				$ilDB->quote($char["class"]).")";
			$this->ilias->db->query($q);
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
	* Get grouped parameter
	*/
	function getStyleParameterGroups()
	{
		$groups = array();
		
		foreach (self::$parameter as $parameter => $props)
		{
			$groups[$props["group"]][] = $parameter;
		}
		return $groups;
	}
	
	static function _getStyleParameterInputType($par)
	{
		$input = self::$parameter[$par]["input"];
		return $input;
	}
	
	static function _getStyleParameterSubPar($par)
	{
		$subpar = self::$parameter[$par]["subpar"];
		return $subpar;
	}

	static function _getStyleParameters($a_tag = "")
	{
		if ($a_tag == "")
		{
			return self::$parameter;
		}
		$par = array();
		foreach (self::$parameter as $k => $v)
		{
			if (is_array(self::$filtered_groups[$v["group"]]) &&
				!in_array($a_tag, self::$filtered_groups[$v["group"]]))
			{
				continue;
			}
			$par[$k] = $v;
		}
		return $par;
	}
	
	static function _getFilteredGroups()
	{
		return self::$filtered_groups;
	}

	static function _getStyleParameterNumericUnits($a_no_percentage = false)
	{
		if ($a_no_percentage)
		{
			return self::$num_unit_no_perc;
		}
		return self::$num_unit;
	}
	
	static function _getStyleParameterValues($par)
	{
		return self::$parameter[$par]["values"];
	}
	
	/*static function _getStyleTypes()
	{
		return self::$style_types;
	}*/

	static function _getStyleSuperTypes()
	{
		return self::$style_super_types;
	}
	
	static function _isExpandable($a_type)
	{
		return in_array($a_type, self::$expandable_types);
	}

	static function _getStyleSuperTypeForType($a_type)
	{
		foreach (self::$style_super_types as $s => $t)
		{
			if (in_array($a_type, $t))
			{
				return $s;
			}
			if ($a_type == $s)
			{
				return $s;
			}
		}
	}

	/**
	* Get core styles
	*/
	static function _getCoreStyles()
	{
		$c_styles = array();
		foreach (self::$core_styles as $cstyle)
		{
			$c_styles[$cstyle["type"].".".ilObjStyleSheet::_determineTag($cstyle["type"]).".".$cstyle["class"]]
				= array("type" => $cstyle["type"],
					"tag" => ilObjStyleSheet::_determineTag($cstyle["type"]),
					"class" => $cstyle["class"]);
		}
		return $c_styles;
	}
	
	static function _determineTag($a_type)
	{
		return self::$assigned_tags[$a_type];
	}
	
	/**
	* Get available parameters
	*/
	static function getAvailableParameters()
	{
		$pars = array();
		foreach(self::$parameter as $p => $v)
		{
			$pars[$p] = $v["values"];
		}
		
		return $pars;
	}
	
	/**
	* Add missing style classes to all styles
	*/
	static function _addMissingStyleClassesToAllStyles()
	{
		global $ilDB;
		
		$styles = ilObject::_getObjectsDataForType("sty");
		$core_styles = ilObjStyleSheet::_getCoreStyles();
		$bdom = ilObjStyleSheet::_getBasicStyleDom();
		
		// get all core image files
		$core_images = array();
		$core_dir = self::$basic_style_image_dir;
		if (is_dir($core_dir))
		{
			$dir = opendir($core_dir);
			while($file = readdir($dir))
			{
				if (substr($file, 0, 1) != "." && is_file($core_dir."/".$file))
				{
					$core_images[] = $file;
				}
			}
		}
		
		foreach ($styles as $style)
		{
			$id = $style["id"];
			
			foreach($core_styles as $cs)
			{
				// check, whether core style class exists
				$st = $ilDB->prepare("SELECT * FROM style_char WHERE style_id = ? ".
					"AND type = ? AND characteristic = ?",
					array("integer", "text", "text"));
				$set = $ilDB->execute($st, array($id, $cs["type"], $cs["class"]));
				
				// if not, add core style class
				if (!($rec = $ilDB->fetchAssoc($set)))
				{
					$st = $ilDB->prepareManip("INSERT INTO style_char (style_id, type, characteristic) ".
						" VALUES (?,?,?) ", array("integer", "text", "text"));
					$ilDB->execute($st, array($id, $cs["type"], $cs["class"]));
					
					$xpath = new DOMXPath($bdom);
					$par_nodes = $xpath->query("/StyleSheet/Style[@Tag = '".$cs["tag"]."' and @Type='".
						$cs["type"]."' and @Class='".$cs["class"]."']/StyleParameter");
					foreach ($par_nodes as $par_node)
					{
						// check whether style parameter exists
						$st = $ilDB->prepare("SELECT * FROM style_parameter WHERE style_id = ? ".
							"AND type = ? AND class = ? AND tag = ? AND parameter = ?",
							array("integer", "text", "text", "text", "text"));
						$set = $ilDB->execute($st, array($id, $cs["type"], $cs["class"],
							$cs["tag"], $par_node->getAttribute("Name")));
							
						// if not, create style parameter
						if (!($rec = $ilDB->fetchAssoc($set)))
						{
							$st = $ilDB->prepareManip("INSERT INTO style_parameter (style_id, type, class, tag, parameter, value) ".
								" VALUES (?,?,?,?,?,?)", array("integer", "text", "text", "text", "text", "text"));
							$ilDB->execute($st, array($id, $cs["type"], $cs["class"], $cs["tag"],
								$par_node->getAttribute("Name"), $par_node->getAttribute("Value")));
						}
					}
				}
			}
			
			// now check, whether some core image files are missing
			ilObjStyleSheet::_createImagesDirectory($id);
			$imdir = ilObjStyleSheet::_getImagesDirectory($id);
			reset($core_images);
			foreach($core_images as $cim)
			{
				if (!is_file($imdir."/".$cim))
				{
					copy($core_dir."/".$cim, $imdir."/".$cim);
				}
			}
		}
	}
	
} // END class.ilObjStyleSheet
?>
