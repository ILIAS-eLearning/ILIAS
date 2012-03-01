<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Help mapping
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilHelpMappingTableGUI extends ilTable2GUI
{
	var $online_help_mode = false;
	
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_validation = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("lm_help_map");
		$this->validation = $a_validation;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		
		$this->setData(ilStructureObject::getChapterList($this->parent_obj->object->getId()));
		$this->cnt_exp_ids = ilLMPageObject::getDuplicateExportIDs(
			$this->parent_obj->object->getId(), "st");

		$this->setTitle($lng->txt("cont_html_export_ids"));

		$this->addColumn($this->lng->txt("st"), "title");
		$this->addColumn($this->lng->txt("cont_screen_ids"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.help_map_row.html", "Modules/LearningModule");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->addCommandButton("saveHelpMapping", $lng->txt("save"));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("PAGE_ID", $a_set["obj_id"]);

		$exp_id = ilLMPageObject::getExportId(
			$this->parent_obj->object->getId(), $a_set["obj_id"], $a_set["type"]);

		if ($this->validation)
		{
			if (!preg_match("/^[a-zA-Z_]*$/",
				trim($_POST["exportid"][$a_set["obj_id"]])))
			{
				// @todo: move to style
				$this->tpl->setVariable("STYLE",
					" style='background-color: #FCEAEA;' ");
				$this->tpl->setVariable("ALERT_IMG",
					ilUtil::img(ilUtil::getImagePath("icon_alert_s.gif"),
					$lng->txt("alert"))
					);
			}
			$this->tpl->setVariable("EXPORT_ID",
				ilUtil::prepareFormOutput(
				ilUtil::stripSlashes($_POST["exportid"][$a_set["obj_id"]])));
		}
		else
		{
			$this->tpl->setVariable("EXPORT_ID",
				ilUtil::prepareFormOutput($exp_id));
		}

		if ($this->cnt_exp_ids[$exp_id] > 1)
		{
			$this->tpl->setVariable("ITEM_ADD_TXT",
				$lng->txt("cont_exp_id_used_multiple"));
			$this->tpl->setVariable("ALERT_IMG",
				ilUtil::img(ilUtil::getImagePath("icon_alert_s.gif"),
				$lng->txt("alert"))
				);
			if (!$this->dup_info_given)
			{
				ilUtil::sendInfo($lng->txt("content_some_export_ids_multiple_times"));
				$this->dup_info_given = true;
			}
		}
	}

}

?>