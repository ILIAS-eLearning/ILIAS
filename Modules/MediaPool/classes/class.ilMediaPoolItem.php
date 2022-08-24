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
 * Media Pool Item
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolItem
{
    protected string $title = "";
    protected int $foreign_id = 0;
    protected string $type = "";
    protected int $id = 0;
    protected ilDBInterface $db;
    protected string $import_id = "";

    /**
     * @param int $a_id media pool item id
     */
    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public function setId(int $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setType(string $a_val): void
    {
        $this->type = $a_val;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set foreign id (mob id)
     * @param int $a_val foreign id
     */
    public function setForeignId(int $a_val): void
    {
        $this->foreign_id = $a_val;
    }

    public function getForeignId(): int
    {
        return $this->foreign_id;
    }

    public function setImportId(string $a_val): void
    {
        $this->import_id = $a_val;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    public function setTitle(string $a_val): void
    {
        $this->title = $a_val;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function create(): void
    {
        $ilDB = $this->db;

        $nid = $ilDB->nextId("mep_item");
        $ilDB->manipulate("INSERT INTO mep_item " .
            "(obj_id, type, foreign_id, title, import_id) VALUES (" .
            $ilDB->quote($nid, "integer") . "," .
            $ilDB->quote($this->getType(), "text") . "," .
            $ilDB->quote($this->getForeignId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getImportId(), "text") .
            ")");
        $this->setId($nid);
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM mep_item WHERE " .
            "obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setType($rec["type"]);
            $this->setForeignId($rec["foreign_id"]);
            $this->setTitle($rec["title"]);
            $this->setImportId((string) $rec["import_id"]);
        }
    }

    public function update(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "UPDATE mep_item SET " .
            " type = " . $ilDB->quote($this->getType(), "text") . "," .
            " foreign_id = " . $ilDB->quote($this->getForeignId(), "integer") . "," .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " import_id = " . $ilDB->quote($this->getImportId(), "text") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM mep_item WHERE "
            . " obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    private static function lookup(
        int $a_id,
        string $a_field
    ): ?string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_field . " FROM mep_item WHERE " .
            " obj_id = " . $ilDB->quote($a_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec[$a_field];
        }
        return null;
    }

    public static function lookupForeignId(int $a_id): int
    {
        return (int) self::lookup($a_id, "foreign_id");
    }

    public static function lookupType(int $a_id): string
    {
        return self::lookup($a_id, "type");
    }

    public static function lookupTitle(int $a_id): string
    {
        return self::lookup($a_id, "title");
    }

    // synch media item title for media objects
    public static function updateObjectTitle(int $a_obj): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (ilObject::_lookupType($a_obj) === "mob") {
            $title = ilObject::_lookupTitle($a_obj);
            $ilDB->manipulate(
                "UPDATE mep_item SET " .
                " title = " . $ilDB->quote($title, "text") .
                " WHERE foreign_id = " . $ilDB->quote($a_obj, "integer") .
                " AND type = " . $ilDB->quote("mob", "text")
            );
        }
    }

    /**
     * @return int[]
     */
    public static function getPoolForItemId(int $a_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM mep_tree " .
            " WHERE child = " . $ilDB->quote($a_id, "integer")
        );
        $pool_ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $pool_ids[] = (int) $rec["mep_id"];
        }
        return $pool_ids;
    }

    /**
     * Get all ids for type
     * @param int    $a_id media pool id
     * @param string $a_type media item type
     * @return int[]
     */
    public static function getIdsForType(
        int $a_id,
        string $a_type
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT mep_tree.child as id" .
            " FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) WHERE " .
            " mep_tree.mep_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            " mep_item.type = " . $ilDB->quote($a_type, "text")
        );

        $ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $rec["id"];
        }
        return $ids;
    }
}
