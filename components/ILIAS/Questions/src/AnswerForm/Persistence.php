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

namespace ILIAS\Questions\AnswerForm;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Questions\Persistence\TableTypes;

interface Persistence
{
    public function getTableNameSpace(): TableNameSpace;

    public function getColumns(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        ?string $table_identifier = null,
        array $columns_to_skip = []
    ): array;

    public function getIdColumn(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        ?string $table_identifier = null
    ): Column;

    public function getForeignKeyColumn(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        ?string $table_identifier = null
    ): Column;

    public function completeQuery(
        Query $query,
        Column $base_table_id_column,
    ): Query;
}
