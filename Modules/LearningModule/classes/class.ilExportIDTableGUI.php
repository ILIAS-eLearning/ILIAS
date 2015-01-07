<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Export IDs table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilExportIDTableGUI extends ilTable2GUI
{
	var $online_help_mode = false;
	
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_validation = false,
		$a_oh_mode = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setOnlineHelpMode($a_oh_mode);
		$this->setId("lm_expids");
		$this->validation = $a_validation;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		
		if ($this->getOnlineHelpMode())
		{
			$this->setData(ilStructureObject::getChapterList($this->parent_obj->object->getId()));
			$this->cnt_exp_ids = ilLMPageObject::getDuplicateExportIDs(
				$this->parent_obj->object->getId(), "st");
		}
		else
		{
			$this->setData(ilLMPageObject::getPageList($this->parent_obj->object->getId()));
			$this->cnt_exp_ids = ilLMPageObject::getDuplicateExportIDs(
				$this->parent_obj->object->getId());
		}

		$this->setTitle($lng->txt("cont_html_export_ids"));

		$this->addColumn($this->lng->txt("pg"), "title");
		$this->addColumn($this->lng->txt("cont_export_id"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.export_id_row.html", "Modules/LearningModule");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->addCommandButton("saveExportIDs", $lng->txt("save"));
	}

	/**
	 * Set online help mode
	 *
	 * @param bool $a_val online help mode	
	 */
	function setOnlineHelpMode($a_val)
	{
		$this->online_help_mode = $a_val;
	}
	
	/**
	 * Get online help mode
	 *
	 * @return bool online help mode
	 */
	function getOnlineHelpMode()
	{
		return $this->online_help_mode;
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
					ilUtil::img(ilUtil::getImagePath("icon_alert.svg"),
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
				ilUtil::img(ilUtil::getImagePath("icon_alert.svg"),
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