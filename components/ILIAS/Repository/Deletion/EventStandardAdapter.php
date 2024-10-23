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

class EventStandardAdapter implements EventInterface
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

    public function beforeSubtreeRemoval(int $obj_id): void
    {
        // old wrandelshofer code, still needed?
        \ilChangeEvent::_recordWriteEvent(
            $obj_id,
            $this->domain->user()->getId(),
            'purge',
            null
        );
    }

    public function beforeObjectRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        string $title
    ): void {
        $this->log->info(
            'delete obj_id: ' . $obj_id .
            ', ref_id: ' . $ref_id .
            ', type: ' . $type .
            ', title: ' . $title
        );
    }

    public function failedRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        string $title,
        string $message
    ): void {
        $this->log->error(
            'failed to remove obj_id: ' . $obj_id .
            ', ref_id: ' . $ref_id .
            ', type: ' . $type .
            ', title: ' . $title .
            ', message: ' . $message
        );
    }

    public function afterObjectRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        int $old_parent_ref_id
    ): void {
        $this->event_handler->raise(
            "components/ILIAS/Object",
            "delete",
            [
                "obj_id" => $obj_id,
                "ref_id" => $ref_id,
                "type" => $type,
                "old_parent_ref_id" => $old_parent_ref_id
            ]
        );
    }

    public function afterTreeDeletion(
        int $tree_id,
        int $child
    ): void {
        $this->log->info(
            'deleted tree, tree_id: ' . $tree_id .
            ', child: ' . $child
        );
    }
}
