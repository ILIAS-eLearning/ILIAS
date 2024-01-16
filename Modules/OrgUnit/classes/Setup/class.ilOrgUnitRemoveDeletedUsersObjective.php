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

use ILIAS\Setup\Environment;
use ILIAS\Setup\NullConfig;

class ilOrgUnitRemoveDeletedUsersObjective extends ilSetupObjective
{
    public function __construct()
    {
        parent::__construct(new NullConfig());
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'OrgUnit assignments are removed for deleted users';
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
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $query = 'DELETE FROM il_orgu_ua' . PHP_EOL
            . 'WHERE user_id NOT IN (' . PHP_EOL
            . 'SELECT usr_id FROM usr_data' . PHP_EOL
            . ')';
        $db->manipulate($query);
        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
