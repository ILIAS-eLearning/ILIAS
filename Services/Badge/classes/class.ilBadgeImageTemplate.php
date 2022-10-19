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

/**
 * Badge Template
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeImageTemplate
{
    protected ilDBInterface $db;
    protected int $id = 0;
    protected string $title = "";
    protected string $image = "";
    /** @var string[] */
    protected ?array $types = null;

    public function __construct(int $a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id) {
            $this->read($a_id);
        }
    }

    /**
     * @return self[]
     */
    public static function getInstances(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $types = array();
        $set = $ilDB->query("SELECT * FROM badge_image_templ_type");
        while ($row = $ilDB->fetchAssoc($set)) {
            $types[$row["tmpl_id"]][] = $row["type_id"];
        }

        $set = $ilDB->query("SELECT * FROM badge_image_template" .
            " ORDER BY title");
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["types"] = (array) ($types[$row["id"]] ?? null);

            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }

        return $res;
    }

    /**
     * @param string $a_type_unique_id
     * @return self[]
     */
    public static function getInstancesByType(string $a_type_unique_id): array
    {
        $res = [];

        foreach (self::getInstances() as $tmpl) {
            if (!count($tmpl->getTypes()) || in_array($a_type_unique_id, $tmpl->getTypes(), true)) {
                $res[] = $tmpl;
            }
        }

        return $res;
    }


    //
    // setter/getter
    //

    protected function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $a_value): void
    {
        $this->title = trim($a_value);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    protected function setImage(string $a_value): void
    {
        $this->image = trim($a_value);
    }

    /**
     * @return string[]
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setTypes(array $types = null): void
    {
        $this->types = is_array($types)
            ? array_unique($types)
            : null;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function uploadImage(array $a_upload_meta): void
    {
        if ($this->getId() &&
            $a_upload_meta["tmp_name"]) {
            $path = $this->getFilePath($this->getId());


            $filename = ilFileUtils::getValidFilename($a_upload_meta["name"]);

            $exp = explode(".", $filename);
            $suffix = strtolower(array_pop($exp));
            $tgt = $path . "img" . $this->getId() . "." . $suffix;

            if (ilFileUtils::moveUploadedFile($a_upload_meta["tmp_name"], "img" . $this->getId() . "." . $suffix, $tgt)) {
                $this->setImage($filename);
                $this->update();
            }
        }
    }

    public function getImagePath(): string
    {
        if ($this->getId()) {
            if (is_file($this->getFilePath($this->getId()) . "img" . $this->getId())) {	// formerly (early 5.2 versino), images have been uploaded with no suffix
                return $this->getFilePath($this->getId()) . "img" . $this->getId();
            }

            $exp = explode(".", $this->getImage());
            $suffix = strtolower(array_pop($exp));
            return $this->getFilePath($this->getId()) . "img" . $this->getId() . "." . $suffix;
        }
        return "";
    }

    /**
     * Init file system storage
     */
    protected function getFilePath(
        int $a_id,
        string $a_subdir = null
    ): string {
        $storage = new ilFSStorageBadgeImageTemplate($a_id);
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

        $set = $ilDB->query("SELECT * FROM badge_image_template" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $row["types"] = $this->readTypes($a_id);
            $this->importDBRow($row);
        }
    }

    protected function readTypes(int $a_id): ?array
    {
        $ilDB = $this->db;

        $res = array();

        $set = $ilDB->query("SELECT * FROM badge_image_templ_type WHERE tmpl_id = " . $ilDB->quote($a_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["type_id"];
        }

        if (!count($res)) {
            $res = null;
        }

        return $res;
    }

    protected function importDBRow(array $a_row): void
    {
        $this->setId($a_row["id"]);
        $this->setTitle($a_row["title"]);
        $this->setImage($a_row["image"]);
        $this->setTypes($a_row["types"]);
    }

    public function create(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $this->update();
            return;
        }

        $id = $ilDB->nextId("badge_image_template");
        $this->setId($id);

        $fields = $this->getPropertiesForStorage();
        $fields["id"] = array("integer", $id);

        $ilDB->insert("badge_image_template", $fields);

        $this->saveTypes();
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
            "badge_image_template",
            $fields,
            array("id" => array("integer", $this->getId()))
        );

        $this->saveTypes();
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        if (!$this->getId()) {
            return;
        }

        $path = $this->getFilePath($this->getId());
        ilFileUtils::delDir($path);

        $ilDB->manipulate("DELETE FROM badge_image_template" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
    }

    /**
     * @return array<string, array>
     */
    protected function getPropertiesForStorage(): array
    {
        return [
            "title" => ["text", $this->getTitle()],
            "image" => ["text", $this->getImage()]
        ];
    }

    protected function saveTypes(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $ilDB->manipulate("DELETE FROM badge_image_templ_type" .
                " WHERE tmpl_id = " . $ilDB->quote($this->getId(), "integer"));

            if ($this->getTypes()) {
                foreach ($this->getTypes() as $type) {
                    $fields = array(
                        "tmpl_id" => array("integer", $this->getId()),
                        "type_id" => array("text", $type)
                    );
                    $ilDB->insert("badge_image_templ_type", $fields);
                }
            }
        }
    }
}
