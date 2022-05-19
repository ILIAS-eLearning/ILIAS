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
 * Interface ilAccessibilitySequentialDocumentEvaluation
 */
class ilAccessibilitySequentialDocumentEvaluation implements ilAccessibilityDocumentEvaluation
{
    protected ilAccessibilityDocumentCriteriaEvaluation $evaluation;
    protected ilObjUser $user;
    /** @var ilAccessibilityDocument[]|null */
    protected ?array $matchingDocuments = null;
    /** @var ilAccessibilitySignableDocument[] */
    protected ?array $possibleDocuments = [];
    protected ilLogger $log;

    public function __construct(
        ilAccessibilityDocumentCriteriaEvaluation $evaluation,
        ilObjUser $user,
        ilLogger $log,
        array $possibleDocuments
    ) {
        $this->evaluation = $evaluation;
        $this->user = $user;
        $this->log = $log;
        $this->possibleDocuments = $possibleDocuments;
    }

    /**
     * @return ilAccessibilitySignableDocument[]
     */
    protected function getMatchingDocuments() : array
    {
        if (null === $this->matchingDocuments) {
            $this->matchingDocuments = [];

            $this->log->debug(sprintf(
                'Evaluating document for user "%s" (id: %s) ...',
                $this->user->getLogin(),
                $this->user->getId()
            ));

            foreach ($this->possibleDocuments as $document) {
                if ($this->evaluation->evaluate($document)) {
                    $this->matchingDocuments[] = $document;
                }
            }

            $this->log->debug(sprintf(
                '%s matching document(s) found',
                count($this->matchingDocuments)
            ));
        }

        return $this->matchingDocuments;
    }

    public function document() : ilAccessibilitySignableDocument
    {
        $matchingDocuments = $this->getMatchingDocuments();
        if (count($matchingDocuments) > 0) {
            return $matchingDocuments[0];
        }

        throw new ilAccessibilityNoSignableDocumentFoundException(sprintf(
            'Could not find any accessibility control concept document for the passed user (id: %s|login: %s)',
            $this->user->getId(),
            $this->user->getLogin()
        ));
    }

    public function hasDocument() : bool
    {
        return count($this->getMatchingDocuments()) > 0;
    }
}
