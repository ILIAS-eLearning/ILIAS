<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTableDataProviderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTableDataProviderFactory
{
    const CONTEXT_ACCEPTANCE_HISTORY = 'acceptance_history';
    const CONTEXT_DOCUMENTS = 'documents';

    /** @var ilDBInterface|null */
    protected $db;

    /**
     * @param string $context
     * @return ilTermsOfServiceTableDataProvider
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws InvalidArgumentException
     */
    public function getByContext(string $context) : ilTermsOfServiceTableDataProvider
    {
        switch ($context) {
            case self::CONTEXT_ACCEPTANCE_HISTORY:
                $this->validateConfiguration(['db']);
                return new ilTermsOfServiceAcceptanceHistoryProvider($this->getDatabaseAdapter());

            case self::CONTEXT_DOCUMENTS:
                return new ilTermsOfServiceDocumentTableDataProvider();

            default:
                throw new InvalidArgumentException('Provider not supported');
        }
    }

    /**
     * @param array $mandatoryMemberVariables
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    protected function validateConfiguration(array $mandatoryMemberVariables) : void
    {
        foreach ($mandatoryMemberVariables as $member) {
            if (null === $this->{$member}) {
                $exception = $this->getExceptionByMember($member);
                throw $exception;
            }
        }
    }

    /**
     * @param string $member
     * @return ilTermsOfServiceMissingDatabaseAdapterException
     * @throws InvalidArgumentException
     */
    protected function getExceptionByMember(string $member)
    {
        switch ($member) {
            case 'db':
                return new ilTermsOfServiceMissingDatabaseAdapterException(
                    'Incomplete factory configuration. Please inject a database adapter.'
                );

            default:
                throw new InvalidArgumentException("Exception for member {$member} not supported");
        }
    }

    /**
     * @param ilDBInterface|null $db
     */
    public function setDatabaseAdapter(?ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return ilDBInterface|null
     */
    public function getDatabaseAdapter() : ?ilDBInterface
    {
        return $this->db;
    }
}
