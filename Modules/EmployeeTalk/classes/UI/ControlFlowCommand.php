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
    const DEFAULT = "view";
    const INDEX = "view";

    const CREATE = "create";
    const SAVE = "save";

    const UPDATE_INDEX = "update";
    const UPDATE = "edit";

    const DELETE_INDEX = "delete";
    const DELETE = "confirmedDelete";

    const APPLY_FILTER = 'applyFilter';
    const RESET_FILTER = 'resetFilter';

    const TABLE_ACTIONS = 'getActions';
}