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

namespace ILIAS\Data\Privacy\PHPStan\Rules\Fixtures;

use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\DbTableColumns;
use ILIAS\Data\Privacy\Source\UserInput;

/**
 * @param class-string<DbTableColumn> $dynamic_class
 */
function undocumentedSources(string $table, string $column, string $dynamic_class): array
{
    return [
        new DbTableColumn('usr_data', 'street'),
        new DbTableColumns('usr_data', 'street', 'city'),
        /* @privacy-undocumented this one is deliberately not catalogued */
        new DbTableColumn('tmp_table', 'tmp_column'),
        new DbTableColumn('tmp_table', 'tmp_column'), // @privacy-undocumented
        new UserInput('some_form'),
        new DbTableColumn($table, $column),
        new $dynamic_class('usr_data', 'street'),
    ];
}
