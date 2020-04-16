<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_NOTE_PRIVATE", 1);
define("IL_NOTE_PUBLIC", 2);

define("IL_NOTE_UNLABELED", 0);
define("IL_NOTE_IMPORTANT", 1);
define("IL_NOTE_QUESTION", 2);
define("IL_NOTE_PRO", 3);
define("IL_NOTE_CONTRA", 4);

/** @defgroup ServicesNotes Services/Notes
 */

/**
 * Note class. Represents a single note.
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesNotes
 */
class ilNote
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * object id (NOT ref_id!) of repository object (e.g for page objects
     * the obj_id of the learning module; for personal desktop this is set to 0)
     *
     * @var int
     */
    protected $rep_obj_id = 0;

    /**
     * object id (e.g for page objects the obj_id of the page object)
     * this is set to 0 for normal repository objects like forums ...
     *
     * @var int
     */
    protected $obj_id;

    /**
     * type of the object (e.g st,pg,crs ... NOT "news")
     *
     * @var string
     */
    protected $obj_type;

    /**
     * @var int
     */
    protected $news_id;


    /**
    * constructor
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        if ($a_id > 0) {
            $this->id = $a_id;
            $this->read();
        }
    }
    
    /**
    * set id
    *
    * @param	int		note id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * get id
    *
    * @return	int		note id
    */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * set assigned object
     *
     * @param string $a_obj_type
     * @param int $a_rep_obj_id
     * @param int $a_obj_id
     * @param int $a_news_id
     */
    public function setObject($a_obj_type, $a_rep_obj_id, $a_obj_id = 0, $a_news_id = 0)
    {
        $this->rep_obj_id = $a_rep_obj_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->news_id = $a_news_id;
    }
    
    public function getObject()
    {
        return array("rep_obj_id" => $this->rep_obj_id,
            "obj_id" => $this->obj_id,
            "obj_type" => $this->obj_type,
            "news_id" => $this->news_id);
    }
    
    
    /**
    * set type
    *
    * @param	int		IL_NOTE_PUBLIC | IL_NOTE_PRIVATE
    */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * get type
    *
    * @return	int		IL_NOTE_PUBLIC | IL_NOTE_PRIVATE
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * set author
    *
    * @param	int		author user id
    */
    public function setAuthor($a_user_id)
    {
        $this->author = $a_user_id;
    }

    /**
    * get author
    *
    * @return	int		user id
    */
    public function getAuthor()
    {
        return $this->author;
    }
    
    /**
    * set text
    *
    * @param	string		text
    */
    public function setText($a_text)
    {
        $this->text = $a_text;
    }

    /**
    * get text
    *
    * @return	string	text
    */
    public function getText()
    {
        return $this->text;
    }

    /**
    * set subject
    *
    * @param	string		text
    */
    public function setSubject($a_subject)
    {
        $this->subject = $a_subject;
    }

    /**
    * get subject
    *
    * @return	string	subject
    */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
    * set creation date
    *
    * @param	string	creation date
    */
    public function setCreationDate($a_date)
    {
        $this->creation_date = $a_date;
    }

    /**
    * get creation date
    *
    * @return	string	creation date
    */
    public function getCreationDate()
    {
        return $this->creation_date;
    }
    
    /**
    * set update date
    *
    * @param	string	update date
    */
    public function setUpdateDate($a_date)
    {
        $this->update_date = $a_date;
    }

    /**
    * get update date
    *
    * @return	string	update date
    */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
    
    /**
    * set label
    *
    * @param	int		IL_NOTE_UNLABELED | IL_NOTE_IMPORTANT | IL_NOTE_QUESTION
    *					| IL_NOTE_PRO | IL_NOTE_CONTRA
    */
    public function setLabel($a_label)
    {
        return $this->label = $a_label;
    }
    
    /**
    * get label
    *
    * @return	int		IL_NOTE_UNLABELED | IL_NOTE_IMPORTANT | IL_NOTE_QUESTION
    *					| IL_NOTE_PRO | IL_NOTE_CONTRA
    */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set news id
     *
     * @param int $a_val news id
     */
    public function setNewsId($a_val)
    {
        $this->news_id = $a_val;
    }
    
    /**
     * Get news id
     *
     * @return int news id
     */
    public function getNewsId()
    {
        return $this->news_id;
    }
    
    /**
    * set repository object status
    *
    * @param	bool
    */
    public function setInRepository($a_value)
    {
        return $this->no_repository = !(bool) $a_value;
    }
    
    /**
    * belongs note to repository object?
    *
    * @return	bool
    */
    public function isInRepository()
    {
        return !$this->no_repository;
    }
    
    public function create($a_use_provided_creation_date = false)
    {
        $ilDB = $this->db;
        
        $cd = ($a_use_provided_creation_date)
            ? $this->getCreationDate()
            : ilUtil::now();
        
        $this->id = $ilDB->nextId("note");

        $ilDB->insert("note", array(
            "id" => array("integer", $this->id),
            "rep_obj_id" => array("integer", (int) $this->rep_obj_id),
            "obj_id" => array("integer", (int) $this->obj_id),
            "obj_type" => array("text", (string) $this->obj_type),
            "news_id" => array("integer", (int) $this->news_id),
            "type" => array("integer", (int) $this->type),
            "author" => array("integer", (int) $this->author),
            "note_text" => array("clob", (string) $this->text),
            "subject" => array("text", (string) $this->subject),
            "label" => array("integer", (int) $this->label),
            "creation_date" => array("timestamp", $cd),
            "no_repository" => array("integer", $this->no_repository)
            ));

        $this->sendNotifications();
        
        $this->creation_date = ilNote::_lookupCreationDate($this->getId());
    }

    public function update()
    {
        $ilDB = $this->db;
        
        $ilDB->update("note", array(
            "rep_obj_id" => array("integer", (int) $this->rep_obj_id),
            "obj_id" => array("integer", (int) $this->obj_id),
            "news_id" => array("integer", (int) $this->news_id),
            "obj_type" => array("text", (string) $this->obj_type),
            "type" => array("integer", (int) $this->type),
            "author" => array("integer", (int) $this->author),
            "note_text" => array("clob", (string) $this->text),
            "subject" => array("text", (string) $this->subject),
            "label" => array("integer", (int) $this->label),
            "update_date" => array("timestamp", ilUtil::now()),
            "no_repository" => array("integer", $this->no_repository)
            ), array(
            "id" => array("integer", $this->getId())
            ));
        
        $this->update_date = ilNote::_lookupUpdateDate($this->getId());

        $this->sendNotifications(true);
    }

    public function read()
    {
        $ilDB = $this->db;
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote((int) $this->getId(), "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);
        $this->setAllData($note_rec);
    }
    
    /**
    * delete note
    */
    public function delete()
    {
        $ilDB = $this->db;
        
        $q = "DELETE FROM note WHERE id = " .
            $ilDB->quote((int) $this->getId(), "integer");
        $ilDB->manipulate($q);
    }
    
    /**
    * set all note data by record array
    */
    public function setAllData($a_note_rec)
    {
        $this->setId($a_note_rec["id"]);
        $this->setObject(
            $a_note_rec["obj_type"],
            $a_note_rec["rep_obj_id"],
            $a_note_rec["obj_id"],
            $a_note_rec["news_id"]
        );
        $this->setType($a_note_rec["type"]);
        $this->setAuthor($a_note_rec["author"]);
        $this->setText($a_note_rec["note_text"]);
        $this->setSubject($a_note_rec["subject"]);
        $this->setLabel($a_note_rec["label"]);
        $this->setCreationDate($a_note_rec["creation_date"]);
        $this->setUpdateDate($a_note_rec["update_date"]);
        $this->setInRepository(!(bool) $a_note_rec["no_repository"]);
    }
    
    /**
    * lookup creation date of note
    */
    public static function _lookupCreationDate($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote((int) $a_id, "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);

        return $note_rec["creation_date"];
    }

    /**
    * lookup update date of note
    */
    public static function _lookupUpdateDate($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote((int) $a_id, "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);

        return $note_rec["update_date"];
    }
    
    /**
    * get all notes related to a specific object
    */
    public static function _getNotesOfObject(
        $a_rep_obj_id,
        $a_obj_id,
        $a_obj_type,
        $a_type = IL_NOTE_PRIVATE,
        $a_incl_sub = false,
        $a_filter = "",
        $a_all_public = "y",
        $a_repository_mode = true,
        $a_sort_ascending = false,
        $a_news_id = 0
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $author_where = ($a_type == IL_NOTE_PRIVATE || $a_all_public == "n")
            ? " AND author = " . $ilDB->quote((int) $ilUser->getId(), "integer")
            : "";

        $sub_where = (!$a_incl_sub)
            ? " AND obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") .
              " AND obj_type = " . $ilDB->quote((string) $a_obj_type, "text")
            : "";

        $news_where =
            " AND news_id = " . $ilDB->quote((int) $a_news_id, "integer");


        $sub_where .= " AND no_repository = " . $ilDB->quote(!$a_repository_mode, "integer");

        $q = "SELECT * FROM note WHERE " .
            " rep_obj_id = " . $ilDB->quote((int) $a_rep_obj_id, "integer") .
            $sub_where .
            " AND type = " . $ilDB->quote((int) $a_type, "integer") .
            $author_where .
            $news_where .
            " ORDER BY creation_date ";
        
        $q .= ((bool) $a_sort_ascending) ? "ASC" : "DESC";
        
        $set = $ilDB->query($q);
        $notes = array();
        while ($note_rec = $ilDB->fetchAssoc($set)) {
            if ($a_filter != "") {
                if (!is_array($a_filter)) {
                    $a_filter = array($a_filter);
                }
                if (!in_array($note_rec["id"], $a_filter)) {
                    continue;
                }
            }
            $cnt = count($notes);
            $notes[$cnt] = new ilNote();
            $notes[$cnt]->setAllData($note_rec);
        }
        
        return $notes;
    }

    /**
     * get all notes related to a single repository object
     */
    public static function _getAllNotesOfSingleRepObject(
        $a_rep_obj_id,
        $a_type = IL_NOTE_PRIVATE,
        $a_incl_sub = false,
        $a_sort_ascending = false,
        $a_since = ""
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $sub_where = (!$a_incl_sub)
            ? " AND obj_id = " . $ilDB->quote((int) 0, "integer") : "";

        if ($a_since != "") {
            $sub_where .= " AND creation_date > " . $ilDB->quote($a_since, "timestamp");
        }

        $sub_where .= " AND no_repository = " . $ilDB->quote(0, "integer");

        $q = "SELECT * FROM note WHERE " .
            " rep_obj_id = " . $ilDB->quote((int) $a_rep_obj_id, "integer") .
            $sub_where .
            " AND type = " . $ilDB->quote((int) $a_type, "integer") .
            " ORDER BY creation_date ";

        $q .= ((bool) $a_sort_ascending) ? "ASC" : "DESC";
        $set = $ilDB->query($q);
        $notes = array();
        while ($note_rec = $ilDB->fetchAssoc($set)) {
            $cnt = count($notes);
            $notes[$cnt] = new ilNote();
            $notes[$cnt]->setAllData($note_rec);
        }
        return $notes;
    }

    /**
    * get last notes of current user
    */
    public static function _getLastNotesOfUser()
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT * FROM note WHERE " .
            " type = " . $ilDB->quote((int) IL_NOTE_PRIVATE, "integer") .
            " AND author = " . $ilDB->quote((int) $ilUser->getId(), "integer") .
            " AND (no_repository IS NULL OR no_repository < " . $ilDB->quote(1, "integer") . ")" .
            " ORDER BY creation_date DESC";

        $ilDB->quote($q);
        $set = $ilDB->query($q);
        $notes = array();
        while ($note_rec = $ilDB->fetchAssoc($set)) {
            $cnt = count($notes);
            $notes[$cnt] = new ilNote();
            $notes[$cnt]->setAllData($note_rec);
        }
        
        return $notes;
    }
    
    /**
    * get all related objects for user
    */
    public static function _getRelatedObjectsOfUser($a_mode)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $tree = $DIC->repositoryTree();
        
        if ($a_mode == ilPDNotesGUI::PRIVATE_NOTES) {
            $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
                " type = " . $ilDB->quote((int) IL_NOTE_PRIVATE, "integer") .
                " AND author = " . $ilDB->quote($ilUser->getId(), "integer") .
                " AND (no_repository IS NULL OR no_repository < " . $ilDB->quote(1, "integer") . ")" .
                " ORDER BY rep_obj_id";
    
            $ilDB->quote($q);
            $set = $ilDB->query($q);
            $reps = array();
            while ($rep_rec = $ilDB->fetchAssoc($set)) {
                // #9343: deleted objects
                if (ilObject::_lookupType($rep_rec["rep_obj_id"])) {
                    $reps[] = array("rep_obj_id" => $rep_rec["rep_obj_id"]);
                }
            }
        } else {
            // all objects where the user wrote at least one comment
            $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
                " type = " . $ilDB->quote((int) IL_NOTE_PUBLIC, "integer") .
                " AND author = " . $ilDB->quote($ilUser->getId(), "integer") .
                " AND (no_repository IS NULL OR no_repository < " . $ilDB->quote(1, "integer") . ")" .
                " ORDER BY rep_obj_id";

            $set = $ilDB->query($q);
            $reps = array();
            while ($rep_rec = $ilDB->fetchAssoc($set)) {
                // #9343: deleted objects
                if ($type = ilObject::_lookupType($rep_rec["rep_obj_id"])) {
                    if (ilNote::commentsActivated($rep_rec["rep_obj_id"], "", $type)) {
                        $reps[] = array("rep_obj_id" => $rep_rec["rep_obj_id"]);
                    }
                }
            }
            
            // additionally all objects on the personal desktop of the user
            // that have at least on comment
            $dis = ilObjUser::_lookupDesktopItems($ilUser->getId());
            $obj_ids = array();
            foreach ($dis as $di) {
                $obj_ids[] = $di["obj_id"];
            }
            if (count($obj_ids) > 0) {
                $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
                    $ilDB->in("rep_obj_id", $obj_ids, false, "integer") .
                    " AND (no_repository IS NULL OR no_repository < " . $ilDB->quote(1, "integer") . ")";

                $set = $ilDB->query($q);
                while ($rec = $ilDB->fetchAssoc($set)) {
                    $add = true;
                    reset($reps);
                    foreach ($reps as $r) {
                        if ($r["rep_obj_id"] == $rec["rep_obj_id"]) {
                            $add = false;
                        }
                    }
                    if ($add) {
                        $type = ilObject::_lookupType($rec["rep_obj_id"]);
                        if (ilNote::commentsActivated($rec["rep_obj_id"], "", $type)) {
                            $reps[] = array("rep_obj_id" => $rec["rep_obj_id"]);
                        }
                    }
                }
            }
        }
                
        if (sizeof($reps)) {
            // check if notes/comments belong to objects in trash
            // see ilNoteGUI::showTargets()
            foreach ($reps as $idx => $rep) {
                $has_active_ref = false;
                
                // repository?
                $ref_ids = ilObject::_getAllReferences($rep["rep_obj_id"]);
                if ($ref_ids) {
                    $reps[$idx]["ref_ids"] = array_values($ref_ids);
                    
                    foreach ($ref_ids as $ref_id) {
                        if (!$tree->isDeleted($ref_id)) {
                            $has_active_ref = true;
                            break;
                        }
                    }
                } else {
                    // personal workspace?
                    include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
                    include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                    $wsp_tree = new ilWorkspaceTree($ilUser->getId());
                    $node_id = $wsp_tree->lookupNodeId($rep["rep_obj_id"]);
                    if ($node_id) {
                        $reps[$idx]["wsp_id"] = $node_id;
                        
                        $has_active_ref = true;
                    }
                }
                
                if (!$has_active_ref) {
                    unset($reps[$idx]);
                }
            }
        }
        
        return $reps;
    }

    /**
    * How many users have attached a note/comment to a given object?
    *
    * @param	int		$a_rep_obj_id		object id (as in object data)
    * @param	int		$a_obj_id			(sub) object id
    * @param	string	$a_type				(sub) object type
    */
    public static function getUserCount($a_rep_obj_id, $a_obj_id, $a_type)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->queryF(
            "SELECT count(DISTINCT author) cnt FROM note WHERE " .
            "rep_obj_id = %s AND obj_id = %s AND obj_type = %s",
            array("integer", "integer", "text"),
            array((int) $a_rep_obj_id, (int) $a_obj_id, (string) $a_type)
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    /**
     * Get all notes related to multiple objcts
     *
     * @param array $a_rep_obj_ids repository object IDs array
     * @param boolean $a_no_sub_objs include subobjects true/false
     */
    public static function _countNotesAndCommentsMultiple($a_rep_obj_ids, $a_no_sub_objs = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT count(id) c, rep_obj_id, type FROM note WHERE " .
            " ((type = " . $ilDB->quote(IL_NOTE_PRIVATE, "integer") . " AND " .
            "author = " . $ilDB->quote((int) $ilUser->getId(), "integer") . ") OR " .
            " type = " . $ilDB->quote(IL_NOTE_PUBLIC, "integer") . ") AND " .
            $ilDB->in("rep_obj_id", $a_rep_obj_ids, false, "integer");
        
        if ($a_no_sub_objs) {
            $q .= " AND obj_id = " . $ilDB->quote(0, "integer");
        }
        
        $q .= " GROUP BY rep_obj_id, type ";
        
        $cnt = array();
        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cnt[$rec["rep_obj_id"]][$rec["type"]] = $rec["c"];
        }
        
        return $cnt;
    }

    /**
     * Get all notes related to a specific object
     *
     * @param array $a_rep_obj_ids repository object IDs array
     * @param int $a_sub_obj_id sub objects (if null, all comments are counted)
     */
    public static function _countNotesAndComments(
        $a_rep_obj_id,
        $a_sub_obj_id = null,
        $a_obj_type = "",
        $a_news_id = 0
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT count(id) c, rep_obj_id, type FROM note WHERE " .
            " ((type = " . $ilDB->quote(IL_NOTE_PRIVATE, "integer") . " AND " .
            "author = " . $ilDB->quote((int) $ilUser->getId(), "integer") . ") OR " .
            " type = " . $ilDB->quote(IL_NOTE_PUBLIC, "integer") . ") AND " .
            " rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer");
        
        if ($a_sub_obj_id !== null) {
            $q .= " AND obj_id = " . $ilDB->quote($a_sub_obj_id, "integer");
            $q .= " AND obj_type = " . $ilDB->quote($a_obj_type, "text");
        }

        $q .= " AND news_id = " . $ilDB->quote($a_news_id, "integer");

        $q .= " GROUP BY rep_obj_id, type ";

        $cnt = array();
        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cnt[$rec["rep_obj_id"]][$rec["type"]] = $rec["c"];
        }
        
        return $cnt;
    }

    /**
     * Activate notes feature
     *
     * @param
     * @return
     */
    public static function activateComments($a_rep_obj_id, $a_obj_id, $a_obj_type, $a_activate = true)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_obj_type == "") {
            $a_obj_type = "-";
        }
        $set = $ilDB->query(
            "SELECT * FROM note_settings " .
            " WHERE rep_obj_id = " . $ilDB->quote((int) $a_rep_obj_id, "integer") .
            " AND obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            if (($rec["activated"] == 0 && $a_activate) ||
                ($rec["activated"] == 1 && !$a_activate)) {
                $ilDB->manipulate(
                    "UPDATE note_settings SET " .
                    " activated = " . $ilDB->quote((int) $a_activate, "integer") .
                    " WHERE rep_obj_id = " . $ilDB->quote((int) $a_rep_obj_id, "integer") .
                    " AND obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") .
                    " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
                );
            }
        } else {
            if ($a_activate) {
                $q = "INSERT INTO note_settings " .
                    "(rep_obj_id, obj_id, obj_type, activated) VALUES (" .
                    $ilDB->quote((int) $a_rep_obj_id, "integer") . "," .
                    $ilDB->quote((int) $a_obj_id, "integer") . "," .
                    $ilDB->quote($a_obj_type, "text") . "," .
                    $ilDB->quote(1, "integer") .
                    ")";
                $ilDB->manipulate($q);
            }
        }
    }
    
    /**
     * Are comments activated for object?
     *
     * @param
     * @return
     */
    public static function commentsActivated($a_rep_obj_id, $a_obj_id, $a_obj_type, $a_news_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_news_id > 0) {
            return true;
        }
        
        if ($a_obj_type == "") {
            $a_obj_type = "-";
        }
        $set = $ilDB->query(
            "SELECT * FROM note_settings " .
            " WHERE rep_obj_id = " . $ilDB->quote((int) $a_rep_obj_id, "integer") .
            " AND obj_id = " . $ilDB->quote((int) $a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec["activated"];
    }
    
    /**
     * Get activation for repository objects
     *
     * @param
     * @return
     */
    public static function getRepObjActivation($a_rep_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT * FROM note_settings " .
            " WHERE " . $ilDB->in("rep_obj_id", $a_rep_obj_ids, false, "integer") .
            " AND obj_id = 0 ");
        $activations = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["activated"]) {
                $activations[$rec["rep_obj_id"]][$rec["obj_type"]] = true;
            }
        }

        return $activations;
    }


    /**
     * Send notifications
     */
    public function sendNotifications($a_changed = false)
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        // no notifications for notes
        if ($this->getType() == IL_NOTE_PRIVATE) {
            return;
        }

        $recipients = $ilSetting->get("comments_noti_recip");
        $recipients = explode(",", $recipients);

        // blog: blog_id, 0, "blog"
        // lm: lm_id, page_id, "pg" (ok)
        // sahs: sahs_id, node_id, node_type
        // info_screen: obj_id, 0, obj_type (ok)
        // portfolio: port_id, page_id, "portfolio_page" (ok)
        // wiki: wiki_id, wiki_page_id, "wpg" (ok)

        $obj = $this->getObject();
        $rep_obj_id = $obj["rep_obj_id"];
        $sub_obj_id = $obj["obj_id"];
        $type = $obj["obj_type"];

        include_once("./Services/Language/classes/class.ilLanguageFactory.php");
        include_once("./Services/User/classes/class.ilUserUtil.php");
        include_once("./Services/Mail/classes/class.ilMail.php");

        // repository objects, no blogs
        $ref_ids = array();
        if (($sub_obj_id == 0 and $type != "blp") ||
            in_array($type, array("pg", "wpg"))) {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_" . $type;
            $ref_ids = ilObject::_getAllReferences($rep_obj_id);
        }

        if ($type == "wpg") {
            $type_lv = "obj_wiki";
        }
        if ($type == "pg") {
            $type_lv = "obj_lm";
        }
        if ($type == "blp") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_blog";
        }
        if ($type == "pfpg") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "portfolio";
        }
        if ($type == "dcl") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_dcl";
        }

        include_once("./Services/Link/classes/class.ilLink.php");
        foreach ($recipients as $r) {
            $login = trim($r);
            if (($user_id = ilObjUser::_lookupId($login)) > 0) {
                $link = "";
                foreach ($ref_ids as $r) {
                    if ($ilAccess->checkAccessOfUser($user_id, "read", "", $r)) {
                        if ($sub_obj_id == 0 and $type != "blog") {
                            $link = ilLink::_getLink($r);
                        } elseif ($type == "wpg") {
                            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                            include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
                            $title = ilWikiPage::lookupTitle($sub_obj_id);
                            $link = ilLink::_getStaticLink(
                                $r,
                                "wiki",
                                true,
                                "_" . ilWikiUtil::makeUrlTitle($title)
                            );
                        } elseif ($type == "pg") {
                            $link = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . "&target=pg_" . $sub_obj_id . "_" . $r;
                        }
                    }
                }
                if ($type == "blp") {
                    // todo
                }
                if ($type == "pfpg") {
                    $link = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . "&target=prtf_" . $rep_obj_id;
                }

                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('note');

                if ($a_changed) {
                    $subject = sprintf($ulng->txt('note_comment_notification_subjectc'), $obj_title . " (" . $ulng->txt($type_lv) . ")");
                } else {
                    $subject = sprintf($ulng->txt('note_comment_notification_subject'), $obj_title . " (" . $ulng->txt($type_lv) . ")");
                }
                $message = sprintf($ulng->txt('note_comment_notification_salutation'), ilObjUser::_lookupFullname($user_id)) . "\n\n";

                $message .= sprintf($ulng->txt('note_comment_notification_user_has_written'), ilUserUtil::getNamePresentation($this->getAuthor())) . "\n\n";

                $message .= $this->getText() . "\n\n";

                if ($link != "") {
                    $message .= $ulng->txt('note_comment_notification_link') . ": " . $link . "\n\n";
                }

                $message .= $ulng->txt('note_comment_notification_reason') . "\n\n";

                $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $mail_obj->sendMail(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array(),
                    array("system")
                );
            }
        }
    }
}
