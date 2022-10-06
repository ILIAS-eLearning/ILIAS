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
 * Class for cloze question numeric answers
 *
 * assAnswerCloze is a class for cloze questions numeric answers.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @see ASS_AnswerBinaryState
 *
 * @TODO Rework class to represent bounds as numerics instead of strings.
 */
class assAnswerCloze extends ASS_AnswerSimple
{
    protected ?string $lowerBound;

    /**
     * Name of the upper bound
     *
     * A string value defining the upper bound
     * of a numeric value
     *
     * @var string
     */
    protected ?string $upperBound;

    protected int $gap_size;


    /**
     * assAnswerCloze constructor
     *
     * The constructor takes possible arguments an creates an instance of the assAnswerCloze object.
     *
     * @param string $answertext A string defining the answer text
     * @param double $points The number of points given for the selected answer
     * @param integer $order A nonnegative value representing a possible display or sort order
     * @TODO See if the initialization of the bounds to null can be avoided to have them string/numeric at all times.
     */
    public function __construct(string $answertext = "", float $points = 0.0, int $order = 0, int $id = -1, int $state = 0)
    {
        parent::__construct($answertext, $points, $order, $id, $state);
        $this->lowerBound = null;
        $this->upperBound = null;
        $this->gap_size = 0;
    }

    // fau: fixGapFormula - allow formula evaluation when checking bounds, save bound text instead of number
    /**
     * Sets the lower boind
     * @param $bound string A string defining the lower bound of an answer for numeric gaps.
     * @TODO: Refactor method to get rid of "locale magic".
     */
    public function setLowerBound(string $bound): void
    {
        $boundvalue = $this->getNumericValueFromText($bound);
        $value = $this->getNumericValueFromAnswerText();

        if ($boundvalue === false || $boundvalue > $value) {
            $this->lowerBound = $this->getAnswertext();
        } else {
            $this->lowerBound = $bound;
        }
    }

    /**
     * Sets the upper bound
     * @param $bound string A string defining the upper bound of an answer for numeric gaps.
     * @TODO: Refactor method to get rid of "locale magic".
     */
    public function setUpperBound(string $bound): void
    {
        $boundvalue = $this->getNumericValueFromText($bound);
        $value = $this->getNumericValueFromAnswerText();

        if ($boundvalue === false || $boundvalue < $value) {
            $this->upperBound = $this->getAnswertext();
        } else {
            $this->upperBound = $bound;
        }
    }

    protected function getNumericValueFromAnswerText()
    {
        return $this->getNumericValueFromText($this->getAnswertext());
    }

    protected function getNumericValueFromText($text)
    {
        include_once("./Services/Math/classes/class.EvalMath.php");
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        return $eval->e(str_replace(",", ".", ilUtil::stripSlashes($text, false)));
    }
    // fau.

    /**
     * Returns the lower bound
     *
     * @return null|string
     */
    public function getLowerBound(): ?string
    {
        return $this->lowerBound;
    }

    /**
     * Returns the upper bound
     *
     * @return null|string
     */
    public function getUpperBound(): ?string
    {
        return $this->upperBound;
    }

    /**
     * @param int $gap_size
     */
    public function setGapSize(int $gap_size): void
    {
        $this->gap_size = $gap_size;
    }

    /**
     * @return int
     */
    public function getGapSize(): int
    {
        return $this->gap_size;
    }
}
