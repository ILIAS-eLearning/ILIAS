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
 * Class ilObject
 * Basic functions for all objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObject
{
    const TITLE_LENGTH = 255; // title column max length in db
    const DESC_LENGTH = 128; // (short) description column max length in db
    const TABLE_OBJECT_DATA = "object_data";

    protected ?ILIAS $ilias;
    protected ?ilObjectDefinition $obj_definition;
    protected ilDBInterface $db;
    protected ?ilLogger $log;
    protected ?ilErrorHandling $error;
    protected ilTree $tree;
    protected ?ilAppEventHandler $app_event_handler;
    protected ilRbacAdmin $rbac_admin;
    protected ilRbacReview $rbac_review;
    protected ilObjUser $user;
    protected ilLanguage $lng;

    protected int $id;
    protected bool $referenced;
    protected bool $call_by_reference;
    protected int $max_title = self::TITLE_LENGTH;
    protected int $max_desc = self::DESC_LENGTH;
    protected bool $add_dots = true;
    protected ?int $ref_id = null;
    protected string $type = "";
    protected string $title = "";
    protected bool $offline = false;
    protected string $desc = "";
    protected string $long_desc = "";
    protected int $owner = 0;
    protected string $create_date = "";
    protected string $last_update = "";
    protected string $import_id = "";
    protected bool $register = false;	// registering required for object? set to true to implement a subscription interface


    /**
    * @var array contains all child objects of current object
    */
    public array $objectList;


    // BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
    public string $untranslatedTitle;
    // END WebDAV: WebDAV needs to access the untranslated title of an object
    
    /**
     * @param int  $id        reference_id or object_id
     * @param bool $reference bool treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(int $id = 0, bool $reference = true)
    {
        global $DIC;

        $this->ilias = $DIC["ilias"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->db = $DIC["ilDB"];
        $this->log = $DIC["ilLog"];
        $this->error = $DIC["ilErr"];
        $this->tree = $DIC["tree"];
        $this->app_event_handler = $DIC["ilAppEventHandler"];

        $this->referenced = $reference;
        $this->call_by_reference = $reference;

        if (isset($DIC["lng"])) {
            $this->lng = $DIC["lng"];
        }

        if (isset($DIC["ilUser"])) {
            $this->user = $DIC["ilUser"];
        }

        if (isset($DIC["rbacadmin"])) {
            $this->rbac_admin = $DIC["rbacadmin"];
        }

        if (isset($DIC["rbacreview"])) {
            $this->rbac_review = $DIC["rbacreview"];
        }

        if ($id == 0) {
            $this->referenced = false;		// newly created objects are never referenced
        }									// they will get referenced if createReference() is called

        if ($this->referenced) {
            $this->ref_id = $id;
        } else {
            $this->id = $id;
        }
        // read object data
        if ($id != 0) {
            $this->read();
        }
    }

    /**
    * determines whether objects are referenced or not (got ref ids or not)
    */
    final public function withReferences() : bool
    {
        // both vars could differ. this method should always return true if one of them is true without changing their status
        return ($this->call_by_reference) ? true : $this->referenced;
    }


    public function read() : void
    {
        global $DIC;
        try {
            $ilUser = $DIC["ilUser"];
        } catch (InvalidArgumentException $e) {
        }

        if ($this->referenced) {
            if (!isset($this->ref_id)) {
                $message = "ilObject::read(): No ref_id given! (" . $this->type . ")";
                $this->error->raiseError($message, $this->error->WARNING);
            }

            // read object data
            $sql =
                "SELECT od.obj_id, od.type, od.title, od.description, od.owner, od.create_date," . PHP_EOL
                . "od.last_update, od.import_id, od.offline, ore.ref_id, ore.obj_id, ore.deleted, ore.deleted_by" . PHP_EOL
                . "FROM " . self::TABLE_OBJECT_DATA . " od" . PHP_EOL
                . "JOIN object_reference ore ON od.obj_id = ore.obj_id" . PHP_EOL
                . "WHERE ore.ref_id = " . $this->db->quote($this->ref_id, "integer") . PHP_EOL
            ;

            $result = $this->db->query($sql);

            // check number of records
            if ($this->db->numRows($result) == 0) {
                $message = sprintf(
                    "ilObject::read(): Object with ref_id %s not found! (%s)",
                    $this->ref_id,
                    $this->type
                );
                $this->error->raiseError($message, $this->error->WARNING);
            }
        } else {
            if (!isset($this->id)) {
                $message = sprintf("ilObject::read(): No obj_id given! (%s)", $this->type);
                $this->error->raiseError($message, $this->error->WARNING);
            }

            $sql =
                "SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
                . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->id, "integer") . PHP_EOL
            ;
            $result = $this->db->query($sql);

            if ($this->db->numRows($result) == 0) {
                $message = sprintf("ilObject::read(): Object with obj_id: %s (%s) not found!", $this->id, $this->type);
                throw new ilObjectNotFoundException($message);
            }
        }
        $obj = $this->db->fetchAssoc($result);

        $this->id = (int) $obj["obj_id"];

        // check type match (the "xxx" type is used for the unit test)
        if ($this->type != $obj["type"] && $obj["type"] != "xxx") {
            $message = sprintf(
                "ilObject::read(): Type mismatch. Object with obj_id: %s was instantiated by type '%s'. DB type is: %s",
                $this->id,
                $this->type,
                $obj["type"]
            );

            $this->log->write($message);
            throw new ilObjectTypeMismatchException($message);
        }
        
        $this->type = (string) $obj["type"];
        $this->title = (string) $obj["title"];
        // BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
        $this->untranslatedTitle = (string) $obj["title"];
        // END WebDAV: WebDAV needs to access the untranslated title of an object

        $this->desc = (string) $obj["description"];
        $this->owner = (int) $obj["owner"];
        $this->create_date = (string) $obj["create_date"];
        $this->last_update = (string) $obj["last_update"];
        $this->import_id = (string) $obj["import_id"];
        
        $this->setOfflineStatus((bool) $obj['offline']);
        
        if ($this->obj_definition->isRBACObject($this->getType())) {
            $sql =
                "SELECT obj_id, description" . PHP_EOL
                . "FROM object_description" . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->id, 'integer') . PHP_EOL
            ;

            $res = $this->db->query($sql);

            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (($row->description ?? '') !== '') {
                    $this->setDescription($row->description);
                }
            }
        }

        // multilingual support system objects (sys) & categories (db)
        $translation_type = $this->obj_definition->getTranslationType($this->type);

        if ($translation_type == "sys") {
            $this->title = $this->lng->txt("obj_" . $this->type);
            $this->setDescription($this->lng->txt("obj_" . $this->type . "_desc"));
        } elseif ($translation_type == "db") {
            $sql =
                "SELECT title, description" . PHP_EOL
                . "FROM object_translation" . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->id, 'integer') . PHP_EOL
                . "AND lang_code = " . $this->db->quote($ilUser->getCurrentLanguage(), 'text') . PHP_EOL
                . "AND NOT lang_default = 1" . PHP_EOL
            ;
            $r = $this->db->query($sql);
            $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
            if ($row) {
                $this->title = (string) $row->title;
                $this->setDescription((string) $row->description);
            }
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    final public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
        $this->referenced = true;
    }

    final public function getRefId() : int
    {
        return $this->ref_id ?? 0;
    }

    public function getType() : string
    {
        return $this->type;
    }

    final public function setType(string $type) : void
    {
        $this->type = $type;
    }

    /**
     * get presentation title
     * Normally same as title
     * Overwritten for sessions
     */
    public function getPresentationTitle() : string
    {
        return $this->getTitle();
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Get untranslated object title
     * WebDAV needs to access the untranslated title of an object
     */
    final public function getUntranslatedTitle() : string
    {
        return $this->untranslatedTitle;
    }

    final public function setTitle(string $title) : void
    {
        $this->title = ilStr::shortenTextExtended($title, $this->max_title ?? self::TITLE_LENGTH, $this->add_dots);

        // WebDAV needs to access the untranslated title of an object
        $this->untranslatedTitle = $this->title;
    }

    final public function getDescription() : string
    {
        return $this->desc;
    }

    final public function setDescription(string $desc) : void
    {
        // Shortened form is storted in object_data. Long form is stored in object_description
        $this->desc = ilStr::shortenTextExtended($desc, $this->max_desc, $this->add_dots);
        $this->long_desc = $desc;
    }

    /**
     * get object long description (stored in object_description)
     */
    public function getLongDescription() : string
    {
        if (strlen($this->long_desc)) {
            return $this->long_desc;
        } elseif (strlen($this->desc)) {
            return $this->desc;
        }
        return "";
    }

    final public function getImportId() : string
    {
        return $this->import_id;
    }

    final public function setImportId(string $import_id) : void
    {
        $this->import_id = $import_id;
    }

    /**
     * Get (latest) object id for an import id
     */
    final public static function _lookupObjIdByImportId(string $import_id) : int
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE import_id = " . $db->quote($import_id, "text") . PHP_EOL
            . "ORDER BY create_date DESC" . PHP_EOL
        ;
        $result = $db->query($sql);

        if ($db->numRows($result) == 0) {
            return 0;
        }

        $row = $db->fetchObject($result);

        return (int) $row->obj_id;
    }

    public function setOfflineStatus(bool $status) : void
    {
        $this->offline = $status;
    }

    public function getOfflineStatus() : bool
    {
        return $this->offline;
    }

    public function supportsOfflineHandling() : bool
    {
        return $this->obj_definition->supportsOfflineHandling($this->getType());
    }

    public static function _lookupImportId(int $obj_id) : string
    {
        global $DIC;

        $db = $DIC->database();

        $sql =
            "SELECT import_id" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE obj_id = " . $db->quote($obj_id, "integer") . PHP_EOL
        ;

        $res = $db->query($sql);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->import_id;
        }
        return '';
    }

    final public function getOwner() : int
    {
        return $this->owner;
    }

    /**
     * get full name of object owner
     */
    final public function getOwnerName() : string
    {
        return ilObject::_lookupOwnerName($this->getOwner());
    }

    /**
     * Lookup owner name for owner id
     */
    final public static function _lookupOwnerName(int $owner_id) : string
    {
        global $DIC;
        $lng = $DIC->language();

        $owner = null;
        if ($owner_id != -1) {
            if (ilObject::_exists($owner_id)) {
                $owner = new ilObjUser($owner_id);
            }
        }

        $own_name = $lng->txt("unknown");
        if (is_object($owner)) {
            $own_name = $owner->getFullname();
        }

        return $own_name;
    }

    final public function setOwner(int $usr_id) : void
    {
        $this->owner = $usr_id;
    }

    /**
     * Get create date in YYYY-MM-DD HH-MM-SS format
     */
    final public function getCreateDate() : string
    {
        return $this->create_date;
    }

    /**
     * Get last update date in YYYY-MM-DD HH-MM-SS format
     */
    final public function getLastUpdateDate() : string
    {
        return $this->last_update;
    }
    

    /**
     * note: title, description and type should be set when this function is called
     */
    public function create() : int
    {
        global $DIC;
        $user = $DIC["ilUser"];

        if (!isset($this->type)) {
            $message = sprintf("%s::create(): No object type given!", get_class($this));
            $this->error->raiseError($message, $this->error->WARNING);
        }

        $this->log->write("ilObject::create(), start");
        
        $this->title = ilStr::shortenTextExtended($this->getTitle(), $this->max_title, $this->add_dots);
        $this->desc = ilStr::shortenTextExtended($this->getDescription(), $this->max_desc, $this->add_dots);
        
        // determine owner
        $owner = 0;
        if ($this->getOwner() > 0) {
            $owner = $this->getOwner();
        } elseif (is_object($user)) {
            $owner = $user->getId();
        }

        $this->id = $this->db->nextId(self::TABLE_OBJECT_DATA);
        $values = [
            "obj_id" => ["integer", $this->getId()],
            "type" => ["text", $this->getType()],
            "title" => ["text", $this->getTitle()],
            "description" => ["text", $this->getDescription()],
            "owner" => ["integer", $owner],
            "create_date" => ["date", $this->db->now()],
            "last_update" => ["date", $this->db->now()],
            "import_id" => ["text", $this->getImportId()],
            "offline" => ["integer", $this->supportsOfflineHandling() ? $this->getOfflineStatus() : null]
        ];

        $this->db->insert(self::TABLE_OBJECT_DATA, $values);


        // Save long form of description if is rbac object
        if ($this->obj_definition->isRBACObject($this->getType())) {
            $values = [
                'obj_id' => ['integer',$this->id],
                'description' => ['clob', $this->getLongDescription()]
            ];
            $this->db->insert('object_description', $values);
        }

        if ($this->obj_definition->isOrgUnitPermissionType($this->type)) {
            ilOrgUnitGlobalSettings::getInstance()->saveDefaultPositionActivationStatus($this->id);
        }

        // the line ($this->read();) messes up meta data handling: meta data,
        // that is not saved at this time, gets lost, so we query for the dates alone
        //$this->read();
        $sql =
            "SELECT last_update, create_date" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->id, "integer") . PHP_EOL
        ;
        $obj_set = $this->db->query($sql);
        $obj_rec = $this->db->fetchAssoc($obj_set);
        $this->last_update = $obj_rec["last_update"];
        $this->create_date = $obj_rec["create_date"];

        // set owner for new objects
        $this->setOwner($owner);

        // write log entry
        $this->log->write(sprintf(
            "ilObject::create(), finished, obj_id: %s, type: %s, title: %s",
            $this->getId(),
            $this->getType(),
            $this->getTitle()
        ));

        $this->app_event_handler->raise(
            'Services/Object',
            'create',
            [
                'obj_id' => $this->id,
                'obj_type' => $this->type
            ]
        );

        return $this->id;
    }

    public function update() : bool
    {
        $values = [
            "title" => ["text", $this->getTitle()],
            "description" => ["text", ilStr::subStr($this->getDescription(), 0, 128)],
            "last_update" => ["date", $this->db->now()],
            "import_id" => ["text", $this->getImportId()],
            "offline" => ["integer", $this->supportsOfflineHandling() ? $this->getOfflineStatus() : null]
        ];

        $where = [
            "obj_id" => ["integer", $this->getId()]
        ];

        $this->db->update(self::TABLE_OBJECT_DATA, $values, $where);

        // the line ($this->read();) messes up meta data handling: metadata,
        // that is not saved at this time, gets lost, so we query for the dates alone
        //$this->read();
        $sql =
            "SELECT last_update" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->getId(), "integer") . PHP_EOL
        ;
        $obj_set = $this->db->query($sql);
        $obj_rec = $this->db->fetchAssoc($obj_set);
        $this->last_update = $obj_rec["last_update"];

        if ($this->obj_definition->isRBACObject($this->getType())) {
            // Update long description
            $sql =
                "SELECT obj_id, description" . PHP_EOL
                . "FROM object_description" . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . PHP_EOL
            ;
            $res = $this->db->query($sql);

            if ($res->numRows()) {
                $values = [
                    'description' => ['clob',$this->getLongDescription()]
                ];
                $where = [
                    'obj_id' => ['integer',$this->getId()]
                ];
                $this->db->update('object_description', $values, $where);
            } else {
                $values = [
                    'description' => ['clob',$this->getLongDescription()],
                    'obj_id' => ['integer',$this->getId()]
                ];
                $this->db->insert('object_description', $values);
            }
        }

        $this->app_event_handler->raise(
            'Services/Object',
            'update',
            [
                'obj_id' => $this->getId(),
                'obj_type' => $this->getType(),
                'ref_id' => $this->getRefId()
            ]
        );

        return true;
    }

    /**
     * Metadata update listener
     *
     * Important note: Do never call create() or update()
     * method of ilObject here. It would result in an
     * endless loop: update object -> update meta -> update
     * object -> ...
     * Use static _writeTitle() ... methods instead.
     */
    final public function MDUpdateListener(string $element) : void
    {
        if ($this->beforeMDUpdateListener($element)) {
            $this->app_event_handler->raise(
                'Services/Object',
                'update',
                array('obj_id' => $this->getId(),
                      'obj_type' => $this->getType(),
                      'ref_id' => $this->getRefId()
                )
            );

            // Update Title and description
            if ($element == 'General') {
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_gen = $md->getGeneral())) {
                    return;
                }
                $this->setTitle($md_gen->getTitle());

                foreach ($md_gen->getDescriptionIds() as $id) {
                    $md_des = $md_gen->getDescription($id);
                    $this->setDescription($md_des->getDescription());
                    break;
                }
                $this->update();
            }
            $this->doMDUpdateListener($element);
        }
    }

    protected function doMDUpdateListener(string $a_element) : void
    {
    }

    protected function beforeMDUpdateListener(string $a_element) : bool
    {
        return true;
    }

    final public function createMetaData() : void
    {
        if ($this->beforeCreateMetaData()) {
            global $DIC;
            $ilUser = $DIC["ilUser"];

            $md_creator = new ilMDCreator($this->getId(), 0, $this->getType());
            $md_creator->setTitle($this->getTitle());
            $md_creator->setTitleLanguage($ilUser->getPref('language'));
            $md_creator->setDescription($this->getLongDescription());
            $md_creator->setDescriptionLanguage($ilUser->getPref('language'));
            $md_creator->setKeywordLanguage($ilUser->getPref('language'));
            // see https://docu.ilias.de/goto_docu_wiki_wpage_4891_1357.html
            //$md_creator->setLanguage($ilUser->getPref('language'));
            $md_creator->create();
            $this->doCreateMetaData();
        }
    }

    protected function doCreateMetaData() : void
    {
    }

    protected function beforeCreateMetaData() : bool
    {
        return true;
    }

    final public function updateMetaData() : void
    {
        if ($this->beforeUpdateMetaData()) {
            $md = new ilMD($this->getId(), 0, $this->getType());
            $md_gen = $md->getGeneral();
            // BEGIN WebDAV: metadata can be missing sometimes.
            if (!$md_gen instanceof ilMDGeneral) {
                $this->createMetaData();
                $md = new ilMD($this->getId(), 0, $this->getType());
                $md_gen = $md->getGeneral();
            }
            // END WebDAV: metadata can be missing sometimes.
            $md_gen->setTitle($this->getTitle());

            // sets first description (maybe not appropriate)
            $md_des_ids = $md_gen->getDescriptionIds();
            if (count($md_des_ids) > 0) {
                $md_des = $md_gen->getDescription($md_des_ids[0]);
                $md_des->setDescription($this->getLongDescription());
                $md_des->update();
            }
            $md_gen->update();
            $this->doUpdateMetaData();
        }
    }

    protected function doUpdateMetaData() : void
    {
    }

    protected function beforeUpdateMetaData() : bool
    {
        return true;
    }

    final public function deleteMetaData() : void
    {
        if ($this->beforeDeleteMetaData()) {
            $md = new ilMD($this->getId(), 0, $this->getType());
            $md->deleteAll();
            $this->doDeleteMetaData();
        }
    }

    protected function doDeleteMetaData() : void
    {
    }

    protected function beforeDeleteMetaData() : bool
    {
        return true;
    }

    /**
     * update owner of object in db
     */
    final public function updateOwner() : void
    {
        $values = [
            "owner" => ["integer", $this->getOwner()],
            "last_update" => ["date", $this->db->now()]
        ];

        $where = [
            "obj_id" => ["integer", $this->getId()]
        ];

        $this->db->update(self::TABLE_OBJECT_DATA, $values, $where);

        // get current values from database so last_update is updated as well
        $this->read();
    }

    final public static function _getIdForImportId(string $import_id) : int
    {
        global $DIC;
        $db = $DIC->database();
        $db->setLimit(1, 0);

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE import_id = " . $db->quote($import_id, "text") . PHP_EOL
            . "ORDER BY create_date DESC" . PHP_EOL
        ;

        $result = $db->query($sql);

        if ($row = $db->fetchAssoc($result)) {
            return (int) $row["obj_id"];
        }

        return 0;
    }

    /**
     * get all reference ids for object ID
     * @return array<int, int>
     */
    final public static function _getAllReferences(int $id) : array
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT ref_id" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "WHERE obj_id = " . $db->quote($id, 'integer') . PHP_EOL
        ;

        $result = $db->query($sql);

        $ref = array();
        while ($row = $db->fetchAssoc($result)) {
            $ref[(int) $row["ref_id"]] = (int) $row["ref_id"];
        }

        return $ref;
    }

    public static function _lookupTitle(int $obj_id) : string
    {
        global $DIC;
        return (string) $DIC["ilObjDataCache"]->lookupTitle($obj_id);
    }

    /**
     * Lookup offline status using objectDataCache
     */
    public static function lookupOfflineStatus(int $obj_id) : bool
    {
        global $DIC;
        return $DIC['ilObjDataCache']->lookupOfflineStatus($obj_id);
    }

    /**
     * Lookup owner user ID for object ID
     */
    final public static function _lookupOwner(int $obj_id) : int
    {
        global $DIC;
        return (int) $DIC["ilObjDataCache"]->lookupOwner($obj_id);
    }

    /**
     * @return int[]
     */
    final public static function _getIdsForTitle(string $title, string $type = '', bool $partial_match = false) : array
    {
        global $DIC;
        $db = $DIC->database();

        $where = "title = " . $db->quote($title, "text");
        if ($partial_match) {
            $where = $db->like("title", "text", '%' . $title . '%');
        }

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE " . $where . PHP_EOL
        ;

        if ($type != '') {
            $sql .= " AND type = " . $db->quote($type, "text");
        }
        
        $result = $db->query($sql);

        $object_ids = [];
        while ($row = $db->fetchAssoc($result)) {
            $object_ids[] = (int) $row['obj_id'];
        }

        return $object_ids;
    }

    final public static function _lookupDescription(int $obj_id) : string
    {
        global $DIC;
        return (string) $DIC["ilObjDataCache"]->lookupDescription($obj_id);
    }

    final public static function _lookupLastUpdate(int $obj_id, bool $formatted = false) : string
    {
        global $DIC;

        $last_update = $DIC["ilObjDataCache"]->lookupLastUpdate($obj_id);

        if ($formatted) {
            return ilDatePresentation::formatDate(new ilDateTime($last_update, IL_CAL_DATETIME));
        }

        return (string) $last_update;
    }

    final public static function _getLastUpdateOfObjects(array $obj_ids) : string
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT MAX(last_update) as last_update" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE " . $db->in("obj_id", $obj_ids, false, "integer") . PHP_EOL
        ;

        $result = $db->query($sql);
        $row = $db->fetchAssoc($result);

        return (string) $row["last_update"];
    }

    final public static function _lookupObjId(int $ref_id) : int
    {
        global $DIC;
        return $DIC["ilObjDataCache"]->lookupObjId($ref_id);
    }

    final public static function _setDeletedDate(int $ref_id, int $deleted_by) : void
    {
        global $DIC;
        $db = $DIC->database();

        $values = [
            "deleted" => ["date", $db->now()],
            "deleted_by" => ["integer", $deleted_by]
        ];

        $where = [
            "ref_id" => ["integer", $ref_id]
        ];

        $db->update("object_reference", $values, $where);
    }

    /**
     * @param int[] $ref_ids
     */
    public static function setDeletedDates(array $ref_ids, int $user_id) : void
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "UPDATE object_reference" . PHP_EOL
            . "SET deleted = " . $db->now() . ", " . PHP_EOL
            . "deleted_by = " . $db->quote($user_id, "integer") . PHP_EOL
            . "WHERE " . $db->in("ref_id", $ref_ids, false, "integer") . PHP_EOL;

        $db->manipulate($sql);
    }

    final public static function _resetDeletedDate(int $ref_id) : void
    {
        global $DIC;
        $db = $DIC->database();

        $values = [
            "deleted" => ["timestamp", null],
            "deleted_by" => ["integer", 0]
        ];

        $where = [
            "ref_id" => ["integer", $ref_id]
        ];

        $db->update("object_reference", $values, $where);
    }

    final public static function _lookupDeletedDate(int $ref_id) : ?string
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT deleted" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "WHERE ref_id = " . $db->quote($ref_id, "integer") . PHP_EOL
        ;
        $result = $db->query($sql);
        $row = $db->fetchAssoc($result);

        return $row["deleted"];
    }

    /**
    * write title to db (static)
    */
    final public static function _writeTitle(int $obj_id, string $title) : void
    {
        global $DIC;
        $db = $DIC->database();

        $values = [
            "title" => ["text", $title],
            "last_update" => ["date", $db->now()]
        ];

        $where = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db->update(self::TABLE_OBJECT_DATA, $values, $where);
    }

    /**
    * write description to db (static)
    */
    final public static function _writeDescription(int $obj_id, string $desc) : void
    {
        global $DIC;

        $db = $DIC->database();
        $obj_definition = $DIC["objDefinition"];

        $desc = ilStr::shortenTextExtended($desc, self::DESC_LENGTH, true);

        $values = [
            "description" => ["text", $desc],
            "last_update" => ["date", $db->now()]
        ];

        $where = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db->update(self::TABLE_OBJECT_DATA, $values, $where);


        if ($obj_definition->isRBACObject(ilObject::_lookupType($obj_id))) {
            // Update long description
            $sql =
                "SELECT obj_id, description" . PHP_EOL
                . "FROM object_description" . PHP_EOL
                . "WHERE obj_id = " . $db->quote($obj_id, 'integer') . PHP_EOL
            ;
            $result = $db->query($sql);

            if ($result->numRows()) {
                $values = [
                    "description" => ["clob", $desc]
                ];
                $db->update("object_description", $values, $where);
            } else {
                $values = [
                    "description" => ["clob",$desc],
                    "obj_id" => ["integer",$obj_id]
                ];
                $db->insert("object_description", $values);
            }
        }
    }

    /**
    * write import id to db (static)
    */
    final public static function _writeImportId(int $obj_id, string $import_id) : void
    {
        global $DIC;
        $db = $DIC->database();

        $values = [
            "import_id" => ["text", $import_id],
            "last_update" => ["date", $db->now()]
        ];

        $where = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db->update(self::TABLE_OBJECT_DATA, $values, $where);
    }

    final public static function _lookupType(int $id, bool $reference = false) : string
    {
        global $DIC;

        if ($reference) {
            return $DIC["ilObjDataCache"]->lookupType($DIC["ilObjDataCache"]->lookupObjId($id));
        }

        return $DIC["ilObjDataCache"]->lookupType($id);
    }

    final public static function _isInTrash(int $ref_id) : bool
    {
        global $DIC;
        return $DIC->repositoryTree()->isSaved($ref_id);
    }

    /**
    * checks whether an object has at least one reference that is not in trash
    */
    final public static function _hasUntrashedReference(int $obj_id) : bool
    {
        $ref_ids = ilObject::_getAllReferences($obj_id);
        foreach ($ref_ids as $ref_id) {
            if (!ilObject::_isInTrash($ref_id)) {
                return true;
            }
        }

        return false;
    }

    final public static function _lookupObjectId(int $ref_id) : int
    {
        global $DIC;
        return $DIC["ilObjDataCache"]->lookupObjId($ref_id);
    }

    /**
    * get all objects of a certain type
    *
    * @param string	$type desired object type
    * @param boolean $omit_trash omit objects, that are in trash only
    * @return array	of object data arrays ("id", "title", "type", "description")
    */
    final public static function _getObjectsDataForType(string $type, bool $omit_trash = false) : array
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE type = " . $db->quote($type, "text") . PHP_EOL
        ;
        $result = $db->query($sql);

        $objects = array();
        while ($row = $db->fetchAssoc($result)) {
            if ((!$omit_trash) || ilObject::_hasUntrashedReference($row["obj_id"])) {
                $objects[$row["title"] . "." . $row["obj_id"]] = [
                    "id" => $row["obj_id"],
                    "type" => $row["type"],
                    "title" => $row["title"],
                    "description" => $row["description"]
                ];
            }
        }
        ksort($objects);
        return $objects;
    }


    /**
     * maybe this method should be in tree object!?
     *
     * @todo    role/rbac stuff
     */
    public function putInTree(int $parent_ref_id) : void
    {
        $this->tree->insertNode($this->getRefId(), $parent_ref_id);

        $log_entry = sprintf(
            "ilObject::putInTree(), parent_ref: %s, ref_id: %s, obj_id: %s, type: %s, title: %s",
            $parent_ref_id,
            $this->getRefId(),
            $this->getId(),
            $this->getType(),
            $this->getTitle()
        );

        $this->log->write($log_entry);

        $this->app_event_handler->raise(
            'Services/Object',
            'putObjectInTree',
            [
                'object' => $this,
                'obj_type' => $this->getType(),
                'obj_id' => $this->getId(),
                'parent_ref_id' => $parent_ref_id
            ]
        );
    }

    public function setPermissions(int $parent_ref_id) : void
    {
        $this->setParentRolePermissions($parent_ref_id);
        $this->initDefaultRoles();
    }

    /**
     * Initialize the permissions of parent roles (local roles of categories, global roles...)
     * This method is overwritten in e.g. courses, groups for building
     * permission intersections with non_member templates.
     */
    public function setParentRolePermissions(int $parent_ref_id) : bool
    {
        $parent_roles = $this->rbac_review->getParentRoleIds($parent_ref_id);
        foreach ($parent_roles as $parent_role) {
            if ($parent_role['obj_id'] == SYSTEM_ROLE_ID) {
                continue;
            }
            $operations = $this->rbac_review->getOperationsOfRole(
                (int) $parent_role['obj_id'],
                $this->getType(),
                (int) $parent_role['parent']
            );
            $this->rbac_admin->grantPermission(
                (int) $parent_role['obj_id'],
                $operations,
                $this->getRefId()
            );
        }
        return true;
    }

    /**
    * creates reference for object
    */
    public function createReference() : int
    {
        if (!isset($this->id)) {
            $message = "ilObject::createNewReference(): No obj_id given!";
            $this->error->raiseError($message, $this->error->WARNING);
        }

        $next_id = $this->db->nextId('object_reference');

        $values = [
            "ref_id" => ["integer", $next_id],
            "obj_id" => ["integer", $this->getId()]
        ];

        $this->db->insert("object_reference", $values);

        $this->ref_id = $next_id;
        $this->referenced = true;

        return $this->ref_id;
    }

    final public function countReferences() : int
    {
        if (!isset($this->id)) {
            $message = "ilObject::countReferences(): No obj_id given!";
            $this->error->raiseError($message, $this->error->WARNING);
        }

        $sql =
            "SELECT COUNT(ref_id) num" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->id, 'integer') . PHP_EOL
        ;

        $res = $this->db->query($sql);
        $row = $this->db->fetchObject($res);

        return (int) $row->num;
    }

    /**
     * delete object or referenced object
     * (in the case of a referenced object, object data is only deleted
     * if last reference is deleted)
     * This function removes an object entirely from system!!
     *
     * @return bool true if object was removed completely; false if only a references was removed
     */
    public function delete() : bool
    {
        global $DIC;
        $rbac_admin = $DIC["rbacadmin"];

        $remove = false;

        // delete object_data entry
        if ((!$this->referenced) || ($this->countReferences() == 1)) {
            $type = ilObject::_lookupType($this->getId());
            if ($this->type != $type) {
                $log_entry = sprintf(
                    "ilObject::delete(): Type mismatch. Object with obj_id: %s was instantiated by type '%s'. DB type is: %s",
                    $this->id,
                    $this->type,
                    $type
                );
                    
                $this->log->write($log_entry);
                $this->error->raiseError(
                    sprintf("ilObject::delete(): Type mismatch. (%s/%s)", $this->type, $this->id),
                    $this->error->WARNING
                );
            }

            $this->app_event_handler->raise('Services/Object', 'beforeDeletion', ['object' => $this]);

            $sql =
                "DELETE FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->getId(), "integer") . PHP_EOL
            ;
            $this->db->manipulate($sql);

            $sql =
                "DELETE FROM object_description" . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->getId(), "integer") . PHP_EOL
            ;
            $this->db->manipulate($sql);

            $this->log->write(
                sprintf(
                    "ilObject::delete(), deleted object, obj_id: %s, type: %s, title: %s",
                    $this->getId(),
                    $this->getType(),
                    $this->getTitle()
                )
            );

            // keep log of core object data
            ilObjectDataDeletionLog::add($this);

            // remove news
            $news_item = new ilNewsItem();
            $news_item->deleteNewsOfContext($this->getId(), $this->getType());
            ilBlockSetting::_deleteSettingsOfBlock($this->getId(), "news");

            ilDidacticTemplateObjSettings::deleteByObjId($this->getId());

            // BEGIN WebDAV: Delete WebDAV properties
            $sql =
                "DELETE FROM dav_property" . PHP_EOL
                . "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . PHP_EOL
            ;
            $this->db->manipulate($sql);
            // END WebDAV: Delete WebDAV properties

            ilECSImportManager::getInstance()->_deleteByObjId($this->getId());
            ilAdvancedMDValues::_deleteByObjId($this->getId());
            ilLPObjSettings::_deleteByObjId($this->getId());

            $remove = true;
        } else {
            $this->log->write(
                sprintf(
                    "ilObject::delete(), object not deleted, number of references: %s, obj_id: %s, type: %s, title: %s",
                    $this->countReferences(),
                    $this->getId(),
                    $this->getType(),
                    $this->getTitle()
                )
            );
        }

        // delete object_reference entry
        if ($this->referenced) {
            ilObjectActivation::deleteAllEntries($this->getRefId());

            $this->app_event_handler->raise('Services/Object', 'deleteReference', ['ref_id' => $this->getRefId()]);

            $sql =
                "DELETE FROM object_reference" . PHP_EOL
                . "WHERE ref_id = " . $this->db->quote($this->getRefId(), 'integer') . PHP_EOL
            ;
            $this->db->manipulate($sql);
            
            $this->log->write(
                sprintf(
                    "ilObject::delete(), reference deleted, ref_id: %s, obj_id: %s, type: %s, title: %s",
                    $this->getRefId(),
                    $this->getId(),
                    $this->getType(),
                    $this->getTitle()
                )
            );

            // DELETE PERMISSION ENTRIES IN RBAC_PA
            // DONE: method overwritten in ilObjRole & ilObjUser.
            // this call only applies for objects in rbac (not usr,role,rolt)
            // TODO: Do this for role templates too
            $rbac_admin->revokePermission($this->getRefId(), 0, false);

            ilRbacLog::delete($this->getRefId());

            // Remove applied didactic template setting
            ilDidacticTemplateObjSettings::deleteByRefId($this->getRefId());
        }

        // remove conditions
        if ($this->referenced) {
            $ch = new ilConditionHandler();
            $ch->delete($this->getRefId());
            unset($ch);
        }

        return $remove;
    }

    /**
     * init default roles settings
     * Purpose of this function is to create a local role folder
     * and local roles, that are needed depending on the object type.
     * If you want to set up default local roles you MUST overwrite this
     * method in derived object classes (see ilObjForum for an example).
     */
    public function initDefaultRoles() : void
    {
    }
    
    public function applyDidacticTemplate(int $tpl_id) : void
    {
        ilLoggerFactory::getLogger('obj')->debug('Applying didactic template with id: ' . $tpl_id);
        if ($tpl_id) {
            foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($tpl_id) as $action) {
                $action->setRefId($this->getRefId());
                $action->apply();
            }
        }

        ilDidacticTemplateObjSettings::assignTemplate($this->getRefId(), $this->getId(), $tpl_id);
    }

    /**
     * checks if an object exists in object_data
     *
     * @param integer $id object id or reference id
     * @param bool $reference true if id is a reference, else false (default)
     * @param string|null $type string restrict on a certain type.
     * @return bool true if object exists
     */
    public static function _exists(int $id, bool $reference = false, ?string $type = null) : bool
    {
        global $DIC;
        $db = $DIC->database();
        
        if ($reference) {
            $sql =
                "SELECT object_data.obj_id" . PHP_EOL
                . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
                . "LEFT JOIN object_reference ON object_reference.obj_id = object_data.obj_id " . PHP_EOL
                . "WHERE object_reference.ref_id= " . $db->quote($id, "integer") . PHP_EOL
            ;
        } else {
            $sql =
                "SELECT object_data.obj_id" . PHP_EOL
                . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
                . "WHERE obj_id = " . $db->quote($id, "integer") . PHP_EOL
            ;
        }

        if ($type) {
            $sql .= " AND object_data.type = " . $db->quote($type, "text") . PHP_EOL;
        }
        
        $result = $db->query($sql);

        return (bool) $db->numRows($result);
    }

    public function getXMLZip() : string
    {
        return "";
    }
    public function getHTMLDirectory() : bool
    {
        return false;
    }

    final public static function _getObjectsByType(string $obj_type = "", int $owner = null) : array
    {
        global $DIC;
        $db = $DIC->database();
                
        $order = " ORDER BY title";

        $where = "";
        if ($obj_type) {
            $where = "WHERE type = " . $db->quote($obj_type, "text");
                
            if (!is_null($owner)) {
                $where .= " AND owner = " . $db->quote($owner, "integer");
            }
        }

        $sql =
            "SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . $where . PHP_EOL
            . $order . PHP_EOL
        ;
        $result = $db->query($sql);

        $arr = [];
        if ($db->numRows($result) > 0) {
            while ($row = $db->fetchAssoc($result)) {
                $row["desc"] = $row["description"];
                $arr[$row["obj_id"]] = $row;
            }
        }

        return $arr;
    }

    /**
     * Prepare copy wizard object selection
     *
     * This method should be renamed. Currently, used in ilObjSurvey and ilObjTest
     * @deprecated since version 5.2
     */
    final public static function _prepareCloneSelection(
        array $ref_ids,
        string $new_type,
        bool $show_path = true
    ) : array {
        global $DIC;

        $db = $DIC->database();
        $lng = $DIC->language();
        $obj_definition = $DIC["objDefinition"];
        
        $sql =
            "SELECT obj_data.title obj_title, path_data.title path_title, child" . PHP_EOL
            . "FROM tree " . PHP_EOL
            . "JOIN object_reference obj_ref ON child = obj_ref.ref_id " . PHP_EOL
            . "JOIN object_data obj_data ON obj_ref.obj_id = obj_data.obj_id " . PHP_EOL
            . "JOIN object_reference path_ref ON parent = path_ref.ref_id " . PHP_EOL
            . "JOIN object_data path_data ON path_ref.obj_id = path_data.obj_id " . PHP_EOL
            . "WHERE " . $db->in("child", $ref_ids, false, "integer") . PHP_EOL
            . "ORDER BY obj_data.title" . PHP_EOL
        ;
        $res = $db->query($sql);
        
        if (!$obj_definition->isPlugin($new_type)) {
            $options[0] = $lng->txt('obj_' . $new_type . '_select');
        } else {
            $options[0] = ilObjectPlugin::lookupTxtById($new_type, "obj_" . $new_type . "_select");
        }

        while ($row = $db->fetchObject($res)) {
            if (strlen($title = $row->obj_title) > 40) {
                $title = substr($title, 0, 40) . '...';
            }

            if ($show_path) {
                if (strlen($path = $row->path_title) > 40) {
                    $path = substr($path, 0, 40) . '...';
                }

                $title .= ' (' . $lng->txt('path') . ': ' . $path . ')';
            }

            $options[$row->child] = $title;
        }
        return $options ?: array();
    }

    /**
     * Clone object permissions, put in tree ...
     */
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        global $DIC;

        $ilUser = $DIC["ilUser"];
        $rbac_admin = $DIC["rbacadmin"];

        $class_name = ('ilObj' . $this->obj_definition->getClassName($this->getType()));
        
        $options = ilCopyWizardOptions::_getInstance($copy_id);

        $title = $this->getTitle();
        if (!$options->isTreeCopyDisabled() && !$omit_tree) {
            $title = $this->appendCopyInfo($target_id, $copy_id);
        }

        // create instance
        $new_obj = new $class_name(0, false);
        $new_obj->setOwner($ilUser->getId());
        $new_obj->setTitle($title);
        $new_obj->setDescription($this->getLongDescription());
        $new_obj->setType($this->getType());

        // Choose upload mode to avoid creation of additional settings, db entries ...
        $new_obj->create(true);

        if ($this->supportsOfflineHandling()) {
            $new_obj->setOffLineStatus($this->getOfflineStatus());
            $new_obj->update();
        }

        if (!$options->isTreeCopyDisabled() && !$omit_tree) {
            ilLoggerFactory::getLogger('obj')->debug('Tree copy is enabled');
            $new_obj->createReference();
            $new_obj->putInTree($target_id);
            $new_obj->setPermissions($target_id);

            // when copying from personal workspace we have no current ref id
            if ($this->getRefId()) {
                // copy local roles
                $rbac_admin->copyLocalRoles($this->getRefId(), $new_obj->getRefId());
            }
        } else {
            ilLoggerFactory::getLogger('obj')->debug('Tree copy is disabled');
        }
        
        ilAdvancedMDValues::_cloneValues($copy_id, $this->getId(), $new_obj->getId());

        // BEGIN WebDAV: Clone WebDAV properties
        $sql =
            "INSERT INTO dav_property" . PHP_EOL
            . "(obj_id, node_id, ns, name, value)" . PHP_EOL
            . "SELECT " . $this->db->quote($new_obj->getId(), 'integer') . ", node_id, ns, name, value " . PHP_EOL
            . "FROM dav_property" . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . PHP_EOL
        ;
        $this->db->manipulate($sql);
        // END WebDAV: Clone WebDAV properties

        /** @var ilObjectCustomIconFactory $customIconFactory */
        $customIconFactory = $DIC['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->getId(), $this->getType());
        $customIcon->copy($new_obj->getId());

        $tile_image = $DIC->object()->commonSettings()->tileImage()->getByObjId($this->getId());
        $tile_image->copy($new_obj->getId());

        $this->app_event_handler->raise(
            'Services/Object',
            'cloneObject',
            [
                'object' => $new_obj,
                'cloned_from_object' => $this,
            ]
        );

        return $new_obj;
    }

    /**
     * Prepend Copy info if object with same name exists in that container
     */
    final public function appendCopyInfo(int $target_id, int $copy_id) : string
    {
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);
        if (!$cp_options->isRootNode($this->getRefId())) {
            return $this->getTitle();
        }
        $nodes = $this->tree->getChilds($target_id);

        $title_unique = false;
        $numberOfCopy = 1;
        $handleExtension = ($this->getType() == "file"); // #14883
        $title = ilObjFileAccess::_appendNumberOfCopyToFilename($this->getTitle(), $numberOfCopy, $handleExtension);
        while (!$title_unique) {
            $found = 0;
            foreach ($nodes as $node) {
                if (($title == $node['title']) && ($this->getType() == $node['type'])) {
                    $found++;
                }
            }

            if ($found === 0) {
                break;
            }

            $title = ilObjFileAccess::_appendNumberOfCopyToFilename(
                $this->getTitle(),
                ++$numberOfCopy,
                $handleExtension
            );
        }

        return $title;
    }

    /**
     * Clone object dependencies
     *
     * This method allows to refresh any ref id references to other objects
     * that are affected in the same copy process. Ask ilCopyWizardOptions for
     * the mappings.
     */
    public function cloneDependencies(int $target_id, int $copy_id) : bool
    {
        ilConditionHandler::cloneDependencies($this->getRefId(), $target_id, $copy_id);

        $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
        if ($tpl_id) {
            $factory = new ilObjectFactory();
            $obj = $factory->getInstanceByRefId($target_id, false);
            if ($obj instanceof ilObject) {
                $obj->applyDidacticTemplate($tpl_id);
            }
        }
        return true;
    }

    /**
     * Copy meta data
     */
    public function cloneMetaData(ilObject $target_obj) : bool
    {
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md->cloneMD($target_obj->getId(), 0, $target_obj->getType());
        return true;
    }

    public static function getIconForReference(
        int $ref_id,
        int $obj_id,
        string $size,
        string $type = "",
        bool $offline = false
    ) : string {
        global $DIC;

        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        if ($obj_id == "" && $type == "") {
            return "";
        }

        if ($type == "") {
            $type = ilObject::_lookupType($obj_id);
        }

        if ($size == "") {
            $size = "big";
        }

        if ($obj_id && $ilSetting->get('custom_icons')) {
            /** @var ilObjectCustomIconFactory  $customIconFactory */
            $customIconFactory = $DIC['object.customicons.factory'];
            $customIcon = $customIconFactory->getPresenterByObjId($obj_id, $type);
            if ($customIcon->exists()) {
                $filename = $customIcon->getFullPath();
                return $filename . '?tmp=' . filemtime($filename);
            }
        }

        if ($obj_id) {
            $dtpl_icon_factory = ilDidacticTemplateIconFactory::getInstance();
            if ($ref_id) {
                $path = $dtpl_icon_factory->getIconPathForReference($ref_id);
            } else {
                $path = $dtpl_icon_factory->getIconPathForObject($obj_id);
            }
            if ($path) {
                return $path . '?tmp=' . filemtime($path);
            }
        }

        if (!$offline) {
            if ($objDefinition->isPluginTypeName($type)) {
                if ($objDefinition->getClassName($type) != "") {
                    $class_name = "il" . $objDefinition->getClassName($type) . 'Plugin';
                    $location = $objDefinition->getLocation($type);
                    if (is_file($location . "/class." . $class_name . ".php")) {
                        return call_user_func(array($class_name, "_getIcon"), $type, $size, $obj_id);
                    }
                }
                return ilUtil::getImagePath("icon_cmps.svg");
            }

            return ilUtil::getImagePath("icon_" . $type . ".svg");
        } else {
            return "./images/icon_" . $type . ".svg";
        }
    }

    /**
     * Get icon for repository item.
     *
     * @param int object id
     * @param string size (big, small, tiny)
     * @param string object type
     * @param bool true: offline, false: online
     */
    final public static function _getIcon(
        int $obj_id = 0,
        string $size = "big",
        string $type = "",
        bool $offline = false
    ) : string {
        return self::getIconForReference(0, $obj_id, $size, $type, $offline);
    }

    /**
     * Collect deletion dependencies. E.g.
     */
    public static function collectDeletionDependencies(
        array &$deps,
        int $ref_id,
        int $obj_id,
        string $type,
        int $depth = 0
    ) : void {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        if ($depth == 0) {
            $deps["dep"] = array();
        }
        
        $deps["del_ids"][$obj_id] = $obj_id;
        
        if (!$objDefinition->isPluginTypeName($type)) {
            $class_name = "ilObj" . $objDefinition->getClassName($type);
            $odeps = call_user_func(array($class_name, "getDeletionDependencies"), $obj_id);
            if (is_array($odeps)) {
                foreach ($odeps as $id => $message) {
                    $deps["dep"][$id][$obj_id][] = $message;
                }
            }
            
            // get deletion dependency of children
            foreach ($tree->getChilds($ref_id) as $c) {
                ilObject::collectDeletionDependencies($deps, (int) $c["child"], (int) $c["obj_id"], (string) $c["type"], $depth + 1);
            }
        }

        // delete all dependencies to objects that will be deleted, too
        if ($depth == 0) {
            foreach ($deps["del_ids"] as $obj_id) {
                unset($deps["dep"][$obj_id]);
            }
            $deps = $deps["dep"];
        }
    }

    /**
     * Get deletion dependencies
     */
    public static function getDeletionDependencies(int $obj_id) : array
    {
        return [];
    }
    
    public static function getLongDescriptions(array $obj_ids) : array
    {
        global $DIC;
        $db = $DIC->database();
        
        $sql =
            "SELECT obj_id, description" . PHP_EOL
            . "FROM object_description" . PHP_EOL
            . "WHERE " . $db->in("obj_id", $obj_ids, false, "integer") . PHP_EOL
        ;
        $result = $db->query($sql);

        $all = array();
        while ($row = $db->fetchAssoc($result)) {
            $all[$row["obj_id"]] = $row["description"];
        }
        return $all;
    }
    
    public static function getAllOwnedRepositoryObjects(int $user_id) : array
    {
        global $DIC;

        $db = $DIC->database();
        $obj_definition = $DIC["objDefinition"];

        // restrict to repository
        $types = array_keys($obj_definition->getSubObjectsRecursively("root"));
            
        $sql =
            "SELECT od.obj_id, od.type, od.title" . PHP_EOL
            . "FROM object_data od" . PHP_EOL
            . "JOIN object_reference oref ON(oref.obj_id = od.obj_id)" . PHP_EOL
            . "JOIN tree ON (tree.child = oref.ref_id)" . PHP_EOL
        ;

        if ($user_id) {
            $sql .= "WHERE od.owner = " . $db->quote($user_id, "integer") . PHP_EOL;
        } else {
            $sql .=
                "LEFT JOIN usr_data ud ON (ud.usr_id = od.owner)" . PHP_EOL
                . "WHERE (od.owner < " . $db->quote(1, "integer") . PHP_EOL
                . "OR od.owner IS NULL OR ud.login IS NULL)" . PHP_EOL
                . "AND od.owner <> " . $db->quote(-1, "integer") . PHP_EOL
            ;
        }
        
        $sql .=
            "AND " . $db->in("od.type", $types, false, "text") . PHP_EOL
            . "AND tree.tree > " . $db->quote(0, "integer") . PHP_EOL
        ;
            
        $res = $db->query($sql);

        $all = array();
        while ($row = $db->fetchAssoc($res)) {
            $all[$row["type"]][$row["obj_id"]] = $row["title"];
        }

        return $all;
    }

    /**
     * Try to fix missing object titles
     */
    public static function fixMissingTitles($type, array &$obj_title_map)
    {
        global $DIC;
        $db = $DIC->database();
        
        if (!in_array($type, array("catr", "crsr", "sess", "grpr", "prgr"))) {
            return;
        }

        // any missing titles?
        $missing_obj_ids = array();
        foreach ($obj_title_map as $obj_id => $title) {
            if (!trim($title)) {
                $missing_obj_ids[] = $obj_id;
            }
        }

        if (!sizeof($missing_obj_ids)) {
            return;
        }
        
        switch ($type) {
            case "grpr":
            case "catr":
            case "crsr":
            case "prgr":
                $sql =
                    "SELECT oref.obj_id, od.type, od.title" . PHP_EOL
                    . "FROM object_data od" . PHP_EOL
                    . "JOIN container_reference oref ON (od.obj_id = oref.target_obj_id)" . PHP_EOL
                    . "AND " . $db->in("oref.obj_id", $missing_obj_ids, false, "integer") . PHP_EOL
                ;
                $result = $db->query($sql);

                while ($row = $db->fetchAssoc($result)) {
                    $obj_title_map[$row["obj_id"]] = $row["title"];
                }
                break;
            case "sess":
                foreach ($missing_obj_ids as $obj_id) {
                    $sess = new ilObjSession($obj_id, false);
                    $obj_title_map[$obj_id] = $sess->getFirstAppointment()->appointmentToString();
                }
                break;
        }
    }
    
    public static function _lookupCreationDate(int $obj_id) : string
    {
        global $DIC;
        $db = $DIC->database();
        
        $sql =
            "SELECT create_date" . PHP_EOL
            . "FROM " . self::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE obj_id = " . $db->quote($obj_id, "integer") . PHP_EOL
        ;
        $result = $db->query($sql);
        $rec = $db->fetchAssoc($result);
        return $rec["create_date"];
    }

    /**
     * Check if auto rating is active for parent group/course
     */
    public static function hasAutoRating(string $type, int $ref_id) : bool
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        
        if (!$ref_id || !in_array($type, array("file", "lm", "wiki"))) {
            return false;
        }

        $parent_ref_id = $tree->checkForParentType($ref_id, "grp");
        if (!$parent_ref_id) {
            $parent_ref_id = $tree->checkForParentType($ref_id, "crs");
        }
        if ($parent_ref_id) {
            // get auto rate setting
            $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
            return (bool) ilContainer::_lookupContainerSetting(
                $parent_obj_id,
                ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS
            );
        }
        return false;
    }

    /**
    * get all possible sub objects of this type
    * the object can decide which types of sub objects are possible jut in time
    * overwrite if the decision distinguish from standard model
    *
    * @param bool filter disabled objects? ($a_filter = true)
    * @return array list of allowed object types
    */
    public function getPossibleSubObjects(bool $filter = true) : array
    {
        return $this->obj_definition->getSubObjects($this->type, $filter);
    }

    public static function _getObjectTypeIdByTitle(string $type, \ilDBInterface $ilDB = null) : ?int
    {
        if (!$ilDB) {
            global $DIC;
            $ilDB = $DIC->database();
        }

        $sql =
            "SELECT obj_id FROM object_data" . PHP_EOL
            . "WHERE type = 'typ'" . PHP_EOL
            . "AND title = " . $ilDB->quote($type, 'text') . PHP_EOL
        ;

        $res = $ilDB->query($sql);
        if ($ilDB->numRows($res) == 0) {
            return null;
        }

        $row = $ilDB->fetchAssoc($res);
        return (int) $row['obj_id'] ?? null;
    }
} // END class.ilObject
