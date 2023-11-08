<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestGradingMessageBuilder
{
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
     * @var \ILIAS\DI\Container
     */
    private $container;

    /**
     * @var string[] $messageText
     */
    private array $messageText = [];

    /**
     * @param ilLanguage $lng
     * @param ilObjTest $testOBJ
     */
    public function __construct(
        private ilLanguage $lng,
        private ilGlobalTemplateInterface $main_tpl,
        private ilObjTest $testOBJ
    ) {
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getActiveId(): int
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
    }

    private function addMessagePart($msgPart)
    {
        $this->messageText[] = $msgPart;
    }

    private function getFullMessage(): string
    {
        return implode(' ', $this->messageText);
    }

    private function isPassed(): bool
    {
        return (bool) $this->resultData['passed'];
    }

    public function sendMessage()
    {
        if (!$this->testOBJ->isShowGradingStatusEnabled()) {
            $this->main_tpl->setOnScreenMessage('info', $this->getFullMessage());
        } elseif ($this->isPassed()) {
            $this->main_tpl->setOnScreenMessage('success', $this->getFullMessage());
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->getFullMessage());
        }
    }

    private function loadResultData()
    {
        $this->resultData = $this->testOBJ->getResultsForActiveId($this->getActiveId());
    }

    private function buildGradingStatusMsg(): string
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
        $markMsg = str_replace("[reached]", (string) $this->getReachedPoints(), $markMsg);
        $markMsg = str_replace("[max]", (string) $this->getMaxPoints(), $markMsg);

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

    private function getPercentage(): string
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

    private function areObligationsAnswered(): bool
    {
        return (bool) $this->resultData['obligations_answered'];
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

        $this->parseListTemplate();
    }

    public function initListTemplate()
    {
        $this->tpl = new ilTemplate('tpl.tst_grading_msg_list.html', true, true, 'components/ILIAS/Test');
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

    public function getList(): string
    {
        return $this->tpl->get();
    }
}
