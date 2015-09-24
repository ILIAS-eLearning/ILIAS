<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List srt files from zip file for upload confirmation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilLMMultiSrtConfirmationTable2GUI extends ilTable2GUI
{
	protected $mob;

	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

		$this->multi_srt = $a_parent_obj->multi_srt;
		$lng->loadLanguageModule("meta");

		$this->setId("mob_msrt_upload");
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		$this->setData($this->multi_srt->getMultiSrtFiles());
		$this->setTitle($lng->txt("cont_multi_srt_files"));

		$this->addColumn($this->lng->txt("filename"));
		$this->addColumn($this->lng->txt("language"));
		$this->addColumn($this->lng->txt("mob"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.multi_srt_confirmation_row.html", "Modules/LearningModule");

		$this->addCommandButton("saveMultiSrt", $lng->txt("save"));
		$this->addCommandButton("cancelMultiSrt", $lng->txt("cancel"));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		if ($a_set["lang"] != "")
		{
			$language = $lng->txt("meta_l_".$a_set["lang"]);
			$this->tpl->setVariable("LANGUAGE", $language);
		}
		if ($a_set["mob"] != "")
		{
			$this->tpl->setVariable("MEDIA_OBJECT", $a_set["mob_title"]);
		}
		else
		{
			$this->tpl->setVariable("MEDIA_OBJECT", "-");
		}
		$this->tpl->setVariable("FILENAME", $a_set["filename"]);
	}

}
?>
