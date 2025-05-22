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

namespace ILIAS\EmployeeTalk\UI;

interface ControlFlowCommand
{
    public const string DEFAULT = "view";
    public const string INDEX = "view";

    public const string CREATE = "create";
    public const string SAVE = "save";

    public const string UPDATE_INDEX = "update";
    public const string UPDATE = "edit";

    public const string DELETE_INDEX = "delete";
    public const string DELETE = "confirmedDelete";

    public const string APPLY_FILTER = 'applyFilter';
    public const string RESET_FILTER = 'resetFilter';

    public const string TABLE_ACTIONS = 'getActions';
}
