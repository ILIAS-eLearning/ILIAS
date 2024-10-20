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

namespace ILIAS\MetaData\Vocabularies\Controlled\Database;

class NullWrapper implements WrapperInterface
{
    public function nextID(string $table): int
    {
        return 0;
    }

    public function insert(string $table, array $values): void
    {
    }

    public function update(string $table, array $values, array $where): void
    {
    }

    public function query(string $query): \Generator
    {
        yield from [];
    }

    public function manipulate(string $query): void
    {
    }

    public function quoteAsInteger(string $value): string
    {
        return '';
    }

    public function quoteAsString(string $value): string
    {
        return '';
    }

    public function in(string $field, string ...$values): string
    {
        return '';
    }
}
