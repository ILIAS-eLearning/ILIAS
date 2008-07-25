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

require_once ("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
require_once ("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
require_once ("classes/class.ilObjectGUI.php");

/**
* Class ilObjMediaObjectGUI
*
* Editing User Interface for MediaObjects within LMs (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilObjMediaObjectGUI: ilInternalLinkGUI, ilMDEditorGUI
*
* @ingroup ServicesMediaObjects
*/
class ilObjMediaObjectGUI extends ilObjectGUI
{
	var $ctrl;
	var $header;
	var $target_script;
	var $enabledmapareas = true;

	function ilObjMediaObjectGUI($a_data, $a_id = 0, $a_call_by_reference = false, $a_prepare_output = false)
	{
		global $lng, $ilCtrl;

		$this->ctrl =& $ilCtrl;
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng =& $lng;
		$this->back_title = "";
		$this->type = "mob";
	}

	function _forwards()
	{
		return array("ilInternalLinkGUI");
	}

	function setHeader($a_title = "")
	{
		$this->header = $a_title;
	}

	function getHeader()
	{
		return $this->header;
	}

	/**
	* Set Enable map areas.
	*
	* @param	boolean	$a_enabledmapareas	Enable map areas
	*/
	function setEnabledMapAreas($a_enabledmapareas)
	{
		$this->enabledmapareas = $a_enabledmapareas;
	}

	/**
	* Get Enable map areas.
	*
	* @return	boolean	Enable map areas
	*/
	function getEnabledMapAreas()
	{
		return $this->enabledmapareas;
	}

	function assignObject()
	{
		if ($this->id != 0)
		{
			$this->object =& new ilObjMediaObject($this->id);
		}
	}

	function returnToContextObject()
	{
		$this->ctrl->returnToParent($this);
	}
	

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			case 'ilmdeditorgui':

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI(0, $this->object->getId(), $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case "ilinternallinkgui":
				require_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
				$link_gui = new ilInternalLinkGUI("Media_Media", 0);
				$link_gui->setMode("link");
				$link_gui->setSetLinkTargetScript(
					$this->ctrl->getLinkTargetByClass("ilObjMediaObjectGUI",
					"setInternalLink", "", true));
				$link_gui->filterLinkType("Media");
				//$ret =& $link_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($link_gui);
				break;

			default:
				if (isset($_POST["editImagemapForward"]) ||
					isset($_POST["editImagemapForward_x"]) ||
					isset($_POST["editImagemapForward_y"]))
				{
					$cmd = "editImagemapForward";
				}
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* set title for back tab
	*/
	function setBackTitle($a_title)
	{
		$this->back_title = $a_title;
	}
	
	/**
	* create new media object form
	*/
	function createObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_new.html", "Services/MediaObjects");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_mob"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

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
		$this->tpl->setVariable("TXT_RESIZE", $this->lng->txt("cont_resize_image"));
		$this->tpl->setVariable("TXT_RESIZE_EXPLANATION", $this->lng->txt("cont_resize_explanation"));
		//$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "save");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancel");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* create new media object in dom and update page in db
	*/
	function saveObject()
	{
		// determinte title and format
		if ($_POST["standard_type"] == "File")
		{
			$title = $_FILES['standard_file']['name'];
		}
		else
		{
			$title = $_POST["standard_reference"];
		}

		// create dummy object in db (we need an id)
		$this->object = new ilObjMediaObject();

		$this->object->setTitle($title);
		$this->object->setDescription("");
		$this->object->create();

		// determine and create mob directory, move uploaded file to directory
		//$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
		$this->object->createDirectory();
		$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

		$media_item =& new ilMediaItem();
		$this->object->addMediaItem($media_item);
		$media_item->setPurpose("Standard");

		if ($_POST["standard_type"] == "File")
		{
			$file = $mob_dir."/".$_FILES['standard_file']['name'];
			//move_uploaded_file($_FILES['standard_file']['tmp_name'], $file);
			ilUtil::moveUploadedFile($_FILES['standard_file']['tmp_name'],
				$_FILES['standard_file']['name'], $file);

			// get mime type
			$format = ilObjMediaObject::getMimeType($file);
			$location = $_FILES['standard_file']['name'];

			// resize standard images
			if ($_POST["standard_size"] != "original" &&
				$_POST["standard_resize"] == "y" &&
				is_int(strpos($format, "image")))
			{
				$location = ilObjMediaObject::_resizeImage($file, $_POST["standard_width"],
					$_POST["standard_height"]);
			}

			// set real meta and object data
			$media_item->setFormat($format);
			$media_item->setLocation($location);
			$media_item->setLocationType("LocalFile");
//			$meta_technical->addFormat($format);
//			$meta_technical->setSize($_FILES['standard_file']['size']);
//			$meta_technical->addLocation("LocalFile", $location);
			$this->object->setTitle($_FILES['standard_file']['name']);
		}
		else	// standard type: reference
		{
			$format = ilObjMediaObject::getMimeType($_POST["standard_reference"]);
			$media_item->setFormat($format);
			$media_item->setLocation($_POST["standard_reference"]);
			$media_item->setLocationType("Reference");
//			$meta_technical->addFormat($format);
//			$meta_technical->setSize(0);
//			$meta_technical->addLocation("Reference", $_POST["standard_reference"]);
			$this->object->setTitle($_POST["standard_reference"]);
		}
//		$meta->addTechnicalSection($meta_technical);
		$this->object->setDescription($format);

		// determine width and height of known image types
		if ($_POST["standard_size"] == "original")
		{
			if (ilUtil::deducibleSize($format))
			{
				$size = getimagesize($file);
				$media_item->setWidth($size[0]);
				$media_item->setHeight($size[1]);
			}
			else
			{
				$media_item->setWidth(500);
				$media_item->setHeight(400);
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
			$this->object->addMediaItem($media_item);
			$media_item->setPurpose("Fullscreen");

			// file
			if ($_POST["full_type"] == "File")
			{
				if ($_FILES['full_file']['name'] != "")
				{
					$file = $mob_dir."/".$_FILES['full_file']['name'];
					//move_uploaded_file($_FILES['full_file']['tmp_name'], $file);
					ilUtil::moveUploadedFile($_FILES['full_file']['tmp_name'],
						$_FILES['full_file']['name'], $file);
				}

				if ($_FILES['full_file']['name'] != "" ||
						($_POST["full_size"] != "original" &&
						$_POST["full_resize"] == "y" &&
						is_int(strpos($format, "image")))
					)
				{
					// set real meta and object data
					$format = ilObjMediaObject::getMimeType($file);
					$location = $_FILES['full_file']['name'];

					// resize fullscreen images
					if ($_POST["full_size"] != "original" &&
						$_POST["full_resize"] == "y" &&
						is_int(strpos($format, "image")))
					{
						$location = ilObjMediaObject::_resizeImage($file, $_POST["full_width"],
							$_POST["full_height"]);
					}
				}

				$media_item->setFormat($format);
				$media_item->setLocation($location);
				$media_item->setLocationType("LocalFile");
/*
				$meta_technical->addFormat($format);
				$meta_technical->setSize($meta_technical->getSize()
				+ $_FILES['full_file']['size']);
				$meta_technical->addLocation("LocalFile", $location);
*/

			}
			else	// reference
			{
				if ($_POST["full_reference"] != "")
				{
					$format = ilObjMediaObject::getMimeType($_POST["full_reference"]);
					$media_item->setFormat($format);
					$media_item->setLocation($_POST["full_reference"]);
					$media_item->setLocationType("Reference");
/*
					$meta_technical->addFormat($format);
					$meta_technical->addLocation("Reference", $_POST["full_reference"]);
*/
				}
			}

			// determine width and height of known image types
			if ($_POST["full_size"] == "original")
			{
				if (ilUtil::deducibleSize($format))
				{
					$size = getimagesize($file);
					$media_item->setWidth($size[0]);
					$media_item->setHeight($size[1]);
				}
				else
				{
					$media_item->setWidth(500);
					$media_item->setHeight(400);
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
//echo "-".$mob_dir."-";
		ilUtil::renameExecutables($mob_dir);
		$this->object->update();

		return $this->object;

	}
	
	function cancelObject()
	{
		$this->ctrl->returnToParent($this);
	}

	/**
	* edit media object properties
	*/
	function editObject()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// standard item
		$std_item =& $this->object->getMediaItem("Standard");

		// edit media alias template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mob_properties.html", "Services/MediaObjects");

		// deduce size button
		if ($std_item->getLocationType() == "LocalFile" &&
			ilUtil::deducibleSize($std_item->getFormat()))
		{
			$this->tpl->setCurrentBlock("get_size");
			$this->tpl->setVariable("CMD_SIZE", "getStandardSize");
			$this->tpl->setVariable("TXT_GET_SIZE", $this->lng->txt("cont_get_orig_size"));
			$this->tpl->parseCurrentBlock();
			//$this->tpl->setCurrentBlock("adm_content");
		}

		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_mob_properties"));
		$this->tpl->setVariable("TXT_STANDARD_VIEW", $this->lng->txt("cont_std_view"));

		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("cont_LocalFile"));
		$this->tpl->setVariable("TXT_REFERENCE", $this->lng->txt("cont_reference"));
		$this->tpl->setVariable("TXT_REF_HELPTEXT", $this->lng->txt("cont_ref_helptext"));
		if ($std_item->getLocationType() == "LocalFile")
		{
			$this->tpl->setVariable("FILE_CHECKED", "checked=\"1\"");
			$this->tpl->setVariable("VAL_FILE", $std_item->getLocation());
		}
		else
		{
			$this->tpl->setVariable("REF_CHECKED", "checked=\"1\"");
			$this->tpl->setVariable("VAL_REFERENCE", $std_item->getLocation());
		}

		$this->tpl->setVariable("TXT_FORMAT", $this->lng->txt("cont_format"));
		$this->tpl->setVariable("VAL_FORMAT", $std_item->getFormat());
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// width
		$this->tpl->setVariable("TXT_MOB_WIDTH", $this->lng->txt("cont_width"));
		$this->tpl->setVariable("INPUT_MOB_WIDTH", "mob_width");
		$this->tpl->setVariable("VAL_MOB_WIDTH", $std_item->getWidth());

		// height
		$this->tpl->setVariable("TXT_MOB_HEIGHT", $this->lng->txt("cont_height"));
		$this->tpl->setVariable("INPUT_MOB_HEIGHT", "mob_height");
		$this->tpl->setVariable("VAL_MOB_HEIGHT", $std_item->getHeight());

		// output original size
		if ($orig_size = $std_item->getOriginalSize())
		{
			$this->tpl->setCurrentBlock("orig_size");
			$this->tpl->setVariable("TXT_ORIGINAL_SIZE", $this->lng->txt("cont_orig_size"));
			$this->tpl->setVariable("VAL_ORIG_WIDTH", $orig_size["width"]);
			$this->tpl->setVariable("VAL_ORIG_HEIGHT", $orig_size["height"]);
			$this->tpl->parseCurrentBlock();
		}

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "mob_caption");
		$this->tpl->setVariable("VAL_CAPTION", $std_item->getCaption());
		//$this->tpl->parseCurrentBlock();

		// parameters
		$this->tpl->setVariable("TXT_PARAMETER", $this->lng->txt("cont_parameter"));
		$this->tpl->setVariable("INPUT_PARAMETERS", "mob_parameters");
		$this->tpl->setVariable("VAL_PARAMETERS", $std_item->getParameterString());
		//$this->tpl->parseCurrentBlock();

		// fullscreen view
		if($this->object->hasFullScreenItem())
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");

			if ($full_item->getLocationType() == "LocalFile" &&
				ilUtil::deducibleSize($full_item->getFormat()))
			{
				$this->tpl->setCurrentBlock("get_full_size");
				$this->tpl->setVariable("CMD_FULL_SIZE", "getFullscreenSize");
				$this->tpl->setVariable("TXT_GET_FULL_SIZE", $this->lng->txt("cont_get_orig_size"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("fullscreen");

			// edit media alias template
			$this->tpl->setVariable("TXT_FULLSCREEN_VIEW", $this->lng->txt("cont_fullscreen"));

			$this->tpl->setVariable("TXT_FULL_FILE", $this->lng->txt("cont_LocalFile"));
			$this->tpl->setVariable("TXT_FULL_REFERENCE", $this->lng->txt("cont_reference"));
			$this->tpl->setVariable("TXT_FULL_REF_HELPTEXT", $this->lng->txt("cont_ref_helptext"));
			if ($full_item->getLocationType() == "LocalFile")
			{
				$this->tpl->setVariable("FULL_FILE_CHECKED", "checked=\"1\"");
				$this->tpl->setVariable("VAL_FULL_FILE", $full_item->getLocation());
			}
			else
			{
				$this->tpl->setVariable("FULL_REF_CHECKED", "checked=\"1\"");
				$this->tpl->setVariable("VAL_FULL_REFERENCE", $full_item->getLocation());
			}

			//$this->tpl->setVariable("TXT_FULL_TYPE", $this->lng->txt("cont_".$full_item->getLocationType()));
			//$this->tpl->setVariable("TXT_FULL_LOCATION", $full_item->getLocation());
			$this->tpl->setVariable("TXT_FULL_FORMAT", $this->lng->txt("cont_format"));
			$this->tpl->setVariable("VAL_FULL_FORMAT", $full_item->getFormat());

			// width
			$this->tpl->setVariable("TXT_FULL_WIDTH", $this->lng->txt("cont_width"));
			$this->tpl->setVariable("INPUT_FULL_WIDTH", "full_width");
			$this->tpl->setVariable("VAL_FULL_WIDTH", $full_item->getWidth());

			// height
			$this->tpl->setVariable("TXT_FULL_HEIGHT", $this->lng->txt("cont_height"));
			$this->tpl->setVariable("INPUT_FULL_HEIGHT", "full_height");
			$this->tpl->setVariable("VAL_FULL_HEIGHT", $full_item->getHeight());

			// output original size
			if ($orig_size = $full_item->getOriginalSize())
			{
				$this->tpl->setCurrentBlock("orig_full_size");
				$this->tpl->setVariable("TXT_ORIGINAL_SIZE", $this->lng->txt("cont_orig_size"));
				$this->tpl->setVariable("VAL_ORIG_WIDTH", $orig_size["width"]);
				$this->tpl->setVariable("VAL_ORIG_HEIGHT", $orig_size["height"]);
				$this->tpl->parseCurrentBlock();
			}

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
		if($this->object->hasFullScreenItem())
		{
			$this->tpl->setCurrentBlock("remove_full");
			$this->tpl->setVariable("CMD_REMOVE_FULL", "removeFullscreen");
			$this->tpl->setVariable("TXT_REMOVE_FULL", $this->lng->txt("cont_remove_fullscreen"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("add_full");
			$this->tpl->setVariable("CMD_ADD_FULL", "addFullscreen");
			$this->tpl->setVariable("TXT_ADD_FULL", $this->lng->txt("cont_add_fullscreen"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("commands");

		$this->tpl->setVariable("BTN_RESIZE", "resizeImages");
		$this->tpl->setVariable("TXT_RESIZE", $this->lng->txt("cont_resize_image")." [*]");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("TXT_RESIZE_EXPLANATION",
			$this->lng->txt("cont_resize_explanation2"));
	}


	/**
	* resize images to specified size
	*/
	function resizeImagesObject()
	{
		// directory
		$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

		// standard item
		$std_item =& $this->object->getMediaItem("Standard");
		if ($std_item->getLocationType() == "LocalFile" &&
			is_int(strpos($std_item->getFormat(), "image"))
			)
		{
			$file = $mob_dir."/".$std_item->getLocation();
			$location = ilObjMediaObject::_resizeImage($file, $std_item->getWidth(),
				$std_item->getHeight());
			$std_item->setLocation($location);
			$std_item->update();
		}

		// fullscreen item
		if($this->object->hasFullScreenItem())
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");
			if ($full_item->getLocationType() == "LocalFile" &&
				is_int(strpos($full_item->getFormat(), "image"))
				)
			{
				$file = $mob_dir."/".$full_item->getLocation();
				$location = ilObjMediaObject::_resizeImage($file, $full_item->getWidth(),
					$full_item->getHeight());
				$full_item->setLocation($location);
				$full_item->update();
			}
		}

		$this->ctrl->redirect($this, "edit");
	}


	/**
	* set original size of standard file
	*/
	function getStandardSizeObject()
	{
		$std_item =& $this->object->getMediaItem("Standard");
		$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

		if ($std_item->getLocationType() == "LocalFile")
		{
			$file = $mob_dir."/".$std_item->getLocation();
			$size = getimagesize($file);
			$std_item->setWidth($size[0]);
			$std_item->setHeight($size[1]);
			$this->object->update();
		}
		$this->ctrl->redirect($this, "edit");
	}


	/**
	* set original size of fullscreen file
	*/
	function getFullscreenSizeObject()
	{
		$full_item =& $this->object->getMediaItem("Fullscreen");
		$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

		if ($full_item->getLocationType() == "LocalFile")
		{
			$file = $mob_dir."/".$full_item->getLocation();
			$size = getimagesize($file);
			$full_item->setWidth($size[0]);
			$full_item->setHeight($size[1]);
			$this->object->update();
		}
		$this->ctrl->redirect($this, "edit");
	}

	/**
	* save table properties in db and return to page edit screen
	*/
	function savePropertiesObject()
	{
		$std_item =& $this->object->getMediaItem("Standard");
		if ($_POST["standard_type"] == "Reference")
		{
			$std_item->setLocationType("Reference");
			$std_item->setFormat(ilObjMediaObject::getMimeType($_POST["standard_reference"]));
			$std_item->setLocation($_POST["standard_reference"]);
		}
		if ($_POST["standard_type"] == "LocalFile")
		{
			if ($_FILES['standard_file']['name'] != "")
			{
				$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());
				$file = $mob_dir."/".$_FILES['standard_file']['name'];
				//move_uploaded_file($_FILES['standard_file']['tmp_name'], $file);
				ilUtil::moveUploadedFile($_FILES['standard_file']['tmp_name'],
					$_FILES['standard_file']['name'], $file);

				$format = ilObjMediaObject::getMimeType($file);
				$std_item->setFormat($format);
				$std_item->setLocation($_FILES['standard_file']['name']);
			}
			$std_item->setLocationType("LocalFile");
		}
		$std_item->setWidth($_POST["mob_width"]);
		$std_item->setHeight($_POST["mob_height"]);
		$std_item->setCaption($_POST["mob_caption"]);
		$std_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["mob_parameters"])));

		if($this->object->hasFullscreenItem())
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");
			if ($_POST["full_type"] == "Reference")
			{
				$full_item->setLocationType("Reference");
				$full_item->setFormat(ilObjMediaObject::getMimeType($_POST["full_reference"]));
				$full_item->setLocation($_POST["full_reference"]);
			}
			if ($_POST["full_type"] == "LocalFile")
			{
				if ($_FILES['full_file']['name'] != "")
				{
					$mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());
					$file = $mob_dir."/".$_FILES['full_file']['name'];
					//move_uploaded_file($_FILES['full_file']['tmp_name'], $file);
					ilUtil::moveUploadedFile($_FILES['full_file']['tmp_name'],
						$_FILES['full_file']['name'], $file);

					$format = ilObjMediaObject::getMimeType($file);
					$full_item->setFormat($format);
					$full_item->setLocation($_FILES['full_file']['name']);
				}
				$full_item->setLocationType("LocalFile");
			}
			$full_item->setWidth($_POST["full_width"]);
			$full_item->setHeight($_POST["full_height"]);
			$full_item->setCaption($_POST["full_caption"]);
			$full_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["full_parameters"])));
		}

		$this->object->update();

		$this->ctrl->redirect($this, "edit");
	}


	/**
	* administrate files of media object
	*/
	function editFilesObject()
	{
		// standard item
		$std_item =& $this->object->getMediaItem("Standard");
		if($this->object->hasFullscreenItem())
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");
		}

		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
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
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.mob_files.html", "Services/MediaObjects");

		//$this->tpl->setVariable("FORMACTION1", "lm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"].
		//	"&hier_id=".$_GET["hier_id"]."&cdir=".$cur_subdir."&cmd=post");

		$this->ctrl->setParameter($this, "cdir", urlencode($cur_subdir));
		$this->tpl->setVariable("FORMACTION1", $this->ctrl->getFormAction($this));
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
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.mob_file_row.html", "Services/MediaObjects");

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

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
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
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
					$this->ctrl->setParameter($this, "cdir", $cur_subdir);
					$this->ctrl->setParameter($this, "newdir", rawurlencode($entry["entry"]));
					$this->tpl->setVariable("LINK_FILENAME", $this->ctrl->getLinkTarget($this, "editFiles"));
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
				if($this->object->hasFullscreenItem())
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
	function createDirectoryObject()
	{
//echo "cdir:".$_GET["cdir"].":<br>";
		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;

		$new_dir = str_replace(".", "", $_POST["new_dir"]);
		$new_dir = str_replace("/", "", $new_dir);

		if (!empty($new_dir))
		{
			ilUtil::makeDir($cur_dir."/".$new_dir);
		}
		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");
	}

	/**
	* upload file
	*/
	function uploadFileObject()
	{
		// determine directory
		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		if (is_file($_FILES["new_file"]["tmp_name"]))
		{
			//move_uploaded_file($_FILES["new_file"]["tmp_name"],
				//$cur_dir."/".$_FILES["new_file"]["name"]);
			$file = $cur_dir."/".$_FILES["new_file"]["name"];
			ilUtil::moveUploadedFile($_FILES['new_file']['tmp_name'],
				$_FILES['new_file']['name'], $file);

		}
		ilUtil::renameExecutables($mob_dir);
		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");
	}

	/**
	* assign file to standard view
	*/
	function assignStandardObject()
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
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
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

		$std_item =& $this->object->getMediaItem("Standard");
		$std_item->setLocationType("LocalFile");
		$std_item->setLocation($location);
		$format = ilObjMediaObject::getMimeType($file);
		$std_item->setFormat($format);
		$this->object->update();
		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");

		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");
	}


	/**
	* assign file to fullscreen view
	*/
	function assignFullscreenObject()
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
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
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

		if(!$this->object->hasFullScreenItem())
		{	// create new fullscreen item
			$std_item =& $this->object->getMediaItem("Standard");
			$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
			$file = $mob_dir."/".$location;
			$full_item =& new ilMediaItem();
			$full_item->setMobId($std_item->getMobId());
			$full_item->setLocation($location);
			$full_item->setLocationType("LocalFile");
			$full_item->setFormat(ilObjMediaObject::getMimeType($file));
			$full_item->setPurpose("Fullscreen");
			$this->object->addMediaItem($full_item);
		}
		else	// alter existing fullscreen item
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");

			$full_item->setLocationType("LocalFile");
			$full_item->setLocation($location);
			$format = ilObjMediaObject::getMimeType($file);
			$full_item->setFormat($format);
		}
		$this->object->update();
		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");
	}


	/**
	* remove fullscreen view
	*/
	function removeFullscreenObject()
	{
		$this->object->removeMediaItem("Fullscreen");
		$this->object->update();

		$this->ctrl->redirect($this, "edit");
	}


	/**
	* add fullscreen view
	*/
	function addFullscreenObject()
	{
		if (!$this->object->hasFullScreenItem())
		{
			$std_item =& $this->object->getMediaItem("Standard");
			$full_item =& new ilMediaItem();
			$full_item->setMobId($std_item->getMobId());
			$full_item->setLocation($std_item->getLocation());
			$full_item->setLocationType($std_item->getLocationType());
			$full_item->setFormat($std_item->getFormat());
			$full_item->setWidth($std_item->getWidth());
			$full_item->setHeight($std_item->getHeight());
			$full_item->setCaption($std_item->getCaption());
			$full_item->setPurpose("Fullscreen");
			$this->object->addMediaItem($full_item);

			$this->object->update();
		}

		$this->ctrl->redirect($this, "edit");
	}


	/**
	* delete object file
	*/
	function deleteFileObject()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		if ($_POST["file"][0] == "..")
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", $_GET["cdir"]);
		$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
		$cur_dir = (!empty($cur_subdir))
			? $mob_dir."/".$cur_subdir
			: $mob_dir;
		$file = $cur_dir."/".$_POST["file"][0];
		$location = (!empty($cur_subdir))
			? $cur_subdir."/".$_POST["file"][0]
			: $_POST["file"][0];

		$full_item =& $this->object->getMediaItem("Fullscreen");
		$std_item =& $this->object->getMediaItem("Standard");

		if ($location == $std_item->getLocation())
		{
			$this->ilias->raiseError($this->lng->txt("cont_cant_del_std"),$this->ilias->error_obj->MESSAGE);
		}

		if($this->object->hasFullScreenItem())
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

			if($this->object->hasFullScreenItem())
			{
				if (substr($full_item->getLocation(), 0 ,strlen($location)) == $location)
				{
					$this->ilias->raiseError($this->lng->txt("cont_full_is_in_dir"),$this->ilias->error_obj->MESSAGE);
				}
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

		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "editFiles");
	}


	/**
	* show all usages of mob
	*/
	function showUsagesObject()
	{
		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.mob_usage_row.html", "Services/MediaObjects");

		$num = 0;

		$tbl->setTitle($this->lng->txt("cont_mob_usages"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		//$tbl->setHeaderNames(array($this->lng->txt("container")));
		$tbl->disable("header");

		$cols = array("object");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "showUsages", "hier_id" => $_GET["hier_id"], "cmdClass" => "ilObjMediaObjectGUI");
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

		//$entries = ilUtil::getDir($cur_dir);
		$usages = $this->object->getUsages();

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($usages));
		$usages = array_slice($usages, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($usages) > 0)
		{
			$i=0;
			$clip_cnt = 0;
			foreach($usages as $usage)
			{
				if ($usage["type"] == "clip")
				{
					$clip_cnt++;
					continue;
				}

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

						require_once("./Services/COPage/classes/class.ilPageObject.php");
						$page_obj = new ilPageObject($cont_type, $usage["id"]);

						//$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
						switch ($cont_type)
						{
							case "lm":
								require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
								require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
								require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
								$lm_obj =& new ilObjLearningModule($page_obj->getParentId(), false);
								$this->tpl->setVariable("TXT_OBJECT", $this->lng->txt("obj_".$cont_type).
									": ".$lm_obj->getTitle().", ".$this->lng->txt("page").": ".
									ilLMObject::_lookupTitle($page_obj->getId()));
								break;
							case "dbk":
								require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
								require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
								require_once("./Modules/LearningModule/classes/class.ilObjDlBook.php");
								$lm_obj =& new ilObjDlBook($page_obj->getParentId(), false);
								$this->tpl->setVariable("TXT_OBJECT", $this->lng->txt("obj_".$cont_type).
									": ".$lm_obj->getTitle().", ".$this->lng->txt("page").": ".
									ilLMObject::_lookupTitle($page_obj->getId()));
								break;
						}
						break;

					case "mep":
						$this->tpl->setVariable("TXT_OBJECT", $this->lng->txt("obj_mep").
							": ".ilObject::_lookupTitle($usage["id"]));
						break;

					case "map":
						$this->tpl->setVariable("TXT_OBJECT", $this->lng->txt("obj_mob").
							" (".$this->lng->txt("cont_link_area")."): ".
							ilObject::_lookupTitle($usage["id"]));
						break;

				}
				// set usage link / text
				//$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
				$this->tpl->setVariable("TXT_CONTEXT", "-");

				$this->tpl->parseCurrentBlock();
			}

			// usages in clipboards
			if ($clip_cnt > 0)
			{
				$this->tpl->setCurrentBlock("tbl_content");

				// set color
				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TXT_OBJECT", $this->lng->txt("cont_users_have_mob_in_clip1").
					" ".$clip_cnt." ".$this->lng->txt("cont_users_have_mob_in_clip2"));
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
	function editMapAreasObject()
	{
		$_SESSION["il_map_edit_target_script"] = $this->ctrl->getLinkTarget($this, "addArea");

		//$this->initMapParameters();
		$this->handleMapParameters();

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.map_edit.html", "Services/MediaObjects");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TXT_IMAGEMAP", $this->lng->txt("cont_imagemap"));

		// create/update imagemap work copy
		$st_item =& $this->object->getMediaItem("Standard");
		$st_item->makeMapWorkCopy();

		// output image map
		$xml = "<dummy>";
		$xml.= $this->object->getXML(IL_MODE_ALIAS);
		$xml.= $this->object->getXML(IL_MODE_OUTPUT);
		$xml.="</dummy>";
//echo "xml:".htmlentities($xml).":<br>";
		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();
		$wb_path = ilUtil::getWebspaceDir("output");
		$mode = "media";
		$params = array ('map_item' => $st_item->getId(),
			'mode' => $mode,
			'link_params' => "ref_id=".$_GET["ref_id"]."&rand=".rand(1,999999),
			'ref_id' => $_GET["ref_id"],
			'pg_frame' => "",
			'image_map_link' => $this->ctrl->getLinkTarget($this, "showImageMap"),
 			'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo "<br>html:".htmlentities($output).":<br>";
		echo xslt_error($xh);
		xslt_free($xh);
		$this->tpl->setVariable("IMAGE_MAP", $output);

		$this->tpl->setCurrentBlock("area_table");

		// output area table header
		$this->tpl->setVariable("TXT_NAME", $this->lng->txt("cont_name"));
		$this->tpl->setVariable("TXT_SHAPE", $this->lng->txt("cont_shape"));
		$this->tpl->setVariable("TXT_COORDS", $this->lng->txt("cont_coords"));
		$this->tpl->setVariable("TXT_LINK", $this->lng->txt("cont_link"));

		// output command line
		$this->tpl->setCurrentBlock("commands");
		$sel_arr = array("Rect" => $this->lng->txt("cont_Rect"),
			"Circle" => $this->lng->txt("cont_Circle"),
			"Poly" => $this->lng->txt("cont_Poly"));
		$sel_str = ilUtil::formSelect("", "areatype", $sel_arr, false, true);
		$sel_str2 = ilUtil::formSelect("", "areatype2", $sel_arr, false, true);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("BTN_DELETE", "deleteAreas");
		$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
		$this->tpl->setVariable("SELECT_TYPE", $sel_str);
		$this->tpl->setVariable("SELECT_TYPE2", $sel_str2);
		$this->tpl->setVariable("BTN_UPDATE", "updateAreas");
		$this->tpl->setVariable("TXT_UPDATE", $this->lng->txt("cont_update_names"));
		$this->tpl->setVariable("BTN_ADD_AREA", "newArea");
		$this->tpl->setVariable("TXT_ADD_AREA", $this->lng->txt("cont_add_area"));
		$this->tpl->setVariable("BTN_SET_LINK", "editLink");
		$this->tpl->setVariable("TXT_SET_LINK", $this->lng->txt("cont_set_link"));
		$this->tpl->setVariable("BTN_SET_SHAPE", "editShape");
		$this->tpl->setVariable("TXT_SET_SHAPE", $this->lng->txt("cont_set_shape"));
		$this->tpl->parseCurrentBlock();

		// output area data
		$st_item =& $this->object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		for ($i=1; $i<=$max; $i++)
		{
			$this->tpl->setCurrentBlock("area_row");

			$css_row = ilUtil::switchColor($i, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW", $css_row);

			$area =& new ilMapArea($st_item->getId(), $i);
			$this->tpl->setVariable("CHECKBOX",
				ilUtil::formCheckBox("", "area[]", $i));
			$this->tpl->setVariable("VAR_NAME", "name_".$i);
			$this->tpl->setVariable("VAL_NAME", $area->getTitle());
			$this->tpl->setVariable("VAL_SHAPE", $area->getShape());
			$this->tpl->setVariable("VAL_COORDS",
				implode(explode(",", $area->getCoords()), ", "));
			switch ($area->getLinkType())
			{
				case "ext":
					$this->tpl->setVariable("VAL_LINK", $area->getHRef());
					break;

				case "int":
					$link_str = $this->getMapAreaLinkString($area->getTarget(),
						$area->getType(), $area->getTargetFrame());
					$this->tpl->setVariable("VAL_LINK", $link_str);
					break;
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* show image map
	*/
	function showImageMapObject()
	{
		$item =& new ilMediaItem($_GET["item_id"]);
		$item->outputMapWorkCopy();
	}



	/**
	* handle parameter during map area editing (storing to session)
	*/
	function handleMapParameters()
	{
		/*if($_POST["areatype"] != "")
		{
			$_SESSION["il_map_edit_area_type"] = $_POST["areatype"];
		}*/
//echo "AT:".$_SESSION["il_map_edit_area_type"].":";
		/*if($_GET["areatype"] != "")
		{
			$_SESSION["il_map_edit_area_type"] = $_GET["areatype"];
		}*/

		if($_GET["ref_id"] != "")
		{
			$_SESSION["il_map_edit_ref_id"] = $_GET["ref_id"];
		}

		if($_GET["obj_id"] != "")
		{
			$_SESSION["il_map_edit_obj_id"] = $_GET["obj_id"];
		}

		if($_GET["hier_id"] != "")
		{
			$_SESSION["il_map_edit_hier_id"] = $_GET["hier_id"];
		}

		/*
		if($_GET["coords"] != "")
		{
//echo "setcoords:".$_GET["coords"].":";
			$_SESSION["il_map_edit_coords"] = $_GET["coords"];
		}*/
	}


	/**
	* recover paramters from session variables (static)
	*/
	function _recoverParameters()
	{
		$_GET["ref_id"] = $_SESSION["il_map_edit_ref_id"];
		$_GET["obj_id"] = $_SESSION["il_map_edit_obj_id"];
		$_GET["hier_id"] = $_SESSION["il_map_edit_hier_id"];
		//$_GET["areatype"] = $_SESSION["il_map_edit_area_type"];
		//$_GET["coords"] = $_SESSION["il_map_edit_coords"];
	}

	function clearParameters()
	{
		$_SESSION["il_map_el_href"] = "";
	}


	/**
	* init map parameters
	*/
	function initMapParameters()
	{
		/*
		//unset($_SESSION["il_map_edit_ref_id"]);
		//unset($_SESSION["il_map_edit_obj_id"]);
		//unset($_SESSION["il_map_edit_hier_id"]);
		unset($_SESSION["il_map_edit_area_type"]);
		unset($_SESSION["il_map_edit_coords"]);
		unset($_SESSION["il_map_el_href"]);
		unset($_SESSION["il_map_il_type"]);
		unset($_SESSION["il_map_il_ltype"]);
		unset($_SESSION["il_map_il_target"]);
		unset($_SESSION["il_map_il_targetframe"]);
		unset($_SESSION["il_map_edit_mode"]);
		unset($_SESSION["il_map_area_nr"]);*/
	}

	function newAreaObject()
	{
		$_SESSION["il_map_edit_coords"] = "";
		$_SESSION["il_map_edit_mode"] = "";
		$_SESSION["il_map_el_href"] = "";
		$_SESSION["il_map_il_type"] = "";
		$_SESSION["il_map_il_ltype"] = "";
		$_SESSION["il_map_il_target"] = "";
		$_SESSION["il_map_il_targetframe"] = "";
		$_SESSION["il_map_edit_area_type"] = $_POST["areatype"];
		$this->addAreaObject(false);
	}

	/**
	* add new area
	*/
	function addAreaObject($a_handle = true)
	{
		// init all SESSION variables if "ADD AREA" button is pressed
		/*
		if ($_POST["areatype"] != "")
		{
			$this->initMapParameters();
		}*/

		// handle map parameters
		if($a_handle)
		{
			$this->handleMapParameters();
		}

		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		$cnt_coords = ilMapArea::countCoords($coords);
//echo "areatype:".$_SESSION["il_map_edit_area_type"].":<br>";
		// decide what to do next
		switch ($area_type)
		{
			// Rectangle
			case "Rect" :
				if ($cnt_coords < 2)
				{
					$this->editMapArea(true, false, false);
				}
				else if ($cnt_coords == 2)
				{
//echo "setting2:".$_SESSION["il_map_il_target"].":<br>";
					$this->editMapArea(false, true, true);
				}
				break;

			// Circle
			case "Circle":
//echo $coords."BHB".$cnt_coords;
				if ($cnt_coords <= 1)
				{
					$this->editMapArea(true, false, false);
				}
				else
				{
					if ($cnt_coords == 2)
					{
						$c = explode(",",$coords);
						$coords = $c[0].",".$c[1].",";	// determine radius
						$coords .= round(sqrt(pow(abs($c[3]-$c[1]),2)+pow(abs($c[2]-$c[0]),2)));
					}
					$_SESSION["il_map_edit_coords"] = $coords;

					$this->editMapArea(false, true, true);
				}
				break;

			// Polygon
			case "Poly":
				if ($cnt_coords < 1)
				{
					$this->editMapArea(true, false, false);
				}
				else if ($cnt_coords < 3)
				{
					$this->editMapArea(true, true, false);
				}
				else
				{
					$this->editMapArea(true, true, true);
				}
				break;
		}
//echo "setting3:".$_SESSION["il_map_il_target"].":<br>";
	}


	/**
	* get a single map area
	*
	* @param	boolean		$a_get_next_coordinate		enable next coordinate input
	* @param	boolean		$a_output_new_area			output the new area
	* @param	boolean		$a_save_from				output save form
	* @param	string		$a_edit_property			"" | "link" | "shape"
	*/
	function editMapArea($a_get_next_coordinate = false, $a_output_new_area = false,
		$a_save_form = false, $a_edit_property = "", $a_area_nr = 0)
	{

		$area_type = $_SESSION["il_map_edit_area_type"];
//echo "sessioncoords:".$_SESSION["il_map_edit_coords"].":<br>";
		$coords = $_SESSION["il_map_edit_coords"];
		$cnt_coords = ilMapArea::countCoords($coords);

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.map_edit.html", "Services/MediaObjects");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TXT_IMAGEMAP", $this->lng->txt("cont_imagemap"));

		// output instruction text

		$this->tpl->setCurrentBlock("instruction");
//echo "at:$area_type:<br>";
//echo "cntcoords:".$cnt_coords.":<br>";
//echo "coords:".$coords.":<br>";
		if ($a_edit_property != "link")
		{
			switch ($area_type)
			{
				// rectangle
				case "Rect" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_tl_corner"));
					}
					if ($cnt_coords == 1)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_br_corner"));
					}
					break;

				// circle
				case "Circle" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_center"));
					}
					if ($cnt_coords == 1)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_circle"));
					}
					break;

				// polygon
				case "Poly" :
					if ($cnt_coords == 0)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_starting_point"));
					}
					else if ($cnt_coords < 3)
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_next_point"));
					}
					else
					{
						$this->tpl->setVariable("INSTRUCTION", $this->lng->txt("cont_click_next_or_save"));
					}
					break;
			}
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");


		// map properties input fields (name and link)
		if ($a_save_form)
		{
			if ($a_edit_property != "link" && $a_edit_property != "shape")
			{
				$this->tpl->setCurrentBlock("edit_name");
				$this->tpl->setVariable("VAR_NAME2", "area_name");
				$this->tpl->setVariable("TXT_NAME2", $this->lng->txt("cont_name"));
				$this->tpl->parseCurrentBlock();
			}

			if ($a_edit_property != "shape")
			{
				$this->tpl->setCurrentBlock("edit_link");
				$this->tpl->setVariable("TXT_LINK_EXT", $this->lng->txt("cont_link_ext"));
				$this->tpl->setVariable("TXT_LINK_INT", $this->lng->txt("cont_link_int"));
				if ($_SESSION["il_map_el_href"] != "")
				{
					$this->tpl->setVariable("VAL_LINK_EXT", $_SESSION["il_map_el_href"]);
				}
				else
				{
					$this->tpl->setVariable("VAL_LINK_EXT", "http://");
				}
				$this->tpl->setVariable("VAR_LINK_EXT", "area_link_ext");
				$this->tpl->setVariable("VAR_LINK_TYPE", "area_link_type");
				if ($_SESSION["il_map_il_ltype"] != "int")
				{
					$this->tpl->setVariable("EXT_CHECKED", "checked=\"1\"");
				}
				else
				{
					$this->tpl->setVariable("INT_CHECKED", "checked=\"1\"");
				}

				// internal link
				$link_str = "";
				if($_SESSION["il_map_il_target"] != "")
				{
					$link_str = $this->getMapAreaLinkString($_SESSION["il_map_il_target"],
						$_SESSION["il_map_il_type"], $_SESSION["il_map_il_targetframe"]);
					$this->tpl->setVariable("VAL_LINK_INT", $link_str);
				}

				// internal link list
				$this->ctrl->setParameter($this, "linkmode", "map");
				$this->tpl->setVariable("LINK_ILINK",
					$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp",
					array("ilObjMediaObjectGUI"), true));
				$this->tpl->setVariable("TXT_ILINK", "[".$this->lng->txt("cont_get_link")."]");

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("new_area");
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("BTN_SAVE", "saveArea");
			if ($a_edit_property == "")
			{
				$this->tpl->setVariable("TXT_NEW_AREA", $this->lng->txt("cont_new_area"));
			}
			else
			{
				$this->tpl->setVariable("TXT_NEW_AREA", $this->lng->txt("cont_edit_area"));
			}
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("adm_content");
		}

		// create/update imagemap work copy
		$st_item =& $this->object->getMediaItem("Standard");

		if ($a_edit_property == "shape")
		{
			$st_item->makeMapWorkCopy($a_area_nr, true);	// exclude area currently being edited
		}
		else
		{
			$st_item->makeMapWorkCopy($a_area_nr, false);
		}

		if ($a_output_new_area)
		{
			$st_item->addAreaToMapWorkCopy($area_type, $coords);
		}

		// output image map
		$xml = "<dummy>";
		$xml.= $this->object->getXML(IL_MODE_ALIAS);
		$xml.= $this->object->getXML(IL_MODE_OUTPUT);
		$xml.="</dummy>";
