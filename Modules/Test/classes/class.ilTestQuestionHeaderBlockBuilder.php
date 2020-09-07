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

    // fau: testNav - answer status variable
    /**
     * @var boolean | null
     */
    protected $questionAnswered;
    // fau.

    public function __construct(ilLanguage $lng)
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

    // fau: testNav - setter for question answered
    /**
     * @param bool $questionAnswered
     */
    public function setQuestionAnswered($questionAnswered)
    {
        $this->questionAnswered = $questionAnswered;
    }
    // fau.
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

    // fau: testNav - get question answered status
    /**
     * @return boolean | null
     */
    public function isQuestionAnswered()
    {
        return $this->questionAnswered;
    }
    // fau.

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
        if (!$this->getQuestionPosition()) {
            return '';
        }
        
        if ($this->getQuestionCount()) {
            return sprintf($this->lng->txt("tst_position"), $this->getQuestionPosition(), $this->getQuestionCount());
        }

        return sprintf($this->lng->txt("tst_position_without_total"), $this->getQuestionPosition());
    }

    // fau: testNav - remove HTML from building strings (is now in tpl.tst_question_info.html)
    protected function buildQuestionPointsString()
    {
        if ($this->getQuestionPoints() == 1) {
            return "{$this->getQuestionPoints()} {$this->lng->txt('point')}";
        }

        return "{$this->getQuestionPoints()} {$this->lng->txt('points')}";
    }
    
    protected function buildQuestionPostponedString()
    {
        if ($this->isQuestionPostponed()) {
            return $this->lng->txt("postponed");
        }
        
        return '';
    }
    
    protected function buildQuestionObligatoryString()
    {
        if ($this->isQuestionObligatory()) {
            return $this->lng->txt("tst_you_have_to_answer_this_question");
        }
        
        return '';
    }
    
    protected function buildQuestionRelatedObjectivesString()
    {
        if (strlen($this->getQuestionRelatedObjectives())) {
            $label = $this->lng->txt('tst_res_lo_objectives_header');
            return $label . ': ' . $this->getQuestionRelatedObjectives();
        }
        
        return '';
    }
    // fau.


    // fau: testNav - split generation of presentation title and question info

    /**
     * Get the presentation title of the question
     * This is shown above the title line in a test run
     * @return	string
     */
    public function getPresentationTitle()
    {
        switch ($this->getHeaderMode()) {
            case 2: 	// neither titles nor points => show position as title
                return $this->buildQuestionPositionString();
                break;

            case 0:		// titles and points => show title here
            case 1:		// only titles => show title here
            default:
                return $this->getQuestionTitle();
        }
    }


    /**
     * Get the additional question info and answering status
     * This is shown below the title line in a test run
     * @return string		html code of the info block
     */
    public function getQuestionInfoHTML()
    {
        $tpl = new ilTemplate('tpl.tst_question_info.html', true, true, 'Modules/Test');

        // position and/or points
        switch ($this->getHeaderMode()) {
            case 1: // only titles =>  show position here
                $text = $this->buildQuestionPositionString();
                break;

            case 2: //	neither titles nor points => position is separate title, show nothing here
                $text = '';
                break;

            case 0: //  titles and points => show position and points here
            default:
                $text = $this->buildQuestionPositionString() . ' (' . $this->buildQuestionPointsString() . ')';
        }
        if ($this->isQuestionPostponed()) {
            $text .= ($text ? ', ' : '') . $this->buildQuestionPostponedString();
        }

        $tpl->setVariable('TXT_POSITION_POINTS', $text);

        // obligatory
        if ($this->isQuestionObligatory() && !$this->isQuestionAnswered()) {
            $tpl->setVariable('TXT_OBLIGATORY', $this->buildQuestionObligatoryString());
        }

        // objectives
        if (strlen($this->getQuestionRelatedObjectives())) {
            $tpl->setVariable('TXT_OBJECTIVES', $this->buildQuestionRelatedObjectivesString());
        }

        // answer status
        if ($this->isQuestionAnswered()) {
            $tpl->setVariable('HIDDEN_NOT_ANSWERED', 'hidden');
        } else {
            $tpl->setVariable('HIDDEN_ANSWERED', 'hidden');
        }

        $tpl->setVariable('SRC_ANSWERED', ilUtil::getImagePath('answered.svg'));
        $tpl->setVariable('SRC_NOT_ANSWERED', ilUtil::getImagePath('answered_not.svg'));
        $tpl->setVariable('TXT_ANSWERED', $this->lng->txt('tst_answer_status_answered'));
        $tpl->setVariable('TXT_NOT_ANSWERED', $this->lng->txt('tst_answer_status_not_answered'));
        $tpl->setVariable('TXT_EDITING', $this->lng->txt('tst_answer_status_editing'));

        return $tpl->get();
    }
    // fau.

    public function getHTML()
    {
        $headerBlock = $this->buildQuestionPositionString();

        switch ($this->getHeaderMode()) {
            case 1:

                $headerBlock .= " - " . $this->getQuestionTitle();
                $headerBlock .= $this->buildQuestionPostponedString();
                $headerBlock .= $this->buildQuestionObligatoryString();
                break;
            
            case 2:

                $headerBlock .= $this->buildQuestionPostponedString();
                $headerBlock .= $this->buildQuestionObligatoryString();
                break;
            
            case 0:
            default:

                $headerBlock .= " - " . $this->getQuestionTitle();
                $headerBlock .= $this->buildQuestionPostponedString();
// fau: testNav - put the points in parentheses here, not in building the string
                $headerBlock .= ' (' . $this->buildQuestionPointsString() . ')';
// fau.
                $headerBlock .= $this->buildQuestionObligatoryString();
        }

        $headerBlock .= $this->buildQuestionRelatedObjectivesString();

        return $headerBlock;
    }
}
