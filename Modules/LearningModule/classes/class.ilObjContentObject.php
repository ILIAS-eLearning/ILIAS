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
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilObjContentObject extends ilObject
{
    protected \ILIAS\Notes\Service $notes;
    protected array $q_ids = [];
    protected array $mob_ids = [];
    protected array $file_ids = [];
    protected array $public_export_file = [];
    protected int $header_page = 0;
    protected int $footer_page = 0;
    protected bool $user_comments = false;
    protected bool $clean_frames = false;
    protected bool $pub_notes = false;
    protected bool $downloads_public_active = false;
    protected bool $downloads_active = false;
    protected bool $hide_header_footer_print = false;
    protected bool $prevent_glossary_appendix_active = false;
    protected bool $print_view_active = false;
    protected bool $numbering = false;
    protected bool $toc_active = false;
    protected bool $lm_menu_active = false;
    protected string $public_access_mode = '';
    protected string $toc_mode = '';
    protected bool $restrict_forw_nav = false;
    protected bool $store_tries = false;
    protected bool $progr_icons = false;
    protected bool $disable_def_feedback = false;
    protected bool $layout_per_page = false;
    protected ilObjUser $user;
    protected ilLocatorGUI $locator;
    public ilLMTree $lm_tree;
    public string $layout = '';
    public int $style_id = 0;
    public string $pg_header = '';
    public bool $online = false;
    public bool $for_translation = false;
    protected bool $rating = false;
    protected bool $rating_pages = false;
    public array $auto_glossaries = array();
    private string $import_dir = '';
    protected ilObjLearningModule $lm;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->error = $DIC["ilErr"];
        if (isset($DIC["ilLocator"])) {
            $this->locator = $DIC["ilLocator"];
        }

        $this->notes = $DIC->notes();

        // this also calls read() method! (if $a_id is set)
        parent::__construct($a_id, $a_call_by_reference);

        $this->log = ilLoggerFactory::getLogger('lm');

        /** @var ilObjLearningModule $lm */
        $lm = $this;
        $this->lm = $lm;

        $this->mob_ids = array();
        $this->file_ids = array();
        $this->q_ids = array();
        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain();
    }

    /**
     * create content object
     */
    public function create(
        bool $a_no_meta_data = false
    ): int {
        $this->setOfflineStatus(true);
        $id = parent::create();

        // meta data will be created by
        // import parser
        if (!$a_no_meta_data) {
            $this->createMetaData();
        }

        $this->createProperties();
        $this->updateAutoGlossaries();
        return $id;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $this->lm_tree = new ilLMTree($this->getId());

        $this->readProperties();

        // read auto glossaries
        $set = $ilDB->query(
            "SELECT * FROM lm_glossaries " .
            " WHERE lm_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $glos = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $glos[] = $rec["glo_id"];
        }
        $this->setAutoGlossaries($glos);
    }

    public function setLayoutPerPage(bool $a_val): void
    {
        $this->layout_per_page = $a_val;
    }

    public function getLayoutPerPage(): bool
    {
        return $this->layout_per_page;
    }

    /**
     * Set disable default feedback for questions
     */
    public function setDisableDefaultFeedback(bool $a_val): void
    {
        $this->disable_def_feedback = $a_val;
    }

    public function getDisableDefaultFeedback(): bool
    {
        return $this->disable_def_feedback;
    }

    public function setProgressIcons(bool $a_val): void
    {
        $this->progr_icons = $a_val;
    }

    public function getProgressIcons(): bool
    {
        return $this->progr_icons;
    }

    public function setStoreTries(bool $a_val): void
    {
        $this->store_tries = $a_val;
    }

    public function getStoreTries(): bool
    {
        return $this->store_tries;
    }

    public function setRestrictForwardNavigation(bool $a_val): void
    {
        $this->restrict_forw_nav = $a_val;
    }

    public function getRestrictForwardNavigation(): bool
    {
        return $this->restrict_forw_nav;
    }

    public function getTree(): ilLMTree
    {
        return $this->lm_tree;
    }

    public function update(): bool
    {
        $this->updateMetaData();
        parent::update();
        $this->updateProperties();
        $this->updateAutoGlossaries();
        return true;
    }

    public function updateAutoGlossaries(): void
    {
        $ilDB = $this->db;

        // update auto glossaries
        $ilDB->manipulate(
            "DELETE FROM lm_glossaries WHERE " .
            " lm_id = " . $ilDB->quote($this->getId(), "integer")
        );
        foreach ($this->getAutoGlossaries() as $glo_id) {
            $ilDB->manipulate("INSERT INTO lm_glossaries " .
                "(lm_id, glo_id) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($glo_id, "integer") .
                ")");
        }
    }


    /**
     * if implemented, this function should be called from an Out/GUI-Object
     */
    public function import(): void
    {
        // nothing to do. just display the dialogue in Out
    }

    public function createLMTree(): void
    {
        $this->lm_tree = new ilLMTree($this->getId(), false);
        $this->lm_tree->addTree($this->getId(), 1);
    }

    public function setAutoGlossaries(array $a_val): void
    {
        $this->auto_glossaries = array();
        if (is_array($a_val)) {
            foreach ($a_val as $v) {
                $v = (int) $v;
                if ($v > 0 && ilObject::_lookupType($v) == "glo" &&
                    !in_array($v, $this->auto_glossaries)) {
                    $this->auto_glossaries[] = $v;
                }
            }
        }
    }

    public function getAutoGlossaries(): array
    {
        return $this->auto_glossaries;
    }

    public function removeAutoGlossary(int $a_glo_id): void
    {
        $glo_ids = array();
        foreach ($this->getAutoGlossaries() as $g) {
            if ($g != $a_glo_id) {
                $glo_ids[] = $g;
            }
        }
        $this->setAutoGlossaries($glo_ids);
    }

    public function addFirstChapterAndPage(): void
    {
        $lng = $this->lng;


        $root_id = $this->lm_tree->getRootId();

        // chapter
        $chap = new ilStructureObject($this->lm);
        $chap->setType("st");
        $chap->setTitle($lng->txt("cont_new_chap"));
        $chap->setLMId($this->getId());
        $chap->create();
        ilLMObject::putInTree($chap, $root_id, ilTree::POS_FIRST_NODE);

        // page
        /** @var ilObjLearningModule $lm */
        $lm = $this;
        $page = new ilLMPageObject($lm);
        $page->setType("pg");
        $page->setTitle($lng->txt("cont_new_page"));
        $page->setLMId($this->getId());
        $page->create();
        ilLMObject::putInTree($page, $chap->getId(), ilTree::POS_FIRST_NODE);
    }

    /**
     * Set for translation (lm has been imported for translation purposes)
     */
    public function setForTranslation(bool $a_val): void
    {
        $this->for_translation = $a_val;
    }

    public function getForTranslation(): bool
    {
        return $this->for_translation;
    }

    public function getLMTree(): ilLMTree
    {
        return $this->lm_tree;
    }


    /**
     * creates data directory for import files
     * (data_dir/lm_data/lm_<id>/import, depending on data
     * directory that is set in ILIAS setup/ini)
     */
    public function createImportDirectory(): void
    {
        $ilErr = $this->error;

        $lm_data_dir = ilFileUtils::getDataDir() . "/lm_data";
        if (!is_writable($lm_data_dir)) {
            $ilErr->raiseError("Content object Data Directory (" . $lm_data_dir
                . ") not writeable.", $ilErr->FATAL);
        }

        // create learning module directory (data_dir/lm_data/lm_<id>)
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();
        ilFileUtils::makeDir($lm_dir);
        if (!is_dir($lm_dir)) {
            $ilErr->raiseError("Creation of Learning Module Directory failed.", $ilErr->FATAL);
        }

        // create import subdirectory (data_dir/lm_data/lm_<id>/import)
        $import_dir = $lm_dir . "/import";
        ilFileUtils::makeDir($import_dir);
        if (!is_dir($import_dir)) {
            $ilErr->raiseError("Creation of Import Directory failed.", $ilErr->FATAL);
        }
    }

    public function getDataDirectory(): string
    {
        return ilFileUtils::getDataDir() . "/lm_data" .
            "/lm_" . $this->getId();
    }

    public function getImportDirectory(): string
    {
        if (strlen($this->import_dir)) {
            return $this->import_dir;
        }

        $import_dir = ilFileUtils::getDataDir() . "/lm_data" .
            "/lm_" . $this->getId() . "/import";
        if (is_dir($import_dir)) {
            return $import_dir;
        }
        return "";
    }

    public function setImportDirectory(string $a_import_dir): void
    {
        $this->import_dir = $a_import_dir;
    }


    /**
     * creates data directory for export files
     * (data_dir/lm_data/lm_<id>/export, depending on data
     * directory that is set in ILIAS setup/ini)
     */
    public function createExportDirectory(
        string $a_type = "xml"
    ): void {
        $ilErr = $this->error;

        $lm_data_dir = ilFileUtils::getDataDir() . "/lm_data";
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();
        ilFileUtils::makeDirParents($lm_dir);
        if (!is_dir($lm_dir)) {
            $ilErr->raiseError("Creation of Learning Module Directory failed.", $ilErr->FATAL);
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        switch ($a_type) {
            default:		// = xml
                if (substr($a_type, 0, 4) == "html") {
                    $export_dir = $lm_dir . "/export_" . $a_type;
                } else {
                    $export_dir = $lm_dir . "/export";
                }
                break;
        }
        ilFileUtils::makeDir($export_dir);

        if (!is_dir($export_dir)) {
            $ilErr->raiseError("Creation of Export Directory failed.", $ilErr->FATAL);
        }
    }

    public function getExportDirectory(
        string $a_type = "xml"
    ): string {
        switch ($a_type) {
            default:			// = xml
                if (substr($a_type, 0, 4) == "html") {
                    $export_dir = ilFileUtils::getDataDir() . "/lm_data" . "/lm_" . $this->getId() . "/export_" . $a_type;
                } else {
                    $export_dir = ilFileUtils::getDataDir() . "/lm_data" . "/lm_" . $this->getId() . "/export";
                }
                break;
        }
        return $export_dir;
    }


    /**
     * delete learning module and all related data
     *
     * this method has been tested on may 9th 2004
     * meta data, content object data, data directory, bib items
     * learning module tree and pages have been deleted correctly as desired
     * @return bool true if all object data were removed; false if only a references were removed
     */
    public function delete(): bool
    {
        $ilDB = $this->db;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete lm object data
        /** @var ilObjLearningModule $lm */
        $lm = $this;
        ilLMObject::_deleteAllObjectData($lm);

        // delete meta data of content object
        $this->deleteMetaData();


        // delete learning module tree
        $this->lm_tree->removeTree($this->lm_tree->getTreeId());

        // delete data directory
        ilFileUtils::delDir($this->getDataDirectory());

        // delete content object record
        $q = "DELETE FROM content_object WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        // delete lm menu entries
        $q = "DELETE FROM lm_menu WHERE lm_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        // remove auto glossary entries
        $ilDB->manipulate(
            "DELETE FROM lm_glossaries WHERE " .
            " lm_id = " . $ilDB->quote($this->getId(), "integer")
        );


        return true;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $a_layout): void
    {
        $this->layout = $a_layout;
    }

    public static function writeHeaderPage(
        int $a_lm_id,
        int $a_page_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE content_object SET " .
            " header_page = " . $ilDB->quote($a_page_id, "integer") .
            " WHERE id = " . $ilDB->quote($a_lm_id, "integer")
        );
    }

    public static function writeFooterPage(
        int $a_lm_id,
        int $a_page_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE content_object SET " .
            " footer_page = " . $ilDB->quote($a_page_id, "integer") .
            " WHERE id = " . $ilDB->quote($a_lm_id, "integer")
        );
    }


    /**
     * move learning modules from one style to another
     */
    public static function _moveLMStyles(
        int $a_from_style,
        int $a_to_style
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_from_style < 0) {	// change / delete all individual styles
            $q = "SELECT stylesheet FROM content_object, style_data " .
                " WHERE content_object.stylesheet = style_data.id " .
                " AND style_data.standard = " . $ilDB->quote(0, "integer") .
                " AND content_object.stylesheet > " . $ilDB->quote(0, "integer");
            $style_set = $ilDB->query($q);
            while ($style_rec = $ilDB->fetchAssoc($style_set)) {
                // assign learning modules to new style
                $q = "UPDATE content_object SET " .
                    " stylesheet = " . $ilDB->quote($a_to_style, "integer") .
                    " WHERE stylesheet = " . $ilDB->quote($style_rec["stylesheet"], "integer");
                $ilDB->manipulate($q);

                // delete style
                $style_obj = ilObjectFactory::getInstanceByObjId($style_rec["stylesheet"]);
                $style_obj->delete();
            }
        } else {
            $q = "UPDATE content_object SET " .
                " stylesheet = " . $ilDB->quote($a_to_style, "integer") .
                " WHERE stylesheet = " . $ilDB->quote($a_from_style, "integer");
            $ilDB->manipulate($q);
        }
    }

    protected static function _lookup(
        int $a_obj_id,
        string $a_field
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT " . $a_field . " FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_obj_id, "integer");

        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return $rec[$a_field];
    }

    public static function _lookupRestrictForwardNavigation(
        int $a_obj_id
    ): string {
        return self::_lookup($a_obj_id, "restrict_forw_nav");
    }

    public static function _lookupStyleSheetId(int $a_cont_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT stylesheet FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_cont_obj_id, "integer");
        $res = $ilDB->query($q);
        $sheet = $ilDB->fetchAssoc($res);

        return (int) $sheet["stylesheet"];
    }

    public static function _lookupContObjIdByStyleId(int $a_style_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id FROM content_object " .
            " WHERE stylesheet = " . $ilDB->quote($a_style_id, "integer");
        $res = $ilDB->query($q);
        $obj_ids = array();
        while ($cont = $ilDB->fetchAssoc($res)) {
            $obj_ids[] = (int) $cont["id"];
        }
        return $obj_ids;
    }

    public static function _lookupDisableDefaultFeedback(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT disable_def_feedback FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return (bool) ($rec["disable_def_feedback"] ?? false);
    }

    public static function _lookupStoreTries(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT store_tries FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return (bool) ($rec["store_tries"] ?? false);
    }


    /**
     * gets the number of learning modules assigned to a content style
     */
    public static function _getNrOfAssignedLMs(int $a_style_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT count(*) as cnt FROM content_object " .
            " WHERE stylesheet = " . $ilDB->quote($a_style_id, "integer");
        $cset = $ilDB->query($q);
        $crow = $ilDB->fetchAssoc($cset);

        return (int) ($crow["cnt"] ?? 0);
    }


    /**
     * get number of learning modules with individual styles
     */
    public static function _getNrLMsIndividualStyles(): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        // joining with style table (not perfectly nice)
        $q = "SELECT count(*) as cnt FROM content_object, style_data " .
            " WHERE stylesheet = style_data.id " .
            " AND standard = " . $ilDB->quote(0, "integer");
        $cset = $ilDB->query($q);
        $crow = $ilDB->fetchAssoc($cset);

        return (int) $crow["cnt"];
    }

    /**
     * get number of learning modules assigned no style
     */
    public static function _getNrLMsNoStyle(): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT count(*) as cnt FROM content_object " .
            " WHERE stylesheet = " . $ilDB->quote(0, "integer");
        $cset = $ilDB->query($q);
        $crow = $ilDB->fetchAssoc($cset);

        return (int) $crow["cnt"];
    }

    /**
     * delete all style references to style
     */
    public static function _deleteStyleAssignments(
        int $a_style_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE content_object SET " .
            " stylesheet = " . $ilDB->quote(0, "integer") .
            " WHERE stylesheet = " . $ilDB->quote($a_style_id, "integer");

        $ilDB->manipulate($q);
    }

    /**
     * get page header mode (ilLMOBject::CHAPTER_TITLE | ilLMOBject::PAGE_TITLE | ilLMOBject::NO_HEADER)
     */
    public function getPageHeader(): string
    {
        return $this->pg_header;
    }

    /**
     * set page header mode
     * @param string $a_pg_header		ilLMOBject::CHAPTER_TITLE | ilLMOBject::PAGE_TITLE | ilLMOBject::NO_HEADER
     */
    public function setPageHeader(
        string $a_pg_header = ilLMObject::CHAPTER_TITLE
    ): void {
        $this->pg_header = $a_pg_header;
    }

    /**
     * get toc mode ("chapters" | "pages")
     */
    public function getTOCMode(): string
    {
        return $this->toc_mode;
    }

    /**
     * get public access mode ("complete" | "selected")
     */
    public function getPublicAccessMode(): string
    {
        return $this->public_access_mode;
    }

    /**
     * set toc mode
     * @param string $a_toc_mode		"chapters" | "pages"
     */
    public function setTOCMode(string $a_toc_mode = "chapters"): void
    {
        $this->toc_mode = $a_toc_mode;
    }

    public function setActiveLMMenu(bool $a_act_lm_menu): void
    {
        $this->lm_menu_active = $a_act_lm_menu;
    }

    public function isActiveLMMenu(): bool
    {
        return $this->lm_menu_active;
    }

    public function setActiveTOC(bool $a_toc): void
    {
        $this->toc_active = $a_toc;
    }

    public function isActiveTOC(): bool
    {
        return $this->toc_active;
    }

    public function setActiveNumbering(bool $a_num): void
    {
        $this->numbering = $a_num;
    }

    public function isActiveNumbering(): bool
    {
        return $this->numbering;
    }

    public function setActivePrintView(bool $a_print): void
    {
        $this->print_view_active = $a_print;
    }

    public function isActivePrintView(): bool
    {
        return $this->print_view_active;
    }

    public function setActivePreventGlossaryAppendix(bool $a_print): void
    {
        $this->prevent_glossary_appendix_active = $a_print;
    }

    public function isActivePreventGlossaryAppendix(): bool
    {
        return $this->prevent_glossary_appendix_active;
    }

    /**
     * Set hide header footer in print mode
     */
    public function setHideHeaderFooterPrint(bool $a_val): void
    {
        $this->hide_header_footer_print = $a_val;
    }

    public function getHideHeaderFooterPrint(): bool
    {
        return $this->hide_header_footer_print;
    }

    public function setActiveDownloads(bool $a_down): void
    {
        $this->downloads_active = $a_down;
    }

    public function isActiveDownloads(): bool
    {
        return $this->downloads_active;
    }

    public function setActiveDownloadsPublic(bool $a_down): void
    {
        $this->downloads_public_active = $a_down;
    }

    public function isActiveDownloadsPublic(): bool
    {
        return $this->downloads_public_active;
    }

    public function setPublicNotes(bool $a_pub_notes): void
    {
        $this->pub_notes = $a_pub_notes;
    }

    public function publicNotes(): bool
    {
        return $this->pub_notes;
    }

    public function setCleanFrames(bool $a_clean): void
    {
        $this->clean_frames = $a_clean;
    }

    public function cleanFrames(): bool
    {
        return $this->clean_frames;
    }

    public function setHistoryUserComments(bool $a_comm): void
    {
        $this->user_comments = $a_comm;
    }

    public function setPublicAccessMode(string $a_mode): void
    {
        $this->public_access_mode = $a_mode;
    }

    public function isActiveHistoryUserComments(): bool
    {
        return $this->user_comments;
    }

    public function setHeaderPage(int $a_pg): void
    {
        $this->header_page = $a_pg;
    }

    public function getHeaderPage(): int
    {
        return $this->header_page;
    }

    public function setFooterPage(int $a_pg): void
    {
        $this->footer_page = $a_pg;
    }

    public function getFooterPage(): int
    {
        return $this->footer_page;
    }

    public function readProperties(): void
    {
        $ilDB = $this->db;

        $q = "SELECT * FROM content_object WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $lm_set = $ilDB->query($q);
        $lm_rec = $ilDB->fetchAssoc($lm_set);
        $this->setLayout($lm_rec["default_layout"]);
        $this->setPageHeader($lm_rec["page_header"]);
        $this->setTOCMode($lm_rec["toc_mode"]);
        $this->setActiveTOC(ilUtil::yn2tf($lm_rec["toc_active"]));
        $this->setActiveNumbering(ilUtil::yn2tf($lm_rec["numbering"]));
        $this->setActivePrintView(ilUtil::yn2tf($lm_rec["print_view_active"]));
        $this->setActivePreventGlossaryAppendix(ilUtil::yn2tf($lm_rec["no_glo_appendix"]));
        $this->setHideHeaderFooterPrint($lm_rec["hide_head_foot_print"]);
        $this->setActiveDownloads(ilUtil::yn2tf($lm_rec["downloads_active"]));
        $this->setActiveDownloadsPublic(ilUtil::yn2tf($lm_rec["downloads_public_active"]));
        $this->setActiveLMMenu(ilUtil::yn2tf($lm_rec["lm_menu_active"]));
        $this->setCleanFrames(ilUtil::yn2tf($lm_rec["clean_frames"]));
        $this->setHeaderPage((int) $lm_rec["header_page"]);
        $this->setFooterPage((int) $lm_rec["footer_page"]);
        $this->setHistoryUserComments(ilUtil::yn2tf($lm_rec["hist_user_comments"]));
        $this->setPublicAccessMode($lm_rec["public_access_mode"]);
        $this->setPublicExportFile("xml", (string) $lm_rec["public_xml_file"]);
        $this->setPublicExportFile("html", (string) $lm_rec["public_html_file"]);
        $this->setLayoutPerPage((bool) $lm_rec["layout_per_page"]);
        $this->setRating($lm_rec["rating"]);
        $this->setRatingPages($lm_rec["rating_pages"]);
        $this->setDisableDefaultFeedback($lm_rec["disable_def_feedback"]);
        $this->setProgressIcons($lm_rec["progr_icons"]);
        $this->setStoreTries($lm_rec["store_tries"]);
        $this->setRestrictForwardNavigation($lm_rec["restrict_forw_nav"]);

        // #14661
        $this->setPublicNotes($this->notes->domain()->commentsActive($this->getId()));

        $this->setForTranslation($lm_rec["for_translation"]);
    }

    public function updateProperties(): void
    {
        $ilDB = $this->db;

        // force clean_frames to be set, if layout per page is activated
        if ($this->getLayoutPerPage()) {
            $this->setCleanFrames(true);
        }

        $q = "UPDATE content_object SET " .
            " default_layout = " . $ilDB->quote($this->getLayout(), "text") . ", " .
            " page_header = " . $ilDB->quote($this->getPageHeader(), "text") . "," .
            " toc_mode = " . $ilDB->quote($this->getTOCMode(), "text") . "," .
            " toc_active = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveTOC()), "text") . "," .
            " numbering = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveNumbering()), "text") . "," .
            " print_view_active = " . $ilDB->quote(ilUtil::tf2yn($this->isActivePrintView()), "text") . "," .
            " no_glo_appendix = " . $ilDB->quote(ilUtil::tf2yn($this->isActivePreventGlossaryAppendix()), "text") . "," .
            " hide_head_foot_print = " . $ilDB->quote($this->getHideHeaderFooterPrint(), "integer") . "," .
            " downloads_active = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveDownloads()), "text") . "," .
            " downloads_public_active = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveDownloadsPublic()), "text") . "," .
            " clean_frames = " . $ilDB->quote(ilUtil::tf2yn($this->cleanFrames()), "text") . "," .
            " hist_user_comments = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveHistoryUserComments()), "text") . "," .
            " public_access_mode = " . $ilDB->quote($this->getPublicAccessMode(), "text") . "," .
            " public_xml_file = " . $ilDB->quote($this->getPublicExportFile("xml"), "text") . "," .
            " public_html_file = " . $ilDB->quote($this->getPublicExportFile("html"), "text") . "," .
            " header_page = " . $ilDB->quote($this->getHeaderPage(), "integer") . "," .
            " footer_page = " . $ilDB->quote($this->getFooterPage(), "integer") . "," .
            " lm_menu_active = " . $ilDB->quote(ilUtil::tf2yn($this->isActiveLMMenu()), "text") . ", " .
            " layout_per_page = " . $ilDB->quote($this->getLayoutPerPage(), "integer") . ", " .
            " rating = " . $ilDB->quote($this->hasRating(), "integer") . ", " .
            " rating_pages = " . $ilDB->quote($this->hasRatingPages(), "integer") . ", " .
            " disable_def_feedback = " . $ilDB->quote($this->getDisableDefaultFeedback(), "integer") . ", " .
            " progr_icons = " . $ilDB->quote($this->getProgressIcons(), "integer") . ", " .
            " store_tries = " . $ilDB->quote($this->getStoreTries(), "integer") . ", " .
            " restrict_forw_nav = " . $ilDB->quote($this->getRestrictForwardNavigation(), "integer") . ", " .
            " for_translation = " . $ilDB->quote((int) $this->getForTranslation(), "integer") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);
        // #14661
        $this->notes->domain()->activateComments($this->getId());
    }

    /**
     * create new properties record
     */
    public function createProperties(): void
    {
        $ilDB = $this->db;

        $q = "INSERT INTO content_object (id) VALUES (" . $ilDB->quote($this->getId(), "integer") . ")";
        $ilDB->manipulate($q);

        // #14661
        $this->notes->domain()->activateComments($this->getId());

        $this->readProperties();		// to get db default values
    }


    /**
     * get all available lm layouts
     */
    public static function getAvailableLayouts(): array
    {
        $dir = opendir("./Modules/LearningModule/layouts/lm");

        $layouts = array();

        while ($file = readdir($dir)) {
            if ($file != "." && $file != ".." && $file != "CVS" && $file != ".svn") {
                // directories
                if (is_dir("./Modules/LearningModule/layouts/lm/" . $file)) {
                    $layouts[$file] = $file;
                }
            }
        }
        asort($layouts);

        // workaround: fix ordering
        $ret = array(
            'toc2win' => 'toc2win',
            'toc2windyn' => 'toc2windyn',
            '1window' => '1window',
            '2window' => '2window',
            '3window' => '3window',
            'presentation' => 'presentation',
            'fullscreen' => 'fullscreen'
            );

        foreach ($layouts as $l) {
            if (!in_array($l, $ret)) {
                $ret[$l] = $l;
            }
        }

        return $ret;
    }

    /**
     * checks whether the preconditions of a page are fulfilled or not
     */
    public static function _checkPreconditionsOfPage(
        int $cont_ref_id,
        int $cont_obj_id,
        int $page_id
    ): bool {
        $lm_tree = new ilTree($cont_obj_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");

        if ($lm_tree->isInTree($page_id)) {
            $path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
            foreach ($path as $node) {
                if ($node["type"] == "st") {
                    if (!ilConditionHandler::_checkAllConditionsOfTarget($cont_ref_id, $node["child"], "st")) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * gets all missing preconditions of page
     */
    public static function _getMissingPreconditionsOfPage(
        int $cont_ref_id,
        int $cont_obj_id,
        int $page_id
    ): array {
        $lm_tree = new ilTree($cont_obj_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");

        $conds = array();
        if ($lm_tree->isInTree($page_id)) {
            // get full path of page
            $path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
            foreach ($path as $node) {
                if ($node["type"] == "st") {
                    // get all preconditions of upper chapters
                    $tconds = ilConditionHandler::_getPersistedConditionsOfTarget($cont_ref_id, $node["child"], "st");
                    foreach ($tconds as $tcond) {
                        // store all missing preconditions
                        if (!ilConditionHandler::_checkCondition($tcond)) {
                            $conds[] = $tcond;
                        }
                    }
                }
            }
        }

        return $conds;
    }

    /**
     * get top chapter of page for that any precondition is missing
     */
    public static function _getMissingPreconditionsTopChapter(
        int $cont_obj_ref_id,
        int $cont_obj_id,
        int $page_id
    ): int {
        $lm_tree = new ilTree($cont_obj_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");

        if ($lm_tree->isInTree($page_id)) {
            // get full path of page
            $path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
            foreach ($path as $node) {
                if ($node["type"] == "st") {
                    // get all preconditions of upper chapters
                    $tconds = ilConditionHandler::_getPersistedConditionsOfTarget($cont_obj_ref_id, $node["child"], "st");
                    foreach ($tconds as $tcond) {
                        // look for missing precondition
                        if (!ilConditionHandler::_checkCondition($tcond)) {
                            return (int) $node["child"];
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * checks if page has a successor page
     */
    public static function hasSuccessorPage(
        int $a_cont_obj_id,
        int $a_page_id
    ): bool {
        $tree = new ilTree($a_cont_obj_id);
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");
        if ($tree->isInTree($a_page_id)) {
            $succ = $tree->fetchSuccessorNode($a_page_id, "pg");
            if ($succ > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws ilInvalidTreeStructureException
     */
    public function checkTree(): void
    {
        $tree = new ilLMTree($this->getId());
        $tree->checkTree();
        $tree->checkTreeChilds();
    }

    public function fixTree(): void
    {
        $ilDB = $this->db;

        $tree = $this->getLMTree();

        // check numbering, if errors, renumber
        // it is very important to keep this step before deleting subtrees
        // in the following steps
        $set = $ilDB->query(
            "SELECT DISTINCT l1.lm_id" .
            " FROM lm_tree l1" .
            " JOIN lm_tree l2 ON ( l1.child = l2.parent" .
            " AND l1.lm_id = l2.lm_id )" .
            " JOIN lm_data ON ( l1.child = lm_data.obj_id )" .
            " WHERE (l2.lft < l1.lft" .
            " OR l2.rgt > l1.rgt OR l2.lft > l1.rgt OR l2.rgt < l1.lft)" .
            " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY lm_data.create_date DESC"
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $tree->renumber();
        }

        // delete subtrees that have no lm_data records (changed due to #20637)
        $set = $ilDB->query("SELECT * FROM lm_tree WHERE lm_tree.lm_id = " . $ilDB->quote($this->getId(), "integer"));
        while ($node = $ilDB->fetchAssoc($set)) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["child"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if (!$obj_rec) {
                $node_data = $tree->getNodeData($node["child"]);
                $node_data["child"] = $node["child"];
                $tree->deleteTree($node_data);
            }
        }

        // delete subtrees that have pages as parent
        $nodes = $tree->getSubTree($tree->getNodeData($tree->getRootId()));
        foreach ($nodes as $node) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["parent"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if (($obj_rec["type"] ?? "") == "pg") {
                $node_data = $tree->getNodeData($node["child"]);
                if ($tree->isInTree($node["child"])) {
                    $tree->deleteTree($node_data);
                }
            }
        }

        // check for multi-references pages or chapters
        // if errors -> create copies of them here
        $set = $ilDB->query("SELECT DISTINCT l1.lm_id" .
                " FROM lm_tree l1" .
                " JOIN lm_tree l2 ON ( l1.child = l2.child AND l1.lm_id <> l2.lm_id )" .
                " JOIN lm_data ON (l1.child = lm_data.obj_id)" .
                " WHERE l1.child <> 1" .
                " AND l1.lm_id <> lm_data.lm_id" .
                " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $set = $ilDB->query("SELECT DISTINCT l1.child " .
                " FROM lm_tree l1" .
                " JOIN lm_tree l2 ON ( l1.child = l2.child AND l1.lm_id <> l2.lm_id )" .
                " JOIN lm_data ON (l1.child = lm_data.obj_id)" .
                " WHERE l1.child <> 1" .
                " AND l1.lm_id <> lm_data.lm_id" .
                " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer"));
            while ($rec = $ilDB->fetchAssoc($set)) {
                $cobj = ilLMObjectFactory::getInstance($this->lm, $rec["child"]);

                if (is_object($cobj)) {
                    if ($cobj->getType() == "pg") {
                        // make a copy of it
                        $pg_copy = $cobj->copy($this->lm);

                        // replace the child in the tree with the copy (id)
                        $ilDB->manipulate(
                            "UPDATE lm_tree SET " .
                            " child = " . $ilDB->quote($pg_copy->getId(), "integer") .
                            " WHERE child = " . $ilDB->quote($cobj->getId(), "integer") .
                            " AND lm_id = " . $ilDB->quote($this->getId(), "integer")
                        );
                    } elseif ($cobj->getType() == "st") {
                        // make a copy of it
                        $st_copy = $cobj->copy($this->lm);

                        // replace the child in the tree with the copy (id)
                        $ilDB->manipulate(
                            "UPDATE lm_tree SET " .
                            " child = " . $ilDB->quote($st_copy->getId(), "integer") .
                            " WHERE child = " . $ilDB->quote($cobj->getId(), "integer") .
                            " AND lm_id = " . $ilDB->quote($this->getId(), "integer")
                        );

                        // make all childs refer to the copy now
                        $ilDB->manipulate(
                            "UPDATE lm_tree SET " .
                            " parent = " . $ilDB->quote($st_copy->getId(), "integer") .
                            " WHERE parent = " . $ilDB->quote($cobj->getId(), "integer") .
                            " AND lm_id = " . $ilDB->quote($this->getId(), "integer")
                        );
                    }
                }
            }
        }

        // missing copage entries
        $set = $ilDB->queryF(
            "SELECT * FROM lm_data " .
            " WHERE lm_id = %s AND type = %s",
            array("integer", "text"),
            array($this->getId(), "pg")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!ilPageObject::_exists("lm", $rec["obj_id"], "-")) {
                $lm_page = new ilLMPage();
                $lm_page->setId($rec["obj_id"]);
                $lm_page->setParentId($this->getId());
                $lm_page->create(false);
            }
        }
    }

    /**
     * Check tree (this has been copied from fixTree due to a bug fixing, should be reorganised)
     */
    public function checkStructure(): array
    {
        $issues = [];
        $ilDB = $this->db;

        $tree = $this->getLMTree();

        // check numbering, if errors, renumber
        // it is very important to keep this step before deleting subtrees
        // in the following steps
        $set = $ilDB->query(
            "SELECT l1.child, l1.lft l1lft, l1.rgt l1rgt, l2.parent, l2.lft l2lft, l2.rgt l2rgt" .
            " FROM lm_tree l1" .
            " JOIN lm_tree l2 ON ( l1.child = l2.parent" .
            " AND l1.lm_id = l2.lm_id )" .
            " JOIN lm_data ON ( l1.child = lm_data.obj_id )" .
            " WHERE (l2.lft < l1.lft" .
            " OR l2.rgt > l1.rgt OR l2.lft > l1.rgt OR l2.rgt < l1.lft)" .
            " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY lm_data.create_date DESC"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $issues[] = "Tree numbering: " . print_r($rec, true);
        }

        // delete subtrees that have no lm_data records (changed due to #20637)
        $set = $ilDB->query("SELECT * FROM lm_tree WHERE lm_tree.lm_id = " . $ilDB->quote($this->getId(), "integer"));
        while ($node = $ilDB->fetchAssoc($set)) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["child"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if (!$obj_rec) {
                $issues[] = "Tree entry without data entry: " . print_r($node, true);
            }
        }

        // delete subtrees that have pages as parent
        $nodes = $tree->getSubTree($tree->getNodeData($tree->getRootId()));
        foreach ($nodes as $node) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["parent"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if (($obj_rec["type"] ?? "") == "pg") {
                $node_data = $tree->getNodeData($node["child"]);
                if ($tree->isInTree($node["child"])) {
                    $issues[] = "Subtree with page parent: " . print_r($node_data, true);
                }
            }
        }

        // check for multi-references pages or chapters
        // if errors -> create copies of them here
        $set = $ilDB->query("SELECT DISTINCT l1.lm_id" .
            " FROM lm_tree l1" .
            " JOIN lm_tree l2 ON ( l1.child = l2.child AND l1.lm_id <> l2.lm_id )" .
            " JOIN lm_data ON (l1.child = lm_data.obj_id)" .
            " WHERE l1.child <> 1" .
            " AND l1.lm_id <> lm_data.lm_id" .
            " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $set = $ilDB->query("SELECT DISTINCT l1.child " .
                " FROM lm_tree l1" .
                " JOIN lm_tree l2 ON ( l1.child = l2.child AND l1.lm_id <> l2.lm_id )" .
                " JOIN lm_data ON (l1.child = lm_data.obj_id)" .
                " WHERE l1.child <> 1" .
                " AND l1.lm_id <> lm_data.lm_id" .
                " AND l1.lm_id = " . $ilDB->quote($this->getId(), "integer"));
            while ($rec = $ilDB->fetchAssoc($set)) {
                $set3 = $ilDB->queryF(
                    "SELECT * FROM lm_tree " .
                    " WHERE child = %s ",
                    array("integer"),
                    array($rec["child"])
                );
                while ($rec3 = $ilDB->fetchAssoc($set3)) {
                    $issues[] = "Multi-reference item: " . print_r($rec3, true);
                }
            }
        }

        // missing copage entries
        $set = $ilDB->queryF(
            "SELECT * FROM lm_data " .
            " WHERE lm_id = %s AND type = %s",
            array("integer", "text"),
            array($this->getId(), "pg")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!ilPageObject::_exists("lm", $rec["obj_id"], "-")) {
                $issues[] = "Missing COPage: " . print_r($rec, true);
            }
        }


        return $issues;
    }

    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        string $a_target_dir,
        ilLog $expLog
    ): void {
        $attrs = array();
        switch ($this->getType()) {
            case "lm":
                $attrs["Type"] = "LearningModule";
                break;
        }
        $a_xml_writer->xmlStartTag("ContentObject", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // StructureObjects
        //echo "ContObj:".$a_inst.":<br>";
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Structure Objects");
        $this->exportXMLStructureObjects($a_xml_writer, $a_inst, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Structure Objects");

        // PageObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Page Objects");
        $this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Page Objects");

        // MediaObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Media Objects");
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Media Objects");

        // FileItems
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export File Items");
        $this->exportFileItems($a_target_dir, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export File Items");

        // Questions
        if (count($this->q_ids) > 0) {
            $qti_file = fopen($a_target_dir . "/qti.xml", "w");
            $pool = new ilObjQuestionPool();
            fwrite($qti_file, $pool->questionsToXML($this->q_ids));
            fclose($qti_file);
        }

        // To do: implement version selection/detection
        // Properties
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Properties");
        $this->exportXMLProperties($a_xml_writer, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Properties");

        $a_xml_writer->xmlEndTag("ContentObject");
    }

    public function exportXMLMetaData(
        ilXmlWriter $a_xml_writer
    ): void {
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function exportXMLStructureObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        ilLog $expLog
    ): void {
        $childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
        foreach ($childs as $child) {
            if ($child["type"] != "st") {
                continue;
            }

            $structure_obj = new ilStructureObject($this->lm, $child["obj_id"]);
            $structure_obj->exportXML($a_xml_writer, $a_inst, $expLog);
            unset($structure_obj);
        }
    }

    public function exportXMLPageObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        ilLog $expLog
    ): void {
        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $page) {
            if (ilLMPage::_exists($this->getType(), $page["obj_id"])) {
                $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $page["obj_id"]);

                // export xml to writer object
                $page_obj = new ilLMPageObject($this->lm, $page["obj_id"]);
                $page_obj->exportXML($a_xml_writer, "normal", $a_inst);

                // collect media objects
                $mob_ids = $page_obj->getMediaObjectIds();
                foreach ($mob_ids as $mob_id) {
                    $this->mob_ids[$mob_id] = $mob_id;
                }

                // collect all file items
                $file_ids = $page_obj->getFileItemIds();
                foreach ($file_ids as $file_id) {
                    $this->file_ids[$file_id] = $file_id;
                }

                // collect all questions
                $q_ids = $page_obj->getQuestionIds();
                foreach ($q_ids as $q_id) {
                    $this->q_ids[$q_id] = $q_id;
                }

                unset($page_obj);
            }
        }
    }

    public function exportXMLMediaObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        string $a_target_dir,
        ilLog $expLog
    ): void {
        $linked_mobs = array();

        // mobs directly embedded into pages
        foreach ($this->mob_ids as $mob_id) {
            if ($mob_id > 0 && ilObject::_lookupType($mob_id) == "mob") {
                $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                $media_obj = new ilObjMediaObject($mob_id);
                $media_obj->exportXML($a_xml_writer, $a_inst);
                $media_obj->exportFiles($a_target_dir);

                $lmobs = $media_obj->getLinkedMediaObjects($this->mob_ids);
                $linked_mobs = array_merge($linked_mobs, $lmobs);

                unset($media_obj);
            }
        }

        // linked mobs (in map areas)
        foreach ($linked_mobs as $mob_id) {
            if ($mob_id > 0) {
                $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                $media_obj = new ilObjMediaObject($mob_id);
                $media_obj->exportXML($a_xml_writer, $a_inst);
                $media_obj->exportFiles($a_target_dir);
                unset($media_obj);
            }
        }
    }

    public function exportFileItems(
        string $a_target_dir,
        ilLog $expLog
    ): void {
        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_obj = new ilObjFile($file_id, false);
            $file_obj->export($a_target_dir);
            unset($file_obj);
        }
    }

    public function exportXMLProperties(
        ilXmlWriter $a_xml_writer,
        ilLog $expLog
    ): void {
        $attrs = array();
        $a_xml_writer->xmlStartTag("Properties", $attrs);

        // Layout
        $attrs = array("Name" => "Layout", "Value" => $this->getLayout());
        $a_xml_writer->xmlElement("Property", $attrs);

        // Page Header
        $attrs = array("Name" => "PageHeader", "Value" => $this->getPageHeader());
        $a_xml_writer->xmlElement("Property", $attrs);

        // TOC Mode
        $attrs = array("Name" => "TOCMode", "Value" => $this->getTOCMode());
        $a_xml_writer->xmlElement("Property", $attrs);

        // LM Menu Activation
        $attrs = array("Name" => "ActiveLMMenu", "Value" =>
            ilUtil::tf2yn($this->isActiveLMMenu()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Numbering Activation
        $attrs = array("Name" => "ActiveNumbering", "Value" =>
            ilUtil::tf2yn($this->isActiveNumbering()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Table of contents button activation
        $attrs = array("Name" => "ActiveTOC", "Value" =>
            ilUtil::tf2yn($this->isActiveTOC()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Print view button activation
        $attrs = array("Name" => "ActivePrintView", "Value" =>
            ilUtil::tf2yn($this->isActivePrintView()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Note that download button is not saved, because
        // download files do not exist after import

        // Clean frames
        $attrs = array("Name" => "CleanFrames", "Value" =>
            ilUtil::tf2yn($this->cleanFrames()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Public notes activation
        $attrs = array("Name" => "PublicNotes", "Value" =>
            ilUtil::tf2yn($this->publicNotes()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // History comments for authors activation
        $attrs = array("Name" => "HistoryUserComments", "Value" =>
            ilUtil::tf2yn($this->isActiveHistoryUserComments()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Rating
        $attrs = array("Name" => "Rating", "Value" =>
            ilUtil::tf2yn($this->hasRating()));
        $a_xml_writer->xmlElement("Property", $attrs);
        $attrs = array("Name" => "RatingPages", "Value" =>
            ilUtil::tf2yn($this->hasRatingPages()));
        $a_xml_writer->xmlElement("Property", $attrs);

        // Header Page
        if ($this->getHeaderPage() > 0) {
            $attrs = array("Name" => "HeaderPage", "Value" =>
                "il_" . IL_INST_ID . "_pg_" . $this->getHeaderPage());
            $a_xml_writer->xmlElement("Property", $attrs);
        }

        // Footer Page
        if ($this->getFooterPage() > 0) {
            $attrs = array("Name" => "FooterPage", "Value" =>
                "il_" . IL_INST_ID . "_pg_" . $this->getFooterPage());
            $a_xml_writer->xmlElement("Property", $attrs);
        }

        // layout per page
        $attrs = array("Name" => "LayoutPerPage", "Value" =>
            $this->getLayoutPerPage());
        $a_xml_writer->xmlElement("Property", $attrs);

        // progress icons
        $attrs = array("Name" => "ProgressIcons", "Value" =>
            $this->getProgressIcons());
        $a_xml_writer->xmlElement("Property", $attrs);

        // store tries
        $attrs = array("Name" => "StoreTries", "Value" =>
            $this->getStoreTries());
        $a_xml_writer->xmlElement("Property", $attrs);

        // restrict forward navigation
        $attrs = array("Name" => "RestrictForwardNavigation", "Value" =>
            $this->getRestrictForwardNavigation());
        $a_xml_writer->xmlElement("Property", $attrs);

        // disable default feedback
        $attrs = array("Name" => "DisableDefaultFeedback", "Value" =>
            $this->getDisableDefaultFeedback());
        $a_xml_writer->xmlElement("Property", $attrs);

        $a_xml_writer->xmlEndTag("Properties");
    }

    public function getExportFiles(): array
    {
        $file = array();

        $types = array("xml", "html");

        foreach ($types as $type) {
            $dir = $this->getExportDirectory($type);
            // quit if import dir not available
            if (!is_dir($dir) or
                !is_writeable($dir)) {
                continue;
            }

            // open directory
            $cdir = dir($dir);

            // initialize array

            // get files and save the in the array
            while ($entry = $cdir->read()) {
                if ($entry != "." and
                    $entry != ".." and
                    substr($entry, -4) == ".zip" and
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(lm_)*[0-9]+\.zip\$~", $entry)) {
                    $file[$entry . $type] = array("type" => $type, "file" => $entry,
                        "size" => filesize($dir . "/" . $entry));
                }
            }

            // close import directory
            $cdir->close();
        }

        // sort files
        ksort($file);
        return $file;
    }

    /**
     * specify public export file for type
     */
    public function setPublicExportFile(
        string $a_type,
        string $a_file
    ): void {
        $this->public_export_file[$a_type] = $a_file;
    }

    public function getPublicExportFile(string $a_type): string
    {
        return $this->public_export_file[$a_type] ?? "";
    }

    public function getOfflineFiles(
        string $dir
    ): array {
        // quit if offline dir not available
        if (!is_dir($dir) or
            !is_writeable($dir)) {
            return array();
        }

        // open directory
        $dir = dir($dir);

        // initialize array
        $file = array();

        // get files and save the in the array
        while ($entry = $dir->read()) {
            if ($entry != "." and
                $entry != ".." and
                substr($entry, -4) == ".pdf" and
                preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(lm_)*[0-9]+\.pdf\$~", $entry)) {
                $file[] = $entry;
            }
        }

        // close import directory
        $dir->close();

        // sort files
        sort($file);
        return $file;
    }

    public function exportFO(
        ilXmlWriter $a_xml_writer,
        string $a_target_dir
    ): void {
        throw new ilException("Export FO is deprecated.");
        /*
        // fo:root (start)
        $attrs = array();
        $attrs["xmlns:fo"] = "http://www.w3.org/1999/XSL/Format";
        $a_xml_writer->xmlStartTag("fo:root", $attrs);

        // fo:layout-master-set (start)
        $attrs = array();
        $a_xml_writer->xmlStartTag("fo:layout-master-set", $attrs);

        // fo:simple-page-master (start)
        $attrs = array();
        $attrs["master-name"] = "DinA4";
        $attrs["page-height"] = "29.7cm";
        $attrs["page-width"] = "21cm";
        $attrs["margin-top"] = "4cm";
        $attrs["margin-bottom"] = "1cm";
        $attrs["margin-left"] = "2.8cm";
        $attrs["margin-right"] = "7.3cm";
        $a_xml_writer->xmlStartTag("fo:simple-page-master", $attrs);

        // fo:region-body (complete)
        $attrs = array();
        $attrs["margin-top"] = "0cm";
        $attrs["margin-bottom"] = "1.25cm";
        $a_xml_writer->xmlElement("fo:region-body", $attrs);

        // fo:region-before (complete)
        $attrs = array();
        $attrs["extent"] = "1cm";
        $a_xml_writer->xmlElement("fo:region-before", $attrs);

        // fo:region-after (complete)
        $attrs = array();
        $attrs["extent"] = "1cm";
        $a_xml_writer->xmlElement("fo:region-after", $attrs);

        // fo:simple-page-master (end)
        $a_xml_writer->xmlEndTag("fo:simple-page-master");

        // fo:layout-master-set (end)
        $a_xml_writer->xmlEndTag("fo:layout-master-set");

        // fo:page-sequence (start)
        $attrs = array();
        $attrs["master-reference"] = "DinA4";
        $a_xml_writer->xmlStartTag("fo:page-sequence", $attrs);

        // fo:flow (start)
        $attrs = array();
        $attrs["flow-name"] = "xsl-region-body";
        $a_xml_writer->xmlStartTag("fo:flow", $attrs);


        // StructureObjects
        $this->exportFOStructureObjects($a_xml_writer);

        // fo:flow (end)
        $a_xml_writer->xmlEndTag("fo:flow");

        // fo:page-sequence (end)
        $a_xml_writer->xmlEndTag("fo:page-sequence");

        // fo:root (end)
        $a_xml_writer->xmlEndTag("fo:root");
        */
    }

    public function exportFOStructureObjects(
        ilXmlWriter $a_xml_writer
    ): void {
        $childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
        foreach ($childs as $child) {
            if ($child["type"] != "st") {
                continue;
            }

            $structure_obj = new ilStructureObject($this->lm, $child["obj_id"]);
            $structure_obj->exportFO($a_xml_writer);
            unset($structure_obj);
        }
    }

    public function executeDragDrop(
        int $source_id,
        int $target_id,
        bool $first_child,
        bool $as_subitem = false,
        string $movecopy = "move"
    ): void {
        if ($source_id === $target_id) {
            return;
        }
        $lmtree = new ilTree($this->getId());
        $lmtree->setTableNames('lm_tree', 'lm_data');
        $lmtree->setTreeTablePK("lm_id");
        //echo "-".$source_id."-".$target_id."-".$first_child."-".$as_subitem."-";
        $source_obj = ilLMObjectFactory::getInstance($this->lm, $source_id, true);
        $source_obj->setLMId($this->getId());

        if (!$first_child) {
            $target_obj = ilLMObjectFactory::getInstance($this->lm, $target_id, true);
            $target_obj->setLMId($this->getId());
            $target_parent = $lmtree->getParentId($target_id);
        }

        // handle pages
        if ($source_obj->getType() == "pg") {
            //echo "1";
            if ($lmtree->isInTree($source_obj->getId())) {
                $node_data = $lmtree->getNodeData($source_obj->getId());

                // cut on move
                if ($movecopy == "move") {
                    $parent_id = $lmtree->getParentId($source_obj->getId());
                    $lmtree->deleteTree($node_data);

                    // write history entry
                    ilHistory::_createEntry(
                        $source_obj->getId(),
                        "cut",
                        array(ilLMObject::_lookupTitle($parent_id), $parent_id),
                        $this->getType() . ":pg"
                    );
                    ilHistory::_createEntry(
                        $parent_id,
                        "cut_page",
                        array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
                        $this->getType() . ":st"
                    );
                } else {
                    // copy page
                    $new_page = $source_obj->copy($this->lm);
                    $source_id = $new_page->getId();
                    $source_obj = $new_page;
                }

                // paste page
                if (!$lmtree->isInTree($source_obj->getId())) {
                    if ($first_child) {			// as first child
                        $target_pos = ilTree::POS_FIRST_NODE;
                        $parent = $target_id;
                    } elseif ($as_subitem) {		// as last child
                        $parent = $target_id;
                        $target_pos = ilTree::POS_FIRST_NODE;
                        $pg_childs = $lmtree->getChildsByType($parent, "pg");
                        if (count($pg_childs) != 0) {
                            $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                        }
                    } else {						// at position
                        $target_pos = $target_id;
                        $parent = $target_parent;
                    }

                    // insert page into tree
                    $lmtree->insertNode(
                        $source_obj->getId(),
                        $parent,
                        $target_pos
                    );

                    // write history entry
                    if ($movecopy == "move") {
                        // write history comments
                        ilHistory::_createEntry(
                            $source_obj->getId(),
                            "paste",
                            array(ilLMObject::_lookupTitle($parent), $parent),
                            $this->getType() . ":pg"
                        );
                        ilHistory::_createEntry(
                            $parent,
                            "paste_page",
                            array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
                            $this->getType() . ":st"
                        );
                    }
                }
            }
        }

        // handle chapters
        if ($source_obj->getType() == "st") {
            //echo "2";
            $source_node = $lmtree->getNodeData($source_id);
            $subnodes = $lmtree->getSubTree($source_node);

            // check, if target is within subtree
            foreach ($subnodes as $subnode) {
                if ($subnode["obj_id"] == $target_id) {
                    return;
                }
            }

            $target_pos = $target_id;

            if ($first_child) {		// as first subchapter
                $target_pos = ilTree::POS_FIRST_NODE;
                $target_parent = $target_id;

                $pg_childs = $lmtree->getChildsByType($target_parent, "pg");
                if (count($pg_childs) != 0) {
                    $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                }
            } elseif ($as_subitem) {		// as last subchapter
                $target_parent = $target_id;
                $target_pos = ilTree::POS_FIRST_NODE;
                $childs = $lmtree->getChilds($target_parent);
                if (count($childs) != 0) {
                    $target_pos = $childs[count($childs) - 1]["obj_id"];
                }
            }

            // delete source tree
            if ($movecopy == "move") {
                $lmtree->deleteTree($source_node);
            } else {
                // copy chapter (incl. subcontents)
                throw new ilException("ilObjContentObject: Not implemented");
                //$new_chapter = $source_obj->copy($lmtree, $target_parent, $target_pos);
            }

            if (!$lmtree->isInTree($source_id)) {
                $lmtree->insertNode($source_id, $target_parent, $target_pos);

                // insert moved tree
                foreach ($subnodes as $node) {
                    if ($node["obj_id"] != $source_id) {
                        $lmtree->insertNode($node["obj_id"], $node["parent"]);
                    }
                }
            }

            // check the tree
            $this->checkTree();
        }

        $this->checkTree();
    }

    /**
     * Validate all pages
     */
    public function validatePages(): string
    {
        $mess = "";

        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $page) {
            if (ilLMPage::_exists($this->getType(), $page["obj_id"])) {
                $cpage = new ilLMPage($page["obj_id"]);
                $cpage->buildDom();
                $error = $cpage->validateDom();

                if ($error != "") {
                    $this->lng->loadLanguageModule("content");
                    $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("cont_import_validation_errors"));
                    $title = ilLMObject::_lookupTitle($page["obj_id"]);
                    $page_obj = new ilLMPageObject($this->lm, $page["obj_id"]);
                    $mess .= $this->lng->txt("obj_pg") . ": " . $title;
                    $mess .= '<div class="small">';
                    foreach ($error as $e) {
                        $err_mess = implode(" - ", $e);
                        if (!is_int(strpos($err_mess, ":0:"))) {
                            $mess .= htmlentities($err_mess) . "<br />";
                        }
                    }
                    $mess .= '</div>';
                    $mess .= "<br />";
                }
            }
        }

        return $mess;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        /** @var ilObjLearningModule $new_obj */
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($new_obj);
        //$new_obj->createProperties();

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus($this->getOfflineStatus());
        }

        //		$new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setLayoutPerPage($this->getLayoutPerPage());
        $new_obj->setLayout($this->getLayout());
        $new_obj->setTOCMode($this->getTOCMode());
        $new_obj->setActiveLMMenu($this->isActiveLMMenu());
        $new_obj->setActiveTOC($this->isActiveTOC());
        $new_obj->setActiveNumbering($this->isActiveNumbering());
        $new_obj->setActivePrintView($this->isActivePrintView());
        $new_obj->setActivePreventGlossaryAppendix($this->isActivePreventGlossaryAppendix());
        $new_obj->setActiveDownloads($this->isActiveDownloads());
        $new_obj->setActiveDownloadsPublic($this->isActiveDownloadsPublic());
        $new_obj->setPublicNotes($this->publicNotes());
        $new_obj->setCleanFrames($this->cleanFrames());
        $new_obj->setHistoryUserComments($this->isActiveHistoryUserComments());
        $new_obj->setPublicAccessMode($this->getPublicAccessMode());
        $new_obj->setPageHeader($this->getPageHeader());
        $new_obj->setRating($this->hasRating());
        $new_obj->setRatingPages($this->hasRatingPages());
        $new_obj->setDisableDefaultFeedback($this->getDisableDefaultFeedback());
        $new_obj->setProgressIcons($this->getProgressIcons());
        $new_obj->setStoreTries($this->getStoreTries());
        $new_obj->setRestrictForwardNavigation($this->getRestrictForwardNavigation());
        $new_obj->setAutoGlossaries($this->getAutoGlossaries());

        $new_obj->update();

        $new_obj->createLMTree();

        // copy style
        $style = $this->content_style_domain->styleForObjId($this->getId());
        $style->cloneTo($new_obj->getId());

        $new_obj->update();

        // copy content
        $copied_nodes = $this->copyAllPagesAndChapters($new_obj, $copy_id);

        // page header and footer
        if ($this->getHeaderPage() > 0 && ($new_page_header = $copied_nodes[$this->getHeaderPage()]) > 0) {
            $new_obj->setHeaderPage($new_page_header);
        }
        if ($this->getFooterPage() > 0 && ($new_page_footer = $copied_nodes[$this->getFooterPage()]) > 0) {
            $new_obj->setFooterPage($new_page_footer);
        }
        $new_obj->update();

        // Copy learning progress settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);

        // copy (page) multilang settings
        $ot = ilObjectTranslation::getInstance($this->getId());
        $ot->copy($new_obj->getId());

        // copy lm menu
        $menu = new ilLMMenuEditor();
        $menu->setObjId($this->getId());
        $new_menu = new ilLMMenuEditor();
        $new_menu->setObjId($new_obj->getId());
        foreach ($menu->getMenuEntries() as $entry) {
            /*'id'		=> $row->id,
                               'title'	=> $row->title,
                               'link'	=> $row->target,
                               'type'	=> $row->link_type,
                               'ref_id'	=> $row->link_ref_id,
                               'active'*/

            $new_menu->setTarget($entry["link"]);
            $new_menu->setTitle($entry["title"]);
            $new_menu->setLinkType($entry["type"]);
            $new_menu->setLinkRefId($entry["ref_id"]);
            $new_menu->create();
            ilLMMenuEditor::writeActive($new_menu->getEntryId(), $entry["active"] == "y");
        }


        return $new_obj;
    }

    public function copyAllPagesAndChapters(
        ilObjLearningModule $a_target_obj,
        int $a_copy_id = 0
    ): array {
        $parent_id = $a_target_obj->lm_tree->readRootId();
        $time = null;

        // get all chapters of root lm
        $chapters = $this->lm_tree->getChildsByType($this->lm_tree->readRootId(), "st");
        $copied_nodes = array();
        //$time = time();
        foreach ($chapters as $chap) {
            $cid = ilLMObject::pasteTree(
                $a_target_obj,
                $chap["child"],
                $parent_id,
                ilTree::POS_LAST_NODE,
                (string) $time,
                $copied_nodes,
                true,
                $this->lm
            );
            $target = $cid;
        }

        // copy free pages
        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $p) {
            if (!$this->lm_tree->isInTree($p["obj_id"])) {
                $item = new ilLMPageObject($this->lm, $p["obj_id"]);
                $target_item = $item->copy($a_target_obj);
                $copied_nodes[$item->getId()] = $target_item->getId();
            }
        }

        // Add mapping for pages and chapters
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        foreach ($copied_nodes as $old_id => $new_id) {
            $options->appendMapping(
                $this->getRefId() . '_' . $old_id,
                $a_target_obj->getRefId() . '_' . $new_id
            );
        }

        ilLMObject::updateInternalLinks($copied_nodes);

        $a_target_obj->checkTree();

        return $copied_nodes;
    }

    public static function lookupAutoGlossaries(
        int $a_lm_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        // read auto glossaries
        $set = $ilDB->query(
            "SELECT * FROM lm_glossaries " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer")
        );
        $glos = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $glos[] = $rec["glo_id"];
        }
        return $glos;
    }

    public function autoLinkGlossaryTerms(
        int $a_glo_ref_id
    ): void {
        // get terms
        $terms = ilGlossaryTerm::getTermList([$a_glo_ref_id]);

        // each get page: get content
        $pages = ilLMPage::getAllPages($this->getType(), $this->getId());

        // determine terms that occur in the page
        $found_pages = array();
        foreach ($pages as $p) {
            $pg = new ilLMPage($p["id"]);
            $c = $pg->getXMLContent();
            foreach ($terms as $t) {
                if (is_int(stripos($c, $t["term"]))) {
                    $found_pages[$p["id"]]["terms"][] = $t;
                    if (!is_object($found_pages[$p["id"]]["page"])) {
                        $found_pages[$p["id"]]["page"] = $pg;
                    }
                }
            }
            reset($terms);
        }

        // ilPCParagraph autoLinkGlossariesPage with page and terms
        foreach ($found_pages as $id => $fp) {
            ilPCParagraph::autoLinkGlossariesPage($fp["page"], $fp["terms"]);
        }
    }


    ////
    //// Online help
    ////

    /**
     * Is module an online module
     * @return bool true, if current learning module is an online help lm
     */
    public static function isOnlineHelpModule(
        int $a_id,
        bool $a_as_obj_id = false
    ): bool {
        if (!$a_as_obj_id && $a_id > 0 && $a_id === (int) OH_REF_ID) {
            return true;
        }
        if ($a_as_obj_id && $a_id > 0 && $a_id === ilObject::_lookupObjId((int) OH_REF_ID)) {
            return true;
        }
        return false;
    }

    public function setRating(bool $a_value): void
    {
        $this->rating = $a_value;
    }

    public function hasRating(): bool
    {
        return $this->rating;
    }

    public function setRatingPages(bool $a_value): void
    {
        $this->rating_pages = $a_value;
    }

    public function hasRatingPages(): bool
    {
        return $this->rating_pages;
    }


    protected function doMDUpdateListener(
        string $a_element
    ): void {
        switch ($a_element) {
            case 'Educational':
                $obj_lp = ilObjectLP::getInstance($this->getId());
                if (in_array(
                    $obj_lp->getCurrentMode(),
                    array(ilLPObjSettings::LP_MODE_TLT, ilLPObjSettings::LP_MODE_COLLECTION_TLT)
                )) {
                    ilLPStatusWrapper::_refreshStatus($this->getId());
                }
                break;

            case 'General':

                // Update Title and description
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_gen = $md->getGeneral())) {
                    return;
                }

                $ot = ilObjectTranslation::getInstance($this->getId());
                if ($ot->getContentActivated()) {
                    $ot->setDefaultTitle($md_gen->getTitle());

                    foreach ($md_gen->getDescriptionIds() as $id) {
                        $md_des = $md_gen->getDescription($id);
                        $ot->setDefaultDescription($md_des->getDescription());
                        break;
                    }
                    $ot->save();
                }
                break;

        }
    }

    /**
     * Get public export files
     *
     * @return array array of arrays with keys "type" (html, scorm or xml), "file" (filename) and "size" in bytes, "dir_type" detailed directory type, e.g. html_de
     */
    public function getPublicExportFiles(): array
    {
        $dirs = array("xml");
        $export_files = array();

        $ot = ilObjectTranslation::getInstance($this->getId());
        if ($ot->getContentActivated()) {
            $langs = $ot->getLanguages();
            foreach ($langs as $l => $ldata) {
                $dirs[] = "html_" . $l;
            }
            $dirs[] = "html_all";
        } else {
            $dirs[] = "html";
        }

        foreach ($dirs as $dir) {
            $type = explode("_", $dir);
            $type = $type[0];
            if ($this->getPublicExportFile($type) != "") {
                if (is_file($this->getExportDirectory($dir) . "/" .
                    $this->getPublicExportFile($type))) {
                    $size = filesize($this->getExportDirectory($dir) . "/" .
                        $this->getPublicExportFile($type));
                    $export_files[] = array("type" => $type,
                        "dir_type" => $dir,
                        "file" => $this->getPublicExportFile($type),
                        "size" => $size);
                }
            }
        }

        return $export_files;
    }

    public function isInfoEnabled(): bool
    {
        return ilObjContentObjectAccess::isInfoEnabled($this->getId());
    }
}