//echo "xml:".htmlentities($xml).":<br>";
		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();
		$wb_path = ilUtil::getWebspaceDir("output");
		$mode = "media";
		if ($a_get_next_coordinate)
		{
			$map_edit_mode = "get_coords";
		}
		else
		{
			$map_edit_mode = "";
		}
		$params = array ('map_edit_mode' => $map_edit_mode,
			'map_item' => $st_item->getId(),
			'mode' => $mode,
			'image_map_link' => $this->ctrl->getLinkTarget($this, "showImageMap"),
			'link_params' => "ref_id=".$_GET["ref_id"]."&rand=".rand(1,999999),
			'ref_id' => $_GET["ref_id"],
			'pg_frame' => "",
			'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo "<br>html:".htmlentities($output).":<br>";
		echo xslt_error($xh);
		xslt_free($xh);
		$this->tpl->setVariable("IMAGE_MAP", $output);

		$this->tpl->parseCurrentBlock();
	}


	/**
	* get image map coords
	*/
	function editImagemapForwardObject()
	{
		ilObjMediaObjectGUI::_recoverParameters();

		if ($_SESSION["il_map_edit_coords"] != "")
		{
			$_SESSION["il_map_edit_coords"] .= ",";
		}

		$_SESSION["il_map_edit_coords"] .= $_POST["editImagemapForward_x"].",".
			$_POST["editImagemapForward_y"];

		// call lm_edit script
		ilUtil::redirect($_SESSION["il_map_edit_target_script"]);
	}


	/**
	*
	*
	* @access	private
	*/
	function setInternalLinkObject()
	{
		$_SESSION["il_map_il_type"] = $_GET["linktype"];
		$_SESSION["il_map_il_ltype"] = "int";

		$_SESSION["il_map_il_target"] = $_GET["linktarget"];
//echo "setting1:".$_SESSION["il_map_il_target"].":<br>";
		$_SESSION["il_map_il_targetframe"] = $_GET["linktargetframe"];
		switch ($_SESSION["il_map_edit_mode"])
		{
			case "edit_link":
				$this->setLink();
				break;

			default:
//echo "addArea";
				$this->addAreaObject();
				break;
		}
	}

	/**
	* get text name of internal link
	*
	* @param	string		$a_target		target object link id
	* @param	string		$a_type			type
	* @param	string		$a_frame		target frame
	*
	* @access	private
	*/
	function getMapAreaLinkString($a_target, $a_type, $a_frame)
	{
		$t_arr = explode("_", $a_target);
		if ($a_frame != "")
		{
			$frame_str = " (".$a_frame." Frame)";
		}
		switch($a_type)
		{
			case "StructureObject":
				require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
				$link_str = $this->lng->txt("chapter").
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "PageObject":
				require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
				$link_str = $this->lng->txt("page").
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "GlossaryItem":
				require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
				$term =& new ilGlossaryTerm($t_arr[count($t_arr) - 1]);
				$link_str = $this->lng->txt("term").
					": ".$term->getTerm()." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;

			case "MediaObject":
				require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
				$mob =& new ilObjMediaObject($t_arr[count($t_arr) - 1]);
				$link_str = $this->lng->txt("mob").
					": ".$mob->getTitle()." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;
				
			case "RepositoryItem":
				$title = ilObject::_lookupTitle(
					ilObject::_lookupObjId($t_arr[count($t_arr) - 1]));
				$link_str = $this->lng->txt("obj_".$t_arr[count($t_arr) - 2]).
					": ".$title." [".$t_arr[count($t_arr) - 1]."]".$frame_str;
				break;
		}

		return $link_str;
	}

	/**
	* update map areas
	*/
	function updateAreasObject()
	{
		$st_item =& $this->object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());
		for ($i=1; $i<=$max; $i++)
		{
			$area =& new ilMapArea($st_item->getId(), $i);
			$area->setTitle(ilUtil::stripSlashes($_POST["name_".$i]));
			$area->update();
		}

		ilUtil::sendInfo($this->lng->txt("cont_saved_map_data"), true);
		$this->ctrl->redirect($this, "editMapAreas");
	}


	/**
	* delete map areas
	*/
	function deleteAreasObject()
	{
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->editMapAreasObject();
			return;
		}

		$st_item =& $this->object->getMediaItem("Standard");
		$max = ilMapArea::_getMaxNr($st_item->getId());

		if (count($_POST["area"]) > 0)
		{
			$i = 0;

			foreach ($_POST["area"] as $area_nr)
			{
				$st_item->deleteMapArea($area_nr - $i);
				$i++;
			}

			$this->object->update();
			ilUtil::sendInfo($this->lng->txt("cont_areas_deleted"), true);
		}

		$this->ctrl->redirect($this, "editMapAreas");
	}


	/**
	* save new or updated map area
	*/
	function saveAreaObject()
	{
		switch ($_SESSION["il_map_edit_mode"])
		{
			// save edited link
			case "edit_link":
				$st_item =& $this->object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());
				$area =& new ilMapArea($st_item->getId(), $_SESSION["il_map_area_nr"]);

				if ($_POST["area_link_type"] == IL_INT_LINK)
				{
					$area->setLinkType(IL_INT_LINK);
					$area->setType($_SESSION["il_map_il_type"]);
					$area->setTarget($_SESSION["il_map_il_target"]);
					$area->setTargetFrame($_SESSION["il_map_il_targetframe"]);
				}
				else
				{
					$area->setLinkType(IL_EXT_LINK);
					$area->setHref($_POST["area_link_ext"]);
				}
				$area->update();
				break;

			// save edited shape
			case "edit_shape":
				$st_item =& $this->object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());
				$area =& new ilMapArea($st_item->getId(), $_SESSION["il_map_area_nr"]);

				$area->setShape($_SESSION["il_map_edit_area_type"]);
				$area->setCoords($_SESSION["il_map_edit_coords"]);
				$area->update();
				break;

			// save new area
			default:
				$area_type = $_SESSION["il_map_edit_area_type"];
				$coords = $_SESSION["il_map_edit_coords"];

				$st_item =& $this->object->getMediaItem("Standard");
				$max = ilMapArea::_getMaxNr($st_item->getId());

				// make new area object
				$area = new ilMapArea();
				$area->setItemId($st_item->getId());
				$area->setShape($area_type);
				$area->setCoords($coords);
				$area->setNr($max + 1);
				$area->setTitle($_POST["area_name"]);
				switch($_POST["area_link_type"])
				{
					case "ext":
						$area->setLinkType(IL_EXT_LINK);
						$area->setHref($_POST["area_link_ext"]);
						break;

					case "int":
						$area->setLinkType(IL_INT_LINK);
						$area->setType($_SESSION["il_map_il_type"]);
//echo "savingTarget:".$_SESSION["il_map_il_target"].":";
						$area->setTarget($_SESSION["il_map_il_target"]);
						$area->setTargetFrame($_SESSION["il_map_il_targetframe"]);
						break;
				}

				// put area into item and update media object
				$st_item->addMapArea($area);
				$this->object->update();
				break;
		}

		$this->initMapParameters();
		ilUtil::sendInfo($this->lng->txt("cont_saved_map_area"), true);
		$this->ctrl->redirect($this, "editMapAreas");
	}

	function editLinkObject()
	{
		$_SESSION["il_map_edit_coords"] = "";
		$_SESSION["il_map_edit_mode"] = "";
		$_SESSION["il_map_el_href"] = "";
		$_SESSION["il_map_il_type"] = "";
		$_SESSION["il_map_il_ltype"] = "";
		$_SESSION["il_map_il_target"] = "";
		$_SESSION["il_map_il_targetframe"] = "";
		$_SESSION["il_map_area_nr"] = "";
		$this->setLink(false);
	}

	/**
	* set link
	*/
	function setLink($a_handle = true)
	{
		if($a_handle)
		{
			$this->handleMapParameters();
		}
		if ($_SESSION["il_map_area_nr"] != "")
		{
			$_POST["area"][0] = $_SESSION["il_map_area_nr"];
		}
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->editMapAreasObject();
			return;
		}

		if (count($_POST["area"]) > 1)
		{
			//$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendInfo($this->lng->txt("cont_select_max_one_item"), true);
			$this->editMapAreasObject();
			return;
		}

		$st_item =& $this->object->getMediaItem("Standard");
		$area =& $st_item->getMapArea($_POST["area"][0]);
		//$max = ilMapArea::_getMaxNr($st_item->getId());

		if ($_SESSION["il_map_edit_mode"] != "edit_link")
		{
			$_SESSION["il_map_area_nr"] = $_POST["area"][0];
			$_SESSION["il_map_il_ltype"] = $area->getLinkType();
			$_SESSION["il_map_edit_mode"] = "edit_link";
			$_SESSION["il_map_edit_target_script"] = $this->ctrl->getLinkTarget($this, "setLink");
			if ($_SESSION["il_map_il_ltype"] == IL_INT_LINK)
			{
				$_SESSION["il_map_il_type"] = $area->getType();
				$_SESSION["il_map_il_target"] = $area->getTarget();
				$_SESSION["il_map_il_targetframe"] = $area->getTargetFrame();
			}
			else
			{
				$_SESSION["il_map_el_href"] = $area->getHref();
			}
		}

		$this->editMapArea(false, false, true, "link", $_POST["area"][0]);
	}

	function editShapeObject()
	{
		$_SESSION["il_map_area_nr"] = "";
		$_SESSION["il_map_edit_coords"] = "";
		$_SESSION["il_map_edit_mode"] = "";
		$_SESSION["il_map_el_href"] = "";
		$_SESSION["il_map_il_type"] = "";
		$_SESSION["il_map_il_ltype"] = "";
		$_SESSION["il_map_il_target"] = "";
		$_SESSION["il_map_il_targetframe"] = "";
		$this->setShapeObject(false);
	}

	/**
	* edit shape of existing map area
	*/
	function setShapeObject($a_handle = true)
	{
		if($a_handle)
		{
			$this->handleMapParameters();
		}
		if($_POST["areatype2"] != "")
		{
			$_SESSION["il_map_edit_area_type"] = $_POST["areatype2"];
		}
		if ($_SESSION["il_map_area_nr"] != "")
		{
			$_POST["area"][0] = $_SESSION["il_map_area_nr"];
		}
		if (!isset($_POST["area"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->editMapAreasObject();
			return;
		}

		if (count($_POST["area"]) > 1)
		{
			//$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendInfo($this->lng->txt("cont_select_max_one_item"), true);
			$this->editMapAreasObject();
			return;			
		}

		$st_item =& $this->object->getMediaItem("Standard");
		$area =& $st_item->getMapArea($_POST["area"][0]);
		//$max = ilMapArea::_getMaxNr($st_item->getId());

		if ($_SESSION["il_map_edit_mode"] != "edit_shape")
		{
			$_SESSION["il_map_area_nr"] = $_POST["area"][0];
			$_SESSION["il_map_edit_mode"] = "edit_shape";
			$_SESSION["il_map_edit_target_script"] = $this->ctrl->getLinkTarget($this, "setShape");
		}


		$area_type = $_SESSION["il_map_edit_area_type"];
		$coords = $_SESSION["il_map_edit_coords"];
		$cnt_coords = ilMapArea::countCoords($coords);

		// decide what to do next
		switch ($area_type)
		{
			// Rectangle
			case "Rect" :
				if ($cnt_coords < 2)
				{
					$this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else if ($cnt_coords == 2)
				{
					$this->saveAreaObject();
				}
				break;

			// Circle
			case "Circle":
//echo $coords."BHB".$cnt_coords;
				if ($cnt_coords <= 1)
				{
					$this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else
				{
					if ($cnt_coords == 2)
					{
						$c = explode(",",$coords);
						$coords = $c[0].",".$c[1].",";	// determine radius
						$coords .= round(sqrt(pow(abs($c[3]-$c[1]),2)+pow(abs($c[2]-$c[0]),2)));
					}
					$_SESSION["il_map_edit_coords"] = $coords;

					$this->saveAreaObject();
				}
				break;

			// Polygon
			case "Poly":
				if ($cnt_coords < 1)
				{
					$this->editMapArea(true, false, false, "shape", $_POST["area"][0]);
				}
				else if ($cnt_coords < 3)
				{
					$this->editMapArea(true, true, false, "shape", $_POST["area"][0]);
				}
				else
				{
					$this->editMapArea(true, true, true, "shape", $_POST["area"][0]);
				}
				break;
		}

	}

	/**
	* get media info as html
	*/
	function _getMediaInfoHTML(&$a_mob)
	{
		global $lng;

		$tpl =& new ilTemplate("tpl.media_info.html", true, true, "Services/MediaObjects");
		$types = array("Standard", "Fullscreen");
		foreach ($types as $type)
		{
			if($type == "Fullscreen" && !$a_mob->hasFullScreenItem())
			{
				continue;
			}

			$med =& $a_mob->getMediaItem($type);
			$tpl->setCurrentBlock("media_info");
			if ($type == "Standard")
			{
				$tpl->setVariable("TXT_PURPOSE", $lng->txt("cont_std_view"));
			}
			else
			{
				$tpl->setVariable("TXT_PURPOSE", $lng->txt("cont_fullscreen"));
			}
			$tpl->setVariable("TXT_TYPE", $lng->txt("cont_".$med->getLocationType()));
			$tpl->setVariable("VAL_LOCATION", $med->getLocation());
			if ($med->getLocationType() == "LocalFile")
			{
				$file = ilObjMediaObject::_getDirectory($med->getMobId())."/".$med->getLocation();
				if (is_file($file))
				{
					$size = filesize($file);
				}
				else
				{
					$size = 0;
				}
				$tpl->setVariable("VAL_FILE_SIZE", " ($size ".$lng->txt("bytes").")");
			}
			$tpl->setVariable("TXT_FORMAT", $lng->txt("cont_format"));
			$tpl->setVariable("VAL_FORMAT", $med->getFormat());
			if ($med->getWidth() != "" && $med->getHeight() != "")
			{
				$tpl->setCurrentBlock("size");
				$tpl->setVariable("TXT_SIZE", $lng->txt("size"));
				$tpl->setVariable("VAL_SIZE", $med->getWidth()."x".$med->getHeight());
				$tpl->parseCurrentBlock();
			}

			// original size
			if ($orig_size = $med->getOriginalSize())
			{
				if ($orig_size["width"] != $med->getWidth() ||
					$orig_size["height"] != $med->getHeight())
				{
					$tpl->setCurrentBlock("orig_size");
					$tpl->setVariable("TXT_ORIG_SIZE", $lng->txt("cont_orig_size"));
					$tpl->setVariable("ORIG_WIDTH", $orig_size["width"]);
					$tpl->setVariable("ORIG_HEIGHT", $orig_size["height"]);
					$tpl->parseCurrentBlock();
				}
			}
			$tpl->setCurrentBlock("media_info");
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	* set admin tabs
	*/
	function setAdminTabs()
	{
		// catch feedback message
		ilUtil::sendInfo();
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);

		//$tabs_gui->setTargetScript($this->ctrl->getLinkTarget($this));
		if (is_object($this->object) && strtolower(get_class($this->object)) == "ilobjmediaobject")
		{
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
			//$this->tpl->setCurrentBlock("header_image");
			//$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mob_b.gif"));
			//$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock();
			$title = $this->object->getTitle();
			$this->tpl->setVariable("HEADER", $title);
		}
		else
		{
			//$title = $this->object->getTitle();
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
			//$this->tpl->setCurrentBlock("header_image");
			//$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mob_b.gif"));
			//$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("HEADER", $this->lng->txt("cont_create_mob"));
		}

		// output tabs
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

	}

	function getTabs(&$tabs_gui)
	{
		global $ilTabs;

		//$tabs_gui->setTargetScript($this->ctrl->getLinkTarget($this));
		if (is_object($this->object) && strtolower(get_class($this->object)) == "ilobjmediaobject"
			&& $this->object->getId() > 0)
		{
			// object properties
			$ilTabs->addTarget("cont_mob_prop",
				$this->ctrl->getLinkTarget($this, "edit"), "edit",
				get_class($this));

			// object files
			$ilTabs->addTarget("cont_mob_files",
				$this->ctrl->getLinkTarget($this, "editFiles"), "editFiles",
				get_class($this));

			// object usages
			$ilTabs->addTarget("cont_mob_usages",
				$this->ctrl->getLinkTarget($this, "showUsages"), "showUsages",
				get_class($this));

			// link areas
			$st_item =& $this->object->getMediaItem("Standard");
			if (is_object($st_item) && $this->getEnabledMapAreas())
			{
				$format = $st_item->getFormat();
				if (substr($format, 0, 5) == "image")
				{
					$ilTabs->addTarget("cont_map_areas",
						$this->ctrl->getLinkTarget($this, "editMapAreas"), "editMapAreas",
						get_class($this));
				}
			}

			$ilTabs->addTarget("meta_data",
				$this->ctrl->getLinkTargetByClass(
					array("ilobjmediaobjectgui", "ilmdeditorgui"),'listSection'),
				"", "ilmdeditorgui");

		}

		// back to upper context
		if ($this->back_title != "")
		{
			$tabs_gui->setBackTarget($this->back_title,
				$this->ctrl->getParentReturn($this));
		}
	}

}
?>
