<?php declare(strict_types=1);

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
 * @author Alexander Killing <killing@leifos.de>
 */
class NoteDBRepository
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    public function createNote(
        Note $note
    ) : Note {
        $db = $this->db;

        $id = $db->nextId("note");
        $context = $note->getContext();
        $db->insert("note", array(
            "id" => array("integer", $id),
            "rep_obj_id" => array("integer", $context->getObjId()),
            "obj_id" => array("integer", $context->getSubObjId()),
            "obj_type" => array("text", $context->getType()),
            "news_id" => array("integer", $context->getNewsId()),
            "type" => array("integer", $note->getType()),
            "author" => array("integer", $note->getAuthor()),
            "note_text" => array("clob", $note->getText()),
            "creation_date" => array("timestamp", $note->getCreationDate()),
            "no_repository" => array("integer", (int) !$context->getInRepository())
        ));
        return $this->getById($id);
    }

    public function deleteNote(int $id) : void
    {
        $db = $this->db;
        $q = "DELETE FROM note WHERE id = " .
            $db->quote($id, "integer");
        $db->manipulate($q);
    }

    public function updateNoteText(
        int $id,
        string $text
    ) : void {
        $db = $this->db;

        $update_date = \ilUtil::now();
        $db->update("note", array(
            "note_text" => array("clob", $text),
            "update_date" => array("timestamp", $update_date),
        ), array(
            "id" => array("integer", $id)
        ));
    }

    /**
     * Get note by id
     * @throws NoteNotFoundException
     */
    public function getById(
        int $id
    ) : Note {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM note " .
            " WHERE id = %s ",
            ["integer"],
            [$id]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return $this->getNoteFromRecord($rec);
        }
        throw new NoteNotFoundException("Note with ID $id not found.");
    }

    protected function getNoteFromRecord(array $rec) : Note
    {
        return $this->data->note(
            (int) $rec["id"],
            $this->data->context(
                (int) $rec["rep_obj_id"],
                (int) $rec["obj_id"],
                $rec["obj_type"],
                (int) $rec["news_id"],
                !$rec["no_repository"]
            ),
            $rec["note_text"],
            (int) $rec["author"],
            (int) $rec["type"],
            $rec["creation_date"],
            $rec["update_date"]
        );
    }

    /**
     * Get query
     * @return string
     */
    protected function getQuery(
        ?Context $context,
        int $type = Note::PRIVATE,
        bool $incl_sub = false,
        int $author = 0,
        bool $ascending = false,
        bool $count = false,
        string $since = "",
        array $obj_ids = [],
        string $search_text = ""
    ) : string {
        $db = $this->db;

        $author_where = ($author > 0)
            ? " AND author = " . $db->quote($author, "integer")
            : "";

        $sub_where = ($context)
            ? " rep_obj_id = " . $db->quote($context->getObjId(), "integer")
            : " " . $db->in("rep_obj_id", $obj_ids, false, "integer");

        $sub_where .= ($context && !$incl_sub)
            ? " AND note.obj_id = " . $db->quote($context->getSubObjId(), "integer") .
            " AND note.obj_type = " . $db->quote($context->getType(), "text")
            : "";

        if ($since !== "") {
            $sub_where .= " AND creation_date > " . $db->quote($since, "timestamp");
        }

        if ($context) {
            $news_where =
                " AND news_id = " . $db->quote($context->getNewsId(), "integer");

            $sub_where .= " AND no_repository = " . $db->quote(!$context->getInRepository(), "integer");
        }

        // search text
        $join = "";
        if ($search_text !== "") {
            $sub_where .= " AND (" . $db->like("note_text", "text", "%" . $search_text . "%");
            $join = " JOIN usr_data ud ON (author = ud.usr_id)";
            $join .= " LEFT JOIN object_data od ON (rep_obj_id = od.obj_id)";
            $sub_where .= " OR " . $db->like("ud.lastname", "text", "%" . $search_text . "%");
            $sub_where .= " OR " . $db->like("ud.firstname", "text", "%" . $search_text . "%");
            $sub_where .= " OR " . $db->like("ud.login", "text", "%" . $search_text . "%");
            $sub_where .= " OR " . $db->like("od.title", "text", "%" . $search_text . "%");
            $sub_where .= ")";
        }

        $fields = $count ? "count(*) cnt" : "note.*";
        $query = "SELECT $fields FROM note $join WHERE " .
            $sub_where .
            " AND note.type = " . $db->quote($type, "integer") .
            $author_where .
            $news_where .
            " ORDER BY creation_date ";
        $query .= ($ascending) ? "ASC" : "DESC";
        return $query;
    }

    /**
     * Get all notes related to a specific object
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
        $db = $this->db;

        $query = $this->getQuery(
            $context,
            $type,
            $incl_sub,
            $author,
            $ascending,
            false,
            $since,
            [],
            $search_text
        );

        $set = $db->query($query);
        $notes = [];
        while ($note_rec = $db->fetchAssoc($set)) {
            $notes[] = $this->getNoteFromRecord($note_rec);
        }
        return $notes;
    }

    /**
     * Get all notes related to a specific object
     * @return Note[]
     */
    public function getNotesForObjIds(
        array $obj_ids,
        int $type = Note::PRIVATE,
        bool $incl_sub = false,
        int $author = 0,
        bool $ascending = false,
        string $since = "",
        string $search_text = ""
    ) : array {
        $db = $this->db;

        $query = $this->getQuery(
            null,
            $type,
            $incl_sub,
            $author,
            $ascending,
            false,
            $since,
            $obj_ids,
            $search_text
        );

        $set = $db->query($query);
        $notes = [];
        while ($note_rec = $db->fetchAssoc($set)) {
            $notes[] = $this->getNoteFromRecord($note_rec);
        }
        return $notes;
    }

    public function getNrOfNotesForContext(
        Context $context,
        int $type = Note::PRIVATE,
        bool $incl_sub = false
    ) : int {
        $db = $this->db;

        $query = $this->getQuery(
            $context,
            $type,
            $incl_sub,
            0,
            false,
            true
        );

        $set = $db->query($query);
        $rec = $db->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    /**
     * @return int[]
     */
    public function getRelatedObjIdsOfUser(
        int $user_id,
        int $type
    ) : array {
        $db = $this->db;

        $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
            " type = " . $db->quote($type, "integer") .
            " AND author = " . $db->quote($user_id, "integer") .
            " AND (no_repository IS NULL OR no_repository < " . $db->quote(1, "integer") . ")";

        $set = $db->query($q);
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = (int) $rec["rep_obj_id"];
        }
        return $ids;
    }

    /**
     * @param int[] $obj_ids
     * @return int[]
     */
    public function filterObjectsWithNotes(array $obj_ids, int $type) : array
    {
        $db = $this->db;

        $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
            $db->in("rep_obj_id", $obj_ids, false, "integer") .
            " AND type = " . $db->quote($type, "integer") .
            " AND (no_repository IS NULL OR no_repository < " . $db->quote(1, "integer") . ")";

        $set = $db->query($q);
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = (int) $rec["rep_obj_id"];
        }
        return $ids;
    }

    /**
     * How many users have attached a note/comment to a given object?
     */
    public function getUserCount(
        int $obj_id,
        int $sub_obj_id,
        string $obj_type
    ) : int {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT count(DISTINCT author) cnt FROM note WHERE " .
            "rep_obj_id = %s AND obj_id = %s AND obj_type = %s",
            array("integer", "integer", "text"),
            array($obj_id, $sub_obj_id, $obj_type)
        );
        $rec = $db->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    /**
     * Get all notes related to multiple repository objects
     * @todo this is currently used to implement a caching in ilObjListGUI objects.
     *       the caching should be moved into this repo instead
     */
    public function countNotesAndCommentsMultipleObjects(
        array $obj_ids,
        int $user_id,
        bool $no_sub_objs = false
    ) : array {
        $db = $this->db;

        $q = "SELECT count(id) c, rep_obj_id, type FROM note WHERE " .
            " ((type = " . $db->quote(Note::PRIVATE, "integer") . " AND " .
            "author = " . $db->quote($user_id, "integer") . ") OR " .
            " type = " . $db->quote(Note::PUBLIC, "integer") . ") AND " .
            $db->in("rep_obj_id", $obj_ids, false, "integer");

        if ($no_sub_objs) {
            $q .= " AND obj_id = " . $db->quote(0, "integer");
        }

        $q .= " GROUP BY rep_obj_id, type ";

        $cnt = array();
        $set = $db->query($q);
        while ($rec = $db->fetchAssoc($set)) {
            $cnt[$rec["rep_obj_id"]][$rec["type"]] = $rec["c"];
        }
        return $cnt;
    }
}
