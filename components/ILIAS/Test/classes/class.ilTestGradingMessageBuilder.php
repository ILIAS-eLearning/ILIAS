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

use ILIAS\Test\Results\Data\ParticipantResult;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestGradingMessageBuilder
{
    private ilTemplate $tpl;

    /**
     * @var array<string> $messageText
     */
    private array $messageText = [];

    public function __construct(
        private ilLanguage $lng,
        private ilGlobalTemplateInterface $main_tpl,
        private ilObjTest $testOBJ,
        private readonly ParticipantResult $result
    ) {
    }

    public function buildMessage()
    {
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

    public function sendMessage()
    {
        if (!$this->testOBJ->isShowGradingStatusEnabled()) {
            $this->main_tpl->setOnScreenMessage('info', $this->getFullMessage());
        } elseif ($this->result->isPassed()) {
            $this->main_tpl->setOnScreenMessage('success', $this->getFullMessage());
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->getFullMessage());
        }
    }

    private function buildGradingStatusMsg(): string
    {
        if ($this->result->isPassed()) {
            return $this->lng->txt('grading_status_passed_msg');
        }

        return $this->lng->txt('grading_status_failed_msg');
    }

    private function buildGradingMarkMsg()
    {
        $markMsg = $this->lng->txt('grading_mark_msg');

        $markMsg = str_replace("[mark]", $this->result->getMarkOfficial(), $markMsg);
        $markMsg = str_replace("[markshort]", $this->result->getMarkShort(), $markMsg);
        $markMsg = str_replace("[percentage]", sprintf("%.2f", $this->result->getPercentage()), $markMsg);
        $markMsg = str_replace("[reached]", (string) $this->result->getReachedPoints(), $markMsg);
        $markMsg = str_replace("[max]", (string) $this->result->getMaxPoints(), $markMsg);

        return $markMsg;
    }

    public function buildList()
    {
        $this->initListTemplate();

        if ($this->testOBJ->isShowGradingStatusEnabled()) {
            $passedStatusLangVar = $this->result->isPassed() ? 'passed_official' : 'failed_official';

            $this->populateListEntry(
                $this->lng->txt('passed_status'),
                $this->lng->txt($passedStatusLangVar)
            );
        }

        if ($this->testOBJ->isShowGradingMarkEnabled()) {
            $this->populateListEntry($this->lng->txt('tst_mark'), $this->result->getMarkOfficial());
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
