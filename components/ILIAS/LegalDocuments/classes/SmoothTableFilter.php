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

namespace ILIAS\LegalDocuments;

use ilTableFilterItem;
use ilTable2GUI;

class SmoothTableFilter implements TableFilter
{
    /** @var list<Closure(): void> */
    private array $later = [];

    public function __construct(private readonly ilTable2GUI $target, private readonly string $reset_command)
    {
    }

    public function addFilterItem(
        ilTableFilterItem $a_input_item,
        bool $a_optional = false
    ): void {
        $args = func_get_args();
        $this->later[] = fn() => $this->target->addFilterItem(...$args);
    }

    public function flush(): void
    {
        array_map(fn($f) => $f(), $this->later);
        $this->later = [];
        $this->target->setupFilter($this->reset_command);
    }
}
