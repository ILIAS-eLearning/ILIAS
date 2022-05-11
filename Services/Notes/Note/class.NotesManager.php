<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class NotesManager
{
    protected NotificationsManager $notification;
    protected AccessManager $note_access;
    protected NoteSettingsDBRepository $db_settings_repo;
    protected NoteDBRepository $db_repo;
    protected InternalDomainService $domain;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected NotesSessionRepository $sess_repo;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
        $this->sess_repo = $repo->notesSession();
        $this->db_repo = $repo->note();
        $this->db_settings_repo = $repo->settings();
        $this->note_access = $domain->noteAccess();
        $this->notification = $domain->notification();
    }

    public function setSortAscending(bool $asc) : void
    {
        $this->sess_repo->setSortAscending($asc);
    }

    public function getSortAscending() : bool
    {
        return $this->sess_repo->getSortAscending();
    }

    public function createNote(
        Note $note,
        array $observer,
        bool $use_provided_creation_date = false
    ) : void {
        if (!$use_provided_creation_date) {
            $note = $note->withCreationDate(\ilUtil::now());
        }
        $note = $this->db_repo->createNote($note);
        $this->notification->sendNotifications($note, false);
        $this->notification->notifyObserver($observer, "new", $note);
    }

    public function deleteNote(Note $note, int $user_id, $public_deletion_enabled = false) : void
    {
        if ($this->note_access->canDelete($note, $user_id, $public_deletion_enabled)) {
            $this->db_repo->deleteNote($note->getId());
        }
    }

    public function updateNoteText(
        int $id,
        string $text,
        array $observer
    ) : void {
        $note = $this->db_repo->getById($id);
        if ($this->note_access->canEdit($note)) {
            $this->db_repo->updateNoteText($id, $text);
            $note = $this->db_repo->getById($id);
            $this->notification->sendNotifications($note, true);
            $this->notification->notifyObserver($observer, "update", $note);
        }
    }

    /**
     * Get all notes related to a specific context
     * @return Note[]
     */
    public function getNotesForContext(
        Context $context,
        int $type = Note::PRIVATE,
        bool $incl_sub = false,
        int $author = 0,
        bool $ascending = false,
        string $since = "",
        string $search_text = ""
    ) : array {
        return $this->db_repo->getNotesForContext(
            $context,
            $type,
            $incl_sub,
            $author,
            $ascending,
            $since,
            $search_text
        );
    }

    /**
     * Get all notes related to a specific repository object
     * @return Note[]
     */
    public function getNotesForRepositoryObjId(
        int $obj_id,
        int $type = Note::PRIVATE,
        bool $incl_sub = false,
        int $author = 0,
        bool $ascending = false,
        string $since = ""
    ) : array {
        $context = $this->data->context(
            $obj_id,
            0,
            ""
        );
        return $this->db_repo->getNotesForContext(
            $context,
            $type,
            $incl_sub,
            $author,
            $ascending,
            $since
        );
    }

    /**
     * Get all notes related to a specific repository object
     * @param array  $obj_ids
     * @return Note[]
     */
    public function getNotesForRepositoryObjIds(
        array $obj_ids,
        int $type = Note::PRIVATE,
        bool $incl_sub = false,
        int $author = 0,
        bool $ascending = false,
        string $since = "",
        string $search_text = ""
    ) : array {
        return $this->db_repo->getNotesForObjIds(
            $obj_ids,
            $type,
            $incl_sub,
            $author,
            $ascending,
            $since,
            $search_text
        );
    }


    public function getNrOfNotesForContext(
        Context $context,
        int $type = Note::PRIVATE,
        bool $incl_sub = false
    ) : int {
        return $this->db_repo->getNrOfNotesForContext(
            $context,
            $type,
            $incl_sub
        );
    }

    /**
     * Get all untrashed objects that have either notes/comments of the user
     * attached, or are favourites of the user and have at least one comment (of any user)
     * @param int $type Note::PRIVATE | Note::PUBLIC
     * @return int[]
     */
    public function getRelatedObjectsOfUser(int $type) : array
    {
        $tree = $this->domain->repositoryTree();
        $user_id = $this->domain->user()->getId();
        $fav_rep = new \ilFavouritesDBRepository();

        $ids = $this->db_repo->getRelatedObjIdsOfUser($user_id, $type);
        $ids = array_filter($ids, function ($id) {
            return \ilObject::_exists($id);
        });

        if ($type === Note::PUBLIC) {

            // additionally all objects on the personal desktop of the user
            // that have at least on comment
            $fav_obj_ids = array_map(function ($i) {
                return $i["obj_id"];
            }, $fav_rep->getFavouritesOfUser($user_id));
            if (count($fav_obj_ids) > 0) {
                $fav_obj_ids = $this->db_repo->filterObjectsWithNotes($fav_obj_ids, Note::PUBLIC);
                $ids = array_unique(array_merge($ids, $fav_obj_ids));
            }

            $ids = array_filter($ids, function ($id) {
                return $this->commentsActive($id);
            });
        }

        $wsp_tree = new \ilWorkspaceTree($user_id);

        $ids = array_filter($ids, function ($id) use ($wsp_tree) {
            if (\ilObject::_hasUntrashedReference($id)) {
                return true;
            }
            if ($wsp_tree->lookupNodeId($id) > 0) {
                return true;
            }
            return false;
        });

        return $ids;
    }

    /**
     * @throws NoteNotFoundException
     */
    public function getById(int $id) : Note
    {
        return $this->db_repo->getById($id);
    }

    /**
     * Are comments activated for object?
     */
    public function commentsActive(
        int $obj_id
    ) : bool {
        return $this->db_settings_repo->commentsActive($obj_id);
    }

    public function commentsActiveMultiple(
        array $obj_ids
    ) : array {
        return $this->db_settings_repo->commentsActiveMultiple($obj_ids);
    }

    /**
     * Activate notes feature
     */
    public function activateComments(
        int $obj_id,
        bool $a_activate = true
    ) : void {
        $this->db_settings_repo->activateComments(
            $obj_id,
            0,
            \ilObject::_lookupType($obj_id),
            $a_activate
        );
    }

    /**
     * How many users have attached a note/comment to a given object?
     */
    public function getUserCount(
        int $obj_id,
        int $sub_obj_id,
        string $obj_type
    ) : int {
        return $this->db_repo->getUserCount($obj_id, $sub_obj_id, $obj_type);
    }

    /**
     * Get all notes related to multiple repository objects (current user)
     * @todo see comment in db repo class
     */
    public function countNotesAndCommentsMultipleObjects(
        array $obj_ids,
        bool $no_sub_objs = false
    ) : array {
        return $this->db_repo->countNotesAndCommentsMultipleObjects(
            $obj_ids,
            $this->domain->user()->getId(),
            $no_sub_objs
        );
    }
}
