<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/interfaces/interface.ilQuestionHeaderBlockBuilder.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestQuestionHeaderBlockBuilder implements ilQuestionHeaderBlockBuilder
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var integer
	 */
	protected $headerMode;

	/**
	 * @var string
	 */
	protected $questionTitle;

	/**
	 * @var float
	 */
	protected $questionPoints;

	/**
	 * @var integer
	 */
	protected $questionPosition;

	/**
	 * @var integer
	 */
	protected $questionCount;

	/**
	 * @var bool
	 */
	protected $questionPostponed;

	/**
	 * @var bool
	 */
	protected $questionObligatory;

	/**
	 * @var string
	 */
	protected $questionRelatedObjectives;

	function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
		
		$this->headerMode = null;
		$this->questionTitle = '';
		$this->questionPoints = 0.0;
		$this->questionPosition = 0;
		$this->questionCount = 0;
		$this->questionPostponed = false;
		$this->questionObligatory = false;
		$this->questionRelatedObjectives = '';
	}

	/**
	 * @return int
	 */
	public function getHeaderMode()
	{
		return $this->headerMode;
	}

	/**
	 * @param int $headerMode
	 */
	public function setHeaderMode($headerMode)
	{
		$this->headerMode = $headerMode;
	}

	/**
	 * @return string
	 */
	public function getQuestionTitle()
	{
		return $this->questionTitle;
	}

	/**
	 * @param string $questionTitle
	 */
	public function setQuestionTitle($questionTitle)
	{
		$this->questionTitle = $questionTitle;
	}

	/**
	 * @return float
	 */
	public function getQuestionPoints()
	{
		return $this->questionPoints;
	}

	/**
	 * @param float $questionPoints
	 */
	public function setQuestionPoints($questionPoints)
	{
		$this->questionPoints = $questionPoints;
	}

	/**
	 * @return int
	 */
	public function getQuestionPosition()
	{
		return $this->questionPosition;
	}

	/**
	 * @param int $questionPosition
	 */
	public function setQuestionPosition($questionPosition)
	{
		$this->questionPosition = $questionPosition;
	}

	/**
	 * @return int
	 */
	public function getQuestionCount()
	{
		return $this->questionCount;
	}

	/**
	 * @param int $questionCount
	 */
	public function setQuestionCount($questionCount)
	{
		$this->questionCount = $questionCount;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionPostponed()
	{
		return $this->questionPostponed;
	}

	/**
	 * @param boolean $questionPostponed
	 */
	public function setQuestionPostponed($questionPostponed)
	{
		$this->questionPostponed = $questionPostponed;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionObligatory()
	{
		return $this->questionObligatory;
	}

	/**
	 * @param boolean $questionObligatory
	 */
	public function setQuestionObligatory($questionObligatory)
	{
		$this->questionObligatory = $questionObligatory;
	}

	/**
	 * @return string
	 */
	public function getQuestionRelatedObjectives()
	{
		return $this->questionRelatedObjectives;
	}

	/**
	 * @param string $questionRelatedObjectives
	 */
	public function setQuestionRelatedObjectives($questionRelatedObjectives)
	{
		$this->questionRelatedObjectives = $questionRelatedObjectives;
	}
	
	protected function buildQuestionPositionString()
	{
		if( $this->getQuestionCount() )
		{
			return sprintf($this->lng->txt("tst_position"), $this->getQuestionPosition(), $this->getQuestionCount());
		}

		return sprintf($this->lng->txt("tst_position_without_total"), $this->getQuestionPosition());
	}

	protected function buildQuestionPointsString()
	{
		if( $this->getQuestionPoints() == 1 )
		{
			return " ({$this->getQuestionPoints()} {$this->lng->txt('point')})";
		}

		return " ({$this->getQuestionPoints()} {$this->lng->txt('points')})";
	}
	
	protected function buildQuestionPostponedString()
	{
		if( $this->isQuestionPostponed() )
		{
			return " <em>(" . $this->lng->txt("postponed") . ")</em>";
		}
		
		return '';
	}
	
	protected function buildQuestionObligatoryString()
	{
		if( $this->isQuestionObligatory() )
		{
			$obligatoryText = $this->lng->txt("tst_you_have_to_answer_this_question");
			return '<br /><span class="obligatory" style="font-size:small">'.$obligatoryText.'</span>';
		}
		
		return '';
	}
	
	protected function buildQuestionRelatedObjectivesString()
	{
		if( strlen($this->getQuestionRelatedObjectives()) )
		{
			$label = $this->lng->txt('tst_res_lo_objectives_header');
			return '<div class="ilTestQuestionRelatedObjectivesInfo">'.$label.': '.$this->getQuestionRelatedObjectives();
		}
		
		return '';
	}

	public function getHTML()
	{
		$headerBlock = $this->buildQuestionPositionString();

		switch( $this->getHeaderMode() )
		{
			case 1: 

				$headerBlock .= " - ".$this->getQuestionTitle();
				$headerBlock .= $this->buildQuestionPostponedString();  
				$headerBlock .= $this->buildQuestionObligatoryString();  
				break;
			
			case 2:

				$headerBlock .= $this->buildQuestionPostponedString();
				$headerBlock .= $this->buildQuestionObligatoryString();
				break;
			
			case 0:
			default:

				$headerBlock .= " - ".$this->getQuestionTitle();
				$headerBlock .= $this->buildQuestionPostponedString();
				$headerBlock .= $this->buildQuestionPointsString();
				$headerBlock .= $this->buildQuestionObligatoryString();
		}

		$headerBlock .= $this->buildQuestionRelatedObjectivesString();

		return $headerBlock;
	}
} 