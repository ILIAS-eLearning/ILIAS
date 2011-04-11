<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed tests for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilTestVerificationTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("passed"), "passed");
		$this->addColumn($this->lng->txt("action"), "");

		$this->setTitle($this->lng->txt("tstv_create"));
		$this->setDescription($this->lng->txt("tstv_create_info"));
		
		$this->setRowTemplate("tpl.il_test_verification_row.html", "Modules/Test");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{
		global $ilUser;

		include_once "Modules/Test/classes/class.ilObjTest.php";

		$data = array();
		foreach(ilObjTest::_lookupFinishedUserTests($ilUser->getId()) as $test_id => $passed)
		{
			$data[] = array("id" => $test_id,
				"title" => ilObject::_lookupTitle($test_id),
				"passed" => $passed);
		}

		$this->setData($data);
	}

	/**
	 * Fill template row
	 * 
	 * @param array $a_set
	 */
	protected function fillRow($a_set)
	{
		global $ilCtrl;

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
			$this->lng->txt("no"));
		$this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));

		$ilCtrl->setParameter($this->parent_obj, "tst_id", $a_set["id"]);
		$action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
		$this->tpl->setVariable("URL_SELECT", $action);
	}
}

?>