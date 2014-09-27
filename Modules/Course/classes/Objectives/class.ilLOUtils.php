<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOUtils
{
	
	/**
	 * Check if objective is completed
	 */
	public static function isCompleted($a_cont_oid, $a_test_rid, $a_objective_id, $max_points, $reached,$limit_perc)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$settings = ilLOSettings::getInstanceByObjId($a_cont_oid);
		
		if(self::lookupRandomTest(ilObject::_lookupObjId($a_test_rid)))
		{
			if(!$max_points)
			{
				return true;
			}
			else
			{
				return ($reached / $max_points * 100) >= $limit_perc;
			}
		}
		else
		{
			
			$GLOBALS['ilLog']->write(__METHOD__.': No random test');
			
			include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
			$required = ilCourseObjectiveQuestion::loookupTestLimit(
					ilObject::_lookupObjId($a_test_rid),
					$a_objective_id);
			
			$GLOBALS['ilLog']->write(__METHOD__.': '.$reached.' <-> '.$required);
			
			return $reached >= $required;
		}
	}

	/**
	 * 
	 * @param type $a_container_id
	 * @param type $a_objective_id
	 * @param type $a_test_type
	 */
	public static function lookupObjectiveRequiredPercentage($a_container_id, $a_objective_id, $a_test_type, $a_max_points)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$settings = ilLOSettings::getInstanceByObjId($a_container_id);
		
		if($a_test_type == ilLOSettings::TYPE_TEST_QUALIFIED)
		{
			$tst_ref_id = $settings->getQualifiedTest();
		}
		else
		{
			$tst_ref_id = $settings->getInitialTest();
		}
		if(self::lookupRandomTest(ilObject::_lookupObjId($tst_ref_id)))
		{
			include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
			return (int) ilLORandomTestQuestionPools::lookupLimit($a_container_id, $a_objective_id, $a_test_type);
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
			$limit = ilCourseObjectiveQuestion::loookupTestLimit(ilObject::_lookupObjId($tst_ref_id), $a_objective_id);

			if($a_max_points)
			{
				return (int) $limit / $a_max_points * 100;
			}
			return 0;
			
		}
	}
	
	/**
	 * 
	 * @param type $a_container_id
	 * @param type $a_objective_id
	 * @param type $a_passes
	 * @return boolean
	 */
	public static function lookupMaxAttempts($a_container_id, $a_objective_id)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$settings = ilLOSettings::getInstanceByObjId($a_container_id);
		if($settings->isGeneralQualifiedTestVisible())
		{
			return 0;
		}
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		$max_passes = ilCourseObjective::lookupMaxPasses($a_objective_id);
		
		return (int) $max_passes;
	}
	
	
	/**
	 * Check if test is a random test
	 * @param type $a_test_obj_id
	 * @return bool
	 */
	public static function lookupRandomTest($a_test_obj_id)
	{
		include_once './Modules/Test/classes/class.ilObjTest.php';
		return ilObjTest::_lookupRandomTest($a_test_obj_id);
	}
	
	/**
	 * Lookup assigned qpl name (including taxonomy) by sequence
	 * @param type $a_test_ref_id
	 * @param type $a_sequence_id
	 * @return string
	 */
	public static function lookupQplBySequence($a_test_ref_id, $a_sequence_id)
	{
		if(!$a_sequence_id)
		{
			return '';
		}
		$tst = ilObjectFactory::getInstanceByRefId($a_test_ref_id,false);
		if(!$tst instanceof ilObjTest)
		{
			return '';
		}
		include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
		include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
		$list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
				$GLOBALS['ilDB'],
				$tst,
				new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
						$GLOBALS['ilDB'],
						$tst
				)
		);
				
		$list->loadDefinitions();

		include_once './Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
		$translator = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['ilDB']);
		$translator->loadLabels($list);
		
		$title = '';
		foreach ($list as $definition)
		{
			if($definition->getId() != $a_sequence_id)
			{
				continue;
			}
			$title = self::buildQplTitleByDefinition($definition, $translator);
		}
		return $title;
	}
	
	/**
	 * build title by definition
	 * @param ilTestRandomQuestionSetSourcePoolDefinition $def
	 */
	protected static function buildQplTitleByDefinition(ilTestRandomQuestionSetSourcePoolDefinition $def, ilTestTaxonomyFilterLabelTranslater $trans)
	{
		$title = $def->getPoolTitle();
		$tax_id = $def->getMappedFilterTaxId();
		if($tax_id)
		{
			$title .= (' -> '. $trans->getTaxonomyTreeLabel($tax_id));
		}
		$tax_node = $def->getMappedFilterTaxNodeId();
		if($tax_node)
		{
			$title .= (' -> ' .$trans->getTaxonomyNodeLabel($tax_node));
		}
		return $title;
	}
	
	public static function hasActiveRun($a_container_id, $a_test_ref_id, $a_objective_id)
	{
		// check if pass exists
		include_once './Modules/Test/classes/class.ilObjTest.php';
		if(
			!ilObjTest::isParticipantsLastPassActive(
				$a_test_ref_id,
				$GLOBALS['ilUser']->getId())
		)
		{
			return false;
		}

		// check if multiple pass exists
		include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
		$last_objectives = ilLOTestRun::lookupObjectives(
				$a_container_id, 
				$GLOBALS['ilUser']->getId(),
				ilObject::_lookupObjId($a_test_ref_id)
		);
		
		if(count((array) $last_objectives) and in_array((int) $a_objective_id, (array) $last_objectives))
		{
			return true;
		}
		return false;
	}
}
?>