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
 * A single database column holding personal data.
 *
 * Do not instantiate this with string literals in consuming code — add a
 * named getter to the {@see Known\KnownSources} catalogue instead (enforced
 * by the PreferKnownSourcesRule PHPStan rule).
 */
final readonly class DbTableColumn implements DbTarget
{
    public function __construct(
        private string $table,
        private string $column,
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function describe(): string
    {
        return "{$this->table}.{$this->column}";
    }
}
