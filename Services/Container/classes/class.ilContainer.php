<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

// note: the values are derived from ilObjCourse constants
// to enable easy migration from course view setting to container view setting

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilContainer
*
* Base class for all container objects (categories, courses, groups)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilObject
*/
class ilContainer extends ilObject
{
    /**
     * @var ilNewsService
     */
    protected $news;
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    protected $order_type = 0;
    protected $hiddenfilesfound = false;
    protected $news_timeline = false;
    protected $news_timeline_auto_entries = false;

    // container view constants
    const VIEW_SESSIONS = 0;
    const VIEW_OBJECTIVE = 1;
    const VIEW_TIMING = 2;
    const VIEW_ARCHIVE = 3;
    const VIEW_SIMPLE = 4;
    const VIEW_BY_TYPE = 5;
    const VIEW_INHERIT = 6;

    const VIEW_DEFAULT = self::VIEW_BY_TYPE;

    
    const SORT_TITLE = 0;
    const SORT_MANUAL = 1;
    const SORT_ACTIVATION = 2;
    const SORT_INHERIT = 3;
    const SORT_CREATION = 4;
    
    const SORT_DIRECTION_ASC = 0;
    const SORT_DIRECTION_DESC = 1;

    const SORT_NEW_ITEMS_POSITION_TOP = 0;
    const SORT_NEW_ITEMS_POSITION_BOTTOM = 1;

    const SORT_NEW_ITEMS_ORDER_TITLE = 0;
    const SORT_NEW_ITEMS_ORDER_CREATION = 1;
    const SORT_NEW_ITEMS_ORDER_ACTIVATION = 2;

    public static $data_preloaded = false;

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var ilObjectTranslation
     */
    protected $obj_trans = null;

    /**
     * @var ilRecommendedContentManager
     */
    protected $recommended_content_manager;

    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->news = $DIC->news();


        $this->setting = $DIC["ilSetting"];
        parent::__construct($a_id, $a_reference);
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");

