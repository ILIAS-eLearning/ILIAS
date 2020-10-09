<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/** @defgroup ModulesIliasLearningModule Modules/IliasLearningModule
 */

/**
* Class ilObjContentObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjContentObject extends ilObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    public $lm_tree;
    public $meta_data;
    public $layout;
    public $style_id;
    public $pg_header;
    public $online;
    public $for_translation = 0;
    protected $rating;
    protected $rating_pages;
    public $auto_glossaries = array();
    
    private $import_dir = '';
    /**
     * @var ilLogger
     */
    protected $log;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->error = $DIC["ilErr"];
        if (isset($DIC["tpl"])) {
            $this->tpl = $DIC["tpl"];
        }
        if (isset($DIC["ilLocator"])) {
            $this->locator = $DIC["ilLocator"];
        }

        // this also calls read() method! (if $a_id is set)
        parent::__construct($a_id, $a_call_by_reference);

        $this->log = ilLoggerFactory::getLogger('lm');

        $this->mob_ids = array();
        $this->file_ids = array();
        $this->q_ids = array();
    }

    /**
    * create content object
    */
    public function create($a_no_meta_data = false)
    {
        $this->setOfflineStatus(true);
        parent::create();
        
        // meta data will be created by
        // import parser
        if (!$a_no_meta_data) {
            $this->createMetaData();
        }

        $this->createProperties();
        $this->updateAutoGlossaries();
    }



    /**
    * read data of content object
    */
    public function read()
    {
        $ilDB = $this->db;
        
        parent::read();
        #		echo "Content<br>\n";

        $this->lm_tree = new ilTree($this->getId());
        $this->lm_tree->setTableNames('lm_tree', 'lm_data');
        $this->lm_tree->setTreeTablePK("lm_id");

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
        
        //parent::read();
    }

    /**
    * get title of content object
    *
    * @return	string		title
    */
    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
    * set title of content object
    */
    public function setTitle($a_title)
    {
        parent::setTitle($a_title);
        //		$this->meta_data->setTitle($a_title);
    }

    /**
    * get description of content object
    *
    * @return	string		description
    */
    public function getDescription()
    {
        return parent::getDescription();
    }

    /**
    * set description of content object
    */
    public function setDescription($a_description)
    {
        parent::setDescription($a_description);
        //		$this->meta_data->setDescription($a_description);
    }


    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_id)
    {
        $this->import_id = $a_id;
    }

    /**
    * Set layout per page
    *
    * @param	boolean		layout per page
    */
    public function setLayoutPerPage($a_val)
    {
        $this->layout_per_page = $a_val;
    }
    
    /**
    * Get layout per page
    *
    * @return	boolean		layout per page
    */
    public function getLayoutPerPage()
    {
        return $this->layout_per_page;
    }
    
    /**
     * Set disable default feedback for questions
     *
     * @param bool $a_val disable default feedback
     */
    public function setDisableDefaultFeedback($a_val)
    {
        $this->disable_def_feedback = $a_val;
    }
    
    /**
     * Get disable default feedback for questions
     *
     * @return bool disable default feedback
     */
    public function getDisableDefaultFeedback()
    {
        return $this->disable_def_feedback;
    }
    
    /**
     * Set progress icons
     *
     * @param bool $a_val show progress icons
     */
    public function setProgressIcons($a_val)
    {
        $this->progr_icons = $a_val;
    }
    
    /**
     * Get progress icons
     *
     * @return bool show progress icons
     */
    public function getProgressIcons()
    {
        return $this->progr_icons;
    }

    /**
     * Set store tries
     *
     * @param bool $a_val store tries
     */
    public function setStoreTries($a_val)
    {
        $this->store_tries = $a_val;
    }

    /**
     * Get store tries
     *
     * @return bool store tries
     */
    public function getStoreTries()
    {
        return $this->store_tries;
    }
    
    /**
     * Set restrict forward navigation
     *
     * @param bool $a_val restrict forward navigation
     */
    public function setRestrictForwardNavigation($a_val)
    {
        $this->restrict_forw_nav = $a_val;
    }
    
    /**
     * Get restrict forward navigation
     *
     * @return bool restrict forward navigation
     */
    public function getRestrictForwardNavigation()
    {
        return $this->restrict_forw_nav;
    }
    

    public function &getTree()
    {
        return $this->lm_tree;
    }

    /**
    * update complete object (meta data and properties)
    */
    public function update()
    {
        $this->updateMetaData();
        parent::update();
        $this->updateProperties();
        $this->updateAutoGlossaries();
    }

    /**
     * Update auto glossaries
     *
     * @param
     * @return
     */
    public function updateAutoGlossaries()
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
    public function import()
    {
        // nothing to do. just display the dialogue in Out
        return;
    }


    /**
    * put content object in main tree
    *
    */
    public function putInTree($a_parent)
    {
        $tree = $this->tree;

        // put this object in tree under $a_parent
        parent::putInTree($a_parent);

        // make new tree for this object
        //$tree->addTree($this->getId());
    }


    /**
    * create content object tree (that stores structure object hierarchie)
    *
    * todo: rename LM to ConOb
    */
    public function createLMTree()
    {
        $this->lm_tree = new ilTree($this->getId());
        $this->lm_tree->setTreeTablePK("lm_id");
        $this->lm_tree->setTableNames('lm_tree', 'lm_data');
        $this->lm_tree->addTree($this->getId(), 1);
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
                $v = (int) $v;
                if ($v > 0 && ilObject::_lookupType($v) == "glo" &&
                    !in_array($v, $this->auto_glossaries)) {
                    $this->auto_glossaries[] = $v;
                }
            }
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
     * Add first chapter and page
     */
    public function addFirstChapterAndPage()
    {
        $lng = $this->lng;
        
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
        include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        
        $root_id = $this->lm_tree->getRootId();
        
        // chapter
        $chap = new ilStructureObject($this);
        $chap->setType("st");
        $chap->setTitle($lng->txt("cont_new_chap"));
        $chap->setLMId($this->getId());
        $chap->create();
        ilLMObject::putInTree($chap, $root_id, IL_FIRST_NODE);

        // page
        $page = new ilLMPageObject($this);
        $page->setType("pg");
        $page->setTitle($lng->txt("cont_new_page"));
        $page->setLMId($this->getId());
        $page->create();
        ilLMObject::putInTree($page, $chap->getId(), IL_FIRST_NODE);
    }
    
    /**
     * Set for translation
     *
     * @param bool $a_val lm has been imported for translation purposes
     */
    public function setForTranslation($a_val)
    {
        $this->for_translation = $a_val;
    }
    
    /**
     * Get for translation
     *
     * @return bool lm has been imported for translation purposes
     */
    public function getForTranslation()
    {
        return $this->for_translation;
    }

    /**
    * get content object tree
    */
    public function &getLMTree()
    {
        return $this->lm_tree;
    }


    /**
    * creates data directory for import files
    * (data_dir/lm_data/lm_<id>/import, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createImportDirectory()
    {
        $ilErr = $this->error;

        $lm_data_dir = ilUtil::getDataDir() . "/lm_data";
        if (!is_writable($lm_data_dir)) {
            $ilErr->raiseError("Content object Data Directory (" . $lm_data_dir
                . ") not writeable.", $ilErr->FATAL);
        }

        // create learning module directory (data_dir/lm_data/lm_<id>)
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();
        ilUtil::makeDir($lm_dir);
        if (!@is_dir($lm_dir)) {
            $ilErr->raiseError("Creation of Learning Module Directory failed.", $ilErr->FATAL);
        }

        // create import subdirectory (data_dir/lm_data/lm_<id>/import)
        $import_dir = $lm_dir . "/import";
        ilUtil::makeDir($import_dir);
        if (!@is_dir($import_dir)) {
            $ilErr->raiseError("Creation of Import Directory failed.", $ilErr->FATAL);
        }
    }

    /**
    * get data directory
    */
    public function getDataDirectory()
    {
        return ilUtil::getDataDir() . "/lm_data" .
            "/lm_" . $this->getId();
    }

    /**
    * get import directory of lm
    */
    public function getImportDirectory()
    {
        if (strlen($this->import_dir)) {
            return $this->import_dir;
        }
        
        $import_dir = ilUtil::getDataDir() . "/lm_data" .
            "/lm_" . $this->getId() . "/import";
        if (@is_dir($import_dir)) {
            return $import_dir;
        } else {
            return false;
        }
    }
    
    /**
     * Set import directory for further use in ilContObjParser
     *
     * @param string import directory
     * @return void
     */
    public function setImportDirectory($a_import_dir)
    {
        $this->import_dir = $a_import_dir;
    }


    /**
    * creates data directory for export files
    * (data_dir/lm_data/lm_<id>/export, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createExportDirectory($a_type = "xml")
    {
        $ilErr = $this->error;

        $lm_data_dir = ilUtil::getDataDir() . "/lm_data";
        if (!is_writable($lm_data_dir)) {
            $ilErr->raiseError("Content object Data Directory (" . $lm_data_dir
                . ") not writeable.", $ilErr->FATAL);
        }
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();
        ilUtil::makeDir($lm_dir);
        if (!@is_dir($lm_dir)) {
            $ilErr->raiseError("Creation of Learning Module Directory failed.", $ilErr->FATAL);
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        switch ($a_type) {
            // scorm
            case "scorm":
                $export_dir = $lm_dir . "/export_scorm";
                break;

            default:		// = xml
                if (substr($a_type, 0, 4) == "html") {
                    $export_dir = $lm_dir . "/export_" . $a_type;
                } else {
                    $export_dir = $lm_dir . "/export";
                }
                break;
        }
        ilUtil::makeDir($export_dir);

        if (!@is_dir($export_dir)) {
            $ilErr->raiseError("Creation of Export Directory failed.", $ilErr->FATAL);
        }
    }

    /**
    * get export directory of lm
    */
    public function getExportDirectory($a_type = "xml")
    {
        switch ($a_type) {
            case "scorm":
                $export_dir = ilUtil::getDataDir() . "/lm_data" . "/lm_" . $this->getId() . "/export_scorm";
                break;
                
            default:			// = xml
                if (substr($a_type, 0, 4) == "html") {
                    $export_dir = ilUtil::getDataDir() . "/lm_data" . "/lm_" . $this->getId() . "/export_" . $a_type;
                } else {
                    $export_dir = ilUtil::getDataDir() . "/lm_data" . "/lm_" . $this->getId() . "/export";
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
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $ilDB = $this->db;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete lm object data
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
        ilLMObject::_deleteAllObjectData($this);

        // delete meta data of content object
        $this->deleteMetaData();


        // delete learning module tree
        $this->lm_tree->removeTree($this->lm_tree->getTreeId());

        // delete data directory
        ilUtil::delDir($this->getDataDirectory());

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


    /**
    * get default page layout of content object (see directory layouts/)
    *
    * @return	string		default layout
    */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
    * set default page layout
    *
    * @param	string		$a_layout		default page layout
    */
    public function setLayout($a_layout)
    {
        $this->layout = $a_layout;
    }

    /**
    * get ID of assigned style sheet object
    */
    public function getStyleSheetId()
    {
        return $this->style_id;
    }

    /**
    * set ID of assigned style sheet object
    */
    public function setStyleSheetId($a_style_id)
    {
        $this->style_id = $a_style_id;
    }

    /**
    * write ID of assigned style sheet object to db
    */
    public function writeStyleSheetId($a_style_id)
    {
        $ilDB = $this->db;

        $q = "UPDATE content_object SET " .
            " stylesheet = " . $ilDB->quote((int) $a_style_id, "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        $this->style_id = $a_style_id;
    }

    /**
     * Write header page
     *
     * @param int $a_lm_id learning module id
     * @param int $a_page_id page
     */
    public static function writeHeaderPage($a_lm_id, $a_page_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE content_object SET " .
            " header_page = " . $ilDB->quote($a_page_id, "integer") .
            " WHERE id = " . $ilDB->quote($a_lm_id, "integer")
        );
    }

    /**
     * Write footer page
     *
     * @param int $a_lm_id learning module id
     * @param int $a_page_id page
     */
    public static function writeFooterPage($a_lm_id, $a_page_id)
    {
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
    public static function _moveLMStyles($a_from_style, $a_to_style)
    {
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
                    " stylesheet = " . $ilDB->quote((int) $a_to_style, "integer") .
                    " WHERE stylesheet = " . $ilDB->quote($style_rec["stylesheet"], "integer");
                $ilDB->manipulate($q);
                
                // delete style
                $style_obj = ilObjectFactory::getInstanceByObjId($style_rec["stylesheet"]);
                $style_obj->delete();
            }
        } else {
            $q = "UPDATE content_object SET " .
                " stylesheet = " . $ilDB->quote((int) $a_to_style, "integer") .
                " WHERE stylesheet = " . $ilDB->quote($a_from_style, "integer");
            $ilDB->manipulate($q);
        }
    }

    /**
     * Lookup property
     *
     * @param int $a_obj_id object id
     * @param string $a_field property field name
     * @return string property value
     */
    protected static function _lookup($a_obj_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];

        $q = "SELECT " . $a_field . " FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_obj_id, "integer");

        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return $rec[$a_field];
    }

    /**
     * Lookup forward restriction navigation
     *
     * @param int $a_obj_id object id
     * @return string property value
     */
    public static function _lookupRestrictForwardNavigation($a_obj_id)
    {
        return self::_lookup($a_obj_id, "restrict_forw_nav");
    }

    /**
    * lookup style sheet ID
    */
    public static function _lookupStyleSheetId($a_cont_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT stylesheet FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_cont_obj_id, "integer");
        $res = $ilDB->query($q);
        $sheet = $ilDB->fetchAssoc($res);

        return $sheet["stylesheet"];
    }
    
    /**
    * lookup style sheet ID
    */
    public static function _lookupContObjIdByStyleId($a_style_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id FROM content_object " .
            " WHERE stylesheet = " . $ilDB->quote($a_style_id, "integer");
        $res = $ilDB->query($q);
        $obj_ids = array();
        while ($cont = $ilDB->fetchAssoc($res)) {
            $obj_ids[] = $cont["id"];
        }
        return $obj_ids;
    }

    /**
     * Lookup disable default feedback
     */
    public static function _lookupDisableDefaultFeedback($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT disable_def_feedback FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return $rec["disable_def_feedback"];
    }

    /**
     * Lookup disable default feedback
     */
    public static function _lookupStoreTries($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT store_tries FROM content_object " .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($res);

        return $rec["store_tries"];
    }


    /**
    * gets the number of learning modules assigned to a content style
    *
    * @param	int		$a_style_id		style id
    */
    public static function _getNrOfAssignedLMs($a_style_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT count(*) as cnt FROM content_object " .
            " WHERE stylesheet = " . $ilDB->quote($a_style_id, "integer");
        $cset = $ilDB->query($q);
        $crow = $ilDB->fetchAssoc($cset);

        return (int) $crow["cnt"];
    }
    
    
    /**
    * get number of learning modules with individual styles
    */
    public static function _getNrLMsIndividualStyles()
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
    public static function _getNrLMsNoStyle()
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
    *
    * @param	int		$a_style_id		style_id
    */
    public static function _deleteStyleAssignments($a_style_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "UPDATE content_object SET " .
            " stylesheet = " . $ilDB->quote(0, "integer") .
            " WHERE stylesheet = " . $ilDB->quote((int) $a_style_id, "integer");

        $ilDB->manipulate($q);
    }

    /**
    * get page header mode (IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER)
    */
    public function getPageHeader()
    {
        return $this->pg_header;
    }

    /**
    * set page header mode
    *
    * @param string $a_pg_header		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
    */
    public function setPageHeader($a_pg_header = IL_CHAPTER_TITLE)
    {
        $this->pg_header = $a_pg_header;
    }

    /**
    * get toc mode ("chapters" | "pages")
    */
    public function getTOCMode()
    {
        return $this->toc_mode;
    }
    
    /**
    * get public access mode ("complete" | "selected")
    */
    public function getPublicAccessMode()
    {
        return $this->public_access_mode;
    }

    /**
    * set toc mode
    *
    * @param string $a_toc_mode		"chapters" | "pages"
    */
    public function setTOCMode($a_toc_mode = "chapters")
    {
        $this->toc_mode = $a_toc_mode;
    }

    public function setActiveLMMenu($a_act_lm_menu)
    {
        $this->lm_menu_active = $a_act_lm_menu;
    }

    public function isActiveLMMenu()
    {
        return $this->lm_menu_active;
    }

    public function setActiveTOC($a_toc)
    {
        $this->toc_active = $a_toc;
    }

    public function isActiveTOC()
    {
        return $this->toc_active;
    }

    public function setActiveNumbering($a_num)
    {
        $this->numbering = $a_num;
    }

    public function isActiveNumbering()
    {
        return $this->numbering;
    }

    public function setActivePrintView($a_print)
    {
        $this->print_view_active = $a_print;
    }

    public function isActivePrintView()
    {
        return $this->print_view_active;
    }

    public function setActivePreventGlossaryAppendix($a_print)
    {
        $this->prevent_glossary_appendix_active = $a_print;
    }
    
    public function isActivePreventGlossaryAppendix()
    {
        return $this->prevent_glossary_appendix_active;
    }
    
    /**
     * Set hide header footer in print mode
     *
     * @param bool $a_val hide header and footer?
     */
    public function setHideHeaderFooterPrint($a_val)
    {
        $this->hide_header_footer_print = $a_val;
    }
    
    /**
     * Get hide header footer in print mode
     *
     * @return bool hide header and footer?
     */
    public function getHideHeaderFooterPrint()
    {
        return $this->hide_header_footer_print;
    }

    public function setActiveDownloads($a_down)
    {
        $this->downloads_active = $a_down;
    }
    
    public function isActiveDownloads()
    {
        return $this->downloads_active;
    }
    
    public function setActiveDownloadsPublic($a_down)
    {
        $this->downloads_public_active = $a_down;
    }
    
    public function isActiveDownloadsPublic()
    {
        return $this->downloads_public_active;
    }

    public function setPublicNotes($a_pub_notes)
    {
        $this->pub_notes = $a_pub_notes;
    }

    public function publicNotes()
    {
        return $this->pub_notes;
    }
    
    public function setCleanFrames($a_clean)
    {
        $this->clean_frames = $a_clean;
    }

    public function cleanFrames()
    {
        return $this->clean_frames;
    }
    
    public function setHistoryUserComments($a_comm)
    {
        $this->user_comments = $a_comm;
    }

    public function setPublicAccessMode($a_mode)
    {
        $this->public_access_mode = $a_mode;
    }

    public function isActiveHistoryUserComments()
    {
        return $this->user_comments;
    }

    public function setHeaderPage($a_pg)
    {
        $this->header_page = $a_pg;
    }
    
    public function getHeaderPage()
    {
        return $this->header_page;
    }
    
    public function setFooterPage($a_pg)
    {
        $this->footer_page = $a_pg;
    }
    
    public function getFooterPage()
    {
        return $this->footer_page;
    }

    /**
    * read content object properties
    */
    public function readProperties()
    {
        $ilDB = $this->db;
        
        $q = "SELECT * FROM content_object WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $lm_set = $ilDB->query($q);
        $lm_rec = $ilDB->fetchAssoc($lm_set);
        $this->setLayout($lm_rec["default_layout"]);
        $this->setStyleSheetId((int) $lm_rec["stylesheet"]);
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
        $this->setPublicExportFile("xml", $lm_rec["public_xml_file"]);
        $this->setPublicExportFile("html", $lm_rec["public_html_file"]);
        $this->setPublicExportFile("scorm", $lm_rec["public_scorm_file"]);
        $this->setLayoutPerPage($lm_rec["layout_per_page"]);
        $this->setRating($lm_rec["rating"]);
        $this->setRatingPages($lm_rec["rating_pages"]);
        $this->setDisableDefaultFeedback($lm_rec["disable_def_feedback"]);
        $this->setProgressIcons($lm_rec["progr_icons"]);
        $this->setStoreTries($lm_rec["store_tries"]);
        $this->setRestrictForwardNavigation($lm_rec["restrict_forw_nav"]);
        
        // #14661
        include_once("./Services/Notes/classes/class.ilNote.php");
        $this->setPublicNotes(ilNote::commentsActivated($this->getId(), 0, $this->getType()));

        $this->setForTranslation($lm_rec["for_translation"]);
    }

    /**
    * Update content object properties
    */
    public function updateProperties()
    {
        $ilDB = $this->db;
        
        // force clean_frames to be set, if layout per page is activated
        if ($this->getLayoutPerPage()) {
            $this->setCleanFrames(true);
        }
        
        $q = "UPDATE content_object SET " .
            " default_layout = " . $ilDB->quote($this->getLayout(), "text") . ", " .
            " stylesheet = " . $ilDB->quote($this->getStyleSheetId(), "integer") . "," .
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
            " public_scorm_file = " . $ilDB->quote($this->getPublicExportFile("scorm"), "text") . "," .
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
        include_once("./Services/Notes/classes/class.ilNote.php");
        ilNote::activateComments($this->getId(), 0, $this->getType(), $this->publicNotes());
    }

    /**
    * create new properties record
    */
    public function createProperties()
    {
        $ilDB = $this->db;
        
        $q = "INSERT INTO content_object (id) VALUES (" . $ilDB->quote($this->getId(), "integer") . ")";
        $ilDB->manipulate($q);
        
        // #14661
        include_once("./Services/Notes/classes/class.ilNote.php");
        ilNote::activateComments($this->getId(), 0, $this->getType(), true);
        
        $this->readProperties();		// to get db default values
    }


    /**
    * get all available lm layouts
    */
    public static function getAvailableLayouts()
    {
        $dir = opendir("./Modules/LearningModule/layouts/lm");

        $layouts = array();

        while ($file = readdir($dir)) {
            if ($file != "." && $file != ".." && $file != "CVS" && $file != ".svn") {
                // directories
                if (@is_dir("./Modules/LearningModule/layouts/lm/" . $file)) {
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
    * checks wether the preconditions of a page are fulfilled or not
    */
    public static function _checkPreconditionsOfPage($cont_ref_id, $cont_obj_id, $page_id)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilErr = $DIC["ilErr"];

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
    public static function _getMissingPreconditionsOfPage($cont_ref_id, $cont_obj_id, $page_id)
    {
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
    public static function _getMissingPreconditionsTopChapter($cont_obj_ref_id, $cont_obj_id, $page_id)
    {
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
                    $tconds = ilConditionHandler::_getPersistedConditionsOfTarget($cont_obj_ref_id, $node["child"], "st");
                    foreach ($tconds as $tcond) {
                        // look for missing precondition
                        if (!ilConditionHandler::_checkCondition($tcond)) {
                            return $node["child"];
                        }
                    }
                }
            }
        }
        
        return "";
    }

    /**
    * checks if page has a successor page
    */
    public static function hasSuccessorPage($a_cont_obj_id, $a_page_id)
    {
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

    
    public function checkTree()
    {
        $tree = new ilTree($this->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");
        $tree->checkTree();
        $tree->checkTreeChilds();
        //echo "checked";
    }

    /**
    * fix tree
    */
    public function fixTree()
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
        $nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));
        foreach ($nodes as $node) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["parent"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if ($obj_rec["type"] == "pg") {
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
            include_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
            while ($rec = $ilDB->fetchAssoc($set)) {
                $cobj = ilLMObjectFactory::getInstance($this, $rec["child"]);

                if (is_object($cobj)) {
                    if ($cobj->getType() == "pg") {
                        // make a copy of it
                        $pg_copy = $cobj->copy($this);
                        
                        // replace the child in the tree with the copy (id)
                        $ilDB->manipulate(
                            "UPDATE lm_tree SET " .
                            " child = " . $ilDB->quote($pg_copy->getId(), "integer") .
                            " WHERE child = " . $ilDB->quote($cobj->getId(), "integer") .
                            " AND lm_id = " . $ilDB->quote($this->getId(), "integer")
                        );
                    } elseif ($cobj->getType() == "st") {
                        // make a copy of it
                        $st_copy = $cobj->copy($this);
                        
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
                $lm_page->create();
            }
        }
    }

    /**
     * Check tree (this has been copied from fixTree due to a bug fixing, should be reorganised)
     */
    public function checkStructure()
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
        $nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));
        foreach ($nodes as $node) {
            $q = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($node["parent"], "integer");
            $obj_set = $ilDB->query($q);
            $obj_rec = $ilDB->fetchAssoc($obj_set);
            if ($obj_rec["type"] == "pg") {
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
            include_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
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

    /**
    * export object to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
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
            include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
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

    /**
    * export content objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    /**
    * export structure objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
    {
        include_once './Modules/LearningModule/classes/class.ilStructureObject.php';

        $childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
        foreach ($childs as $child) {
            if ($child["type"] != "st") {
                continue;
            }

            $structure_obj = new ilStructureObject($this, $child["obj_id"]);
            $structure_obj->exportXML($a_xml_writer, $a_inst, $expLog);
            unset($structure_obj);
        }
    }


    /**
    * export page objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog)
    {
        include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";
        include_once "./Modules/LearningModule/classes/class.ilLMPage.php";

        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $page) {
            if (ilLMPage::_exists($this->getType(), $page["obj_id"])) {
                $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $page["obj_id"]);
    
                // export xml to writer object
                $page_obj = new ilLMPageObject($this, $page["obj_id"]);
                $page_obj->exportXML($a_xml_writer, "normal", $a_inst);
    
                // collect media objects
                $mob_ids = $page_obj->getMediaObjectIDs();
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

    /**
    * export media objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

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

    /**
    * export files of file itmes
    *
    */
    public function exportFileItems($a_target_dir, &$expLog)
    {
        include_once("./Modules/File/classes/class.ilObjFile.php");

        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_obj = new ilObjFile($file_id, false);
            $file_obj->export($a_target_dir);
            unset($file_obj);
        }
    }

    /**
    * export properties of content object
    *
    */
    public function exportXMLProperties($a_xml_writer, &$expLog)
    {
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

    /**
    * get export files
    */
    public function getExportFiles()
    {
        $file = array();
        
        $types = array("xml", "html", "scorm");
        
        foreach ($types as $type) {
            $dir = $this->getExportDirectory($type);
            // quit if import dir not available
            if (!@is_dir($dir) or
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
        reset($file);
        return $file;
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
    * get offline files
    */
    public function getOfflineFiles($dir)
    {
        // quit if offline dir not available
        if (!@is_dir($dir) or
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
        reset($file);

        return $file;
    }
    
    /**
    * export scorm package
    */
    public function exportSCORM($a_target_dir, $log)
    {
        ilUtil::delDir($a_target_dir);
        ilUtil::makeDir($a_target_dir);
        //ilUtil::makeDir($a_target_dir."/res");
        
        // export everything to html
        $this->exportHTML($a_target_dir . "/res", $log, false, "scorm");
        
        // build manifest file
        include("./Modules/LearningModule/classes/class.ilLMContObjectManifestBuilder.php");
        $man_builder = new ilLMContObjectManifestBuilder($this);
        $man_builder->buildManifest();
        $man_builder->dump($a_target_dir);
        
        // copy scorm 1.2 schema definitions
        copy("Modules/LearningModule/scorm_xsd/adlcp_rootv1p2.xsd", $a_target_dir . "/adlcp_rootv1p2.xsd");
        copy("Modules/LearningModule/scorm_xsd/imscp_rootv1p1p2.xsd", $a_target_dir . "/imscp_rootv1p1p2.xsd");
        copy("Modules/LearningModule/scorm_xsd/imsmd_rootv1p2p1.xsd", $a_target_dir . "/imsmd_rootv1p2p1.xsd");
        copy("Modules/LearningModule/scorm_xsd/ims_xml.xsd", $a_target_dir . "/ims_xml.xsd");

        // zip it all
        $date = time();
        $zip_file = $a_target_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->getType() . "_" . $this->getId() . ".zip";
        //echo "zip-".$a_target_dir."-to-".$zip_file;
        ilUtil::zip(array($a_target_dir . "/res",
            $a_target_dir . "/imsmanifest.xml",
            $a_target_dir . "/adlcp_rootv1p2.xsd",
            $a_target_dir . "/imscp_rootv1p1p2.xsd",
            $a_target_dir . "/ims_xml.xsd",
            $a_target_dir . "/imsmd_rootv1p2p1.xsd"), $zip_file);

        $dest_file = $this->getExportDirectory("scorm") . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->getType() . "_" . $this->getId() . ".zip";
        
        rename($zip_file, $dest_file);
        ilUtil::delDir($a_target_dir);
    }

    
    /**
    * export html package
    */
    public function exportHTML($a_target_dir, $log, $a_zip_file = true, $a_export_format = "html", $a_lang = "")
    {
        $tpl = $this->tpl;
        $ilLocator = $this->locator;
        $ilUser = $this->user;

        $user_lang = $ilUser->getLanguage();

        // initialize temporary target directory
        ilUtil::delDir($a_target_dir);
        ilUtil::makeDir($a_target_dir);
        $mob_dir = $a_target_dir . "/mobs";
        ilUtil::makeDir($mob_dir);
        $file_dir = $a_target_dir . "/files";
        ilUtil::makeDir($file_dir);
        $teximg_dir = $a_target_dir . "/teximg";
        ilUtil::makeDir($teximg_dir);
        $style_dir = $a_target_dir . "/style";
        ilUtil::makeDir($style_dir);
        $style_img_dir = $a_target_dir . "/style/images";
        ilUtil::makeDir($style_img_dir);
        $content_style_dir = $a_target_dir . "/content_style";
        ilUtil::makeDir($content_style_dir);
        $content_style_img_dir = $a_target_dir . "/content_style/images";
        ilUtil::makeDir($content_style_img_dir);

        // init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

        // export system style sheet
        $location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
        $style_name = $ilUser->prefs["style"] . ".css";
        copy($location_stylesheet, $style_dir . "/" . $style_name);
        $fh = fopen($location_stylesheet, "r");
        $css = fread($fh, filesize($location_stylesheet));
        preg_match_all("/url\(([^\)]*)\)/", $css, $files);
        foreach (array_unique($files[1]) as $fileref) {
            $css_fileref = str_replace(array("'", '"'), "", $fileref);
            $fileref = dirname($location_stylesheet) . "/" . $css_fileref;
            if (is_file($fileref)) {
                //echo "<br>make dir: ".dirname($style_dir."/".$css_fileref);
                ilUtil::makeDirParents(dirname($style_dir . "/" . $css_fileref));
                //echo "<br>copy: ".$fileref." TO ".$style_dir."/".$css_fileref;
                copy($fileref, $style_dir . "/" . $css_fileref);
            }
        }
        fclose($fh);
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        
        // export content style sheet
        if ($this->getStyleSheetId() < 1) {
            $cont_stylesheet = "./Services/COPage/css/content.css";
            
            $css = fread(fopen($cont_stylesheet, 'r'), filesize($cont_stylesheet));
            preg_match_all("/url\(([^\)]*)\)/", $css, $files);
            foreach (array_unique($files[1]) as $fileref) {
                $target_fileref = str_replace("..", ".", $fileref);
                $target_fileref = str_replace('"', "", $target_fileref);
                if (is_file($target_fileref)) {
                    copy($target_fileref, $content_style_img_dir . "/" . basename($target_fileref));
                }
                $css = str_replace($fileref, "images/" . basename($target_fileref), $css);
            }
            fwrite(fopen($content_style_dir . "/content.css", 'w'), $css);
        } else {
            $style = new ilObjStyleSheet($this->getStyleSheetId());
            $style->writeCSSFile($content_style_dir . "/content.css", "images");
            $style->copyImagesToDir($content_style_img_dir);
        }

        // export syntax highlighting style
        $syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
        copy($syn_stylesheet, $a_target_dir . "/syntaxhighlight.css");

        // get learning module presentation gui class
        include_once("./Modules/LearningModule/classes/class.ilLMPresentationGUI.php");
        $_GET["cmd"] = "nop";
        $get_transl = $_GET["transl"];
        $_GET["transl"] = "";
        $lm_gui = new ilLMPresentationGUI();
        $lm_gui->setOfflineMode(true, ($a_lang == "all"));
        $lm_gui->setOfflineDirectory($a_target_dir);
        $lm_gui->setExportFormat($a_export_format);

        $ot = ilObjectTranslation::getInstance($this->getId());
        $langs = array();
        if ($a_lang != "all") {
            $langs = array($a_lang);
        } else {
            $ot_langs = $ot->getLanguages();
            foreach ($ot_langs as $otl) {
                $langs[] = $otl["lang_code"];
            }
        }

        // init collector arrays
        $this->offline_mobs = array();
        $this->offline_int_links = array();
        $this->offline_files = array();

        // iterate all languages
        foreach ($langs as $lang) {
            if ($lang != "") {
                $ilUser->setLanguage($lang);
                $ilUser->setCurrentLanguage($lang);
            } else {
                $ilUser->setLanguage($user_lang);
                $ilUser->setCurrentLanguage($user_lang);
            }

            if ($lang != "") {
                if ($lang == $ot->getMasterLanguage()) {
                    $lm_gui->lang = "";
                } else {
                    $lm_gui->lang = $lang;
                }
            }

            // export pages
            // now: forward ("all" info to export files and links)
            $this->exportHTMLPages($lm_gui, $a_target_dir, $lm_gui->lang, ($a_lang == "all"));

            // export table of contents
            $ilLocator->clearItems();
            if ($this->isActiveTOC()) {
                $tpl = new ilTemplate("tpl.main.html", true, true);

                $GLOBALS["tpl"] = $tpl;

                $lm_gui->tpl = $tpl;
                $content = $lm_gui->showTableOfContents();
                //var_dump($content); exit;
                if ($a_lang == "all") {
                    $file = $a_target_dir . "/table_of_contents_" . $lang . ".html";
                } else {
                    $file = $a_target_dir . "/table_of_contents.html";
                }

                // open file
                if (!($fp = @fopen($file, "w+"))) {
                    die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                        " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
                }
                chmod($file, 0770);
                fwrite($fp, $content);
                fclose($fp);
            }
        }

        // export glossary terms
        $this->exportHTMLGlossaryTerms($lm_gui, $a_target_dir);

        // export all media objects
        $linked_mobs = array();
        foreach ($this->offline_mobs as $mob) {
            if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob") {
                $this->exportHTMLMOB($a_target_dir, $lm_gui, $mob, "_blank", $linked_mobs);
            }
        }
        $linked_mobs2 = array();				// mobs linked in link areas
        foreach ($linked_mobs as $mob) {
            if (ilObject::_exists($mob)) {
                $this->exportHTMLMOB($a_target_dir, $lm_gui, $mob, "_blank", $linked_mobs2);
            }
        }
        $_GET["obj_type"] = "MediaObject";
        $_GET["obj_id"] = $a_mob_id;
        $_GET["cmd"] = "";

        // export all file objects
        foreach ($this->offline_files as $file) {
            $this->exportHTMLFile($a_target_dir, $file);
        }

        // export questions (images)
        if (count($this->q_ids) > 0) {
            foreach ($this->q_ids as $q_id) {
                ilUtil::makeDirParents($a_target_dir . "/assessment/0/" . $q_id . "/images");
                ilUtil::rCopy(
                    ilUtil::getWebspaceDir() . "/assessment/0/" . $q_id . "/images",
                    $a_target_dir . "/assessment/0/" . $q_id . "/images"
                );
            }
        }

        // export images
        $image_dir = $a_target_dir . "/images";
        ilUtil::makeDir($image_dir);
        ilUtil::makeDir($image_dir . "/browser");
        copy(
            ilUtil::getImagePath("enlarge.svg", false, "filesystem"),
            $image_dir . "/enlarge.svg"
        );
        copy(
            ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir . "/browser/plus.png"
        );
        copy(
            ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir . "/browser/minus.png"
        );
        copy(
            ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir . "/browser/blank.png"
        );
        copy(
            ilUtil::getImagePath("spacer.png", false, "filesystem"),
            $image_dir . "/spacer.png"
        );
        copy(
            ilUtil::getImagePath("icon_st.svg", false, "filesystem"),
            $image_dir . "/icon_st.svg"
        );
        copy(
            ilUtil::getImagePath("icon_pg.svg", false, "filesystem"),
            $image_dir . "/icon_pg.svg"
        );
        copy(
            ilUtil::getImagePath("icon_lm.svg", false, "filesystem"),
            $image_dir . "/icon_lm.svg"
        );
        copy(
            ilUtil::getImagePath("nav_arr_L.png", false, "filesystem"),
            $image_dir . "/nav_arr_L.png"
        );
        copy(
            ilUtil::getImagePath("nav_arr_R.png", false, "filesystem"),
            $image_dir . "/nav_arr_R.png"
        );

        // export flv/mp3 player
        $services_dir = $a_target_dir . "/Services";
        ilUtil::makeDir($services_dir);
        $media_service_dir = $services_dir . "/MediaObjects";
        ilUtil::makeDir($media_service_dir);
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        $flv_dir = $a_target_dir . "/" . ilPlayerUtil::getFlashVideoPlayerDirectory();
        ilUtil::makeDirParents($flv_dir);
        $mp3_dir = $media_service_dir . "/flash_mp3_player";
        ilUtil::makeDir($mp3_dir);
        //		copy(ilPlayerUtil::getFlashVideoPlayerFilename(true),
        //			$flv_dir."/".ilPlayerUtil::getFlashVideoPlayerFilename());
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($flv_dir);
        include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");
        ilExplorerBaseGUI::createHTMLExportDirs($a_target_dir);
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($flv_dir);

        // js files
        ilUtil::makeDir($a_target_dir . '/js');
        ilUtil::makeDir($a_target_dir . '/js/yahoo');
        ilUtil::makeDir($a_target_dir . '/css');
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        foreach (self::getSupplyingExportFiles($a_target_dir) as $f) {
            if ($f["source"] != "") {
                ilUtil::makeDirParents(dirname($f["target"]));
                copy($f["source"], $f["target"]);
            }
        }
        // template workaround: reset of template
        $tpl = new ilTemplate("tpl.main.html", true, true);
        $tpl->setVariable("LOCATION_STYLESHEET", $location_stylesheet);
        $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

        if ($a_lang != "") {
            $ilUser->setLanguage($user_lang);
            $ilUser->setCurrentLanguage($user_lang);
        }

        // zip everything
        if ($a_zip_file) {
            if ($a_lang == "") {
                $zip_target_dir = $this->getExportDirectory("html");
            } else {
                $zip_target_dir = $this->getExportDirectory("html_" . $a_lang);
                ilUtil::makeDir($zip_target_dir);
            }

            // zip it all
            $date = time();
            $zip_file = $zip_target_dir . "/" . $date . "__" . IL_INST_ID . "__" .
                $this->getType() . "_" . $this->getId() . ".zip";
            //echo "-".$a_target_dir."-".$zip_file."-"; exit;
            ilUtil::zip($a_target_dir, $zip_file);
            ilUtil::delDir($a_target_dir);
        }
    }

    /**
     * Get supplying export files
     *
     * @param
     * @return
     */
    public static function getSupplyingExportFiles($a_target_dir = ".")
    {
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");
        $scripts = array(
            array("source" => ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'),
                "target" => $a_target_dir . '/js/yahoo/yahoo-min.js',
                "type" => "js"),
            array("source" => ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'),
                "target" => $a_target_dir . '/js/yahoo/yahoo-dom-event.js',
                "type" => "js"),
            array("source" => ilYuiUtil::getLocalPath('animation/animation-min.js'),
                "target" => $a_target_dir . '/js/yahoo/animation-min.js',
                "type" => "js"),
            array("source" => './Services/JavaScript/js/Basic.js',
                "target" => $a_target_dir . '/js/Basic.js',
                "type" => "js"),
            array("source" => './Services/Accordion/js/accordion.js',
                "target" => $a_target_dir . '/js/accordion.js',
                "type" => "js"),
            array("source" => './Services/Accordion/css/accordion.css',
                "target" => $a_target_dir . '/css/accordion.css',
                "type" => "css"),
            array("source" => iljQueryUtil::getLocaljQueryPath(),
                "target" => $a_target_dir . '/js/jquery.js',
                "type" => "js"),
            array("source" => iljQueryUtil::getLocalMaphilightPath(),
                "target" => $a_target_dir . '/js/maphilight.js',
                "type" => "js"),
            array("source" => iljQueryUtil::getLocaljQueryUIPath(),
                "target" => $a_target_dir . '/js/jquery-ui-min.js',
                "type" => "js"),
            array("source" => './Services/COPage/js/ilCOPagePres.js',
                "target" => $a_target_dir . '/js/ilCOPagePres.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/scripts/questions/pure.js',
                "target" => $a_target_dir . '/js/pure.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/scripts/questions/question_handling.js',
                "target" => $a_target_dir . '/js/question_handling.js',
                "type" => "js"),
            array("source" => './Modules/TestQuestionPool/js/ilMatchingQuestion.js',
                "target" => $a_target_dir . '/js/ilMatchingQuestion.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/templates/default/question_handling.css',
                "target" => $a_target_dir . '/css/question_handling.css',
                "type" => "css"),
            array("source" => './Modules/TestQuestionPool/templates/default/test_javascript.css',
                "target" => $a_target_dir . '/css/test_javascript.css',
                "type" => "css"),
            array("source" => './Modules/TestQuestionPool/js/ilAssMultipleChoice.js',
                "target" => $a_target_dir . '/js/ilAssMultipleChoice.js',
                "type" => "js"),
            array("source" => ilPlayerUtil::getLocalMediaElementJsPath(),
                "target" => $a_target_dir . "/" . ilPlayerUtil::getLocalMediaElementJsPath(),
                "type" => "js"),
            array("source" => ilPlayerUtil::getLocalMediaElementCssPath(),
                "target" => $a_target_dir . "/" . ilPlayerUtil::getLocalMediaElementCssPath(),
                "type" => "css"),
            array("source" => ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "target" => $a_target_dir . "/" . ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "type" => "js"),
            array("source" => ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "target" => $a_target_dir . "/" . ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "type" => "js"),
            array("source" => ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "target" => $a_target_dir . "/" . ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "type" => "css"),
            array("source" => './Modules/LearningModule/js/LearningModule.js',
                "target" => $a_target_dir . '/js/LearningModule.js',
                "type" => "js")
        );
        
        $mathJaxSetting = new ilSetting("MathJax");
        $use_mathjax = $mathJaxSetting->get("enable");
        if ($use_mathjax) {
            $scripts[] = array("source" => "",
                "target" => $mathJaxSetting->get("path_to_mathjax"),
                "type" => "js");
        }

        // auto linking js
        include_once("./Services/Link/classes/class.ilLinkifyUtil.php");
        foreach (ilLinkifyUtil::getLocalJsPaths() as $p) {
            if (is_int(strpos($p, "ExtLink"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/ilExtLink.js',
                    "type" => "js");
            }
            if (is_int(strpos($p, "linkify.min.js"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/linkify.min.js',
                    "type" => "js");
            }
            if (is_int(strpos($p, "linkify-jquery.min.js"))) {
                $scripts[] = array("source" => $p,
                                   "target" => $a_target_dir . '/js/linkify-jquery.min.js',
                                   "type" => "js");
            }
        }

        return $scripts;
    }
    
    /**
    * export file object
    */
    public function exportHTMLFile($a_target_dir, $a_file_id)
    {
        $file_dir = $a_target_dir . "/files/file_" . $a_file_id;
        ilUtil::makeDir($file_dir);
        include_once("./Modules/File/classes/class.ilObjFile.php");
        $file_obj = new ilObjFile($a_file_id, false);
        $source_file = $file_obj->getDirectory($file_obj->getVersion()) . "/" . $file_obj->getFileName();
        if (!is_file($source_file)) {
            $source_file = $file_obj->getDirectory() . "/" . $file_obj->getFileName();
        }
        if (is_file($source_file)) {
            copy($source_file, $file_dir . "/" . $file_obj->getFileName());
        }
    }

    /**
    * export media object to html
    */
    public function exportHTMLMOB($a_target_dir, &$a_lm_gui, $a_mob_id, $a_frame, &$a_linked_mobs)
    {
        $tpl = $this->tpl;

        $mob_dir = $a_target_dir . "/mobs";

        $source_dir = ilUtil::getWebspaceDir() . "/mobs/mm_" . $a_mob_id;
        if (@is_dir($source_dir)) {
            ilUtil::makeDir($mob_dir . "/mm_" . $a_mob_id);
            ilUtil::rCopy($source_dir, $mob_dir . "/mm_" . $a_mob_id);
        }
        
        $tpl = new ilTemplate("tpl.main.html", true, true);
        $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        $_GET["obj_type"] = "MediaObject";
        $_GET["mob_id"] = $a_mob_id;
        $_GET["frame"] = $a_frame;
        $_GET["cmd"] = "";
        $content = $a_lm_gui->media();
        $file = $a_target_dir . "/media_" . $a_mob_id . ".html";

        // open file
        if (!($fp = @fopen($file, "w+"))) {
            die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
        }
        chmod($file, 0770);
        fwrite($fp, $content);
        fclose($fp);
        
        // fullscreen
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mob_obj = new ilObjMediaObject($a_mob_id);
        if ($mob_obj->hasFullscreenItem()) {
            $tpl = new ilTemplate("tpl.main.html", true, true);
            $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
            $_GET["obj_type"] = "";
            $_GET["frame"] = "";
            $_GET["mob_id"] = $a_mob_id;
            $_GET["cmd"] = "fullscreen";
            $content = $a_lm_gui->fullscreen();
            $file = $a_target_dir . "/fullscreen_" . $a_mob_id . ".html";
    
            // open file
            if (!($fp = @fopen($file, "w+"))) {
                die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                    " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
            }
            chmod($file, 0770);
            fwrite($fp, $content);
            fclose($fp);
        }
        $linked_mobs = $mob_obj->getLinkedMediaObjects();
        foreach ($linked_mobs as $id) {
            $this->log->debug("HTML Export: Add media object $id (" . ilObject::_lookupTitle($id) . ") " .
                " due to media object " . $a_mob_id . " (" . ilObject::_lookupTitle($a_mob_id) . ").");
        }
        $a_linked_mobs = array_merge($a_linked_mobs, $linked_mobs);
    }
    
    /**
    * export glossary terms
    */
    public function exportHTMLGlossaryTerms(&$a_lm_gui, $a_target_dir)
    {
        $ilLocator = $this->locator;
        
        foreach ($this->offline_int_links as $int_link) {
            $ilLocator->clearItems();
            if ($int_link["type"] == "git") {
                $tpl = new ilTemplate("tpl.main.html", true, true);
                $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

                $_GET["obj_id"] = $int_link["id"];
                $_GET["frame"] = "_blank";
                $content = $a_lm_gui->glossary();
                $file = $a_target_dir . "/term_" . $int_link["id"] . ".html";
                    
                // open file
                if (!($fp = @fopen($file, "w+"))) {
                    die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                            " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
                }
                chmod($file, 0770);
                fwrite($fp, $content);
                fclose($fp);

                // store linked/embedded media objects of glosssary term
                include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
                $defs = ilGlossaryDefinition::getDefinitionList($int_link["id"]);
                foreach ($defs as $def) {
                    $def_mobs = ilObjMediaObject::_getMobsOfObject("gdf:pg", $def["id"]);
                    foreach ($def_mobs as $def_mob) {
                        $this->offline_mobs[$def_mob] = $def_mob;
                        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                        $this->log->debug("HTML Export: Add media object $def_mob (" . ilObject::_lookupTitle($def_mob) . ") " .
                            " due to glossary entry " . $int_link["id"] . " (" . ilGlossaryTerm::_lookGlossaryTerm($int_link["id"]) . ").");
                    }
                    
                    // get all files of page
                    $def_files = ilObjFile::_getFilesOfObject("gdf:pg", $page["obj_id"]);
                    $this->offline_files = array_merge($this->offline_files, $def_files);
                }
            }
        }
    }
    
    /**
    * export all pages of learning module to html file
    */
    public function exportHTMLPages(&$a_lm_gui, $a_target_dir, $a_lang = "", $a_all_languages = false)
    {
        $ilLocator = $this->locator;
                
        $pages = ilLMPageObject::getPageList($this->getId());
        
        $lm_tree = $this->getLMTree();
        $first_page = $lm_tree->fetchSuccessorNode($lm_tree->getRootId(), "pg");
        $this->first_page_id = $first_page["child"];

        // iterate all learning module pages
        $mobs = array();
        $int_links = array();
        $this->offline_files = array();

        include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

        // get html export id mapping
        $lm_set = new ilSetting("lm");
        $exp_id_map = array();

        if ($lm_set->get("html_export_ids")) {
            foreach ($pages as $page) {
                $exp_id = ilLMPageObject::getExportId($this->getId(), $page["obj_id"]);
                if (trim($exp_id) != "") {
                    $exp_id_map[$page["obj_id"]] = trim($exp_id);
                }
            }
        }
        //exit;
        if ($a_lang == "") {
            $a_lang = "-";
        }

        reset($pages);
        foreach ($pages as $page) {
            if (ilLMPage::_exists($this->getType(), $page["obj_id"])) {
                $ilLocator->clearItems();
                $this->exportPageHTML(
                    $a_lm_gui,
                    $a_target_dir,
                    $page["obj_id"],
                    "",
                    $exp_id_map,
                    $a_lang,
                    $a_all_languages
                );

                // get all snippets of page
                $pcs = ilPageContentUsage::getUsagesOfPage($page["obj_id"], $this->getType() . ":pg", 0, false, $a_lang);
                foreach ($pcs as $pc) {
                    if ($pc["type"] == "incl") {
                        $incl_mobs = ilObjMediaObject::_getMobsOfObject("mep:pg", $pc["id"]);
                        foreach ($incl_mobs as $incl_mob) {
                            $mobs[$incl_mob] = $incl_mob;
                            include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                            $this->log->debug("HTML Export: Add media object $incl_mob (" . ilObject::_lookupTitle($incl_mob) . ") " .
                                " due to snippet " . $pc["id"] . " in page " . $page["obj_id"] . " (" . ilLMObject::_lookupTitle($page["obj_id"]) . ").");
                        }
                    }
                }

                // get all media objects of page
                $pg_mobs = ilObjMediaObject::_getMobsOfObject($this->getType() . ":pg", $page["obj_id"], 0, $a_lang);
                foreach ($pg_mobs as $pg_mob) {
                    $mobs[$pg_mob] = $pg_mob;
                    include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                    $this->log->debug("HTML Export: Add media object $pg_mob (" . ilObject::_lookupTitle($pg_mob) . ") " .
                        " due to page " . $page["obj_id"] . " (" . ilLMObject::_lookupTitle($page["obj_id"]) . ").");
                }
                
                // get all internal links of page
                $pg_links = ilInternalLink::_getTargetsOfSource($this->getType() . ":pg", $page["obj_id"], $a_lang);
                $int_links = array_merge($int_links, $pg_links);
                
                // get all files of page
                include_once("./Modules/File/classes/class.ilObjFile.php");
                $pg_files = ilObjFile::_getFilesOfObject($this->getType() . ":pg", $page["obj_id"], 0, $a_lang);
                $this->offline_files = array_merge($this->offline_files, $pg_files);

                // collect all questions
                include_once("./Services/COPage/classes/class.ilPCQuestion.php");
                $q_ids = ilPCQuestion::_getQuestionIdsForPage($this->getType(), $page["obj_id"], $a_lang);
                foreach ($q_ids as $q_id) {
                    $this->q_ids[$q_id] = $q_id;
                }
            }
        }
        foreach ($mobs as $m) {
            $this->offline_mobs[$m] = $m;
        }
        foreach ($int_links as $k => $v) {
            $this->offline_int_links[$k] = $v;
        }
    }



    /**
    * export page html
    */
    public function exportPageHTML(
        &$a_lm_gui,
        $a_target_dir,
        $a_lm_page_id,
        $a_frame = "",
        $a_exp_id_map = array(),
        $a_lang = "-",
        $a_all_languages = false
    ) {
        $tpl = $this->tpl;

        $lang_suffix = "";
        if ($a_lang != "-" && $a_lang != "" && $a_all_languages) {
            $lang_suffix = "_" . $a_lang;
        }
        
        //echo "<br>B: export Page HTML ($a_lm_page_id)"; flush();
        // template workaround: reset of template
        $tpl = new ilTemplate("tpl.main.html", true, true);
        $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

        include_once("./Services/COPage/classes/class.ilPCQuestion.php");
        ilPCQuestion::resetInitialState();

        $_GET["obj_id"] = $a_lm_page_id;
        $_GET["frame"] = $a_frame;

        if ($a_frame == "") {
            //if ($nid = ilLMObject::_lookupNID($a_lm_gui->lm->getId(), $a_lm_page_id, "pg"))
            if (is_array($a_exp_id_map) && isset($a_exp_id_map[$a_lm_page_id])) {
                $file = $a_target_dir . "/lm_pg_" . $a_exp_id_map[$a_lm_page_id] . $lang_suffix . ".html";
            } else {
                $file = $a_target_dir . "/lm_pg_" . $a_lm_page_id . $lang_suffix . ".html";
            }
        } else {
            if ($a_frame != "toc") {
                $file = $a_target_dir . "/frame_" . $a_lm_page_id . "_" . $a_frame . $lang_suffix . ".html";
            } else {
                $file = $a_target_dir . "/frame_" . $a_frame . $lang_suffix . ".html";
            }
        }
        
        // return if file is already existing
        if (@is_file($file)) {
            return;
        }

        $content = $a_lm_gui->layout("main.xml", false);

        // open file
        if (!($fp = @fopen($file, "w+"))) {
            die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                    " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
        }
    
        // set file permissions
        chmod($file, 0770);
            
        // write xml data into the file
        fwrite($fp, $content);
        
        // close file
        fclose($fp);

        if ($this->first_page_id == $a_lm_page_id && $a_frame == "") {
            copy($file, $a_target_dir . "/index" . $lang_suffix . ".html");
        }

        // write frames of frameset
        $frameset = $a_lm_gui->getCurrentFrameSet();

        foreach ($frameset as $frame) {
            $this->exportPageHTML($a_lm_gui, $a_target_dir, $a_lm_page_id, $frame);
        }
    }

    /**
    * export object to fo
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportFO(&$a_xml_writer, $a_target_dir)
    {
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
        $this->exportFOStructureObjects($a_xml_writer, $expLog);

        // fo:flow (end)
        $a_xml_writer->xmlEndTag("fo:flow");

        // fo:page-sequence (end)
        $a_xml_writer->xmlEndTag("fo:page-sequence");

        // fo:root (end)
        $a_xml_writer->xmlEndTag("fo:root");
    }

    /**
    * export structure objects to fo
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportFOStructureObjects(&$a_xml_writer)
    {
        $childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
        foreach ($childs as $child) {
            if ($child["type"] != "st") {
                continue;
            }

            $structure_obj = new ilStructureObject($this, $child["obj_id"]);
            $structure_obj->exportFO($a_xml_writer, $expLog);
            unset($structure_obj);
        }
    }

    public function getXMLZip()
    {
        include_once("./Modules/LearningModule/classes/class.ilContObjectExport.php");

        $cont_exp = new ilContObjectExport($this, 'xml');

        $export_file = $cont_exp->buildExportFile();
        return $export_file;
    }

    /**
    * Execute Drag Drop Action
    *
    * @param	string	$source_id		Source element ID
    * @param	string	$target_id		Target element ID
    * @param	string	$first_child	Insert as first child of target
    * @param	string	$movecopy		Position ("move" | "copy")
    */
    public function executeDragDrop($source_id, $target_id, $first_child, $as_subitem = false, $movecopy = "move")
    {
        $lmtree = new ilTree($this->getId());
        $lmtree->setTableNames('lm_tree', 'lm_data');
        $lmtree->setTreeTablePK("lm_id");
        //echo "-".$source_id."-".$target_id."-".$first_child."-".$as_subitem."-";
        $source_obj = ilLMObjectFactory::getInstance($this, $source_id, true);
        $source_obj->setLMId($this->getId());

        if (!$first_child) {
            $target_obj = ilLMObjectFactory::getInstance($this, $target_id, true);
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
                    require_once("./Services/History/classes/class.ilHistory.php");
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
                    $new_page = $source_obj->copy();
                    $source_id = $new_page->getId();
                    $source_obj = $new_page;
                }

                // paste page
                if (!$lmtree->isInTree($source_obj->getId())) {
                    if ($first_child) {			// as first child
                        $target_pos = IL_FIRST_NODE;
                        $parent = $target_id;
                    } elseif ($as_subitem) {		// as last child
                        $parent = $target_id;
                        $target_pos = IL_FIRST_NODE;
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
                        include_once("./Services/History/classes/class.ilHistory.php");
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
            $subnodes = $lmtree->getSubtree($source_node);

            // check, if target is within subtree
            foreach ($subnodes as $subnode) {
                if ($subnode["obj_id"] == $target_id) {
                    return;
                }
            }

            $target_pos = $target_id;

            if ($first_child) {		// as first subchapter
                $target_pos = IL_FIRST_NODE;
                $target_parent = $target_id;
                
                $pg_childs = $lmtree->getChildsByType($target_parent, "pg");
                if (count($pg_childs) != 0) {
                    $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                }
            } elseif ($as_subitem) {		// as last subchapter
                $target_parent = $target_id;
                $target_pos = IL_FIRST_NODE;
                $childs = $lmtree->getChilds($target_parent);
                if (count($childs) != 0) {
                    $target_pos = $childs[count($childs) - 1]["obj_id"];
                }
            }

            // insert into
            /*
                        if ($position == "into")
                        {
                            $target_parent = $target_id;
                            $target_pos = IL_FIRST_NODE;

                            // if target_pos is still first node we must skip all pages
                            if ($target_pos == IL_FIRST_NODE)
                            {
                                $pg_childs =& $lmtree->getChildsByType($target_parent, "pg");
                                if (count($pg_childs) != 0)
                                {
                                    $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                                }
                            }
                        }
            */


            // delete source tree
            if ($movecopy == "move") {
                $lmtree->deleteTree($source_node);
            } else {
                // copy chapter (incl. subcontents)
                $new_chapter = $source_obj->copy($lmtree, $target_parent, $target_pos);
            }

            if (!$lmtree->isInTree($source_id)) {
                $lmtree->insertNode($source_id, $target_parent, $target_pos);

                // insert moved tree
                if ($movecopy == "move") {
                    foreach ($subnodes as $node) {
                        if ($node["obj_id"] != $source_id) {
                            $lmtree->insertNode($node["obj_id"], $node["parent"]);
                        }
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
    public function validatePages()
    {
        include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";
        include_once "./Modules/LearningModule/classes/class.ilLMPage.php";

        $mess = "";
        
        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $page) {
            if (ilLMPage::_exists($this->getType(), $page["obj_id"])) {
                $cpage = new ilLMPage($page["obj_id"]);
                $cpage->buildDom();
                $error = @$cpage->validateDom();
                
                if ($error != "") {
                    $this->lng->loadLanguageModule("content");
                    ilUtil::sendInfo($this->lng->txt("cont_import_validation_errors"));
                    $title = ilLMObject::_lookupTitle($page["obj_id"]);
                    $page_obj = new ilLMPageObject($this, $page["obj_id"]);
                    $mess .= $this->lng->txt("obj_pg") . ": " . $title;
                    $mess .= '<div class="small">';
                    foreach ($error as $e) {
                        $err_mess = implode($e, " - ");
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

    /**
     * Import lm from zip file
     *
     * @param
     * @return
     */
    public function importFromZipFile(
        $a_tmp_file,
        $a_filename,
        $a_validate = true,
        $a_import_into_help_module = 0
    ) {
        $lng = $this->lng;

        // create import directory
        $this->createImportDirectory();

        // copy uploaded file to import directory
        $file = pathinfo($a_filename);
        $full_path = $this->getImportDirectory() . "/" . $a_filename;

        ilUtil::moveUploadedFile(
            $a_tmp_file,
            $a_filename,
            $full_path
        );

        // unzip file
        ilUtil::unzip($full_path);

        $subdir = basename($file["basename"], "." . $file["extension"]);

        $mess = $this->importFromDirectory(
            $this->getImportDirectory() . "/" . $subdir,
            $a_validate
        );

        
        // delete import directory
        ilUtil::delDir($this->getImportDirectory());

        return $mess;
    }


    /**
     * Import lm from directory
     *
     * @param
     * @return
     */
    // begin-patch optes_lok_export
    public function importFromDirectory($a_directory, $a_validate = true, $a_mapping = null)
    // end-patch optes_lok_export
    {
        $lng = $this->lng;

        $this->log->debug("import from directory " . $a_directory);
        
        // determine filename of xml file
        $subdir = basename($a_directory);
        $xml_file = $a_directory . "/" . $subdir . ".xml";

        // check directory exists within zip file
        if (!is_dir($a_directory)) {
            $this->log->error(sprintf($lng->txt("cont_no_subdir_in_zip"), $subdir));
            return sprintf($lng->txt("cont_no_subdir_in_zip"), $subdir);
        }

        // check whether xml file exists within zip file
        if (!is_file($xml_file)) {
            $this->log->error(sprintf($lng->txt("cont_zip_file_invalid"), $subdir . "/" . $subdir . ".xml"));
            return sprintf($lng->txt("cont_zip_file_invalid"), $subdir . "/" . $subdir . ".xml");
        }

        // import questions
        $this->log->debug("import qti");
        $qti_file = $a_directory . "/qti.xml";
        $qtis = array();
        if (is_file($qti_file)) {
            include_once "./Services/QTI/classes/class.ilQTIParser.php";
            include_once("./Modules/Test/classes/class.ilObjTest.php");
            $qtiParser = new ilQTIParser(
                $qti_file,
                IL_MO_VERIFY_QTI,
                0,
                ""
            );
            $result = $qtiParser->startParsing();
            $founditems = &$qtiParser->getFoundItems();
            $testObj = new ilObjTest(0, true);
            if (count($founditems) > 0) {
                $qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, 0, "");
                $qtiParser->setTestObject($testObj);
                $result = $qtiParser->startParsing();
                $qtis = array_merge($qtis, $qtiParser->getImportMapping());
            }
        }

        $this->log->debug("get ilContObjParser");
        include_once("./Modules/LearningModule/classes/class.ilContObjParser.php");
        $subdir = ".";
        $contParser = new ilContObjParser($this, $xml_file, $subdir, $a_directory);
        // smeyer: added \ilImportMapping lok im/export
        $contParser->setImportMapping($a_mapping);
        $contParser->setQuestionMapping($qtis);
        $contParser->startParsing();
        ilObject::_writeImportId($this->getId(), $this->getImportId());
        $this->MDUpdateListener('General');

        // import style
        $style_file = $a_directory . "/style.xml";
        $style_zip_file = $a_directory . "/style.zip";
        if (is_file($style_zip_file)) {	// try to import style.zip first
            require_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $style = new ilObjStyleSheet();
            $style->import($style_zip_file);
            $this->writeStyleSheetId($style->getId());
        } elseif (is_file($style_file)) {	// try to import style.xml
            require_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $style = new ilObjStyleSheet();
            $style->import($style_file);
            $this->writeStyleSheetId($style->getId());
        }

        //		// validate
        if ($a_validate) {
            $mess = $this->validatePages();
        }

        if ($mess == "") {
            // handle internal links to this learning module
            include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
            ilLMPage::_handleImportRepositoryLinks(
                $this->getImportId(),
                $this->getType(),
                $this->getRefId()
            );
        }

        return $mess;
    }

    /**
     * Clone learning module
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $this->cloneMetaData($new_obj);
        //$new_obj->createProperties();

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

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
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = $this->getStyleSheetId();
        if ($style_id > 0 &&
            !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            $new_obj->setStyleSheetId($new_id);
        } else {	// or just set the same standard style
            $new_obj->setStyleSheetId($style_id);
        }
        $new_obj->update();
        
        // copy content
        $copied_nodes = $this->copyAllPagesAndChapters($new_obj, $a_copy_id);

        // page header and footer
        if ($this->getHeaderPage() > 0 && ($new_page_header = $copied_nodes[$this->getHeaderPage()]) > 0) {
            $new_obj->setHeaderPage($new_page_header);
        }
        if ($this->getFooterPage() > 0 && ($new_page_footer = $copied_nodes[$this->getFooterPage()]) > 0) {
            $new_obj->setFooterPage($new_page_footer);
        }
        $new_obj->update();

        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);

        // copy (page) multilang settings
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $ot = ilObjectTranslation::getInstance($this->getId());
        $ot->copy($new_obj->getId());

        // copy lm menu
        include_once './Modules/LearningModule/classes/class.ilLMMenuEditor.php';
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
            ilLMMenuEditor::writeActive($new_menu->getEntryId(), $entry["active"] == "y" ? true : false);
        }


        return $new_obj;
    }
    
    /**
     * Copy all pages and chapters
     *
     * @param object $a_target_obj target learning module
     */
    public function copyAllPagesAndChapters($a_target_obj, $a_copy_id = 0)
    {
        $parent_id = $a_target_obj->lm_tree->readRootId();
        
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        
        // get all chapters of root lm
        $chapters = $this->lm_tree->getChildsByType($this->lm_tree->readRootId(), "st");
        $copied_nodes = array();
        //$time = time();
        foreach ($chapters as $chap) {
            $cid = ilLMObject::pasteTree(
                $a_target_obj,
                $chap["child"],
                $parent_id,
                IL_LAST_NODE,
                $time,
                $copied_nodes,
                true,
                $this
            );
            $target = $cid;
        }
        
        // copy free pages
        $pages = ilLMPageObject::getPageList($this->getId());
        foreach ($pages as $p) {
            if (!$this->lm_tree->isInTree($p["obj_id"])) {
                $item = new ilLMPageObject($this, $p["obj_id"]);
                $target_item = $item->copy($a_target_obj);
                $copied_nodes[$item->getId()] = $target_item->getId();
            }
        }
        
        // Add mapping for pages and chapters
        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
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


    /**
     * Lookup auto glossaries
     *
     * @param
     * @return
     */
    public static function lookupAutoGlossaries($a_lm_id)
    {
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
    
    /**
     * Auto link glossary terms
     *
     * @param
     * @return
     */
    public function autoLinkGlossaryTerms($a_glo_ref_id)
    {
        // get terms
        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
        $terms = ilGlossaryTerm::getTermList($a_glo_ref_id);

        // each get page: get content
        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
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
        include_once("./Services/COPage/classes/class.ilPCParagraph.php");
        foreach ($found_pages as $id => $fp) {
            ilPCParagraph::autoLinkGlossariesPage($fp["page"], $fp["terms"]);
        }
    }
    

    ////
    //// Online help
    ////

    /**
     * Is module an online module
     *
     * @return boolean true, if current learning module is an online help lm
     */
    public static function isOnlineHelpModule($a_id, $a_as_obj_id = false)
    {
        if (!$a_as_obj_id && $a_id > 0 && $a_id == OH_REF_ID) {
            return true;
        }
        if ($a_as_obj_id && $a_id > 0 && $a_id == ilObject::_lookupObjId(OH_REF_ID)) {
            return true;
        }
        return false;
    }
        
    public function setRating($a_value)
    {
        $this->rating = (bool) $a_value;
    }
    
    public function hasRating()
    {
        return $this->rating;
    }
    
    public function setRatingPages($a_value)
    {
        $this->rating_pages = (bool) $a_value;
    }
    
    public function hasRatingPages()
    {
        return $this->rating_pages;
    }
    
    
    public function MDUpdateListener($a_element)
    {
        parent::MDUpdateListener($a_element);
        
        include_once 'Services/MetaData/classes/class.ilMD.php';

        switch ($a_element) {
            case 'Educational':
                include_once("./Services/Object/classes/class.ilObjectLP.php");
                $obj_lp = ilObjectLP::getInstance($this->getId());
                if (in_array(
                    $obj_lp->getCurrentMode(),
                    array(ilLPObjSettings::LP_MODE_TLT, ilLPObjSettings::LP_MODE_COLLECTION_TLT)
                )) {
                    include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                    ilLPStatusWrapper::_refreshStatus($this->getId());
                }
                break;

            case 'General':

                // Update Title and description
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_gen = $md->getGeneral())) {
                    return false;
                }

                include_once("./Services/Object/classes/class.ilObjectTranslation.php");
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
        return true;
    }
    
    /**
     * Get public export files
     *
     * @return array array of arrays with keys "type" (html, scorm or xml), "file" (filename) and "size" in bytes, "dir_type" detailed directoy type, e.g. html_de
     */
    public function getPublicExportFiles()
    {
        $dirs = array("xml", "scorm");
        $export_files = array();

        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
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
}
