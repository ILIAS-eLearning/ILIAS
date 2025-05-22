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

use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\DI\Container;
use ILIAS\Services\Badge\BadgeException;

class ilBadge
{
    private ilLogger $log;
    protected ilDBInterface $db;
    protected int $id = 0;
    protected int $parent_id = 0;
    protected string $type_id = "";
    protected bool $active = false;
    protected string $title = "";
    protected string $desc = "";
    protected string $image = "";
    protected ?string $image_rid = null;
    protected string $valid = "";
    protected ?array $config = null;
    protected string $criteria = "";

    private ?Services $resource_storage;

    public function __construct(
        ?int $a_id = null,
        ?Container $container = null
    ) {

        if ($container === null) {
            global $DIC;
            $container = $DIC;
        }

        $this->db = $container->database();
        $this->resource_storage = $container->resourceStorage();
        $this->log = $container->logger()->root();
        if ($a_id) {
            $this->read($a_id);
        }
    }

    /**
     * @param array|null $a_filter
     * @return self[]
     */
    public static function getInstancesByParentId(
        int $a_parent_id,
        ?array $a_filter = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];

        $sql = "SELECT * FROM badge_badge" .
            " WHERE parent_id = " . $ilDB->quote($a_parent_id);

        if ($a_filter) {
            if ($a_filter["title"] ?? false) {
                $sql .= " AND " . $ilDB->like("title", "text", "%" . trim($a_filter["title"]) . "%");
            }
            if ($a_filter["type"] ?? false) {
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

    /**
     * @return self[]
     */
    public static function getInstancesByType(
        string $a_type_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];

        $set = $ilDB->query("SELECT * FROM badge_badge" .
            " WHERE type_id = " . $ilDB->quote($a_type_id, "text") .
            " ORDER BY title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }

        return $res;
    }

    public function clone(int $target_parent_obj_id): void
    {
        $this->setParentId($target_parent_obj_id);
        $this->setActive(false);

        if ($this->getId()) {
            if ($this->getImageRid()) {
                $current_collection_id = new ResourceIdentification($this->getImageRid());
                $new_collection_id = $this->resource_storage->manage()->clone($current_collection_id);
                $this->setId(0);
                $this->setImageRid($new_collection_id);
                $this->create();
            } else {
                $this->log->warning('Please run the "Migration of files of badges to the resource storage service" job, before working with badges.');
            }
        }
    }

    public function getTypeInstance(): ?ilBadgeType
    {
        if ($this->getTypeId()) {
            $handler = ilBadgeHandler::getInstance();
            return $handler->getTypeInstanceByUniqueId($this->getTypeId());
        }
        return null;
    }

    public function copy(
        int $a_new_parent_id,
        string $copy_suffix
    ): void {
        $this->setTitle($this->getTitle() . " " . $copy_suffix);
        $this->setParentId($a_new_parent_id);
        $this->setActive(false);

        if ($this->getId()) {
            $this->setId(0);
            $old_rid = $this->getImageRid();
            $this->create();
            if ($old_rid !== null) {
                $new_rid = $this->resource_storage->manage()->clone(new ResourceIdentification($old_rid));
                $this->setImageRid($new_rid);
                $this->update();
            }
        }
    }

    /**
     * @param array{type: string, title: string, object: string}|null $filter
     * @return list<array{
     *     id: int,
     *     parent_id: int,
     *     type_id: string,
     *     active: int,
     *     title: ?string,
     *     descr: ?string,
     *     conf: ?string,
     *     image: ?string,
     *     valid: ?string,
     *     crit: ?string,
     *     image_rid: ?string,
     *     parent_title: ?string,
     *     parent_type: ?string,
     *     deleted: bool
     * }>
     */
    public static function getObjectInstances(
        ?array $filter = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $rows = [];

        $where = '';

        if ($filter['type']) {
            $where .= ' AND bb.type_id = ' . $ilDB->quote($filter['type'], ilDBConstants::T_TEXT);
        }
        if ($filter['title']) {
            $where .= ' AND ' . $ilDB->like('bb.title', ilDBConstants::T_TEXT, '%' . $filter['title'] . '%');
        }
        if ($filter['object']) {
            $where .= ' AND ' . $ilDB->like('od.title', ilDBConstants::T_TEXT, '%' . $filter['object'] . '%');
        }

        $set = $ilDB->query('SELECT bb.*, od.title parent_title, od.type parent_type' .
            ' FROM badge_badge bb' .
            ' INNER JOIN object_data od ON bb.parent_id = od.obj_id' .
            ' WHERE od.type != ' . $ilDB->quote('bdga', ilDBConstants::T_TEXT) .
            $where);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row['deleted'] = false;
            $rows[] = $row;
        }

        $set = $ilDB->query('SELECT bb.*, od.title parent_title, od.type parent_type' .
            ' FROM badge_badge bb' .
            ' INNER JOIN object_data_del od ON bb.parent_id = od.obj_id' .
            ' WHERE od.type != ' . $ilDB->quote('bdga', ilDBConstants::T_TEXT) .
            $where);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row['deleted'] = true;
            $rows[] = $row;
        }

        return $rows;
    }

