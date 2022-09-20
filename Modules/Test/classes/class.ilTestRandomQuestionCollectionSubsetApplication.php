<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestionCollection.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionCollectionSubsetApplication extends ilTestRandomQuestionSetQuestionCollection
{
    /**
     * @var integer
     */
    protected $applicantId;

    /**
     * @var integer
     */
    protected $requiredAmount;

    /**
     * @return int
     */
    public function getApplicantId(): int
    {
        return $this->applicantId;
    }

    /**
     * @param int $applicantId
     */
    public function setApplicantId($applicantId)
    {
        $this->applicantId = $applicantId;
    }

    /**
     * @return int
     */
    public function getRequiredAmount(): int
    {
        return $this->requiredAmount;
    }

    /**
     * @param int $requiredAmount
     */
    public function setRequiredAmount($requiredAmount)
    {
        $this->requiredAmount = $requiredAmount;
    }

    /*
     * returns the fact if required amount is still positive
     */
    public function hasRequiredAmountLeft(): bool
    {
        return $this->getRequiredAmount() > 0;
    }

    /**
     * decrements the amount required by applicant
     */
    public function decrementRequiredAmount()
    {
        $this->setRequiredAmount($this->getRequiredAmount() - 1);
    }

    /**
     * @return bool
     */
    public function hasQuestion($questionId): bool
    {
        return $this->getQuestion($questionId) !== null;
    }

    /**
     * @return ilTestRandomQuestionSetQuestion
     */
    public function getQuestion($questionId): ?ilTestRandomQuestionSetQuestion
    {
        foreach ($this as $question) {
            if ($question->getQuestionId() != $questionId) {
                continue;
            }

            return $question;
        }

        return null;
    }
}
