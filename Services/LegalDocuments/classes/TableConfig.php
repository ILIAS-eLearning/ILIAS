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

use Closure;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

interface TableConfig
{
    public function setTitle(
        string $a_title,
        string $a_icon = "",
        string $a_icon_alt = ""
    ): void;

    public function setExternalSorting(bool $a_val): void;
    public function setDefaultOrderField(string $a_defaultorderfield): void;
    public function setDefaultOrderDirection(string $a_defaultorderdirection): void;
    public function setSelectableColumns(...$names): void;

    public function addMultiCommand(string $a_cmd, string $a_text): void;
    public function addCommandButton(
        string $a_cmd,
        string $a_text,
        string $a_onclick = '',
        string $a_id = "",
        string $a_class = ""
    ): void;

    public function asFilter(string $reset_command): TableFilter;
}
