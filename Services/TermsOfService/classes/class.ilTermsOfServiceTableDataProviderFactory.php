<?php declare(strict_types=1);

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
 * Class ilTermsOfServiceTableDataProviderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTableDataProviderFactory
{
    public const CONTEXT_ACCEPTANCE_HISTORY = 'acceptance_history';
    public const CONTEXT_DOCUMENTS = 'documents';

    protected ?ilDBInterface $db = null;

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
     * @return ilTermsOfServiceException
     * @throws InvalidArgumentException
     */
    protected function getExceptionByMember(string $member) : ilTermsOfServiceException
    {
        switch ($member) {
            case 'db':
                return new ilTermsOfServiceMissingDatabaseAdapterException(
                    'Incomplete factory configuration. Please inject a database adapter.'
                );

            default:
                throw new InvalidArgumentException("Exception for member $member not supported");
        }
    }

    public function setDatabaseAdapter(?ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function getDatabaseAdapter() : ?ilDBInterface
    {
        return $this->db;
    }
}
