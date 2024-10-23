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

use ILIAS\News\Service as News;

/**
 * Class ilContainer
 *
 * Base class for all container objects (categories, courses, groups)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainer extends ilObject
{
    protected News $news;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;
    // container view constants
    public const VIEW_SESSIONS = 0;
    public const VIEW_OBJECTIVE = 1;
    public const VIEW_TIMING = 2;
    public const VIEW_ARCHIVE = 3;
    public const VIEW_SIMPLE = 4;
    public const VIEW_BY_TYPE = 5;
    public const VIEW_INHERIT = 6;

    public const VIEW_DEFAULT = self::VIEW_BY_TYPE;

    public const SORT_TITLE = 0;
    public const SORT_MANUAL = 1;
    public const SORT_ACTIVATION = 2;
    public const SORT_INHERIT = 3;
    public const SORT_CREATION = 4;

    public const SORT_DIRECTION_ASC = 0;
    public const SORT_DIRECTION_DESC = 1;

    public const SORT_NEW_ITEMS_POSITION_TOP = 0;
    public const SORT_NEW_ITEMS_POSITION_BOTTOM = 1;

    public const SORT_NEW_ITEMS_ORDER_TITLE = 0;
    public const SORT_NEW_ITEMS_ORDER_CREATION = 1;
    public const SORT_NEW_ITEMS_ORDER_ACTIVATION = 2;

    public const TILE_NORMAL = 0;
    public const TILE_SMALL = 1;
    public const TILE_LARGE = 2;
    public const TILE_EXTRA_LARGE = 3;
    public const TILE_FULL = 4;

    public static bool $data_preloaded = false;
    protected \ILIAS\Container\InternalDomainService $domain;

    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    public array $items = [];
    protected ?ilObjectDefinition $obj_definition;
    protected int $order_type = 0;
    protected bool $hiddenfilesfound = false;
    protected bool $news_timeline = false;
    protected bool $news_timeline_auto_entries = false;
    protected ilSetting $setting;
    protected ?ilObjectTranslation $obj_trans = null;
    protected int $style_id = 0;
    protected bool $news_timeline_landing_page = false;
    protected bool $news_block_activated = false;
    protected bool $use_news = false;
    protected ilRecommendedContentManager $recommended_content_manager;

    protected ?array $type_grps = null;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        /** @var \ILIAS\DI\Container $DIC */
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

        if ($this->getId() > 0) {
            $this->obj_trans = ilObjectTranslation::getInstance($this->getId());
        }
        $this->recommended_content_manager = new ilRecommendedContentManager();
        $this->content_style_domain = $DIC->contentStyle()->domain();
        $this->domain = $DIC->container()->internal()->domain();
    }

    /**
     * @return array<int,string>
     */
    public function getTileSizes(): array
    {
        $lng = $this->lng;
        return [
            self::TILE_SMALL => $lng->txt("cont_tile_size_1"),
            self::TILE_NORMAL => $lng->txt("cont_tile_size_0"),
            self::TILE_LARGE => $lng->txt("cont_tile_size_2"),
            self::TILE_EXTRA_LARGE => $lng->txt("cont_tile_size_3"),
            self::TILE_FULL => $lng->txt("cont_tile_size_4")
        ];
    }

    public function getObjectTranslation(): ?ilObjectTranslation
    {
        return $this->obj_trans;
    }

    public function setObjectTranslation(?ilObjectTranslation $obj_trans): void
    {
        $this->obj_trans = $obj_trans;
    }

    // <webspace_dir>/container_data.
    public function createContainerDirectory(): void
    {
        $webspace_dir = ilFileUtils::getWebspaceDir();
        $cont_dir = $webspace_dir . "/container_data";
        if (!is_dir($cont_dir)) {
            ilFileUtils::makeDir($cont_dir);
        }
        $obj_dir = $cont_dir . "/obj_" . $this->getId();
        if (!is_dir($obj_dir)) {
            ilFileUtils::makeDir($obj_dir);
        }
    }

    public function getContainerDirectory(): string
    {
        return self::_getContainerDirectory($this->getId());
    }

    public static function _getContainerDirectory(int $a_id): string
    {
        return ilFileUtils::getWebspaceDir() . "/container_data/obj_" . $a_id;
    }

    // Set Found hidden files (set by getSubItems).
    public function setHiddenFilesFound(bool $a_hiddenfilesfound): void
    {
        $this->hiddenfilesfound = $a_hiddenfilesfound;
    }

    public function getHiddenFilesFound(): bool
    {
        return $this->hiddenfilesfound;
    }

    public function getStyleSheetId(): int
    {
        return $this->style_id;
    }

    public function setStyleSheetId(int $a_style_id): void
    {
        $this->style_id = $a_style_id;
    }

    public function setNewsTimeline(bool $a_val): void
    {
        $this->news_timeline = $a_val;
    }

    public function getNewsTimeline(): bool
    {
        return $this->news_timeline;
    }

    public function setNewsTimelineAutoEntries(bool $a_val): void
    {
        $this->news_timeline_auto_entries = $a_val;
    }

    public function getNewsTimelineAutoEntries(): bool
    {
        return $this->news_timeline_auto_entries;
    }

    public function setNewsTimelineLandingPage(bool $a_val): void
    {
        $this->news_timeline_landing_page = $a_val;
    }

    public function getNewsTimelineLandingPage(): bool
    {
        return $this->news_timeline_landing_page;
    }

    public function isNewsTimelineEffective(): bool
    {
        if (!$this->news->isGloballyActivated()) {
            return false;
        }
        if ($this->getUseNews() && $this->getNewsTimeline()) {
            return true;
        }
        return false;
    }

    public function isNewsTimelineLandingPageEffective(): bool
    {
        if ($this->getUseNews() && $this->getNewsTimeline() && $this->getNewsTimelineLandingPage()) {
            return true;
        }
        return false;
    }


    public function setNewsBlockActivated(bool $a_val): void
    {
        $this->news_block_activated = $a_val;
    }

    public function getNewsBlockActivated(): bool
    {
        return $this->news_block_activated;
    }

    public function setUseNews(bool $a_val): void
    {
        $this->use_news = $a_val;
    }

    public function getUseNews(): bool
    {
        return $this->use_news;
    }

    public static function _lookupContainerSetting(
        int $a_id,
        string $a_keyword,
        string $a_default_value = null
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT value FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer') . " AND " .
                " keyword = " . $ilDB->quote($a_keyword, 'text');
        $set = $ilDB->query($q);
        $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        if (isset($rec['value'])) {
            return $rec["value"];
        }

        return $a_default_value ?? '';
    }

    public static function _hasContainerSetting(
        int $a_id,
        string $a_keyword
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT value FROM container_settings WHERE " .
            " id = " . $ilDB->quote($a_id, 'integer') . " AND " .
            " keyword = " . $ilDB->quote($a_keyword, 'text');
        $set = $ilDB->query($q);
        $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (bool) $rec;
    }

    public static function _writeContainerSetting(
        int $a_id,
        string $a_keyword,
        string $a_value
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM container_settings WHERE " .
            "id = " . $ilDB->quote($a_id, 'integer') . " " .
            "AND keyword = " . $ilDB->quote($a_keyword, 'text');
        $ilDB->manipulate($query);

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Write container setting, id: " . $a_id . ", keyword: " . $a_keyword . ", value: " . $a_value);

        $query = "INSERT INTO container_settings (id, keyword, value) VALUES (" .
            $ilDB->quote($a_id, 'integer') . ", " .
            $ilDB->quote($a_keyword, 'text') . ", " .
            $ilDB->quote($a_value, 'text') .
            ")";

        $ilDB->manipulate($query);
    }

    /**
     * @param int $a_id
     * @return array<string, string>
     */
    public static function _getContainerSettings(int $a_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];

        $sql = "SELECT keyword, value FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer');
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["keyword"]] = $row["value"];
        }

        return $res;
    }

    public static function _deleteContainerSettings(
        int $a_id,
        string $a_keyword = "",
        bool $a_keyword_like = false
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$a_id) {
            return;
        }

        $sql = "DELETE FROM container_settings WHERE " .
                " id = " . $ilDB->quote($a_id, 'integer');
        if ($a_keyword !== "") {
            if (!$a_keyword_like) {
                $sql .= " AND keyword = " . $ilDB->quote($a_keyword, "text");
            } else {
                $sql .= " AND " . $ilDB->like("keyword", "text", $a_keyword);
            }
        }
        $ilDB->manipulate($sql);
    }

    public static function _exportContainerSettings(
        ilXmlWriter $a_xml,
        int $a_obj_id
    ): void {
        // container settings
        $settings = self::_getContainerSettings($a_obj_id);
        if (count($settings)) {
            $a_xml->xmlStartTag("ContainerSettings");

            foreach ($settings as $keyword => $value) {
                // :TODO: proper custom icon export/import
                if (
                    stripos($keyword, "icon") !== false
                    && $keyword !== 'hide_header_icon_and_title'
                ) {
                    continue;
                }

                $a_xml->xmlStartTag(
                    'ContainerSetting',
                    [
                        'id' => $keyword,
                    ]
                );

                $a_xml->xmlData((string) $value);
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
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        /** @var ilObjCourse $new_obj */
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        $log = ilLoggerFactory::getLogger("cont");

        // translations
        $ot = ilObjectTranslation::getInstance($this->getId());
        $ot->setDefaultTitle($new_obj->getTitle());     // get possible "- COPY" extension
        $ot->copy($new_obj->getId());
        $ot2 = ilObjectTranslation::getInstance($new_obj->getId());
        $ot2->read();
        $new_obj->setObjectTranslation($ot2);
        if ($ot2->getDefaultDescription() !== "") {
            $new_obj->setDescription($ot2->getDefaultDescription());
        }
        $log->debug("**1**" . count($new_obj->getObjectTranslation()->getLanguages()));
        $log->debug("ilContainer: cloning translations from " . $this->getId() . " to " .
            $new_obj->getId());

        #18624 - copy all sorting settings
        ilContainerSortingSettings::_cloneSettings($this->getId(), $new_obj->getId());

        // copy content page
        if (ilContainerPage::_exists(
            "cont",
            $this->getId()
        )) {
            $orig_page = new ilContainerPage($this->getId());
            $orig_page->copy($new_obj->getId(), "cont", $new_obj->getId());
        }

        // #20614 - copy style
        $this->content_style_domain->styleForRefId($this->getRefId())->cloneTo($new_obj->getId());

        // #10271 - copy start objects page
        if (ilContainerStartObjectsPage::_exists(
            "cstr",
            $this->getId()
        )) {
            $orig_page = new ilContainerStartObjectsPage($this->getId());
            $orig_page->copy($new_obj->getId(), "cstr", $new_obj->getId());
        }

        // #10271
        foreach (self::_getContainerSettings($this->getId()) as $keyword => $value) {
            self::_writeContainerSetting($new_obj->getId(), (string) $keyword, (string) $value);
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
    public function cloneDependencies(int $target_id, int $copy_id): bool
    {
        $ilLog = $this->log;

        parent::cloneDependencies($target_id, $copy_id);

        ilContainerSorting::_getInstance($this->getId())->cloneSorting($target_id, $copy_id);

        // fix internal links to other objects
        self::fixInternalLinksAfterCopy($target_id, $copy_id, $this->getRefId());

        // fix item group references in page content
        ilObjItemGroup::fixContainerItemGroupRefsAfterCloning($this, $copy_id);

        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $collection->cloneCollection($target_id, $copy_id);
        }

        return true;
    }

    /**
     * @param string $session_id
     * @param string $client_id
     * @param string $new_type
     * @param int    $ref_id
     * @param int    $clone_source
     * @param array  $options
     * @param bool   $soap_call force soap
     * @param int    $a_submode submode 1 => copy all, 2 => copy content
     * @return array
     */
    public function cloneAllObject(
        string $session_id,
        string $client_id,
        string $new_type,
        int $ref_id,
        int $clone_source,
        array $options,
        bool $soap_call = false,
        int $a_submode = 1
    ): array {
        $ilLog = $this->log;
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

        if ($a_submode === ilObjectCopyGUI::SUBMODE_CONTENT_ONLY) {
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
        $soap_client = new ilSoapClient();
        $soap_client->setResponseTimeout($soap_client->getResponseTimeout());
        $soap_client->enableWSDL(true);

        $ilLog->write(__METHOD__ . ': Trying to call Soap client...');
        if ($soap_client->init()) {
            ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
            $res = $soap_client->call('ilClone', [$new_session_id . '::' . $client_id, $copy_id]);
        } else {
            ilLoggerFactory::getLogger('obj')->warning('SOAP clone call failed. Calling clone method manually');
            $wizard_options->disableSOAP();
            $wizard_options->read();
            $res = ilSoapFunctions::ilClone($new_session_id . '::' . $client_id, $copy_id);
        }
        return [
                'copy_id' => $copy_id,
                'ref_id' => (int) $res
        ];
    }

    /**
     * delete category and all related data
     *
     * @return    bool    true if all object data were removed; false if only a references were removed
     */
    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // delete translations
        $this->obj_trans->delete();

        return true;
    }

    public function getViewMode(): int
    {
        return self::VIEW_BY_TYPE;
    }

    public function getOrderType(): int
    {
        return $this->order_type ?: self::SORT_TITLE;
    }

    public function setOrderType(int $a_value): void
    {
        $this->order_type = $a_value;
    }

    public function isClassificationFilterActive(): bool
    {
        // apply container classification filters
        $classification = $this->domain->classification($this->getRefId());
        foreach (ilClassificationProvider::getValidProviders($this->getRefId(), $this->getId(), $this->getType()) as $class_provider) {
            $id = get_class($class_provider);
            $current = $classification->getSelectionOfProvider($id);
            if ($current) {
                return true;
            }
        }
        return false;
    }

    /**
     * Note grp/crs currently allow to filter in their whole subtrees
     * Catetories only their direct childs
     */
    public function filteredSubtree(): bool
    {
        if ($this->isClassificationFilterActive() && in_array($this->getType(), ["grp", "crs"])) {
            return true;
        }
        return false;
    }

    /**
     * @deprecated
     */
    protected function getInitialSubitems(): array
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
     * @deprecated
     */
    public function getSubItems(
        bool $a_admin_panel_enabled = false,
        bool $a_include_side_block = false,
        int $a_get_single = 0,
        ilContainerUserFilter $container_user_filter = null
    ): array {
        $objDefinition = $this->obj_definition;

        // Caching
        if (
            isset($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]) &&
            is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]) &&
            !$a_get_single
        ) {
            return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
        }

        $objects = $this->getInitialSubitems();
        $objects = $this->applyContainerUserFilter($objects, $container_user_filter);
        $objects = self::getCompleteDescriptions($objects);

        // apply container classification filters
        $classification = $this->domain->classification($this->getRefId());
        foreach (ilClassificationProvider::getValidProviders($this->getRefId(), $this->getId(), $this->getType()) as $class_provider) {
            $id = get_class($class_provider);
            $current = $classification->getSelectionOfProvider($id);
            if ($current) {
                $class_provider->setSelection($current);
                $filtered = $class_provider->getFilteredObjects();
                $objects = array_filter($objects, static function (array $i) use ($filtered): bool {
                    return (is_array($filtered) && in_array($i["obj_id"], $filtered));
                });
            }
        }

        $found = false;
        $all_ref_ids = [];

        $preloader = null;
        if (!self::$data_preloaded) {
            $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
        }

        $sort = ilContainerSorting::_getInstance($this->getId());

        // TODO: check this
        // get items attached to a session

        $classification_filter_active = $this->isClassificationFilterActive();
        foreach ($objects as $key => $object) {
            // see #41377, this ensures session materials to be preloaded
            if (!self::$data_preloaded) {
                if ($object["type"] === "sess") {
                    $ev_items = ilObjectActivation::getItemsByEvent((int) $object["obj_id"]);
                    foreach ($ev_items as $ev_item) {
                        $preloader->addItem((int) $ev_item["obj_id"], $ev_item["type"], $ev_item["ref_id"]);
                    }
                }
            }
            if ($a_get_single > 0 && $object["child"] != $a_get_single) {
                continue;
            }

            // hide object types in devmode
            if ($object["type"] === "adm" || $object["type"] === "rolf" || $objDefinition->getDevMode($object["type"])) {
                continue;
            }

            // remove inactive plugins
            if ($objDefinition->isInactivePlugin($object["type"])) {
                continue;
            }
            // BEGIN WebDAV: Don't display hidden Files, Folders and Categories
            if (in_array(
                $object['type'],
                ['file', 'fold', 'cat'],
                true
            ) && ilObjFileAccess::_isFileHidden((string) $object['title'])) {
                $this->setHiddenFilesFound(true);
                if (!$a_admin_panel_enabled) {
                    continue;
                }
            }
            // END WebDAV: Don't display hidden Files, Folders and Categories

            if (!self::$data_preloaded) {
                $preloader->addItem((int) $object["obj_id"], $object["type"], $object["child"]);
            }

            // filter side block items
            if (!$a_include_side_block && $objDefinition->isSideBlock($object['type'])) {
                continue;
            }

            $all_ref_ids[] = (int) $object["child"];
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
            if ($object["type"] !== "sess") {
                $this->items["_non_sess"][$key] = $object;
            }
        }
        $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]
            = $sort->sortItems($this->items);

        return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
    }

    /**
     * @deprecated
     */
    public function gotItems(): bool
    {
        if (isset($this->items["_all"]) && is_array($this->items["_all"]) && count($this->items["_all"]) > 0) {
            return true;
        }
        return false;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(array &$object): void
    {
    }

    // Get grouped repository object types.
    public function getGroupedObjTypes(): array
    {
        $objDefinition = $this->obj_definition;

        if (empty($this->type_grps)) {
            $this->type_grps = $objDefinition::getGroupedRepositoryObjectTypes($this->getType());
        }
        return $this->type_grps;
    }

    public function enablePageEditing(): bool
    {
        $ilSetting = $this->setting;

        // @todo: this will need a more general approach
        if ($ilSetting->get("enable_cat_page_edit")) {
            return true;
        }
        return false;
    }

    public function create(): int
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
            '1'
        );

        if (($this->getStyleSheetId()) > 0) {
            //ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());
        }

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Create Container, id: " . $this->getId());

        self::_writeContainerSetting($this->getId(), "news_timeline", (string) ((int) $this->getNewsTimeline()));
        self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (string) ((int) $this->getNewsTimelineAutoEntries()));
        self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (string) ((int) $this->getNewsTimelineLandingPage()));
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::NEWS_VISIBILITY, (string) ((int) $this->getNewsBlockActivated()));
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, (string) ((int) $this->getUseNews()));

        return $ret;
    }

    public function putInTree(int $parent_ref_id): void
    {
        parent::putInTree($parent_ref_id);

        // copy title, icon actions visibilities
        if (self::_lookupContainerSetting(ilObject::_lookupObjId($parent_ref_id), "hide_header_icon_and_title")) {
            self::_writeContainerSetting($this->getId(), "hide_header_icon_and_title", '1');
        }
        if (self::_lookupContainerSetting(ilObject::_lookupObjId($parent_ref_id), "hide_top_actions")) {
            self::_writeContainerSetting($this->getId(), "hide_top_actions", '1');
        }
    }

    public function update(): bool
    {
        $ret = parent::update();

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("**5**" . count($this->getObjectTranslation()->getLanguages()));

        $trans = $this->getObjectTranslation();
        $trans->setDefaultTitle($this->getTitle());
        $trans->setDefaultDescription($this->getLongDescription());
        $trans->save();

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug(":::::::::::::::::::::::::::");
        $log->logStack(10);

        //ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());

        $log = ilLoggerFactory::getLogger("cont");
        $log->debug("Update Container, id: " . $this->getId());

        self::_writeContainerSetting($this->getId(), "news_timeline", (string) ((int) $this->getNewsTimeline()));
        self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (string) (int) $this->getNewsTimelineAutoEntries());
        self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (string) ((int) ($this->getNewsTimelineLandingPage())));
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::NEWS_VISIBILITY, (string) ((int) $this->getNewsBlockActivated()));
        self::_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, (string) ((int) $this->getUseNews()));

        return $ret;
    }

    public function read(): void
    {
        parent::read();

        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));

        //$this->setStyleSheetId(ilObjStyleSheet::lookupObjectStyle($this->getId()));

        $this->readContainerSettings();
        $this->obj_trans = ilObjectTranslation::getInstance($this->getId());
    }

    public function readContainerSettings(): void
    {
        $this->setNewsTimeline((bool) self::_lookupContainerSetting($this->getId(), "news_timeline"));
        $this->setNewsTimelineAutoEntries((bool) self::_lookupContainerSetting($this->getId(), "news_timeline_incl_auto"));
        $this->setNewsTimelineLandingPage((bool) self::_lookupContainerSetting($this->getId(), "news_timeline_landing_page"));
        $this->setNewsBlockActivated((bool) self::_lookupContainerSetting(
            $this->getId(),
            ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
            $this->setting->get('block_activated_news', '1')
        ));
        $this->setUseNews((bool) self::_lookupContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, '1') &&
        $this->news->isGloballyActivated());
    }


    /**
     * overwrites description fields to long or short description in an assoc array
     * keys needed (obj_id and description)
     */
    public static function getCompleteDescriptions(array $objects): array
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $ilObjDataCache = $DIC["ilObjDataCache"];

        // using long descriptions?
        $short_desc = $ilSetting->get("rep_shorten_description");
        $short_desc_max_length = $ilSetting->get("rep_shorten_description_length");
        if (!$short_desc || (int) $short_desc_max_length !== ilObject::DESC_LENGTH) {
            // using (part of) shortened description
            if ($short_desc && $short_desc_max_length && $short_desc_max_length < ilObject::DESC_LENGTH) {
                foreach ($objects as $key => $object) {
                    $objects[$key]["description"] = ilStr::shortenTextExtended(
                        $object["description"],
                        (int) $short_desc_max_length,
                        true
                    );
                }
            }
            // using (part of) long description
            else {
                $obj_ids = [];
                foreach ($objects as $key => $object) {
                    $obj_ids[] = $object["obj_id"];
                }
                if (count($obj_ids)) {
                    $long_desc = ilObject::getLongDescriptions($obj_ids);
                    foreach ($objects as $key => $object) {
                        // #12166 - keep translation, ignore long description
                        if ($ilObjDataCache->isTranslatedDescription((int) $object["obj_id"])) {
                            $long_desc[$object["obj_id"]] = $object["description"];
                        }
                        if ($short_desc && $short_desc_max_length) {
                            $long_desc[$object["obj_id"]] = ilStr::shortenTextExtended(
                                $long_desc[$object["obj_id"]] ?? '',
                                (int) $short_desc_max_length,
                                true
                            );
                        }
                        $objects[$key]["description"] = $long_desc[$object["obj_id"]] ?? '';
                    }
                }
            }
        }
        return $objects;
    }

    protected static function fixInternalLinksAfterCopy(
        int $a_target_id,
        int $a_copy_id,
        int $a_source_ref_id
    ): void {
        global $DIC;

        /** @var ilObjectDefinition $obj_definition */
        $obj_definition = $DIC["objDefinition"];

        $obj_id = ilObject::_lookupObjId($a_target_id);
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

    // Remove all translations of container
    public function removeTranslations(): void
    {
        $this->obj_trans->delete();
    }

    public function deleteTranslation(string $a_lang): void
    {
        $this->obj_trans->removeLanguage($a_lang);
        $this->obj_trans->save();
    }

    public function addTranslation(
        string $a_title,
        string $a_desc,
        string $a_lang,
        string $a_lang_default
    ): void {
        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $this->obj_trans->addLanguage($a_lang, $a_title, $a_desc, (bool) $a_lang_default, true);
        $this->obj_trans->save();
    }

    protected function applyContainerUserFilter(
        array $objects,
        ilContainerUserFilter $container_user_filter = null
    ): array {
        $filter = $this->domain->content()->filter(
            $objects,
            $container_user_filter,
            !self::_lookupContainerSetting($this->getId(), "filter_show_empty", false)
        );
        return $filter->apply();
    }
}
