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

require_once ("content/classes/class.ilPageContentGUI.php");
require_once ("content/classes/class.ilMediaObject.php");

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

	function ilMediaObjectGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}


	////
	// The following methods are for editing MediaAliases in PageObjects
	// not the object itself
	////

	/**
	* insert new media object form
	*/
	function insert()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_new.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_mob"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');


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
		$this->tpl->setVariable("BTN_NAME", "create_mob");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		// create dummy object in db (we need an id)
		$this->content_obj = new ilMediaObject();
		$dummy_meta =& new ilMetaData();
		$this->content_obj->assignMetaData($dummy_meta);
		$this->content_obj->setTitle("dummy");
		$this->content_obj->setDescription("dummy");
		$this->content_obj->create();

		// determine and create mob directory, move uploaded file to directory
		$mob_dir = $this->ilias->ini->readVariable("server","webspace_dir").
			"/mobs/mm_".$this->content_obj->getId();
		@mkdir($mob_dir);
		@chmod($mob_dir, 0755);

		if ($_POST["standard_type"] == "File")
		{
			$file = $mob_dir."/".$_FILES['standard_file']['name'];
			move_uploaded_file($_FILES['standard_file']['tmp_name'], $file);

			// set real meta and object data
			$format = ilMediaObject::getMimeType($file);
			$meta =& $this->content_obj->getMetaData();
			$meta_technical =& new ilMetaTechnical($meta);
			$meta_technical->setFormat($format);
			$meta_technical->setSize($_FILES['standard_file']['size']);
			$meta_technical->addLocation($_FILES['standard_file']['name']);
			$meta->addTechnicalSection($meta_technical);
			$this->content_obj->setTitle($_FILES['standard_file']['name']);
			$this->content_obj->setDescription($format);
			$this->content_obj->setStandardType("File");
		}
		else	// standard type: reference
		{
			$format = ilMediaObject::getMimeType($_POST["standard_reference"]);
			$meta =& $this->content_obj->getMetaData();
			$meta_technical =& new ilMetaTechnical($meta);
			$meta_technical->setFormat($format);
			$meta_technical->setSize(0);
			$meta_technical->addLocation($_POST["standard_reference"]);
			$meta->addTechnicalSection($meta_technical);
			$this->content_obj->setTitle($_POST["standard_reference"]);
			$this->content_obj->setDescription($format);
			$this->content_obj->setStandardType("Reference");
		}

		// determine width and height of known image types
		if ($_POST["standard_size"] == "original")
		{
			if (($format == "image/gif") || ($format == "image/jpeg") ||
				($format == "image/png") || ($format == "application/x-shockwave-flash") ||
				($format == "image/tiff") || ($format == "image/x-ms-bmp") ||
				($format == "image/psd") || ($format == "image/iff"))
			{
				$size = getimagesize($file);
				$this->content_obj->setWidth($size[0]);
				$this->content_obj->setHeight($size[1]);
			}
		}
		else
		{
			$this->content_obj->setWidth($_POST["standard_width"]);
			$this->content_obj->setHeight($_POST["standard_height"]);
		}

		if ($_POST["standard_caption"] != "")
		{
			$this->content_obj->setCaption($_POST["standard_caption"]);
		}

		$this->content_obj->setHAlign("Left");
		$this->content_obj->update();

		$this->content_obj->setDom($this->dom);
		$this->content_obj->createAlias($this->pg_obj, $this->hier_id);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$this->pg_obj->getId());
			exit;
		}
		else
		{
			$this->insert();
		}
	}



	/**
	* edit properties form
	*/
	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_properties"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		// width
		$this->tpl->setVariable("TXT_MOB_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("INPUT_MOB_WIDTH", "mob_width");
		$this->tpl->setVariable("VAL_MOB_WIDTH", $this->content_obj->getAliasWidth());

		// height
		$this->tpl->setVariable("TXT_MOB_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("INPUT_MOB_HEIGHT", "mob_height");
		$this->tpl->setVariable("VAL_MOB_HEIGHT", $this->content_obj->getAliasHeight());

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "mob_caption");
		$this->tpl->setVariable("VAL_CAPTION", $this->content_obj->getAliasCaption());

		$this->tpl->parseCurrentBlock();

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
		$this->content_obj->setAliasWidth($_POST["mob_width"]);
		$this->content_obj->setAliasHeight($_POST["mob_height"]);
		$this->content_obj->setAliasCaption($_POST["mob_caption"]);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$this->pg_obj->getId());
			exit;
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}



	function centerAlign()
	{
		$this->content_obj->setHorizontalAlign("Center");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function leftAlign()
	{
		$this->content_obj->setHorizontalAlign("Left");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function rightAlign()
	{
		$this->content_obj->setHorizontalAlign("Right");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function leftFloatAlign()
	{
		$this->content_obj->setHorizontalAlign("LeftFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function rightFloatAlign()
	{
		$this->content_obj->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

}
?>
