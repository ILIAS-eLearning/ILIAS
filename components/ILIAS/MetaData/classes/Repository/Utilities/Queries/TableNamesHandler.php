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

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\RowInterface;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\ResultFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentRowInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\Action;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\ActionAssignmentInterface;

trait TableNamesHandler
{
    protected function checkTable(string $table): void
    {
        if (
            is_null($this->table($table)) ||
            is_null($this->IDName($table))
        ) {
            throw new \ilMDRepositoryException('Invalid MD table: ' . $table);
        }
    }

    protected function table(string $table): ?string
    {
        return LOMDictionaryInitiator::TABLES[$table] ?? null;
    }

    protected function IDName(string $table): ?string
    {
        return LOMDictionaryInitiator::ID_NAME[$table] ?? null;
    }
}
