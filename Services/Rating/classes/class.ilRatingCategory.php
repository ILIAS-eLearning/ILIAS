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
 * Class ilRatingCategory
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingCategory
{
    protected \ilDBInterface $db;
    protected int $id = 0; // sequence
    protected int $parent_id = 0; // parent object
    protected string $title = "";
    protected string $description = "";
    protected int $pos = 0; // order


    public function __construct(
        int $a_id = null,
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;

        if ($a_id > 0) {
            $this->read($a_id);
        }
    }

    public function setId(int $a_value): void
    {
        $this->id = $a_value;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentId(int $a_value): void
    {
        $this->parent_id = $a_value;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setTitle(string $a_value): void
    {
        $this->title = $a_value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_value): void
    {
        $this->description = $a_value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setPosition(int $a_value): void
    {
        $this->pos = $a_value;
    }

    public function getPosition(): int
    {
        return $this->pos;
    }

    // Load db entry
    protected function read(int $a_id): void
    {
        $ilDB = $this->db;

        if ($a_id > 0) {
            $sql = "SELECT * FROM il_rating_cat" .
                " WHERE id = " . $ilDB->quote($a_id, "integer");
            $set = $ilDB->query($sql);
            $row = $ilDB->fetchAssoc($set);
            if ($row["id"]) {
                $this->setId($row["id"]);
                $this->setParentId($row["parent_id"]);
                $this->setTitle($row["title"]);
                $this->setDescription($row["description"]);
                $this->setPosition($row["pos"]);
            }
        }
    }

    // Parse properties into db definition
    protected function getDBProperties(): array
    {
        // parent id must not change
        $fields = array("title" => array("text", $this->getTitle()),
                "description" => array("text", $this->getDescription()),
                "pos" => array("integer", $this->getPosition()));

        return $fields;
    }

    public function update(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $fields = $this->getDBProperties();

            $ilDB->update(
                "il_rating_cat",
                $fields,
                array("id" => array("integer", $this->getId()))
            );
        }
    }

    public function save(): void
    {
        $ilDB = $this->db;

        $id = $ilDB->nextId("il_rating_cat");
        $this->setId($id);

        // append
        $sql = "SELECT max(pos) pos FROM il_rating_cat" .
            " WHERE parent_id = " . $ilDB->quote($this->getParentId(), "integer");
        $set = $ilDB->query($sql);
        $pos = $ilDB->fetchAssoc($set);
        $pos = $pos["pos"];
        $this->setPosition($pos + 10);

        $fields = $this->getDBProperties();
        $fields["id"] = array("integer", $id);
        $fields["parent_id"] = array("integer", $this->getParentId());

        $ilDB->insert("il_rating_cat", $fields);
    }

    public static function delete(int $a_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_id > 0) {
            $sql = "DELETE FROM il_rating" .
                " WHERE category_id = " . $ilDB->quote($a_id, "integer");
            $ilDB->manipulate($sql);

            $sql = "DELETE FROM il_rating_cat" .
                " WHERE id = " . $ilDB->quote($a_id, "integer");
            $ilDB->manipulate($sql);
        }
    }

    // Get all categories for object
    public static function getAllForObject(int $a_parent_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $cats = array();

        $sql = "SELECT * FROM il_rating_cat" .
            " WHERE parent_id = " . $ilDB->quote($a_parent_obj_id, "integer") .
            " ORDER BY pos";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $cats[] = $row;
        }

        return $cats;
    }

    // Delete all categories for object
    public static function deleteForObject(int $a_parent_obj_id): void
    {
        if ($a_parent_obj_id) {
            foreach (self::getAllForObject($a_parent_obj_id) as $item) {
                self::delete($item["id"]);
            }
        }
    }
}
