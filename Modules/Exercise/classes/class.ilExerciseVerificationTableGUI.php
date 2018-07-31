<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed exercises for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExerciseVerificationTableGUI extends ilTable2GUI
{
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->user = $DIC->user();
		$ilCtrl = $DIC->ctrl();
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("passed"), "passed");
		$this->addColumn($this->lng->txt("action"), "");

		$this->setTitle($this->lng->txt("excv_create"));
		$this->setDescription($this->lng->txt("excv_create_info"));
		
		$this->setRowTemplate("tpl.exc_verification_row.html", "Modules/Exercise");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{
		$ilUser = $this->user;

		$data = array();
		foreach(ilObjExercise::_lookupFinishedUserExercises($ilUser->getId()) as $exercise_id => $passed)
		{			
			// #11210 - only available certificates!
			$exc = new ilObjExercise($exercise_id, false);				
			if($exc->hasUserCertificate($ilUser->getId()))
			{
				$certificate = new ilCertificate(
					new ilExerciseCertificateAdapter($exc),
					new ilExercisePlaceHolderValues(),
					$exc->getId(),
					ilCertificatePathConstants::EXERCISE_PATH . $exc->getId() . '/',
					new ilCertificateSettingsCourseFormRepository(new ilCertificateSettingsFormRepository())
				);

				if($certificate->isComplete()) {
					$data[] = array(
						"id" => $exercise_id,
						"title" => ilObject::_lookupTitle($exercise_id),
						"passed" => $passed
					);
				}
			}
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
		$ilCtrl = $this->ctrl;

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
			$this->lng->txt("no"));
		
		if($a_set["passed"])
		{
			$ilCtrl->setParameter($this->parent_obj, "exc_id", $a_set["id"]);
			$action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
			$this->tpl->setVariable("URL_SELECT", $action);
			$this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
		}
	}
}

?>
