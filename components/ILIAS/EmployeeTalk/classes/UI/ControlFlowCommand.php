<?php

declare(strict_types=1);

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

namespace ILIAS\EmployeeTalk\UI;

interface ControlFlowCommand
{
    public const DEFAULT = "view";
    public const INDEX = "view";

    public const CREATE = "create";
    public const SAVE = "save";

    public const UPDATE_INDEX = "update";
    public const UPDATE = "edit";

    public const DELETE_INDEX = "delete";
    public const DELETE = "confirmedDelete";

    public const APPLY_FILTER = 'applyFilter';
    public const RESET_FILTER = 'resetFilter';

    public const TABLE_ACTIONS = 'getActions';
}
