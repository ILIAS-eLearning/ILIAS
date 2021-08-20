<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSequentialDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceSequentialDocumentEvaluation implements ilTermsOfServiceDocumentEvaluation
{
    protected ilTermsOfServiceDocumentCriteriaEvaluation $evaluation;
    protected ilObjUser $user;
    /** @var array<int, ilTermsOfServiceDocument[]> */
    protected array $matchingDocumentsByUser = [];
    /** @var ilTermsOfServiceSignableDocument[] */
    protected array $possibleDocuments = [];
    protected ilLogger $log;

    public function __construct(
        ilTermsOfServiceDocumentCriteriaEvaluation $evaluation,
        ilObjUser $user,
        ilLogger $log,
        array $possibleDocuments
    ) {
        $this->evaluation = $evaluation;
        $this->user = $user;
        $this->log = $log;
        $this->possibleDocuments = $possibleDocuments;
    }

    public function withContextUser(ilObjUser $user) : ilTermsOfServiceDocumentEvaluation
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->evaluation = $clone->evaluation->withContextUser($user);

        return $clone;
    }

    /**
     * @return ilTermsOfServiceSignableDocument[]
     */
    protected function getMatchingDocuments() : array
    {
        if (!array_key_exists((int) $this->user->getId(), $this->matchingDocumentsByUser)) {
            $this->matchingDocumentsByUser[(int) $this->user->getId()] = [];

            $this->log->debug(sprintf(
                'Evaluating document for user "%s" (id: %s) ...',
                $this->user->getLogin(),
                $this->user->getId()
            ));

            foreach ($this->possibleDocuments as $document) {
                if ($this->evaluateDocument($document)) {
                    $this->matchingDocumentsByUser[(int) $this->user->getId()][] = $document;
                }
            }

            $this->log->debug(sprintf(
                '%s matching document(s) found',
                count($this->matchingDocumentsByUser[(int) $this->user->getId()])
            ));
        }

        return $this->matchingDocumentsByUser[(int) $this->user->getId()];
    }

    public function evaluateDocument(ilTermsOfServiceSignableDocument $document) : bool
    {
        return $this->evaluation->evaluate($document);
    }

    public function document() : ilTermsOfServiceSignableDocument
    {
        $matchingDocuments = $this->getMatchingDocuments();
        if (count($matchingDocuments) > 0) {
            return $matchingDocuments[0];
        }

        throw new ilTermsOfServiceNoSignableDocumentFoundException(sprintf(
            'Could not find any terms of service document for the passed user (id: %s|login: %s)',
            $this->user->getId(),
            $this->user->getLogin()
        ));
    }

    public function hasDocument() : bool
    {
        return count($this->getMatchingDocuments()) > 0;
    }
}
