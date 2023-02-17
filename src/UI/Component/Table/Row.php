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

namespace ILIAS\UI\Component\Table;

interface Row extends \ILIAS\UI\Component\Component
{
    public function getId(): string;

    /**
     * Refer to an Action by its id and disable it for this row/record only.
     */
    public function withDisabledAction(string $action_id, bool $disable = true): self;

    /**
     * @return <string, Column>
     */
    public function getColumns(): array;

    /**
     * @return <string, Action>
     */
    public function getActions(): array;

    public function getCellContent(string $col_id): string;
}