    //
    // setter/getter
    //

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentId(int $a_id): void
    {
        $this->parent_id = $a_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setTypeId(string $a_id): void
    {
        $this->type_id = trim($a_id);
    }

    public function getTypeId(): string
    {
        return $this->type_id;
    }

    public function setActive(bool $a_value): void
    {
        $this->active = $a_value;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setTitle(string $a_value): void
    {
        $this->title = trim($a_value);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_value): void
    {
        $this->desc = trim($a_value);
    }

    public function getDescription(): string
    {
        return $this->desc;
    }

    public function setCriteria(string $a_value): void
    {
        $this->criteria = trim($a_value);
    }

    public function getCriteria(): string
    {
        return $this->criteria;
    }

    public function setValid(string $a_value): void
    {
        $this->valid = trim($a_value);
    }

    public function getValid(): string
    {
        return $this->valid;
    }

    public function setConfiguration(?array $a_value = null): void
    {
        if (is_array($a_value) && !count($a_value)) {
            $a_value = null;
        }
        $this->config = $a_value;
    }

    public function getConfiguration(): ?array
    {
        return $this->config;
    }

    public function setImage(?string $a_value): void
    {
        if ($a_value !== null) {
            $this->image = trim($a_value);
        }
    }

    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @throws BadgeException
     */
    public function uploadImage(
        array $a_upload_meta
    ): void {
        if ($this->getId() &&
            $a_upload_meta["tmp_name"]) {
            $this->setImage($a_upload_meta["name"]);
            $path = $this->getImagePath();

            try {
                if (ilFileUtils::moveUploadedFile($a_upload_meta['tmp_name'], $this->getImagePath(false), $path)) {
                    $this->update();
                }
            } catch (ilException $e) {
                throw BadgeException::moveUploadedBadgeImageFailed($this, $e);
            }

        }
    }

    /**
     * @throws BadgeException
     */
    public function importImage(
        string $a_name,
        string $a_file
    ): void {
        if (file_exists($a_file)) {
            $this->setImage($a_name);
            copy($a_file, $this->getImagePath()); // #18280

            $this->update();
        } else {
            throw BadgeException::uploadedBadgeImageFileNotFound($this);
        }
    }

    public function getImagePath(
        bool $a_full_path = true
    ): string {
        if ($this->getId()) {
            $exp = explode(".", $this->getImage());
            $suffix = strtolower(array_pop($exp));
            if ($a_full_path) {
                return $this->getFilePath($this->getId()) . "img" . $this->getId() . "." . $suffix;
            }

            return "img" . $this->getId() . "." . $suffix;
        }

        return "";
    }

    protected function getFilePath(
        int $a_id,
        ?string $a_subdir = null
    ): string {
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

    protected function read(int $a_id): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM badge_badge" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $this->importDBRow($row);
        }
    }

    protected function importDBRow(
        array $a_row
    ): void {
        $this->setId($a_row["id"]);
        $this->setParentId($a_row["parent_id"]);
        $this->setTypeId($a_row["type_id"]);
        $this->setActive($a_row["active"]);
        $this->setTitle($a_row["title"]);
        $this->setDescription($a_row["descr"]);
        $this->setCriteria($a_row["crit"]);
        $this->setImage($a_row["image"]);
        $this->setImageRid($a_row["image_rid"]);
        $this->setValid($a_row["valid"]);
        $this->setConfiguration($a_row["conf"]
            ? unserialize($a_row["conf"], ["allowed_classes" => false])
            : null);
    }

    public function create(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $this->update();
            return;
        }

        $id = $ilDB->nextId("badge_badge");
        $this->setId($id);

        $fields = $this->getPropertiesForStorage();

        $fields["id"] = ["integer", $id];
        $fields["parent_id"] = ["integer", $this->getParentId()];
        $fields["type_id"] = ["text", $this->getTypeId()];

        $ilDB->insert("badge_badge", $fields);
    }

