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

use ILIAS\LearningModule\ReadingTime\ReadingTimeManager;

/**
 * LearningModule Data set class
 *
 * This class implements the following entities:
 * - lm: data from content_object
 * - lm_tree: data from lm_tree/lm_data
 * - lm_data_transl: data from lm_data_transl
 * - lm_menu: data from lm_menu
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleDataSet extends ilDataSet
{
    protected ReadingTimeManager $reading_time_manager;
    protected \ILIAS\Notes\Service $notes;
    protected ilObjLearningModule $current_obj;
    protected bool $master_lang_only = false;
    protected bool $transl_into = false;
    protected ?ilObjLearningModule $transl_into_lm = null;
    protected string $transl_lang = "";
    protected ilLogger$lm_log;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->lm_log = ilLoggerFactory::getLogger('lm');
        $this->notes = $DIC->notes();
        $this->reading_time_manager = new ReadingTimeManager();
    }

    public function setMasterLanguageOnly(bool $a_val): void
    {
        $this->master_lang_only = $a_val;
    }

    public function getMasterLanguageOnly(): bool
    {
        return $this->master_lang_only;
    }

    public function setTranslationImportMode(
        ilObjLearningModule $a_lm,
        string $a_lang = ""
    ): void {
        if ($a_lm != null) {
            $this->transl_into = true;
            $this->transl_into_lm = $a_lm;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    public function getTranslationImportMode(): bool
    {
        return $this->transl_into;
    }

    public function getTranslationLM(): ilObjLearningModule
    {
        return $this->transl_into_lm;
    }

    public function getTranslationLang(): string
    {
        return $this->transl_lang;
    }

    public function getSupportedVersions(): array
    {
        return array("5.1.0", "5.4.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/LearningModule/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity == "lm") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "DefaultLayout" => "text",
                        "PageHeader" => "text",
                        "TocActive" => "text",
                        "LMMenuActive" => "text",
                        "TOCMode" => "text",
                        "PrintViewActive" => "text",
                        "Numbering" => "text",
                        "HistUserComments" => "text",
                        "PublicAccessMode" => "text",
                        "PubNotes" => "text",
                        "HeaderPage" => "integer",
                        "FooterPage" => "integer",
                        "LayoutPerPage" => "integer",
                        "Rating" => "integer",
                        "HideHeadFootPrint" => "integer",
                        "DisableDefFeedback" => "integer",
                        "RatingPages" => "integer",
                        "ProgrIcons" => "integer",
                        "StoreTries" => "integer",
                        "RestrictForwNav" => "integer",
                        "Comments" => "integer",
                        "ForTranslation" => "integer",
                        "StyleId" => "integer"
                    );

                case "5.4.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "DefaultLayout" => "text",
                        "PageHeader" => "text",
                        "TocActive" => "text",
                        "LMMenuActive" => "text",
                        "TOCMode" => "text",
                        "PrintViewActive" => "text",
                        "NoGloAppendix" => "text",
                        "Numbering" => "text",
                        "HistUserComments" => "text",
                        "PublicAccessMode" => "text",
                        "PubNotes" => "text",
                        "HeaderPage" => "integer",
                        "FooterPage" => "integer",
                        "LayoutPerPage" => "integer",
                        "Rating" => "integer",
                        "HideHeadFootPrint" => "integer",
                        "DisableDefFeedback" => "integer",
                        "RatingPages" => "integer",
                        "ProgrIcons" => "integer",
                        "StoreTries" => "integer",
                        "RestrictForwNav" => "integer",
                        "Comments" => "integer",
                        "ForTranslation" => "integer",
                        "StyleId" => "integer"
                    );

                case "8.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "DefaultLayout" => "text",
                        "PageHeader" => "text",
                        "TocActive" => "text",
                        "LMMenuActive" => "text",
                        "TOCMode" => "text",
                        "PrintViewActive" => "text",
                        "NoGloAppendix" => "text",
                        "Numbering" => "text",
                        "HistUserComments" => "text",
                        "PublicAccessMode" => "text",
                        "PubNotes" => "text",
                        "HeaderPage" => "integer",
                        "FooterPage" => "integer",
                        "LayoutPerPage" => "integer",
                        "Rating" => "integer",
                        "HideHeadFootPrint" => "integer",
                        "DisableDefFeedback" => "integer",
                        "RatingPages" => "integer",
                        "ProgrIcons" => "integer",
                        "StoreTries" => "integer",
                        "RestrictForwNav" => "integer",
                        "Comments" => "integer",
                        "ForTranslation" => "integer",
                        "StyleId" => "integer",
                        "EstimatedReadingTime" => "integer"
                    );

            }
        }

        if ($a_entity == "lm_tree") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                    return array(
                        "LmId" => "integer",
                        "Child" => "integer",
                        "Parent" => "integer",
                        "Depth" => "integer",
                        "Type" => "text",
                        "Title" => "text",
                        "ShortTitle" => "text",
                        "PublicAccess" => "text",
                        "Active" => "text",
                        "Layout" => "text",
                        "ImportId" => "text"
                    );
            }
        }

        if ($a_entity == "lm_menu") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                return array(
                        "LmId" => "integer",
                        "LinkType" => "text",
                        "Title" => "text",
                        "Target" => "text",
                        "LinkRefId" => "text",
                        "Active" => "text"
                    );
            }
        }

        if ($a_entity == "lm_data_transl") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                return array(
                        "Id" => "integer",
                        "Lang" => "text",
                        "Title" => "text",
                        "ShortTitle" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        $q = "";
        if ($a_entity == "lm") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                switch ($a_version) {
                        case "5.1.0":
                            $q = "SELECT id, title, description," .
                                " default_layout, page_header, toc_active, lm_menu_active, toc_mode, print_view_active, numbering," .
                                " hist_user_comments, public_access_mode, header_page, footer_page, layout_per_page, rating, " .
                                " hide_head_foot_print, disable_def_feedback, rating_pages, store_tries, restrict_forw_nav, progr_icons, stylesheet style_id" .
                                " FROM content_object JOIN object_data ON (content_object.id = object_data.obj_id)" .
                                " WHERE " . $ilDB->in("id", $a_ids, false, "integer");
                                break;

                        case "5.4.0":
                        case "8.0":
                        $q = "SELECT id, title, description," .
                                " default_layout, page_header, toc_active, lm_menu_active, toc_mode, print_view_active, numbering," .
                                " hist_user_comments, public_access_mode, no_glo_appendix, header_page, footer_page, layout_per_page, rating, " .
                                " hide_head_foot_print, disable_def_feedback, rating_pages, store_tries, restrict_forw_nav, progr_icons, stylesheet style_id" .
                                " FROM content_object JOIN object_data ON (content_object.id = object_data.obj_id)" .
                                " WHERE " . $ilDB->in("id", $a_ids, false, "integer");

                    }

                    $set = $ilDB->query($q);
                    $this->data = array();
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        // comments activated?
                        $rec["comments"] = (string) (int)
                            $this->notes->domain()->commentsActive((int) $rec["id"]);

                        if ($a_version === "8.0") {
                            $rec["estimated_reading_time"] = (string) (int)
                                $this->reading_time_manager->isActivated((int) $rec["id"]);
                        }

                        if ($this->getMasterLanguageOnly()) {
                            $rec["for_translation"] = 1;
                        }
                        $tmp = array();
                        foreach ($rec as $k => $v) {
                            $tmp[$this->convertToLeadingUpper($k)]
                                = $v;
                        }
                        $rec = $tmp;
                        $this->data[] = $rec;
                    }
                    break;
            }
        }

        if ($a_entity == "lm_tree") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                    // the order by lft is very important, this ensures that parent nodes are written before
                    // their childs and that the import can add nodes simply with a "add at last child" target
                    $q = "SELECT lm_tree.lm_id, child, parent, depth, type, title, short_title, public_access, active, layout, import_id" .
                        " FROM lm_tree JOIN lm_data ON (lm_tree.child = lm_data.obj_id)" .
                        " WHERE " . $ilDB->in("lm_tree.lm_id", $a_ids, false, "integer") .
                        " ORDER BY lft";

                    $set = $ilDB->query($q);
                    $this->data = array();
                    $obj_ids = array();
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $set2 = $ilDB->query("SELECT for_translation FROM content_object WHERE id = " . $ilDB->quote($rec["lm_id"], "integer"));
                        $rec2 = $ilDB->fetchAssoc($set2);
                        if (!$rec2["for_translation"]) {
                            $rec["import_id"] = "il_" . IL_INST_ID . "_" . $rec["type"] . "_" . $rec["child"];
                        }
                        $tmp = array();
                        foreach ($rec as $k => $v) {
                            $tmp[$this->convertToLeadingUpper($k)]
                                = $v;
                        }
                        $rec = $tmp;
                        $obj_ids[] = $rec["Child"];
                        $this->data[] = $rec;
                    }

                    // add free pages #18976
                    $set3 = $ilDB->query($q = "SELECT lm_id, type, title, short_title, public_access, active, layout, import_id, obj_id child FROM lm_data " .
                        "WHERE " . $ilDB->in("lm_id", $a_ids, false, "integer") .
                        " AND " . $ilDB->in("obj_id", $obj_ids, true, "integer") .
                        " AND type = " . $ilDB->quote("pg", "text"));
                    while ($rec3 = $ilDB->fetchAssoc($set3)) {
                        $set2 = $ilDB->query("SELECT for_translation FROM content_object WHERE id = " . $ilDB->quote($rec3["lm_id"], "integer"));
                        $rec2 = $ilDB->fetchAssoc($set2);
                        if (!$rec2["for_translation"]) {
                            $rec3["import_id"] = "il_" . IL_INST_ID . "_pg_" . $rec3["child"];
                        }
                        $rec3["type"] = "free_pg";
                        $rec3["depth"] = 0;
                        $rec3["parent"] = 0;
                        $tmp = array();
                        foreach ($rec3 as $k => $v) {
                            $tmp[$this->convertToLeadingUpper($k)]
                                = $v;
                        }
                        $this->data[] = $tmp;
                    }
                    break;
            }
        }

        if ($a_entity == "lm_menu") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                $this->getDirectDataFromQuery("SELECT lm_id, link_type, title, target, link_ref_id, active" .
                        " FROM lm_menu " .
                        " WHERE " . $ilDB->in("lm_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "lm_data_transl") {
            switch ($a_version) {
                case "5.1.0":
                case "5.4.0":
                case "8.0":
                $this->getDirectDataFromQuery("SELECT id, lang, title, short_title" .
                        " FROM lm_data_transl " .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        switch ($a_entity) {
            case "lm":
                return array(
                    "lm_tree" => array("ids" => $a_rec["Id"] ?? null),
                    "lm_menu" => array("ids" => $a_rec["Id"] ?? null)
                );

            case "lm_tree":
                if ($this->getMasterLanguageOnly()) {
                    return [];
                } else {
                    return array(
                        "lm_data_transl" => array("ids" => $a_rec["Child"] ?? null)
                    );
                }
        }

        return [];
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        //var_dump($a_rec);

        switch ($a_entity) {
            case "lm":

                if ($this->getTranslationImportMode()) {
                    return;
                }

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjLearningModule();
                    $newObj->setType("lm");
                    $newObj->create(true);
                    $newObj->createLMTree();
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setLayout($a_rec["DefaultLayout"]);
                $newObj->setPageHeader($a_rec["PageHeader"]);
                $newObj->setActiveTOC(ilUtil::yn2tf($a_rec["TocActive"]));
                $newObj->setActiveLMMenu(ilUtil::yn2tf($a_rec["LmMenuActive"]));
                $newObj->setTOCMode($a_rec["TocMode"]);
                $newObj->setActivePrintView(ilUtil::yn2tf($a_rec["PrintViewActive"]));
                $newObj->setActivePreventGlossaryAppendix(ilUtil::yn2tf($a_rec["NoGloAppendix"]));
                $newObj->setActiveNumbering(ilUtil::yn2tf($a_rec["Numbering"]));
                $newObj->setHistoryUserComments(ilUtil::yn2tf($a_rec["HistUserComments"]));
                $newObj->setPublicAccessMode($a_rec["PublicAccessMode"]);
                $newObj->setPublicNotes(ilUtil::yn2tf($a_rec["PubNotes"] ?? "n"));
                // Header Page/ Footer Page ???
                $newObj->setLayoutPerPage($a_rec["LayoutPerPage"]);
                $newObj->setRating($a_rec["Rating"]);
                $newObj->setHideHeaderFooterPrint($a_rec["HideHeadFootPrint"]);
                $newObj->setDisableDefaultFeedback($a_rec["DisableDefFeedback"]);
                $newObj->setRatingPages($a_rec["RatingPages"]);
                $newObj->setForTranslation($a_rec["ForTranslation"] ?? false);
                $newObj->setProgressIcons($a_rec["ProgrIcons"]);
                $newObj->setStoreTries($a_rec["StoreTries"]);
                $newObj->setRestrictForwardNavigation($a_rec["RestrictForwNav"]);
                if ($a_rec["HeaderPage"] > 0) {
                    $a_mapping->addMapping("Modules/LearningModule", "lm_header_page", $a_rec["HeaderPage"], "-");
                }
                if ($a_rec["FooterPage"] > 0) {
                    $a_mapping->addMapping("Modules/LearningModule", "lm_footer_page", $a_rec["FooterPage"], "-");
                }

                $newObj->update();
                $this->current_obj = $newObj;

                // activated comments
                $this->notes->domain()->activateComments($newObj->getId());
                if ($a_rec["EstimatedReadingTime"] ?? false) {
                    $this->reading_time_manager->activate($newObj->getId(), true);
                }

                $a_mapping->addMapping("Modules/LearningModule", "lm", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Modules/LearningModule", "lm_style", $newObj->getId(), $a_rec["StyleId"]);
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:lm",
                    $newObj->getId() . ":0:lm"
                );
                break;

            case "lm_tree":
                if (!$this->getTranslationImportMode()) {
                    switch ($a_rec["Type"]) {
                        case "st":
                            $parent = (int) $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Parent"]);
                            $st_obj = new ilStructureObject($this->current_obj);
                            $st_obj->setType("st");
                            $st_obj->setLMId($this->current_obj->getId());
                            $st_obj->setTitle($a_rec["Title"]);
                            $st_obj->setShortTitle($a_rec["ShortTitle"]);
                            $st_obj->setImportId($a_rec["ImportId"]);
                            $st_obj->create(true);
                            ilLMObject::putInTree($st_obj, $parent, ilTree::POS_LAST_NODE);
                            $a_mapping->addMapping(
                                "Modules/LearningModule",
                                "lm_tree",
                                $a_rec["Child"],
                                $st_obj->getId()
                            );
                            $a_mapping->addMapping(
                                "Services/MetaData",
                                "md",
                                $a_rec["LmId"] . ":" . $a_rec["Child"] . ":st",
                                $this->current_obj->getId() . ":" . $st_obj->getId() . ":st"
                            );
                            break;

                        case "pg":
                            $parent = (int) $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Parent"]);
                            $pg_obj = new ilLMPageObject($this->current_obj);
                            $pg_obj->setType("pg");
                            $pg_obj->setLMId($this->current_obj->getId());
                            $pg_obj->setTitle($a_rec["Title"]);
                            $pg_obj->setShortTitle($a_rec["ShortTitle"]);
                            $pg_obj->setImportId($a_rec["ImportId"]);
                            $pg_obj->create(true, true);
                            ilLMObject::putInTree($pg_obj, $parent, ilTree::POS_LAST_NODE);
                            $a_mapping->addMapping(
                                "Modules/LearningModule",
                                "lm_tree",
                                $a_rec["Child"],
                                $pg_obj->getId()
                            );
                            $a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_obj->getId());
                            $this->lm_log->debug("add pg map (1), old : " . $a_rec["Child"] . ", new: " . $pg_obj->getId());
                            $a_mapping->addMapping(
                                "Services/COPage",
                                "pg",
                                "lm:" . $a_rec["Child"],
                                "lm:" . $pg_obj->getId()
                            );
                            $a_mapping->addMapping(
                                "Services/MetaData",
                                "md",
                                $a_rec["LmId"] . ":" . $a_rec["Child"] . ":pg",
                                $this->current_obj->getId() . ":" . $pg_obj->getId() . ":pg"
                            );
                            break;

                        // add free pages #18976
                        case "free_pg":
                            $pg_obj = new ilLMPageObject($this->current_obj);
                            $pg_obj->setType("pg");
                            $pg_obj->setLMId($this->current_obj->getId());
                            $pg_obj->setTitle($a_rec["Title"]);
                            $pg_obj->setShortTitle($a_rec["ShortTitle"]);
                            $pg_obj->setImportId($a_rec["ImportId"]);
                            $pg_obj->create(true, true);
                            $a_mapping->addMapping(
                                "Modules/LearningModule",
                                "lm_tree",
                                $a_rec["Child"],
                                $pg_obj->getId()
                            );
                            $a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_obj->getId());
                            $this->lm_log->debug("add pg map (2), old : " . $a_rec["Child"] . ", new: " . $pg_obj->getId());
                            $a_mapping->addMapping(
                                "Services/COPage",
                                "pg",
                                "lm:" . $a_rec["Child"],
                                "lm:" . $pg_obj->getId()
                            );
                            $a_mapping->addMapping(
                                "Services/MetaData",
                                "md",
                                $a_rec["LmId"] . ":" . $a_rec["Child"] . ":pg",
                                $this->current_obj->getId() . ":" . $pg_obj->getId() . ":pg"
                            );
                            break;
                    }
                } else {
                    switch ($a_rec["Type"]) {
                        case "st":
                            //"il_inst_st_66"
                            $imp_id = explode("_", $a_rec["ImportId"]);
                            if ($imp_id[0] == "il" &&
                                (int) $imp_id[1] == (int) IL_INST_ID &&
                                $imp_id[2] == "st"
                                ) {
                                $st_id = $imp_id[3];
                                if (ilLMObject::_lookupContObjID($st_id) == $this->getTranslationLM()->getId()) {
                                    $trans = new ilLMObjTranslation($st_id, $this->getTranslationLang());
                                    $trans->setTitle($a_rec["Title"]);
                                    $trans->save();
                                    $a_mapping->addMapping(
                                        "Modules/LearningModule",
                                        "link",
                                        "il_" . $this->getCurrentInstallationId() . "_" . $a_rec["Type"] . "_" . $a_rec["Child"],
                                        $a_rec["ImportId"]
                                    );
                                }
                            }
                            // no meta-data mapping, since we do not want to import metadata
                            break;

                        case "pg":
                            //"il_inst_pg_66"
                            $imp_id = explode("_", $a_rec["ImportId"]);
                            if ($imp_id[0] == "il" &&
                                (int) $imp_id[1] == (int) IL_INST_ID &&
                                $imp_id[2] == "pg"
                            ) {
                                $pg_id = $imp_id[3];
                                if (ilLMObject::_lookupContObjID($pg_id) == $this->getTranslationLM()->getId()) {
                                    $trans = new ilLMObjTranslation($pg_id, $this->getTranslationLang());
                                    $trans->setTitle($a_rec["Title"]);
                                    $trans->save();
                                    $a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_id);
                                    $this->lm_log->debug("add pg map (3), old : " . $a_rec["Child"] . ", new: " . $pg_id);
                                    $a_mapping->addMapping(
                                        "Modules/LearningModule",
                                        "link",
                                        "il_" . $this->getCurrentInstallationId() . "_" . $a_rec["Type"] . "_" . $a_rec["Child"],
                                        $a_rec["ImportId"]
                                    );
                                    $a_mapping->addMapping(
                                        "Services/COPage",
                                        "pg",
                                        "lm:" . $a_rec["Child"],
                                        "lm:" . $pg_id
                                    );
                                }
                            }
                            // no meta-data mapping, since we do not want to import metadata
                            break;
                    }
                }
                break;

            case "lm_data_transl":
                if (!$this->getTranslationImportMode()) {
                    // save page/chapter title translation
                    $lm_obj_id = $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Id"]);
                    if ($lm_obj_id > 0) {
                        $t = new ilLMObjTranslation($lm_obj_id, $a_rec["Lang"]);
                        $t->setTitle($a_rec["Title"]);
                        $t->setShortTitle($a_rec["ShortTitle"]);
                        $t->save();
                    }
                }
                break;

            case "lm_menu":
                $lm_id = (int) $a_mapping->getMapping("Modules/LearningModule", "lm", $a_rec["LmId"]);
                if ($lm_id > 0) {
                    $lm_menu_ed = new ilLMMenuEditor();
                    $lm_menu_ed->setObjId($lm_id);
                    $lm_menu_ed->setTitle($a_rec["Title"]);
                    $lm_menu_ed->setTarget($a_rec["Target"]);
                    $lm_menu_ed->setLinkType($a_rec["LinkType"]);
                    $lm_menu_ed->setLinkRefId($a_rec["LinkRefId"]);
                    $lm_menu_ed->setActive($a_rec["Active"]);
                    $lm_menu_ed->create();
                }
                break;
        }
    }
}
