<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Style Migration Class (->3.11)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilStyleMigration
{
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
	protected static $basic_style_file = "./Services/Migration/DBUpdate_1385/basic_style/style.xml";
	protected static $basic_style_image_dir = "./Services/Migration/DBUpdate_1385/basic_style/images";
	protected static $basic_style_dom;
	
	/**
	* Add missing style characteristics to styles
	*/
	function addMissingStyleCharacteristics($a_id = "")
	{
		global $ilDB;
		
		$add_str = "";
		if ($a_id != "")
		{
			$add_str = " AND style_id = ".$ilDB->quote($a_id, "integer");
		}

		$set = $ilDB->query($q = "SELECT DISTINCT style_id, tag, class FROM style_parameter WHERE ".
			$ilDB->equals("type", "", "text", true)." ".$add_str);

		while ($rec = $ilDB->fetchAssoc($set))
		{
			// derive types from tag
			$types = array();
			switch ($rec["tag"])
			{
				case "div":
				case "p":
					if (in_array($rec["class"], array("Headline3", "Headline1",
						"Headline2", "TableContent", "List", "Standard", "Remark",
						"Additional", "Mnemonic", "Citation", "Example")))
					{
						$types[] = "text_block";
					}
					if (in_array($rec["class"], array("Block", "Remark",
						"Additional", "Mnemonic", "Example", "Excursus", "Special")))
					{
						$types[] = "section";
					}
					if (in_array($rec["class"], array("Page", "Footnote", "PageTitle", "LMNavigation")))
					{
						$types[] = "page";
					}
					break;
					
				case "td":
					$types[] = "table_cell";
					break;
					
				case "a":
					if (in_array($rec["class"], array("ExtLink", "IntLink", "FootnoteLink")))
					{
						$types[] = "link";
					}
					break;

				case "span":
					$types[] = "text_inline";
					break;

				case "table":
					$types[] = "table";
					break;
					
				default:
					$types[] = array();
					break;
			}

			// check if style_char set exists
			foreach ($types as $t)
			{
				// check if second type already exists
				$set4 = $ilDB->queryF("SELECT * FROM style_char ".
					" WHERE style_id = %s AND type = %s AND characteristic = %s",
					array("integer", "text", "text"),
					array($rec["style_id"], $t, $rec["class"]));
				if ($rec4 = $ilDB->fetchAssoc($set4))
				{
					// ok
				}
				else
				{
//echo "<br>1-".$rec["style_id"]."-".$t."-".$rec["class"]."-";
					$ilDB->manipulateF("INSERT INTO style_char ".
						" (style_id, type, characteristic) VALUES ".
						" (%s,%s,%s) ",
						array("integer", "text", "text"),
						array($rec["style_id"], $t, $rec["class"]));
				}
			}
			
			// update types
			if ($rec["type"] == "")
			{
				if (count($types) > 0)
				{
					$ilDB->manipulateF("UPDATE style_parameter SET type = %s ".
						" WHERE style_id = %s AND class = %s AND ".$ilDB->equals("type", "", "text", true),
						array("text", "integer", "text"),
						array($types[0], $rec["style_id"], $rec["class"]));

					// links extra handling
					if ($types[0] == "link")
					{
						$ilDB->manipulateF("UPDATE style_parameter SET type = %s ".
							" WHERE style_id = %s AND (class = %s OR class = %s) AND ".$ilDB->equals("type", "", "text", true),
							array("text", "integer", "text", "text"),
							array($types[0], $rec["style_id"], $rec["class"].":visited",
							$rec["class"].":hover"));
//echo "<br>4-".$types[0]."-".$rec["style_id"]."-".$rec["class"].":visited"."-".
//	$rec["class"].":hover";
					}
				}

				if (count($types) == 2)
				{
					// select all records of first type and add second type 
					// records if necessary.
					$set2 = $ilDB->queryF("SELECT * FROM style_parameter ".
						" WHERE style_id = %s AND class = %s AND type = %s",
						array("integer", "text", "text"),
						array($rec["style_id"], $rec["class"], $types[0]));
					while ($rec2 = $ilDB->fetchAssoc($set2))
					{
						// check if second type already exists
						$set3 = $ilDB->queryF("SELECT * FROM style_parameter ".
							" WHERE style_id = %s AND tag = %s AND class = %s AND type = %s AND parameter = %s",
							array("integer", "text", "text", "text", "text"),
							array($rec["style_id"], $rec["tag"], $rec["class"], $types[1], $rec["parameter"]));
						if ($rec3 = $ilDB->fetchAssoc($set3))
						{
							// ok
						}
						else
						{
							$ilDB->manipulateF("INSERT INTO style_parameter ".
								" (style_id, tag, class, parameter, value, type) VALUES ".
								" (%s,%s,%s,%s,%s,%s) ",
								array("integer", "text", "text", "text", "text", "text"),
								array($rec2["style_id"], $rec2["tag"], $rec2["class"],
									$rec2["parameter"], $rec2["value"], $types[1]));
						}
					}
				}
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
			$c_styles[$cstyle["type"].".".ilStyleMigration::_determineTag($cstyle["type"]).".".$cstyle["class"]]
				= array("type" => $cstyle["type"],
					"tag" => ilStyleMigration::_determineTag($cstyle["type"]),
					"class" => $cstyle["class"]);
		}
		return $c_styles;
	}

	static function _determineTag($a_type)
	{
		return self::$assigned_tags[$a_type];
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
	* Create images directory
	* <data_dir>/sty/sty_<id>/images
	*/
	static function _createImagesDirectory($a_style_id)
	{
		global $ilErr;
		
		$sty_data_dir = CLIENT_WEB_DIR."/sty";
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
	static function _getImagesDirectory($a_style_id)
	{
		return CLIENT_WEB_DIR."/sty/sty_".$a_style_id.
			"/images";
	}

	/**
	* Add missing style classes to all styles
	*/
	static function _addMissingStyleClassesToAllStyles()
	{
		global $ilDB;
		
		$core_styles = ilStyleMigration::_getCoreStyles();
		$bdom = ilStyleMigration::_getBasicStyleDom();
		
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
		
		// check, whether core style class exists
		$sets = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
		
		while ($recs = $ilDB->fetchAssoc($sets))
		{
			$id = $recs["obj_id"];
			
			foreach($core_styles as $cs)
			{
				// check, whether core style class exists
				$set = $ilDB->queryF("SELECT * FROM style_char WHERE style_id = %s ".
					"AND type = %s AND characteristic = %s",
					array("integer", "text", "text"),
					array($id, $cs["type"], $cs["class"]));
				
				// if not, add core style class
				if (!($rec = $ilDB->fetchAssoc($set)))
				{
					$ilDB->manipulateF("INSERT INTO style_char (style_id, type, characteristic) ".
						" VALUES (%s,%s,%s) ",
						array("integer", "text", "text"),
						array($id, $cs["type"], $cs["class"]));
					
					$xpath = new DOMXPath($bdom);
					$par_nodes = $xpath->query("/StyleSheet/Style[@Tag = '".$cs["tag"]."' and @Type='".
						$cs["type"]."' and @Class='".$cs["class"]."']/StyleParameter");
					foreach ($par_nodes as $par_node)
					{
						// check whether style parameter exists
						$set = $ilDB->queryF("SELECT * FROM style_parameter WHERE style_id = %s ".
							"AND type = %s AND class = %s AND tag = %s AND parameter = %s",
							array("integer", "text", "text", "text", "text"),
							array($id, $cs["type"], $cs["class"],
							$cs["tag"], $par_node->getAttribute("Name")));
							
						// if not, create style parameter
						if (!($rec = $ilDB->fetchAssoc($set)))
						{
							$ilDB->manipulateF("INSERT INTO style_parameter (style_id, type, class, tag, parameter, value) ".
								" VALUES (%s,%s,%s,%s,%s,%s)",
								array("integer", "text", "text", "text", "text", "text"),
								array($id, $cs["type"], $cs["class"], $cs["tag"],
								$par_node->getAttribute("Name"), $par_node->getAttribute("Value")));
						}
					}
				}
			}
			
			// now check, whether some core image files are missing
			ilStyleMigration::_createImagesDirectory($id);
			$imdir = ilStyleMigration::_getImagesDirectory($id);
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

}
?>
