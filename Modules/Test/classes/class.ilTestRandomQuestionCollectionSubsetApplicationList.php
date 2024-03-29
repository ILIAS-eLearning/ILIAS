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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 */
class ilTestRandomQuestionCollectionSubsetApplicationList implements Iterator
{
    /**
     * @var ilTestRandomQuestionCollectionSubsetApplication[]
     */
    protected $collectionSubsetApplications = array();

    /**
     * @var ilTestRandomQuestionSetQuestionCollection
     */
    protected $reservedQuestionCollection;

    /**
     * ilTestRandomQuestionCollectionSubsetApplicantList constructor.
     */
    public function __construct()
    {
        $this->setReservedQuestionCollection(new ilTestRandomQuestionSetQuestionCollection());
    }

    /**
     * @param integer $applicantId
     * @return ilTestRandomQuestionCollectionSubsetApplication
     */
    public function getCollectionSubsetApplication($applicantId): ?ilTestRandomQuestionCollectionSubsetApplication
    {
        if (!isset($this->collectionSubsetApplications[$applicantId])) {
            return null;
        }

        return $this->collectionSubsetApplications[$applicantId];
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication[]
     */
    public function getCollectionSubsetApplications(): array
    {
        return $this->collectionSubsetApplications;
    }

    public function addCollectionSubsetApplication(ilTestRandomQuestionCollectionSubsetApplication $collectionSubsetApplication)
    {
        $this->collectionSubsetApplications[$collectionSubsetApplication->getApplicantId()] = $collectionSubsetApplication;
    }

    /**
     * @param ilTestRandomQuestionCollectionSubsetApplication[] $collectionSubsetApplications
     */
    public function setCollectionSubsetApplications($collectionSubsetApplications)
    {
        $this->collectionSubsetApplications = $collectionSubsetApplications;
    }

    /**
     * resetter for collectionSubsetApplicants
     */
    public function resetCollectionSubsetApplicants()
    {
        $this->setCollectionSubsetApplications(array());
    }

    /**
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getReservedQuestionCollection(): ilTestRandomQuestionSetQuestionCollection
    {
        return $this->reservedQuestionCollection;
    }

    /**
     * @param ilTestRandomQuestionSetQuestionCollection $reservedQuestionCollection
     */
    public function setReservedQuestionCollection($reservedQuestionCollection)
    {
        $this->reservedQuestionCollection = $reservedQuestionCollection;
    }

    /**
     * @param ilTestRandomQuestionSetQuestion $question
     */
    public function addReservedQuestion(ilTestRandomQuestionSetQuestion $reservedQuestion)
    {
        $this->getReservedQuestionCollection()->addQuestion($reservedQuestion);
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication|false
     */
    public function current()
    {
        return current($this->collectionSubsetApplications);
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication|false
     */
    public function next()
    {
        return next($this->collectionSubsetApplications);
    }
    /* @return string */
    public function key(): string
    {
        return key($this->collectionSubsetApplications);
    }
    /* @return bool */
    public function valid(): bool
    {
        return key($this->collectionSubsetApplications) !== null;
    }
    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication|false
     */
    public function rewind()
    {
        return reset($this->collectionSubsetApplications);
    }

    /**
     * @param ilTestRandomQuestionSetQuestion $question
     */
    public function handleQuestionRequest(ilTestRandomQuestionSetQuestion $question)
    {
        $questionReservationRequired = false;

        foreach ($this as $collectionSubsetApplication) {
            if (!$collectionSubsetApplication->hasQuestion($question->getQuestionId())) {
                continue;
            }

            if ($collectionSubsetApplication->hasRequiredAmountLeft()) {
                $questionReservationRequired = true;
                $collectionSubsetApplication->decrementRequiredAmount();
            }
        }

        if ($questionReservationRequired) {
            $this->addReservedQuestion($question);
        }
    }

    /**
     * @return int
     */
    public function getNonReservedQuestionAmount(): int
    {
        $availableQuestionCollection = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($this as $collectionSubsetApplication) {
            $applicationsNonReservedQstCollection = $collectionSubsetApplication->getRelativeComplementCollection(
                $this->getReservedQuestionCollection()
            );

            $availableQuestionCollection->mergeQuestionCollection($applicationsNonReservedQstCollection);
        }

        $nonReservedQuestionCollection = $availableQuestionCollection->getUniqueQuestionCollection();

        return $nonReservedQuestionCollection->getQuestionAmount();
    }
}