        if ($this->getId() > 0) {
            $this->obj_trans = ilObjectTranslation::getInstance($this->getId());
        }
        $this->recommended_content_manager = new ilRecommendedContentManager();
    }

    /**
     * Get object translation
     * @return ilObjectTranslation
     */
    public function getObjectTranslation()
    {
        return $this->obj_trans;
    }

    /**
     * Get object translation
     * @param ilObjectTranslation $obj_trans
     */
    public function setObjectTranslation(ilObjectTranslation $obj_trans)
    {
        $this->obj_trans = $obj_trans;
    }

    /**
    * Create directory for the container.
    * It is <webspace_dir>/container_data.
    */
    public function createContainerDirectory()
    {
        $webspace_dir = ilUtil::getWebspaceDir();
        $cont_dir = $webspace_dir . "/container_data";
        if (!is_dir($cont_dir)) {
            ilUtil::makeDir($cont_dir);
        }
        $obj_dir = $cont_dir . "/obj_" . $this->getId();
        if (!is_dir($obj_dir)) {
            ilUtil::makeDir($obj_dir);
        }
    }
    
    /**
    * Get the container directory.
    *
    * @return	string	container directory
    */
    public function getContainerDirectory()
    {
        return $this->_getContainerDirectory($this->getId());
    }
    
    /**
    * Get the container directory.
    *
    * @return	string	container directory
    */
    public static function _getContainerDirectory($a_id)
    {
        return ilUtil::getWebspaceDir() . "/container_data/obj_" . $a_id;
    }

    /**
    * Set Found hidden files (set by getSubItems).
    *
    * @param	boolean	$a_hiddenfilesfound	Found hidden files (set by getSubItems)
    */
    public function setHiddenFilesFound($a_hiddenfilesfound)
    {
        $this->hiddenfilesfound = $a_hiddenfilesfound;
    }

    /**
    * Get Found hidden files (set by getSubItems).
    *
    * @return	boolean	Found hidden files (set by getSubItems)
    */
    public function getHiddenFilesFound()
    {
        return $this->hiddenfilesfound;
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
     * Set news timeline
     *
     * @param bool $a_val activate news timeline
     */
    public function setNewsTimeline($a_val)
    {
        $this->news_timeline = $a_val;
    }

    /**
     * Get news timeline
     *
     * @return bool activate news timeline
     */
    public function getNewsTimeline()
    {
        return $this->news_timeline;
    }
    
    /**
     * Set news timeline auto entries
     *
     * @param bool $a_val include automatically created entries
     */
    public function setNewsTimelineAutoEntries($a_val)
    {
        $this->news_timeline_auto_entries = $a_val;
    }
    
    /**
     * Get news timeline auto entries
     *
     * @return bool include automatically created entries
     */
    public function getNewsTimelineAutoEntries()
    {
        return $this->news_timeline_auto_entries;
    }

    /**
     * Set news timline is landing page
     *
     * @param bool $a_val is news timline landing page?
     */
    public function setNewsTimelineLandingPage($a_val)
    {
        $this->news_timeline_landing_page = $a_val;
    }

    /**
     * Get news timline is landing page
     *
     * @return bool is news timline landing page?
     */
    public function getNewsTimelineLandingPage()
    {
        return $this->news_timeline_landing_page;
    }

    /**
     * Is news timeline effective?
     *
     * @return bool
     */
    public function isNewsTimelineEffective()
    {
        if (!$this->news->isGloballyActivated()) {
            return false;
        }
        if ($this->getUseNews()) {
            if ($this->getNewsTimeline()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is news timeline landing page effective?
     *
     * @return bool
     */
    public function isNewsTimelineLandingPageEffective()
    {
        if ($this->getUseNews()) {
            if ($this->getNewsTimeline()) {
                if ($this->getNewsTimelineLandingPage()) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Set news block activated
     *
     * @param bool $a_val news block activated
     */
    public function setNewsBlockActivated($a_val)
    {
        $this->news_block_activated = $a_val;
    }
    
    /**
     * Get news block activated
     *
     * @return bool news block activated
     */
    public function getNewsBlockActivated()
    {
        return $this->news_block_activated;
    }
    
    /**
     * Set use news
     *
     * @param bool $a_val use news system?
     */
    public function setUseNews($a_val)
    {
        $this->use_news = $a_val;
    }
    
    /**
     * Get use news
     *
     * @return bool use news system?
     */
    public function getUseNews()
    {
        return $this->use_news;
    }
    
    /**
    * Lookup a container setting.
    *
    * @param	int			container id
    * @param	string		setting keyword
    *
    * @return	string		setting value
    */
    public static function _lookupContainerSetting($a_id, $a_keyword, $a_default_value = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer') . " AND " .
                " keyword = " . $ilDB->quote($a_keyword, 'text');
        $set = $ilDB->query($q);
        $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        
        if (isset($rec['value'])) {
            return $rec["value"];
        }
        if ($a_default_value === null) {
            return '';
        }
        return $a_default_value;
    }

    /**
     * @param $a_id
     * @param $a_keyword
     * @param $a_value
     */
    public static function _writeContainerSetting($a_id, $a_keyword, $a_value)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM container_settings WHERE " .
            "id = " . $ilDB->quote($a_id, 'integer') . " " .
            "AND keyword = " . $ilDB->quote($a_keyword, 'text');
        $res = $ilDB->manipulate($query);

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Write container setting, id: " . $a_id . ", keyword: " . $a_keyword . ", value: " . $a_value);

        $query = "INSERT INTO container_settings (id, keyword, value) VALUES (" .
            $ilDB->quote($a_id, 'integer') . ", " .
            $ilDB->quote($a_keyword, 'text') . ", " .
            $ilDB->quote($a_value, 'text') .
            ")";

        $res = $ilDB->manipulate($query);
    }
    
    public static function _getContainerSettings($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT * FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer');
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["keyword"]] = $row["value"];
        }
        
        return $res;
    }
    
    public static function _deleteContainerSettings($a_id, $a_keyword = null, $a_keyword_like = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (!$a_id) {
            return;
        }
        
        $sql = "DELETE FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer');
        if ($a_keyword) {
            if (!$a_keyword_like) {
                $sql .= " AND keyword = " . $ilDB->quote($a_keyword, "text");
            } else {
                $sql .= " AND " . $ilDB->like("keyword", "text", $a_keyword);
            }
        }
        $ilDB->manipulate($sql);
    }
    
    public static function _exportContainerSettings(ilXmlWriter $a_xml, $a_obj_id)
    {
        // container settings
        $settings = self::_getContainerSettings($a_obj_id);
        if (sizeof($settings)) {
            $a_xml->xmlStartTag("ContainerSettings");
            
            foreach ($settings as $keyword => $value) {
                // :TODO: proper custom icon export/import
                if (stristr($keyword, "icon")) {
                    continue;
                }
                
                $a_xml->xmlStartTag(
                    'ContainerSetting',
                    array(
                        'id' => $keyword,
                    )
                );
                
                $a_xml->xmlData($value);
                $a_xml->xmlEndTag("ContainerSetting");
            }
            
            $a_xml->xmlEndTag("ContainerSettings");
        }
    }

    /**
     * Clone container settings
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     * @return object new object
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        /** @var ilObjCourse $new_obj */
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        // translations
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $ot = ilObjectTranslation::getInstance($this->getId());
        $ot->copy($new_obj->getId());

        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        #18624 - copy all sorting settings
        ilContainerSortingSettings::_cloneSettings($this->getId(), $new_obj->getId());
        
        // copy content page
        include_once("./Services/Container/classes/class.ilContainerPage.php");
        if (ilContainerPage::_exists(
            "cont",
            $this->getId()
        )) {
            $orig_page = new ilContainerPage($this->getId());
            $orig_page->copy($new_obj->getId(), "cont", $new_obj->getId());
        }

        // #20614 - copy style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = $this->getStyleSheetId();
        if ($style_id > 0) {
            if (!ilObjStyleSheet::_lookupStandard($style_id)) {
                $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
                $new_id = $style_obj->ilClone();
                $new_obj->setStyleSheetId($new_id);
                $new_obj->update();
            } else {
                $new_obj->setStyleSheetId($this->getStyleSheetId());
            }
        }

        // #10271 - copy start objects page
        include_once("./Services/Container/classes/class.ilContainerStartObjectsPage.php");
        if (ilContainerStartObjectsPage::_exists(
            "cstr",
            $this->getId()
        )) {
            $orig_page = new ilContainerStartObjectsPage($this->getId());
            $orig_page->copy($new_obj->getId(), "cstr", $new_obj->getId());
        }
        
        // #10271
        foreach (self::_getContainerSettings($this->getId()) as $keyword => $value) {
            self::_writeContainerSetting($new_obj->getId(), $keyword, $value);
        }

        $new_obj->setNewsTimeline($this->getNewsTimeline());
        $new_obj->setNewsBlockActivated($this->getNewsBlockActivated());
        $new_obj->setUseNews($this->getUseNews());
        $new_obj->setNewsTimelineAutoEntries($this->getNewsTimelineAutoEntries());
        $new_obj->setNewsTimelineLandingPage($this->getNewsTimelineLandingPage());
        ilBlockSetting::cloneSettingsOfBlock("news", $this->getId(), $new_obj->getId());
        $mom_noti = new ilMembershipNotifications($this->getRefId());
        $mom_noti->cloneSettings($new_obj->getRefId());

        return $new_obj;
    }
    
    /**
     * Clone object dependencies (container sorting)
     *
     * @access public
     * @param int target ref id of new course
     * @param int copy id
     * return bool
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        $ilLog = $this->log;
        
        parent::cloneDependencies($a_target_id, $a_copy_id);

        include_once('./Services/Container/classes/class.ilContainerSorting.php');
        ilContainerSorting::_getInstance($this->getId())->cloneSorting($a_target_id, $a_copy_id);

        // fix internal links to other objects
        ilContainer::fixInternalLinksAfterCopy($a_target_id, $a_copy_id, $this->getRefId());
        
        // fix item group references in page content
        include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");
        ilObjItemGroup::fixContainerItemGroupRefsAfterCloning($this, $a_copy_id);
        
        include_once('Services/Object/classes/class.ilObjectLP.php');
        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $collection->cloneCollection($a_target_id, $a_copy_id);
        }

        return true;
    }

    /**
     * clone all objects according to this container
     *
     * @param string $session_id
     * @param string $client_id
     * @param string $new_type
     * @param int $ref_id
     * @param int $clone_source
     * @param array $options
     * @param bool force soap
     * @param int submode 1 => copy all, 2 => copy content
     * @return new refid if clone has finished or parameter ref id if cloning is still in progress
     * @return array(copy_id => xyz, ref_id => new ref_id)
     */
    public function cloneAllObject($session_id, $client_id, $new_type, $ref_id, $clone_source, $options, $soap_call = false, $a_submode = 1)
    {
        $ilLog = $this->log;
        
        include_once('./Services/Link/classes/class.ilLink.php');
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        
        $ilAccess = $this->access;
        $ilErr = $this->error;
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $ilUser = $this->user;
            
        // Save wizard options
        $copy_id = ilCopyWizardOptions::_allocateCopyId();
        $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
        $wizard_options->saveOwner($ilUser->getId());
        $wizard_options->saveRoot($clone_source);
            
        // add entry for source container
        $wizard_options->initContainer($clone_source, $ref_id);
        
        foreach ($options as $source_id => $option) {
            $wizard_options->addEntry($source_id, $option);
        }
        $wizard_options->read();
        $wizard_options->storeTree($clone_source);
        
        include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
        if ($a_submode == ilObjectCopyGUI::SUBMODE_CONTENT_ONLY) {
            ilLoggerFactory::getLogger('obj')->info('Copy content only...');
            ilLoggerFactory::getLogger('obj')->debug('Added mapping, source ID: ' . $clone_source . ', target ID: ' . $ref_id);
            $wizard_options->read();
            $wizard_options->dropFirstNode();
            $wizard_options->appendMapping($clone_source, $ref_id);
        }
        
        
        #print_r($options);
        // Duplicate session to avoid logout problems with backgrounded SOAP calls
        $new_session_id = ilSession::_duplicate($session_id);
        // Start cloning process using soap call
        include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

        $soap_client = new ilSoapClient();
        $soap_client->setResponseTimeout(5);
        $soap_client->enableWSDL(true);

        $ilLog->write(__METHOD__ . ': Trying to call Soap client...');
        if ($soap_client->init()) {
            ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
            $res = $soap_client->call('ilClone', array($new_session_id . '::' . $client_id, $copy_id));
        } else {
            ilLoggerFactory::getLogger('obj')->warning('SOAP clone call failed. Calling clone method manually');
            $wizard_options->disableSOAP();
            $wizard_options->read();
            include_once('./webservice/soap/include/inc.soap_functions.php');
            $res = ilSoapFunctions::ilClone($new_session_id . '::' . $client_id, $copy_id);
        }
        return array(
                'copy_id' => $copy_id,
                'ref_id' => (int) $res
        );
    }

    /**
     * delete category and all related data
     *
     * @return	boolean	true if all object data were removed; false if only a references were removed
     */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // delete translations
        $this->obj_trans->delete();

        return true;
    }

    /**
    * Get container view mode
    */
    public function getViewMode()
    {
        return ilContainer::VIEW_BY_TYPE;
    }
    
    /**
    * Get order type default implementation
    */
    public function getOrderType()
    {
        return $this->order_type ? $this->order_type : ilContainer::SORT_TITLE;
    }

    public function setOrderType($a_value)
    {
        $this->order_type = $a_value;
    }

    /**
     * Is classification filter active?
     * @return bool
     */
    public function isClassificationFilterActive() : bool
    {
        // apply container classification filters
        $repo = new ilClassificationSessionRepository($this->getRefId());
        foreach (ilClassificationProvider::getValidProviders($this->getRefId(), $this->getId(), $this->getType()) as $class_provider) {
            $id = get_class($class_provider);
            $current = $repo->getValueForProvider($id);
            if ($current) {
                return true;
            }
        }
        return false;
    }

    /**
     * Note grp/crs currently allow to filter in their whole subtrees
     * Catetories only their direct childs
     * @return bool
     */
    public function filteredSubtree() : bool
    {
        if ($this->isClassificationFilterActive() && in_array($this->getType(), ["grp", "crs"])) {
            return true;
        }
        return false;
    }

    /**
     * Get initial subitems
     *
     * @return array
     */
    protected function getInitialSubitems() : array
    {
        $tree = $this->tree;
        if ($this->filteredSubtree()) {
            $objects = $tree->getSubTree($tree->getNodeData($this->getRefId()));
        } else {
            $objects = $tree->getChilds($this->getRefId(), "title");
        }
        return $objects;
    }

    /**
    * Get subitems of container
    *
    * @param bool administration panel enabled
    * @param bool side blocks enabled
    *
    * @return	array
    */
    public function getSubItems(
        $a_admin_panel_enabled = false,
        $a_include_side_block = false,
        $a_get_single = 0,
        \ilContainerUserFilter $container_user_filter = null
    ) {
        $objDefinition = $this->obj_definition;

        // Caching
        if (is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]) &&
            !$a_get_single) {
            return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
        }
        
        $objects = $this->getInitialSubitems();
        $objects = $this->applyContainerUserFilter($objects, $container_user_filter);
        $objects = self::getCompleteDescriptions($objects);

        // apply container classification filters
        $repo = new ilClassificationSessionRepository($this->getRefId());
        foreach (ilClassificationProvider::getValidProviders($this->getRefId(), $this->getId(), $this->getType()) as $class_provider) {
            $id = get_class($class_provider);
            $current = $repo->getValueForProvider($id);
            if ($current) {
                $class_provider->setSelection($current);
                $filtered = $class_provider->getFilteredObjects();
                $objects = array_filter($objects, function ($i) use ($filtered) {
                    return (is_array($filtered) && in_array($i["obj_id"], $filtered));
                });
                //if (count($filtered) > 0) {
                //    var_dump($filtered);
                //    echo "<br><br>";
                //    var_dump($objects);
                //    exit;
                //}
            }
        }

        $found = false;
        $all_ref_ids = array();
        
        if (!self::$data_preloaded) {
            include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
            $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
        }

        include_once('Services/Container/classes/class.ilContainerSorting.php');
        $sort = ilContainerSorting::_getInstance($this->getId());

        // TODO: check this
        // get items attached to a session
        include_once './Modules/Session/classes/class.ilEventItems.php';
        //$event_items = ilEventItems::_getItemsOfContainer($this->getRefId());

        $classification_filter_active = $this->isClassificationFilterActive();
        foreach ($objects as $key => $object) {
            if ($a_get_single > 0 && $object["child"] != $a_get_single) {
                continue;
            }
            
            // hide object types in devmode
            if ($objDefinition->getDevMode($object["type"]) || $object["type"] == "adm"
                || $object["type"] == "rolf") {
                continue;
            }
            
            // remove inactive plugins
            if ($objDefinition->isInactivePlugin($object["type"])) {
                continue;
            }

            // BEGIN WebDAV: Don't display hidden Files, Folders and Categories
            if (in_array($object['type'], array('file','fold','cat'))) {
                include_once 'Modules/File/classes/class.ilObjFileAccess.php';
                if (ilObjFileAccess::_isFileHidden($object['title'])) {
                    $this->setHiddenFilesFound(true);
                    if (!$a_admin_panel_enabled) {
                        continue;
                    }
                }
            }
            // END WebDAV: Don't display hidden Files, Folders and Categories
            
            // including event items!
            if (!self::$data_preloaded) {
                $preloader->addItem($object["obj_id"], $object["type"], $object["child"]);
            }
            
            // filter out items that are attached to an event
            // no, see #34701
            /*if (in_array($object['ref_id'], $event_items) && !$classification_filter_active) {
                continue;
            }*/
            
            // filter side block items
            if (!$a_include_side_block && $objDefinition->isSideBlock($object['type'])) {
                continue;
            }

            $all_ref_ids[] = $object["child"];
        }
                        
        // data preloader
        if (!self::$data_preloaded) {
            $preloader->preload();
            unset($preloader);
            
            self::$data_preloaded = true;
        }

        foreach ($objects as $key => $object) {
            // see above, objects were filtered
            if (!in_array($object["child"], $all_ref_ids)) {
                continue;
            }
            
            // group object type groups together (e.g. learning resources)
            $type = $objDefinition->getGroupOfObj($object["type"]);
            if ($type == "") {
                $type = $object["type"];
            }
            
            // this will add activation properties
            $this->addAdditionalSubItemInformation($object);
            
            $this->items[$type][$key] = $object;
                        
            $this->items["_all"][$key] = $object;
            if ($object["type"] != "sess") {
                $this->items["_non_sess"][$key] = $object;
            }
        }
        $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]
            = $sort->sortItems($this->items);

        return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
    }
    
    /**
    * Check whether we got any items
    */
    public function gotItems()
    {
        if (is_array($this->items["_all"]) && count($this->items["_all"]) > 0) {
            return true;
        }
        return false;
    }
    
    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$object)
    {
    }
    
    /**
    * Get grouped repository object types.
    *
    * @return	array	array of object types
    */
    public function getGroupedObjTypes()
    {
        $objDefinition = $this->obj_definition;
        
        if (empty($this->type_grps)) {
            $this->type_grps = $objDefinition->getGroupedRepositoryObjectTypes($this->getType());
        }
        return $this->type_grps;
    }
    
    /**
    * Check whether page editing is allowed for container
    */
    public function enablePageEditing()
    {
        $ilSetting = $this->setting;
        
        // @todo: this will need a more general approach
        if ($ilSetting->get("enable_cat_page_edit")) {
            return true;
        }
    }
    
    /**
    * Create
    */
    public function create()
    {
        global $DIC;

        $lng = $DIC->language();

        $ret = parent::create();

        // set translation object, since we have an object id now
        $this->obj_trans = ilObjectTranslation::getInstance($this->getId());

        // add default translation
        $this->addTranslation(
            $this->getTitle(),
            $this->getDescription(),
            $lng->getDefaultLanguage(),
            true
        );

        if (((int) $this->getStyleSheetId()) > 0) {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());
        }

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Create Container, id: " . $this->getId());

        self::_writeContainerSetting($this->getId(), "news_timeline", (int) $this->getNewsTimeline());
        self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (int) $this->getNewsTimelineAutoEntries());
        self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (int) $this->getNewsTimelineLandingPage());
        include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::NEWS_VISIBILITY, (int) $this->getNewsBlockActivated());
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, (int) $this->getUseNews());

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function putInTree($a_parent_ref)
    {
        parent::putInTree($a_parent_ref);

        // copy title, icon actions visibilities
        if (self::_lookupContainerSetting(ilObject::_lookupObjId($a_parent_ref), "hide_header_icon_and_title")) {
            self::_writeContainerSetting($this->getId(), "hide_header_icon_and_title", true);
        }
        if (self::_lookupContainerSetting(ilObject::_lookupObjId($a_parent_ref), "hide_top_actions")) {
            self::_writeContainerSetting($this->getId(), "hide_top_actions", true);
        }
    }

    /**
    * Update
    */
    public function update()
    {
        $ret = parent::update();

        $trans = $this->getObjectTranslation();
        $trans->setDefaultTitle($this->getTitle());
        $trans->setDefaultDescription($this->getLongDescription());
        $trans->save();

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Update Container, id: " . $this->getId());

        self::_writeContainerSetting($this->getId(), "news_timeline", (int) $this->getNewsTimeline());
        self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (int) $this->getNewsTimelineAutoEntries());
        self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (int) $this->getNewsTimelineLandingPage());
        include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::NEWS_VISIBILITY, (int) $this->getNewsBlockActivated());
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, (int) $this->getUseNews());

        return $ret;
    }
    
    
    /**
     * read
     *
     * @access public
     * @param
     * @return
     */
    public function read()
    {
        parent::read();

        include_once("./Services/Container/classes/class.ilContainerSortingSettings.php");
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
        
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->setStyleSheetId((int) ilObjStyleSheet::lookupObjectStyle($this->getId()));

        $this->readContainerSettings();
        $this->obj_trans = ilObjectTranslation::getInstance($this->getId());
    }

    /**
     * Read container settings
     *
     * @param
     * @return
     */
    public function readContainerSettings()
    {
        $this->setNewsTimeline(self::_lookupContainerSetting($this->getId(), "news_timeline"));
        $this->setNewsTimelineAutoEntries(self::_lookupContainerSetting($this->getId(), "news_timeline_incl_auto"));
        $this->setNewsTimelineLandingPage(self::_lookupContainerSetting($this->getId(), "news_timeline_landing_page"));
        include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
        $this->setNewsBlockActivated(self::_lookupContainerSetting(
            $this->getId(),
            ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
            $this->setting->get('block_activated_news', true)
        ));
        $this->setUseNews(
            self::_lookupContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, true)
            && $this->news->isGloballyActivated()
        );
    }


    /**
     * overwrites description fields to long or short description in an assoc array
     * keys needed (obj_id and description)
     *
     * @param array $objects
     * @return array
     */
    public static function getCompleteDescriptions(array $objects)
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $ilObjDataCache = $DIC["ilObjDataCache"];
        // using long descriptions?
        $short_desc = $ilSetting->get("rep_shorten_description");
        $short_desc_max_length = $ilSetting->get("rep_shorten_description_length");
        if (!$short_desc || $short_desc_max_length > 0) {
            // using (part of) shortened description
            if ($short_desc && $short_desc_max_length && $short_desc_max_length < ilObject::DESC_LENGTH) {
                foreach ($objects as $key => $object) {
                    $objects[$key]["description"] = ilUtil::shortenText($object["description"], $short_desc_max_length, true);
                }
            }
            // using (part of) long description
            else {
                $obj_ids = array();
                foreach ($objects as $key => $object) {
                    $obj_ids[] = $object["obj_id"];
                }
                if (sizeof($obj_ids)) {
                    $long_desc = ilObject::getLongDescriptions($obj_ids);
                    foreach ($objects as $key => $object) {
                        // #12166 - keep translation, ignore long description
                        if ($ilObjDataCache->isTranslatedDescription($object["obj_id"])) {
                            $long_desc[$object["obj_id"]] = $object["description"];
                        }
                        if ($short_desc && $short_desc_max_length) {
                            $long_desc[$object["obj_id"]] = ilUtil::shortenText($long_desc[$object["obj_id"]], $short_desc_max_length, true);
                        }
                        $objects[$key]["description"] = $long_desc[$object["obj_id"]];
                    }
                }
            }
        }
        return $objects;
    }

    /**
     * Fix internal links after copy process
     *
     * @param int $a_target_id ref if of new container
     * @param int $a_copy_id copy process id
     */
    protected static function fixInternalLinksAfterCopy($a_target_id, $a_copy_id, $a_source_ref_id)
    {
        global $DIC;

        /** @var ilObjectDefinition $obj_definition */
        $obj_definition = $DIC["objDefinition"];

        $obj_id = ilObject::_lookupObjId($a_target_id);
        include_once("./Services/Container/classes/class.ilContainerPage.php");

        include_once("./Services/CopyWizard/classes/class.ilCopyWizardOptions.php");
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mapping = $cwo->getMappings();

        if (ilContainerPage::_exists("cont", $obj_id)) {
            $pg = new ilContainerPage($obj_id);
            $pg->handleRepositoryLinksOnCopy($mapping, $a_source_ref_id);
            $pg->update(true, true);
        }

        foreach ($mapping as $old_ref_id => $new_ref_id) {
            if (!is_numeric($old_ref_id) || !is_numeric($new_ref_id)) {
                continue;
            }

            $type = ilObject::_lookupType($new_ref_id, true);
            $class = 'il' . $obj_definition->getClassName($type) . 'PageCollector';
            $loc = $obj_definition->getLocation($type);
            $file = $loc . '/class.' . $class . '.php';

            if (is_file($file)) {
                /** @var ilCOPageCollectorInterface $coll */
                $coll = new $class();
                foreach ($coll->getAllPageIds(ilObject::_lookupObjId($new_ref_id)) as $page_id) {
                    if (ilPageObject::_exists($page_id['parent_type'], $page_id['id'], $page_id['lang'])) {
                        /** @var ilPageObject $page */
                        $page = ilPageObjectFactory::getInstance($page_id['parent_type'], $page_id['id'], 0, $page_id['lang']);
                        $page->handleRepositoryLinksOnCopy($mapping, $a_source_ref_id);
                        $page->update(true, true);
                    }
                }
            }
        }
    }

    /**
     * Remove all translations of container
     */
    public function removeTranslations()
    {
        $this->obj_trans->delete();
    }

    /**
     * Delete translation
     *
     * @param $a_lang
     */
    public function deleteTranslation($a_lang)
    {
        $this->obj_trans->removeLanguage($a_lang);
        $this->obj_trans->save();
    }

    /**
     * Add translation
     *
     * @param $a_title
     * @param $a_desc
     * @param $a_lang
     * @param $a_lang_default
     * @return bool
     */
    public function addTranslation($a_title, $a_desc, $a_lang, $a_lang_default)
    {
        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $this->obj_trans->addLanguage($a_lang, $a_title, $a_desc, $a_lang_default, true);
        $this->obj_trans->save();

        return true;
    }

    /**
     * Apply container user filter on objects
     *
     * @todo this deserces a decentralized general concept (consumers provide object filter types)
     * @todo move selects to respective components
     *
     * @param $objects
     * @param ilContainerUserFilter|null $container_user_filter
     * @return array
     * @throws ilException
     */
    protected function applyContainerUserFilter($objects, ilContainerUserFilter $container_user_filter = null)
    {
        global $DIC;
        $db = $DIC->database();

        if (is_null($container_user_filter)) {
            return $objects;
        }

        if ($container_user_filter->isEmpty() && !ilContainer::_lookupContainerSetting($this->getId(), "filter_show_empty", false)) {
            return [];
        }


        $obj_ids = array_map(function ($i) {
            return $i["obj_id"];
        }, $objects);
        $filter_data = $container_user_filter->getData();
        if (is_array($filter_data)) {
            foreach ($filter_data as $key => $val) {
                if (count($obj_ids) == 0) {    // stop if no object ids are left
                    continue;
                }
                if (!in_array(substr($key, 0, 4), ["adv_", "std_"])) {
                    continue;
                }
                if ($val == "") {
                    continue;
                }
                $field_id = substr($key, 4);
                $val = ilUtil::stripSlashes($val);
                $query_parser = new ilQueryParser($val);
                if (substr($key, 0, 4) == "std_") {
                    // object type
                    if ($field_id == ilContainerFilterField::STD_FIELD_OBJECT_TYPE) {
                        $result = null;
                        $set = $db->queryF(
                            "SELECT obj_id FROM object_data " .
                            " WHERE  " . $db->in("obj_id", $obj_ids, false, "integer") .
                            " AND type = %s",
                            array("text"),
                            array($val)
                        );
                        $result_obj_ids = [];
                        while ($rec = $db->fetchAssoc($set)) {
                            $result_obj_ids[] = $rec["obj_id"];
                        }
                        $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                    } elseif ($field_id == ilContainerFilterField::STD_FIELD_ONLINE) {
                        if (in_array($val, [1, 2])) {
                            $online_where = ($val == 1)
                                ? " (offline <> " . $db->quote(1, "integer") . " OR offline IS NULL) "
                                : " offline = " . $db->quote(1, "integer") . " ";
                            $result = null;
                            $set = $db->queryF(
                                "SELECT obj_id FROM object_data " .
                                " WHERE  " . $db->in("obj_id", $obj_ids, false, "integer") .
                                " AND " . $online_where,
                                [],
                                []
                            );
                            $result_obj_ids = [];
                            while ($rec = $db->fetchAssoc($set)) {
                                $result_obj_ids[] = $rec["obj_id"];
                            }
                            $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                            $obj_ids = $this->legacyOnlineFilter($obj_ids, $objects, $val);
                        }
                    } elseif ($field_id == ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT) {
                        $result = null;
                        $set = $db->queryF(
                            "SELECT DISTINCT(obj_id) FROM obj_members m JOIN usr_data u ON (u.usr_id = m.usr_id) " .
                            " WHERE  " . $db->in("m.obj_id", $obj_ids, false, "integer") .
                            " AND " . $db->like("u.lastname", "text", $val) .
                            " AND m.contact = %s",
                            array("integer"),
                            array(1)
                        );
                        $result_obj_ids = [];
                        while ($rec = $db->fetchAssoc($set)) {
                            $result_obj_ids[] = $rec["obj_id"];
                        }
                        $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                    } elseif ($field_id == ilContainerFilterField::STD_FIELD_COPYRIGHT) {
                        $result = null;
                        $set = $db->queryF(
                            "SELECT DISTINCT(rbac_id) FROM il_meta_rights " .
                            " WHERE  " . $db->in("rbac_id", $obj_ids, false, "integer") .
                            " AND description = %s ",
                            array("text"),
                            array('il_copyright_entry__' . IL_INST_ID . '__' . $val)
                        );
                        $result_obj_ids = [];
                        while ($rec = $db->fetchAssoc($set)) {
                            $result_obj_ids[] = $rec["rbac_id"];
                        }
                        $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                    } else {
                        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
                        include_once 'Services/Search/classes/class.ilQueryParser.php';

                        #$query_parser->setCombination($this->options['title_ao']);
                        $query_parser->setCombination(QP_COMBINATION_OR);
                        $query_parser->parse();
                        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);

                        //$meta_search->setFilter($this->filter);		// object types ['lm', ...]
                        switch ($field_id) {
                            case ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION:
                            case ilContainerFilterField::STD_FIELD_DESCRIPTION:
                            case ilContainerFilterField::STD_FIELD_TITLE:
                                $meta_search->setMode('title_description');
                                break;
                            case ilContainerFilterField::STD_FIELD_KEYWORD:
                                $meta_search->setMode('keyword_all');
                                break;
                            case ilContainerFilterField::STD_FIELD_AUTHOR:
                                $meta_search->setMode('contribute');
                                break;

                        }
                        //$meta_search->setOptions($this->options);
                        $result = $meta_search->performSearch();
                    }
                } else {        // advanced metadata search
                    $field = ilAdvancedMDFieldDefinition::getInstance($field_id);

                    $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                        $field->getADTDefinition(),
                        true,
                        false
                    );
                    $field_form->setElementId("query[" . $key . "]");
                    $field_form->validate();

                    /**
                     * Workaround:
                     * Only text fields take care of $parser_value being passed through
                     * new ilQueryParser($parser_value), thus other fields pass values by setting
                     * directly in the ADT objects. This could go to a new bridge.
                     * Workaround #2:
                     * Subtracting the value by 1 for selects completes the workaround started in
                     * ilContainerFilterUtil::getFilterForRefId. This is necessary in R7 since KS
                     * selects are confused by the value 0. For R8 this is somehow not a problem.
                     */
                    if ($field instanceof ilAdvancedMDFieldDefinitionSelectMulti) {
                        $field_form->getADT()->setSelections([$val - 1]);
                    }
                    if ($field instanceof ilAdvancedMDFieldDefinitionSelect) {
                        $adt = $field_form->getADT();
                        if ($adt instanceof ilADTMultiEnumText) {
                            $field_form->getADT()->setSelections([$val - 1]);
                        } else {
                            $field_form->getADT()->setSelection($val - 1);
                        }
                    }
                    if ($field instanceof ilAdvancedMDFieldDefinitionInteger) {
                        $field_form->getADT()->setNumber((int) $val);
                    }

                    include_once 'Services/Search/classes/class.ilQueryParser.php';
                    include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
                    $adv_md_search = ilObjectSearchFactory::_getAdvancedMDSearchInstance($query_parser);
                    //$adv_md_search->setFilter($this->filter);	// this could be set to an array of object types
                    $adv_md_search->setDefinition($field);            // e.g. ilAdvancedMDFieldDefinitionSelectMulti
                    $adv_md_search->setIdFilter(array(0));
                    $adv_md_search->setSearchElement($field_form);    // e.g. ilADTEnumSearchBridgeMulti
                    $result = $adv_md_search->performSearch();
                }

                // intersect results
                if ($result instanceof ilSearchResult) {
                    $result_obj_ids = array_map(
                        function ($i) {
                            return $i["obj_id"];
                        },
                        $result->getEntries()
                    );
                    $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                }
            }
        }
        $objects = array_filter($objects, function ($o) use ($obj_ids) {
            return in_array($o["obj_id"], $obj_ids);
        });

        return $objects;
    }

    /**
     * Legacy online filter
     *
     * This can be removed, once all objects use the central online/offline property
     *
     * @param $obj_ids
     * @param $objects
     * @param $val
     * @return mixed
     */
    protected function legacyOnlineFilter($obj_ids, $objects, $val)
    {
        $legacy_types = ["glo", "wiki", "qpl", "book", "dcl", "prtt"];
        foreach ($legacy_types as $type) {
            $lobjects = array_filter($objects, function ($o) use ($type) {
                return ($o["type"] == $type);
            });
            $lobj_ids = array_map(function ($i) {
                return $i["obj_id"];
            }, $lobjects);
            switch ($type) {
                case "glo":
                    $status = ilObjGlossaryAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "wiki":
                    $status = ilObjWikiAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "book":
                    $status = ilObjBookingPoolAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "qpl":
                    $status = [];
                    foreach ($lobj_ids as $lid) {
                        $status[$lid] = ilObjQuestionPoolAccess::isOnline($lid);
                    }
                    break;
                case "dcl":
                    $status = [];
                    foreach ($lobj_ids as $lid) {
                        $status[$lid] = ilObjDataCollectionAccess::_lookupOnline($lid);
                    }
                    break;
                case "prtt":
                    $status = ilObjPortfolioTemplateAccess::_lookupOnlineStatus($lobj_ids);
                    break;
            }
            foreach ($status as $obj_id => $online) {
                if ($val == 1 && !$online || $val == 2 && $online) {
                    if (($key = array_search($obj_id, $obj_ids)) !== false) {
                        unset($obj_ids[$key]);
                    }
                } elseif (!in_array($obj_id, $obj_ids)) {
                    $obj_ids[] = $obj_id;
                }
            }
        }

        return $obj_ids;
    }
} // END class ilContainer
