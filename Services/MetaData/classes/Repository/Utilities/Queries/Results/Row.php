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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Results;

class Row implements RowInterface
{
    protected int $id;
    protected string $table;

    /**
     * @var FieldInterface[]
     */
    protected array $data;

    public function __construct(
        int $id,
        string $table,
        FieldInterface ...$data
    ) {
        $this->id = $id;
        $this->table = $table;
        $this->data = $data;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function value(string $field): string
    {
        foreach ($this->data as $datum) {
            if ($datum->name() === $field) {
                return $datum->value();
            }
        }
        return '';
    }
}
