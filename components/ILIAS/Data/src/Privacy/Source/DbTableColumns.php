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

namespace ILIAS\Data\Privacy\Source;

/**
 * Several columns of one database table forming a single compound value,
 * e.g. usr_data.(street,city,zipcode,country) for a postal address.
 */
final readonly class DbTableColumns implements DbTarget
{
    /**
     * @var non-empty-list<string>
     */
    private array $columns;

    public function __construct(
        private string $table,
        string ...$columns,
    ) {
        if ($columns === []) {
            throw new \InvalidArgumentException('At least one column is required.');
        }
        $this->columns = array_values($columns);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function describe(): string
    {
        return $this->table . '.(' . implode(',', $this->columns) . ')';
    }
}
