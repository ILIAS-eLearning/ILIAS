<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilSCORM2004TrackingItemsPerScoFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004TrackingItemsPerScoFilterGUI extends ilPropertyFormGUI
{

	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		parent::__construct($a_parent_obj, $a_parent_cmd);
	}

	public function parse($scoSelected,$report,$reports)
	{
		global $ilCtrl, $lng;
		$lng->loadLanguageModule("scormtrac");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

		$options = array("all" => $lng->txt("all"));
		$scos = $this->parent_obj->object->getTrackedItems();
		foreach($scos as $row)
		{
			$options[$row["id"]] = $row["title"];
		}
		$si = new ilSelectInputGUI($lng->txt("chapter"), "scoSelected");
		$si->setOptions($options);
		$si->setValue($scoSelected);
		$this->form->addItem($si);

		$options = array("choose" => $lng->txt("please_choose"));
		for ($i=0;$i<count($reports);$i++) {
			$options[$reports[$i]] = $lng->txt(strtolower($reports[$i]));
		}
		$si = new ilSelectInputGUI($lng->txt("report"), "report");
		$si->setOptions($options);
		$si->setValue($report);
		$this->form->addItem($si);
		$this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
	}

}
?>
