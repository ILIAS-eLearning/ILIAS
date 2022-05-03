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
}
