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
 * Class ilAccessibilityTableDataProviderFactory
 */
class ilAccessibilityTableDataProviderFactory
{
    public const CONTEXT_DOCUMENTS = 'documents';

    protected ?ilDBInterface $db = null;

    public function getByContext(string $context) : ilAccessibilityTableDataProvider
    {
        switch ($context) {
            case self::CONTEXT_DOCUMENTS:
                return new ilAccessibilityDocumentTableDataProvider();

            default:
                throw new InvalidArgumentException('Provider not supported');
        }
    }

    /**
     * @throws ilAccessibilityMissingDatabaseAdapterException
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
     * @throws InvalidArgumentException
     */
    protected function getExceptionByMember(string $member) : ilAccessibilityMissingDatabaseAdapterException
    {
        switch ($member) {
            case 'db':
                return new ilAccessibilityMissingDatabaseAdapterException(
                    'Incomplete factory configuration. Please inject a database adapter.'
                );

            default:
                throw new InvalidArgumentException("Exception for member {$member} not supported");
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
