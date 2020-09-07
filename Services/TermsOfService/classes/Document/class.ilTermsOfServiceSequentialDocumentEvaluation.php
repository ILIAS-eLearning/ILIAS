<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSequentialDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceSequentialDocumentEvaluation implements \ilTermsOfServiceDocumentEvaluation
{
    /** @var \ilTermsOfServiceDocumentCriteriaEvaluation */
    protected $evaluation;

    /** @var \ilObjUser */
    protected $user;

    /** @var \ilTermsOfServiceDocument[]|null */
    protected $matchingDocuments = null;

    /** @var \ilTermsOfServiceSignableDocument[] */
    protected $possibleDocuments = [];

    /** @var \ilLogger */
    protected $log;

    /**
     * ilTermsOfServiceDocumentLogicalAndCriteriaEvaluation constructor.
     * @param \ilTermsOfServiceDocumentCriteriaEvaluation $evaluation
     * @param \ilObjUser $user
     * @param \ilLogger $log
     * @param \ilTermsOfServiceSignableDocument[] $possibleDocuments
     */
    public function __construct(
        \ilTermsOfServiceDocumentCriteriaEvaluation $evaluation,
        \ilObjUser $user,
        \ilLogger $log,
        array $possibleDocuments
    ) {
        $this->evaluation = $evaluation;
        $this->user = $user;
        $this->log = $log;
        $this->possibleDocuments = $possibleDocuments;
    }

    /**
     * @return \ilTermsOfServiceSignableDocument[]
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
    public function document() : \ilTermsOfServiceSignableDocument
    {
        $matchingDocuments = $this->getMatchingDocuments();
        if (count($matchingDocuments) > 0) {
            return $matchingDocuments[0];
        }

        throw new \ilTermsOfServiceNoSignableDocumentFoundException(sprintf(
            'Could not find any terms of service document for the passed user (id: %s|login: %s)',
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
