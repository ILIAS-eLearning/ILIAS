<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* @ilCtrl_Calls ilObjMediaObjectGUI: ilMDEditorGUI, ilImageMapEditorGUI
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
	

	/**
	* Execute current command
	*/
	function &executeCommand()
	{
		global $tpl;
		
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
				
			case "ilimagemapeditorgui":
				require_once("./Services/MediaObjects/classes/class.ilImageMapEditorGUI.php");
				$image_map_edit = new ilImageMapEditorGUI($this->object);
				$ret = $this->ctrl->forwardCommand($image_map_edit);
				$tpl->setContent($ret);
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
			$title = ilUtil::stripSlashes($_POST["standard_reference"]);
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
				$location = ilObjMediaObject::_resizeImage($file, (int) $_POST["standard_width"],
					(int) $_POST["standard_height"]);
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
			$format = ilObjMediaObject::getMimeType(ilUtil::stripSlashes($_POST["standard_reference"]));
			$media_item->setFormat($format);
			$media_item->setLocation(ilUtil::stripSlashes($_POST["standard_reference"]));
			$media_item->setLocationType("Reference");
//			$meta_technical->addFormat($format);
//			$meta_technical->setSize(0);
//			$meta_technical->addLocation("Reference", $_POST["standard_reference"]);
			$this->object->setTitle(ilUtil::stripSlashes($_POST["standard_reference"]));
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
			$media_item->setWidth((int) $_POST["standard_width"]);
			$media_item->setHeight((int) $_POST["standard_height"]);
		}

		if ($_POST["standard_caption"] != "")
		{
			$media_item->setCaption(ilUtil::stripSlashes($_POST["standard_caption"]));
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
						$location = ilObjMediaObject::_resizeImage($file, (int) $_POST["full_width"],
							(int) $_POST["full_height"]);
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
					$media_item->setLocation(ilUtil::stripSlashes($_POST["full_reference"]));
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
				$media_item->setWidth((int) $_POST["full_width"]);
				$media_item->setHeight((int) $_POST["full_height"]);
			}

			if ($_POST["full_caption"] != "")
			{
				$media_item->setCaption(ilUtil::stripSlashes($_POST["full_caption"]));
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

		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("cont_localfile"));
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

			$this->tpl->setVariable("TXT_FULL_FILE", $this->lng->txt("cont_localfile"));
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
	* save properties in db and return to page edit screen
	*/
	function savePropertiesObject()
	{
		global $lng;
		
		$std_item =& $this->object->getMediaItem("Standard");
		if ($_POST["standard_type"] == "Reference")
		{
			$std_item->setLocationType("Reference");
			$std_item->setFormat(ilObjMediaObject::getMimeType($_POST["standard_reference"]));
			$std_item->setLocation(ilUtil::stripSlashes($_POST["standard_reference"]));
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
		$std_item->setWidth((int) $_POST["mob_width"]);
		$std_item->setHeight((int) $_POST["mob_height"]);
		$std_item->setCaption(ilUtil::stripSlashes($_POST["mob_caption"]));
		$std_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["mob_parameters"])));

		if($this->object->hasFullscreenItem())
		{
			$full_item =& $this->object->getMediaItem("Fullscreen");
			if ($_POST["full_type"] == "Reference")
			{
				$full_item->setLocationType("Reference");
				$full_item->setFormat(ilObjMediaObject::getMimeType($_POST["full_reference"]));
				$full_item->setLocation(ilUtil::stripSlashes($_POST["full_reference"]));
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
			$full_item->setWidth((int) $_POST["full_width"]);
			$full_item->setHeight((int) $_POST["full_height"]);
			$full_item->setCaption(ilUtil::stripSlashes($_POST["full_caption"]));
			$full_item->setParameters(ilUtil::stripSlashes(utf8_decode($_POST["full_parameters"])));
		}

		$this->object->update();
		ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
		
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
		global $tpl;
		
		include_once("./Services/MediaObjects/classes/class.ilMediaObjectUsagesTableGUI.php");
		$usages_table = new ilMediaObjectUsagesTableGUI($this, "showUsages",
			$this->object);
		$tpl->setContent($usages_table->getHTML());
return;

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
			$tpl->setVariable("TXT_TYPE", $lng->txt("cont_".strtolower($med->getLocationType())));
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
	}

	/**
	* Get Tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilTabs;

		//$tabs_gui->setTargetScript($this->ctrl->getLinkTarget($this));
		if (is_object($this->object) && strtolower(get_class($this->object)) == "ilobjmediaobject"
			&& $this->object->getId() > 0)
		{
			// object properties
			$ilTabs->addTarget("cont_mob_def_prop",
				$this->ctrl->getLinkTarget($this, "edit"), "edit",
				get_class($this));

			// link areas
			$st_item =& $this->object->getMediaItem("Standard");
			if (is_object($st_item) && $this->getEnabledMapAreas())
			{
				$format = $st_item->getFormat();
				if (substr($format, 0, 5) == "image")
				{
//					$ilTabs->addTarget("cont_map_areas",
//						$this->ctrl->getLinkTarget($this, "editMapAreas"), "editMapAreas",
//						get_class($this));
					$ilTabs->addTarget("cont_def_map_areas",
						$this->ctrl->getLinkTargetByClass(
							array("ilobjmediaobjectgui", "ilimagemapeditorgui"), "editMapAreas"), "editMapAreas",
						"ilimagemapeditorgui");
				}
			}

			// object usages
			$ilTabs->addTarget("cont_mob_usages",
				$this->ctrl->getLinkTarget($this, "showUsages"), "showUsages",
				get_class($this));

			// object files
			$ilTabs->addTarget("cont_files",
				$this->ctrl->getLinkTarget($this, "editFiles"), "editFiles",
				get_class($this));

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
