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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for error text answers
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assAnswerErrorText
{
    protected string $text_wrong;
    protected string $text_correct;
    protected float  $points;
    protected ?int $position;
    protected int $length;

    /**
     * assAnswerErrorText constructor
     * @param string $text_wrong   Wrong text
     * @param string $text_correct Correct text
     * @param double $points       Points
     */
    public function __construct(
        string $text_wrong = "",
        string $text_correct = "",
        float $points = 0.0,
        ?int $position = null
    ) {
        $this->text_wrong = $text_wrong;
        $this->text_correct = $text_correct;
        $this->points = $points;
        $this->position = $position;

        $word_array = preg_split("/\s+/", $text_wrong);

        if ($word_array) {
            $this->length = count($word_array);
        }
    }

    public function getTextWrong(): string
    {
        return $this->text_wrong;
    }

    public function getTextCorrect(): string
    {
        return $this->text_correct;
    }

    public function getPoints(): string
    {
        return $this->points;
    }

    public function withPoints(float $points): self
    {
        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function withPosition(int $position): self
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
