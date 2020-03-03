<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadge
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadge
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDB
     */
    protected $db;

    protected $id; // [int]
    protected $parent_id; // [int]
    protected $type_id; // [string]
    protected $active; // [bool]
    protected $title; // [string]
    protected $desc; // [string]
    protected $image; // [string]
    protected $valid; // [string]
    protected $config; // [array]
    protected $criteria; // [string]
    
    /**
     * Constructor
     *
     * @param int $a_id
     * @return self
     */
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        if ($a_id) {
            $this->read($a_id);
        }
    }
    
    public static function getInstancesByParentId($a_parent_id, array $a_filter = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT * FROM badge_badge" .
            " WHERE parent_id = " . $ilDB->quote($a_parent_id);
        
        if ($a_filter) {
            if ($a_filter["title"]) {
                $sql .= " AND " . $ilDB->like("title", "text", "%" . trim($a_filter["title"]) . "%");
            }
            if ($a_filter["type"]) {
                $sql .= " AND type_id = " . $ilDB->quote($a_filter["type"], "integer");
            }
        }
        
        $set = $ilDB->query($sql .
            " ORDER BY title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }
                
        return $res;
    }
    
    public static function getInstancesByType($a_type_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $set = $ilDB->query("SELECT * FROM badge_badge" .
            " WHERE type_id = " . $ilDB->quote($a_type_id) .
            " ORDER BY title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }
                
        return $res;
    }
    
    public function getTypeInstance()
    {
        if ($this->getTypeId()) {
            include_once "./Services/Badge/classes/class.ilBadgeHandler.php";
            $handler = ilBadgeHandler::getInstance();
            return $handler->getTypeInstanceByUniqueId($this->getTypeId());
        }
    }
    
    public function copy($a_new_parent_id)
    {
        $lng = $this->lng;
        
        $this->setTitle($this->getTitle() . " " . $lng->txt("copy_of_suffix"));
        $this->setParentId($a_new_parent_id);
        $this->setActive(false);
    
        if ($this->getId()) {
            $img = $this->getImagePath();
            
            $this->setId(null);
            $this->create();

            if ($img) {
                // see uploadImage()
                copy($img, $this->getImagePath());
            }
        }
    }
    
    public static function getObjectInstances(array $a_filter = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = $raw = array();
        
        $where = "";
        
        if ($a_filter["type"]) {
            $where .= " AND bb.type_id = " . $ilDB->quote($a_filter["type"], "text");
        }
        if ($a_filter["title"]) {
            $where .= " AND " . $ilDB->like("bb.title", "text", "%" . $a_filter["title"] . "%");
        }
        if ($a_filter["object"]) {
            $where .= " AND " . $ilDB->like("od.title", "text", "%" . $a_filter["object"] . "%");
        }
        
        $set = $ilDB->query("SELECT bb.*, od.title parent_title, od.type parent_type" .
            " FROM badge_badge bb" .
            " JOIN object_data od ON (bb.parent_id = od.obj_id)" .
            " WHERE od.type <> " . $ilDB->quote("bdga", "text") .
            $where);
        while ($row = $ilDB->fetchAssoc($set)) {
            $raw[] = $row;
        }
        
        $set = $ilDB->query("SELECT bb.*, od.title parent_title, od.type parent_type" .
            " FROM badge_badge bb" .
            " JOIN object_data_del od ON (bb.parent_id = od.obj_id)" .
            " WHERE od.type <> " . $ilDB->quote("bdga", "text") .
            $where);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["deleted"] = true;
            $raw[] = $row;
        }
        
        foreach ($raw as $row) {
            // :TODO:
            
            $res[] = $row;
        }
        
        return $res;
    }
    
    
    //
    // setter/getter
    //
    
    protected function setId($a_id)
    {
        $this->id = (int) $a_id;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setParentId($a_id)
    {
        $this->parent_id = (int) $a_id;
    }
    
    public function getParentId()
    {
        return $this->parent_id;
    }
    
    public function setTypeId($a_id)
    {
        $this->type_id = trim($a_id);
    }
    
    public function getTypeId()
    {
        return $this->type_id;
    }
    
    public function setActive($a_value)
    {
        $this->active = (bool) $a_value;
    }
    
    public function isActive()
    {
        return $this->active;
    }
    
    public function setTitle($a_value)
    {
        $this->title = trim($a_value);
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setDescription($a_value)
    {
        $this->desc = trim($a_value);
    }
    
    public function getDescription()
    {
        return $this->desc;
    }
    
    public function setCriteria($a_value)
    {
        $this->criteria = trim($a_value);
    }
    
    public function getCriteria()
    {
        return $this->criteria;
    }
    
    public function setValid($a_value)
    {
        $this->valid = trim($a_value);
    }
    
    public function getValid()
    {
        return $this->valid;
    }
    
    public function setConfiguration(array $a_value = null)
    {
        if (is_array($a_value) &&
            !sizeof($a_value)) {
            $a_value = null;
        }
        $this->config = $a_value;
    }
    
    public function getConfiguration()
    {
        return $this->config;
    }
    
    protected function setImage($a_value)
    {
        $this->image = trim($a_value);
    }
    
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param array $a_upload_meta
     * @throws ilFileUtilsException
     */
    public function uploadImage(array $a_upload_meta)
    {
        if ($this->getId() &&
            $a_upload_meta["tmp_name"]) {
            $this->setImage($a_upload_meta["name"]);
            $path = $this->getImagePath();

            if (ilUtil::moveUploadedFile($a_upload_meta["tmp_name"], $this->getImagePath(false), $path)) {
                $this->update();
            }
        }
    }
    
    public function importImage($a_name, $a_file)
    {
        if (file_exists($a_file)) {
            $this->setImage($a_name);
            copy($a_file, $this->getImagePath()); // #18280
            
            $this->update();
        }
    }
    
    public function getImagePath($a_full_path = true)
    {
        if ($this->getId()) {
            $suffix = strtolower(array_pop(explode(".", $this->getImage())));
            if ($a_full_path) {
                return $this->getFilePath($this->getId()) . "img" . $this->getId() . "." . $suffix;
            } else {
                return "img" . $this->getId() . "." . $suffix;
            }
        }
    }
    
    /**
     * Init file system storage
     *
     * @param type $a_id
     * @param type $a_subdir
     * @return string
     */
    protected function getFilePath($a_id, $a_subdir = null)
    {
        include_once "Services/Badge/classes/class.ilFSStorageBadge.php";
        $storage = new ilFSStorageBadge($a_id);
        $storage->create();
        
        $path = $storage->getAbsolutePath() . "/";
        
        if ($a_subdir) {
            $path .= $a_subdir . "/";
            
            if (!is_dir($path)) {
                mkdir($path);
            }
        }
                
        return $path;
    }
    
    
    //
    // crud
    //
    
    protected function read($a_id)
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM badge_badge" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $this->importDBRow($row);
        }
    }
    
    protected function importDBRow(array $a_row)
    {
        $this->setId($a_row["id"]);
        $this->setParentId($a_row["parent_id"]);
        $this->setTypeId($a_row["type_id"]);
        $this->setActive($a_row["active"]);
        $this->setTitle($a_row["title"]);
        $this->setDescription($a_row["descr"]);
        $this->setCriteria($a_row["crit"]);
        $this->setImage($a_row["image"]);
        $this->setValid($a_row["valid"]);
        $this->setConfiguration($a_row["conf"]
                ? unserialize($a_row["conf"])
                : null);
    }
    
    public function create()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            return $this->update();
        }
        
        $id = $ilDB->nextId("badge_badge");
        $this->setId($id);
        
        $fields = $this->getPropertiesForStorage();
            
        $fields["id"] = array("integer", $id);
        $fields["parent_id"] = array("integer", $this->getParentId());
        $fields["type_id"] = array("text", $this->getTypeId());
        
        $ilDB->insert("badge_badge", $fields);
    }
    
    public function update()
    {
        $ilDB = $this->db;
        
        if (!$this->getId()) {
            return $this->create();
        }
        
        $fields = $this->getPropertiesForStorage();
        
        $ilDB->update(
            "badge_badge",
            $fields,
            array("id"=>array("integer", $this->getId()))
        );
    }
    
    public function delete()
    {
        $ilDB = $this->db;
        
        if (!$this->getId()) {
            return;
        }
        
        if (file_exists($this->getImagePath())) {
            unlink($this->getImagePath());
        }
        
        $this->deleteStaticFiles();
        
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        ilBadgeAssignment::deleteByBadgeId($this->getId());
        
        $ilDB->manipulate("DELETE FROM badge_badge" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
    }
    
    protected function getPropertiesForStorage()
    {
        return array(
            "active" => array("integer", $this->isActive()),
            "title" => array("text", $this->getTitle()),
            "descr" => array("text", $this->getDescription()),
            "crit" => array("text", $this->getCriteria()),
            "image" => array("text", $this->getImage()),
            "valid" => array("text", $this->getValid()),
            "conf" => array("text", $this->getConfiguration()
                ? serialize($this->getConfiguration())
                : null)
        );
    }
    
    
    //
    // helper
    //
    
    public function getParentMeta()
    {
        $parent_type = ilObject::_lookupType($this->getParentId());
        if ($parent_type) {
            $parent_title = ilObject::_lookupTitle($this->getParentId());
            $deleted = false;
        } else {
            // already deleted?
            include_once "Services/Object/classes/class.ilObjectDataDeletionLog.php";
            $parent = ilObjectDataDeletionLog::get($this->getParentId());
            if ($parent["type"]) {
                $parent_type = $parent["type"];
                $parent_title = $parent["title"];
            }
            $deleted = true;
        }
        
        return array(
            "id" => $this->getParentId(),
            "type" => $parent_type,
            "title" => $parent_title,
            "deleted" => $deleted
        );
    }
    
    
    //
    // PUBLISHING
    //
    
    protected function prepareJson($a_base_url, $a_img_suffix)
    {
        $json = new stdClass();
        $json->{"@context"} = "https://w3id.org/openbadges/v1";
        $json->type = "BadgeClass";
        $json->id = $a_base_url . "class.json";
        $json->name = $this->getTitle();
        $json->description = $this->getDescription();
        $json->image = $a_base_url . "image." . $a_img_suffix;
        $json->criteria = $a_base_url . "criteria.txt";
        $json->issuer = ilBadgeHandler::getInstance()->getIssuerStaticUrl();
        
        return $json;
    }
    
    public function getStaticUrl()
    {
        $path = ilBadgeHandler::getInstance()->getBadgePath($this);
        
        $base_url = ILIAS_HTTP_PATH . substr($path, 1);
        
        if (!file_exists($path . "class.json")) {
            $img_suffix = array_pop(explode(".", $this->getImage()));
            
            $json = json_encode($this->prepareJson($base_url, $img_suffix));
            file_put_contents($path . "class.json", $json);
            
            // :TODO: scale?
            copy($this->getImagePath(), $path . "image." . $img_suffix);
            
            file_put_contents($path . "criteria.txt", $this->getCriteria());
        }
        
        return $base_url . "class.json";
    }

    public function deleteStaticFiles()
    {
        // remove instance files
        $path = ilBadgeHandler::getInstance()->getBadgePath($this);
        if (is_dir($path)) {
            ilUtil::delDir($path);
        }
    }
    
    public static function getExtendedTypeCaption(ilBadgeType $a_type)
    {
        global $DIC;

        $lng = $DIC->language();
        
        return $a_type->getCaption() . " (" .
            ($a_type instanceof ilBadgeAuto
                ? $lng->txt("badge_subtype_auto")
                : $lng->txt("badge_subtype_manual")) . ")";
    }
}
