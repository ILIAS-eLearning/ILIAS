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
 * @author         Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 * @implements Iterator<int, ilTestRandomQuestionCollectionSubsetApplication>
 */
class ilTestRandomQuestionCollectionSubsetApplicationList implements Iterator
{
    /** @var array<int, ilTestRandomQuestionCollectionSubsetApplication> */
    protected array $collectionSubsetApplications = [];
    protected ilTestRandomQuestionSetQuestionCollection $reservedQuestionCollection;

    public function __construct()
    {
        $this->setReservedQuestionCollection(new ilTestRandomQuestionSetQuestionCollection());
    }

    /**
     * @param int $applicantId
     */
    public function getCollectionSubsetApplication($applicantId): ?ilTestRandomQuestionCollectionSubsetApplication
    {
        return $this->collectionSubsetApplications[$applicantId] ?? null;
    }

    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication[]
     */
    public function getCollectionSubsetApplications(): array
    {
        return $this->collectionSubsetApplications;
    }

    public function addCollectionSubsetApplication(
        ilTestRandomQuestionCollectionSubsetApplication $collectionSubsetApplication
    ): void {
        $this->collectionSubsetApplications[$collectionSubsetApplication->getApplicantId()] = $collectionSubsetApplication;
    }

    /**
     * @param array<int, ilTestRandomQuestionCollectionSubsetApplication> $collectionSubsetApplications
     */
    public function setCollectionSubsetApplications(array $collectionSubsetApplications): void
    {
        $this->collectionSubsetApplications = $collectionSubsetApplications;
    }

    public function resetCollectionSubsetApplicants(): void
    {
        $this->setCollectionSubsetApplications([]);
    }

    public function getReservedQuestionCollection(): ilTestRandomQuestionSetQuestionCollection
    {
        return $this->reservedQuestionCollection;
    }

    public function setReservedQuestionCollection(
        ilTestRandomQuestionSetQuestionCollection $reservedQuestionCollection
    ): void {
        $this->reservedQuestionCollection = $reservedQuestionCollection;
    }

    public function addReservedQuestion(ilTestRandomQuestionSetQuestion $reservedQuestion): void
    {
        $this->getReservedQuestionCollection()->addQuestion($reservedQuestion);
    }

    public function current(): ilTestRandomQuestionCollectionSubsetApplication
    {
        return current($this->collectionSubsetApplications);
    }

    public function next(): void
    {
        next($this->collectionSubsetApplications);
    }

    public function key(): int
    {
        return key($this->collectionSubsetApplications);
    }

    public function valid(): bool
    {
        return key($this->collectionSubsetApplications) !== null;
    }

    public function rewind(): void
    {
        reset($this->collectionSubsetApplications);
    }

    public function handleQuestionRequest(ilTestRandomQuestionSetQuestion $question): void
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
