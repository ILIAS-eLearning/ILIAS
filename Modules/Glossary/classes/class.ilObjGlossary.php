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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjGlossary extends ilObject implements ilAdvancedMetaDataSubItems
{
    protected ilGlossaryDefPage $page_object;
    protected array $file_ids = [];
    protected array $mob_ids = [];
    protected bool $show_tax = false;
    protected int $style_id = 0;
    protected bool $downloads_active = false;
    protected bool $glo_menu_active = false;
    protected bool $online = false;
    protected int $snippet_length = 0;
    protected string $pres_mode = "";
    protected bool $virtual = false;
    protected string $virtual_mode = "";
    protected ilGlobalTemplateInterface $tpl;
    public array $auto_glossaries = array();
    protected ilObjUser $user;
    protected array $public_export_file = [];
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_service;


    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;
        $this->tpl = $DIC["tpl"];

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "glo";
        parent::__construct($a_id, $a_call_by_reference);
        $this->content_style_service = $DIC
            ->contentStyle()
            ->domain()
            ->styleForRefId($this->getRefId());
    }

    public function create(bool $a_upload = false): int
    {
        $id = parent::create();

        // meta data will be created by
        // import parser
        if (!$a_upload) {
            $this->createMetaData();
        }
        $this->db->insert(
            'glossary',
            array(
                'id' => array('integer', $this->getId()),
                'is_online' => array('text', 'n'),
                'virtual' => array('text', $this->getVirtualMode()),
                'pres_mode' => array('text', 'table'),
                'snippet_length' => array('integer', 200)
            )
        );

        $this->setPresentationMode("table");
        $this->setSnippetLength(200);

        $this->updateAutoGlossaries();

        return $id;
    }

    public function read(): void
    {
        parent::read();
        #		echo "Glossary<br>\n";

        $q = "SELECT * FROM glossary WHERE id = " .
            $this->db->quote($this->getId(), "integer");
        $gl_set = $this->db->query($q);
        $gl_rec = $this->db->fetchAssoc($gl_set);
        $this->setOnline(ilUtil::yn2tf($gl_rec["is_online"]));
        $this->setVirtualMode($gl_rec["virtual"]);
        if (isset($gl_rec["public_xml_file"]) && $gl_rec["public_xml_file"] != "") {
            $this->setPublicExportFile("xml", $gl_rec["public_xml_file"]);
        }
        if (isset($gl_rec["public_html_file"]) && $gl_rec["public_html_file"] != "") {
            $this->setPublicExportFile("html", $gl_rec["public_html_file"]);
        }
        $this->setActiveGlossaryMenu(ilUtil::yn2tf($gl_rec["glo_menu_active"]));
        $this->setActiveDownloads(ilUtil::yn2tf($gl_rec["downloads_active"]));
        $this->setPresentationMode($gl_rec["pres_mode"]);
        $this->setSnippetLength($gl_rec["snippet_length"]);
        $this->setShowTaxonomy($gl_rec["show_tax"]);

        // read auto glossaries
        $set = $this->db->query(
            "SELECT * FROM glo_glossaries " .
            " WHERE id = " . $this->db->quote($this->getId(), "integer")
        );
        $glos = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $glos[] = $rec["glo_id"];
        }
        $this->setAutoGlossaries($glos);
    }

    public function setVirtualMode(string $a_mode): void
    {
        switch ($a_mode) {
            case "level":
            case "subtree":
                // case "fixed":
                $this->virtual_mode = $a_mode;
                $this->virtual = true;
                break;

            default:
                $this->virtual_mode = "none";
                $this->virtual = false;
                break;
        }
    }

    public function getVirtualMode(): string
    {
        return $this->virtual_mode;
    }

    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    public function setPresentationMode(string $a_val): void
    {
        $this->pres_mode = $a_val;
    }

    public function getPresentationMode(): string
    {
        return $this->pres_mode;
    }

    /** Set definition snippet length (in overview) */
    public function setSnippetLength(int $a_val): void
    {
        $this->snippet_length = $a_val;
    }

    public function getSnippetLength(): ?int
    {
        return ($this->snippet_length > 0)
            ? $this->snippet_length
            : null;
    }

    public function setOnline(bool $a_online): void
    {
        $this->online = $a_online;
    }

    public function getOnline(): bool
    {
        return $this->online;
    }

    public static function _lookupOnline(
        int $a_id
    ): bool {
        global $DIC;

        $db = $DIC->database();

        $q = "SELECT is_online FROM glossary WHERE id = " .
            $db->quote($a_id, "integer");
        $lm_set = $db->query($q);
        $lm_rec = $db->fetchAssoc($lm_set);

        return ilUtil::yn2tf($lm_rec["is_online"]);
    }

    /**
     * Lookup glossary property
     */
    protected static function lookup(
        int $a_id,
        string $a_property
    ): string {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query("SELECT $a_property FROM glossary WHERE id = " .
            $db->quote($a_id, "integer"));
        $rec = $db->fetchAssoc($set);

        return $rec[$a_property];
    }

    public static function lookupSnippetLength(int $a_id): int
    {
        return (int) self::lookup($a_id, "snippet_length");
    }


    public function setActiveGlossaryMenu(bool $a_act_glo_menu): void
    {
        $this->glo_menu_active = $a_act_glo_menu;
    }

    public function isActiveGlossaryMenu(): bool
    {
        return $this->glo_menu_active;
    }

    public function setActiveDownloads(bool $a_down): void
    {
        $this->downloads_active = $a_down;
    }

    public function isActiveDownloads(): bool
    {
        return $this->downloads_active;
    }

    public function setShowTaxonomy(bool $a_val): void
    {
        $this->show_tax = $a_val;
    }

    public function getShowTaxonomy(): bool
    {
        return $this->show_tax;
    }

    /**
     * @param int[] $a_val
     */
    public function setAutoGlossaries(
        array $a_val
    ): void {
        $this->auto_glossaries = array();
        foreach ($a_val as $v) {
            $this->addAutoGlossary($v);
        }
    }

    public function addAutoGlossary(int $glo_id): void
    {
        if ($glo_id > 0 && ilObject::_lookupType($glo_id) == "glo" &&
            !in_array($glo_id, $this->auto_glossaries)) {
            $this->auto_glossaries[] = $glo_id;
        }
    }

    /**
     * @return int[]
     */
    public function getAutoGlossaries(): array
    {
        return $this->auto_glossaries;
    }

    public function removeAutoGlossary(
        int $a_glo_id
    ): void {
        $glo_ids = array();
        foreach ($this->getAutoGlossaries() as $g) {
            if ($g != $a_glo_id) {
                $glo_ids[] = $g;
            }
        }
        $this->setAutoGlossaries($glo_ids);
    }

    public function update(): bool
    {
        $this->updateMetaData();

        $this->db->update(
            'glossary',
            array(
                'is_online' => array('text', ilUtil::tf2yn($this->getOnline())),
                'virtual' => array('text', $this->getVirtualMode()),
                'public_xml_file' => array('text', $this->getPublicExportFile("xml")),
                'public_html_file' => array('text', $this->getPublicExportFile("html")),
                'glo_menu_active' => array('text', ilUtil::tf2yn($this->isActiveGlossaryMenu())),
                'downloads_active' => array('text', ilUtil::tf2yn($this->isActiveDownloads())),
                'pres_mode' => array('text', $this->getPresentationMode()),
                'show_tax' => array('integer', $this->getShowTaxonomy()),
                'snippet_length' => array('integer', $this->getSnippetLength())
            ),
            array(
                'id' => array('integer', $this->getId())
            )
        );

        $this->updateAutoGlossaries();
        return parent::update();
    }

    public function updateAutoGlossaries(): void
    {
        // update auto glossaries
        $this->db->manipulate(
            "DELETE FROM glo_glossaries WHERE " .
            " id = " . $this->db->quote($this->getId(), "integer")
        );
        foreach ($this->getAutoGlossaries() as $glo_id) {
            $this->db->insert(
                'glo_glossaries',
                array(
                    'id' => array('integer', $this->getId()),
                    'glo_id' => array('integer', $glo_id)
                )
            );
        }
    }

    public static function lookupAutoGlossaries(
        int $a_id
    ): array {
        global $DIC;

        $db = $DIC->database();

        // read auto glossaries
        $set = $db->query(
            "SELECT * FROM glo_glossaries " .
            " WHERE id = " . $db->quote($a_id, "integer")
        );
        $glos = array();
        while ($rec = $db->fetchAssoc($set)) {
            $glos[] = (int) $rec["glo_id"];
        }
        return $glos;
    }

    public function getTermList(
        string $searchterm = "",
        string $a_letter = "",
        string $a_def = "",
        int $a_tax_node = 0,
        bool $a_include_offline_childs = false,
        bool $a_add_amet_fields = false,
        array $a_amet_filter = null,
        bool $a_omit_virtual = false,
        bool $a_include_references = false
    ): array {
        if ($a_omit_virtual) {
            $glo_ref_ids[] = $this->getRefId();
        } else {
            $glo_ref_ids = $this->getAllGlossaryIds($a_include_offline_childs, true);
        }
        $list = ilGlossaryTerm::getTermList(
            $glo_ref_ids,
            $searchterm,
            $a_letter,
            $a_def,
            $a_tax_node,
            $a_add_amet_fields,
            $a_amet_filter,
            $a_include_references
        );
        return $list;
    }

    public function getFirstLetters(
        int $a_tax_node = 0
    ): array {
        $glo_ids = $this->getAllGlossaryIds();
        $first_letters = ilGlossaryTerm::getFirstLetters($glo_ids, $a_tax_node);
        return $first_letters;
    }

    /**
     * Get all glossary ids
     * @return int[]
     */
    public function getAllGlossaryIds(
        bool $a_include_offline_childs = false,
        bool $ids_are_ref_ids = false
    ): array {
        global $DIC;

        $tree = $DIC->repositoryTree();

        if ($this->isVirtual()) {
            $glo_ids = array();

            $virtual_mode = $this->getRefId() ? $this->getVirtualMode() : '';
            switch ($virtual_mode) {
                case "level":
                    $glo_arr = $tree->getChildsByType($tree->getParentId($this->getRefId()), "glo");
                    foreach ($glo_arr as $glo) {
                        {
                            if ($ids_are_ref_ids) {
                                $glo_ids[] = (int) $glo['child'];
                            } else {
                                $glo_ids[] = (int) $glo['obj_id'];
                            }
                        }
                    }
                    break;

                case "subtree":
                    $subtree_nodes = $tree->getSubTree($tree->getNodeData($tree->getParentId($this->getRefId())));

                    foreach ($subtree_nodes as $node) {
                        if ($node['type'] == 'glo') {
                            if ($ids_are_ref_ids) {
                                $glo_ids[] = (int) $node['child'];
                            } else {
                                $glo_ids[] = (int) $node['obj_id'];
                            }
                        }
                    }
                    break;
            }
            if (!$a_include_offline_childs) {
                $glo_ids = $this->removeOfflineGlossaries($glo_ids, $ids_are_ref_ids);
            }
            // always show entries of current glossary (if no permission is given, user will not come to the presentation screen)
            // see bug #14477
            if ($ids_are_ref_ids) {
                if (!in_array($this->getRefId(), $glo_ids)) {
                    $glo_ids[] = $this->getRefId();
                }
            } elseif (!in_array($this->getId(), $glo_ids)) {
                $glo_ids[] = $this->getId();
            }
        } elseif ($ids_are_ref_ids) {
            $glo_ids = [$this->getRefId()];
        } else {
            $glo_ids = [$this->getId()];
        }

        return $glo_ids;
    }

    /**
     * creates data directory for import files
     * (data_dir/glo_data/glo_<id>/import, depending on data
     * directory that is set in ILIAS setup/ini)
     */
    public function createImportDirectory(): void
    {
        $glo_data_dir = ilFileUtils::getDataDir() . "/glo_data";
        ilFileUtils::makeDir($glo_data_dir);
        if (!is_writable($glo_data_dir)) {
            throw new ilGlossaryException("Glossary Data Directory (" . $glo_data_dir
                . ") not writeable.");
        }

        // create glossary directory (data_dir/glo_data/glo_<id>)
        $glo_dir = $glo_data_dir . "/glo_" . $this->getId();
        ilFileUtils::makeDir($glo_dir);
        if (!is_dir($glo_dir)) {
            throw new ilGlossaryException("Creation of Glossary Directory failed.");
        }
        // create Import subdirectory (data_dir/glo_data/glo_<id>/import)
        $import_dir = $glo_dir . "/import";
        ilFileUtils::makeDir($import_dir);
        if (!is_dir($import_dir)) {
            throw new ilGlossaryException("Creation of Export Directory failed.");
        }
    }

    public function getImportDirectory(): string
    {
        $export_dir = ilFileUtils::getDataDir() . "/glo_data" . "/glo_" . $this->getId() . "/import";

        return $export_dir;
    }

    public function createExportDirectory(string $a_type = "xml"): string
    {
        return ilExport::_createExportDirectory($this->getId(), $a_type, $this->getType());
    }

    public function getExportDirectory(string $a_type = "xml"): string
    {
        return ilExport::_getExportDirectory($this->getId(), $a_type, $this->getType());
    }

    /**
     * Get export files
     */
    public function getExportFiles(): array
    {
        return ilExport::_getExportFiles($this->getId(), array("xml", "html"), $this->getType());
    }

    /**
     * specify public export file for type
     * @param	string		$a_type		type ("xml" / "html")
     * @param	string		$a_file		file name
     */
    public function setPublicExportFile(
        string $a_type,
        string $a_file
    ): void {
        $this->public_export_file[$a_type] = $a_file;
    }

    /**
     * get public export file
     * @param string $a_type type ("xml" / "html")
     */
    public function getPublicExportFile(string $a_type): string
    {
        return $this->public_export_file[$a_type] ?? "";
    }

    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        string $a_target_dir,
        ilLog $expLog
    ): void {
        // export glossary
        $attrs = array();
        $attrs["Type"] = "Glossary";
        $a_xml_writer->xmlStartTag("ContentObject", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // collect media objects
        $terms = $this->getTermList();
        $this->mob_ids = array();
        $this->file_ids = array();
        foreach ($terms as $term) {
            $defs = ilGlossaryDefinition::getDefinitionList($term["id"]);

            foreach ($defs as $def) {
                $this->page_object = new ilGlossaryDefPage($def["id"]);
                $this->page_object->buildDom();
                $this->page_object->insertInstIntoIDs(IL_INST_ID);
                $mob_ids = $this->page_object->collectMediaObjects(false);
                $file_ids = ilPCFileList::collectFileItems($this->page_object, $this->page_object->getDomDoc());
                foreach ($mob_ids as $mob_id) {
                    $this->mob_ids[$mob_id] = $mob_id;
                }
                foreach ($file_ids as $file_id) {
                    $this->file_ids[$file_id] = $file_id;
                }
            }
        }

        // export media objects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Media Objects");
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Media Objects");

        // FileItems
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export File Items");
        $this->exportFileItems($a_target_dir, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export File Items");

        // Glossary
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Glossary Items");
        $this->exportXMLGlossaryItems($a_xml_writer, $a_inst, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Glossary Items");

        $a_xml_writer->xmlEndTag("ContentObject");
    }

    public function exportXMLGlossaryItems(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        ilLog $expLog
    ): void {
        $attrs = array();
        $a_xml_writer->xmlStartTag("Glossary", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        $terms = $this->getTermList();

        // export glossary terms
        reset($terms);
        foreach ($terms as $term) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $term["obj_id"]);

            // export xml to writer object
            $glo_term = new ilGlossaryTerm($term["id"]);
            $glo_term->exportXML($a_xml_writer, $a_inst);

            unset($glo_term);
        }

        $a_xml_writer->xmlEndTag("Glossary");
    }

    public function exportXMLMetaData(
        ilXmlWriter $a_xml_writer
    ): void {
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function exportXMLMediaObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        string $a_target_dir,
        ilLog $expLog
    ): void {
        foreach ($this->mob_ids as $mob_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
            $media_obj = new ilObjMediaObject($mob_id);
            $media_obj->exportXML($a_xml_writer, $a_inst);
            $media_obj->exportFiles($a_target_dir);
            unset($media_obj);
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

    public function modifyExportIdentifier(
        string $a_tag,
        string $a_param,
        string $a_value
    ): string {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . IL_INST_ID . "_glo_" . $this->getId();
        }
        return $a_value;
    }

    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete terms
        if (!$this->isVirtual()) {
            $terms = $this->getTermList();
            foreach ($terms as $term) {
                $term_obj = new ilGlossaryTerm($term["id"]);
                $term_obj->delete();
            }
        }

        // delete term references
        $refs = new ilGlossaryTermReferences($this->getId());
        $refs->delete();

        // delete glossary data entry
        $q = "DELETE FROM glossary WHERE id = " . $this->db->quote($this->getId());
        $this->db->query($q);

        // delete meta data
        $this->deleteMetaData();

        return true;
    }

    public function getXMLZip(): string
    {
        $glo_exp = new ilGlossaryExport($this);
        return $glo_exp->buildExportFile();
    }

    public static function getDeletionDependencies(int $obj_id): array
    {
        global $DIC;

        $lng = $DIC->language();

        $dep = array();
        $sms = ilObjSAHSLearningModule::getScormModulesForGlossary($obj_id);
        foreach ($sms as $sm) {
            $lng->loadLanguageModule("content");
            $dep[$sm] = $lng->txt("glo_used_in_scorm");
        }
        return $dep;
    }

    public function getTaxonomyId(): int
    {
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->getId());
        if (count($tax_ids) > 0) {
            // glossaries handle max. one taxonomy
            return (int) $tax_ids[0];
        }
        return 0;
    }


    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($new_obj);

        $tax_ass = null;
        $new_tax_ass = null;
        $map = [];

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        //		$new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setVirtualMode($this->getVirtualMode());
        $new_obj->setPresentationMode($this->getPresentationMode());
        $new_obj->setSnippetLength($this->getSnippetLength());
        $new_obj->setAutoGlossaries($this->getAutoGlossaries());
        $new_obj->update();

        // set/copy stylesheet
        $this->content_style_service->cloneTo($new_obj->getId());

        // copy taxonomy
        if (($tax_id = $this->getTaxonomyId()) > 0) {
            // clone it
            $tax = new ilObjTaxonomy($tax_id);
            $new_tax = $tax->cloneObject(0, 0, true);
            $map = $tax->getNodeMapping();

            // assign new taxonomy to new glossary
            ilObjTaxonomy::saveUsage($new_tax->getId(), $new_obj->getId());

            $tax_ass = new ilTaxNodeAssignment("glo", $this->getId(), "term", $tax_id);
            $new_tax_ass = new ilTaxNodeAssignment("glo", $new_obj->getId(), "term", $new_tax->getId());
        }

        // copy terms
        $term_mappings = array();
        foreach (ilGlossaryTerm::getTermList([$this->getRefId()]) as $term) {
            $new_term_id = ilGlossaryTerm::_copyTerm($term["id"], $new_obj->getId());
            $term_mappings[$term["id"]] = $new_term_id;

            // copy tax node assignments
            if ($tax_id > 0) {
                $assignmts = $tax_ass->getAssignmentsOfItem($term["id"]);
                foreach ($assignmts as $a) {
                    if ($map[$a["node_id"]] > 0) {
                        $new_tax_ass->addAssignment($map[$a["node_id"]], $new_term_id);
                    }
                }
            }
        }

        // add mapping of term_ids to copy wizard options
        if (!empty($term_mappings)) {
            $cp_options->appendMapping($this->getRefId() . '_glo_terms', $term_mappings);
        }


        return $new_obj;
    }

    /**
     * Remove offline glossaries from obj id array
     */
    public function removeOfflineGlossaries(
        array $a_glo_ids,
        bool $ids_are_ref_ids = false
    ): array {
        $glo_ids = $a_glo_ids;
        if ($ids_are_ref_ids) {
            $glo_ids = array_map(static function ($id): int {
                return ilObject::_lookupObjectId($id);
            }, $a_glo_ids);
        }

        $set = $this->db->query(
            "SELECT id FROM glossary " .
            " WHERE " . $this->db->in("id", $glo_ids, false, "integer") .
            " AND is_online = " . $this->db->quote("y", "text")
        );
        $online_glo_ids = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $online_glo_ids[] = $rec["id"];
        }

        if (!$ids_are_ref_ids) {
            return $online_glo_ids;
        }

        $online_ref_ids = array_filter($a_glo_ids, static function ($ref_id) use ($online_glo_ids): bool {
            return in_array(ilObject::_lookupObjectId($ref_id), $online_glo_ids);
        });


        return $online_ref_ids;
    }

    public static function getAdvMDSubItemTitle($a_obj_id, $a_sub_type, $a_sub_id): string
    {
        global $DIC;

        $lng = $DIC->language();

        if ($a_sub_type == "term") {
            $lng->loadLanguageModule("glo");

            return $lng->txt("glo_term") . ' "' . ilGlossaryTerm::_lookGlossaryTerm($a_sub_id) . '"';
        }
        return "";
    }

    /**
     * Auto link glossary terms
     */
    public function autoLinkGlossaryTerms(
        int $a_glo_ref_id
    ): void {
        // get terms of target glossary
        $terms = ilGlossaryTerm::getTermList([$a_glo_ref_id]);

        // for each get page: get content
        $source_terms = ilGlossaryTerm::getTermList([$this->getRefId()]);
        $found_pages = array();
        foreach ($source_terms as $source_term) {
            $source_defs = ilGlossaryDefinition::getDefinitionList($source_term["id"]);

            for ($j = 0, $jMax = count($source_defs); $j < $jMax; $j++) {
                $def = $source_defs[$j];
                $pg = new ilGlossaryDefPage($def["id"]);

                $c = $pg->getXMLContent();
                foreach ($terms as $t) {
                    if (is_int(stripos($c, $t["term"]))) {
                        $found_pages[$def["id"]]["terms"][] = $t;
                        if (!isset($found_pages[$def["id"]]["page"])) {
                            $found_pages[$def["id"]]["page"] = $pg;
                        }
                    }
                }
                reset($terms);
            }
        }

        // ilPCParagraph autoLinkGlossariesPage with page and terms
        foreach ($found_pages as $id => $fp) {
            ilPCParagraph::autoLinkGlossariesPage($fp["page"], $fp["terms"]);
        }
    }

    /**
     * Is long text search supported
     */
    public function supportsLongTextQuery(): bool
    {
        return true;
    }
}
