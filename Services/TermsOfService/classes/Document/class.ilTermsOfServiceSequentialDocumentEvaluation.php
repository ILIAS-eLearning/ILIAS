<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSequentialDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceSequentialDocumentEvaluation implements ilTermsOfServiceDocumentEvaluation
{
    /** @var ilTermsOfServiceDocumentCriteriaEvaluation */
    protected $evaluation;
    /** @var ilObjUser */
    protected $user;
    /** @var array<int, ilTermsOfServiceDocument[]> */
    protected $matchingDocumentsByUser = [];
    /** @var ilTermsOfServiceSignableDocument[] */
    protected $possibleDocuments = [];
    /** @var ilLogger */
    protected $log;

    /**
     * ilTermsOfServiceDocumentLogicalAndCriteriaEvaluation constructor.
     * @param ilTermsOfServiceDocumentCriteriaEvaluation $evaluation
     * @param ilObjUser                                  $user
     * @param ilLogger                                   $log
     * @param ilTermsOfServiceSignableDocument[]         $possibleDocuments
     */
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

    /**
     * @param ilObjUser $user
     * @return ilTermsOfServiceSignableDocument[]
     */
    protected function getMatchingDocuments(ilObjUser $user) : array
    {
        if (!array_key_exists((int) $user->getId(), $this->matchingDocumentsByUser)) {
            $this->matchingDocumentsByUser[(int) $user->getId()] = [];

            $this->log->debug(sprintf(
                'Evaluating document for user "%s" (id: %s) ...',
                $this->user->getLogin(),
                $this->user->getId()
            ));

            foreach ($this->possibleDocuments as $document) {
                if ($this->evaluateDocument($document, $user)) {
                    $this->matchingDocumentsByUser[(int) $user->getId()][] = $document;
                }
            }

            $this->log->debug(sprintf(
                '%s matching document(s) found',
                count($this->matchingDocumentsByUser[(int) $user->getId()])
            ));
        }

        return $this->matchingDocumentsByUser[(int) $user->getId()];
    }

    /**
     * @inheritDoc
     */
    public function evaluateDocument(ilTermsOfServiceSignableDocument $document, ilObjUser $user = null) : bool
    {
        if (null === $user) {
            $user = $this->user;
        }

        return $this->evaluation->evaluate($document, $user);
    }

    /**
     * @inheritdoc
     */
    public function document(ilObjUser $user = null) : ilTermsOfServiceSignableDocument
    {
        if (null === $user) {
            $user = $this->user;
        }

        $matchingDocuments = $this->getMatchingDocuments($user);
        if (count($matchingDocuments) > 0) {
            return $matchingDocuments[0];
        }

        throw new ilTermsOfServiceNoSignableDocumentFoundException(sprintf(
            'Could not find any terms of service document for the passed user (id: %s|login: %s)',
            $user->getId(),
            $user->getLogin()
        ));
    }

    /**
     * @inheritdoc
     */
    public function hasDocument(ilObjUser $user = null) : bool
    {
        if (null === $user) {
            $user = $this->user;
        }

        return count($this->getMatchingDocuments($user)) > 0;
    }
}
