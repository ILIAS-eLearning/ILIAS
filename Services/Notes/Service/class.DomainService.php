<?php declare(strict_types = 1);

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

namespace ILIAS\Notes;

/**
 * Domain facade
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected NotesManager $notes_manager;
    protected InternalDomainService $internal_domain;

    public function __construct(
        InternalDomainService $internal_domain
    ) {
        $this->internal_domain = $internal_domain;
        $this->notes_manager = $internal_domain->notes();
    }

    public function getNrOfNotesForContext(
        Context $context,
        bool $incl_sub = false
    ) : int {
        return $this->notes_manager->getNrOfNotesForContext(
            $context,
            Note::PRIVATE,
            $incl_sub
        );
    }

    public function getNrOfCommentsForContext(
        Context $context,
        bool $incl_sub = false
    ) : int {
        return $this->notes_manager->getNrOfNotesForContext(
            $context,
            Note::PUBLIC,
            $incl_sub
        );
    }

    /**
     * Get note by id
     */
    public function getById(
        int $id
    ) : Note {
        return $this->notes_manager->getById($id);
    }

    /**
     * Gets all comments of a repo object, incl.
     * comments of subobjects.
     * @return Note[]
     */
    public function getAllCommentsForObjId(
        int $obj_id,
        string $since = ""
    ) : array {
        return $this->notes_manager->getNotesForRepositoryObjId(
            $obj_id,
            Note::PUBLIC,
            false,
            0,
            false,
            $since
        );
    }

    /**
     * Are comments activated for object?
     */
    public function commentsActive(
        int $obj_id
    ) : bool {
        return $this->notes_manager->commentsActive($obj_id);
    }

    public function activateComments(
        int $obj_id,
        bool $a_activate = true
    ) : void {
        $this->notes_manager->activateComments($obj_id, $a_activate);
    }

    /**
     * How many users have attached a note/comment to a given object?
     */
    public function getUserCount(
        int $obj_id,
        int $sub_obj_id,
        string $obj_type
    ) : int {
        return $this->notes_manager->getUserCount($obj_id, $sub_obj_id, $obj_type);
    }
}
