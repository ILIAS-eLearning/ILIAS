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

    public function buildMessage()
    {
        $this->loadResultData();
        
        if ($this->testOBJ->isShowGradingStatusEnabled()) {
            $this->addMessagePart($this->buildGradingStatusMsg());
        }

        if ($this->testOBJ->isShowGradingMarkEnabled()) {
            $this->addMessagePart($this->buildGradingMarkMsg());
        }

        if ($this->testOBJ->getECTSOutput()) {
            $this->addMessagePart($this->buildEctsGradeMsg());
        }
    }

    private function addMessagePart($msgPart)
    {
        $this->messageText[] = $msgPart;
    }
    
    private function getFullMessage()
    {
        return implode(' ', $this->messageText);
    }

    private function isPassed()
    {
        return (bool) $this->resultData['passed'];
    }
    
    public function sendMessage()
    {
        if (!$this->testOBJ->isShowGradingStatusEnabled()) {
            ilUtil::sendInfo($this->getFullMessage());
        } elseif ($this->isPassed()) {
            ilUtil::sendSuccess($this->getFullMessage());
        } else {
            ilUtil::sendFailure($this->getFullMessage());
        }
    }
    
    private function loadResultData()
    {
        $this->resultData = $this->testOBJ->getResultsForActiveId($this->getActiveId());

        if ($this->testOBJ->getECTSOutput()) {
            $ectsMark = $this->testOBJ->getECTSGrade(
                $this->testOBJ->getTotalPointsPassedArray(),
                $this->resultData['reached_points'],
                $this->resultData['max_points']
            );

            $this->resultData['ects_grade'] = strtoupper($ectsMark);
        }
    }

    private function buildGradingStatusMsg()
    {
        if ($this->isPassed()) {
            return $this->lng->txt('grading_status_passed_msg');
        }

        return $this->lng->txt('grading_status_failed_msg');
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

        if ($this->getMaxPoints() > 0) {
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
    
    private function areObligationsAnswered()
    {
        return (bool) $this->resultData['obligations_answered'];
    }
    
    private function buildEctsGradeMsg()
    {
        return str_replace('[markects]', $this->getEctsGrade(), $this->lng->txt('mark_tst_ects'));
    }
    
    private function getEctsGrade()
    {
        return $this->resultData['ects_grade'];
    }
    
    public function buildList()
    {
        $this->loadResultData();

        $this->initListTemplate();

        if ($this->testOBJ->isShowGradingStatusEnabled()) {
            $passedStatusLangVar = $this->isPassed() ? 'passed_official' : 'failed_official';
            
            $this->populateListEntry(
                $this->lng->txt('passed_status'),
                $this->lng->txt($passedStatusLangVar)
            );
        }

        if ($this->testOBJ->areObligationsEnabled()) {
            if ($this->areObligationsAnswered()) {
                $obligAnsweredStatusLangVar = 'grading_obligations_answered_listentry';
            } else {
                $obligAnsweredStatusLangVar = 'grading_obligations_missing_listentry';
            }
            
            $this->populateListEntry(
                $this->lng->txt('grading_obligations_listlabel'),
                $this->lng->txt($obligAnsweredStatusLangVar)
            );
        }

        if ($this->testOBJ->isShowGradingMarkEnabled()) {
            $this->populateListEntry($this->lng->txt('tst_mark'), $this->getMarkOfficial());
        }

        if ($this->testOBJ->getECTSOutput()) {
            $this->populateListEntry($this->lng->txt('ects_grade'), $this->getEctsGrade());
        }
        
        $this->parseListTemplate();
    }

    public function initListTemplate()
    {
        $this->tpl = new ilTemplate('tpl.tst_grading_msg_list.html', true, true, 'Modules/Test');
    }

    private function populateListEntry($label, $value)
    {
        $this->tpl->setCurrentBlock('grading_msg_entry');
        $this->tpl->setVariable('LABEL', $label);
        $this->tpl->setVariable('VALUE', $value);
        $this->tpl->parseCurrentBlock();
    }

    private function parseListTemplate()
    {
        $this->tpl->setCurrentBlock('grading_msg_list');
        $this->tpl->parseCurrentBlock();
    }
    
    public function getList()
    {
        return $this->tpl->get();
    }
}
