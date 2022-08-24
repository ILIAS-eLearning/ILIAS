<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for true/false or yes/no answers
 *
 * ASS_AnswerBinaryState is a class for answers with a binary state indicator (checked/unchecked, set/unset)
 *
 *
 * @todo Get rid of duplicate methods (hiding behind different names.
 * @todo Rework class to use a true binary state (boolean) instead of integer
 * @todo Rename class to something that matches the filename properly.
 *
 * @author  Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author  Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @see ASS_AnswerSimple
 */
class ASS_AnswerBinaryState extends ASS_AnswerSimple
{
    private bool $checked;

    /**
     * ASS_AnswerBinaryState constructor
     * The constructor takes possible arguments and creates an instance of the ASS_AnswerBinaryState object.
     * @param string  $answertext A string defining the answer text
     * @param double  $points     The number of points given for the selected answer
     * @param integer $order      A nonnegative value representing a possible display or sort order
     * @param integer $state      A integer value indicating the state of the answer
     * @param integer $id         The database id of the answer
     */
    public function __construct(string $answertext = "", float $points = 0.0, int $order = 0, bool $checked = false, int $id = -1)
    {
        parent::__construct($answertext, $points, $order, $id);
        $this->checked = $checked;
    }

    /**
     * Gets the state
     *
     * Returns the state of the answer
     *
     * @return boolean state
     * @see $state
     */
    public function getState(): bool
    {
        return $this->checked;
    }

    /**
     * Gets the state
     *
     * Returns the answer state
     *
     * @return boolean state
     * @see $state
     */
    public function isStateChecked(): bool
    {
        return $this->checked;
    }

    /**
     * Gets the state
     *
     * Returns the answer state
     *
     * @return boolean state
     * @see $state
     */
    public function isStateSet(): bool
    {
        return $this->checked;
    }

    /**
     * Gets the state
     *
     * Returns the answer state
     *
     * @return boolean state
     * @see $state
     */
    public function isStateUnset(): bool
    {
        return !$this->checked;
    }

    /**
     * Gets the state
     *
     * Returns the answer state
     *
     * @return boolean state
     * @see $state
     */
    public function isStateUnchecked(): bool
    {
        return !$this->checked;
    }

    /**
     * Sets the state
     *
     * Sets the state of the answer using 1 or 0 as indicator
     *
     * @param bool|int $state A integer value indicating the state of the answer
     *
     * @see $state
     */
    public function setState(bool $state = false): void
    {
        $this->checked = $state;
    }

    /**
     * Sets the answer as a checked answer
     *
     * Sets the state value of the answer to 1
     *
     * @see $state
     */
    public function setChecked(): void
    {
        $this->checked = true;
    }

    /**
     * Sets the answer as a set answer
     *
     * Sets the state value of the answer to 1
     *
     * @see $state
     */
    public function setSet(): void
    {
        $this->checked = true;
    }

    /**
     * Sets the answer as a unset answer
     *
     * Sets the state value of the answer to 0
     *
     * @see $state
     */
    public function setUnset(): void
    {
        $this->checked = false;
    }

    /**
     * Sets the answer as a unchecked answer
     *
     * Sets the state value of the answer to 0
     *
     * @see $state
     */
    public function setUnchecked(): void
    {
        $this->checked = false;
    }
}
