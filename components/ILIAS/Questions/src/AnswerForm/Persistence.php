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
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Questions\Persistence\TableTypes;

interface Persistence
{
    public function getTableNameSpace(): TableNameSpace;

    public function getColumns(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = '',
        array $columns_to_skip = []
    ): array;

    public function getIdColumn(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = ''
    ): Column;

    public function getForeignKeyColumn(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = ''
    ): Column;

    public function completeQuery(
        Query $query,
        Column $base_table_id_column,
    ): Query;
}
