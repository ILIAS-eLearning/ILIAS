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

		/*
		$meta =& $this->content_obj->getMetaData();
		$meta_tech =& $meta->getTechnicalSection();
		$locations = $meta_tech->getLocations();
		$formats = $meta_tech->getFormats();*/

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// edit object button
		/*
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","lm_edit.php?ref_id=".
			$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&hier_id=".$this->hier_id.
			"&cmd=edit");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_edit_mob"));
		$this->tpl->parseCurrentBlock();*/


		//$item_nr = $this->content_obj->getMediaItemNr("Standard");
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
		$std_item =& $this->content_obj->getMediaItem("Standard");

		// edit media alias template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_alias_prop"));
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
			$this->tpl->parseCurrentBlock();

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

		if($this->content_obj->hasFullscreenItem())
		{
			$full_item->setWidth($_POST["full_width"]);
			$full_item->setHeight($_POST["full_height"]);
			$full_item->setCaption($_POST["full_caption"]);
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
		//$item_nr = $this->content_obj->getMediaItemNr("Standard");
		$std_alias_item =& new ilMediaAliasItem($this->dom, $this->getHierId(), "Standard");
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
			$this->tpl->parseCurrentBlock();

			$this->tpl->parseCurrentBlock();
		}

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
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
