<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilitySequentialDocumentEvaluation
 */
class ilAccessibilitySequentialDocumentEvaluation implements ilAccessibilityDocumentEvaluation
{
    /** @var ilAccessibilityDocumentCriteriaEvaluation */
    protected $evaluation;

    /** @var ilObjUser */
    protected $user;

    /** @var ilAccessibilityDocument[]|null */
    protected $matchingDocuments = null;

    /** @var ilAccessibilitySignableDocument[] */
    protected $possibleDocuments = [];

    /** @var ilLogger */
    protected $log;

    /**
     * ilAccessibilityDocumentLogicalAndCriteriaEvaluation constructor.
     * @param ilAccessibilityDocumentCriteriaEvaluation $evaluation
     * @param ilObjUser                                  $user
     * @param ilLogger                                   $log
     * @param ilAccessibilitySignableDocument[]         $possibleDocuments
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function hasDocument() : bool
    {
        return count($this->getMatchingDocuments()) > 0;
    }
}
