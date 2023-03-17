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

use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;

class DataRowFactory implements T\DataRowFactory
{
    /**
     * @param <string, Column> $columns
     * @param <string, Action> $single_actions
     */
    public function __construct(
        protected bool $table_has_singleactions,
        protected bool $table_has_multiactions,
        /**
         * @var <string, Column>
         */
        protected array $columns,
        /**
         * @var <string, Action>
         */
        protected array $row_actions
    ) {
    }

    public function standard(string $id, array $record): DataRow
    {
        return new StandardRow(
            $this->table_has_singleactions,
            $this->table_has_multiactions,
            $this->columns,
            $this->row_actions,
            $id,
            $record
        );
    }
}
