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

/**
 * Note class. Represents a single note.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNote
{
    public const PRIVATE = 1;
    public const PUBLIC = 2;
    protected int $id = 0;
    protected int $label = 0;
    protected ?string $update_date;
    protected ?string $creation_date;
    protected string $subject = "";
    protected int $author = 0;
    protected int $type = 0;
    protected string $text = "";
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
    protected bool $no_repository = false;

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
        
        if (!$a_use_provided_creation_date) {
            $this->setCreationDate(ilUtil::now());
        }

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
            "creation_date" => array("timestamp", $this->getCreationDate()),
            "no_repository" => array("integer", (int) $this->no_repository)
            ));

        $this->sendNotifications();
    }

    public function update() : void
    {
        $ilDB = $this->db;

        $this->setUpdateDate(ilUtil::now());

        $ilDB->update("note", array(
            "rep_obj_id" => array("integer", $this->rep_obj_id),
            "obj_id" => array("integer", $this->obj_id),
            "news_id" => array("integer", $this->news_id),
            "obj_type" => array("text", $this->obj_type),
            "type" => array("integer", $this->type),
            "author" => array("integer", $this->author),
            "note_text" => array("clob", $this->text),
            "subject" => array("text", $this->subject),
            "update_date" => array("timestamp", $this->getUpdateDate()),
            "no_repository" => array("integer", $this->no_repository)
            ), array(
            "id" => array("integer", $this->getId())
            ));
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
        $this->setCreationDate($a_note_rec["creation_date"]);
        $this->setUpdateDate($a_note_rec["update_date"]);
        $this->setInRepository(!$a_note_rec["no_repository"]);
    }

    public function sendNotifications(
        bool $a_changed = false
    ) : void {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        $obj_title = "";
        $type_lv = "";

        // no notifications for notes
        if ($this->getType() === self::PRIVATE) {
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

                if ($link !== "") {
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
