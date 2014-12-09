<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestGradingMessageBuilder
{
	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilObjTest
	 */
	private $testOBJ;

	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var array
	 */
	private $resultData;

	/**
	 * @var integer
	 */
	private $activeId;

	/**
	 * @param ilLanguage $lng
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilLanguage $lng, ilObjTest $testOBJ)
	{
		$this->lng = $lng;
		$this->testOBJ = $testOBJ;
	}

	public function setActiveId($activeId)
	{
		$this->activeId = $activeId;
	}

	public function getActiveId()
	{
		return $this->activeId;
	}

	public function build()
	{
		$this->initTemplate();

		$this->loadResultData();
		
		if( $this->testOBJ->isShowGradingStatusEnabled() )
		{
			$this->populateGradingStatus();
		}

		if( $this->testOBJ->areObligationsEnabled() )
		{
			$this->populateObligationsStatus();
		}
		
		if( $this->testOBJ->isShowGradingMarkEnabled() )
		{
			$this->populateGradingMark();
		}

		if( $this->testOBJ->getECTSOutput() )
		{
			$this->populateEctsGrade();
		}
	}
	
	public function getMessage()
	{
		return $this->tpl->get();
	}

	private function initTemplate()
	{
		$this->tpl = new ilTemplate('tpl.tst_grading_message.html', true, true, 'Modules/Test');
	}
	
	private function loadResultData()
	{
		$this->resultData = $this->testOBJ->getResultsForActiveId($this->getActiveId());

		if( $this->testOBJ->getECTSOutput() )
		{
			$ectsMark = $this->testOBJ->getECTSGrade(
				$this->testOBJ->getTotalPointsPassedArray(),
				$this->resultData['reached_points'],
				$this->resultData['max_points']
			);

			$this->resultData['ects_grade'] = $this->lng->txt('ects_grade_'.strtolower($ectsMark));
		}
	}
	
	private function populateGradingStatus()
	{
		$this->tpl->setCurrentBlock('status_css_class');
		$this->tpl->setVariable('STATUS_CSS_CLASS', $this->getGradingStatusCssClass());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('grading_status_msg');
		$this->tpl->setVariable('GRADING_STATUS_MSG', $this->buildGradingStatusMsg());
		$this->tpl->parseCurrentBlock();
	}
	
	private function getGradingStatusCssClass()
	{
		if( $this->isPassed() )
		{
			return 'passed';
		}
		
		return 'failed';
	}

	private function buildGradingStatusMsg()
	{
		if( $this->isPassed() )
		{
			return $this->lng->txt('grading_status_passed_msg');
		}

		return $this->lng->txt('grading_status_failed_msg');
	}

	private function isPassed()
	{
		return (bool)$this->resultData['passed'];
	}
	
	private function populateGradingMark()
	{
		$this->tpl->setCurrentBlock('grading_mark_msg');
		$this->tpl->setVariable('GRADING_MARK_MSG', $this->buildGradingMarkMsg());
		$this->tpl->parseCurrentBlock();
	}

	private function buildGradingMarkMsg()
	{
		$markMsg = $this->lng->txt('grading_mark_msg');

		$markMsg = str_replace("[mark]", $this->getMarkOfficial(), $markMsg);
		$markMsg = str_replace("[markshort]", $this->getMarkShort(), $markMsg);
		$markMsg = str_replace("[percentage]", $this->getPercentage(), $markMsg);
		$markMsg = str_replace("[reached]", $this->getReachedPoints(), $markMsg);
		$markMsg = str_replace("[max]", $this->getMaxPoints(), $markMsg);
		
		return $markMsg;
	}

	private function getMarkOfficial()
	{
		return $this->resultData['mark_official'];
	}

	private function getMarkShort()
	{
		return $this->resultData['mark_short'];
	}

	private function getPercentage()
	{
		$percentage = 0;

		if( $this->getMaxPoints() > 0 )
		{
			$percentage = $this->getReachedPoints() / $this->getMaxPoints();
		}
		
		return sprintf("%.2f", $percentage);
	}

	private function getReachedPoints()
	{
		return $this->resultData['reached_points'];
	}

	private function getMaxPoints()
	{
		return $this->resultData['max_points'];
	}
	
	private function populateObligationsStatus()
	{
		$this->tpl->setCurrentBlock('obligations_msg');
		$this->tpl->setVariable('OBLIGATIONS_MSG', $this->buildObligationsMsg());
		$this->tpl->parseCurrentBlock();
	}
	
	private function buildObligationsMsg()
	{
		if( $this->areObligationsAnswered() )
		{
			return $this->lng->txt('grading_obligations_answered_msg');
		}

		return $this->lng->txt('grading_obligations_missing_msg');
	}
	
	private function areObligationsAnswered()
	{
		return (bool)$this->resultData['obligations_answered'];
	}
	
	private function populateEctsGrade()
	{
		$this->tpl->setCurrentBlock('grading_mark_ects_msg');
		$this->tpl->setVariable('GRADING_MARK_ECTS_MSG', $this->buildEctsGradeMsg());
		$this->tpl->parseCurrentBlock();
	}
	
	private function buildEctsGradeMsg()
	{
		return str_replace('[markects]', $this->getEctsGrade(), $this->lng->txt('mark_tst_ects'));
	}
	
	private function getEctsGrade()
	{
		return $this->resultData['ects_grade'];
	}
}