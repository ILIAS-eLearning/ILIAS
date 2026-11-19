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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;

class DataRowBuilder extends RowBuilder implements C\Table\DataRowBuilder
{
    /**
     * @param array<string, mixed> $record
     */
    public function buildDataRow(string $id, array $record): DataRow
    {
        return new DataRow(
            $this->row_actions !== [],
            $this->table_has_multiactions,
            $this->columns,
            $this->row_actions,
            $id,
            $record
        );
    }
}