    public function update(): void
    {
        $ilDB = $this->db;

        if (!$this->getId()) {
            $this->create();
            return;
        }

        $fields = $this->getPropertiesForStorage();

        $ilDB->update(
            "badge_badge",
            $fields,
            ["id" => ["integer", $this->getId()]]
        );
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        if (!$this->getId()) {
            return;
        }

        if ($this->getImageRid()) {
            try {
                $this->resource_storage->manage()->remove(new ResourceIdentification($this->getImageRid()), new ilBadgeFileStakeholder());
            } catch (Exception $e) {
                $this->log->warning(sprintf('There was an exception, while deleting the badge with id %s. Exception: %s', $this->getId(), $e->getMessage()));
            }
        }

        $this->deleteStaticFiles();

        ilBadgeAssignment::deleteByBadgeId($this->getId());

        $ilDB->manipulate("DELETE FROM badge_badge" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
    }

    /**
     * @return array<string, array>
     */
    protected function getPropertiesForStorage(): array
    {
        return [
            "active" => ["integer", $this->isActive()],
            "title" => ["text", $this->getTitle()],
            "descr" => ["text", $this->getDescription()],
            "crit" => ["text", $this->getCriteria()],
            "image" => ["text", $this->getImage()],
            "image_rid" => ["text", $this->getImageRid()],
            "valid" => ["text", $this->getValid()],
            "conf" => [
                "text", $this->getConfiguration() ? serialize($this->getConfiguration()) : null
            ]
        ];
    }


    //
    // helper
    //

    /**
     * @return array{id: int, type: string, title: string, deleted: bool}
     */
    public function getParentMeta(): array
    {
        $parent_type = ilObject::_lookupType($this->getParentId());
        $parent_title = "";
        if ($parent_type) {
            $parent_title = ilObject::_lookupTitle($this->getParentId());
            $deleted = false;
        } else {
            // already deleted?
            $parent = ilObjectDataDeletionLog::get($this->getParentId());
            if ($parent["type"]) {
                $parent_type = $parent["type"];
                $parent_title = $parent["title"];
            }
            $deleted = true;
        }

        return [
            "id" => $this->getParentId(),
            "type" => $parent_type,
            "title" => $parent_title,
            "deleted" => $deleted
        ];
    }


    //
    // PUBLISHING
    //

    protected function prepareJson(
        string $a_base_url,
        string $a_img_suffix
    ): stdClass {
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


    public function deleteStaticFiles(): void
    {
        // remove instance files
        $path = ilBadgeHandler::getInstance()->getBadgePath($this);
        if (is_dir($path)) {
            ilFileUtils::delDir($path);
        }
    }

    public static function getExtendedTypeCaption(
        ilBadgeType $a_type
    ): string {
        global $DIC;

        $lng = $DIC->language();

        return $a_type->getCaption() . " (" .
            ($a_type instanceof ilBadgeAuto
                ? $lng->txt("badge_subtype_auto")
                : $lng->txt("badge_subtype_manual")) . ")";
    }

    public function getImageRid(): ?string
    {
        return $this->image_rid;
    }

    public function setImageRid(?string $image_rid): void
    {
        $this->image_rid = $image_rid;
    }
}
