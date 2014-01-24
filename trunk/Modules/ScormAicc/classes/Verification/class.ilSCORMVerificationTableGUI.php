<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed learning modules for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMVerificationTableGUI extends ilTable2GUI
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

		$this->setTitle($this->lng->txt("scov_create"));
		$this->setDescription($this->lng->txt("scov_create_info"));
		
		$this->setRowTemplate("tpl.sahs_verification_row.html", "Modules/ScormAicc");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{
		global $ilUser, $tree;
		
		$data = array();
		
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if (ilCertificate::isActive())
		{	
			$obj_ids = array();
			$root = $tree->getNodeData($tree->getRootId());
			foreach($tree->getSubTree($root, true, "sahs") as $node)
			{
				$obj_ids[] = $node["obj_id"];
			}			
			if($obj_ids)
			{				
				include_once "./Services/Tracking/classes/class.ilObjUserTracking.php";
				include_once "./Services/Tracking/classes/class.ilLPStatus.php";
				include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";	
				include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";				
				include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";				
				include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";		
				$lp_active = ilObjUserTracking::_enabledLearningProgress();				
				foreach(ilCertificate::areObjectsActive($obj_ids) as $obj_id => $active)				
				{
					if($active)
					{									
						$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);	
						if($type == "scorm")
						{
							$lm = new ilObjSCORMLearningModule($obj_id, false);
						}
						else
						{
							$lm = new ilObjSCORM2004LearningModule($obj_id, false);
						}						
						$adapter = new ilSCORMCertificateAdapter($lm);
						if(ilCertificate::_isComplete($adapter))
						{	
							$lpdata = $completed = false;	
							if($lp_active)
							{							
								$completed = ilLPStatus::_hasUserCompleted($obj_id, $ilUser->getId());
								$lpdata = true;
							}
							if(!$lpdata)
							{								
								switch ($type)
								{
									case "scorm":																	
										$completed = ilObjSCORMLearningModule::_getCourseCompletionForUser($obj_id, $ilUser->getId());									
										break;

									case "scorm2004":									
										$completed = ilObjSCORM2004LearningModule::_getCourseCompletionForUser($obj_id, $ilUser->getId());									
										break;								
								}
							}
																				
							$data[] = array("id" => $obj_id,
								"title" => ilObject::_lookupTitle($obj_id),
								"passed" => (bool)$completed);			
						}																							
					}					
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
		global $ilCtrl;

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
			$this->lng->txt("no"));		

		if($a_set["passed"])
		{
			$ilCtrl->setParameter($this->parent_obj, "lm_id", $a_set["id"]);
			$action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
			$this->tpl->setVariable("URL_SELECT", $action);
			$this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
		}
	}
}

?>