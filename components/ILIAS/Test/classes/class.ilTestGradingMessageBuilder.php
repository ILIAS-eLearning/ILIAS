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
    private ilTemplate $tpl;
    private array $result_data = [];
    private int $active_id;

    /**
     * @var array<string> $message_text
     */
    private array $message_text = [];

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly ilObjTest $test_obj
    ) {
    }

    public function setActiveId(int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function buildMessage(): void
    {
        $this->loadResultData();

        if ($this->test_obj->isShowGradingStatusEnabled()) {
            $this->addMessagePart($this->buildGradingStatusMsg());
        }

        if ($this->test_obj->isShowGradingMarkEnabled()) {
            $this->addMessagePart($this->buildGradingMarkMsg());
        }
    }

    private function addMessagePart(string $msg_part): void
    {
        $this->message_text[] = $msg_part;
    }

    private function getFullMessage(): string
    {
        return implode(' ', $this->message_text);
    }

    private function isPassed(): bool
    {
        return (bool) $this->result_data['passed'];
    }

    public function sendMessage(): void
    {
        $this->main_tpl->setOnScreenMessage(
            $this->test_obj->isShowGradingStatusEnabled() && $this->isPassed()
                ? 'success'
                : 'info',
            $this->getFullMessage()
        );
    }

    private function loadResultData(): void
    {
        $this->result_data = $this->test_obj->getResultsForActiveId($this->getActiveId());
    }

    private function buildGradingStatusMsg(): string
    {
        return $this->lng->txt($this->isPassed() ? 'grading_status_passed_msg' : 'grading_status_failed_msg');
    }

    private function buildGradingMarkMsg(): string
    {
        return str_replace(
            ['[mark]', '[markshort]', '[percentage]', '[reached]', '[max]'],
            [
                $this->getMarkOfficial(),
                $this->getMarkShort(),
                $this->getPercentage(),
                (string) $this->getReachedPoints(),
                (string) $this->getMaxPoints()
            ],
            $this->lng->txt('grading_mark_msg')
        );
    }

    private function getMarkOfficial(): string
    {
        return $this->result_data['mark_official'];
    }

    private function getMarkShort(): string
    {
        return $this->result_data['mark_short'];
    }

    private function getPercentage(): string
    {
        return sprintf(
            '%.2f',
            $this->getMaxPoints() > 0 ? ($this->getReachedPoints() / $this->getMaxPoints()) : 0
        );
    }

    private function getReachedPoints(): float
    {
        return $this->result_data['reached_points'];
    }

    private function getMaxPoints(): float
    {
        return $this->result_data['max_points'];
    }

    public function buildList(): void
    {
        $this->loadResultData();

        $this->initListTemplate();

        if ($this->test_obj->isShowGradingStatusEnabled()) {
            $this->populateListEntry(
                $this->lng->txt('passed_status'),
                $this->lng->txt($this->isPassed() ? 'passed_official' : 'failed_official')
            );
        }

        if ($this->test_obj->isShowGradingMarkEnabled()) {
            $this->populateListEntry($this->lng->txt('tst_mark'), $this->getMarkOfficial());
        }

        $this->parseListTemplate();
    }

    public function initListTemplate(): void
    {
        $this->tpl = new ilTemplate('tpl.tst_grading_msg_list.html', true, true, 'components/ILIAS/Test');
    }

    private function populateListEntry(string $label, string $value): void
    {
        $this->tpl->setCurrentBlock('grading_msg_entry');
        $this->tpl->setVariable('LABEL', $label);
        $this->tpl->setVariable('VALUE', $value);
        $this->tpl->parseCurrentBlock();
    }

    private function parseListTemplate(): void
    {
        $this->tpl->setCurrentBlock('grading_msg_list');
        $this->tpl->parseCurrentBlock();
    }

    public function getList(): string
    {
        return $this->tpl->get();
    }
}
