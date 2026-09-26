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

declare(strict_types=1);

namespace ILIAS\Test\ExportImport\Import;

use ilDBInterface;
use ILIAS\Test\Settings\GlobalSettings\UserIdentifiers;
use ilImportMapping;
use Psr\Log\LoggerInterface;

class UserImportResolver
{
    public function __construct(
        private readonly ilDBInterface $db,
        private readonly LoggerInterface $log,
    ) {
    }

    /**
     * @param array<int, mixed> $users
     * @return array<int, int>
     */
    public function resolve(UserIdentifiers $criteria, array $users): array
    {
        $this->log->debug("Resolving user import for criteria {$criteria->value}");

        if (!$this->db->tableColumnExists('usr_data', $criteria->value)) {
            $this->log->error("User criteria field {$criteria->value} does not exist in usr_data table, using anonymous user ID for all users");

            return array_fill_keys(array_keys($users), ANONYMOUS_USER_ID);
        }

        $in_clause = $this->db->in(
            $criteria->value,
            array_values($users),
            false,
            $criteria->getColumnType()
        );
        $query = $this->db->query("SELECT usr_id, {$criteria->value} AS identifier FROM usr_data WHERE {$in_clause}");

        $db_mapping = array_column($this->db->fetchAll($query), 'usr_id', 'identifier');

        $mapping = [];
        foreach ($users as $original_id => $identifier) {
            if (!isset($db_mapping[$identifier])) {
                $this->log->warning("User identifier {$identifier} not found for user {$original_id}, using anonymous user ID");
                $mapping[$original_id] = ANONYMOUS_USER_ID;
            }

            $this->log->debug("User identifier {$identifier} found, mapping user {$original_id} to {$db_mapping[$identifier]}");
            $mapping[$original_id] = $db_mapping[$identifier];
        }

        return $mapping;
    }

    /**
     * @param array<int, int> $user_mapping
     */
    public function store(array $user_mapping, ilImportMapping $import_mapping): void
    {
        foreach ($user_mapping as $original_id => $user_id) {
            $import_mapping->addMapping('tst', 'user', (string) $original_id, (string) $user_id);
        }
    }
}
