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

require_once ("content/classes/Pages/class.ilPageContentGUI.php");
require_once ("content/classes/Pages/class.ilMediaObject.php");
require_once ("content/classes/Pages/class.ilMediaAliasItem.php");

/**
* Class ilMediaObjectGUI
*
* Editing User Interface for MediaObjects within LMs (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
// Todo: extend ilObjMediaObjectGUI !?
class ilMediaObjectGUI extends ilPageContentGUI
{

	function ilMediaObjectGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id = 0)
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id);
	}


	////
	// The following methods are for editing MediaAliases in PageObjects
	// not the object itself
	////

	/**
	* insert new media object form
	*/
	function insert($a_post_cmd = "edpost", $a_submit_cmd = "create_mob")
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_new.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_mob"));
		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=$a_post_cmd"));

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		//header('Content-type: text/html; charset=UTF-8');

		// select fields for number of columns
		$this->tpl->setVariable("TXT_STANDARD_VIEW", $this->lng->txt("cont_std_view"));
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("cont_file"));
		$this->tpl->setVariable("TXT_REFERENCE", $this->lng->txt("cont_reference"));
		$this->tpl->setVariable("TXT_REF_HELPTEXT", $this->lng->txt("cont_ref_helptext"));
		$this->tpl->setVariable("TXT_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("TXT_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("TXT_ORIGINAL_SIZE", $this->lng->txt("cont_orig_size"));
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("TXT_FULLSCREEN_VIEW", $this->lng->txt("cont_fullscreen"));
		$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("cont_parameter"));
		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", $a_submit_cmd);
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* create new media object in dom and update page in db
	*/
	function &create($a_create_alias = true)
	{
		// create dummy object in db (we need an id)
		$this->content_obj = new ilMediaObject();
		$dummy_meta =& new ilMetaData();
		$dummy_meta->setObject($this->content_obj);
		$this->content_obj->assignMetaData($dummy_meta);
		$this->content_obj->setTitle("dummy");
		$this->content_obj->setDescription("dummy");
		$this->content_obj->create();

		// determine and create mob directory, move uploaded file to directory
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
//		$mob_dir = $this->ilias->ini->readVariable("server","webspace_dir")."/mobs/mm_".$this->content_obj->getId();
		ilUtil::createDirectory($mob_dir);

		$media_item =& new ilMediaItem();
		$this->content_obj->addMediaItem($media_item);
		$media_item->setPurpose("Standard");
		$meta =& $this->content_obj->getMetaData();
		$meta_technical =& new ilMetaTechnical($meta);

		if ($_POST["standard_type"] == "File")
		{
			$file = $mob_dir."/".$_FILES['standard_file']['name'];
			move_uploaded_file($_FILES['standard_file']['tmp_name'], $file);

			// set real meta and object data
			$format = ilMediaObject::getMimeType($file);
			$media_item->setFormat($format);
			$media_item->setLocation($_FILES['standard_file']['name']);
			$media_item->setLocationType("LocalFile");
			$meta_technical->addFormat($format);
			$meta_technical->setSize($_FILES['standard_file']['size']);
			$meta_technical->addLocation("LocalFile", $_FILES['standard_file']['name']);
			$this->content_obj->setTitle($_FILES['standard_file']['name']);
		}
		else	// standard type: reference
		{
			$format = ilMediaObject::getMimeType($_POST["standard_reference"]);
			$media_item->setFormat($format);
			$media_item->setLocation($_POST["standard_reference"]);
			$media_item->setLocationType("Reference");
			$meta_technical->addFormat($format);
			$meta_technical->setSize(0);
			$meta_technical->addLocation("Reference", $_POST["standard_reference"]);
			$this->content_obj->setTitle($_POST["standard_reference"]);
		}
		$meta->addTechnicalSection($meta_technical);
		$this->content_obj->setDescription($format);

		// determine width and height of known image types
		if ($_POST["standard_size"] == "original")
		{
			if (($format == "image/gif") || ($format == "image/jpeg") ||
				($format == "image/png") || ($format == "application/x-shockwave-flash") ||
				($format == "image/tiff") || ($format == "image/x-ms-bmp") ||
				($format == "image/psd") || ($format == "image/iff"))
			{
				$size = getimagesize($file);
				$media_item->setWidth($size[0]);
				$media_item->setHeight($size[1]);
			}
		}
		else
		{
			$media_item->setWidth($_POST["standard_width"]);
			$media_item->setHeight($_POST["standard_height"]);
		}

		if ($_POST["standard_caption"] != "")
		{
			$media_item->setCaption($_POST["standard_caption"]);
		}

		if ($_POST["standard_param"] != "")
		{
			$media_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["standard_param"])));
		}

		$media_item->setHAlign("Left");

		// fullscreen view
		if ($_POST["fullscreen"] == "y")
		{
			$media_item =& new ilMediaItem();
			$this->content_obj->addMediaItem($media_item);
			$media_item->setPurpose("Fullscreen");

			// file
			if ($_POST["full_type"] == "File")
			{
				if ($_FILES['full_file']['name'] != "")
				{
					$file = $mob_dir."/".$_FILES['full_file']['name'];
					move_uploaded_file($_FILES['full_file']['tmp_name'], $file);

					// set real meta and object data
					$format = ilMediaObject::getMimeType($file);
					$media_item->setFormat($format);
					$media_item->setLocation($_FILES['full_file']['name']);
					$media_item->setLocationType("LocalFile");
					$meta_technical->addFormat($format);
					$meta_technical->setSize($meta_technical->getSize()
					 + $_FILES['full_file']['size']);
					$meta_technical->addLocation("LocalFile", $_FILES['full_file']['name']);
				}
			}
			else	// reference
			{
				if ($_POST["full_reference"] != "")
				{
					$format = ilMediaObject::getMimeType($_POST["full_reference"]);
					$media_item->setFormat($format);
					$media_item->setLocation($_POST["full_reference"]);
					$media_item->setLocationType("Reference");
					$meta_technical->addFormat($format);
					$meta_technical->addLocation("Reference", $_POST["full_reference"]);
				}
			}

			// determine width and height of known image types
			if ($_POST["full_size"] == "original")
			{
				if (($format == "image/gif") || ($format == "image/jpeg") ||
					($format == "image/png") || ($format == "application/x-shockwave-flash") ||
					($format == "image/tiff") || ($format == "image/x-ms-bmp") ||
					($format == "image/psd") || ($format == "image/iff"))
				{
					$size = getimagesize($file);
					$media_item->setWidth($size[0]);
					$media_item->setHeight($size[1]);
				}
			}
			else
			{
				$media_item->setWidth($_POST["full_width"]);
				$media_item->setHeight($_POST["full_height"]);
			}

			if ($_POST["full_caption"] != "")
			{
				$media_item->setCaption($_POST["full_caption"]);
			}

			if ($_POST["full_param"] != "")
			{
				$media_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["full_param"])));
			}

		}

		$this->content_obj->update();

		if ($a_create_alias)
		{
			$this->content_obj->setDom($this->dom);
			$this->content_obj->createAlias($this->pg_obj, $this->hier_id);
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				header("Location: ".$this->getReturnLocation());
				exit;
			}
			else
			{
				$this->insert();
			}
		}
		else
		{
			return $this->content_obj;
		}
	}



	/**
	* edit properties form
	*/
	function editAlias()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// edit object button
		/*
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","lm_edit.php?ref_id=".
			$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&hier_id=".$this->hier_id.
			"&cmd=edit");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_edit_mob"));
		$this->tpl->parseCurrentBlock();*/

		//$item_nr = $this->content_obj->getMediaItemNr("Standard");
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_item =& $this->content_obj->getMediaItem("Standard");
//echo htmlentities($this->dom->dump_node($std_alias_item->item_node));
		// edit media alias template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_alias_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_alias_prop"));
		$this->tpl->setVariable("TXT_STANDARD_VIEW", $this->lng->txt("cont_std_view"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_".$std_item->getLocationType()));
		$this->tpl->setVariable("TXT_LOCATION", $std_item->getLocation());
		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=edpost"));

		$this->displayValidationError();

		// width
		$this->tpl->setVariable("TXT_MOB_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("INPUT_MOB_WIDTH", "mob_width");
		$this->tpl->setVariable("VAL_MOB_WIDTH", $std_alias_item->getWidth());

		// height
		$this->tpl->setVariable("TXT_MOB_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("INPUT_MOB_HEIGHT", "mob_height");
		$this->tpl->setVariable("VAL_MOB_HEIGHT", $std_alias_item->getHeight());

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "mob_caption");
		$this->tpl->setVariable("VAL_CAPTION", $std_alias_item->getCaption());
		$this->tpl->parseCurrentBlock();

		// parameters
		$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("cont_parameter"));
		$this->tpl->setVariable("INPUT_PARAMETERS", "mob_parameters");
		$this->tpl->setVariable("VAL_PARAMETERS", $std_alias_item->getParameterString());
		$this->tpl->parseCurrentBlock();

		// fullscreen view
		if($this->content_obj->hasFullScreenItem())
		{
			$this->tpl->setCurrentBlock("fullscreen");
			$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen");
			$full_item =& $this->content_obj->getMediaItem("Fullscreen");

			// edit media alias template
			$this->tpl->setVariable("TXT_FULLSCREEN_VIEW", $this->lng->txt("cont_fullscreen"));
			$this->tpl->setVariable("TXT_FULL_TYPE", $this->lng->txt("cont_".$full_item->getLocationType()));
			$this->tpl->setVariable("TXT_FULL_LOCATION", $full_item->getLocation());

			// width
			$this->tpl->setVariable("TXT_FULL_WIDTH", $this->lng->txt("cont_width"));
			$this->tpl->setVariable("INPUT_FULL_WIDTH", "full_width");
			$this->tpl->setVariable("VAL_FULL_WIDTH", $full_alias_item->getWidth());

			// height
			$this->tpl->setVariable("TXT_FULL_HEIGHT", $this->lng->txt("cont_height"));
			$this->tpl->setVariable("INPUT_FULL_HEIGHT", "full_height");
			$this->tpl->setVariable("VAL_FULL_HEIGHT", $full_alias_item->getHeight());

			// caption
			$this->tpl->setVariable("TXT_FULL_CAPTION", $this->lng->txt("cont_caption"));
			$this->tpl->setVariable("INPUT_FULL_CAPTION", "full_caption");
			$this->tpl->setVariable("VAL_FULL_CAPTION", $full_alias_item->getCaption());

			// parameters
			$this->tpl->setVariable("TXT_FULL_PARAMETER", $this->lng->txt("cont_parameter"));
			$this->tpl->setVariable("INPUT_FULL_PARAMETERS", "full_parameters");
			$this->tpl->setVariable("VAL_FULL_PARAMETERS", $full_alias_item->getParameterString());

			$this->tpl->parseCurrentBlock();
		}

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveAliasProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* save table properties in db and return to page edit screen
	*/
	function saveAliasProperties()
	{
		$std_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$full_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen");

		$std_item->setWidth($_POST["mob_width"]);
		$std_item->setHeight($_POST["mob_height"]);
		$std_item->setCaption($_POST["mob_caption"]);
		$std_item->setParameters(ilUtil::extractParameterString(ilUtil::stripSlashes(utf8_decode($_POST["mob_parameters"]))));

		if($this->content_obj->hasFullscreenItem())
		{
			$full_item->setWidth($_POST["full_width"]);
			$full_item->setHeight($_POST["full_height"]);
			$full_item->setCaption($_POST["full_caption"]);
			$full_item->setParameters(ilUtil::extractParameterString(ilUtil::stripSlashes(utf8_decode($_POST["full_parameters"]))));
		}

		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("Location: ".$this->getReturnLocation());
			exit;
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}


	function edit()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// edit object button
		/*
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","lm_edit.php?ref_id=".
			$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&hier_id=".$this->hier_id.
			"&cmd=editFiles");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_edit_mob_files"));
		$this->tpl->parseCurrentBlock();*/

		// standard item
		$std_item =& $this->content_obj->getMediaItem("Standard");

		// edit media alias template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_properties"));
		$this->tpl->setVariable("TXT_STANDARD_VIEW", $this->lng->txt("cont_std_view"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_".$std_item->getLocationType()));
		$this->tpl->setVariable("TXT_LOCATION", $std_item->getLocation());
		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=edpost"));

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		//header('Content-type: text/html; charset=UTF-8');

		// width
		$this->tpl->setVariable("TXT_MOB_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("INPUT_MOB_WIDTH", "mob_width");
		$this->tpl->setVariable("VAL_MOB_WIDTH", $std_item->getWidth());

		// height
		$this->tpl->setVariable("TXT_MOB_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("INPUT_MOB_HEIGHT", "mob_height");
		$this->tpl->setVariable("VAL_MOB_HEIGHT", $std_item->getHeight());

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "mob_caption");
		$this->tpl->setVariable("VAL_CAPTION", $std_item->getCaption());
		$this->tpl->parseCurrentBlock();

		// parameters
		$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("cont_parameter"));
		$this->tpl->setVariable("INPUT_PARAMETERS", "mob_parameters");
		$this->tpl->setVariable("VAL_PARAMETERS", $std_item->getParameterString());
		$this->tpl->parseCurrentBlock();

		// fullscreen view
		if($this->content_obj->hasFullScreenItem())
		{
			$this->tpl->setCurrentBlock("fullscreen");
			//$full_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Fullscreen");
			$full_item =& $this->content_obj->getMediaItem("Fullscreen");

			// edit media alias template
			$this->tpl->setVariable("TXT_FULLSCREEN_VIEW", $this->lng->txt("cont_fullscreen"));
			$this->tpl->setVariable("TXT_FULL_TYPE", $this->lng->txt("cont_".$full_item->getLocationType()));
			$this->tpl->setVariable("TXT_FULL_LOCATION", $full_item->getLocation());

			// width
			$this->tpl->setVariable("TXT_FULL_WIDTH", $this->lng->txt("cont_width"));
			$this->tpl->setVariable("INPUT_FULL_WIDTH", "full_width");
			$this->tpl->setVariable("VAL_FULL_WIDTH", $full_item->getWidth());

			// height
			$this->tpl->setVariable("TXT_FULL_HEIGHT", $this->lng->txt("cont_height"));
			$this->tpl->setVariable("INPUT_FULL_HEIGHT", "full_height");
			$this->tpl->setVariable("VAL_FULL_HEIGHT", $full_item->getHeight());

			// caption
			$this->tpl->setVariable("TXT_FULL_CAPTION", $this->lng->txt("cont_caption"));
			$this->tpl->setVariable("INPUT_FULL_CAPTION", "full_caption");
			$this->tpl->setVariable("VAL_FULL_CAPTION", $full_item->getCaption());

			// parameters
			$this->tpl->setVariable("TXT_FULL_PARAMETER", $this->lng->txt("cont_parameter"));
			$this->tpl->setVariable("INPUT_FULL_PARAMETERS", "full_parameters");
			$this->tpl->setVariable("VAL_FULL_PARAMETERS", $full_item->getParameterString());

			$this->tpl->parseCurrentBlock();
		}

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* save table properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		$std_item =& $this->content_obj->getMediaItem("Standard");
		$std_item->setWidth($_POST["mob_width"]);
		$std_item->setHeight($_POST["mob_height"]);
		$std_item->setCaption($_POST["mob_caption"]);
		$std_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["mob_parameters"])));

		if($this->content_obj->hasFullscreenItem())
		{
			$full_item =& $this->content_obj->getMediaItem("Fullscreen");
			$full_item->setWidth($_POST["full_width"]);
			$full_item->setHeight($_POST["full_height"]);
			$full_item->setCaption($_POST["full_caption"]);
			$full_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["full_parameters"])));
		}

		$this->content_obj->update();

		header("Location: ".$this->getReturnLocation());
		exit;
	}


	/**
	* administrate files of media object
	*/
	function editFiles()
	{
		if($_GET["limit"] == 0 )
		{
			$_GET["limit"] = 15;
		}

		// standard item
		$std_item =& $this->content_obj->getMediaItem("Standard");
		if($this->content_obj->hasFullscreenItem())
		{
			$full_item =& $this->content_obj->getMediaItem("Fullscreen");
		}

		// create table
		require_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// determine directory
		$cur_subdir = $_GET["cdir"];
		if($_GET["newdir"] == "..")
		{
			$cur_subdir = substr($cur_subdir, 0, strrpos($cur_subdir, "/"));
		}
		else
		{
			if (!empty($_GET["newdir"]))
			{
				if (!empty($cur_subdir))
				{
					$cur_subdir = $cur_subdir."/".$_GET["newdir"];
				}
				else
				{
					$cur_subdir = $_GET["newdir"];
				}
			}
		}

		$cur_subdir = str_replace(".", "", $cur_subdir);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.mob_files.html", true);

		//$this->tpl->setVariable("FORMACTION1", "lm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"].
		//	"&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir."&cmd=post");
		$this->tpl->setVariable("FORMACTION1", $this->getTargetScript().
			"&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir."&cmd=post");
//echo "--".$this->getTargetScript().
			//"&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir."&cmd=post"."--<br>";
		$this->tpl->setVariable("TXT_NEW_DIRECTORY", $this->lng->txt("cont_new_dir"));
		$this->tpl->setVariable("TXT_NEW_FILE", $this->lng->txt("cont_new_file"));
		$this->tpl->setVariable("CMD_NEW_DIR", "createDirectory");
		$this->tpl->setVariable("CMD_NEW_FILE", "uploadFile");
		$this->tpl->setVariable("BTN_NEW_DIR", $this->lng->txt("create"));
		$this->tpl->setVariable("BTN_NEW_FILE", $this->lng->txt("upload"));

		//
		$this->tpl->addBlockfile("FILE_TABLE", "files", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.mob_file_row.html", true);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", $this->getTargetScript().
			"&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir."&cmd=post");

		$tbl->setTitle($this->lng->txt("cont_files")." ".$cur_subdir);
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array("", "", $this->lng->txt("cont_dir_file"),
			$this->lng->txt("cont_size"), $this->lng->txt("cont_purpose")));

		$cols = array("", "", "dir_file", "size", "purpose");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "editFiles", "hier_id" => $_GET["hier_id"]);
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "1%", "33%", "33%", "32%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		//$tbl->setMaxCount(30);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 5);

		// delete button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "deleteFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "assignStandard");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_assign_std"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "assignFullscreen");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_assign_full"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		//require_once("./content/classes/class.ilObjMediaObject.php");
		//$cont_obj =& new ilObjContentObject($content_obj, true);

		$entries = ilUtil::getDir($cur_dir);

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($entries));
		$entries = array_slice($entries, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($entries) > 0)
		{
			$i=0;
			foreach($entries as $entry)
			{
				if(($entry["entry"] == ".") || ($entry["entry"] == ".." && empty($cur_subdir)))
				{
					continue;
				}

				//$this->tpl->setVariable("ICON", $obj["title"]);
				if($entry["type"] == "dir")
				{
					$this->tpl->setCurrentBlock("FileLink");
					$this->tpl->setVariable("LINK_FILENAME",
						$this->getTargetScript().
						"&hier_id=".$_GET["hier_id"]."&cmd=editFiles&cdir=".$cur_subdir."&newdir=".
						rawurlencode($entry["entry"]));
					$this->tpl->setVariable("TXT_FILENAME", $entry["entry"]);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setVariable("ICON", "<img src=\"".
						ilUtil::getImagePath("icon_cat.gif")."\">");
				}
				else
				{
					$this->tpl->setCurrentBlock("File");
					$this->tpl->setVariable("TXT_FILENAME2", $entry["entry"]);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $entry["size"]);
				$this->tpl->setVariable("CHECKBOX_ID", $entry["entry"]);
				$compare = (!empty($cur_subdir))
					? $cur_subdir."/".$entry["entry"]
					: $entry["entry"];
				$purpose = array();
				if ($std_item->getLocation() == $compare)
				{
					$purpose[] = $this->lng->txt("cont_std_view");
				}
				if($this->content_obj->hasFullscreenItem())
				{
					if ($full_item->getLocation() == $compare)
					{
						$purpose[] = $this->lng->txt("cont_fullscreen");
					}
				}
				$this->tpl->setVariable("TXT_PURPOSE", implode($purpose, ", "));

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}


	/**
	* create directory
	*/
	function createDirectory()
	{
		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;

		$new_dir = str_replace(".", "", $_POST["new_dir"]);
		$new_dir = str_replace("/", "", $new_dir);

		if (!empty($new_dir))
		{
			ilUtil::makeDir($cur_dir."/".$new_dir);
		}

		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editFiles&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir));
	}

	/**
	* upload file
	*/
	function uploadFile()
	{
		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		if (is_file($_FILES["new_file"]["tmp_name"]))
		{
			move_uploaded_file($_FILES["new_file"]["tmp_name"],
				$cur_dir."/".$_FILES["new_file"]["name"]);
		}
		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editFiles&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir));
	}

	/**
	* assign file to standard view
	*/
	function assignStandard()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		$file = $cur_dir."/".$_POST["file"][0];
		$location = (!empty($cur_subdir))
			? $cur_subdir."/".$_POST["file"][0]
			: $_POST["file"][0];

		if(!is_file($file))
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_file"),$this->ilias->error_obj->MESSAGE);
		}

		$std_item =& $this->content_obj->getMediaItem("Standard");
		$std_item->setLocationType("LocalFile");
		$std_item->setLocation($location);
		$format = ilMediaObject::getMimeType($file);
		$this->content_obj->update();
		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editFiles&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir));
	}


	/**
	* assign file to fullscreen view
	*/
	function assignFullscreen()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		$file = $cur_dir."/".$_POST["file"][0];
		$location = (!empty($cur_subdir))
			? $cur_subdir."/".$_POST["file"][0]
			: $_POST["file"][0];

		if(!is_file($file))
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_file"),$this->ilias->error_obj->MESSAGE);
		}

		if(!$this->content_obj->hasFullScreenItem())
		{
			$this->ilias->raiseError($this->lng->txt("cont_no_fullscreen_item"),$this->ilias->error_obj->MESSAGE);
		}

		$full_item =& $this->content_obj->getMediaItem("Fullscreen");

		$full_item->setLocationType("LocalFile");
		$full_item->setLocation($location);
		$format = ilMediaObject::getMimeType($file);
		$this->content_obj->update();
		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editFiles&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir));
	}


	function deleteFile()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->content_obj->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		$file = $cur_dir."/".$_POST["file"][0];
		$location = (!empty($cur_subdir))
			? $cur_subdir."/".$_POST["file"][0]
			: $_POST["file"][0];

		$full_item =& $this->content_obj->getMediaItem("Fullscreen");
		$std_item =& $this->content_obj->getMediaItem("Standard");

		if ($location == $std_item->getLocation())
		{
			$this->ilias->raiseError($this->lng->txt("cont_cant_del_std"),$this->ilias->error_obj->MESSAGE);
		}

		if($this->content_obj->hasFullScreenItem())
		{
			if ($location == $full_item->getLocation())
			{
				$this->ilias->raiseError($this->lng->txt("cont_cant_del_full"),$this->ilias->error_obj->MESSAGE);
			}
		}

		if (@is_dir($file))
		{
			if (substr($std_item->getLocation(), 0 ,strlen($location)) == $location)
			{
				$this->ilias->raiseError($this->lng->txt("cont_std_is_in_dir"),$this->ilias->error_obj->MESSAGE);
			}

			if (substr($full_item->getLocation(), 0 ,strlen($location)) == $location)
			{
				$this->ilias->raiseError($this->lng->txt("cont_full_is_in_dir"),$this->ilias->error_obj->MESSAGE);
			}
		}

		if (@is_file($file))
		{
			unlink($file);
		}

		if (@is_dir($file))
		{
			ilUtil::delDir($file);
		}

		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editFiles&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir));
	}


	/**
	* show all usages of mob
	*/
	function showUsages()
	{
		if($_GET["limit"] == 0 )
		{
			$_GET["limit"] = 15;
		}

		// create table
		require_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.mob_usage_row.html", true);

		$num = 0;

		$tbl->setTitle($this->lng->txt("cont_mob_usages"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array($this->lng->txt("cont_object"),
			$this->lng->txt("context")));

		$cols = array("object", "context");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "showUsages", "hier_id" => $_GET["hier_id"]);
		$tbl->setHeaderVars($cols, $header_params);
		//$tbl->setColumnWidth(array("1%", "1%", "33%", "33%", "32%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		//$tbl->setMaxCount(30);		// ???

		//$this->tpl->setVariable("COLUMN_COUNTS", 2);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		//require_once("./content/classes/class.ilObjMediaObject.php");
		//$cont_obj =& new ilObjContentObject($content_obj, true);

		//$entries = ilUtil::getDir($cur_dir);
		$usages = $this->content_obj->getUsages();

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($usages));
		$usages = array_slice($usages, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($usages) > 0)
		{
			$i=0;
			foreach($usages as $usage)
			{

				$this->tpl->setCurrentBlock("tbl_content");

				// set color
				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				if(is_int(strpos($usage["type"], ":")))
				{
					$us_arr = explode(":", $usage["type"]);
					$usage["type"] = $us_arr[1];
					$cont_type = $us_arr[0];
				}

				switch($usage["type"])
				{
					case "pg":

						require_once("content/classes/Pages/class.ilPageObject.php");
						$page_obj = new ilPageObject($cont_type, $usage["id"]);

						//$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
						switch ($cont_type)
						{
							case "lm":
							case "dbk":
								require_once("content/classes/class.ilObjContentObject.php");
								$lm_obj =& new ilObjContentObject($page_obj->getParentId(), false);
								$this->tpl->setVariable("TXT_OBJECT", $lm_obj->getTitle());
								break;
						}
						break;
				}
				// set usage link / text
				//$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
				$this->tpl->setVariable("TXT_CONTEXT", "-");

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}


	/**
	* edit map areas
	*/
	function editMapAreas()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.map_edit.html", true);

		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=edpost"));

		$this->tpl->setVariable("TXT_IMAGEMAP", $this->lng->txt("cont_imagemap"));

		// output image map
		$xml = "<dummy>";
		$xml.= $this->content_obj->getXML(IL_MODE_ALIAS);
		$xml.= $this->content_obj->getXML(IL_MODE_OUTPUT);
		$xml.="</dummy>";
		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();
		$wb_path = ilUtil::getWebspaceDir("output");
		$mode = "media";
		$params = array ('mode' => $mode,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => "", 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);
		$this->tpl->setVariable("IMAGE_MAP", $output);

		// output area table header
		$this->tpl->setVariable("TXT_NAME", $this->lng->txt("cont_name"));
		$this->tpl->setVariable("TXT_SHAPE", $this->lng->txt("cont_shape"));
		$this->tpl->setVariable("TXT_COORDS", $this->lng->txt("cont_coords"));
		$this->tpl->setVariable("TXT_LINK", $this->lng->txt("cont_link"));

		// output command line
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_UPDATE", "updateAreas");
		$this->tpl->setVariable("TXT_UPDATE", $this->lng->txt("cont_update"));
		$this->tpl->setVariable("BTN_ADD_AREA", "addArea");
		$this->tpl->setVariable("TXT_ADD_AREA", $this->lng->txt("cont_add_area"));
		$this->tpl->parseCurrentBlock();

		// output area data
		$st_item =& $this->content_obj->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		for ($i=1; $i<=$max; $i++)
		{
			$this->tpl->setCurrentBlock("area_row");

			$css_row = ilUtil::switchColor($i, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW", $css_row);

			$area =& new ilMapArea($st_item->getId(), $i);
			$this->tpl->setVariable("VAR_NAME", "name_".$i);
			$this->tpl->setVariable("VAL_NAME", $area->getTitle());
			$this->tpl->setVariable("VAL_SHAPE", $area->getShape());
			$this->tpl->setVariable("VAL_COORDS", $area->getCoords());
			switch ($area->getLinkType())
			{
				case "ext":
					$this->tpl->setVariable("VAL_LINK", $area->getHRef());
					break;

				case "int":
					$target = $area->getTarget();
					$tar_arr = explode("_", $target);
					$tar_id = $tar_arr[count($tar_arr) - 1];
					$frame_str = (($frame = $area->getTargetFrame()) == "")
						? ""
						: " ($frame)";
					$this->tpl->setVariable("VAL_LINK", $area->getType()." ".$tar_id.
						$frame_str);
					break;
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* update map areas
	*/
	function updateAreas()
	{
		$st_item =& $this->content_obj->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		for ($i=1; $i<=$max; $i++)
		{
			$area =& new ilMapArea($st_item->getId(), $i);
			$area->setTitle(ilUtil::stripSlashes($_POST["name_".$i]));
			$area->update();
		}
		header("Location: ".ilUtil::appendUrlParameterString($this->getReturnLocation(),
			"mode=page_edit&cmd=editMapAreas&hier_id=".$_GET["hier_id"]));
	}


	function copyToClipboard()
	{
		$this->ilias->account->addObjectToClipboard($this->content_obj->getId(), $this->content_obj->getType()
			, $this->content_obj->getTitle());
		sendInfo($this->lng->txt("copied_to_clipboard"));
		header("Location: ".$this->getReturnLocation());
	}

	function centerAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_alias_item->setHorizontalAlign("Center");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("Location: ".$this->getReturnLocation());
	}

	function leftAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_alias_item->setHorizontalAlign("Left");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("Location: ".$this->getReturnLocation());
	}

	function rightAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_alias_item->setHorizontalAlign("Right");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("Location: ".$this->getReturnLocation());
	}

	function leftFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_alias_item->setHorizontalAlign("LeftFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("Location: ".$this->getReturnLocation());
	}

	function rightFloatAlign()
	{
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_alias_item->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("Location: ".$this->getReturnLocation());
	}

}
?>
