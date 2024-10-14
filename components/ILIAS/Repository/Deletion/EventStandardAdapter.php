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

namespace ILIAS\Repository\Deletion;

use ILIAS\Repository\InternalDomainService;

class EventStandardAdapter implements PermissionInterface
{
    protected \ilLogger $log;
    protected \ilAppEventHandler $event_handler;

    public function __construct(
        protected InternalDomainService $domain,
    ) {
        $this->event_handler = $domain->event();
        $this->log = $domain->logger()->root();
    }

    public function beforeMoveToTrash(int $ref_id, array $subnodes): void
    {
        // TODO: needs other handling
        // This class shouldn't have to know anything about ECS
        \ilECSObjectSettings::_handleDelete($subnodes); // why only subnodes?
    }

    public function afterMoveToTrash(int $ref_id, int $old_parent_ref_id): void
    {
        $this->log->write("ilObjectGUI::confirmedDeleteObject(), moved ref_id " . $ref_id .
            " to trash");

        $this->event_handler->raise(
            "components/ILIAS/Object",
            "toTrash",
            [
                "obj_id" => \ilObject::_lookupObjId($ref_id),
                "ref_id" => $ref_id,
                "old_parent_ref_id" => $old_parent_ref_id
            ]
        );
    }
}
