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

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\MetaData\Paths\PathInterface as LOMPath;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Search\Filters\Placeholder;
use ILIAS\MetaData\Search\Clauses\Mode;

/**
 * Class ilLMObject
 *
 * Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMObject
{
    public const CHAPTER_TITLE = "st_title";
    public const PAGE_TITLE = "pg_title";
    public const NO_HEADER = "none";
    protected string $layout = "";
    protected string $import_id = "";

    protected ilObjUser $user;
    public int $lm_id = 0;
    public string $type = "";
    public int $id = 0;
    public ?array $data_record;		// assoc array of lm_data record
    public ilObjLearningModule $content_object;
    public string $title = "";
    public string $short_title = "";
    public string $description = "";
    public bool $active = true;
    protected static $data_records = array();
    protected ilDBInterface $db;
    protected LOMServices $lom_services;

    public function __construct(
        ilObjLearningModule $a_content_obj,
        int $a_id = 0
    ) {
        global $DIC;
        $this->user = $DIC->user();

        $this->db = $DIC->database();
        $this->lom_services = $DIC->learningObjectMetadata();

        $this->id = $a_id;
        $this->setContentObject($a_content_obj);
        $this->setLMId($a_content_obj->getId());
        if ($a_id != 0) {
            $this->read();
        }
    }

    /**
     * Meta data update listener
     * Important note: Do never call create() or update()
     * method of ilObject here. It would result in an
     * endless loop: update object -> update meta -> update
     * object -> ...
     * Use static _writeTitle() ... methods instead.
     * @param string $a_element md element
     */
    public function MDUpdateListener(string $a_element): void
    {
        switch ($a_element) {
            case 'General':

                // Update Title and description
                $paths = $this->lom_services->paths();
                $reader = $this->lom_services->read(
                    $this->getLMId(),
                    $this->getId(),
                    $this->getType(),
                    $paths->custom()->withNextStep('general')->get()
                );
                $title = $reader->firstData($paths->title())->value();

                ilLMObject::_writeTitle($this->getId(), $title);
                break;

            case 'Educational':
                $obj_lp = ilObjectLP::getInstance($this->getLMId());
                if (in_array(
                    $obj_lp->getCurrentMode(),
                    array(ilLPObjSettings::LP_MODE_TLT, ilLPObjSettings::LP_MODE_COLLECTION_TLT)
                )) {
                    ilLPStatusWrapper::_refreshStatus($this->getLMId());
                }
                break;

            default:
        }
    }


    /**
     * create meta data entry
     */
    public function createMetaData(): void
    {
        $ilUser = $this->user;

        $this->lom_services->derive()
                           ->fromBasicProperties(
                               $this->getTitle(),
                               $this->getDescription(),
                               $ilUser->getPref('language')
                           )->forObject($this->getLMId(), $this->getId(), $this->getType());
    }

    /**
    * update meta data entry
    */
    public function updateMetaData(): void
    {
        $this->lom_services->manipulate($this->getLMId(), $this->getId(), $this->getType())
                           ->prepareCreateOrUpdate(
                               $this->lom_services->paths()->title(),
                               $this->getTitle()
                           )->execute();
    }


    /**
     * delete meta data entry
     */
    public function deleteMetaData(): void
    {
        $this->lom_services->deleteAll($this->getLMId(), $this->getId(), $this->getType());
    }



    /**
     * this method should only be called by class ilLMObjectFactory
     */
    public function setDataRecord(array $a_record): void
    {
        $this->data_record = $a_record;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        if (!isset($this->data_record)) {
            $query = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($this->id, "integer");
            $obj_set = $ilDB->query($query);
            $this->data_record = $ilDB->fetchAssoc($obj_set);
        }

        $this->type = $this->data_record["type"];
        $this->setImportId((string) $this->data_record["import_id"]);
        $this->setTitle((string) $this->data_record["title"]);
        $this->setShortTitle((string) $this->data_record["short_title"]);
        $this->setLayout((string) $this->data_record["layout"]);
    }


    /**
     * Preload data records by lm
     * @return int number of preloaded records
     */
    public static function preloadDataByLM(int $a_lm_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM lm_data " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            self::$data_records[$rec["obj_id"]] = $rec;
        }
        return count(self::$data_records);
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setShortTitle(string $a_title): void
    {
        $this->short_title = $a_title;
    }

    public function getShortTitle(): string
    {
        return $this->short_title;
    }

    protected static function _lookup(int $a_obj_id, string $a_field): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_obj_id])) {
            return self::$data_records[$a_obj_id][$a_field] ?? "";
        }

        $query = "SELECT " . $a_field . " FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec[$a_field] ?? "";
    }

    public static function _lookupTitle(int $a_obj_id): string
    {
        return self::_lookup($a_obj_id, "title");
    }

    public static function _lookupShortTitle(int $a_obj_id): string
    {
        return self::_lookup($a_obj_id, "short_title");
    }

    public static function _lookupType(int $a_obj_id, int $a_lm_id = 0): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_obj_id])) {
            if ($a_lm_id == 0 || self::$data_records[$a_obj_id]["lm_id"] == $a_lm_id) {
                return self::$data_records[$a_obj_id]["type"];
            }
        }

        $and = "";
        if ($a_lm_id) {
            $and = ' AND lm_id = ' . $ilDB->quote($a_lm_id, 'integer');
        }

        $query = "SELECT type FROM lm_data WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") . $and;
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["type"] ?? "";
    }


    public static function _writeTitle(int $a_obj_id, string $a_title): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE lm_data SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        $ilDB->manipulate($query);
    }


    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setType(string $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setLMId(int $a_lm_id): void
    {
        $this->lm_id = $a_lm_id;
    }

    public function getLMId(): int
    {
        return $this->lm_id;
    }

    public function setContentObject(ilObjLearningModule $a_content_obj): void
    {
        $this->content_object = $a_content_obj;
    }

    public function getContentObject(): ilObjLearningModule
    {
        return $this->content_object;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    public function setImportId(string $a_id): void
    {
        $this->import_id = $a_id;
    }

    public function setLayout(string $a_val): void
    {
        $this->layout = $a_val;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public static function _writeImportId(int $a_id, string $a_import_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE lm_data " .
            "SET " .
            "import_id = " . $ilDB->quote($a_import_id, "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($a_id, "integer");

        $ilDB->manipulate($q);
    }

    public function create(bool $a_upload = false): void
    {
        $ilDB = $this->db;

        // insert object data
        $this->setId($ilDB->nextId("lm_data"));
        $query = "INSERT INTO lm_data (obj_id, title, type, layout, lm_id, import_id, short_title, create_date) " .
            "VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getType(), "text") . ", " .
            $ilDB->quote($this->getLayout(), "text") . ", " .
            $ilDB->quote($this->getLMId(), "integer") . "," .
            $ilDB->quote($this->getImportId(), "text") . "," .
            $ilDB->quote($this->getShortTitle(), "text") .
            ", " . $ilDB->now() . ")";
        $ilDB->manipulate($query);

        if (!$a_upload) {
            $this->createMetaData();
        }
    }

    public function update(): void
    {
        $ilDB = $this->db;

        $this->updateMetaData();

        $query = "UPDATE lm_data SET " .
            " lm_id = " . $ilDB->quote($this->getLMId(), "integer") .
            " ,title = " . $ilDB->quote($this->getTitle(), "text") .
            " ,short_title = " . $ilDB->quote($this->getShortTitle(), "text") .
            " ,layout = " . $ilDB->quote($this->getLayout(), "text") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($query);
    }

    public function delete(bool $a_delete_meta_data = true): void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM lm_data WHERE obj_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        $this->deleteMetaData();
    }

    /**
     * get current object id for import id (static)
     *
     * import ids can exist multiple times (if the same learning module
     * has been imported multiple times). we get the object id of
     * the last imported object, that is not in trash
     */
    public static function _getIdForImportId(string $a_import_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $help_module = $DIC->help()->internal()->domain()->module();

        $q = "SELECT obj_id FROM lm_data WHERE import_id = " .
            $ilDB->quote($a_import_id, "text") . " " .
            " ORDER BY create_date DESC";
        $obj_set = $ilDB->query($q);
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $lm_id = ilLMObject::_lookupContObjID($obj_rec["obj_id"]);

            // link only in learning module, that is not trashed
            $ref_ids = ilObject::_getAllReferences($lm_id);	// will be 0 if import of lm is in progress (new import)
            if (count($ref_ids) == 0 || ilObject::_hasUntrashedReference($lm_id) ||
                $help_module->isHelpLM($lm_id)) {
                return $obj_rec["obj_id"];
            }
        }

        return 0;
    }

    /**
     * Get all items for an import ID
     *
     * (only for items notnot in trash)
     */
    public static function _getAllObjectsForImportId(
        string $a_import_id,
        int $a_in_lm = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $where = ($a_in_lm > 0)
            ? " AND lm_id = " . $ilDB->quote($a_in_lm, "integer") . " "
            : "";

        $q = "SELECT * FROM lm_data WHERE import_id = " .
            $ilDB->quote($a_import_id, "text") . " " .
            $where .
            " ORDER BY create_date DESC";
        $obj_set = $ilDB->query($q);

        $items = array();
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            // check, whether lm is not trashed
            if (ilObject::_hasUntrashedReference($obj_rec["lm_id"])) {
                $items[] = $obj_rec;
            }
        }

        return $items;
    }

    /**
     * checks wether a lm content object with specified id exists or not
     */
    public static function _exists(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (is_int(strpos($a_id, "_"))) {
            $a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
        }

        $q = "SELECT * FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($q);
        if ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getObjectList(
        int $lm_id,
        string $type = ""
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $type_str = ($type != "")
            ? "AND type = " . $ilDB->quote($type, "text") . " "
            : "";

        $query = "SELECT * FROM lm_data " .
            "WHERE lm_id= " . $ilDB->quote($lm_id, "integer") . " " .
            $type_str . " " .
            "ORDER BY title";
        $obj_set = $ilDB->query($query);
        $obj_list = array();
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $obj_list[] = array("obj_id" => $obj_rec["obj_id"],
                                "title" => $obj_rec["title"],
                                "import_id" => $obj_rec["import_id"],
                                "type" => $obj_rec["type"]);
        }
        return $obj_list;
    }


    /**
     * delete all objects of content object (digi book / learning module)
     */
    public static function _deleteAllObjectData(
        ilObjLearningModule $a_cobj
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM lm_data " .
            "WHERE lm_id= " . $ilDB->quote($a_cobj->getId(), "integer");
        $obj_set = $ilDB->query($query);

        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $lm_obj = ilLMObjectFactory::getInstance($a_cobj, $obj_rec["obj_id"], false);

            if (is_object($lm_obj)) {
                $lm_obj->delete(true);
            }
        }
    }

    /**
     * get learning module id for lm object
     */
    public static function _lookupContObjID(int $a_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_id])) {
            return self::$data_records[$a_id]["lm_id"];
        }

        $query = "SELECT lm_id FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return (int) ($obj_rec["lm_id"] ?? 0);
    }

    /**
    * put this object into content object tree
    */
    public static function putInTree(
        ilLMObject $a_obj,
        int $a_parent_id = 0,
        int $a_target_node_id = 0
    ): void {
        global $DIC;

        $ilLog = $DIC["ilLog"];

        $tree = new ilLMTree($a_obj->getContentObject()->getId());

        // determine parent
        $parent_id = ($a_parent_id != 0)
            ? $a_parent_id
            : $tree->getRootId();

        // determine target
        if ($a_target_node_id != 0) {
            $target = $a_target_node_id;
        } else {
            // determine last child that serves as predecessor
            if ($a_obj->getType() == "st") {
                $s_types = array("st", "pg");
                $childs = $tree->getChildsByTypeFilter($parent_id, $s_types);
            } else {
                $s_types = "pg";
                $childs = $tree->getChildsByType($parent_id, $s_types);
            }

            if (count($childs) == 0) {
                $target = ilTree::POS_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }

        if ($tree->isInTree($parent_id) && !$tree->isInTree($a_obj->getId())) {
            $ilLog->write("LMObject::putInTree: insertNode, ID: " . $a_obj->getId() .
                "Parent ID: " . $parent_id . ", Target: " . $target);

            $tree->insertNode($a_obj->getId(), $parent_id, $target);
        }
    }

    /**
     * Get learning module tree
     */
    public static function getTree(
        int $a_cont_obj_id
    ): ilLMTree {
        $tree = new ilLMTree($a_cont_obj_id);
        $tree->readRootId();

        return $tree;
    }

    /**
     * Copy a set of chapters/pages into the clipboard
     */
    public static function clipboardCut(
        int $a_cont_obj_id,
        array $a_ids
    ): void {
        $tree = ilLMObject::getTree($a_cont_obj_id);
        $cut_ids = [];

        if (!is_array($a_ids)) {
            return;
        } else {
            // get all "top" ids, i.e. remove ids, that have a selected parent
            foreach ($a_ids as $id) {
                $path = $tree->getPathId($id);
                $take = true;
                foreach ($path as $path_id) {
                    if ($path_id != $id && in_array($path_id, $a_ids)) {
                        $take = false;
                    }
                }
                if ($take) {
                    $cut_ids[] = $id;
                }
            }
        }

        ilLMObject::clipboardCopy($a_cont_obj_id, $cut_ids);

        // remove the objects from the tree
        // note: we are getting chapters which are *not* in the tree
        // we do not delete any pages/chapters here
        foreach ($cut_ids as $id) {
            $curnode = $tree->getNodeData($id);
            if ($tree->isInTree($id)) {
                $tree->deleteTree($curnode);
            }
        }
    }

    /**
     * Copy a set of chapters/pages into the clipboard
     */
    public static function clipboardCopy(
        int $a_cont_obj_id,
        array $a_ids
    ): void {
        global $DIC;

        $ilUser = $DIC->user();

        $tree = ilLMObject::getTree($a_cont_obj_id);

        $ilUser->clipboardDeleteObjectsOfType("pg");
        $ilUser->clipboardDeleteObjectsOfType("st");

        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
        foreach ($a_ids as $id) {
            $curnode = array();
            if ($tree->isInTree($id)) {
                $curnode = $tree->getNodeData($id);
                $subnodes = $tree->getSubTree($curnode);
                foreach ($subnodes as $subnode) {
                    if ($subnode["child"] != $id) {
                        $ilUser->addObjectToClipboard(
                            $subnode["child"],
                            $subnode["type"],
                            ilStr::subStr($subnode["title"], 0, 70),
                            $subnode["parent"],
                            $time,
                            $subnode["lft"]
                        );
                    }
                }
            }
            $order = (($curnode["lft"] ?? 0) > 0)
                ? $curnode["lft"]
                : (int) ($order + 1);
            $ilUser->addObjectToClipboard(
                $id,
                self::_lookupType($id),
                ilStr::subStr(self::_lookupTitle($id), 0, 70),
                0,
                $time,
                $order
            );
        }
    }

    /**
     * Paste item (tree) from clipboard to current lm
     */
    public static function pasteTree(
        ilObjLearningModule $a_target_lm,
        int $a_item_id,
        int $a_parent_id,
        int $a_target,
        string $a_insert_time,
        array &$a_copied_nodes,
        bool $a_as_copy = false,
        ?ilObjLearningModule $a_source_lm = null
    ): int {
        global $DIC;

        $item = null;
        $ilUser = $DIC->user();
        $ilLog = $DIC["ilLog"];
        $lom_services = $DIC->learningObjectMetadata();

        $item_lm_id = ilLMObject::_lookupContObjID($a_item_id);
        $item_type = ilLMObject::_lookupType($a_item_id);
        /** @var ilObjLearningModule $lm_obj */
        $lm_obj = ilObjectFactory::getInstanceByObjId($item_lm_id);
        if ($item_type == "st") {
            $item = new ilStructureObject($lm_obj, $a_item_id);
        } elseif ($item_type == "pg") {
            $item = new ilLMPageObject($lm_obj, $a_item_id);
        }

        $ilLog->write("Getting from clipboard type " . $item_type . ", " .
            "Item ID: " . $a_item_id . ", of original LM: " . $item_lm_id);

        if ($item_lm_id != $a_target_lm->getId() && !$a_as_copy) {
            // @todo: check whether st is NOT in tree

            // "move" metadata to new lm
            $lom_services->derive()
                         ->fromObject($item_lm_id, $item->getId(), $item->getType())
                         ->forObject($a_target_lm->getId(), $item->getId(), $item->getType());

            // delete old meta data set
            $lom_services->deleteAll($item_lm_id, $item->getId(), $item->getType());

            if ($item_type == "pg") {
                $page = $item->getPageObject();
                $page->buildDom();
                $page->setParentId($a_target_lm->getId());
                $page->update();
            }
        }

        if ($a_as_copy) {
            $target_item = $item->copy($a_target_lm);
            $a_copied_nodes[$item->getId()] = $target_item->getId();
        } else {
            $target_item = $item;
        }

        $ilLog->write("Putting into tree type " . $target_item->getType() .
            "Item ID: " . $target_item->getId() . ", Parent: " . $a_parent_id . ", " .
            "Target: " . $a_target . ", Item LM:" . $target_item->getContentObject()->getId());

        ilLMObject::putInTree($target_item, $a_parent_id, $a_target);

        if ($a_source_lm == null) {
            $childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);
        } else {
            $childs = $a_source_lm->lm_tree->getChilds($item->getId());
            foreach ($childs as $k => $child) {
                $childs[$k]["id"] = $child["child"];
            }
        }

        foreach ($childs as $child) {
            ilLMObject::pasteTree(
                $a_target_lm,
                $child["id"],
                $target_item->getId(),
                ilTree::POS_LAST_NODE,
                $a_insert_time,
                $a_copied_nodes,
                $a_as_copy,
                $a_source_lm
            );
        }

        return $target_item->getId();
        // @todo: write history (see pastePage)
    }

    /**
     * Save titles for lm objects
     */
    public static function saveTitles(
        ilObjLearningModule $a_lm,
        array $a_titles,
        string $a_lang = "-"
    ): void {
        global $DIC;

        $lom_services = $DIC->learningObjectMetadata();

        if ($a_lang == "") {
            $a_lang = "-";
        }
        if (is_array($a_titles)) {
            foreach ($a_titles as $id => $title) {
                self::saveTitle($id, $title, $a_lang);
            }
        }
    }

    public static function saveTitle(int $id, string $title, string $lang = "-"): void
    {
        global $DIC;

        $lom_services = $DIC->learningObjectMetadata();
        $title = ilFormPropertyGUI::removeProhibitedCharacters($title);
        if (in_array($lang, ["-", ""])) {
            $lm_id = self::_lookupContObjID($id);
            $type = self::_lookupType($id);
            if ($type !== "" && $lm_id > 0) {
                $lom_services->manipulate($lm_id, $id, $type)
                             ->prepareCreateOrUpdate(
                                 $lom_services->paths()->title(),
                                 $title
                             )->execute();
                self::_writeTitle($id, $title);
            }
        } else {
            $lmobjtrans = new ilLMObjTranslation($id, $lang);
            $lmobjtrans->setTitle($title);
            $lmobjtrans->save();
        }
    }

    /**
     * Update internal links, after multiple pages have been copied
     */
    public static function updateInternalLinks(
        array $a_copied_nodes,
        string $a_parent_type = "lm"
    ): void {
        $all_fixes = array();
        foreach ($a_copied_nodes as $original_id => $copied_id) {
            $copied_type = ilLMObject::_lookupType($copied_id);
            $copy_lm = ilLMObject::_lookupContObjID($copied_id);

            if ($copied_type == "pg") {
                foreach (ilPageObject::lookupTranslations($a_parent_type, $copied_id) as $l) {
                    //
                    // 1. Outgoing links from the copied page.
                    //
                    //$targets = ilInternalLink::_getTargetsOfSource($a_parent_type.":pg", $copied_id);
                    $tpg = new ilLMPage($copied_id, 0, $l);
                    $tpg->buildDom();
                    $il = $tpg->getInternalLinks();
                    $targets = array();
                    foreach ($il as $l2) {
                        $targets[] = array("type" => ilInternalLink::_extractTypeOfTarget($l2["Target"]),
                            "id" => (int) ilInternalLink::_extractObjIdOfTarget($l2["Target"]),
                            "inst" => (int) ilInternalLink::_extractInstOfTarget($l2["Target"]));
                    }
                    $fix = array();
                    foreach ($targets as $target) {
                        if (($target["inst"] == 0 || $target["inst"] = IL_INST_ID) &&
                            ($target["type"] == "pg" || $target["type"] == "st")) {
                            // first check, whether target is also within the copied set
                            if (($a_copied_nodes[$target["id"]] ?? 0) > 0) {
                                $fix[$target["id"]] = $a_copied_nodes[$target["id"]];
                            } else {
                                // now check, if a copy if the target is already in the same lm

                                // only if target is not already in the same lm!
                                $trg_lm = ilLMObject::_lookupContObjID($target["id"]);
                                if ($trg_lm != $copy_lm) {
                                    $lm_data = ilLMObject::_getAllObjectsForImportId("il__" . $target["type"] . "_" . $target["id"]);
                                    $found = false;

                                    foreach ($lm_data as $item) {
                                        if (!$found && ($item["lm_id"] == $copy_lm)) {
                                            $fix[$target["id"]] = $item["obj_id"];
                                            $found = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // outgoing links to be fixed
                    if (count($fix) > 0) {
                        //echo "<br>--".$copied_id;
                        //var_dump($fix);
                        $t = ilObject::_lookupType($copy_lm);
                        if (isset($all_fixes[$t . ":" . $copied_id])) {
                            $all_fixes[$t . ":" . $copied_id] += $fix;
                        } else {
                            $all_fixes[$t . ":" . $copied_id] = $fix;
                        }
                    }
                }
            }

            if ($copied_type == "pg" ||
                $copied_type == "st") {
                //
                // 2. Incoming links to the original pages
                //
                // A->B			A2			(A+B currently copied)
                // A->C			B2
                // B->A
                // C->A			C2->A		(C already copied)
                $original_lm = ilLMObject::_lookupContObjID($original_id);
                $original_type = ilObject::_lookupType($original_lm);

                if ($original_lm != $copy_lm) {
                    // This gets sources that link to A+B (so we have C here)
                    // (this also does already the trick when instance map areas are given in C)
                    // int_link, where target_type, target_id, target_inst -> ok
                    $sources = ilInternalLink::_getSourcesOfTarget(
                        $copied_type,
                        $original_id,
                        0
                    );

                    // mobs linking to $original_id
                    // map_area, where link_type, target -> ok
                    $mobs = ilMapArea::_getMobsForTarget("int", "il__" . $copied_type .
                        "_" . $original_id);

                    // pages using these mobs
                    foreach ($mobs as $mob) {
                        // mob_usage, where id -> ok
                        // mep_item, where foreign_id, type -> ok
                        // mep_tree, where child -> already existed
                        // il_news_item, where mob_id -> ok
                        // map_area, where link_type, target -> aready existed
                        // media_item, where id -> already existed
                        // personal_clipboard, where item_id, type -> ok
                        $usages = ilObjMediaObject::lookupUsages($mob);
                        foreach ($usages as $usage) {
                            if ($usage["type"] == "lm:pg" | $usage["type"] == "lm:st") {
                                $sources[] = $usage;
                            }
                        }
                    }
                    $fix = array();
                    foreach ($sources as $source) {
                        $stype = explode(":", $source["type"]);
                        $source_type = $stype[1] ?? "";

                        if ($source_type == "pg" || $source_type == "st") {
                            // first of all: source must be in original lm
                            $src_lm = ilLMObject::_lookupContObjID($source["id"]);

                            if ($src_lm == $original_lm) {
                                // check, if a copy if the source is already in the same lm
                                // now we look for the latest copy of C in LM2
                                $lm_data = ilLMObject::_getAllObjectsForImportId(
                                    "il__" . $source_type . "_" . $source["id"],
                                    $copy_lm
                                );
                                $found = false;
                                foreach ($lm_data as $item) {
                                    if (!$found) {
                                        $fix[$item["obj_id"]][$original_id] = $copied_id;
                                        $found = true;
                                    }
                                }
                            }
                        }
                    }
                    // outgoing links to be fixed
                    if (count($fix) > 0) {
                        foreach ($fix as $page_id => $fix_array) {
                            $t = ilObject::_lookupType($copy_lm);
                            if (isset($all_fixes[$t . ":" . $page_id])) {
                                $all_fixes[$t . ":" . $page_id] += $fix_array;
                            } else {
                                $all_fixes[$t . ":" . $page_id] = $fix_array;
                            }
                        }
                    }
                }
            }
        }

        foreach ($all_fixes as $pg => $fixes) {
            $pg = explode(":", $pg);
            foreach (ilPageObject::lookupTranslations($pg[0], $pg[1]) as $l) {
                $page = ilPageObjectFactory::getInstance($pg[0], $pg[1], 0, $l);
                if ($page->moveIntLinks($fixes)) {
                    $page->update(true, true);
                }
            }
        }
    }

    /**
     * Check for unique types (all pages or all chapters)
     */
    public static function uniqueTypesCheck(array $a_items): bool
    {
        $types = array();
        if (is_array($a_items)) {
            foreach ($a_items as $item) {
                $type = ilLMObject::_lookupType($item);
                $types[$type] = $type;
            }
        }

        if (count($types) > 1) {
            return false;
        }
        return true;
    }

    /**
     * Write layout setting
     */
    public static function writeLayout(
        int $a_obj_id,
        string $a_layout,
        ?ilObjLearningModule $a_lm = null
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $t = ilLMObject::_lookupType($a_obj_id);

        if ($t == "pg") {
            $query = "UPDATE lm_data SET " .
                " layout = " . $ilDB->quote($a_layout, "text") .
                " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
            $ilDB->manipulate($query);
        } elseif ($t == "st" && is_object($a_lm)) {
            $node = $a_lm->getLMTree()->getNodeData($a_obj_id);
            $child_nodes = $a_lm->getLMTree()->getSubTree($node);
            if (is_array($child_nodes) && count($child_nodes) > 0) {
                foreach ($child_nodes as $c) {
                    if ($c["type"] == "pg") {
                        $query = "UPDATE lm_data SET " .
                            " layout = " . $ilDB->quote($a_layout, "text") .
                            " WHERE obj_id = " . $ilDB->quote($c["child"], "integer");
                        $ilDB->manipulate($query);
                    }
                }
            }
        }
    }

    /**
     * Lookup type
     */
    public static function lookupLayout(int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT layout FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["layout"];
    }

    /**
     * Get pages of chapter
     */
    public static function getPagesOfChapter(
        int $a_lm_id,
        int $a_chap_id
    ): array {
        // update structure entries: if at least one page of a chapter is public set chapter to public too
        $lm_tree = new ilTree($a_lm_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");
        $lm_tree->readRootId();

        $childs = $lm_tree->getChildsByType($a_chap_id, "pg");

        return $childs;
    }

    /**
     * Get all objects of learning module
     */
    public static function _getAllLMObjectsOfLM(
        int $a_lm_id,
        string $a_type = ""
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $and = ($a_type != "")
            ? " AND type = " . $ilDB->quote($a_type, "text")
            : "";

        $set = $ilDB->query("SELECT obj_id FROM lm_data " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer") . $and);
        $obj_ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $obj_ids[] = $rec["obj_id"];
        }

        return $obj_ids;
    }


    ////
    //// Export ID handling
    ////

    public static function saveExportId(
        int $a_lm_id,
        int $a_lmobj_id,
        string $a_exp_id,
        string $a_type = "pg"
    ): void {
        global $DIC;

        $manipulator = $DIC->learningObjectMetadata()->manipulate($a_lm_id, $a_lmobj_id, $a_type);
        if (trim($a_exp_id) == "") {
            $manipulator = $manipulator->prepareDelete(self::getPathToExportIDInLOM());
        } else {
            $manipulator = $manipulator->prepareCreateOrUpdate(self::getPathToExportIDInLOM(), $a_exp_id);
        }
        $manipulator->execute();
    }

    protected static function getPathToExportIDInLOM(): LOMPath
    {
        global $DIC;

        return $DIC->learningObjectMetadata()
                   ->paths()
                   ->custom()
                   ->withNextStep('general')
                   ->withNextStep('identifier')
                   ->withNextStep('catalog')
                   ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'ILIAS_NID')
                   ->withNextStepToSuperElement()
                   ->withNextStep('entry')
                   ->get();
    }

    public static function getExportId(
        int $a_lm_id,
        int $a_lmobj_id,
        string $a_type = "pg"
    ): string {
        global $DIC;

        // look for export id
        $export_id_path = self::getPathToExportIDInLOM();
        return $DIC->learningObjectMetadata()->read(
            $a_lm_id,
            $a_lmobj_id,
            $a_type,
            $export_id_path
        )->firstData($export_id_path)->value();
    }

    /**
     * Does export ID exist in lm?
     */
    public function existsExportID(
        int $a_lm_id,
        int $a_exp_id,
        string $a_type = "pg"
    ): bool {
        $searcher = $this->lom_services->search();

        $search_clause = $searcher->getClauseFactory()->getBasicClause(
            self::getPathToExportIDInLOM(),
            Mode::EQUALS,
            $a_exp_id
        );
        $results = $searcher->execute(
            $search_clause,
            1,
            null,
            $searcher->getFilter($a_lm_id, Placeholder::ANY, $a_type)
        );
        return count(iterator_to_array($results)) > 0;
    }

    /**
     * Get duplicate export IDs (count export ID usages)
     */
    public static function getDuplicateExportIDs(
        int $a_lm_id,
        string $a_type = "pg"
    ): array {
        global $DIC;

        $lom_services = $DIC->learningObjectMetadata();
        $export_id_path = self::getPathToExportIDInLOM();

        $searcher = $lom_services->search();
        $search_clause = $searcher->getClauseFactory()->getBasicClause(
            $export_id_path,
            Mode::EQUALS,
            '',
            true
        );
        $search_results = $searcher->execute(
            $search_clause,
            1,
            null,
            $searcher->getFilter($a_lm_id, Placeholder::ANY, $a_type)
        );

        $res = [];
        foreach ($search_results as $search_result) {
            if (!ilLMObject::_exists($search_result->subID())) {
                continue;
            }
            $reader = $lom_services->read(
                $search_result->objID(),
                $search_result->subID(),
                $search_result->type(),
                $export_id_path
            );
            foreach ($reader->allData($export_id_path) as $export_id_datum) {
                $export_id = trim($export_id_datum->value());
                $res[$export_id] = ($res[$export_id] ?? 0) + 1;
            }
        }
        return $res;
    }

    // Get effective title
    public static function _getNodePresentationTitle(
        array $a_node,
        string $a_mode = self::PAGE_TITLE,
        bool $a_include_numbers = false,
        bool $a_time_scheduled_activation = false,
        bool $a_force_content = false,
        int $a_lm_id = 0,
        string $a_lang = "-"
    ): string {
        if ($a_lang == "") {
            $a_lang = "-";
        }

        if ($a_node["type"] == "st") {
            return ilStructureObject::_getPresentationTitle(
                $a_node["child"],
                self::CHAPTER_TITLE,
                $a_include_numbers,
                $a_time_scheduled_activation,
                $a_force_content,
                $a_lm_id,
                $a_lang
            );
        } else {
            return ilLMPageObject::_getPresentationTitle(
                $a_node["child"],
                $a_mode,
                $a_include_numbers,
                $a_time_scheduled_activation,
                $a_force_content,
                $a_lm_id,
                $a_lang
            );
        }
    }

    public static function getShortTitles(
        int $a_lm_id,
        string $a_lang = "-"
    ): array {
        global $DIC;

        $db = $DIC->database();

        $title_data = array();
        if ($a_lang == "-") {
            $set = $db->query("SELECT t.child, d.obj_id, d.title, d.short_title FROM lm_data d LEFT JOIN lm_tree t ON (d.obj_id = t.child) WHERE d.lm_id = " .
                $db->quote($a_lm_id, "integer") . " ORDER BY t.lft, d.title");
        } else {
            $set = $db->query("SELECT t.child, d.obj_id, tr.title, tr.short_title, d.title default_title, d.short_title default_short_title FROM lm_data d " .
                " LEFT JOIN lm_tree t ON (d.obj_id = t.child) " .
                " LEFT JOIN lm_data_transl tr ON (tr.id = d.obj_id AND tr.lang=" . $db->quote($a_lang, "text") . ") WHERE d.lm_id = " .
                $db->quote($a_lm_id, "integer") . " ORDER BY t.lft, d.title");
        }
        while ($rec = $db->fetchAssoc($set)) {
            $title_data[] = $rec;
        }
        return $title_data;
    }

    public static function writeShortTitle(
        int $a_id,
        string $a_short_title,
        string $a_lang = "-"
    ): void {
        global $DIC;

        $db = $DIC->database();

        if ($a_lang != "-" && $a_lang != "") {
            $trans = new ilLMObjTranslation($a_id, $a_lang);
            $trans->setShortTitle($a_short_title);
            $trans->save();
        } else {
            $db->manipulate(
                "UPDATE lm_data SET " .
                " short_title = " . $db->quote($a_short_title, "text") .
                " WHERE obj_id = " . $db->quote($a_id, "integer")
            );
        }
    }
}
