<?php

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

define("IL_NOTE_UNLABELED", 0);
define("IL_NOTE_IMPORTANT", 1);
define("IL_NOTE_QUESTION", 2);
define("IL_NOTE_PRO", 3);
define("IL_NOTE_CONTRA", 4);

/**
 * Note class. Represents a single note.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNote
{
    public const PRIVATE = 1;
    public const PUBLIC = 2;
    protected int $id;
    protected int $label;
    protected ?string $update_date;
    protected ?string $creation_date;
    protected string $subject;
    protected int $author;
    protected int $type;
    protected string $text;
    protected ilDBInterface $db;
    protected ilSetting $settings;
    protected ilAccessHandler $access;
    /**
     * object id (NOT ref_id!) of repository object (e.g for page objects
     * the obj_id of the learning module; for personal desktop this is set to 0)
     */
    protected int $rep_obj_id = 0;
    /**
     * object id (e.g for page objects the obj_id of the page object)
     * this is set to 0 for normal repository objects like forums ...
     */
    protected int $obj_id;
    /**
     * type of the object (e.g st,pg,crs ... NOT "news")
     */
    protected string $obj_type;

    protected int $news_id;
    protected int $no_repository = 0;

    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        if ($a_id > 0) {
            $this->id = $a_id;
            $this->read();
        }
    }

    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : int
    {
        return $this->id;
    }
    
    // set assigned object
    public function setObject(
        string $a_obj_type,
        int $a_rep_obj_id,
        int $a_obj_id = 0,
        int $a_news_id = 0
    ) : void {
        $this->rep_obj_id = $a_rep_obj_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->news_id = $a_news_id;
    }

    // get the assigned object
    public function getObject() : array
    {
        // note: any changes here will currently influence
        // the parameters of all observers!
        return array(
            "rep_obj_id" => $this->rep_obj_id,
            "obj_id" => $this->obj_id,
            "obj_type" => $this->obj_type,
            "news_id" => $this->news_id
        );
    }
    
    
    /**
     * @param int $a_type ilNote::PUBLIC | ilNote::PRIVATE
     */
    public function setType(int $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function setAuthor(int $a_user_id) : void
    {
        $this->author = $a_user_id;
    }

    public function getAuthor() : int
    {
        return $this->author;
    }
    
    public function setText(
        string $a_text
    ) : void {
        $this->text = $a_text;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function setSubject(
        string $a_subject
    ) : void {
        $this->subject = $a_subject;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }
    
    public function setCreationDate(
        ?string $a_date
    ) : void {
        $this->creation_date = $a_date;
    }

    public function getCreationDate() : ?string
    {
        return $this->creation_date;
    }
    
    public function setUpdateDate(
        ?string $a_date
    ) : void {
        $this->update_date = $a_date;
    }

    public function getUpdateDate() : ?string
    {
        return $this->update_date;
    }
    
    /**
     * @param int $a_label IL_NOTE_UNLABELED | IL_NOTE_IMPORTANT | IL_NOTE_QUESTION
     *					| IL_NOTE_PRO | IL_NOTE_CONTRA
     * @deprecated
     */
    public function setLabel(int $a_label) : void
    {
        $this->label = $a_label;
    }
    
    public function getLabel() : int
    {
        return $this->label;
    }
    
    public function setNewsId(int $a_val) : void
    {
        $this->news_id = $a_val;
    }
    
    public function getNewsId() : int
    {
        return $this->news_id;
    }
    
    /**
     * set repository object status
     */
    public function setInRepository(bool $a_value) : void
    {
        $this->no_repository = !$a_value;
    }
    
    /**
     * belongs note to repository object?
     */
    public function isInRepository() : bool
    {
        return !$this->no_repository;
    }
    
    public function create(
        bool $a_use_provided_creation_date = false
    ) : void {
        $ilDB = $this->db;
        
        $cd = ($a_use_provided_creation_date)
            ? $this->getCreationDate()
            : ilUtil::now();
        
        $this->id = $ilDB->nextId("note");

        $ilDB->insert("note", array(
            "id" => array("integer", $this->id),
            "rep_obj_id" => array("integer", $this->rep_obj_id),
            "obj_id" => array("integer", $this->obj_id),
            "obj_type" => array("text", $this->obj_type),
            "news_id" => array("integer", $this->news_id),
            "type" => array("integer", $this->type),
            "author" => array("integer", $this->author),
            "note_text" => array("clob", $this->text),
            "subject" => array("text", $this->subject),
            "label" => array("integer", $this->label),
            "creation_date" => array("timestamp", $cd),
            "no_repository" => array("integer", $this->no_repository)
            ));

        $this->sendNotifications();
        
        $this->creation_date = self::_lookupCreationDate($this->getId());
    }

    public function update() : void
    {
        $ilDB = $this->db;
        
        $ilDB->update("note", array(
            "rep_obj_id" => array("integer", $this->rep_obj_id),
            "obj_id" => array("integer", $this->obj_id),
            "news_id" => array("integer", $this->news_id),
            "obj_type" => array("text", $this->obj_type),
            "type" => array("integer", $this->type),
            "author" => array("integer", $this->author),
            "note_text" => array("clob", $this->text),
            "subject" => array("text", $this->subject),
            "label" => array("integer", $this->label),
            "update_date" => array("timestamp", ilUtil::now()),
            "no_repository" => array("integer", $this->no_repository)
            ), array(
            "id" => array("integer", $this->getId())
            ));
        
        $this->update_date = self::_lookupUpdateDate($this->getId());

        $this->sendNotifications(true);
    }

    public function read() : void
    {
        $ilDB = $this->db;
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);
        $this->setAllData($note_rec);
    }
    
    public function delete() : void
    {
        $ilDB = $this->db;
        
        $q = "DELETE FROM note WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);
    }
    
    /**
     * set all note data by record array
     */
    public function setAllData(
        array $a_note_rec
    ) : void {
        $this->setId((int) $a_note_rec["id"]);
        $this->setObject(
            $a_note_rec["obj_type"],
            (int) $a_note_rec["rep_obj_id"],
            (int) $a_note_rec["obj_id"],
            (int) $a_note_rec["news_id"]
        );
        $this->setType((int) $a_note_rec["type"]);
        $this->setAuthor($a_note_rec["author"]);
        $this->setText($a_note_rec["note_text"]);
        $this->setSubject($a_note_rec["subject"]);
        $this->setLabel((int) $a_note_rec["label"]);
        $this->setCreationDate($a_note_rec["creation_date"]);
        $this->setUpdateDate($a_note_rec["update_date"]);
        $this->setInRepository(!(bool) $a_note_rec["no_repository"]);
    }
    
    public static function _lookupCreationDate(
        int $a_id
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);

        return $note_rec["creation_date"] ?? "";
    }

    public static function _lookupUpdateDate(
        int $a_id
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM note WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $set = $ilDB->query($q);
        $note_rec = $ilDB->fetchAssoc($set);

        return $note_rec["update_date"] ?? "";
    }
    
    /**
     * get all notes related to a specific object
     */
    public static function _getNotesOfObject(
        int $a_rep_obj_id,
        int $a_obj_id,
        string $a_obj_type,
        int $a_type = ilNote::PRIVATE,
        bool $a_incl_sub = false,
        string $a_filter = "",
        string $a_all_public = "y",
        bool $a_repository_mode = true,
        bool $a_sort_ascending = false,
        int $a_news_id = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $author_where = ($a_type == self::PRIVATE || $a_all_public === "n")
            ? " AND author = " . $ilDB->quote($ilUser->getId(), "integer")
            : "";

        $sub_where = (!$a_incl_sub)
            ? " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
              " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
            : "";

        $news_where =
            " AND news_id = " . $ilDB->quote($a_news_id, "integer");


        $sub_where .= " AND no_repository = " . $ilDB->quote(!$a_repository_mode, "integer");

        $q = "SELECT * FROM note WHERE " .
            " rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer") .
            $sub_where .
            " AND type = " . $ilDB->quote($a_type, "integer") .
            $author_where .
            $news_where .
            " ORDER BY creation_date ";
        
        $q .= ($a_sort_ascending) ? "ASC" : "DESC";
        
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
        int $a_rep_obj_id,
        int $a_type = ilNote::PRIVATE,
        bool $a_incl_sub = false,
        bool $a_sort_ascending = false,
        string $a_since = ""
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $sub_where = (!$a_incl_sub)
            ? " AND obj_id = " . $ilDB->quote(0, "integer") : "";

        if ($a_since != "") {
            $sub_where .= " AND creation_date > " . $ilDB->quote($a_since, "timestamp");
        }

        $sub_where .= " AND no_repository = " . $ilDB->quote(0, "integer");

        $q = "SELECT * FROM note WHERE " .
            " rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer") .
            $sub_where .
            " AND type = " . $ilDB->quote($a_type, "integer") .
            " ORDER BY creation_date ";

        $q .= ($a_sort_ascending) ? "ASC" : "DESC";
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
    public static function _getRelatedObjectsOfUser(string $a_mode) : array
    {
        global $DIC;

        $fav_rep = new ilFavouritesDBRepository();

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $tree = $DIC->repositoryTree();
        
        if ($a_mode == ilPDNotesGUI::PRIVATE_NOTES) {
            $q = "SELECT DISTINCT rep_obj_id FROM note WHERE " .
                " type = " . $ilDB->quote(self::PRIVATE, "integer") .
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
                " type = " . $ilDB->quote(self::PUBLIC, "integer") .
                " AND author = " . $ilDB->quote($ilUser->getId(), "integer") .
                " AND (no_repository IS NULL OR no_repository < " . $ilDB->quote(1, "integer") . ")" .
                " ORDER BY rep_obj_id";

            $set = $ilDB->query($q);
            $reps = array();
            while ($rep_rec = $ilDB->fetchAssoc($set)) {
                // #9343: deleted objects
                if ($type = ilObject::_lookupType((int) $rep_rec["rep_obj_id"])) {
                    if (self::commentsActivated((int) $rep_rec["rep_obj_id"], 0, $type)) {
                        $reps[] = array("rep_obj_id" => (int) $rep_rec["rep_obj_id"]);
                    }
                }
            }
            
            // additionally all objects on the personal desktop of the user
            // that have at least on comment
            $dis = $fav_rep->getFavouritesOfUser($ilUser->getId());
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
                    foreach ($reps as $r) {
                        if ($r["rep_obj_id"] == $rec["rep_obj_id"]) {
                            $add = false;
                        }
                    }
                    if ($add) {
                        $type = ilObject::_lookupType($rec["rep_obj_id"]);
                        if (self::commentsActivated($rec["rep_obj_id"], "", $type)) {
                            $reps[] = array("rep_obj_id" => $rec["rep_obj_id"]);
                        }
                    }
                }
            }
        }
                
        if (count($reps)) {
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
     */
    public static function getUserCount(
        int $a_rep_obj_id,
        int $a_obj_id,
        string $a_type
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->queryF(
            "SELECT count(DISTINCT author) cnt FROM note WHERE " .
            "rep_obj_id = %s AND obj_id = %s AND obj_type = %s",
            array("integer", "integer", "text"),
            array($a_rep_obj_id, $a_obj_id, $a_type)
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    /**
     * Get all notes related to multiple repository objects
     */
    public static function _countNotesAndCommentsMultiple(
        array $a_rep_obj_ids,
        bool $a_no_sub_objs = false
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT count(id) c, rep_obj_id, type FROM note WHERE " .
            " ((type = " . $ilDB->quote(self::PRIVATE, "integer") . " AND " .
            "author = " . $ilDB->quote($ilUser->getId(), "integer") . ") OR " .
            " type = " . $ilDB->quote(self::PUBLIC, "integer") . ") AND " .
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
     */
    public static function _countNotesAndComments(
        int $a_rep_obj_id,
        ?int $a_sub_obj_id = null,
        string $a_obj_type = "",
        int $a_news_id = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT count(id) c, rep_obj_id, type FROM note WHERE " .
            " ((type = " . $ilDB->quote(self::PRIVATE, "integer") . " AND " .
            "author = " . $ilDB->quote($ilUser->getId(), "integer") . ") OR " .
            " type = " . $ilDB->quote(self::PUBLIC, "integer") . ") AND " .
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
     */
    public static function activateComments(
        int $a_rep_obj_id,
        int $a_obj_id,
        string $a_obj_type,
        bool $a_activate = true
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_obj_type == "") {
            $a_obj_type = "-";
        }
        $set = $ilDB->query(
            "SELECT * FROM note_settings " .
            " WHERE rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            if (($rec["activated"] == 0 && $a_activate) ||
                ($rec["activated"] == 1 && !$a_activate)) {
                $ilDB->manipulate(
                    "UPDATE note_settings SET " .
                    " activated = " . $ilDB->quote((int) $a_activate, "integer") .
                    " WHERE rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer") .
                    " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
                    " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
                );
            }
        } elseif ($a_activate) {
            $q = "INSERT INTO note_settings " .
                "(rep_obj_id, obj_id, obj_type, activated) VALUES (" .
                $ilDB->quote($a_rep_obj_id, "integer") . "," .
                $ilDB->quote($a_obj_id, "integer") . "," .
                $ilDB->quote($a_obj_type, "text") . "," .
                $ilDB->quote(1, "integer") .
                ")";
            $ilDB->manipulate($q);
        }
    }
    
    /**
     * Are comments activated for object?
     */
    public static function commentsActivated(
        int $a_rep_obj_id,
        int $a_obj_id,
        string $a_obj_type,
        int $a_news_id = 0
    ) : bool {
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
            " WHERE rep_obj_id = " . $ilDB->quote($a_rep_obj_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec["activated"] ?? false;
    }
    
    /**
     * Get activation for repository objects
     */
    public static function getRepObjActivation(
        array $a_rep_obj_ids
    ) : array {
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

    public function sendNotifications(
        bool $a_changed = false
    ) : void {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        $obj_title = "";
        $type_lv = "";

        // no notifications for notes
        if ($this->getType() == self::PRIVATE) {
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

        // repository objects, no blogs
        $ref_ids = array();
        if (($sub_obj_id == 0 && $type !== "blp") || in_array($type, array("pg", "wpg"), true)) {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_" . $type;
            $ref_ids = ilObject::_getAllReferences($rep_obj_id);
        }

        if ($type === "wpg") {
            $type_lv = "obj_wiki";
        }
        if ($type === "pg") {
            $type_lv = "obj_lm";
        }
        if ($type === "blp") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_blog";
        }
        if ($type === "pfpg") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "portfolio";
        }
        if ($type === "dcl") {
            $obj_title = ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_dcl";
        }

        foreach ($recipients as $r) {
            $login = trim($r);
            if (($user_id = ilObjUser::_lookupId($login)) > 0) {
                $link = "";
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccessOfUser($user_id, "read", "", $ref_id)) {
                        if ($sub_obj_id == 0 && $type !== "blog") {
                            $link = ilLink::_getLink($ref_id);
                        } elseif ($type === "wpg") {
                            $title = ilWikiPage::lookupTitle($sub_obj_id);
                            $link = ilLink::_getStaticLink(
                                $ref_id,
                                "wiki",
                                true,
                                "_" . ilWikiUtil::makeUrlTitle($title)
                            );
                        } elseif ($type === "pg") {
                            $link = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . "&target=pg_" . $sub_obj_id . "_" . $ref_id;
                        }
                    }
                }
                if ($type === "blp") {
                    // todo
                }
                if ($type === "pfpg") {
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
                $mail_obj->enqueue(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array()
                );
            }
        }
    }
}
