<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for subtitle list
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilMobSubtitleTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_mob)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->mob = $a_mob;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($a_mob->getSrtFiles());
		$this->setTitle($lng->txt("mob_subtitle_files"));
		
		$this->addColumn("", "", 1);
		$this->addColumn($this->lng->txt("mob_file"));
		$this->addColumn($this->lng->txt("mob_language"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.srt_files_row.html", "Services/MediaObjects");

		$this->addMultiCommand("confirmSrtDeletion", $lng->txt("delete"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("FILE_NAME", $a_set["full_path"]);
		$this->tpl->setVariable("LANGUAGE", $lng->txt("meta_l_".$a_set["language"]));
		$this->tpl->setVariable("LANG_KEY", $a_set["language"]);
	}

}
?>
