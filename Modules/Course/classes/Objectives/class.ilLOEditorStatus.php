<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Presentation of the status of single steps during the configuration process.
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOEditorStatus
{
	const SECTION_SETTINGS = 1;
	const SECTION_MATERIALS = 2;
	const SECTION_ITES = 3;
	const SECTION_QTEST = 4;
	const SECTION_OBJECTIVES = 5;
	const SECTION_OBJECTIVES_NEW = 6;
		
	protected static $instance = NULL;
	

	protected $section = NULL;

	protected $failures_by_section = array();
	
	protected $objectives = array();

	protected $settings = NULL;
	protected $parent_obj = NULL;
	protected $cmd_class = NULL;
	protected $html = '';
	
	protected $tpl = NULL;
	protected $ctrl = NULL;
	protected $lng = NULL;
	
	/**
	 * Constructor
	 * @param ilObject
	 */
	public function __construct(ilObject $a_parent)
	{
		$this->parent_obj = $a_parent;
		$this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
		
		$this->ctrl = $GLOBALS['ilCtrl'];
		$this->lng = $GLOBALS['lng'];
		$this->tpl = new ilTemplate('tpl.objective_editor_status.html',true,true,'Modules/Course');
		
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		$this->objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId());
	}
	
	/**
	 * Get instance
	 * @param ilObject $a_parent
	 */
	public static function getInstance(ilObject $a_parent)
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new self($a_parent);
	}
	
	public function getObjectives()
	{
		return $this->objectives;
	}
	
	/**
	 * Set current section
	 * @param type $a_section
	 */
	public function setSection($a_section)
	{
		$this->section = $a_section;
	}
	
	public function getSection()
	{
		return $this->section;
	}
	
	/**
	 * Get failures by section
	 * @param type $a_section
	 */
	public function getFailures($a_section)
	{
		return (array) $this->failures_by_section[$a_section];
	}
	
	/**
	 * Append failure
	 * @param type $a_section
	 * @param type $a_failure_msg_key
	 */
	protected function appendFailure($a_section, $a_failure_msg_key)
	{
		$this->failures_by_section[$a_section][] = $a_failure_msg_key;
	}
	
	/**
	 * Command class
	 * @param type $a_cmd_class
	 */
	public function setCmdClass($a_cmd_class)
	{
		$this->cmd_class = $a_cmd_class;
	}
	
	/**
	 * Get cmd class
	 * @return type
	 */
	public function getCmdClass()
	{
		return $this->cmd_class;
	}
	
	
	/**
	 * Get parent object
	 * @return ilObject
	 */
	public function getParentObject()
	{
		return $this->parent_obj;
	}
	
	/*
	 * @return ilLOSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Get first failed step
	 */
	public function getFirstFailedStep()
	{
		if(!$this->getSettingsStatus(false))
		{
			return 'settings';
		}
		#if(!$this->getMaterialsStatus(false))
		#{
		#	return 'materials';
		#}
		if(!$this->getObjectivesAvailableStatus())
		{
			return 'showObjectiveCreation';
		}
		if($this->getSettings()->worksWithInitialTest())
		{
			if(!$this->getInitialTestStatus(false))
			{
				$_REQUEST['tt'] = ilLOSettings::TYPE_TEST_INITIAL;
				return 'testOverview';
			}
		}
		if(!$this->getQualifiedTestStatus(false))
		{
			$_REQUEST['tt'] = ilLOSettings::TYPE_TEST_QUALIFIED;
			return 'testOverview';
		}
		if(!$this->getObjectivesStatus(false))
		{
			return 'listObjectives';
		}
		return 'listObjectives';
	}
	
	
	/**
	 * Get html
	 */
	public function getHTML()
	{

		$this->tpl->setVariable('TXT_STATUS_HEADER',$this->lng->txt('crs_objective_status_configure'));

		// Step 1
		// course settings
		$done = $this->getSettingsStatus();
		$this->tpl->setCurrentBlock('status_row');
		$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_SETTINGS ? 'tblrowmarked' : 'std');
		$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'settings'));
		$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_settings'));
		$this->showStatusInfo($done);
		$this->tpl->parseCurrentBlock();
		
		
		// Step 1.1
		$done = $this->getObjectivesAvailableStatus();
		$this->tpl->setCurrentBlock('status_row');
		$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_OBJECTIVES_NEW ? 'tblrowmarked' : 'std');
		if($done)
		{
			$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'listObjectives'));
		}
		else
		{
			$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'showObjectiveCreation'));
		}
		$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_objective_creation'));
		$this->showStatusInfo($done);
		$this->tpl->parseCurrentBlock();
		
		
		// Step 2
		// course material
		$done = $this->getMaterialsStatus(true);
		$this->showErrorsBySection(self::SECTION_MATERIALS);
		
		$this->tpl->setCurrentBlock('status_row');
		$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_MATERIALS ? 'tblrowmarked' : 'std');
		
		$this->ctrl->setParameterByClass('ilobjcoursegui','cmd','enableAdministrationPanel');
		$this->tpl->setVariable(
				'STEP_LINK',
				$this->ctrl->getLinkTargetByClass('ilobjcoursegui','')
		);
		$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_materials'));
		$this->showStatusInfo($done);
		$this->tpl->parseCurrentBlock();
		
		// Step 3
		// course itest
		if(ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest())
		{
			$done = $this->getInitialTestStatus();
			$this->showErrorsBySection(self::SECTION_ITES);
			
			$this->tpl->setCurrentBlock('status_row');
			$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_ITES ? 'tblrowmarked' : 'std');
			$this->ctrl->setParameter($this->getCmdClass(),'tt', ilLOEditorGUI::TEST_TYPE_IT);
			$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'testOverview'));
			$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_itest'));
			$this->showStatusInfo($done);
			$this->tpl->parseCurrentBlock();
		}

		// Step 4
		// course qtest
		$done = $this->getQualifiedTestStatus();
		$this->showErrorsBySection(self::SECTION_QTEST);

		$this->tpl->setCurrentBlock('status_row');
		$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_QTEST ? 'tblrowmarked' : 'std');
		$this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOEditorGUI::TEST_TYPE_QT);
		$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'testOverview'));
		$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_qtest'));
		$this->showStatusInfo($done);
		$this->tpl->parseCurrentBlock();

		// Step 5
		// course qtest
		$done = $this->getObjectivesStatus();
		$this->showErrorsBySection(self::SECTION_OBJECTIVES);
		$this->tpl->setCurrentBlock('status_row');
		$this->tpl->setVariable('CSS_ROW',$this->section == self::SECTION_OBJECTIVES ? 'tblrowmarked' : 'std');
		$this->tpl->setVariable('STEP_LINK',$this->ctrl->getLinkTarget($this->getCmdClass(),'listObjectives'));
		$this->tpl->setVariable('TXT_STEP',$this->lng->txt('crs_objective_status_objectives'));
		$this->showStatusInfo($done);
		$this->tpl->parseCurrentBlock();

		
		return $this->tpl->get();
	}
	
	/**
	 * Show info text
	 */
	protected function showStatusInfo($done)
	{
		if($done)
		{
			$this->tpl->setVariable('STATUS_CLASS','smallgreen');
			$this->tpl->setVariable('STATUS_INFO',$this->lng->txt('crs_objective_status_fullfilled'));
		}
		else
		{
			$this->tpl->setVariable('STATUS_CLASS','smallred');
			$this->tpl->setVariable('STATUS_INFO','TODO');
		}
	}
	
	/**
	 * Show errors by section
	 */
	protected function showErrorsBySection($a_current_section)
	{
		foreach($this->getFailures($a_current_section) as $failure_code)
		{
			$this->tpl->setCurrentBlock('step_failure');
			$this->tpl->setVariable('STEP_FAILURE_MSG',$this->lng->txt($failure_code));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Check if course is lo confgured
	 * @return type
	 */
	protected function getSettingsStatus()
	{
		return $this->getSettings()->settingsExist();
	}

	/**
	 * Get objectives
	 * @return type
	 */
	protected function getObjectivesAvailableStatus()
	{
		return count($this->getObjectives());
	}
	
	/**
	 * Get status of materials
	 */
	protected function getMaterialsStatus($a_set_errors = true)
	{
		$childs = $GLOBALS['tree']->getChilds($this->getParentObject()->getRefId());
		foreach((array) $childs as $tnode)
		{
			if($tnode['type'] == 'rolf')
			{
				continue;
			}
			if($tnode['child'] == $this->getSettings()->getInitialTest())
			{
				continue;
			}
			if($tnode['child'] == $this->getSettings()->getQualifiedTest())
			{
				continue;
			}
			return true;
		}
		if($a_set_errors)
		{
			$this->appendFailure(self::SECTION_MATERIALS, 'crs_loc_err_stat_no_materials');
		}
		return false;
	}
	
	protected function getInitialTestStatus($a_set_errors = true)
	{
		$tst_ref = $this->getSettings()->getInitialTest();
		if(!$GLOBALS['tree']->isInTree($tst_ref))
		{
			if($a_set_errors)
			{
				$this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_no_it');
			}
			return false;
		}
		return true;
	}
	
	protected function getQualifiedTestStatus($a_set_errors = true)
	{
		$tst_ref = $this->getSettings()->getQualifiedTest();
		if(!$GLOBALS['tree']->isInTree($tst_ref))
		{
			if($a_set_errors)
			{
				$this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_no_qt');
			}
			return false;
		}
		return true;
	}
	
	/**
	 * Check if questions are assigned
	 * @param type $a_test_ref_id
	 */
	protected function lookupQuestionsAssigned($a_test_ref_id)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
		if(ilLOUtils::lookupRandomTest(ilObject::_lookupObjId($a_test_ref_id)))
		{
			foreach($this->getObjectives() as $objective_id)
			{
				include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
				$seq = ilLORandomTestQuestionPools::lookupSequence(
						$this->parent_obj->getId(), 
						$objective_id, 
						ilObject::_lookupObjId($a_test_ref_id)
				);
				if(!$seq)
				{
					return false;
				}
			}
		}
		else
		{
			foreach($this->getObjectives() as $objective_id)
			{
				include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
				$qsts = ilCourseObjectiveQuestion::lookupQuestionsByObjective(ilObject::_lookupObjId($a_test_ref_id), $objective_id);
				if(!count($qsts))
				{
					return false;
				}
			}
		}
		return true;
	}


	protected function getObjectivesStatus($a_set_errors = true)
	{
		if(!$this->getObjectivesAvailableStatus($a_set_errors))
		{
			return false;
		}
		
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		$num_active = ilCourseObjective::_getCountObjectives($this->getParentObject()->getId(),true);
		if(!$num_active)
		{
			if($a_set_errors)
			{
				$this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_lo');
			}
			return false;
		}
		foreach(ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(),true) as $objective_id)
		{
			include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
			$obj = new ilCourseObjectiveMaterials($objective_id);
			if(!count($obj->getMaterials()))
			{
				if($a_set_errors)
				{
					$this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_mat');
				}
				return false;
			}
		}
		// check for assigned initial test questions
		if($this->getSettings()->worksWithInitialTest())
		{
			// check for assigned questions
			if(!$this->lookupQuestionsAssigned($this->getSettings()->getInitialTest()))
			{
				if($a_set_errors)
				{
					$this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
				}
				return false;
			}
		}
		// check for assigned questions
		if(!$this->lookupQuestionsAssigned($this->getSettings()->getQualifiedTest()))
		{
			if($a_set_errors)
			{
				$this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
			}
			return false;
		}
		
		if(!$this->checkNumberOfTries())
		{
			if($a_set_errors)
			{
				$this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_nr_tries_exceeded');
			}
			return false;
		}
		
		
		
		return true;
	}
	
	protected function getStartStatus()
	{
		return true;
	}
	
	protected function checkNumberOfTries()
	{
		$qt = $this->getSettings()->getQualifiedTest();
		if(!$qt)
		{
			return true;
		}
		
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$factory = new ilObjectFactory();
		$tst = $factory->getInstanceByRefId($qt,false);
		
		if(!$tst instanceof ilObjTest)
		{
			return true;
		}
		$tries = $tst->getNrOfTries();
		if(!$tries)
		{
			return true;
		}
		
		$obj_tries = 0;
		foreach($this->getObjectives() as $objective)
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$obj_tries += ilCourseObjective::lookupMaxPasses($objective);
		}
		$GLOBALS['ilLog']->write(__METHOD__.': '.$obj_tries);
		return $obj_tries <= $tries;
	}
}
?>
