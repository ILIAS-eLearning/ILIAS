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
 * Media pool object
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaPool extends ilObject
{
    protected ?int $default_width = null;
    protected ?int $default_height = null;
    protected ilTree $mep_tree;
    public bool $for_translation = false;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        // this also calls read() method! (if $a_id is set)
        $this->type = "mep";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function setDefaultWidth(?int $a_val): void
    {
        if ($a_val === 0) {
            $a_val = null;
        }
        $this->default_width = $a_val;
    }

    public function getDefaultWidth(): ?int
    {
        return $this->default_width;
    }

    public function setDefaultHeight(?int $a_val): void
    {
        if ($a_val === 0) {
            $a_val = null;
        }
        $this->default_height = $a_val;
    }

    public function getDefaultHeight(): ?int
    {
        return $this->default_height;
    }

    /**
     * @param bool $a_val lm has been imported for translation purposes
     */
    public function setForTranslation(bool $a_val): void
    {
        $this->for_translation = $a_val;
    }

    public function getForTranslation(): bool
    {
        return $this->for_translation;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $set = $ilDB->query(
            "SELECT * FROM mep_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setDefaultWidth($rec["default_width"]);
            $this->setDefaultHeight($rec["default_height"]);
            $this->setForTranslation($rec["for_translation"]);
        }
        $this->mep_tree = self::_getPoolTree($this->getId());
    }


    /**
     * @param int $a_obj_id media pool id
     */
    public static function _getPoolTree(int $a_obj_id): ilTree
    {
        $tree = new ilTree($a_obj_id);
        $tree->setTreeTablePK("mep_id");
        $tree->setTableNames("mep_tree", "mep_item");

        return $tree;
    }

    public function getPoolTree(): ilTree
    {
        return self::_getPoolTree($this->getId());
    }

    public function create(): int
    {
        $ilDB = $this->db;

        $id = parent::create();

        $ilDB->manipulate("INSERT INTO mep_data " .
            "(id, default_width, default_height, for_translation) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . ", " .
            $ilDB->quote($this->getDefaultWidth(), "integer") . ", " .
            $ilDB->quote($this->getDefaultHeight(), "integer") . ", " .
            $ilDB->quote($this->getForTranslation(), "integer") .
            ")");

        $this->createMepTree();
        return $id;
    }

    public function createMepTree(): void
    {
        // create media pool tree
        $this->mep_tree = new ilTree($this->getId());
        $this->mep_tree->setTreeTablePK("mep_id");
        $this->mep_tree->setTableNames('mep_tree', 'mep_item');
        $this->mep_tree->addTree($this->getId(), 1);
    }

    public function getTree(): ilTree
    {
        return $this->mep_tree;
    }

    public function update(): bool
    {
        $ilDB = $this->db;

        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        $ilDB->manipulate(
            "UPDATE mep_data SET " .
            " default_width = " . $ilDB->quote($this->getDefaultWidth(), "integer") . "," .
            " default_height = " . $ilDB->quote($this->getDefaultHeight(), "integer") . "," .
            " for_translation = " . $ilDB->quote($this->getForTranslation(), "integer") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );

        return true;
    }


    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // get childs
        $childs = $this->mep_tree->getSubTree($this->mep_tree->getNodeData($this->mep_tree->readRootId()));

        // delete tree
        $this->mep_tree->removeTree($this->mep_tree->getTreeId());

        // delete childs
        foreach ($childs as $child) {
            $fid = ilMediaPoolItem::lookupForeignId($child["obj_id"]);
            switch ($child["type"]) {
                case "mob":
                    if (ilObject::_lookupType($fid) === "mob") {
                        $mob = new ilObjMediaObject($fid);
                        $mob->delete();
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * @param int $obj_id node id
     */
    public function getChilds(
        int $obj_id = 0,
        string $a_type = ""
    ): array {
        $objs = array();
        $mobs = array();
        $pgs = array();
        if ($obj_id === 0) {
            $obj_id = $this->mep_tree->getRootId();
        }

        if ($a_type === "fold" || $a_type === "") {
            $objs = $this->mep_tree->getChildsByType($obj_id, "fold");
        }
        if ($a_type === "mob" || $a_type === "") {
            $mobs = $this->mep_tree->getChildsByType($obj_id, "mob");
        }
        foreach ($mobs as $key => $mob) {
            $objs[] = $mob;
        }
        if ($a_type === "pg" || $a_type === "") {
            $pgs = $this->mep_tree->getChildsByType($obj_id, "pg");
        }
        foreach ($pgs as $key => $pg) {
            $objs[] = $pg;
        }

        return $objs;
    }

    public function getChildsExceptFolders(
        int $obj_id = 0
    ): array {
        if ($obj_id === 0) {
            $obj_id = $this->mep_tree->getRootId();
        }

        return $this->mep_tree->getFilteredChilds(array("fold", "dummy"), $obj_id);
    }

    /**
     * @param int $a_id media pool id
     * @return int[] object ids of media objects
     */
    public static function getAllMobIds(int $a_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT foreign_id as id FROM " .
            " mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) " .
            " WHERE mep_tree.mep_id = " . $ilDB->quote($a_id, "integer") .
            " AND mep_item.type = " . $ilDB->quote("mob", "text") .
            " AND object_data.type = " . $ilDB->quote("mob", "text");
        $set = $ilDB->query($query);
        $ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $rec["id"];
        }
        return $ids;
    }

    /**
     * @return string[]
     */
    public function getUsedFormats(): array
    {
        $ilDB = $this->db;
        $lng = $this->lng;

        $query = "SELECT DISTINCT media_item.format f FROM mep_tree " .
            " JOIN mep_item ON (mep_item.obj_id = mep_tree.child) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) " .
            " JOIN media_item ON (media_item.mob_id = object_data.obj_id) " .
            " WHERE mep_tree.mep_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND object_data.type = " . $ilDB->quote("mob", "text") .
            " ORDER BY f";
        $formats = array();
        $set = $ilDB->query($query);
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["f"] != "") {
                $formats[$rec["f"]] = $rec["f"];
            } else {
                $formats["unknown"] = $lng->txt("mep_unknown");
            }
        }

        return $formats;
    }

    public function getParentId(int $obj_id = 0): ?int
    {
        if ($obj_id === 0) {
            return null;
        }
        if ($obj_id === $this->mep_tree->getRootId()) {
            return null;
        }

        return (int) $this->mep_tree->getParentId($obj_id);
    }

    /**
     * Insert into tree
     */
    public function insertInTree(
        int $a_obj_id,
        ?int $a_parent = null
    ): bool {
        if (!$this->mep_tree->isInTree($a_obj_id)) {
            $parent = (is_null($a_parent))
                ? $this->mep_tree->getRootId()
                : $a_parent;
            $this->mep_tree->insertNode($a_obj_id, $parent);
            return true;
        }

        return false;
    }


    /**
     * Delete a child of media tree
     */
    public function deleteChild(int $obj_id): void
    {
        $node_data = $this->mep_tree->getNodeData($obj_id);
        $subtree = $this->mep_tree->getSubTree($node_data);

        // delete tree
        if ($this->mep_tree->isInTree($obj_id)) {
            $this->mep_tree->deleteTree($node_data);
        }

        // delete objects
        foreach ($subtree as $node) {
            $fid = ilMediaPoolItem::lookupForeignId($node["child"]);
            if ($node["type"] === "mob" && ilObject::_lookupType($fid) === "mob") {
                $obj = new ilObjMediaObject($fid);
                $obj->delete();
            }

            if ($node["type"] === "fold" && $fid > 0 && ilObject::_lookupType($fid) === "fold") {
                $obj = new ilObjFolder($fid, false);
                $obj->delete();
            }

            if ($node["type"] === "pg" && ilPageObject::_exists("mep", $node["child"])) {
                $pg = new ilMediaPoolPage($node["child"]);
                $pg->delete();
            }

            $item = new ilMediaPoolItem($node["child"]);
            $item->delete();
        }
    }

    /**
     * Check whether foreign id is in tree
     */
    public static function isForeignIdInTree(
        int $a_pool_id,
        int $a_foreign_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE " .
            " foreign_id = " . $ilDB->quote($a_foreign_id, "integer") .
            " AND mep_id = " . $ilDB->quote($a_pool_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Check whether a mep item id is in the media pool
     */
    public static function isItemIdInTree(
        int $a_pool_id,
        int $a_item_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM mep_tree WHERE child = " .
            $ilDB->quote($a_item_id, "integer") .
            " AND mep_id = " . $ilDB->quote($a_pool_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function createFolder(
        string $a_title,
        int $a_parent = 0
    ): ?int {
        // perform save
        $mep_item = new ilMediaPoolItem();
        $mep_item->setTitle($a_title);
        $mep_item->setType("fold");
        $mep_item->create();
        if ($mep_item->getId() > 0) {
            $tree = $this->getTree();
            $parent = $a_parent > 0
                ? $a_parent
                : $tree->getRootId();
            $this->insertInTree($mep_item->getId(), $parent);
            return $mep_item->getId();
        }
        return null;
    }

    /**
     * Clone media pool
     *
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        /** @var ilObjMediaPool $new_obj */
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setDefaultWidth($this->getDefaultWidth());
        $new_obj->setDefaultHeight($this->getDefaultHeight());
        $new_obj->update();

        // copy content
        $this->copyTreeContent(
            $new_obj,
            $new_obj->getTree()->readRootId(),
            $this->getTree()->readRootId()
        );

        return $new_obj;
    }

    public function copyTreeContent(
        ilObjMediaPool $a_new_obj,
        int $a_target_parent,
        int $a_source_parent
    ): void {
        // get all childs
        $nodes = $this->getTree()->getChilds($a_source_parent);
        foreach ($nodes as $node) {
            $item = new ilMediaPoolItem();
            $item->setType($node["type"]);
            switch ($node["type"]) {
                case "mob":
                    $mob_id = ilMediaPoolItem::lookupForeignId($node["child"]);
                    $mob = new ilObjMediaObject($mob_id);
                    $new_mob = $mob->duplicate();
                    $item->setForeignId($new_mob->getId());
                    $item->setTitle($new_mob->getTitle());
                    $item->create();
                    break;

                case "pg":
                    $item->setTitle($node["title"]);
                    $item->create();
                    $page = new ilMediaPoolPage($node["child"]);
                    $new_page = new ilMediaPoolPage();
                    $new_page->setParentId($a_new_obj->getId());
                    $new_page->setId($item->getId());
                    $new_page->create(false);

                    // copy page
                    $page->copy($new_page->getId(), $new_page->getParentType(), $new_page->getParentId(), true);
                    break;

                case "fold":
                    $item->setTitle($node["title"]);
                    $item->create();
                    break;
            }

            // insert item into tree
            $a_new_obj->insertInTree($item->getId(), $a_target_parent);

            // handle childs
            $this->copyTreeContent($a_new_obj, $item->getId(), $node["child"]);
        }
    }

    /**
     * @throws ilExportException
     */
    public function exportXML(string $a_mode = ""): void
    {
        if (in_array($a_mode, array("master", "masternomedia"))) {
            $exp = new ilExport();
            $conf = $exp->getConfig("Modules/MediaPool");
            $conf->setMasterLanguageOnly(true, ($a_mode === "master"));
            $exp->exportObject($this->getType(), $this->getId(), "4.4.0");
        }
    }
}
