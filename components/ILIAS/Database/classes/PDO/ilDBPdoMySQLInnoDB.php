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
/**
 * Class ilDBPdoMySQLInnoDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLInnoDB extends ilDBPdoMySQL
{
    protected string $storage_engine = 'InnoDB';

    #[\Override]
    public function supportsFulltext(): bool
    {
        return false;
    }


    #[\Override]
    public function supportsTransactions(): bool
    {
        return false;
    }


    #[\Override]
    public function addFulltextIndex(string $table_name, array $fields, string $name = 'in'): bool
    {
        return false; // NOT SUPPORTED
    }
}
