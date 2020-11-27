<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjGlossary
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjGlossary extends ilObject implements ilAdvancedMetaDataSubItems
{
    /**
     * @var ilTemplate
     */
    protected $tpl;


    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var array
     */
    public $auto_glossaries = array();

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        $this->error = $DIC["ilErr"];
        $this->tpl = $DIC["tpl"];

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "glo";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * create glossary object
    */
    public function create($a_upload = false)
    {
        parent::create();
        
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

        if (((int) $this->getStyleSheetId()) > 0) {
            ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());
        }
    }

    /**
    * read data of content object
    */
    public function read()
    {
        parent::read();
        #		echo "Glossary<br>\n";

        $q = "SELECT * FROM glossary WHERE id = " .
            $this->db->quote($this->getId(), "integer");
        $gl_set = $this->db->query($q);
        $gl_rec = $this->db->fetchAssoc($gl_set);
        $this->setOnline(ilUtil::yn2tf($gl_rec["is_online"]));
        $this->setVirtualMode($gl_rec["virtual"]);
        $this->setPublicExportFile("xml", $gl_rec["public_xml_file"]);
        $this->setPublicExportFile("html", $gl_rec["public_html_file"]);
        $this->setActiveGlossaryMenu(ilUtil::yn2tf($gl_rec["glo_menu_active"]));
        $this->setActiveDownloads(ilUtil::yn2tf($gl_rec["downloads_active"]));
        $this->setPresentationMode($gl_rec["pres_mode"]);
        $this->setSnippetLength($gl_rec["snippet_length"]);
        $this->setShowTaxonomy($gl_rec["show_tax"]);

        $this->setStyleSheetId((int) ilObjStyleSheet::lookupObjectStyle($this->getId()));

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

    /**
    * get description of glossary object
    *
    * @return	string		description
    */
    public function getDescription()
    {
        return parent::getDescription();
    }

    /**
    * set description of glossary object
    */
    public function setDescription($a_description)
    {
        parent::setDescription($a_description);
    }

    
    /**
    * set glossary type (virtual: fixed/level/subtree, normal:none)
    */
    public function setVirtualMode($a_mode)
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
    
    /**
    * get glossary type (normal or virtual)
    */
    public function getVirtualMode()
    {
        return $this->virtual_mode;
    }
    
    /**
     * returns true if glossary type is virtual (any mode)
     */
    public function isVirtual()
    {
        return $this->virtual;
    }

    /**
    * get title of glossary object
    *
    * @return	string		title
    */
    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
    * set title of glossary object
    */
    public function setTitle($a_title)
    {
        parent::setTitle($a_title);
        //		$this->meta_data->setTitle($a_title);
    }

    /**
     * Set presentation mode
     *
     * @param	string	presentation mode
     */
    public function setPresentationMode($a_val)
    {
        $this->pres_mode = $a_val;
    }

    /**
     * Get presentation mode
     *
     * @return	string	presentation mode
     */
    public function getPresentationMode()
    {
        return $this->pres_mode;
    }

    /**
     * Set snippet length
     *
     * @param	int	snippet length
     */
    public function setSnippetLength($a_val)
    {
        $this->snippet_length = $a_val;
    }

    /**
     * Get snippet length
     *
     * @return	int	snippet length
     */
    public function getSnippetLength()
    {
        return ($this->snippet_length > 0)
            ? $this->snippet_length
            : null;
    }

    public function setOnline($a_online)
    {
        $this->online = $a_online;
    }

    public function getOnline()
    {
        return $this->online;
    }

    /**
     * check wether content object is online
     */
    public static function _lookupOnline($a_id)
    {
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
     *
     * @param	int		glossary id
     * @param	string	property
     */
    protected static function lookup($a_id, $a_property)
    {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query("SELECT $a_property FROM glossary WHERE id = " .
            $db->quote($a_id, "integer"));
        $rec = $db->fetchAssoc($set);

        return $rec[$a_property];
    }

    /**
     * Lookup snippet length
     *
     * @param	int		glossary id
     * @return	int		snippet length
     */
    public static function lookupSnippetLength($a_id)
    {
        return ilObjGlossary::lookup($a_id, "snippet_length");
    }

    
    public function setActiveGlossaryMenu($a_act_glo_menu)
    {
        $this->glo_menu_active = $a_act_glo_menu;
    }

    public function isActiveGlossaryMenu()
    {
        return $this->glo_menu_active;
    }

    public function setActiveDownloads($a_down)
    {
        $this->downloads_active = $a_down;
    }

    public function isActiveDownloads()
    {
        return $this->downloads_active;
    }
    
    /**
     * Get ID of assigned style sheet object
     */
    public function getStyleSheetId()
    {
        return $this->style_id;
    }

    /**
     * Set ID of assigned style sheet object
     */
    public function setStyleSheetId($a_style_id)
    {
        $this->style_id = $a_style_id;
    }


    /**
     * Set show taxonomy
     *
     * @param bool $a_val show taxonomy
     */
    public function setShowTaxonomy($a_val)
    {
        $this->show_tax = $a_val;
    }
    
    /**
     * Get show taxonomy
     *
     * @return bool show taxonomy
     */
    public function getShowTaxonomy()
    {
        return $this->show_tax;
    }

    /**
     * Set auto glossaries
     *
     * @param array $a_val int
     */
    public function setAutoGlossaries($a_val)
    {
        $this->auto_glossaries = array();
        if (is_array($a_val)) {
            foreach ($a_val as $v) {
                $this->addAutoGlossary($v);
            }
        }
    }

    /**
     * Add auto glossary
     * @param int $glo_id
     */
    public function addAutoGlossary($glo_id)
    {
        $glo_id = (int) $glo_id;
        if ($glo_id > 0 && ilObject::_lookupType($glo_id) == "glo" &&
            !in_array($glo_id, $this->auto_glossaries)) {
            $this->auto_glossaries[] = $glo_id;
        }
    }

    /**
     * Get auto glossaries
     *
     * @return array int
     */
    public function getAutoGlossaries()
    {
        return $this->auto_glossaries;
    }

    /**
     * Remove auto glossary
     *
     * @param
     * @return
     */
    public function removeAutoGlossary($a_glo_id)
    {
        $glo_ids = array();
        foreach ($this->getAutoGlossaries() as $g) {
            if ($g != $a_glo_id) {
                $glo_ids[] = $g;
            }
        }
        $this->setAutoGlossaries($glo_ids);
    }

    /**
     * Update object
     */
    public function update()
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
        ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());

        $this->updateAutoGlossaries();
        parent::update();
    }


    /**
     * Update auto glossaries
     *
     * @param
     * @return
     */
    public function updateAutoGlossaries()
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

    /**
     * Lookup auto glossaries
     *
     * @param
     * @return
     */
    public static function lookupAutoGlossaries($a_id)
    {
        global $DIC;

        $db = $DIC->database();

        // read auto glossaries
        $set = $db->query(
            "SELECT * FROM glo_glossaries " .
            " WHERE id = " . $db->quote($a_id, "integer")
        );
        $glos = array();
        while ($rec = $db->fetchAssoc($set)) {
            $glos[] = $rec["glo_id"];
        }
        return $glos;
    }

    /**
    * Get term list
    */
    public function getTermList(
        $searchterm = "",
        $a_letter = "",
        $a_def = "",
        $a_tax_node = 0,
        $a_include_offline_childs = false,
        $a_add_amet_fields = false,
        array $a_amet_filter = null,
        $a_omit_virtual = false,
        $a_include_references = false
    ) {
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

    /**
    * Get term list
    */
    public function getFirstLetters($a_tax_node = 0)
    {
        $glo_ids = $this->getAllGlossaryIds();
        $first_letters = ilGlossaryTerm::getFirstLetters($glo_ids, $a_tax_node);
        return $first_letters;
    }

    /**
     * Get all glossary ids
     *
     * @param
     * @return
     */
    public function getAllGlossaryIds($a_include_offline_childs = false, $ids_are_ref_ids = false)
    {
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
                                $glo_ids[] = $glo['child'];
                            } else {
                                $glo_ids[] = $glo['obj_id'];
                            }
                        }
                    }
                    break;

                case "subtree":
                    $subtree_nodes = $tree->getSubTree($tree->getNodeData($tree->getParentId($this->getRefId())));

                    foreach ($subtree_nodes as $node) {
                        if ($node['type'] == 'glo') {
                            if ($ids_are_ref_ids) {
                                $glo_ids[] = $node['child'];
                            } else {
                                $glo_ids[] = $node['obj_id'];
                            }
                        }
                    }
                    break;
            }
            if (!$a_include_offline_childs) {
                $glo_ids = ilObjGlossary::removeOfflineGlossaries($glo_ids, $ids_are_ref_ids);
            }
            // always show entries of current glossary (if no permission is given, user will not come to the presentation screen)
            // see bug #14477
            if ($ids_are_ref_ids) {
                if (!in_array($this->getRefId(), $glo_ids)) {
                    $glo_ids[] = $this->getRefId();
                }
            } else {
                if (!in_array($this->getId(), $glo_ids)) {
                    $glo_ids[] = $this->getId();
                }
            }
        } else {
            if ($ids_are_ref_ids) {
                $glo_ids = $this->getRefId();
            } else {
                $glo_ids = $this->getId();
            }
        }
        
        return $glo_ids;
    }
    
    /**
    * creates data directory for import files
    * (data_dir/glo_data/glo_<id>/import, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createImportDirectory()
    {
        $ilErr = $this->error;

        $glo_data_dir = ilUtil::getDataDir() . "/glo_data";
        ilUtil::makeDir($glo_data_dir);
        if (!is_writable($glo_data_dir)) {
            $ilErr->raiseError("Glossary Data Directory (" . $glo_data_dir
                . ") not writeable.", $ilErr->error_obj->FATAL);
        }

        // create glossary directory (data_dir/glo_data/glo_<id>)
        $glo_dir = $glo_data_dir . "/glo_" . $this->getId();
        ilUtil::makeDir($glo_dir);
        if (!@is_dir($glo_dir)) {
            $ilErr->raiseError("Creation of Glossary Directory failed.", $ilErr->FATAL);
        }
        // create Import subdirectory (data_dir/glo_data/glo_<id>/import)
        $import_dir = $glo_dir . "/import";
        ilUtil::makeDir($import_dir);
        if (!@is_dir($import_dir)) {
            $ilErr->raiseError("Creation of Export Directory failed.", $ilErr->FATAL);
        }
    }

    /**
    * get import directory of glossary
    */
    public function getImportDirectory()
    {
        $export_dir = ilUtil::getDataDir() . "/glo_data" . "/glo_" . $this->getId() . "/import";

        return $export_dir;
    }

    /**
    * Creates export directory
    */
    public function createExportDirectory($a_type = "xml")
    {
        return ilExport::_createExportDirectory($this->getId(), $a_type, $this->getType());
    }

    /**
    * Get export directory of glossary
    */
    public function getExportDirectory($a_type = "xml")
    {
        return ilExport::_getExportDirectory($this->getId(), $a_type, $this->getType());
    }

    /**
    * Get export files
    */
    public function getExportFiles()
    {
        return ilExport::_getExportFiles($this->getId(), array("xml", "html"), $this->getType());
    }
    
    /**
    * specify public export file for type
    *
    * @param	string		$a_type		type ("xml" / "html")
    * @param	string		$a_file		file name
    */
    public function setPublicExportFile($a_type, $a_file)
    {
        $this->public_export_file[$a_type] = $a_file;
    }

    /**
    * get public export file
    *
    * @param	string		$a_type		type ("xml" / "html")
    *
    * @return	string		$a_file		file name
    */
    public function getPublicExportFile($a_type)
    {
        return $this->public_export_file[$a_type];
    }


    /**
    * export object to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
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
            $defs = ilGlossaryDefinition::getDefinitionList($term[id]);

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

    /**
    * export page objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLGlossaryItems(&$a_xml_writer, $a_inst, &$expLog)
    {
        $attrs = array();
        $a_xml_writer->xmlStartTag("Glossary", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        $terms = $this->getTermList();

        // export glossary terms
        reset($terms);
        foreach ($terms as $term) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $page["obj_id"]);

            // export xml to writer object
            $glo_term = new ilGlossaryTerm($term["id"]);
            $glo_term->exportXML($a_xml_writer, $a_inst);

            unset($glo_term);
        }

        $a_xml_writer->xmlEndTag("Glossary");
    }

    /**
    * export content objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    /**
    * export media objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
        foreach ($this->mob_ids as $mob_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
            $media_obj = new ilObjMediaObject($mob_id);
            $media_obj->exportXML($a_xml_writer, $a_inst);
            $media_obj->exportFiles($a_target_dir);
            unset($media_obj);
        }
    }

    /**
    * export files of file itmes
    *
    */
    public function exportFileItems($a_target_dir, &$expLog)
    {
        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_obj = new ilObjFile($file_id, false);
            $file_obj->export($a_target_dir);
            unset($file_obj);
        }
    }



    /**
    *
    */
    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . IL_INST_ID . "_glo_" . $this->getId();
        }

        return $a_value;
    }




    /**
    * delete glossary and all related data
    *
    * this method has been tested on may 9th 2004
    * meta data, terms, definitions, definition meta data
    * and definition pages have been deleted correctly as desired
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
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

    /**
    * Get zipped xml file for glossary.
    */
    public function getXMLZip()
    {
        $glo_exp = new ilGlossaryExport($this);
        return $glo_exp->buildExportFile();
    }

    /**
     * Get deletion dependencies
     *
     */
    public static function getDeletionDependencies($a_obj_id)
    {
        global $DIC;

        $lng = $DIC->language();
        
        $dep = array();
        $sms = ilObjSAHSLearningModule::getScormModulesForGlossary($a_obj_id);
        foreach ($sms as $sm) {
            $lng->loadLanguageModule("content");
            $dep[$sm] = $lng->txt("glo_used_in_scorm");
        }
        //echo "-".$a_obj_id."-";
        //var_dump($dep);
        return $dep;
    }
    
    /**
     * Get taxonomy
     *
     * @return int taxononmy ID
     */
    public function getTaxonomyId()
    {
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->getId());
        if (count($tax_ids) > 0) {
            // glossaries handle max. one taxonomy
            return $tax_ids[0];
        }
        return 0;
    }
    
    
    /**
     * Clone glossary
     *
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $this->cloneMetaData($new_obj);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

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
        $style_id = $this->getStyleSheetId();
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            $new_obj->setStyleSheetId($new_id);
            $new_obj->update();
        }
        
        // copy taxonomy
        if (($tax_id = $this->getTaxonomyId()) > 0) {
            // clone it
            $tax = new ilObjTaxonomy($tax_id);
            $new_tax = $tax->cloneObject(0, 0, true);
            $map = $tax->getNodeMapping();
            
            // assign new taxonomy to new glossary
            ilObjTaxonomy::saveUsage($new_tax->getId(), $new_obj->getId());
        }
        
        // assign new tax/new glossary
        // handle mapping
        
        // prepare tax node assignments objects
        if ($tax_id > 0) {
            $tax_ass = new ilTaxNodeAssignment("glo", $this->getId(), "term", $tax_id);
            $new_tax_ass = new ilTaxNodeAssignment("glo", $new_obj->getId(), "term", $new_tax->getId());
        }
        
        // copy terms
        $term_mappings = array();
        foreach (ilGlossaryTerm::getTermList($this->getRefId()) as $term) {
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
            $cp_options->appendMapping($this->getRefId() . '_glo_terms', (array) $term_mappings);
        }


        return $new_obj;
    }

    /**
     * Remove offline glossaries from obj id array
     *
     * @param
     * @return
     */
    public function removeOfflineGlossaries($a_glo_ids, $ids_are_ref_ids = false)
    {
        $glo_ids = $a_glo_ids;
        if ($ids_are_ref_ids) {
            $glo_ids = array_map(function ($id) {
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

        $online_ref_ids = array_filter($a_glo_ids, function ($ref_id) use ($online_glo_ids) {
            return in_array(ilObject::_lookupObjectId($ref_id), $online_glo_ids);
        });


        return $online_ref_ids;
    }
    
    public static function getAdvMDSubItemTitle($a_obj_id, $a_sub_type, $a_sub_id)
    {
        global $DIC;

        $lng = $DIC->language();
        
        if ($a_sub_type == "term") {
            $lng->loadLanguageModule("glo");
            
            return $lng->txt("glo_term") . ' "' . ilGlossaryTerm::_lookGlossaryTerm($a_sub_id) . '"';
        }
    }

    /**
     * Auto link glossary terms
     *
     * @param
     * @return
     */
    public function autoLinkGlossaryTerms($a_glo_ref_id)
    {
        // get terms of target glossary
        $terms = ilGlossaryTerm::getTermList($a_glo_ref_id);

        // for each get page: get content
        $source_terms = ilGlossaryTerm::getTermList($this->getRefId());
        $found_pages = array();
        foreach ($source_terms as $source_term) {
            $source_defs = ilGlossaryDefinition::getDefinitionList($source_term["id"]);

            for ($j = 0; $j < count($source_defs); $j++) {
                $def = $source_defs[$j];
                $pg = new ilGlossaryDefPage($def["id"]);

                $c = $pg->getXMLContent();
                foreach ($terms as $t) {
                    if (is_int(stripos($c, $t["term"]))) {
                        $found_pages[$def["id"]]["terms"][] = $t;
                        if (!is_object($found_pages[$def["id"]]["page"])) {
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
     *
     * @return bool
     */
    public function supportsLongTextQuery()
    {
        return true;
    }
}
