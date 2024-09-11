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
 */
class ilTestQuestionHeaderBlockBuilder implements ilQuestionHeaderBlockBuilder
{
    protected ?int $header_mode = null;
    protected string $question_title = '';
    protected float $question_points = 0.0;
    protected int $question_position = 0;
    protected int $question_count = 0;
    protected bool $question_postponed = false;
    protected string $question_related_objectives = '';
    protected ?bool $question_answered = null;

    public function __construct(
        private readonly ilLanguage $lng
    ) {
    }

    public function getHeaderMode(): ?int
    {
        return $this->header_mode;
    }

    public function setHeaderMode(int $header_mode): void
    {
        $this->header_mode = $header_mode;
    }

    public function getQuestionTitle(): string
    {
        return $this->question_title;
    }

    public function setQuestionTitle(string $question_title): void
    {
        $this->question_title = $question_title;
    }

    public function getQuestionPoints(): float
    {
        return $this->question_points;
    }

    public function setQuestionPoints(float $question_points): void
    {
        $this->question_points = $question_points;
    }

    public function setQuestionAnswered(bool $question_answered): void
    {
        $this->question_answered = $question_answered;
    }

    public function getQuestionPosition(): int
    {
        return $this->question_position;
    }

    public function setQuestionPosition(int $question_position): void
    {
        $this->question_position = $question_position;
    }

    public function getQuestionCount(): int
    {
        return $this->question_count;
    }

    public function setQuestionCount(int $question_count): void
    {
        $this->question_count = $question_count;
    }

    public function isQuestionPostponed(): bool
    {
        return $this->question_postponed;
    }

    public function isQuestionAnswered(): ?bool
    {
        return $this->question_answered;
    }

    public function setQuestionPostponed(bool $question_postponed): void
    {
        $this->question_postponed = $question_postponed;
    }

    public function getQuestionRelatedObjectives(): string
    {
        return $this->question_related_objectives;
    }

    public function setQuestionRelatedObjectives(string $question_related_objectives): void
    {
        $this->question_related_objectives = $question_related_objectives;
    }

    protected function buildQuestionPositionString(): string
    {
        if (!$this->getQuestionPosition()) {
            return '';
        }

        if ($this->getQuestionCount()) {
            return sprintf($this->lng->txt('tst_position'), $this->getQuestionPosition(), $this->getQuestionCount());
        }

        return sprintf($this->lng->txt('tst_position_without_total'), $this->getQuestionPosition());
    }

    protected function buildQuestionPointsString(): string
    {
        if ($this->getQuestionPoints() == 1) {
            return "{$this->getQuestionPoints()} {$this->lng->txt('point')}";
        }

        return "{$this->getQuestionPoints()} {$this->lng->txt('points')}";
    }

    protected function buildQuestionPostponedString(): string
    {
        if ($this->isQuestionPostponed()) {
            return $this->lng->txt('postponed');
        }

        return '';
    }

    protected function buildQuestionRelatedObjectivesString(): string
    {
        if (strlen($this->getQuestionRelatedObjectives())) {
            $label = $this->lng->txt('tst_res_lo_objectives_header');
            return $label . ': ' . $this->getQuestionRelatedObjectives();
        }

        return '';
    }

    public function getPresentationTitle(): string
    {
        switch ($this->getHeaderMode()) {
            case 3:     // only points => show no title here
                return $this->buildQuestionPointsString();
                break;
            case 2: 	// neither titles nor points => show position as title
                return $this->buildQuestionPositionString();
                break;

            case 0:		// titles and points => show title here
            case 1:		// only titles => show title here
            default:
                return $this->getQuestionTitle();
        }
    }

    public function getQuestionInfoHTML(): string
    {
        $tpl = new ilTemplate('tpl.tst_question_info.html', true, true, 'components/ILIAS/Test');

        // position and/or points
        switch ($this->getHeaderMode()) {
            case 1: // only titles =>  show position here
                $text = $this->buildQuestionPositionString();
                break;

            case 3: // only points => show nothing here
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

        if (strlen($this->getQuestionRelatedObjectives())) {
            $tpl->setVariable('TXT_OBJECTIVES', $this->buildQuestionRelatedObjectivesString());
        }

        if ($this->isQuestionAnswered()) {
            $tpl->setVariable('HIDDEN_NOT_ANSWERED', 'hidden');
        } else {
            $tpl->setVariable('HIDDEN_ANSWERED', 'hidden');
        }

        $tpl->setVariable('SRC_ANSWERED', ilUtil::getImagePath('object/answered.svg'));
        $tpl->setVariable('SRC_NOT_ANSWERED', ilUtil::getImagePath('object/answered_not.svg'));
        $tpl->setVariable('TXT_ANSWERED', $this->lng->txt('tst_answer_status_answered'));
        $tpl->setVariable('TXT_NOT_ANSWERED', $this->lng->txt('tst_answer_status_not_answered'));
        $tpl->setVariable('TXT_EDITING', $this->lng->txt('tst_answer_status_editing'));

        return $tpl->get();
    }

    public function getHTML(): string
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
