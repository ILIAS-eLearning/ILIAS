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

namespace ILIAS\Repository\Permission;

interface CmdPermissionInterface
{
    // or input is
    // request, user, ctrl (via gui service?)

    // class is used in handleCOmmand of table or getCmd in execute command

    // hasPerm
    // derives cmdClass/cmd from ctrl
    // derives id from request, tablecmd?
    // does not work, when no gui service is passed
    public function isRequestCommandPermitted(): bool;

    // replaces getCmd from ctrl
    // does not return a cmd, if permission not given
    public function getPermittedCommand(): string;

    public function getRequestCommand(): string;

    public function getRequestEntity(): ?CmdEntity;

    public function getRequestNodeId(): int;

    // works without gui being passed
    public function isCommandPermitted(
        string $cmd,                // derived or from table
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): bool;

    public function checkCommand(
        string $cmd,
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): void;

    public function isForwardPermitted(
        string $from_class,
        string $to_class
    ): bool;

    public function forwardPermitted(
        object $from_gui,
        object $to_gui
    ): void;


}
