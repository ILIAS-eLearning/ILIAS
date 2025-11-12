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

namespace ILIAS\User\Setup;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ilAccessCustomRBACOperationAddedObjective;
use ilAccessRBACOperationClonedObjective;
use ilDatabaseInitializedObjective;

class AddReadAllAccountsPermissionObjective implements Objective
{
    private const string TYPE = 'usrf';
    private const int VISIBLE = 2;
    private const int READ = 3;

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return ('Add the "Read All Accounts" permission to the user folder');
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        (new ilAccessCustomRBACOperationAddedObjective(
            'read_all_accounts',
            'read all accounts',
            'object',
            2100,
            [self::TYPE]
        ))->achieve($environment);

        $new_id = $this->getAddedOperationId($environment);
        if ($new_id !== null) {

            (new ilAccessRBACOperationClonedObjective(
                self::TYPE,
                self::READ,
                $new_id
            ))->achieve($environment);

            (new ilAccessRBACOperationClonedObjective(
                self::TYPE,
                self::VISIBLE,
                self::READ
            ))->achieve($environment);

            (new \ilAccessRBACOperationDeletedObjective(
                self::TYPE,
                self::VISIBLE
            ))->achieve($environment);
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return $this->getAddedOperationId($environment) === null;
    }

    private function getAddedOperationId(Environment $environment): ?int
    {
        /** @var \ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $query = 'SELECT ops_id FROM rbac_operations WHERE operation ="read_all_accounts"';
        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            return (int) $row['ops_id'];
        }
        return null;
    }
}
