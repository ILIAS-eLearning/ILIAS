<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_INSERT_BEFORE", 0);
define("IL_INSERT_AFTER", 1);
define("IL_INSERT_CHILD", 2);

define("IL_CHAPTER_TITLE", "st_title");
define("IL_PAGE_TITLE", "pg_title");
define("IL_NO_HEADER", "none");

/** @defgroup ServicesCOPage Services/COPage
 */

/*

    - move dom related code to PageDom class/interface
    - move ilDB dependency to ar object
    - move internal links related code to extra class
    - make factory available through DIC, opt allow decentralized factory parts
    - PC types
    -- internal links used/implemented?
    -- styles used/implemented?
    - application classes need
    -- page object
    --- page object should return php5 domdoc (getDom() vs getDomDoc()?)
        esp. plugins should use this
    -- remove content element hook, if content is not allowed
    - PC types could move to components (e.g. blog, login)
    - How to modularize xsl?
    -- read from db?
    -- xml entries say that xslt code is used -> read file and include in
       main xslt file

*/
 
/**
 * Class ilPageObject
 *
 * Handles PageObjects of ILIAS Learning Modules (see ILIAS DTD)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
abstract class ilPageObject
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    public static $exists = array();

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var int
     */
    protected $id;
    public $dom;
    public $xml;
    public $encoding;
    public $node;
    public $cur_dtd = "ilias_pg_5_4.dtd";
    public $contains_int_link;
    public $needs_parsing;
    public $parent_type;
    public $parent_id;
    public $update_listeners;
    public $update_listener_cnt;
    public $offline_handler;
    public $dom_builded;
    public $history_saved;

    /**
     * @var string
     */
    protected $language = "-";

    /**
     * @var array
     */
    protected static $activation_data = array();

    /**
     * @var bool
     */
    protected $import_mode = false;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var array
     */
    protected $page_record = array();

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var ilPageConfig
     */
    protected $page_config;

    /**
    * Constructor
    * @access	public
    */
    final public function __construct($a_id = 0, $a_old_nr = 0, $a_lang = "-")
    {
        global $DIC;
        $this->obj_definition = $DIC["objDefinition"];
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->log = ilLoggerFactory::getLogger('copg');

        $this->parent_type = $this->getParentType();
        $this->id = $a_id;
        $this->setLanguage($a_lang);

        $this->contains_int_link = false;
        $this->needs_parsing = false;
        $this->update_listeners = array();
        $this->update_listener_cnt = 0;
        $this->dom_builded = false;
        $this->page_not_found = false;
        $this->old_nr = $a_old_nr;
        $this->encoding = "UTF-8";
        $this->id_elements =
            array("PageContent", "TableRow", "TableData", "ListItem", "FileItem",
                "Section", "Tab", "ContentPopup", "GridCell");
        $this->setActive(true);
        $this->show_page_act_info = false;
        
        if ($a_id != 0) {
            $this->read();
        }
        
        $this->initPageConfig();
        
        $this->afterConstructor();
    }
    
    /**
     * After constructor
     */
    public function afterConstructor()
    {
    }
    
    
    /**
     * Get parent type
     *
     * @return string parent type (page type)
     */
    abstract public function getParentType();


    /**
     * Init page config
     */
    final public function initPageConfig()
    {
        include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
        $cfg = ilPageObjectFactory::getConfigInstance($this->getParentType());
        $this->setPageConfig($cfg);
    }
    
    /**
     * Set language
     *
     * @param string $a_val language code or "-" for unknown / not set
     */
    public function setLanguage($a_val)
    {
        $this->language = $a_val;
    }
    
    /**
     * Get language
     *
     * @return string language code
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Set page config object
     *
     * @param object $a_val page config object
     */
    public function setPageConfig($a_val)
    {
        $this->page_config = $a_val;
    }
    
    /**
     * Get page config object
     *
     * @return object page config object
     */
    public function getPageConfig()
    {
        return $this->page_config;
    }

    /**
    * Set Render MD5.
    *
    * @param	string	$a_rendermd5	Render MD5
    */
    public function setRenderMd5($a_rendermd5)
    {
        $this->rendermd5 = $a_rendermd5;
    }

    /**
    * Get Render MD5.
    *
    * @return	string	Render MD5
    */
    public function getRenderMd5()
    {
        return $this->rendermd5;
    }

    /**
    * Set Rendered Content.
    *
    * @param	string	$a_renderedcontent	Rendered Content
    */
    public function setRenderedContent($a_renderedcontent)
    {
        $this->renderedcontent = $a_renderedcontent;
    }

    /**
    * Get Rendered Content.
    *
    * @return	string	Rendered Content
    */
    public function getRenderedContent()
    {
        return $this->renderedcontent;
    }

    /**
    * Set Rendered Time.
    *
    * @param	string	$a_renderedtime	Rendered Time
    */
    public function setRenderedTime($a_renderedtime)
    {
        $this->renderedtime = $a_renderedtime;
    }

    /**
    * Get Rendered Time.
    *
    * @return	string	Rendered Time
    */
    public function getRenderedTime()
    {
        return $this->renderedtime;
    }

    /**
    * Set Last Change.
    *
    * @param	string	$a_lastchange	Last Change
    */
    public function setLastChange($a_lastchange)
    {
        $this->lastchange = $a_lastchange;
    }

    /**
    * Get Last Change.
    *
    * @return	string	Last Change
    */
    public function getLastChange()
    {
        return $this->lastchange;
    }
    
    /**
     * Set last change user
     *
     * @param	integer	$a_val	last change user
     */
    public function setLastChangeUser($a_val)
    {
        $this->last_change_user = $a_val;
    }

    /**
     * Get last change user
     *
     * @return	integer	last change user
     */
    public function getLastChangeUser()
    {
        return $this->last_change_user;
    }
    
    /**
     * Set show page activation info
     *
     * @param bool $a_val show page actication info
     */
    public function setShowActivationInfo($a_val)
    {
        $this->show_page_act_info = $a_val;
    }
    
    /**
     * Get show page activation info
     *
     * @return bool show page actication info
     */
    public function getShowActivationInfo()
    {
        return $this->show_page_act_info;
    }

    /**
     * Read page data
     */
    public function read()
    {
        $this->setActive(true);
        if ($this->old_nr == 0) {
            $query = "SELECT * FROM page_object" .
                " WHERE page_id = " . $this->db->quote($this->id, "integer") .
                " AND parent_type=" . $this->db->quote($this->getParentType(), "text") .
                " AND lang = " . $this->db->quote($this->getLanguage(), "text");
            $pg_set = $this->db->query($query);
            $this->page_record = $this->db->fetchAssoc($pg_set);
            $this->setActive($this->page_record["active"]);
            $this->setActivationStart($this->page_record["activation_start"]);
            $this->setActivationEnd($this->page_record["activation_end"]);
            $this->setShowActivationInfo($this->page_record["show_activation_info"]);
        } else {
            $query = "SELECT * FROM page_history" .
                " WHERE page_id = " . $this->db->quote($this->id, "integer") .
                " AND parent_type=" . $this->db->quote($this->getParentType(), "text") .
                " AND nr = " . $this->db->quote((int) $this->old_nr, "integer") .
                " AND lang = " . $this->db->quote($this->getLanguage(), "text");
            $pg_set = $this->db->query($query);
            $this->page_record = $this->db->fetchAssoc($pg_set);
        }
        if (!$this->page_record) {
            include_once("./Services/COPage/exceptions/class.ilCOPageNotFoundException.php");
            throw new ilCOPageNotFoundException("Error: Page " . $this->id . " is not in database" .
                " (parent type " . $this->getParentType() . ", lang: " . $this->getLanguage() . ").");
        }

        $this->xml = $this->page_record["content"];
        $this->setParentId($this->page_record["parent_id"]);
        $this->last_change_user = $this->page_record["last_change_user"];
        $this->create_user = $this->page_record["create_user"];
        $this->setRenderedContent($this->page_record["rendered_content"]);
        $this->setRenderMd5($this->page_record["render_md5"]);
        $this->setRenderedTime($this->page_record["rendered_time"]);
        $this->setLastChange($this->page_record["last_change"]);
    }
    
    /**
     * Checks whether page exists
     *
     * @param string $a_parent_type parent type
     * @param int $a_id page id
     * @param string $a_lang language code, if empty language independent existence is checked
     */
    public static function _exists($a_parent_type, $a_id, $a_lang = "", $a_no_cache = false)
    {
        global $DIC;

        $db = $DIC->database();

        if (!$a_no_cache && isset(self::$exists[$a_parent_type . ":" . $a_id . ":" . $a_lang])) {
            return self::$exists[$a_parent_type . ":" . $a_id . ":" . $a_lang];
        }
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }
        
        $query = "SELECT page_id FROM page_object WHERE page_id = " . $db->quote($a_id, "integer") . " " .
            "AND parent_type = " . $db->quote($a_parent_type, "text") . $and_lang;
        $set = $db->query($query);
        if ($row = $db->fetchAssoc($set)) {
            self::$exists[$a_parent_type . ":" . $a_id . ":" . $a_lang] = true;
            return true;
        } else {
            self::$exists[$a_parent_type . ":" . $a_id . ":" . $a_lang] = false;
            return false;
        }
    }

    /**
     * Checks whether page exists and is not empty (may return true on some empty pages)
     *
     * @param string $a_parent_type parent type
     * @param int $a_id page id
     * @param string $a_lang language code ("-" for unknown / not set)
     */
    public static function _existsAndNotEmpty($a_parent_type, $a_id, $a_lang = "-")
    {
        include_once("./Services/COPage/classes/class.ilPageUtil.php");
        return ilPageUtil::_existsAndNotEmpty($a_parent_type, $a_id, $a_lang);
    }

    public function buildDom($a_force = false)
    {
        if ($this->dom_builded && !$a_force) {
            return;
        }

        //echo "\n<br>buildDomWith:".$this->getId().":xml:".$this->getXMLContent(true).":<br>";

        $options = 0;
        //$options = DOMXML_LOAD_VALIDATING;
        //$options = LIBXML_DTDLOAD;
        //$options = LIBXML_NOXMLDECL;
        //echo htmlentities($this->getXMLContent(true))."<br>";
        $this->dom = @domxml_open_mem($this->getXMLContent(true), $options, $error);
        //var_dump($error);
        $xpc = xpath_new_context($this->dom);
        $path = "//PageObject";
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) == 1) {
            //	echo "h";
            $this->node = $res->nodeset[0];
        }
        //echo htmlentities($this->dom->dump_node($this->node)); exit;

        if (empty($error)) {
            $this->dom_builded = true;
            return true;
        } else {
            return $error;
        }
    }

    public function freeDom()
    {
        //$this->dom->free();
        unset($this->dom);
    }

    /**
     * Deprecated php4DomDocument
     */
    public function getDom()
    {
        return $this->dom;
    }
    
    /**
     * Get dom doc (php5 dom document)
     *
     * @param
     * @return
     */
    public function getDomDoc()
    {
        if ($this->dom instanceof php4DOMDocument) {
            return $this->dom->myDOMDocument;
        }
        
        return $this->dom;
    }
    

    /**
    * set id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setParentId($a_id)
    {
        $this->parent_id = $a_id;
    }

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function addUpdateListener(&$a_object, $a_method, $a_parameters = "")
    {
        $cnt = $this->update_listener_cnt;
        $this->update_listeners[$cnt]["object"] = $a_object;
        $this->update_listeners[$cnt]["method"] = $a_method;
        $this->update_listeners[$cnt]["parameters"] = $a_parameters;
        $this->update_listener_cnt++;
    }

    public function callUpdateListeners()
    {
        for ($i = 0; $i < $this->update_listener_cnt; $i++) {
            $object = $this->update_listeners[$i]["object"];
            $method = $this->update_listeners[$i]["method"];
            $parameters = $this->update_listeners[$i]["parameters"];
            $object->$method($parameters);
        }
    }

    /**
    * set activation
    *
    * @param	boolean		$a_active	true/false for active or not
    */
    public function setActive($a_active)
    {
        $this->active = $a_active;
    }

    /**
    * get activation
    *
    * @return	boolean		true/false for active or not
    */
    public function getActive($a_check_scheduled_activation = false)
    {
        if ($a_check_scheduled_activation && !$this->active) {
            include_once("./Services/Calendar/classes/class.ilDateTime.php");
            $start = new ilDateTime($this->getActivationStart(), IL_CAL_DATETIME);
            $end = new ilDateTime($this->getActivationEnd(), IL_CAL_DATETIME);
            $now = new ilDateTime(time(), IL_CAL_UNIX);
            if (!ilDateTime::_before($now, $start) && !ilDateTime::_after($now, $end)) {
                return true;
            }
        }
        return $this->active;
    }

    /**
     * Preload activation data by Parent Id
     *
     * @param integer $a_parent_id parent id
     */
    public static function preloadActivationDataByParentId($a_parent_id)
    {
        global $DIC;

        $db = $DIC->database();
        $set = $db->query(
            "SELECT page_id, parent_type, lang, active, activation_start, activation_end, show_activation_info FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer")
        );
        while ($rec = $db->fetchAssoc($set)) {
            self::$activation_data[$rec["page_id"] . ":" . $rec["parent_type"] . ":" . $rec["lang"]] = $rec;
        }
    }


    /**
     * lookup activation status
     */
    public static function _lookupActive($a_id, $a_parent_type, $a_check_scheduled_activation = false, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        // language must be set at least to "-"
        if ($a_lang == "") {
            $a_lang = "-";
        }

        if (isset(self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang])) {
            $rec = self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang];
        } else {
            $set = $db->queryF(
                "SELECT active, activation_start, activation_end FROM page_object WHERE page_id = %s" .
                " AND parent_type = %s AND lang = %s",
                array("integer", "text", "text"),
                array($a_id, $a_parent_type, $a_lang)
            );
            $rec = $db->fetchAssoc($set);
        }


        $rec["n"] = ilUtil::now();

        if (!$rec["active"] && $a_check_scheduled_activation) {
            if ($rec["n"] >= $rec["activation_start"] &&
                $rec["n"] <= $rec["activation_end"]) {
                return true;
            }
        }
        
        return $rec["active"];
    }

    /**
    * Check whether page is activated by time schedule
    */
    public static function _isScheduledActivation($a_id, $a_parent_type, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        // language must be set at least to "-"
        if ($a_lang == "") {
            $a_lang = "-";
        }

        //echo "<br>";
        //var_dump(self::$activation_data); exit;
        if (isset(self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang])) {
            $rec = self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang];
        } else {
            $set = $db->queryF(
                "SELECT active, activation_start, activation_end FROM page_object WHERE page_id = %s" .
            " AND parent_type = %s AND lang = %s",
                array("integer", "text", "text"),
                array($a_id, $a_parent_type, $a_lang)
            );
            $rec = $db->fetchAssoc($set);
        }

        if (!$rec["active"] && $rec["activation_start"] != "") {
            return true;
        }
        
        return false;
    }

    /**
     * write activation status
     */
    public static function _writeActive($a_id, $a_parent_type, $a_active, $a_reset_scheduled_activation = true, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        // language must be set at least to "-"
        if ($a_lang == "") {
            $a_lang = "-";
        }

        if ($a_reset_scheduled_activation) {
            $st = $db->manipulateF(
                "UPDATE page_object SET active = %s, activation_start = %s, " .
                " activation_end = %s WHERE page_id = %s" .
                " AND parent_type = %s AND lang = %s",
                array("boolean", "timestamp", "timestamp", "integer", "text", "text"),
                array($a_active, null, null, $a_id, $a_parent_type, $a_lang)
            );
        } else {
            $st = $db->prepareManip(
                "UPDATE page_object SET active = %s WHERE page_id = %s" .
                " AND parent_type = %s AND lang = %s",
                array("boolean", "integer", "text", "text"),
                array($a_active, $a_id, $a_parent_type, $a_lang)
            );
        }
    }

    /**
     * Lookup activation data
     */
    public static function _lookupActivationData($a_id, $a_parent_type, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();

        // language must be set at least to "-"
        if ($a_lang == "") {
            $a_lang = "-";
        }

        if (isset(self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang])) {
            $rec = self::$activation_data[$a_id . ":" . $a_parent_type . ":" . $a_lang];
        } else {
            $set = $db->queryF(
                "SELECT active, activation_start, activation_end, show_activation_info FROM page_object WHERE page_id = %s" .
            " AND parent_type = %s AND lang = %s",
                array("integer", "text", "text"),
                array($a_id, $a_parent_type, $a_lang)
            );
            $rec = $db->fetchAssoc($set);
        }
        
        return $rec;
    }

    
    /**
    * Lookup parent id
    */
    public static function lookupParentId($a_id, $a_type)
    {
        global $DIC;

        $db = $DIC->database();
        
        $res = $db->query("SELECT parent_id FROM page_object WHERE page_id = " . $db->quote($a_id, "integer") . " " .
                "AND parent_type=" . $db->quote($a_type, "text"));
        $rec = $db->fetchAssoc($res);
        return $rec["parent_id"];
    }
    
    /**
     * Write parent id
     */
    public static function _writeParentId($a_parent_type, $a_pg_id, $a_par_id)
    {
        global $DIC;

        $db = $DIC->database();
        $db->manipulateF(
            "UPDATE page_object SET parent_id = %s WHERE page_id = %s" .
            " AND parent_type = %s",
            array("integer", "integer", "text"),
            array($a_par_id, $a_pg_id, $a_parent_type)
        );
    }
    
    /**
    * Set Activation Start.
    *
    * @param	date	$a_activationstart	Activation Start
    */
    public function setActivationStart($a_activationstart)
    {
        if ($a_activationstart == "") {
            $a_activationstart = null;
        }
        $this->activationstart = $a_activationstart;
    }

    /**
    * Get Activation Start.
    *
    * @return	date	Activation Start
    */
    public function getActivationStart()
    {
        return $this->activationstart;
    }

    /**
    * Set Activation End.
    *
    * @param	date	$a_activationend	Activation End
    */
    public function setActivationEnd($a_activationend)
    {
        if ($a_activationend == "") {
            $a_activationend = null;
        }
        $this->activationend = $a_activationend;
    }

    /**
    * Get Activation End.
    *
    * @return	date	Activation End
    */
    public function getActivationEnd()
    {
        return $this->activationend;
    }

    /**
     * Get a content object of the page
     *
     * @param string hier ID
     * @param string PC ID
     *
     * @return object page content object
     */
    public function getContentObject($a_hier_id, $a_pc_id = "")
    {
        $cont_node = $this->getContentNode($a_hier_id, $a_pc_id);
        if (!is_object($cont_node)) {
            return false;
        }
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $node_name = $cont_node->node_name();
        if ($node_name == "PageObject") {
            return null;
        }
        if ($node_name == "PageContent") {
            $child_node = $cont_node->first_child();
            $node_name = $child_node->node_name();
        }
        
        // table extra handling (@todo: get rid of it)
        if ($node_name == "Table") {
            if ($child_node->get_attribute("DataTable") == "y") {
                require_once("./Services/COPage/classes/class.ilPCDataTable.php");
                $tab = new ilPCDataTable($this);
                $tab->setNode($cont_node);
                $tab->setHierId($a_hier_id);
            } else {
                require_once("./Services/COPage/classes/class.ilPCTable.php");
                $tab = new ilPCTable($this);
                $tab->setNode($cont_node);
                $tab->setHierId($a_hier_id);
            }
            $tab->setPcId($a_pc_id);
            return $tab;
        }

        // media extra handling (@todo: get rid of it)
        if ($node_name == "MediaObject") {
            if ($_GET["pgEdMediaMode"] != "") {
                echo "ilPageObject::error media";
                exit;
            }

            //require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            require_once("./Services/COPage/classes/class.ilPCMediaObject.php");
            
            $mal_node = $child_node->first_child();
            //echo "ilPageObject::getContentObject:nodename:".$mal_node->node_name().":<br>";
            $id_arr = explode("_", $mal_node->get_attribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            
            // allow deletion of non-existing media objects
            if (!ilObject::_exists($mob_id) && in_array("delete", $_POST)) {
                $mob_id = 0;
            }

            //$mob = new ilObjMediaObject($mob_id);
            $mob = new ilPCMediaObject($this);
            $mob->readMediaObject($mob_id);
            
            //$mob->setDom($this->dom);
            $mob->setNode($cont_node);
            $mob->setHierId($a_hier_id);
            $mob->setPcId($a_pc_id);
            return $mob;
        }

        //
        // generic procedure
        //
        
        $pc_def = ilCOPagePCDef::getPCDefinitionByName($node_name);
        
        // check if pc definition has been found
        if (!is_array($pc_def)) {
            include_once("./Services/COPage/exceptions/class.ilCOPageUnknownPCTypeException.php");
            throw new ilCOPageUnknownPCTypeException('Unknown PC Name "' . $node_name . '".');
        }
        $pc_class = "ilPC" . $pc_def["name"];
        $pc_path = "./" . $pc_def["component"] . "/" . $pc_def["directory"] . "/class." . $pc_class . ".php";
        require_once($pc_path);
        $pc = new $pc_class($this);
        $pc->setNode($cont_node);
        $pc->setHierId($a_hier_id);
        $pc->setPcId($a_pc_id);
        return $pc;
    }

    /**
     * Get content node from dom
     *
     * @param string $a_hier_id hierarchical ID
     * @param string $a_pc_id page content ID
     */
    public function &getContentNode($a_hier_id, $a_pc_id = "")
    {
        $xpc = xpath_new_context($this->dom);
        if ($a_hier_id == "pg") {
            return $this->node;
        } else {
            // get per pc id
            if ($a_pc_id != "") {
                $path = "//*[@PCID = '$a_pc_id']";
                $res = xpath_eval($xpc, $path);
                if (count($res->nodeset) == 1) {
                    $cont_node = $res->nodeset[0];
                    return $cont_node;
                }
            }
            
            // fall back to hier id
            $path = "//*[@HierId = '$a_hier_id']";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) == 1) {
                $cont_node = $res->nodeset[0];
                return $cont_node;
            }
        }
    }

    /**
     * Get content node from dom
     *
     * @param string $a_hier_id hierarchical ID
     * @param string $a_pc_id page content ID
     */
    public function checkForTag($a_content_tag, $a_hier_id, $a_pc_id = "")
    {
        $xpc = xpath_new_context($this->dom);
        // get per pc id
        if ($a_pc_id != "") {
            $path = "//*[@PCID = '$a_pc_id']//" . $a_content_tag;
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                return true;
            }
        }
        
        // fall back to hier id
        $path = "//*[@HierId = '$a_hier_id']//" . $a_content_tag;
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) > 0) {
            return true;
        }
        return false;
    }
    
    // only for test purposes
    public function lookforhier($a_hier_id)
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//*[@HierId = '$a_hier_id']";
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) == 1) {
            return "YES";
        } else {
            return "NO";
        }
    }


    public function &getNode()
    {
        return $this->node;
    }


    /**
    * set xml content of page, start with <PageObject...>,
    * end with </PageObject>, comply with ILIAS DTD, omit MetaData, use utf-8!
    *
    * @param	string		$a_xml			xml content
    * @param	string		$a_encoding		encoding of the content (here is no conversion done!
    *										it must be already utf-8 encoded at the time)
    */
    public function setXMLContent($a_xml, $a_encoding = "UTF-8")
    {
        $this->encoding = $a_encoding;
        $this->xml = $a_xml;
    }

    /**
    * append xml content to page
    * setXMLContent must be called before and the same encoding must be used
    *
    * @param	string		$a_xml			xml content
    */
    public function appendXMLContent($a_xml)
    {
        $this->xml .= $a_xml;
    }


    /**
    * get xml content of page
    */
    public function getXMLContent($a_incl_head = false)
    {
        // build full http path for XML DOCTYPE header.
        // Under windows a relative path doesn't work :-(
        if ($a_incl_head) {
            //echo "+".$this->encoding."+";
            $enc_str = (!empty($this->encoding))
                ? "encoding=\"" . $this->encoding . "\""
                : "";
            return "<?xml version=\"1.0\" $enc_str ?>" .
                "<!DOCTYPE PageObject SYSTEM \"" . ILIAS_ABSOLUTE_PATH . "/xml/" . $this->cur_dtd . "\">" .
                $this->xml;
        } else {
            return $this->xml;
        }
    }
    
    /**
    * Copy content of page; replace page components with copies
    * where necessary (e.g. questions)
    */
    public function copyXmlContent($a_clone_mobs = false)
    {
        $xml = $this->getXmlContent();
        $temp_dom = domxml_open_mem(
            '<?xml version="1.0" encoding="UTF-8"?>' . $xml,
            DOMXML_LOAD_PARSING,
            $error
        );
        if (empty($error)) {
            $this->handleCopiedContent($temp_dom, true, $a_clone_mobs);
        }
        $xml = $temp_dom->dump_mem(0, $this->encoding);
        $xml = preg_replace('/<\?xml[^>]*>/i', "", $xml);
        $xml = preg_replace('/<!DOCTYPE[^>]*>/i', "", $xml);

        return $xml;
    }

    // @todo 1: begin: generalize, remove concrete dependencies

    /**
     * Handle copied content
     *
     * This function copies items, that must be copied, if page
     * content is duplicated.
     *
     * @param
     * @return
     */
    public function handleCopiedContent($a_dom, $a_self_ass = true, $a_clone_mobs = false)
    {
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $defs = ilCOPagePCDef::getPCDefinitions();

        // handle question elements
        if ($a_self_ass) {
            $this->newQuestionCopies($a_dom);
        } else {
            $this->removeQuestions($a_dom);
        }
        
        // handle interactive images
        $this->newIIMCopies($a_dom);
        
        // handle media objects
        if ($a_clone_mobs) {
            $this->newMobCopies($a_dom);
        }

        // @todo 1: move all functions from above to the new domdoc
        if ($a_dom instanceof php4DOMDocument) {
            $a_dom = $a_dom->myDOMDocument;
        }
        foreach ($defs as $def) {
            ilCOPagePCDef::requirePCClassByName($def["name"]);
            $cl = $def["pc_class"];
            if ($cl == 'ilPCPlugged') {
                // the page object is provided for ilPageComponentPlugin
                ilPCPlugged::handleCopiedPluggedContent($this, $a_dom);
            } else {
                $cl::handleCopiedContent($a_dom, $a_self_ass, $a_clone_mobs);
            }
        }
    }

    /**
     * Handle content before deletion
     * This currently treats only plugged content
     * If no node is given, then the whole dom will be scanned
     *
     * @param php4DOMNode|DOMNode|null	$a_node
     */
    public function handleDeleteContent($a_node = null)
    {
        if (!isset($a_node)) {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent";
            $res = xpath_eval($xpc, $path);
            $nodes = $res->nodeset;
        } else {
            $nodes = array($a_node);
        }

        require_once('Services/COPage/classes/class.ilPCPlugged.php');
        foreach ($nodes as $node) {
            if ($node instanceof php4DOMNode) {
                $node = $node->myDOMNode;
            }

            /** @var DOMElement $node */
            if ($node->firstChild->nodeName == 'Plugged') {
                ilPCPlugged::handleDeletedPluggedNode($this, $node->firstChild);
            }
        }
    }

    /**
     * Replaces media objects in interactive images
     * with copies of the interactive images
     */
    public function newIIMCopies($temp_dom)
    {
        // Get question IDs
        $path = "//InteractiveImage/MediaAlias";
        $xpc = xpath_new_context($temp_dom);
        $res = &xpath_eval($xpc, $path);

        $q_ids = array();
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $or_id = $res->nodeset[$i]->get_attribute("OriginId");
            
            $inst_id = ilInternalLink::_extractInstOfTarget($or_id);
            $mob_id = ilInternalLink::_extractObjIdOfTarget($or_id);

            if (!($inst_id > 0)) {
                if ($mob_id > 0) {
                    include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                    $media_object = new ilObjMediaObject($mob_id);

                    // now copy this question and change reference to
                    // new question id
                    $new_mob = $media_object->duplicate();
                    
                    $res->nodeset[$i]->set_attribute("OriginId", "il__mob_" . $new_mob->getId());
                }
            }
        }
    }
    
    /**
     * Replaces media objects with copies
     */
    public function newMobCopies($temp_dom)
    {
        // Get question IDs
        $path = "//MediaObject/MediaAlias";
        $xpc = xpath_new_context($temp_dom);
        $res = &xpath_eval($xpc, $path);

        $q_ids = array();
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $or_id = $res->nodeset[$i]->get_attribute("OriginId");
            
            $inst_id = ilInternalLink::_extractInstOfTarget($or_id);
            $mob_id = ilInternalLink::_extractObjIdOfTarget($or_id);

            if (!($inst_id > 0)) {
                if ($mob_id > 0) {
                    include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                    $media_object = new ilObjMediaObject($mob_id);

                    // now copy this question and change reference to
                    // new question id
                    $new_mob = $media_object->duplicate();
                    
                    $res->nodeset[$i]->set_attribute("OriginId", "il__mob_" . $new_mob->getId());
                }
            }
        }
    }
    
    /**
     * Replaces existing question content elements with
     * new copies
     */
    public function newQuestionCopies(&$temp_dom)
    {
        // Get question IDs
        $path = "//Question";
        $xpc = xpath_new_context($temp_dom);
        $res = &xpath_eval($xpc, $path);

        $q_ids = array();
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $qref = $res->nodeset[$i]->get_attribute("QRef");
            
            $inst_id = ilInternalLink::_extractInstOfTarget($qref);
            $q_id = ilInternalLink::_extractObjIdOfTarget($qref);

            if (!($inst_id > 0)) {
                if ($q_id > 0) {
                    include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
                    $question = assQuestion::_instantiateQuestion($q_id);
                    // check due to #16557
                    if (is_object($question) && $question->isComplete()) {
                        // check if page for question exists
                        // due to a bug in early 4.2.x version this is possible
                        if (!ilPageObject::_exists("qpl", $q_id)) {
                            $question->createPageObject();
                        }

                        // now copy this question and change reference to
                        // new question id
                        $duplicate_id = $question->duplicate(false);
                        $res->nodeset[$i]->set_attribute("QRef", "il__qst_" . $duplicate_id);
                    }
                }
            }
        }
    }
    
    /**
     * Remove questions from document
     *
     * @param
     * @return
     */
    public function removeQuestions(&$temp_dom)
    {
        // Get question IDs
        $path = "//Question";
        $xpc = xpath_new_context($temp_dom);
        $res = &xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $parent_node = $res->nodeset[$i]->parent_node();
            $parent_node->unlink_node($parent_node);
        }
    }
    
    // @todo: end
    
    /**
     * Remove questions from document
     *
     * @param
     * @return
     */
    public function countPageContents()
    {
        // Get question IDs
        $this->buildDom();
        $path = "//PageContent";
        $xpc = xpath_new_context($this->dom);
        $res = &xpath_eval($xpc, $path);
        return count($res->nodeset);
    }
    
    /**
    * get xml content of page from dom
    * (use this, if any changes are made to the document)
    */
    public function getXMLFromDom(
        $a_incl_head = false,
        $a_append_mobs = false,
        $a_append_bib = false,
        $a_append_str = "",
        $a_omit_pageobject_tag = false
    ) {
        if ($a_incl_head) {
            //echo "\n<br>#".$this->encoding."#";
            return $this->dom->dump_mem(0, $this->encoding);
        } else {
            // append multimedia object elements
            if ($a_append_mobs || $a_append_bib || $a_append_link_info) {
                $mobs = "";
                $bibs = "";
                if ($a_append_mobs) {
                    $mobs = $this->getMultimediaXML();
                }
                if ($a_append_bib) {
                    // deprecated
//					$bibs = $this->getBibliographyXML();
                }
                $trans = $this->getLanguageVariablesXML();
                //echo htmlentities($this->dom->dump_node($this->node)); exit;
                return "<dummy>" . $this->dom->dump_node($this->node) . $mobs . $bibs . $trans . $a_append_str . "</dummy>";
            } else {
                if (is_object($this->dom)) {
                    if ($a_omit_pageobject_tag) {
                        $xml = "";
                        $childs = $this->node->child_nodes();
                        for ($i = 0; $i < count($childs); $i++) {
                            $xml .= $this->dom->dump_node($childs[$i]);
                        }
                        return $xml;
                    } else {
                        $xml = $this->dom->dump_mem(0, $this->encoding);
                        $xml = preg_replace('/<\?xml[^>]*>/i', "", $xml);
                        $xml = preg_replace('/<!DOCTYPE[^>]*>/i', "", $xml);
                        return $xml;

                        // don't use dump_node. This gives always entities.
                        //return $this->dom->dump_node($this->node);
                    }
                } else {
                    return "";
                }
            }
        }
    }

    /**
     * Get language variables as XML
     */
    public function getLanguageVariablesXML()
    {
        $xml = "<LVs>";
        $lang_vars = array(
            "ed_paste_clip", "ed_edit", "ed_edit_prop", "ed_delete", "ed_moveafter",
            "ed_movebefore", "ed_go", "ed_class", "ed_width", "ed_align_left",
            "ed_align_right", "ed_align_center", "ed_align_left_float",
            "ed_align_right_float", "ed_delete_item", "ed_new_item_before",
            "ed_new_item_after", "ed_copy_clip", "please_select", "ed_split_page",
            "ed_item_up", "ed_item_down", "ed_split_page_next","ed_enable",
            "de_activate", "ed_paste", "ed_edit_multiple", "ed_cut", "ed_copy", "ed_insert_templ",
            "ed_click_to_add_pg", "download");
        
        // collect lang vars from pc elements
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $defs = ilCOPagePCDef::getPCDefinitions();
        foreach ($defs as $def) {
            $lang_vars[] = "pc_" . $def["pc_type"];
            $lang_vars[] = "ed_insert_" . $def["pc_type"];
            
            ilCOPagePCDef::requirePCClassByName($def["name"]);
            $cl = $def["pc_class"];
            $lvs = call_user_func($def["pc_class"] . '::getLangVars');
            foreach ($lvs as $lv) {
                $lang_vars[] = $lv;
            }
        }

        foreach ($lang_vars as $lang_var) {
            $this->appendLangVarXML($xml, $lang_var);
        }

        $xml .= "</LVs>";

        return $xml;
    }

    public function appendLangVarXML(&$xml, $var)
    {
        $xml .= "<LV name=\"$var\" value=\"" . $this->lng->txt("cont_" . $var) . "\"/>";
    }

    // @todo begin: move this to paragraph class

    public function getFirstParagraphText()
    {
        if ($this->dom) {
            require_once("./Services/COPage/classes/class.ilPCParagraph.php");
            $xpc = xpath_new_context($this->dom);
            $path = "//Paragraph[1]";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                $cont_node = $res->nodeset[0]->parent_node();
                $par = new ilPCParagraph($this);
                $par->setNode($cont_node);
                $text = $par->getText();

                return $text;
            }
        }
        return "";
    }

    /**
    * Set content of paragraph
    *
    * @param	string	$a_hier_id		Hier ID
    * @param	string	$a_content		Content
    */
    public function setParagraphContent($a_hier_id, $a_content)
    {
        $node = $this->getContentNode($a_hier_id);
        if (is_object($node)) {
            $node->set_content($a_content);
        }
    }

    // @todo end

    
    /**
    * lm parser set this flag to true, if the page contains intern links
    * (this method should only be called by the import parser)
    *
    * todo: move to ilLMPageObject !?
    *
    * @param	boolean		$a_contains_link		true, if page contains intern link tag(s)
    */
    // @todo: can we do this better
    public function setContainsIntLink($a_contains_link)
    {
        $this->contains_int_link = $a_contains_link;
    }

    /**
    * returns true, if page was marked as containing an intern link (via setContainsIntLink)
    * (this method should only be called by the import parser)
    */
    // @todo: can we do this better
    public function containsIntLink()
    {
        return $this->contains_int_link;
    }
    
    /**
     * Set import mode
     *
     * @param bool $a_val import mode
     */
    public function setImportMode($a_val)
    {
        $this->import_mode = $a_val;
    }
    
    /**
     * Get import mode
     *
     * @return bool import mode
     */
    public function getImportMode()
    {
        return $this->import_mode;
    }

    public function needsImportParsing($a_parse = "")
    {
        if ($a_parse === true) {
            $this->needs_parsing = true;
        }
        if ($a_parse === false) {
            $this->needs_parsing = false;
        }
        return $this->needs_parsing;
    }

    /**
     * Set contains question
     *
     * @param	boolean	$a_val	contains question
     */
    // @todo: can we do this better
    public function setContainsQuestion($a_val)
    {
        $this->contains_question = $a_val;
    }

    /**
     * Get contains question
     *
     * @return	boolean	contains question
     */
    public function getContainsQuestion()
    {
        return $this->contains_question;
    }


    /**
    * get all media objects, that are referenced and used within
    * the page
    */
    // @todo: move to media class
    public function collectMediaObjects($a_inline_only = true)
    {
        //echo htmlentities($this->getXMLFromDom());
        // determine all media aliases of the page
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaObject/MediaAlias";
        $res = xpath_eval($xpc, $path);
        $mob_ids = array();
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $id_arr = explode("_", $res->nodeset[$i]->get_attribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            $mob_ids[$mob_id] = $mob_id;
        }

        // determine all media aliases of interactive images
        $xpc = xpath_new_context($this->dom);
        $path = "//InteractiveImage/MediaAlias";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $id_arr = explode("_", $res->nodeset[$i]->get_attribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            $mob_ids[$mob_id] = $mob_id;
        }

        // determine all inline internal media links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink[@Type = 'MediaObject']";
        $res = xpath_eval($xpc, $path);

        for ($i = 0; $i < count($res->nodeset); $i++) {
            if (($res->nodeset[$i]->get_attribute("TargetFrame") == "") ||
                (!$a_inline_only)) {
                $target = $res->nodeset[$i]->get_attribute("Target");
                $id_arr = explode("_", $target);
                if (($id_arr[1] == IL_INST_ID) ||
                    (substr($target, 0, 4) == "il__")) {
                    $mob_id = $id_arr[count($id_arr) - 1];
                    if (ilObject::_exists($mob_id)) {
                        $mob_ids[$mob_id] = $mob_id;
                    }
                }
            }
        }

        return $mob_ids;
    }


    /**
    * get all internal links that are used within the page
    */
    // @todo: can we do this better?
    public function getInternalLinks($a_cnt_multiple = false)
    {
        // get all internal links of the page
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);

        $links = array();
        $cnt_multiple = 1;
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $add = "";
            if ($a_cnt_multiple) {
                $add = ":" . $cnt_multiple;
            }
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");
            $targetframe = $res->nodeset[$i]->get_attribute("TargetFrame");
            $anchor = $res->nodeset[$i]->get_attribute("Anchor");
            $links[$target . ":" . $type . ":" . $targetframe . ":" . $anchor . $add] =
                array("Target" => $target, "Type" => $type,
                    "TargetFrame" => $targetframe, "Anchor" => $anchor);
                    
            // get links (image map areas) for inline media objects
            if ($type == "MediaObject" && $targetframe == "") {
                if (substr($target, 0, 4) == "il__") {
                    $id_arr = explode("_", $target);
                    $id = $id_arr[count($id_arr) - 1];
    
                    $med_links = ilMediaItem::_getMapAreasIntLinks($id);
                    foreach ($med_links as $key => $med_link) {
                        $links[$key] = $med_link;
                    }
                }
            }
            //echo "<br>-:".$target.":".$type.":".$targetframe.":-";
            $cnt_multiple++;
        }
        unset($xpc);

        // get all media aliases
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaAlias";
        $res = xpath_eval($xpc, $path);

        require_once("Services/MediaObjects/classes/class.ilMediaItem.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $oid = $res->nodeset[$i]->get_attribute("OriginId");
            if (substr($oid, 0, 4) == "il__") {
                $id_arr = explode("_", $oid);
                $id = $id_arr[count($id_arr) - 1];

                $med_links = ilMediaItem::_getMapAreasIntLinks($id);
                foreach ($med_links as $key => $med_link) {
                    $links[$key] = $med_link;
                }
            }
        }
        unset($xpc);

        return $links;
    }

    /**
    * get a xml string that contains all media object elements, that
    * are referenced by any media alias in the page
    */
    // @todo: move to media class
    public function getMultimediaXML()
    {
        $mob_ids = $this->collectMediaObjects();

        // get xml of corresponding media objects
        $mobs_xml = "";
        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        foreach ($mob_ids as $mob_id => $dummy) {
            if (ilObject::_lookupType($mob_id) == "mob") {
                $mob_obj = new ilObjMediaObject($mob_id);
                $mobs_xml .= $mob_obj->getXML(IL_MODE_OUTPUT, $a_inst = 0, true);
            }
        }
        //var_dump($mobs_xml);
        return $mobs_xml;
    }

    /**
    * get complete media object (alias) element
    */
    // @todo: move to media class
    public function getMediaAliasElement($a_mob_id, $a_nr = 1)
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaObject/MediaAlias[@OriginId='il__mob_$a_mob_id']";
        $res = xpath_eval($xpc, $path);
        $mal_node = $res->nodeset[$a_nr - 1];
        $mob_node = $mal_node->parent_node();

        return $this->dom->dump_node($mob_node);
    }

    /**
    * Validate the page content agains page DTD
    *
    * @return	array		Error array.
    */
    public function validateDom()
    {
        $this->stripHierIDs();

        // possible fix for #14820
        libxml_disable_entity_loader(false);

        @$this->dom->validate($error);
        //var_dump($this->dom); exit;
        return $error;
    }

    /**
    * Add hierarchical ID (e.g. for editing) attributes "HierId" to current dom tree.
    * This attribute will be added to the following elements:
    * PageObject, Paragraph, Table, TableRow, TableData.
    * Only elements of these types are counted as "childs" here.
    *
    * Hierarchical IDs have the format "x_y_z_...", e.g. "1_4_2" means: second
    * child of fourth child of first child of page.
    *
    * The PageObject element gets the special id "pg". The first child of the
    * page starts with id 1. The next child gets the 2 and so on.
    *
    * Another example: The first child of the page is a Paragraph -> id 1.
    * The second child is a table -> id 2. The first row gets the id 2_1, the
    */
    // @todo: can we do this better? remove dependencies?
    public function addHierIDs()
    {
        $this->hier_ids = array();
        $this->first_row_ids = array();
        $this->first_col_ids = array();
        $this->list_item_ids = array();
        $this->file_item_ids = array();

        // set hierarchical ids for Paragraphs, Tables, TableRows and TableData elements
        $xpc = xpath_new_context($this->dom);
        //$path = "//Paragraph | //Table | //TableRow | //TableData";
        
        $sep = $path = "";
        foreach ($this->id_elements as $el) {
            $path .= $sep . "//" . $el;
            $sep = " | ";
        }

        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $cnode = $res->nodeset[$i];
            $ctag = $cnode->node_name();

            // get hierarchical id of previous sibling
            $sib_hier_id = "";
            while ($cnode = $cnode->previous_sibling()) {
                if (($cnode->node_type() == XML_ELEMENT_NODE)
                    && $cnode->has_attribute("HierId")) {
                    $sib_hier_id = $cnode->get_attribute("HierId");
                    //$sib_hier_id = $id_attr->value();
                    break;
                }
            }

            if ($sib_hier_id != "") {		// set id to sibling id "+ 1"
                require_once("./Services/COPage/classes/class.ilPageContent.php");
                $node_hier_id = ilPageContent::incEdId($sib_hier_id);
                $res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
                $this->hier_ids[] = $node_hier_id;
                if ($ctag == "TableData") {
                    if (substr($par_hier_id, strlen($par_hier_id) - 2) == "_1") {
                        $this->first_row_ids[] = $node_hier_id;
                    }
                }
                if ($ctag == "ListItem") {
                    $this->list_item_ids[] = $node_hier_id;
                }
                if ($ctag == "FileItem") {
                    $this->file_item_ids[] = $node_hier_id;
                }
            } else {						// no sibling -> node is first child
                // get hierarchical id of next parent
                $cnode = $res->nodeset[$i];
                $par_hier_id = "";
                while ($cnode = $cnode->parent_node()) {
                    if (($cnode->node_type() == XML_ELEMENT_NODE)
                        && $cnode->has_attribute("HierId")) {
                        $par_hier_id = $cnode->get_attribute("HierId");
                        //$par_hier_id = $id_attr->value();
                        break;
                    }
                }
                //echo "<br>par:".$par_hier_id." ($ctag)";
                if (($par_hier_id != "") && ($par_hier_id != "pg")) {		// set id to parent_id."_1"
                    $node_hier_id = $par_hier_id . "_1";
                    $res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
                    $this->hier_ids[] = $node_hier_id;
                    if ($ctag == "TableData") {
                        $this->first_col_ids[] = $node_hier_id;
                        if (substr($par_hier_id, strlen($par_hier_id) - 2) == "_1") {
                            $this->first_row_ids[] = $node_hier_id;
                        }
                    }
                    if ($ctag == "ListItem") {
                        $this->list_item_ids[] = $node_hier_id;
                    }
                    if ($ctag == "FileItem") {
                        $this->file_item_ids[] = $node_hier_id;
                    }
                } else {		// no sibling, no parent -> first node
                    $node_hier_id = "1";
                    $res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
                    $this->hier_ids[] = $node_hier_id;
                }
            }
        }

        // set special hierarchical id "pg" for pageobject
        $xpc = xpath_new_context($this->dom);
        $path = "//PageObject";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {	// should only be 1
            $res->nodeset[$i]->set_attribute("HierId", "pg");
            $this->hier_ids[] = "pg";
        }
        unset($xpc);
    }

    /**
    * get all hierarchical ids
    */
    public function getHierIds()
    {
        return $this->hier_ids;
    }

    /**
    * get ids of all first table rows
    */
    // @todo: move to table classes
    public function getFirstRowIds()
    {
        return $this->first_row_ids;
    }
    
    /**
    * get ids of all first table columns
    */
    // @todo: move to table classes
    public function getFirstColumnIds()
    {
        return $this->first_col_ids;
    }
    
    /**
    * get ids of all list items
    */
    // @todo: move to list class
    public function getListItemIds()
    {
        return $this->list_item_ids;
    }
    
    /**
    * get ids of all file items
    */
    // @todo: move to file item class
    public function getFileItemIds()
    {
        return $this->file_item_ids;
    }
    
    /**
    * strip all hierarchical id attributes out of the dom tree
    */
    public function stripHierIDs()
    {
        if (is_object($this->dom)) {
            $xpc = xpath_new_context($this->dom);
            $path = "//*[@HierId]";
            $res = xpath_eval($xpc, $path);
            for ($i = 0; $i < count($res->nodeset); $i++) {	// should only be 1
                if ($res->nodeset[$i]->has_attribute("HierId")) {
                    $res->nodeset[$i]->remove_attribute("HierId");
                }
            }
            unset($xpc);
        }
    }

    /**
     * Get hier ids for a set of pc ids
     */
    public function getHierIdsForPCIds($a_pc_ids)
    {
        if (!is_array($a_pc_ids) || count($a_pc_ids) == 0) {
            return array();
        }
        $ret = array();

        if (is_object($this->dom)) {
            $xpc = xpath_new_context($this->dom);
            $path = "//*[@PCID]";
            $res = xpath_eval($xpc, $path);
            for ($i = 0; $i < count($res->nodeset); $i++) {	// should only be 1
                $pc_id = $res->nodeset[$i]->get_attribute("PCID");
                if (in_array($pc_id, $a_pc_ids)) {
                    $ret[$pc_id] = $res->nodeset[$i]->get_attribute("HierId");
                }
            }
            unset($xpc);
        }
        //var_dump($ret);
        return $ret;
    }

    /**
    * add file sizes
    */
    // @todo: move to file item class
    public function addFileSizes()
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//FileItem";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $cnode = $res->nodeset[$i];
            $size_node = $this->dom->create_element("Size");
            $size_node = $cnode->append_child($size_node);

            $childs = $cnode->child_nodes();
            $size = "";
            for ($j = 0; $j < count($childs); $j++) {
                if ($childs[$j]->node_name() == "Identifier") {
                    if ($childs[$j]->has_attribute("Entry")) {
                        $entry = $childs[$j]->get_attribute("Entry");
                        $entry_arr = explode("_", $entry);
                        $id = $entry_arr[count($entry_arr) - 1];
                        require_once("./Modules/File/classes/class.ilObjFile.php");
                        $size = ilObjFile::_lookupFileSize($id);
                    }
                }
            }
            $size_node->set_content($size);
        }

        unset($xpc);
    }

    /**
     * Resolves all internal link targets of the page, if targets are available
     * (after import)
     */
    // @todo: possible to improve this?
    public function resolveIntLinks($a_link_map = null)
    {
        $changed = false;
        
        $this->log->debug("start");

        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");

            if ($a_link_map == null) {
                $new_target = ilInternalLink::_getIdForImportId($type, $target);
                $this->log->debug("no map, type: " . $type . ", target: " . $target . ", new target: " . $new_target);
            //				echo "-".$new_target."-".$type."-".$target."-"; exit;
            } else {
                $nt = explode("_", $a_link_map[$target]);
                $new_target = false;
                if ($nt[1] == IL_INST_ID) {
                    $new_target = "il__" . $nt[2] . "_" . $nt[3];
                }
                $this->log->debug("map, type: " . $type . ", target: " . $target . ", new target: " . $new_target);
            }
            if ($new_target !== false) {
                $res->nodeset[$i]->set_attribute("Target", $new_target);
                $changed = true;
            } else {		// check wether link target is same installation
                if (ilInternalLink::_extractInstOfTarget($target) == IL_INST_ID &&
                    IL_INST_ID > 0 && $type != "RepositoryItem") {
                    $new_target = ilInternalLink::_removeInstFromTarget($target);
                    if (ilInternalLink::_exists($type, $new_target)) {
                        $res->nodeset[$i]->set_attribute("Target", $new_target);
                        $changed = true;
                    }
                }
            }
        }
        unset($xpc);

        // resolve internal links in map areas
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaAlias";
        $res = xpath_eval($xpc, $path);
        //echo "<br><b>page::resolve</b><br>";
        //echo "Content:".htmlentities($this->getXMLFromDOM()).":<br>";
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $orig_id = $res->nodeset[$i]->get_attribute("OriginId");
            $id_arr = explode("_", $orig_id);
            $mob_id = $id_arr[count($id_arr) - 1];
            ilMediaItem::_resolveMapAreaLinks($mob_id);
        }
        return $changed;
    }

    /**
     * Resolve media aliases
     * (after import)
     *
     * @param	array		mapping array
     */
    // @todo: move to media classes?
    public function resolveMediaAliases($a_mapping, $a_reuse_existing_by_import = false)
    {
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaAlias";
        $res = xpath_eval($xpc, $path);
        $changed = false;
        for ($i = 0; $i < count($res->nodeset); $i++) {
            // get the ID of the import file from the xml
            $old_id = $res->nodeset[$i]->get_attribute("OriginId");
            $old_id = explode("_", $old_id);
            $old_id = $old_id[count($old_id) - 1];
            $new_id = "";
            $import_id = "";
            // get the new id from the current mapping
            if ($a_mapping[$old_id] > 0) {
                $new_id = $a_mapping[$old_id];
                if ($a_reuse_existing_by_import) {
                    // this should work, if the lm has been imported in a translation installation and re-exported
                    $import_id = ilObject::_lookupImportId($new_id);
                    $imp = explode("_", $import_id);
                    if ($imp[1] == IL_INST_ID && $imp[2] == "mob" && ilObject::_lookupType($imp[3]) == "mob") {
                        $new_id = $imp[3];
                    }
                }
            }
            // now check, if the translation has been done just by changing text in the exported
            // translation file
            if ($import_id == "" && $a_reuse_existing_by_import) {
                // if the old_id is also referred by the page content of the default language
                // we assume that this media object is unchanged
                include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                $med_of_def_lang = ilObjMediaObject::_getMobsOfObject($this->getParentType() . ":pg", $this->getId(), 0, "-");
                if (in_array($old_id, $med_of_def_lang)) {
                    $new_id = $old_id;
                }
            }
            if ($new_id != "") {
                $res->nodeset[$i]->set_attribute("OriginId", "il__mob_" . $new_id);
                $changed = true;
            }
        }
        unset($xpc);
        return $changed;
    }

    /**
     * Resolve iim media aliases
     * (in ilContObjParse)
     *
     * @param	array		mapping array
     */
    // @todo: move to iim classes?
    public function resolveIIMMediaAliases($a_mapping)
    {
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//InteractiveImage/MediaAlias";
        $res = xpath_eval($xpc, $path);
        $changed = false;
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $old_id = $res->nodeset[$i]->get_attribute("OriginId");
            if ($a_mapping[$old_id] > 0) {
                $res->nodeset[$i]->set_attribute("OriginId", "il__mob_" . $a_mapping[$old_id]);
                $changed = true;
            }
        }
        unset($xpc);

        return $changed;
    }

    /**
     * Resolve file items
     * (after import)
     *
     * @param	array		mapping array
     */
    // @todo: move to file classes?
    public function resolveFileItems($a_mapping)
    {
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//FileItem/Identifier";
        $res = xpath_eval($xpc, $path);
        $changed = false;
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $old_id = $res->nodeset[$i]->get_attribute("Entry");
            $old_id = explode("_", $old_id);
            $old_id = $old_id[count($old_id) - 1];
            if ($a_mapping[$old_id] > 0) {
                $res->nodeset[$i]->set_attribute("Entry", "il__file_" . $a_mapping[$old_id]);
                $changed = true;
            }
        }
        unset($xpc);

        return $changed;
    }

    /**
     * Resolve all quesion references
     * (after import)
     */
    // @todo: move to question classes
    public function resolveQuestionReferences($a_mapping)
    {
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//Question";
        $res = xpath_eval($xpc, $path);
        $updated = false;
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $qref = $res->nodeset[$i]->get_attribute("QRef");

            if (isset($a_mapping[$qref])) {
                $res->nodeset[$i]->set_attribute("QRef", "il__qst_" . $a_mapping[$qref]["pool"]);
                $updated = true;
            }
        }
        unset($xpc);

        return $updated;
    }


    /**
    * Move internal links from one destination to another. This is used
    * for pages and structure links. Just use IDs in "from" and "to".
    *
    * @param	array	keys are the old targets, values are the new targets
    */
    // @todo: generalize, internal links usage info
    public function moveIntLinks($a_from_to)
    {
        $this->buildDom();
        
        $changed = false;
        
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");
            $obj_id = ilInternalLink::_extractObjIdOfTarget($target);
            if ($a_from_to[$obj_id] > 0 && is_int(strpos($target, "__"))) {
                if ($type == "PageObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "pg") {
                    $res->nodeset[$i]->set_attribute("Target", "il__pg_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
                if ($type == "StructureObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "st") {
                    $res->nodeset[$i]->set_attribute("Target", "il__st_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
                if ($type == "PortfolioPage") {
                    $res->nodeset[$i]->set_attribute("Target", "il__ppage_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
            }
        }
        unset($xpc);
        
        // map areas
        $this->addHierIDs();
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaAlias";
        $res = xpath_eval($xpc, $path);
        
        require_once("Services/MediaObjects/classes/class.ilMediaItem.php");
        require_once("Services/COPage/classes/class.ilMediaAliasItem.php");

        for ($i = 0; $i < count($res->nodeset); $i++) {
            $media_object_node = $res->nodeset[$i]->parent_node();
            $page_content_node = $media_object_node->parent_node();
            $c_hier_id = $page_content_node->get_attribute("HierId");

            // first check, wheter we got instance map areas -> take these
            $std_alias_item = new ilMediaAliasItem(
                $this->dom,
                $c_hier_id,
                "Standard"
            );
            $areas = $std_alias_item->getMapAreas();
            $correction_needed = false;
            if (count($areas) > 0) {
                // check if correction needed
                foreach ($areas as $area) {
                    if ($area["Type"] == "PageObject" ||
                        $area["Type"] == "StructureObject") {
                        $t = $area["Target"];
                        $tid = _extractObjIdOfTarget($t);
                        if ($a_from_to[$tid] > 0) {
                            $correction_needed = true;
                        }
                    }
                }
            } else {
                $areas = array();

                // get object map areas and check whether at least one must
                // be corrected
                $oid = $res->nodeset[$i]->get_attribute("OriginId");
                if (substr($oid, 0, 4) == "il__") {
                    $id_arr = explode("_", $oid);
                    $id = $id_arr[count($id_arr) - 1];
    
                    $mob = new ilObjMediaObject($id);
                    $med_item = $mob->getMediaItem("Standard");
                    $med_areas = $med_item->getMapAreas();

                    foreach ($med_areas as $area) {
                        $link_type = ($area->getLinkType() == "int")
                            ? "IntLink"
                            : "ExtLink";
                        
                        $areas[] = array(
                            "Nr" => $area->getNr(),
                            "Shape" => $area->getShape(),
                            "Coords" => $area->getCoords(),
                            "Link" => array(
                                "LinkType" => $link_type,
                                "Href" => $area->getHref(),
                                "Title" => $area->getTitle(),
                                "Target" => $area->getTarget(),
                                "Type" => $area->getType(),
                                "TargetFrame" => $area->getTargetFrame()
                                )
                            );

                        if ($area->getType() == "PageObject" ||
                            $area->getType() == "StructureObject") {
                            $t = $area->getTarget();
                            $tid = ilInternalLink::_extractObjIdOfTarget($t);
                            if ($a_from_to[$tid] > 0) {
                                $correction_needed = true;
                            }
                            //var_dump($a_from_to);
                        }
                    }
                }
            }
            
            // correct map area links
            if ($correction_needed) {
                $changed = true;
                $std_alias_item->deleteAllMapAreas();
                foreach ($areas as $area) {
                    if ($area["Link"]["LinkType"] == "IntLink") {
                        $target = $area["Link"]["Target"];
                        $type = $area["Link"]["Type"];
                        $obj_id = ilInternalLink::_extractObjIdOfTarget($target);
                        if ($a_from_to[$obj_id] > 0) {
                            if ($type == "PageObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "pg") {
                                $area["Link"]["Target"] = "il__pg_" . $a_from_to[$obj_id];
                            }
                            if ($type == "StructureObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "st") {
                                $area["Link"]["Target"] = "il__st_" . $a_from_to[$obj_id];
                            }
                        }
                    }
                    
                    $std_alias_item->addMapArea(
                        $area["Shape"],
                        $area["Coords"],
                        $area["Link"]["Title"],
                        array(	"Type" => $area["Link"]["Type"],
                                "TargetFrame" => $area["Link"]["TargetFrame"],
                                "Target" => $area["Link"]["Target"],
                                "Href" => $area["Link"]["Href"],
                                "LinkType" => $area["Link"]["LinkType"],
                        )
                    );
                }
            }
        }
        unset($xpc);

        return $changed;
    }

    /**
    * Change targest of repository links. Use full targets in "from" and "to"!!!
    *
    * @param	array	keys are the old targets, values are the new targets
    */
    // @todo: generalize, internal links usage info
    public static function _handleImportRepositoryLinks($a_rep_import_id, $a_rep_type, $a_rep_ref_id)
    {
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        
        //echo "-".$a_rep_import_id."-".$a_rep_ref_id."-";
        $sources = ilInternalLink::_getSourcesOfTarget(
            "obj",
            ilInternalLink::_extractObjIdOfTarget($a_rep_import_id),
            ilInternalLink::_extractInstOfTarget($a_rep_import_id)
        );
        //var_dump($sources);
        foreach ($sources as $source) {
            //echo "A";
            if ($source["type"] == "lm:pg") {
                //echo "B";
                include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                if (self::_exists("lm", $source["id"], $source["lang"])) {
                    $page_obj = new ilLMPage($source["id"], 0, $source["lang"]);
                    if (!$page_obj->page_not_found) {
                        //echo "C";
                        $page_obj->handleImportRepositoryLink(
                            $a_rep_import_id,
                            $a_rep_type,
                            $a_rep_ref_id
                        );
                    }
                    $page_obj->update();
                }
            }
        }
    }
        
    // @todo: generalize, internal links usage info
    public function handleImportRepositoryLink($a_rep_import_id, $a_rep_type, $a_rep_ref_id)
    {
        $this->buildDom();
        
        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        //echo "1";
        for ($i = 0; $i < count($res->nodeset); $i++) {
            //echo "2";
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");
            if ($target == $a_rep_import_id && $type == "RepositoryItem") {
                //echo "setting:"."il__".$a_rep_type."_".$a_rep_ref_id;
                $res->nodeset[$i]->set_attribute(
                    "Target",
                    "il__" . $a_rep_type . "_" . $a_rep_ref_id
                );
            }
        }
        unset($xpc);
    }

    /**
     * Handle repository links on copy process
     * @param array $a_mapping
     * @param int $a_source_ref_id
     */
    public function handleRepositoryLinksOnCopy($a_mapping, $a_source_ref_id)
    {
        $tree = $this->tree;
        $objDefinition = $this->obj_definition;

        $this->buildDom();
        $this->log->debug("Handle repository links...");

        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");
            $this->log->debug("Target: " . $target);
            $t = explode("_", $target);
            if ($type == "RepositoryItem" && ((int) $t[1] == 0 || (int) $t[1] == IL_INST_ID)) {
                if (isset($a_mapping[$t[3]])) {
                    // we have a mapping -> replace the ID
                    $this->log->debug("... replace " . $t[3] . " with " . $a_mapping[$t[3]] . ".");
                    $res->nodeset[$i]->set_attribute(
                        "Target",
                        "il__obj_" . $a_mapping[$t[3]]
                    );
                } elseif ($this->tree->isGrandChild($a_source_ref_id, $t[3])) {
                    // we have no mapping, but the linked object is child of the original node -> remove link
                    $this->log->debug("... remove links.");
                    if ($res->nodeset[$i]->parent_node()->node_name() == "MapArea") {	// simply remove map areas
                        $parent = $res->nodeset[$i]->parent_node();
                        $parent->unlink_node($parent);
                    } else {	// replace link by content of the link for other internal links
                        $source_node = $res->nodeset[$i];
                        $new_node = $source_node->clone_node(true);
                        $new_node->unlink_node($new_node);
                        $childs = $new_node->child_nodes();
                        for ($j = 0; $j < count($childs); $j++) {
                            $this->log->debug("... move node $j " . $childs[$j]->node_name() . " before " . $source_node->node_name());
                            $source_node->insert_before($childs[$j], $source_node);
                        }
                        $source_node->unlink_node($source_node);
                    }
                }
            }
        }
        unset($xpc);

        // resolve normal external links
        $ilias_url = parse_url(ILIAS_HTTP_PATH);
        $xpc = xpath_new_context($this->dom);
        $path = "//ExtLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $href = $res->nodeset[$i]->get_attribute("Href");
            $this->log->debug("Href: " . $href);

            $url = parse_url($href);

            // only handle links on same host
            $this->log->debug("Host: " . $url["host"]);
            if ($url["host"] != "" && $url["host"] != $ilias_url["host"]) {
                continue;
            }

            // get parameters
            $par = array();
            foreach (explode("&", $url["query"]) as $p) {
                $p = explode("=", $p);
                $par[$p[0]] = $p[1];
            }

            $target_client_id = $par["client_id"];
            if ($target_client_id != "" && $target_client_id != CLIENT_ID) {
                continue;
            }

            // get ref id
            $ref_id = 0;
            if (is_int(strpos($href, "goto.php"))) {
                $t = explode("_", $par["target"]);
                if ($objDefinition->isRBACObject($t[0])) {
                    $ref_id = (int) $t[1];
                    $type = $t[0];
                }
            } elseif (is_int(strpos($href, "ilias.php"))) {
                $ref_id = (int) $par["ref_id"];
            }


            if ($ref_id > 0) {
                if (isset($a_mapping[$ref_id])) {
                    $new_ref_id = $a_mapping[$ref_id];
                    $new_href = "";
                    // we have a mapping -> replace the ID
                    if (is_int(strpos($href, "goto.php"))) {
                        $nt = str_replace($type . "_" . $ref_id, $type . "_" . $new_ref_id, $par["target"]);
                        $new_href = str_replace("target=" . $par["target"], "target=" . $nt, $href);
                    } elseif (is_int(strpos($href, "ilias.php"))) {
                        $new_href = str_replace("ref_id=" . $par["ref_id"], "ref_id=" . $new_ref_id, $href);
                    }
                    if ($new_href != "") {
                        $this->log->debug("... ext link replace " . $href . " with " . $new_href . ".");
                        $res->nodeset[$i]->set_attribute("Href", $new_href);
                    }
                } elseif ($tree->isGrandChild($a_source_ref_id, $ref_id)) {
                    // we have no mapping, but the linked object is child of the original node -> remove link
                    $this->log->debug("... remove ext links.");
                    if ($res->nodeset[$i]->parent_node()->node_name() == "MapArea") {	// simply remove map areas
                        $parent = $res->nodeset[$i]->parent_node();
                        $parent->unlink_node($parent);
                    } else {	// replace link by content of the link for other internal links
                        $source_node = $res->nodeset[$i];
                        $new_node = $source_node->clone_node(true);
                        $new_node->unlink_node($new_node);
                        $childs = $new_node->child_nodes();
                        for ($j = 0; $j < count($childs); $j++) {
                            $this->log->debug("... move node $j " . $childs[$j]->node_name() . " before " . $source_node->node_name());
                            $source_node->insert_before($childs[$j], $source_node);
                        }
                        $source_node->unlink_node($source_node);
                    }
                }
            }
        }
        unset($xpc);
    }


    /**
     * Create new page object with current xml content
     */
    public function createFromXML()
    {
        $empty = false;
        if ($this->getXMLContent() == "") {
            $this->setXMLContent("<PageObject></PageObject>");
            $empty = true;
        }
        
        $content = $this->getXMLContent();
        $this->buildDom(true);
        $dom_doc = $this->getDomDoc();
        
        $iel = $this->containsDeactivatedElements($content);
        $inl = $this->containsIntLinks($content);
                
        // create object
        $this->db->insert("page_object", array(
            "page_id" => array("integer", $this->getId()),
            "parent_id" => array("integer", $this->getParentId()),
            "lang" => array("text", $this->getLanguage()),
            "content" => array("clob", $content),
            "parent_type" => array("text", $this->getParentType()),
            "create_user" => array("integer", $this->user->getId()),
            "last_change_user" => array("integer", $this->user->getId()),
            "active" => array("integer", (int) $this->getActive()),
            "activation_start" => array("timestamp", $this->getActivationStart()),
            "activation_end" => array("timestamp", $this->getActivationEnd()),
            "show_activation_info" => array("integer", (int) $this->getShowActivationInfo()),
            "inactive_elements" => array("integer", $iel),
            "int_links" => array("integer", $inl),
            "created" => array("timestamp", ilUtil::now()),
            "last_change" => array("timestamp", ilUtil::now())
            ));
        
        // after update event
        $this->__afterUpdate($dom_doc, $content, true, $empty);
    }


    /**
     * Updates page object with current xml content
     *
     * This function is currently (4.4.0 alpha) called by:
     * - ilContObjParser (LM and Glossary import parser)
     * - ilSCORM13Package->dbImportSco (SCORM importer)
     * - assQuestion->copyPageOfQuestion
     */
    public function updateFromXML()
    {
        $this->log->debug("ilPageObject, updateFromXML(): start, id: " . $this->getId());

        $content = $this->getXMLContent();

        $this->log->debug("ilPageObject, updateFromXML(): content: " . substr($content, 0, 100));

        $this->buildDom(true);
        $dom_doc = $this->getDomDoc();

        $iel = $this->containsDeactivatedElements($content);
        $inl = $this->containsIntLinks($content);

        $this->db->update("page_object", array(
            "content" => array("clob", $content),
            "parent_id" => array("integer", $this->getParentId()),
            "last_change_user" => array("integer", $this->user->getId()),
            "last_change" => array("timestamp", ilUtil::now()),
            "active" => array("integer", $this->getActive()),
            "activation_start" => array("timestamp", $this->getActivationStart()),
            "activation_end" => array("timestamp", $this->getActivationEnd()),
            "inactive_elements" => array("integer", $iel),
            "int_links" => array("integer", $inl),
            ), array(
            "page_id" => array("integer", $this->getId()),
            "parent_type" => array("text", $this->getParentType()),
            "lang" => array("text", $this->getLanguage())
            ));

        // after update event
        $this->__afterUpdate($dom_doc, $content);

        $this->log->debug("ilPageObject, updateFromXML(): end");

        return true;
    }
    
    /**
     * After update event handler (internal). The hooks are e.g. for
     * storing any dependent relations/references in the database.
     *
     * @param
     */
    final protected function __afterUpdate($a_domdoc, $a_xml, $a_creation = false, $a_empty = false)
    {
        // we do not need this if we are creating an empty page
        if (!$a_creation || !$a_empty) {
            // save internal link information
            // the page object is responsible to do this, since it "offers" the
            // internal link feature pc and page classes
            $this->saveInternalLinks($a_domdoc);

            // save style usage
            $this->saveStyleUsage($a_domdoc);

            // pc classes hook
            include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
            $defs = ilCOPagePCDef::getPCDefinitions();
            foreach ($defs as $def) {
                ilCOPagePCDef::requirePCClassByName($def["name"]);
                $cl = $def["pc_class"];
                call_user_func($def["pc_class"] . '::afterPageUpdate', $this, $a_domdoc, $a_xml, $a_creation);
            }
        }
                
        // call page hook
        $this->afterUpdate($a_domdoc, $a_xml);
        
        // call update listeners
        $this->callUpdateListeners();
    }
    
    /**
     * After update
     *
     * @param
     * @return
     */
    public function afterUpdate()
    {
    }
    

    /**
     * update complete page content in db (dom xml content is used)
     *
     */
    public function update($a_validate = true, $a_no_history = false)
    {
        $this->log->debug("ilPageObject, update(): start, id: " . $this->getId());

        $lm_set = new ilSetting("lm");

        // add missing pc ids
        if (!$this->checkPCIds()) {
            $this->insertPCIds();
        }

        // test validating
        if ($a_validate) {
            $errors = $this->validateDom();
        }
        //var_dump($errors); exit;
        if (empty($errors) && !$this->getEditLock()) {
            include_once("./Services/User/classes/class.ilUserUtil.php");
            $lock = $this->getEditLockInfo();
            $errors[0] = array(0 => 0,
                1 => "nocontent#" . $this->lng->txt("cont_not_saved_edit_lock_expired") . "<br />" .
                    $this->lng->txt("obj_usr") . ": " .
                ilUserUtil::getNamePresentation($lock["edit_lock_user"]) . "<br />" .
                    $this->lng->txt("content_until") . ": " .
                ilDatePresentation::formatDate(new ilDateTime($lock["edit_lock_until"], IL_CAL_UNIX))
            );
        }

        if (!empty($errors)) {
            $this->log->debug("ilPageObject, update(): errors: " . print_r($errors, true));
        }

        //echo "-".htmlentities($this->getXMLFromDom())."-"; exit;
        if (empty($errors)) {
            // @todo 1: is this page type or pc content type
            // related -> plugins should be able to hook in!?
            $this->performAutomaticModifications();
            
            // get xml content
            $content = $this->getXMLFromDom();
            $dom_doc = $this->getDomDoc();
            
            // this needs to be locked

            // write history entry
            $old_set = $this->db->query("SELECT * FROM page_object WHERE " .
                "page_id = " . $this->db->quote($this->getId(), "integer") . " AND " .
                "parent_type = " . $this->db->quote($this->getParentType(), "text") . " AND " .
                "lang = " . $this->db->quote($this->getLanguage(), "text"));
            $last_nr_set = $this->db->query("SELECT max(nr) as mnr FROM page_history WHERE " .
                "page_id = " . $this->db->quote($this->getId(), "integer") . " AND " .
                "parent_type = " . $this->db->quote($this->getParentType(), "text") . " AND " .
                "lang = " . $this->db->quote($this->getLanguage(), "text"));
            $last_nr = $this->db->fetchAssoc($last_nr_set);
            if ($old_rec = $this->db->fetchAssoc($old_set)) {
                // only save, if something has changed
                // added user id to the check for ilias 5.0, 7.10.2014
                if (($content != $old_rec["content"] || $this->user->getId() != $old_rec["last_change_user"]) &&
                    !$a_no_history && !$this->history_saved && $lm_set->get("page_history", 1)) {
                    if ($old_rec["content"] != "<PageObject></PageObject>") {
                        $this->db->manipulateF(
                            "DELETE FROM page_history WHERE " .
                            "page_id = %s AND parent_type = %s AND hdate = %s AND lang = %s",
                            array("integer", "text", "timestamp", "text"),
                            array($old_rec["page_id"], $old_rec["parent_type"], $old_rec["last_change"], $old_rec["lang"])
                        );

                        // the following lines are a workaround for
                        // bug 6741
                        $last_c = $old_rec["last_change"];
                        if ($last_c == "") {
                            $last_c = ilUtil::now();
                        }

                        $this->db->insert("page_history", array(
                            "page_id" => array("integer", $old_rec["page_id"]),
                            "parent_type" => array("text", $old_rec["parent_type"]),
                            "lang" => array("text", $old_rec["lang"]),
                            "hdate" => array("timestamp", $last_c),
                            "parent_id" => array("integer", $old_rec["parent_id"]),
                            "content" => array("clob", $old_rec["content"]),
                            "user_id" => array("integer", $old_rec["last_change_user"]),
                            "ilias_version" => array("text", ILIAS_VERSION_NUMERIC),
                            "nr" => array("integer", (int) $last_nr["mnr"] + 1)
                            ));
                        
                        $old_content = $old_rec["content"];
                        $old_domdoc = new DOMDocument();
                        $old_nr = $last_nr["mnr"] + 1;
                        $old_domdoc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $old_content);

                        // after history entry creation event
                        $this->__afterHistoryEntry($old_domdoc, $old_content, $old_nr);
                        
                        $this->history_saved = true;		// only save one time
                    } else {
                        $this->history_saved = true;		// do not save on first change
                    }
                }
            }
            //echo htmlentities($content);
            $em = (trim($content) == "<PageObject/>")
                ? 1
                : 0;
            
            // @todo: pass dom instead?
            $iel = $this->containsDeactivatedElements($content);
            $inl = $this->containsIntLinks($content);

            $this->db->update("page_object", array(
                "content" => array("clob", $content),
                "parent_id" => array("integer", $this->getParentId()),
                "last_change_user" => array("integer", $this->user->getId()),
                "last_change" => array("timestamp", ilUtil::now()),
                "is_empty" => array("integer", $em),
                "active" => array("integer", $this->getActive()),
                "activation_start" => array("timestamp", $this->getActivationStart()),
                "activation_end" => array("timestamp", $this->getActivationEnd()),
                "show_activation_info" => array("integer", $this->getShowActivationInfo()),
                "inactive_elements" => array("integer", $iel),
                "int_links" => array("integer", $inl),
                ), array(
                "page_id" => array("integer", $this->getId()),
                "parent_type" => array("text", $this->getParentType()),
                "lang" => array("text", $this->getLanguage())
                ));
                        
            // after update event
            $this->__afterUpdate($dom_doc, $content);

            $this->log->debug("ilPageObject, update(): updated and returning true, content: " . substr($this->getXMLContent(), 0, 100));

            //echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":";
            return true;
        } else {
            return $errors;
        }
    }

    

    /**
     * delete page object
     */
    public function delete()
    {
        $copg_logger = ilLoggerFactory::getLogger('copg');
        $copg_logger->debug(
            "ilPageObject: Delete called for ID '" . $this->getId() . "'," .
            " parent type: '" . $this->getParentType() . "', " .
            " hist nr: '" . $this->old_nr . "', " .
            " lang: '" . $this->getLanguage() . "', "
        );

        $mobs = array();
        $files = array();
        
        if (!$this->page_not_found) {
            $this->buildDom();
            $mobs = $this->collectMediaObjects(false);
        }
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mobs2 = ilObjMediaObject::_getMobsOfObject($this->getParentType() . ":pg", $this->getId(), false);
        foreach ($mobs2 as $m) {
            if (!in_array($m, $mobs)) {
                $mobs[] = $m;
            }
        }

        $copg_logger->debug("ilPageObject: ... found " . count($mobs) . " media objects.");

        $this->__beforeDelete();

        // treat plugged content
        $this->handleDeleteContent();

        // delete style usages
        $this->deleteStyleUsages(false);

        // delete internal links
        $this->deleteInternalLinks();

        // delete all mob usages
        ilObjMediaObject::_deleteAllUsages($this->getParentType() . ":pg", $this->getId());

        // delete news
        include_once("./Services/News/classes/class.ilNewsItem.php");
        ilNewsItem::deleteNewsOfContext(
            $this->getParentId(),
            $this->getParentType(),
            $this->getId(),
            "pg"
        );

        // delete page_object entry
        $this->db->manipulate("DELETE FROM page_object " .
            "WHERE page_id = " . $this->db->quote($this->getId(), "integer") .
            " AND parent_type= " . $this->db->quote($this->getParentType(), "text"));

        // delete media objects
        foreach ($mobs as $mob_id) {
            $copg_logger->debug("ilPageObject: ... processing mob " . $mob_id . ".");

            if (ilObject::_lookupType($mob_id) != 'mob') {
                $copg_logger->debug("ilPageObject: ... type mismatch. Ignoring mob " . $mob_id . ".");
                continue;
            }
            
            if (ilObject::_exists($mob_id)) {
                $copg_logger->debug("ilPageObject: ... delete mob " . $mob_id . ".");

                $mob_obj = new ilObjMediaObject($mob_id);
                $mob_obj->delete();
            } else {
                $copg_logger->debug("ilPageObject: ... missing mob " . $mob_id . ".");
            }
        }
    }

    /**
     * Before deletion handler (internal).
     *
     * @param
     */
    final protected function __beforeDelete()
    {
        // pc classes hook
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $defs = ilCOPagePCDef::getPCDefinitions();
        foreach ($defs as $def) {
            ilCOPagePCDef::requirePCClassByName($def["name"]);
            $cl = $def["pc_class"];
            call_user_func($def["pc_class"] . '::beforePageDelete', $this);
        }
    }
    
    /**
     * Before deletion handler (internal).
     *
     * @param
     */
    final protected function __afterHistoryEntry($a_old_domdoc, $a_old_content, $a_old_nr)
    {
        // save style usage
        $this->saveStyleUsage($a_old_domdoc, $a_old_nr);
        
        // pc classes hook
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $defs = ilCOPagePCDef::getPCDefinitions();
        foreach ($defs as $def) {
            ilCOPagePCDef::requirePCClassByName($def["name"]);
            $cl = $def["pc_class"];
            call_user_func($def["pc_class"] . '::afterPageHistoryEntry', $this, $a_old_domdoc, $a_old_content, $a_old_nr);
        }
    }

    /**
     * Save all style class/template usages
     *
     * @param	string		$a_xml		xml data of page
     */
    public function saveStyleUsage($a_domdoc, $a_old_nr = 0)
    {
        // media aliases
        $xpath = new DOMXPath($a_domdoc);
        $path = "//Paragraph | //Section | //MediaAlias | //FileItem" .
            " | //Table | //TableData | //Tabs | //List";
        $nodes = $xpath->query($path);
        $usages = array();
        foreach ($nodes as $node) {
            switch ($node->localName) {
                case "Paragraph":
                    $sname = $node->getAttribute("Characteristic");
                    $stype = "text_block";
                    $template = 0;
                    break;

                case "Section":
                    $sname = $node->getAttribute("Characteristic");
                    $stype = "section";
                    $template = 0;
                    break;

                case "MediaAlias":
                    $sname = $node->getAttribute("Class");
                    $stype = "media_cont";
                    $template = 0;
                    break;

                case "FileItem":
                    $sname = $node->getAttribute("Class");
                    $stype = "flist_li";
                    $template = 0;
                    break;

                case "Table":
                    $sname = $node->getAttribute("Template");
                    if ($sname == "") {
                        $sname = $node->getAttribute("Class");
                        $stype = "table";
                        $template = 0;
                    } else {
                        $stype = "table";
                        $template = 1;
                    }
                    break;

                case "TableData":
                    $sname = $node->getAttribute("Class");
                    $stype = "table_cell";
                    $template = 0;
                    break;

                case "Tabs":
                    $sname = $node->getAttribute("Template");
                    if ($sname != "") {
                        if ($node->getAttribute("Type") == "HorizontalAccordion") {
                            $stype = "haccordion";
                        }
                        if ($node->getAttribute("Type") == "VerticalAccordion") {
                            $stype = "vaccordion";
                        }
                    }
                    $template = 1;
                    break;
                
                case "List":
                    $sname = $node->getAttribute("Class");
                    if ($node->getAttribute("Type") == "Ordered") {
                        $stype = "list_o";
                    } else {
                        $stype = "list_u";
                    }
                    $template = 0;
                    break;
            }
            if ($sname != "" && $stype != "") {
                $usages[$sname . ":" . $stype . ":" . $template] = array("sname" => $sname,
                    "stype" => $stype, "template" => $template);
            }
        }
        
        
        $this->deleteStyleUsages($a_old_nr);
        
        foreach ($usages as $u) {
            $id = $this->db->nextId('page_style_usage');

            $this->db->manipulate("INSERT INTO page_style_usage " .
                "(id, page_id, page_type, page_lang, page_nr, template, stype, sname) VALUES (" .
                $this->db->quote($id, "integer") . "," .
                $this->db->quote($this->getId(), "integer") . "," .
                $this->db->quote($this->getParentType(), "text") . "," .
                $this->db->quote($this->getLanguage(), "text") . "," .
                $this->db->quote($a_old_nr, "integer") . "," .
                $this->db->quote($u["template"], "integer") . "," .
                $this->db->quote($u["stype"], "text") . "," .
                $this->db->quote($u["sname"], "text") .
                ")");
        }
    }
    
    /**
     * Delete style usages
     *
     * @param
     * @return
     */
    public function deleteStyleUsages($a_old_nr = 0)
    {
        if ($a_old_nr !== false) {
            $and_old_nr = " AND page_nr = " . $this->db->quote($a_old_nr, "integer");
        }

        $this->db->manipulate(
            "DELETE FROM page_style_usage WHERE " .
            " page_id = " . $this->db->quote($this->getId(), "integer") .
            " AND page_type = " . $this->db->quote($this->getParentType(), "text") .
            " AND page_lang = " . $this->db->quote($this->getLanguage(), "text") .
            $and_old_nr
        );
    }
    

    /**
    * Get last update of included elements (media objects and files).
    * This is needed for cache logic, cache must be reloaded if anything has changed.
    */
    // @todo: move to content include class
    public function getLastUpdateOfIncludedElements()
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        include_once("./Modules/File/classes/class.ilObjFile.php");
        $mobs = ilObjMediaObject::_getMobsOfObject(
            $this->getParentType() . ":pg",
            $this->getId()
        );
        $files = ilObjFile::_getFilesOfObject(
            $this->getParentType() . ":pg",
            $this->getId()
        );
        $objs = array_merge($mobs, $files);
        return ilObject::_getLastUpdateOfObjects($objs);
    }

    /**
     * Delete internal links
     *
     * @param
     * @return
     */
    public function deleteInternalLinks()
    {
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        ilInternalLink::_deleteAllLinksOfSource(
            $this->getParentType() . ":pg",
            $this->getId(),
            $this->getLanguage()
        );
    }
    
    
    /**
     * save internal links of page
     *
     * @param	string		xml page code
     */
    // @todo: move to specific classes, internal link use info
    public function saveInternalLinks($a_domdoc)
    {
        $this->deleteInternalLinks();

        // query IntLink elements
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//IntLink');
        foreach ($nodes as $node) {
            $link_type = $node->getAttribute("Type");

            switch ($link_type) {
                case "StructureObject":
                    $t_type = "st";
                    break;

                case "PageObject":
                    $t_type = "pg";
                    break;

                case "GlossaryItem":
                    $t_type = "git";
                    break;

                case "MediaObject":
                    $t_type = "mob";
                    break;

                case "RepositoryItem":
                    $t_type = "obj";
                    break;

                case "File":
                    $t_type = "file";
                    break;

                case "WikiPage":
                    $t_type = "wpage";
                    break;

                case "PortfolioPage":
                    $t_type = "ppage";
                    break;

                case "User":
                    $t_type = "user";
                    break;
            }

            $target = $node->getAttribute("Target");
            $target_arr = explode("_", $target);
            $t_id = $target_arr[count($target_arr) - 1];

            // link to other internal object
            if (is_int(strpos($target, "__"))) {
                $t_inst = 0;
            } else {	// link to unresolved object in other installation
                $t_inst = $target_arr[1];
            }

            if ($t_id > 0) {
                ilInternalLink::_saveLink(
                    $this->getParentType() . ":pg",
                    $this->getId(),
                    $t_type,
                    $t_id,
                    $t_inst,
                    $this->getLanguage()
                );
            }
        }
    }

    /**
    * create new page (with current xml data)
    */
    public function create()
    {
        $this->createFromXML();
    }

    /**
    * delete content object with hierarchical id $a_hid
    *
    * @param	string		$a_hid		hierarchical id of content object
    * @param	boolean		$a_update	update page in db (note: update deletes all
    *									hierarchical ids in DOM!)
    */
    public function deleteContent($a_hid, $a_update = true, $a_pcid = "")
    {
        $curr_node = $this->getContentNode($a_hid, $a_pcid);
        $this->handleDeleteContent($curr_node);
        $curr_node->unlink_node($curr_node);
        if ($a_update) {
            return $this->update();
        }
    }
    

    /**
     * Delete multiple content objects
     *
     * @param	string		$a_hids		array of hierarchical ids of content objects
     * @param	boolean		$a_update	update page in db (note: update deletes all
     *									hierarchical ids in DOM!)
     */
    public function deleteContents($a_hids, $a_update = true, $a_self_ass = false)
    {
        if (!is_array($a_hids)) {
            return;
        }
        foreach ($a_hids as $a_hid) {
            $a_hid = explode(":", $a_hid);
            //echo "-".$a_hid[0]."-".$a_hid[1]."-";
            
            // @todo 1: hook
            // do not delete question nodes in assessment pages
            if (!$this->checkForTag("Question", $a_hid[0], $a_hid[1]) || $a_self_ass) {
                $curr_node = $this->getContentNode($a_hid[0], $a_hid[1]);
                if (is_object($curr_node)) {
                    $parent_node = $curr_node->parent_node();
                    if ($parent_node->node_name() != "TableRow") {
                        $this->handleDeleteContent($curr_node);
                        $curr_node->unlink_node($curr_node);
                    }
                }
            }
        }
        if ($a_update) {
            return $this->update();
        }
    }
    
    /**
     * Copy contents to clipboard and cut them from the page
     *
     * @param	string		$a_hids		array of hierarchical ids of content objects
     */
    public function cutContents($a_hids)
    {
        $this->copyContents($a_hids);
        return $this->deleteContents($a_hids, true, $this->getPageConfig()->getEnableSelfAssessment());
    }
    
    /**
     * Copy contents to clipboard
     *
     * @param	string		$a_hids		array of hierarchical ids of content objects
     */
    public function copyContents($a_hids)
    {
        $user = $this->user;

        if (!is_array($a_hids)) {
            return;
        }

        $time = date("Y-m-d H:i:s", time());
        
        $hier_ids = array();
        $skip = array();
        foreach ($a_hids as $a_hid) {
            if ($a_hid == "") {
                continue;
            }
            $a_hid = explode(":", $a_hid);
            
            // check, whether new hid is child of existing one or vice versa
            reset($hier_ids);
            foreach ($hier_ids as $h) {
                if ($h . "_" == substr($a_hid[0], 0, strlen($h) + 1)) {
                    $skip[] = $a_hid[0];
                }
                if ($a_hid[0] . "_" == substr($h, 0, strlen($a_hid[0]) + 1)) {
                    $skip[] = $h;
                }
            }
            $pc_id[$a_hid[0]] = $a_hid[1];
            if ($a_hid[0] != "") {
                $hier_ids[$a_hid[0]] = $a_hid[0];
            }
        }
        foreach ($skip as $s) {
            unset($hier_ids[$s]);
        }
        include_once("./Services/COPage/classes/class.ilPageContent.php");
        $hier_ids = ilPageContent::sortHierIds($hier_ids);
        $nr = 1;
        foreach ($hier_ids as $hid) {
            $curr_node = $this->getContentNode($hid, $pc_id[$hid]);
            if (is_object($curr_node)) {
                if ($curr_node->node_name() == "PageContent") {
                    $content = $this->dom->dump_node($curr_node);
                    // remove pc and hier ids
                    $content = preg_replace('/PCID=\"[a-z0-9]*\"/i', "", $content);
                    $content = preg_replace('/HierId=\"[a-z0-9_]*\"/i', "", $content);
                    
                    $user->addToPCClipboard($content, $time, $nr);
                    $nr++;
                }
            }
        }
        include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
        ilEditClipboard::setAction("copy");
    }

    /**
     * Paste contents from pc clipboard
     */
    public function pasteContents($a_hier_id, $a_self_ass = false)
    {
        $user = $this->user;
        
        $a_hid = explode(":", $a_hier_id);
        $content = $user->getPCClipboardContent();
        
        // we insert from last to first, because we insert all at the
        // same hier_id
        for ($i = count($content) - 1; $i >= 0; $i--) {
            $c = $content[$i];
            $temp_dom = domxml_open_mem(
                '<?xml version="1.0" encoding="UTF-8"?>' . $c,
                DOMXML_LOAD_PARSING,
                $error
            );
            if (empty($error)) {
                $this->handleCopiedContent($temp_dom, $a_self_ass);
                $xpc = xpath_new_context($temp_dom);
                $path = "//PageContent";
                $res = xpath_eval($xpc, $path);
                if (count($res->nodeset) > 0) {
                    $new_pc_node = $res->nodeset[0];
                    $cloned_pc_node = $new_pc_node->clone_node(true);
                    $cloned_pc_node->unlink_node($cloned_pc_node);
                    $this->insertContentNode(
                        $cloned_pc_node,
                        $a_hid[0],
                        IL_INSERT_AFTER,
                        $a_hid[1]
                    );
                }
            } else {
                //var_dump($error);
            }
        }
        $e = $this->update();
        //var_dump($e);
    }
    
    /**
     * (De-)activate elements
     */
    public function switchEnableMultiple($a_hids, $a_update = true, $a_self_ass = false)
    {
        if (!is_array($a_hids)) {
            return;
        }
        $obj = &$this->content_obj;
        
        foreach ($a_hids as $a_hid) {
            $a_hid = explode(":", $a_hid);
            $curr_node = $this->getContentNode($a_hid[0], $a_hid[1]);
            if (is_object($curr_node)) {
                if ($curr_node->node_name() == "PageContent") {
                    $cont_obj = $this->getContentObject($a_hid[0], $a_hid[1]);
                    if ($cont_obj->isEnabled()) {
                        // do not deactivate question nodes in assessment pages
                        if (!$this->checkForTag("Question", $a_hid[0], $a_hid[1]) || $a_self_ass) {
                            $cont_obj->disable();
                        }
                    } else {
                        $cont_obj->enable();
                    }
                }
            }
        }
        
        if ($a_update) {
            return $this->update();
        }
    }


    /**
    * delete content object with hierarchical id >= $a_hid
    *
    * @param	string		$a_hid		hierarchical id of content object
    * @param	boolean		$a_update	update page in db (note: update deletes all
    *									hierarchical ids in DOM!)
    */
    public function deleteContentFromHierId($a_hid, $a_update = true)
    {
        $hier_ids = $this->getHierIds();
        
        // iterate all hierarchical ids
        foreach ($hier_ids as $hier_id) {
            // delete top level nodes only
            if (!is_int(strpos($hier_id, "_"))) {
                if ($hier_id != "pg" && $hier_id >= $a_hid) {
                    $curr_node = $this->getContentNode($hier_id);
                    $this->handleDeleteContent($curr_node);
                    $curr_node->unlink_node($curr_node);
                }
            }
        }
        if ($a_update) {
            return $this->update();
        }
    }

    /**
    * delete content object with hierarchical id < $a_hid
    *
    * @param	string		$a_hid		hierarchical id of content object
    * @param	boolean		$a_update	update page in db (note: update deletes all
    *									hierarchical ids in DOM!)
    */
    public function deleteContentBeforeHierId($a_hid, $a_update = true)
    {
        $hier_ids = $this->getHierIds();
        
        // iterate all hierarchical ids
        foreach ($hier_ids as $hier_id) {
            // delete top level nodes only
            if (!is_int(strpos($hier_id, "_"))) {
                if ($hier_id != "pg" && $hier_id < $a_hid) {
                    $curr_node = $this->getContentNode($hier_id);
                    $this->handleDeleteContent($curr_node);
                    $curr_node->unlink_node($curr_node);
                }
            }
        }
        if ($a_update) {
            return $this->update();
        }
    }
    
    
    /**
    * move content of hierarchical id >= $a_hid to other page
    *
    * @param	string		$a_hid		hierarchical id of content object
    * @param	boolean		$a_update	update page in db (note: update deletes all
    *									hierarchical ids in DOM!)
    */
    public static function _moveContentAfterHierId(&$a_source_page, &$a_target_page, $a_hid)
    {
        $hier_ids = $a_source_page->getHierIds();

        $copy_ids = array();

        // iterate all hierarchical ids
        foreach ($hier_ids as $hier_id) {
            // move top level nodes only
            if (!is_int(strpos($hier_id, "_"))) {
                if ($hier_id != "pg" && $hier_id >= $a_hid) {
                    $copy_ids[] = $hier_id;
                }
            }
        }
        asort($copy_ids);

        $parent_node = $a_target_page->getContentNode("pg");
        $target_dom = $a_target_page->getDom();
        $parent_childs = $parent_node->child_nodes();
        $cnt_parent_childs = count($parent_childs);
        //echo "-$cnt_parent_childs-";
        $first_child = $parent_childs[0];
        foreach ($copy_ids as $copy_id) {
            $source_node = $a_source_page->getContentNode($copy_id);

            $new_node = $source_node->clone_node(true);
            $new_node->unlink_node($new_node);

            $source_node->unlink_node($source_node);

            if ($cnt_parent_childs == 0) {
                $new_node = $parent_node->append_child($new_node);
            } else {
                //$target_dom->import_node($new_node);
                $new_node = $first_child->insert_before($new_node, $first_child);
            }
            $parent_childs = $parent_node->child_nodes();

            //$cnt_parent_childs++;
        }

        $a_target_page->update();
        $a_source_page->update();
    }

    /**
    * insert a content node before/after a sibling or as first child of a parent
    */
    public function insertContent(&$a_cont_obj, $a_pos, $a_mode = IL_INSERT_AFTER, $a_pcid = "")
    {
        //echo "-".$a_pos."-".$a_pcid."-";
        // move mode into container elements is always INSERT_CHILD
        $curr_node = $this->getContentNode($a_pos, $a_pcid);
        $curr_name = $curr_node->node_name();
        
        // @todo: try to generalize this
        if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
            ($curr_name == "ListItem") || ($curr_name == "Section")
            || ($curr_name == "Tab") || ($curr_name == "ContentPopup")
            || ($curr_name == "GridCell")) {
            $a_mode = IL_INSERT_CHILD;
        }

        $hid = $curr_node->get_attribute("HierId");
        if ($hid != "") {
            //echo "-".$a_pos."-".$hid."-";
            $a_pos = $hid;
        }
        
        if ($a_mode != IL_INSERT_CHILD) {			// determine parent hierarchical id
            // of sibling at $a_pos
            $pos = explode("_", $a_pos);
            $target_pos = array_pop($pos);
            $parent_pos = implode($pos, "_");
        } else {		// if we should insert a child, $a_pos is alreade the hierarchical id
            // of the parent node
            $parent_pos = $a_pos;
        }

        // get the parent node
        if ($parent_pos != "") {
            $parent_node = $this->getContentNode($parent_pos);
        } else {
            $parent_node = $this->getNode();
        }

        // count the parent children
        $parent_childs = $parent_node->child_nodes();
        $cnt_parent_childs = count($parent_childs);
        //echo "ZZ$a_mode";
        switch ($a_mode) {
            // insert new node after sibling at $a_pos
            case IL_INSERT_AFTER:
                $new_node = $a_cont_obj->getNode();
                //$a_pos = ilPageContent::incEdId($a_pos);
                //$curr_node = $this->getContentNode($a_pos);
//echo "behind $a_pos:";
                if ($succ_node = $curr_node->next_sibling()) {
                    $new_node = $succ_node->insert_before($new_node, $succ_node);
                } else {
                    //echo "movin doin append_child";
                    $new_node = $parent_node->append_child($new_node);
                }
                $a_cont_obj->setNode($new_node);
                break;

            case IL_INSERT_BEFORE:
//echo "INSERT_BEF";
                $new_node = $a_cont_obj->getNode();
                $succ_node = $this->getContentNode($a_pos);
                $new_node = $succ_node->insert_before($new_node, $succ_node);
                $a_cont_obj->setNode($new_node);
                break;

            // insert new node as first child of parent $a_pos (= $a_parent)
            case IL_INSERT_CHILD:
//echo "insert as child:parent_childs:$cnt_parent_childs:<br>";
                $new_node = $a_cont_obj->getNode();
                if ($cnt_parent_childs == 0) {
                    $new_node = $parent_node->append_child($new_node);
                } else {
                    $new_node = $parent_childs[0]->insert_before($new_node, $parent_childs[0]);
                }
                $a_cont_obj->setNode($new_node);
//echo "PP";
                break;
        }
                
        //check for PlaceHolder to remove in EditMode-keep in Layout Mode
        if (!$this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $sub_nodes = $curr_node->child_nodes() ;
            foreach ($sub_nodes as $sub_node) {
                if ($sub_node->node_name() == "PlaceHolder") {
                    $curr_node->unlink_node();
                }
            }
        }
    }

    /**
    * insert a content node before/after a sibling or as first child of a parent
    */
    public function insertContentNode(&$a_cont_node, $a_pos, $a_mode = IL_INSERT_AFTER, $a_pcid = "")
    {
        // move mode into container elements is always INSERT_CHILD
        $curr_node = $this->getContentNode($a_pos, $a_pcid);
        $curr_name = $curr_node->node_name();
        
        // @todo: try to generalize
        if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
            ($curr_name == "ListItem") || ($curr_name == "Section")
            || ($curr_name == "Tab") || ($curr_name == "ContentPopup")
            || ($curr_name == "GridCell")) {
            $a_mode = IL_INSERT_CHILD;
        }

        $hid = $curr_node->get_attribute("HierId");
        if ($hid != "") {
            $a_pos = $hid;
        }
        
        if ($a_mode != IL_INSERT_CHILD) {			// determine parent hierarchical id
            // of sibling at $a_pos
            $pos = explode("_", $a_pos);
            $target_pos = array_pop($pos);
            $parent_pos = implode($pos, "_");
        } else {		// if we should insert a child, $a_pos is alreade the hierarchical id
            // of the parent node
            $parent_pos = $a_pos;
        }

        // get the parent node
        if ($parent_pos != "") {
            $parent_node = $this->getContentNode($parent_pos);
        } else {
            $parent_node = $this->getNode();
        }

        // count the parent children
        $parent_childs = $parent_node->child_nodes();
        $cnt_parent_childs = count($parent_childs);

        switch ($a_mode) {
            // insert new node after sibling at $a_pos
            case IL_INSERT_AFTER:
                //$new_node = $a_cont_obj->getNode();
                if ($succ_node = $curr_node->next_sibling()) {
                    $a_cont_node = $succ_node->insert_before($a_cont_node, $succ_node);
                } else {
                    $a_cont_node = $parent_node->append_child($a_cont_node);
                }
                //$a_cont_obj->setNode($new_node);
                break;

            case IL_INSERT_BEFORE:
                //$new_node = $a_cont_obj->getNode();
                $succ_node = $this->getContentNode($a_pos);
                $a_cont_node = $succ_node->insert_before($a_cont_node, $succ_node);
                //$a_cont_obj->setNode($new_node);
                break;

            // insert new node as first child of parent $a_pos (= $a_parent)
            case IL_INSERT_CHILD:
                //$new_node = $a_cont_obj->getNode();
                if ($cnt_parent_childs == 0) {
                    $a_cont_node = $parent_node->append_child($a_cont_node);
                } else {
                    $a_cont_node = $parent_childs[0]->insert_before($a_cont_node, $parent_childs[0]);
                }
                //$a_cont_obj->setNode($new_node);
                break;
        }
    }

    /**
    * move content object from position $a_source before position $a_target
    * (both hierarchical content ids)
    */
    public function moveContentBefore($a_source, $a_target, $a_spcid = "", $a_tpcid = "")
    {
        if ($a_source == $a_target) {
            return;
        }

        // clone the node
        $content = $this->getContentObject($a_source, $a_spcid);
        $source_node = $content->getNode();
        $clone_node = $source_node->clone_node(true);

        // delete source node
        $this->deleteContent($a_source, false, $a_spcid);

        // insert cloned node at target
        $content->setNode($clone_node);
        $this->insertContent($content, $a_target, IL_INSERT_BEFORE, $a_tpcid);
        return $this->update();
    }

    /**
    * move content object from position $a_source before position $a_target
    * (both hierarchical content ids)
    */
    public function moveContentAfter($a_source, $a_target, $a_spcid = "", $a_tpcid = "")
    {
        if ($a_source == $a_target) {
            return;
        }

        // clone the node
        $content = $this->getContentObject($a_source, $a_spcid);
        $source_node = $content->getNode();
        $clone_node = $source_node->clone_node(true);

        // delete source node
        $this->deleteContent($a_source, false, $a_spcid);

        // insert cloned node at target
        $content->setNode($clone_node);
        $this->insertContent($content, $a_target, IL_INSERT_AFTER, $a_tpcid);
        return $this->update();
    }

    /**
    * transforms bbCode to corresponding xml
    */
    // @todo: move to paragraph
    public function bbCode2XML(&$a_content)
    {
        $a_content = preg_replace('/\[com\]/i', "<Comment>", $a_content);
        $a_content = preg_replace('/\[\/com\]/i', "</Comment>", $a_content);
        $a_content = preg_replace('/\[emp]/i', "<Emph>", $a_content);
        $a_content = preg_replace('/\[\/emp\]/i', "</Emph>", $a_content);
        $a_content = preg_replace('/\[str]/i', "<Strong>", $a_content);
        $a_content = preg_replace('/\[\/str\]/i', "</Strong>", $a_content);
    }

    /**
    * inserts installation id into ids (e.g. il__pg_4 -> il_23_pg_4)
    * this is needed for xml export of page
    */
    public function insertInstIntoIDs($a_inst, $a_res_ref_to_obj_id = true)
    {
        // insert inst id into internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");

            if (substr($target, 0, 4) == "il__") {
                $id = substr($target, 4, strlen($target) - 4);
                
                // convert repository links obj_<ref_id> to <type>_<obj_id>
                // this leads to bug 6685.
                if ($a_res_ref_to_obj_id && $type == "RepositoryItem") {
                    $id_arr = explode("_", $id);
                    
                    // changed due to bug 6685
                    $ref_id = $id_arr[1];
                    $obj_id = ilObject::_lookupObjId($id_arr[1]);
                    
                    $otype = ilObject::_lookupType($obj_id);
                    if ($obj_id > 0) {
                        // changed due to bug 6685
                        // the ref_id should be used, if the content is
                        // imported on the same installation
                        // the obj_id should be used, if a different
                        // installation imports, but has an import_id for
                        // the object id.
                        $id = $otype . "_" . $obj_id . "_" . $ref_id;
                        //$id = $otype."_".$ref_id;
                    }
                }
                $new_target = "il_" . $a_inst . "_" . $id;
                $res->nodeset[$i]->set_attribute("Target", $new_target);
            }
        }
        unset($xpc);

        // @todo: move to media/fileitems/questions, ...
        
        // insert inst id into media aliases
        $xpc = xpath_new_context($this->dom);
        $path = "//MediaAlias";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $origin_id = $res->nodeset[$i]->get_attribute("OriginId");
            if (substr($origin_id, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($origin_id, 4, strlen($origin_id) - 4);
                $res->nodeset[$i]->set_attribute("OriginId", $new_id);
            }
        }
        unset($xpc);

        // insert inst id file item identifier entries
        $xpc = xpath_new_context($this->dom);
        $path = "//FileItem/Identifier";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $origin_id = $res->nodeset[$i]->get_attribute("Entry");
            if (substr($origin_id, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($origin_id, 4, strlen($origin_id) - 4);
                $res->nodeset[$i]->set_attribute("Entry", $new_id);
            }
        }
        unset($xpc);
        
        // insert inst id into question references
        $xpc = xpath_new_context($this->dom);
        $path = "//Question";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $qref = $res->nodeset[$i]->get_attribute("QRef");
            //echo "<br>setted:".$qref;
            if (substr($qref, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($qref, 4, strlen($qref) - 4);
                //echo "<br>setting:".$new_id;
                $res->nodeset[$i]->set_attribute("QRef", $new_id);
            }
        }
        unset($xpc);

        // insert inst id into content snippets
        $xpc = xpath_new_context($this->dom);
        $path = "//ContentInclude";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $ci = $res->nodeset[$i]->get_attribute("InstId");
            if ($ci == "") {
                $res->nodeset[$i]->set_attribute("InstId", $a_inst);
            }
        }
        unset($xpc);
    }

    /**
    * Check, whether (all) page content hashes are set
    */
    public function checkPCIds()
    {
        $this->builddom();
        $mydom = $this->dom;
        
        $sep = $path = "";
        foreach ($this->id_elements as $el) {
            $path .= $sep . "//" . $el . "[not(@PCID)]";
            $sep = " | ";
            $path .= $sep . "//" . $el . "[@PCID='']";
        }
        
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);

        if (count($res->nodeset) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Get all pc ids
     *
     * @param
     * @return
     */
    public function getAllPCIds()
    {
        $this->builddom();
        $mydom = $this->dom;
        
        $pcids = array();

        $sep = $path = "";
        foreach ($this->id_elements as $el) {
            $path .= $sep . "//" . $el . "[@PCID]";
            $sep = " | ";
        }
        
        // get existing ids
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);

        for ($i = 0; $i < count($res->nodeset); $i++) {
            $node = $res->nodeset[$i];
            $pcids[] = $node->get_attribute("PCID");
        }
        return $pcids;
    }
    
    /**
     * existsPCId
     *
     * @param
     * @return
     */
    public function existsPCId($a_pc_id)
    {
        $this->builddom();
        $mydom = $this->dom;
        
        $pcids = array();

        $sep = $path = "";
        foreach ($this->id_elements as $el) {
            $path .= $sep . "//" . $el . "[@PCID='" . $a_pc_id . "']";
            $sep = " | ";
        }
        
        // get existing ids
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);
        return (count($res->nodeset) > 0);
    }

    /**
     * Generate new pc id
     *
     * @param array $a_pc_ids existing pc ids
     * @return string new pc id
     */
    public function generatePcId($a_pc_ids = false)
    {
        if ($a_pc_ids === false) {
            $a_pc_ids = $this->getAllPCIds();
        }
        $id = ilUtil::randomHash(10, $a_pc_ids);
        return $id;
    }
    
    
    /**
     * Insert Page Content IDs
     */
    public function insertPCIds()
    {
        $this->builddom();
        $mydom = $this->dom;
        
        $pcids = $this->getAllPCIds();

        // add missing ones
        $sep = $path = "";
        foreach ($this->id_elements as $el) {
            $path .= $sep . "//" . $el . "[not(@PCID)]";
            $sep = " | ";
            $path .= $sep . "//" . $el . "[@PCID='']";
            $sep = " | ";
        }
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);

        for ($i = 0; $i < count($res->nodeset); $i++) {
            $node = $res->nodeset[$i];
            $id = ilUtil::randomHash(10, $pcids);
            $pcids[] = $id;
            //echo "setting-".$id."-";
            $res->nodeset[$i]->set_attribute("PCID", $id);
        }
    }
    
    /**
    * Get page contents hashes
    */
    public function getPageContentsHashes()
    {
        $this->builddom();
        $this->addHierIds();
        $mydom = $this->dom;
        
        // get existing ids
        $path = "//PageContent";
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);

        $hashes = array();
        require_once("./Services/COPage/classes/class.ilPCParagraph.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $hier_id = $res->nodeset[$i]->get_attribute("HierId");
            $pc_id = $res->nodeset[$i]->get_attribute("PCID");
            $dump = $mydom->dump_node($res->nodeset[$i]);
            if (($hpos = strpos($dump, ' HierId="' . $hier_id . '"')) > 0) {
                $dump = substr($dump, 0, $hpos) .
                    substr($dump, $hpos + strlen(' HierId="' . $hier_id . '"'));
            }
            
            $childs = $res->nodeset[$i]->child_nodes();
            $content = "";
            if ($childs[0] && $childs[0]->node_name() == "Paragraph") {
                $content = $mydom->dump_node($childs[0]);
                $content = substr(
                    $content,
                    strpos($content, ">") + 1,
                    strrpos($content, "<") - (strpos($content, ">") + 1)
                );
                //var_dump($content);
                $content = ilPCParagraph::xml2output($content);
                //var_dump($content);
            }
            //$hashes[$hier_id] =
            //	array("PCID" => $pc_id, "hash" => md5($dump));
            $hashes[$pc_id] =
                array("hier_id" => $hier_id, "hash" => md5($dump), "content" => $content);
        }
        
        return $hashes;
    }
    
    /**
    * Get question ids
    */
    // @todo: move to questions
    public function getQuestionIds()
    {
        $this->builddom();
        $mydom = $this->dom;

        // Get question IDs
        $path = "//Question";
        $xpc = xpath_new_context($mydom);
        $res = &xpath_eval($xpc, $path);

        $q_ids = array();
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $qref = $res->nodeset[$i]->get_attribute("QRef");
            
            $inst_id = ilInternalLink::_extractInstOfTarget($qref);
            $obj_id = ilInternalLink::_extractObjIdOfTarget($qref);

            if (!($inst_id > 0)) {
                if ($obj_id > 0) {
                    $q_ids[] = $obj_id;
                }
            }
        }
        return $q_ids;
    }

    // @todo: move to paragraph
    public function send_paragraph($par_id, $filename)
    {
        $this->builddom();

        $mydom = $this->dom;

        $xpc = xpath_new_context($mydom);

        //$path = "//PageContent[position () = $par_id]/Paragraph";
        //$path = "//Paragraph[$par_id]";
        $path = "/descendant::Paragraph[position() = $par_id]";

        $res = &xpath_eval($xpc, $path);
        
        if (count($res->nodeset) != 1) {
            die("Should not happen");
        }

        $context_node = $res->nodeset[0];

        // get plain text

        $childs = $context_node->child_nodes();

        for ($j = 0; $j < count($childs); $j++) {
            $content .= $mydom->dump_node($childs[$j]);
        }

        $content = str_replace("<br />", "\n", $content);
        $content = str_replace("<br/>", "\n", $content);

        $plain_content = html_entity_decode($content);

        ilUtil::deliverData($plain_content, $filename);
        /*
        $file_type = "application/octet-stream";
        header("Content-type: ".$file_type);
        header("Content-disposition: attachment; filename=\"$filename\"");
        echo $plain_content;*/
        exit();
    }

    /**
    * get fo page content
    */
    // @todo: deprecated?
    public function getFO()
    {
        $xml = $this->getXMLFromDom(false, true, true);
        $xsl = file_get_contents("./Services/COPage/xsl/page_fo.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        $params = array();


        $fo = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        var_dump($fo);
        // do some replacements
        $fo = str_replace("\n", "", $fo);
        $fo = str_replace("<br/>", "<br>", $fo);
        $fo = str_replace("<br>", "\n", $fo);

        xslt_free($xh);

        //
        $fo = substr($fo, strpos($fo, ">") + 1);
        //echo "<br><b>fo:</b><br>".htmlentities($fo); flush();
        return $fo;
    }
    
    public function registerOfflineHandler($handler)
    {
        $this->offline_handler = $handler;
    }

    /**
     * Get offline handler
     *
     * @param
     * @return
     */
    public function getOfflineHandler()
    {
        return $this->offline_handler;
    }

    
    /**
    * lookup whether page contains deactivated elements
    */
    public static function _lookupContainsDeactivatedElements($a_id, $a_parent_type, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        if ($a_lang == "") {
            $a_lang = "-";
        }

        $query = "SELECT * FROM page_object WHERE page_id = " .
            $db->quote($a_id, "integer") . " AND " .
            " parent_type = " . $db->quote($a_parent_type, "text") . " AND " .
            " lang = " . $db->quote($a_lang, "text") . " AND " .
            " inactive_elements = " . $db->quote(1, "integer");
        $obj_set = $db->query($query);
        
        if ($obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return true;
        }

        return false;
    }

    /**
     * Check whether content contains deactivated elements
     *
     * @param
     * @return
     */
    public function containsDeactivatedElements($a_content)
    {
        if (strpos($a_content, " Enabled=\"False\"")) {
            return true;
        }
        return false;
    }

    /**
    * Get History Entries
    */
    public function getHistoryEntries()
    {
        $db = $this->db;
        
        $h_query = "SELECT * FROM page_history " .
            " WHERE page_id = " . $db->quote($this->getId(), "integer") .
            " AND parent_type = " . $db->quote($this->getParentType(), "text") .
            " AND lang = " . $db->quote($this->getLanguage(), "text") .
            " ORDER BY hdate DESC";
        
        $hset = $db->query($h_query);
        $hentries = array();

        while ($hrec = $db->fetchAssoc($hset)) {
            $hrec["sortkey"] = (int) $hrec["nr"];
            $hrec["user"] = (int) $hrec["user_id"];
            $hentries[] = $hrec;
        }
        //var_dump($hentries);
        return $hentries;
    }
    
    /**
    * Get History Entry
    */
    public function getHistoryEntry($a_old_nr)
    {
        $db = $this->db;
        
        $res = $db->queryF(
            "SELECT * FROM page_history " .
            " WHERE page_id = %s " .
            " AND parent_type = %s " .
            " AND nr = %s" .
            " AND lang = %s",
            array("integer", "text", "integer", "text"),
            array($this->getId(), $this->getParentType(), $a_old_nr, $this->getLanguage())
        );
        if ($hrec = $db->fetchAssoc($res)) {
            return $hrec;
        }
        
        return false;
    }

    
    /**
    * Get information about a history entry, its predecessor and
    * its successor.
    *
    * @param	int		$a_nr		Nr of history entry
    */
    public function getHistoryInfo($a_nr)
    {
        $db = $this->db;
        
        // determine previous entry
        $and_nr = ($a_nr > 0)
            ? " AND nr < " . $db->quote((int) $a_nr, "integer")
            : "";
        $res = $db->query("SELECT MAX(nr) mnr FROM page_history " .
            " WHERE page_id = " . $db->quote($this->getId(), "integer") .
            " AND parent_type = " . $db->quote($this->getParentType(), "text") .
            " AND lang = " . $db->quote($this->getLanguage(), "text") .
            $and_nr);
        $row = $db->fetchAssoc($res);
        if ($row["mnr"] > 0) {
            $res = $db->query("SELECT * FROM page_history " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text") .
                " AND lang = " . $db->quote($this->getLanguage(), "text") .
                " AND nr = " . $db->quote((int) $row["mnr"], "integer"));
            $row = $db->fetchAssoc($res);
            $ret["previous"] = $row;
        }
        
        // determine next entry
        $res = $db->query("SELECT MIN(nr) mnr FROM page_history " .
            " WHERE page_id = " . $db->quote($this->getId(), "integer") .
            " AND parent_type = " . $db->quote($this->getParentType(), "text") .
            " AND lang = " . $db->quote($this->getLanguage(), "text") .
            " AND nr > " . $db->quote((int) $a_nr, "integer"));
        $row = $db->fetchAssoc($res);
        if ($row["mnr"] > 0) {
            $res = $db->query("SELECT * FROM page_history " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text") .
                " AND lang = " . $db->quote($this->getLanguage(), "text") .
                " AND nr = " . $db->quote((int) $row["mnr"], "integer"));
            $row = $db->fetchAssoc($res);
            $ret["next"] = $row;
        }

        // current
        if ($a_nr > 0) {
            $res = $db->query("SELECT * FROM page_history " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text") .
                " AND lang = " . $db->quote($this->getLanguage(), "text") .
                " AND nr = " . $db->quote((int) $a_nr, "integer"));
            $row = $db->fetchAssoc($res);
        } else {
            $res = $db->query("SELECT page_id, last_change hdate, parent_type, parent_id, last_change_user user_id, content, lang FROM page_object " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text") .
                " AND lang = " . $db->quote($this->getLanguage(), "text"));
            $row = $db->fetchAssoc($res);
        }
        $ret["current"] = $row;

        return $ret;
    }
    
    public function addChangeDivClasses($a_hashes)
    {
        $xpc = xpath_new_context($this->dom);
        $path = "/*[1]";
        $res = xpath_eval($xpc, $path);
        $rnode = $res->nodeset[0];

        //echo "A";
        foreach ($a_hashes as $pc_id => $h) {
            //echo "B";
            if ($h["change"] != "") {
                $dc_node = $this->dom->create_element("DivClass");
                $dc_node->set_attribute("HierId", $h["hier_id"]);
                $dc_node->set_attribute("Class", "ilEdit" . $h["change"]);
                $dc_node = $rnode->append_child($dc_node);
            }
        }
        //echo "<br><br><br><br><br><br>".htmlentities($this->getXMLFromDom());
    }
    
    /**
     * Compares to revisions of the page
     *
     * @param	int		$a_left		Nr of first revision
     * @param	int		$a_right	Nr of second revision
     */
    public function compareVersion($a_left, $a_right)
    {
        // get page objects
        include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
        $l_page = ilPageObjectFactory::getInstance($this->getParentType(), $this->getId(), $a_left);
        $r_page = ilPageObjectFactory::getInstance($this->getParentType(), $this->getId(), $a_right);

        $l_hashes = $l_page->getPageContentsHashes();
        $r_hashes = $r_page->getPageContentsHashes();
        // determine all deleted and changed page elements
        foreach ($l_hashes as $pc_id => $h) {
            if (!isset($r_hashes[$pc_id])) {
                $l_hashes[$pc_id]["change"] = "Deleted";
            } else {
                if ($l_hashes[$pc_id]["hash"] != $r_hashes[$pc_id]["hash"]) {
                    $l_hashes[$pc_id]["change"] = "Modified";
                    $r_hashes[$pc_id]["change"] = "Modified";
                    
                    include_once("./Services/COPage/mediawikidiff/class.WordLevelDiff.php");
                    // if modified element is a paragraph, highlight changes
                    if ($l_hashes[$pc_id]["content"] != "" &&
                        $r_hashes[$pc_id]["content"] != "") {
                        $new_left = str_replace("\n", "<br />", $l_hashes[$pc_id]["content"]);
                        $new_right = str_replace("\n", "<br />", $r_hashes[$pc_id]["content"]);
                        $wldiff = new WordLevelDiff(
                            array($new_left),
                            array($new_right)
                        );
                        $new_left = $wldiff->orig();
                        $new_right = $wldiff->closing();
                        $l_page->setParagraphContent($l_hashes[$pc_id]["hier_id"], $new_left[0]);
                        $r_page->setParagraphContent($l_hashes[$pc_id]["hier_id"], $new_right[0]);
                    }
                }
            }
        }
        
        // determine all new paragraphs
        foreach ($r_hashes as $pc_id => $h) {
            if (!isset($l_hashes[$pc_id])) {
                $r_hashes[$pc_id]["change"] = "New";
            }
        }
        $l_page->addChangeDivClasses($l_hashes);
        $r_page->addChangeDivClasses($r_hashes);

        return array("l_page" => $l_page, "r_page" => $r_page,
            "l_changes" => $l_hashes, "r_changes" => $r_hashes);
    }
    
    /**
     * Increase view cnt
     */
    public function increaseViewCnt()
    {
        $db = $this->db;

        $db->manipulate("UPDATE page_object " .
            " SET view_cnt = view_cnt + 1 " .
            " WHERE page_id = " . $db->quote($this->getId(), "integer") .
            " AND parent_type = " . $db->quote($this->getParentType(), "text") .
            " AND lang = " . $db->quote($this->getLanguage(), "text"));
    }
    
    /**
    * Get recent pages changes for parent object.
    *
    * @param	string	$a_parent_type	Parent Type
    * @param	int		$a_parent_id	Parent ID
    * @param	int		$a_period		Time Period
    */
    public static function getRecentChanges($a_parent_type, $a_parent_id, $a_period = 30, $a_lang = "")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }
        
        $page_changes = array();
        $limit_ts = date('Y-m-d H:i:s', time() - ($a_period * 24 * 60 * 60));
        $q = "SELECT * FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") .
            " AND last_change >= " . $db->quote($limit_ts, "timestamp") . $and_lang;
        //	" AND (TO_DAYS(now()) - TO_DAYS(last_change)) <= ".((int)$a_period);
        $set = $db->query($q);
        while ($page = $db->fetchAssoc($set)) {
            $page_changes[] = array(
                "date" => $page["last_change"],
                "id" => $page["page_id"],
                "lang" => $page["lang"],
                "type" => "page",
                "user" => $page["last_change_user"]);
        }

        $and_str = "";
        if ($a_period > 0) {
            $limit_ts = date('Y-m-d H:i:s', time() - ($a_period * 24 * 60 * 60));
            $and_str = " AND hdate >= " . $db->quote($limit_ts, "timestamp") . " ";
        }

        $q = "SELECT * FROM page_history " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") .
            $and_str . $and_lang;
        $set = $db->query($q);
        while ($page = $db->fetchAssoc($set)) {
            $page_changes[] = array(
                "date" => $page["hdate"],
                "id" => $page["page_id"],
                "lang" => $page["lang"],
                "type" => "hist",
                "nr" => $page["nr"],
                "user" => $page["user_id"]);
        }
        
        $page_changes = ilUtil::sortArray($page_changes, "date", "desc");
        
        return $page_changes;
    }
    
    /**
     * Get all pages for parent object
     *
     * @param string $a_parent_type
     * @param int $a_parent_id
     * @param string $a_lang
     * @return array
     */
    public static function getAllPages($a_parent_type, $a_parent_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }

        $q = "SELECT * FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") . $and_lang;
        $set = $db->query($q);
        $pages = array();
        while ($page = $db->fetchAssoc($set)) {
            $key_add = ($a_lang == "")
                ? ":" . $page["lang"]
                : "";
            $pages[$page["page_id"] . $key_add] = array(
                "date" => $page["last_change"],
                "id" => $page["page_id"],
                "lang" => $page["lang"],
                "user" => $page["last_change_user"]);
        }

        return $pages;
    }

    /**
    * Get new pages.
    *
    * @param	string	$a_parent_type	Parent Type
    * @param	int		$a_parent_id	Parent ID
    */
    public static function getNewPages($a_parent_type, $a_parent_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }

        $pages = array();
        
        $q = "SELECT * FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") . $and_lang .
            " ORDER BY created DESC";
        $set = $db->query($q);
        while ($page = $db->fetchAssoc($set)) {
            if ($page["created"] != "") {
                $pages[] = array(
                    "created" => $page["created"],
                    "id" => $page["page_id"],
                    "lang" => $page["lang"],
                    "user" => $page["create_user"],
                    );
            }
        }

        return $pages;
    }

    /**
    * Get all contributors for parent object
    *
    * @param	string	$a_parent_type	Parent Type
    * @param	int		$a_parent_id	Parent ID
    */
    public static function getParentObjectContributors($a_parent_type, $a_parent_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }
        
        $contributors = array();
        $set = $db->queryF(
            "SELECT last_change_user, lang, page_id FROM page_object " .
            " WHERE parent_id = %s AND parent_type = %s " .
            " AND last_change_user != %s" . $and_lang,
            array("integer", "text", "integer"),
            array($a_parent_id, $a_parent_type, 0)
        );

        while ($page = $db->fetchAssoc($set)) {
            if ($a_lang == "") {
                $contributors[$page["last_change_user"]][$page["page_id"]][$page["lang"]] = 1;
            } else {
                $contributors[$page["last_change_user"]][$page["page_id"]] = 1;
            }
        }

        $set = $db->queryF(
            "SELECT count(*) as cnt, lang, page_id, user_id FROM page_history " .
            " WHERE parent_id = %s AND parent_type = %s AND user_id != %s " . $and_lang .
            " GROUP BY page_id, user_id, lang ",
            array("integer", "text", "integer"),
            array($a_parent_id, $a_parent_type, 0)
        );
        while ($hpage = $db->fetchAssoc($set)) {
            if ($a_lang == "") {
                $contributors[$hpage["user_id"]][$hpage["page_id"]][$hpage["lang"]] =
                    $contributors[$hpage["user_id"]][$hpage["page_id"]][$hpage["lang"]] + $hpage["cnt"];
            } else {
                $contributors[$hpage["user_id"]][$hpage["page_id"]] =
                    $contributors[$hpage["user_id"]][$hpage["page_id"]] + $hpage["cnt"];
            }
        }
        
        $c = array();
        foreach ($contributors as $k => $co) {
            if (ilObject::_lookupType($k) == "usr") {
                $name = ilObjUser::_lookupName($k);
                $c[] = array("user_id" => $k, "pages" => $co,
                    "lastname" => $name["lastname"], "firstname" => $name["firstname"]);
            }
        }
        
        return $c;
    }

    /**
    * Get all contributors for parent object
    *
    * @param	string	$a_parent_type	Parent Type
    * @param	int		$a_parent_id	Parent ID
    */
    public static function getPageContributors($a_parent_type, $a_page_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }

        $contributors = array();
        $set = $db->queryF(
            "SELECT last_change_user, lang FROM page_object " .
            " WHERE page_id = %s AND parent_type = %s " .
            " AND last_change_user != %s" . $and_lang,
            array("integer", "text", "integer"),
            array($a_page_id, $a_parent_type, 0)
        );

        while ($page = $db->fetchAssoc($set)) {
            if ($a_lang == "") {
                $contributors[$page["last_change_user"]][$page["lang"]] = 1;
            } else {
                $contributors[$page["last_change_user"]] = 1;
            }
        }

        $set = $db->queryF(
            "SELECT count(*) as cnt, lang, page_id, user_id FROM page_history " .
            " WHERE page_id = %s AND parent_type = %s AND user_id != %s " . $and_lang .
            " GROUP BY user_id, page_id, lang ",
            array("integer", "text", "integer"),
            array($a_page_id, $a_parent_type, 0)
        );
        while ($hpage = $db->fetchAssoc($set)) {
            if ($a_lang == "") {
                $contributors[$hpage["user_id"]][$page["lang"]] =
                    $contributors[$hpage["user_id"]][$page["lang"]] + $hpage["cnt"];
            } else {
                $contributors[$hpage["user_id"]] =
                    $contributors[$hpage["user_id"]] + $hpage["cnt"];
            }
        }
        
        $c = array();
        foreach ($contributors as $k => $co) {
            include_once "Services/User/classes/class.ilObjUser.php";
            $name = ilObjUser::_lookupName($k);
            $c[] = array("user_id" => $k, "pages" => $co,
                "lastname" => $name["lastname"], "firstname" => $name["firstname"]);
        }
        
        return $c;
    }

    /**
     * Write rendered content
     */
    public function writeRenderedContent($a_content, $a_md5)
    {
        global $DIC;

        $db = $DIC->database();

        $db->update("page_object", array(
            "rendered_content" => array("clob", $a_content),
            "render_md5" => array("text", $a_md5),
            "rendered_time" => array("timestamp", ilUtil::now())
            ), array(
            "page_id" => array("integer", $this->getId()),
            "lang" => array("text", $this->getLanguage()),
            "parent_type" => array("text", $this->getParentType())
            ));
    }

    /**
    * Get all pages for parent object that contain internal links
    *
    * @param	string	$a_parent_type	Parent Type
    * @param	int		$a_parent_id	Parent ID
    * @param	int		$a_period		Time Period
    */
    public static function getPagesWithLinks($a_parent_type, $a_parent_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();
        
        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }

        $q = "SELECT * FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") .
            " AND int_links = " . $db->quote(1, "integer") . $and_lang;
        $set = $db->query($q);
        $pages = array();
        while ($page = $db->fetchAssoc($set)) {
            $key_add = ($a_lang == "")
                ? ":" . $page["lang"]
                : "";
            $pages[$page["page_id"] . $key_add] = array(
                "date" => $page["last_change"],
                "id" => $page["page_id"],
                "lang" => $page["lang"],
                "user" => $page["last_change_user"]);
        }

        return $pages;
    }

    /**
     * Check whether content contains internal links
     *
     * @param
     * @return
     */
    public function containsIntLinks($a_content)
    {
        if (strpos($a_content, "IntLink")) {
            return true;
        }
        return false;
    }

    /**
     * Perform automatic modifications (may be overwritten by sub classes)
     */
    public function performAutomaticModifications()
    {
    }
    
    /**
     * Save initial opened content
     *
     * @param
     */
    // @todo begin: generalize
    public function saveInitialOpenedContent($a_type, $a_id, $a_target)
    {
        $this->buildDom();

        $link_type = "";

        switch ($a_type) {
            case "media":
                $link_type = "MediaObject";
                $a_id = "il__mob_" . $a_id;
                break;
                
            case "page":
                $link_type = "PageObject";
                $a_id = "il__pg_" . $a_id;
                break;
                
            case "term":
                $link_type = "GlossaryItem";
                $a_id = "il__git_" . $a_id;
                $a_target = "Glossary";
                break;
        }

        // if type or id missing -> delete InitOpenedContent, if existing
        if ($link_type == "" || $a_id == "") {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageObject/InitOpenedContent";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                $res->nodeset[0]->unlink_node($res->nodeset[0]);
            }
        } else {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageObject/InitOpenedContent";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                $init_node = $res->nodeset[0];
                $childs = $init_node->child_nodes();
                for ($i = 0; $i < count($childs); $i++) {
                    if ($childs[$i]->node_name() == "IntLink") {
                        $il_node = $childs[$i];
                    }
                }
            } else {
                $path = "//PageObject";
                $res = xpath_eval($xpc, $path);
                $page_node = $res->nodeset[0];
                $init_node = $this->dom->create_element("InitOpenedContent");
                $init_node = $page_node->append_child($init_node);
                $il_node = $this->dom->create_element("IntLink");
                $il_node = $init_node->append_child($il_node);
            }
            $il_node->set_attribute("Target", $a_id);
            $il_node->set_attribute("Type", $link_type);
            $il_node->set_attribute("TargetFrame", $a_target);
        }

        $this->update();
    }
    
    
    /**
     * Get initial opened content
     *
     * @param
     */
    public function getInitialOpenedContent()
    {
        $this->buildDom();

        $xpc = xpath_new_context($this->dom);
        $path = "//PageObject/InitOpenedContent";
        $res = xpath_eval($xpc, $path);
        $il_node = null;
        if (count($res->nodeset) > 0) {
            $init_node = $res->nodeset[0];
            $childs = $init_node->child_nodes();
            for ($i = 0; $i < count($childs); $i++) {
                if ($childs[$i]->node_name() == "IntLink") {
                    $il_node = $childs[$i];
                }
            }
        }
        if (!is_null($il_node)) {
            $id = $il_node->get_attribute("Target");
            $link_type = $il_node->get_attribute("Type");
            $target = $il_node->get_attribute("TargetFrame");
            
            switch ($link_type) {
                case "MediaObject":
                    $type = "media";
                    break;
                    
                case "PageObject":
                    $type = "page";
                    break;
                    
                case "GlossaryItem":
                    $type = "term";
                    break;
            }
            include_once("./Services/Link/classes/class.ilInternalLink.php");
            $id = ilInternalLink::_extractObjIdOfTarget($id);
            return array("id" => $id, "type" => $type, "target" => $target);
        }
        
        return array();
    }
    // @todo end
    
    /**
     * Before page content update
     *
     * Note: This one is "work in progress", currently only text paragraphs call this hook
     * It is called before the page content object invokes the update procedure of
     * ilPageObject
     *
     * @param
     * @return
     */
    public function beforePageContentUpdate($a_page_content)
    {
    }

    /**
     * Copy page
     *
     * @param int $a_id target page id
     * @param string $a_parent_type target parent type
     * @param int $a_parent_id target parent id
     */
    public function copy($a_id, $a_parent_type = "", $a_parent_id = 0, $a_clone_mobs = false)
    {
        if ($a_parent_type == "") {
            $a_parent_type = $this->getParentType();
            if ($a_parent_id == 0) {
                $a_parent_id = $this->getParentId();
            }
        }

        include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
        foreach (self::lookupTranslations($this->getParentType(), $this->getId()) as $l) {
            $existed = false;
            $orig_page = ilPageObjectFactory::getInstance($this->getParentType(), $this->getId(), 0, $l);
            if (ilPageObject::_exists($a_parent_type, $a_id, $l)) {
                $new_page_object = ilPageObjectFactory::getInstance($a_parent_type, $a_id, 0, $l);
                $existed = true;
            } else {
                $new_page_object = ilPageObjectFactory::getInstance($a_parent_type, 0, 0, $l);
                $new_page_object->setParentId($a_parent_id);
                $new_page_object->setId($a_id);
            }
            $new_page_object->setXMLContent($orig_page->copyXMLContent($a_clone_mobs));
            $new_page_object->setActive($orig_page->getActive());
            $new_page_object->setActivationStart($orig_page->getActivationStart());
            $new_page_object->setActivationEnd($orig_page->getActivationEnd());
            if ($existed) {
                $new_page_object->buildDom();
                $new_page_object->update();
            } else {
                $new_page_object->create();
            }
        }
    }

    /**
     * Lookup translations
     *
     * @param string $a_parent_type parent type
     * @param int $a_id page id
     * @return array language codes
     */
    public static function lookupTranslations($a_parent_type, $a_id)
    {
        global $DIC;

        $db = $DIC->database();
        
        $set = $db->query(
            "SELECT lang FROM page_object " .
            " WHERE page_id = " . $db->quote($a_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text")
        );
        $langs = array();
        while ($rec = $db->fetchAssoc($set)) {
            $langs[] = $rec["lang"];
        }
        return $langs;
    }
    

    /**
     * Copy page to translation
     *
     * @param string $a_target_lang target language
     */
    public function copyPageToTranslation($a_target_lang)
    {
        $transl_page = ilPageObjectFactory::getInstance(
            $this->getParentType(),
            0,
            0,
            $a_target_lang
        );
        $transl_page->setId($this->getId());
        $transl_page->setParentId($this->getParentId());
        $transl_page->setXMLContent($this->copyXMLContent());
        $transl_page->setActive($this->getActive());
        $transl_page->setActivationStart($this->getActivationStart());
        $transl_page->setActivationEnd($this->getActivationEnd());
        $transl_page->create();
    }
    
    ////
    //// Page locking
    ////
    
    /**
     * Get page lock
     */
    public function getEditLock()
    {
        $db = $this->db;
        $user = $this->user;
        
        $min = (int) $this->getEffectiveEditLockTime();
        if ($min > 0) {
            // try to set the lock for the user
            $ts = time();
            $db->manipulate(
                "UPDATE page_object SET " .
                " edit_lock_user = " . $db->quote($user->getId(), "integer") . "," .
                " edit_lock_ts = " . $db->quote($ts, "integer") .
                " WHERE (edit_lock_user = " . $db->quote($user->getId(), "integer") . " OR " .
                " edit_lock_ts < " . $db->quote(time() - ($min * 60), "integer") . ") " .
                " AND page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text")
            );
            
            $set = $db->query(
                "SELECT edit_lock_user FROM page_object " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text")
            );
            $rec = $db->fetchAssoc($set);
            if ($rec["edit_lock_user"] != $user->getId()) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Release page lock
     */
    public function releasePageLock()
    {
        $db = $this->db;
        $user = $this->user;
        $aset = new ilSetting("adve");

        $min = (int) $aset->get("block_mode_minutes") ;
        if ($min > 0) {
            // try to set the lock for the user
            $ts = time();
            $db->manipulate(
                "UPDATE page_object SET " .
                " edit_lock_user = " . $db->quote($user->getId(), "integer") . "," .
                " edit_lock_ts = 0" .
                " WHERE edit_lock_user = " . $db->quote($user->getId(), "integer") .
                " AND page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text")
            );

            $set = $db->query(
                "SELECT edit_lock_user FROM page_object " .
                " WHERE page_id = " . $db->quote($this->getId(), "integer") .
                " AND parent_type = " . $db->quote($this->getParentType(), "text")
            );
            $rec = $db->fetchAssoc($set);
            if ($rec["edit_lock_user"] != $user->getId()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get edit lock info
     *
     * @return array info array
     */
    public function getEditLockInfo()
    {
        $db = $this->db;

        $aset = new ilSetting("adve");
        $min = (int) $aset->get("block_mode_minutes");
        
        $set = $db->query(
            "SELECT edit_lock_user, edit_lock_ts FROM page_object " .
            " WHERE page_id = " . $db->quote($this->getId(), "integer") .
            " AND parent_type = " . $db->quote($this->getParentType(), "text")
        );
        $rec = $db->fetchAssoc($set);
        $rec["edit_lock_until"] = $rec["edit_lock_ts"] + $min * 60;
        
        return $rec;
    }
    
    /**
     * Truncate (html) string
     *
     * @see http://dodona.wordpress.com/2009/04/05/how-do-i-truncate-an-html-string-without-breaking-the-html-code/
     *
     * @param string $a_text
     * @param int $a_length
     * @param string $a_ending
     * @param bool $a_exact
     * @param bool $a_consider_html
     * @return string
     */
    public static function truncateHTML($a_text, $a_length = 100, $a_ending = '...', $a_exact = false, $a_consider_html = true)
    {
        include_once "Services/Utilities/classes/class.ilStr.php";
                    
        if ($a_consider_html) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $a_text)) <= $a_length) {
                return $a_text;
            }
            
            // splits all html-tags to scanable lines
            $total_length = strlen($a_ending);
            $open_tags = array();
            $truncate = '';
            preg_match_all('/(<.+?>)?([^<>]*)/s', $a_text, $lines, PREG_SET_ORDER);
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                    }
                    // if tag is a closing tag
                    elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    }
                    // if tag is an opening tag
                    elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $a_length) {
                    // the number of characters which are left
                    $left = $a_length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }

                    // $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    $truncate .= ilStr::shortenText($line_matchings[2], 0, $left + $entities_length);
                    
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                
                // if the maximum length is reached, get off the loop
                if ($total_length >= $a_length) {
                    break;
                }
            }
        } else {
            if (strlen($a_text) <= $a_length) {
                return $a_text;
            } else {
                // $truncate = substr($a_text, 0, $a_length - strlen($a_ending));
                $truncate = ilStr::shortenText($a_text, 0, $a_length - strlen($a_ending));
            }
        }
        
        // THIS IS BUGGY AS IT MIGHT BREAK AN OPEN TAG AT THE END
        if (!sizeof($open_tags)) {
            // if the words shouldn't be cut in the middle...
            if (!$a_exact) {
                // ...search the last occurance of a space...
                $spacepos = strrpos($truncate, ' ');
                if ($spacepos !== false) {
                    // ...and cut the text in this position
                    // $truncate = substr($truncate, 0, $spacepos);
                    $truncate = ilStr::shortenText($truncate, 0, $spacepos);
                }
            }
        }
                
        // add the defined ending to the text
        $truncate .= $a_ending;
        
        if ($a_consider_html) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        
        return $truncate;
    }
    
    /**
     * Get content templates
     *
     * @return array array of arrays with "id" => page id (int), "parent_type" => parent type (string), "title" => title (string)
     */
    public function getContentTemplates()
    {
        return array();
    }

    /**
     * Get all pages for parent object
     *
     * @param string	$a_parent_type Parent Type
     * @param int		$a_parent_id Parent ID
     * @param string	$a_lang language
     */
    public static function getLastChangeByParent($a_parent_type, $a_parent_id, $a_lang = "")
    {
        global $DIC;

        $db = $DIC->database();

        $and_lang = "";
        if ($a_lang != "") {
            $and_lang = " AND lang = " . $db->quote($a_lang, "text");
        }

        $db->setLimit(1);
        $q = "SELECT last_change FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") . $and_lang .
            " ORDER BY last_change DESC";

        $set = $db->query($q);
        $rec = $db->fetchAssoc($set);

        return $rec["last_change"];
    }

    public function getEffectiveEditLockTime()
    {
        if ($this->getPageConfig()->getEditLockSupport() == false) {
            return 0;
        }
        
        $aset = new ilSetting("adve");
        $min = (int) $aset->get("block_mode_minutes") ;

        return $min;
    }

    /**
     * Get all file object ids
     *
     * @return array
     */
    public function getAllFileObjIds()
    {
        $file_obj_ids = array();

        // insert inst id file item identifier entries
        $xpc = xpath_new_context($this->dom);
        $path = "//FileItem/Identifier";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $file_obj_ids[] = $res->nodeset[$i]->get_attribute("Entry");
        }
        unset($xpc);
        return $file_obj_ids;
    }

    /**
     * Resolve resources
     * @todo: move this into proper "afterImport" routine that calls all PC components
     */
    public function resolveResources($ref_mapping)
    {
        include_once("./Services/COPage/classes/class.ilPCResources.php");
        ilPCResources::resolveResources($this, $ref_mapping);
    }

    /**
     * Get object id of repository object that contains this page, return 0 if page does not belong to a repo object
     * @return int
     */
    public function getRepoObjId()
    {
        return $this->getParentId();
    }

}
