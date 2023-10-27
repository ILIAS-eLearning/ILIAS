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

use ilTable2GUI;

/**
 * This class takes care of the order in which the methods must be called.
 */
class SmoothTableConfig implements TableConfig
{
    /** @var list<Closure(): void> */
    private array $later = [];

    public function __construct(private readonly ilTable2GUI $target)
    {
    }

    public function setTitle(
        string $a_title,
        string $a_icon = "",
        string $a_icon_alt = ""
    ): void {
        $this->target->setTitle(...func_get_args());
    }

    public function setExternalSorting(bool $a_val): void
    {
        $this->target->setExternalSorting($a_val);
    }

    public function setDefaultOrderField(string $a_defaultorderfield): void
    {
        $this->target->setDefaultOrderField($a_defaultorderfield);
    }

    public function setDefaultOrderDirection(string $a_defaultorderdirection): void
    {
        $this->target->setDefaultOrderDirection($a_defaultorderdirection);
    }

    public function setSelectableColumns(...$names): void
    {
        $this->target->setSelectableColumns(...$names);
    }

    public function addMultiCommand(string $a_cmd, string $a_text): void
    {
        $this->later[] = fn() => $this->target->addMultiCommand($a_cmd, $a_text);
    }

    public function addCommandButton(
        string $a_cmd,
        string $a_text,
        string $a_onclick = '',
        string $a_id = "",
        string $a_class = ""
    ): void {
        $args = func_get_args();
        $this->later[] = fn() => $this->target->addCommandButton(...$args);
    }

    public function asFilter(string $reset_command): TableFilter
    {
        $filter = new SmoothTableFilter($this->target, $reset_command);
        $this->later[] = $filter->flush(...);
        return $filter;
    }

    public function flush(): void
    {
        array_map(fn($proc) => $proc(), $this->later);
        $this->later = [];
    }
}
