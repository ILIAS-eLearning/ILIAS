<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;

/**
 * @defgroup ServicesComponent Services/Component
 *
 * @author   Alex Killing <alex.killing@gmx.de>
 * @author   Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup  ServicesComponent
 */
abstract class ilPlugin
{

    /**
     * @var ilPluginSlot
     */
    protected $slot;
    /**
     * @var bool
     */
    protected $active = false;
    /**
     * @var string
     */
    protected $iliasmaxversion = "";
    /**
     * @var string
     */
    protected $iliasminversion = "";
    /**
     * @var string
     */
    protected $version = "";
    /**
     * @var string
     */
    protected $lastupdateversion = "";
    /**
     * @var int
     */
    protected $dbversion = 0;
    /**
     * @var bool
     */
    protected $lang_initialised = false;
    /**
     * @var string
     */
    protected $id = '';


    public function __construct()
    {
        $this->__init();
    }


    /**
     * Get Component Type
     *
     * Must be overwritten in plugin class of plugin slot.
     *
     * @return    string    Component Type
     */
    abstract public function getComponentType();


    /**
     * Get Component Name.
     *
     * Must be overwritten in plugin class of plugin slot.
     *
     * @return    string    Component Name
     */
    abstract public function getComponentName();


    /**
     * Get Slot Name.
     *
     * Must be overwritten in plugin class of plugin slot.
     *
     * @return    string    Slot Name
     */
    abstract public function getSlot();


    /**
     * Get Slot ID.
     *
     * Must be overwritten in plugin class of plugin slot.
     *
     * @return    string    Slot Id
     */
    abstract public function getSlotId();


    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * Must be overwritten in plugin class of plugin
     *
     * @return    string    Plugin Name
     */
    abstract public function getPluginName();


    /**
     * Set Id.
     *
     * @param    string $a_id Id
     */
    private function setId($a_id)
    {
        $this->id = $a_id;
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }


    /**
     * Set Version of last update.
     *
     * @param    string $a_lastupdateversion Version of last update
     */
    private function setLastUpdateVersion(string $a_lastupdateversion)
    {
        $this->lastupdateversion = $a_lastupdateversion;
    }


    /**
     * Get Version of last update.
     *
     * @return    string    Version of last update
     */
    public function getLastUpdateVersion() : string
    {
        return $this->lastupdateversion;
    }


    /**
     * @param string $a_version
     */
    private function setVersion(string $a_version)
    {
        $this->version = $a_version;
    }


    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }


    /**
     * @param $a_iliasminversion
     */
    private function setIliasMinVersion(string $a_iliasminversion)
    {
        $this->iliasminversion = $a_iliasminversion;
    }


    /**
     * @return string
     */
    public function getIliasMinVersion() : string
    {
        return $this->iliasminversion;
    }


    /**
     * @param $a_iliasmaxversion
     */
    private function setIliasMaxVersion(string $a_iliasmaxversion)
    {
        $this->iliasmaxversion = $a_iliasmaxversion;
    }


    /**
     * Get Required ILIAS max. release.
     *
     * @return    string    Required ILIAS max. release
     */
    public function getIliasMaxVersion()
    {
        return $this->iliasmaxversion;
    }


    /**
     * @param bool $a_active
     */
    private function setActive(bool $a_active)
    {
        $this->active = $a_active;
    }


    /**
     * @return bool
     */
    public function getActive() : bool
    {
        return $this->active;
    }


    /**
     * @param ilPluginSlot $a_slot
     */
    protected function setSlotObject(ilPluginSlot $a_slot)
    {
        $this->slot = $a_slot;
    }


    /**
     * @return ilPluginSlot
     */
    protected function getSlotObject() : ilPluginSlot
    {
        return $this->slot;
    }


    /**
     * @param int $a_dbversion
     */
    public function setDBVersion(int $a_dbversion)
    {
        $this->dbversion = $a_dbversion;
    }


    /**
     * @return int
     */
    public function getDBVersion() : int
    {
        return $this->dbversion;
    }


    /**
     * @param string $a_dbversion
     */
    public function writeDBVersion(int $a_dbversion)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->setDBVersion($a_dbversion);

        $q = "UPDATE il_plugin SET db_version = " . $ilDB->quote((int) $this->getDBVersion(), "integer") .
            " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
            " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
            " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
            " AND name = " . $ilDB->quote($this->getPluginName(), "text");

        $ilDB->manipulate($q);
    }


    /**
     * Get Plugin Directory
     *
     * @return    object    Plugin Slot
     */
    public function getDirectory() : string
    {
        return $this->getSlotObject()->getPluginsDirectory() . "/" . $this->getPluginName();
    }


    /**
     * Get plugin directory
     */
    public static function _getDirectory(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname) : string
    {
        return ilPluginSlot::_getPluginsDirectory($a_ctype, $a_cname, $a_slot_id) . "/" . $a_pname;
    }


    /**
     * @return string
     */
    protected function getClassesDirectory() : string
    {
        return $this->getDirectory() . "/classes";
    }


    /**
     * Include (once) a class file
     */
    public function includeClass($a_class_file_name)
    {
        include_once($this->getClassesDirectory() . "/" . $a_class_file_name);
    }


    /**
     * @return string
     */
    protected function getLanguageDirectory() : string
    {
        return $this->getDirectory() . "/lang";
    }


    /**
     * Get array of all language files in the plugin
     */
    public static function getAvailableLangFiles(string $a_lang_directory) : array
    {
        $langs = array();

        if (!@is_dir($a_lang_directory)) {
            return array();
        }

        $dir = opendir($a_lang_directory);
        while ($file = readdir($dir)) {
            if ($file != "." and
                $file != ".."
            ) {
                // directories
                if (@is_file($a_lang_directory . "/" . $file)) {
                    if (substr($file, 0, 6) == "ilias_"
                        && substr($file, strlen($file) - 5) == ".lang"
                    ) {
                        $langs[] = array("key"  => substr($file, 6, 2), "file" => $file,
                                         "path" => $a_lang_directory . "/" . $file);
                    }
                }
            }
        }

        return $langs;
    }


    /**
     * Has the plugin a configure class?
     *
     * @param string $a_slot_dir     slot directory
     * @param array  $plugin_data    plugin data
     * @param array  $plugin_db_data plugin db data
     *
     * @return boolean true/false
     */
    public static function hasConfigureClass(string $a_slot_dir, array $plugin_data, array $plugin_db_data) : bool
    {
        // Mantis: 23282: Disable plugin config page for incompatible plugins
        if (!(ilComponent::isVersionGreaterString($plugin_data["ilias_min_version"], ILIAS_VERSION_NUMERIC)
            || ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, $plugin_data["ilias_max_version"])
            || ilComponent::isVersionGreaterString($plugin_db_data["last_update_version"], $plugin_data["version"]))
        ) {
            if (is_file($a_slot_dir . "/" . $plugin_data["name"] . "/classes/class.il" . $plugin_data["name"] . "ConfigGUI.php")) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get plugin configure class name
     *
     * @param array $plugin_data
     *
     * @return string
     */
    public static function getConfigureClassName(array $plugin_data) : string
    {
        return "il" . $plugin_data["name"] . "ConfigGUI";
    }


    /**
     * Get plugin prefix, used for lang vars
     */
    public function getPrefix() : string
    {
        return $this->getSlotObject()->getPrefix() . "_" . $this->getId();
    }


    /**
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_name
     * @param string $a_pname
     *
     * @return string
     */
    public static function getDBUpdateScriptName(string $a_ctype, string $a_cname, string $a_slot_name, string $a_pname) : string
    {
        return "Customizing/global/plugins/" . $a_ctype . "/" . $a_cname . "/" .
            $a_slot_name . "/" . $a_pname . "/sql/dbupdate.php";
    }


    /**
     * Get db table plugin prefix
     */
    public function getTablePrefix()
    {
        return $this->getPrefix();
    }


    /**
     * Update all or selected languages
     *
     * @var array|null $a_lang_keys keys of languages to be updated (null for all)
     */
    public function updateLanguages($a_lang_keys = null)
    {
        ilGlobalCache::flushAll();

        // get the keys of all installed languages if keys are not provided
        if (!isset($a_lang_keys)) {
            $a_lang_keys = array();
            foreach (ilObjLanguage::getInstalledLanguages() as $langObj) {
                if ($langObj->isInstalled()) {
                    $a_lang_keys[] = $langObj->getKey();
                }
            }
        }

        $langs = $this->getAvailableLangFiles($this->getLanguageDirectory());

        $prefix = $this->getPrefix();

        foreach ($langs as $lang) {
            // check if the language should be updated, otherwise skip it
            if (!in_array($lang['key'], $a_lang_keys)) {
                continue;
            }

            $txt = file($this->getLanguageDirectory() . "/" . $lang["file"]);
            $lang_array = array();

            // get locally changed variables of the module (these should be kept)
            $local_changes = ilObjLanguage::_getLocalChangesByModule($lang['key'], $prefix);

            // get language data
            if (is_array($txt)) {
                foreach ($txt as $row) {
                    if ($row[0] != "#" && strpos($row, "#:#") > 0) {
                        $a = explode("#:#", trim($row));
                        $identifier = $prefix . "_" . trim($a[0]);
                        $value = trim($a[1]);

                        if (isset($local_changes[$identifier])) {
                            $lang_array[$identifier] = $local_changes[$identifier];
                        } else {
                            $lang_array[$identifier] = $value;
                            ilObjLanguage::replaceLangEntry($prefix, $identifier, $lang["key"], $value);
                        }
                        //echo "<br>-$prefix-".$prefix."_".trim($a[0])."-".$lang["key"]."-";
                    }
                }
            }

            ilObjLanguage::replaceLangModule($lang["key"], $prefix, $lang_array);
        }
    }


    /**
     * Update database
     */
    public function updateDatabase()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        ilGlobalCache::flushAll();

        $dbupdate = new ilPluginDBUpdate(
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName(),
            $ilDB,
            true,
            $this->getTablePrefix()
        );

        $result = $dbupdate->applyUpdate();
        $message = '';
        if ($dbupdate->updateMsg == "no_changes") {
            $message = $lng->txt("no_changes") . ". " . $lng->txt("database_is_uptodate");
        } else {
            foreach ($dbupdate->updateMsg as $row) {
                $message .= $lng->txt($row["msg"]) . ": " . $row["nr"] . "<br/>";
            }
        }

        $this->message .= $message;
        ilGlobalCache::flushAll();

        return $result;
    }


    /**
     * Load language module for plugin
     */
    public function loadLanguageModule()
    {
        global $DIC;
        $lng = $DIC->language();

        if (!$this->lang_initialised && is_object($lng)) {
            $lng->loadLanguageModule($this->getPrefix());
            $this->lang_initialised = true;
        }
    }


    /**
     * Get Language Variable (prefix will be prepended automatically)
     */
    public function txt(string $a_var) : string
    {
        global $DIC;
        $lng = $DIC->language();
        $this->loadLanguageModule();

        return $lng->txt($this->getPrefix() . "_" . $a_var, $this->getPrefix());
    }


    /**
     * @param string $a_mod_prefix
     * @param string $a_pl_id
     * @param string $a_lang_var
     *
     * @return string
     */
    public static function lookupTxt(string $a_mod_prefix, string $a_pl_id, string $a_lang_var) : string
    {
        global $DIC;
        $lng = $DIC->language();

        // this enables default language fallback
        $prefix = $a_mod_prefix . "_" . $a_pl_id;

        return $lng->txt($prefix . "_" . $a_lang_var, $prefix);
    }


    /**
     * Is searched lang var available in plugin lang files
     *
     * @param string $pluginId
     * @param string $langVar
     *
     * @return bool
     */
    public static function langExitsById(string $pluginId, string $langVar) : bool
    {
        global $DIC;
        $lng = $DIC->language();

        $pl = ilObjectPlugin::getPluginObjectByType($pluginId);
        $pl->loadLanguageModule();

        return $lng->exists($pl->getPrefix() . "_" . $langVar);
    }


    /**
     * gets a ilTemplate instance of a html-file in the plugin /templates
     *
     * @param string $a_template
     * @param bool   $a_par1
     * @param bool   $a_par2
     *
     * @return ilTemplate
     */
    public function getTemplate(string $a_template, bool $a_par1 = true, bool $a_par2 = true) : ilTemplate
    {
        return new ilTemplate($this->getDirectory() . "/templates/" . $a_template, $a_par1, $a_par2);
    }


    /**
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     * @param string $a_pname
     * @param string $a_img
     *
     * @return string
     */
    public static function _getImagePath(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname, string $a_img) : string
    {
        $d2 = ilComponent::lookupId($a_ctype, $a_cname) . "_" . $a_slot_id . "_" .
            ilPlugin::lookupIdForName($a_ctype, $a_cname, $a_slot_id, $a_pname);

        $img = ilUtil::getImagePath($d2 . "/" . $a_img);
        if (is_int(strpos($img, "Customizing"))) {
            return $img;
        }

        $d = ilPlugin::_getDirectory($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $d . "/templates/images/" . $a_img;
    }


    /**
     * Get image path
     */
    public function getImagePath(string $a_img) : string
    {
        return self::_getImagePath(
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName(),
            $a_img
        );
    }


    /**
     * @param string $a_css_file
     *
     * @return string
     */
    public function getStyleSheetLocation(string $a_css_file) : string
    {
        $d2 = ilComponent::lookupId($this->getComponentType(), $this->getComponentName()) . "_" . $this->getSlotId() . "_" .
            ilPlugin::lookupIdForName($this->getComponentType(), $this->getComponentName(), $this->getSlotId(), $this->getPluginName());

        $css = ilUtil::getStyleSheetLocation("output", $a_css_file, $d2);
        if (is_int(strpos($css, "Customizing"))) {
            return $css;
        }

        return $this->getDirectory() . "/templates/" . $a_css_file;
    }


    /**
     * Add template content to placeholder variable
     */
    public function addBlockFile($a_tpl, $a_var, $a_block, $a_tplname)
    {
        $a_tpl->addBlockFile(
            $a_var,
            $a_block,
            $this->getDirectory() . "/templates/" . $a_tplname
        );
    }


    /**
     * @param $a_ctype
     * @param $a_cname
     * @param $a_slot_id
     * @param $a_pname
     *
     * @description Create plugin record
     */
    public static function createPluginRecord(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname)
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilCachedComponentData::flush();

        $q = "INSERT INTO il_plugin (component_type, component_name, slot_id, name)" .
            " VALUES (" . $ilDB->quote($a_ctype, "text") . "," .
            $ilDB->quote($a_cname, "text") . "," .
            $ilDB->quote($a_slot_id, "text") . "," .
            $ilDB->quote($a_pname, "text") . ")";

        $ilDB->manipulate($q);
    }


    /**
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     * @param string $a_pname
     *
     * @return array
     * @throws ilPluginException
     */
    public static function getPluginRecord(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname) : array
    {
        $cached_component = ilCachedComponentData::getInstance();
        $rec = $cached_component->lookupPluginByName($a_pname);

        if ($rec['component_type'] == $a_ctype and $rec['component_name'] == $a_cname and $rec['slot_id'] == $a_slot_id) {
            return $rec;
        } else {
            throw new ilPluginException("No plugin record found for '{$a_ctype}', '{$a_cname}', '{$a_slot_id}', '{$a_pname}");
        }
    }


    /**
     * Default initialization
     */
    private function __init()
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        // read/set basic data
        $rec = ilPlugin::getPluginRecord(
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName()
        );
        $this->setLastUpdateVersion((string) $rec["last_update_version"]);
        $this->setDBVersion((int) $rec["db_version"]);
        $this->setActive((bool) $rec["active"]);

        // get id
        $this->setId(
            $ilPluginAdmin->getId(
                $this->getComponentType(),
                $this->getComponentName(),
                $this->getSlotId(),
                $this->getPluginName()
            )
        );

        // get version
        $this->setVersion(
            $ilPluginAdmin->getVersion(
                $this->getComponentType(),
                $this->getComponentName(),
                $this->getSlotId(),
                $this->getPluginName()
            )
        );

        // get ilias min version
        $this->setIliasMinVersion(
            $ilPluginAdmin->getIliasMinVersion(
                $this->getComponentType(),
                $this->getComponentName(),
                $this->getSlotId(),
                $this->getPluginName()
            )
        );

        // get ilias max version
        $this->setIliasMaxVersion(
            $ilPluginAdmin->getIliasMaxVersion(
                $this->getComponentType(),
                $this->getComponentName(),
                $this->getSlotId(),
                $this->getPluginName()
            )
        );

        // get slot object
        $this->setSlotObject(
            new ilPluginSlot(
                $this->getComponentType(),
                $this->getComponentName(),
                $this->getSlotId()
            )
        );

        // load language module

        // Fix for authentication plugins
        $this->loadLanguageModule();

        // call slot and plugin init methods
        $this->slotInit();
        $this->init();
    }


    /**
     * Object initialization done by slot.
     * Must be overwritten in plugin class of plugin slot.
     *
     * (and should be made protected)
     */
    abstract protected function slotInit();


    /**
     * Object initialization. Can be overwritten by plugin class
     * (and should be made protected)
     */
    protected function init()
    {
    }


    /**
     * Check whether plugin is active
     */
    public function isActive()
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        return $ilPluginAdmin->isActive(
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName()
        );
    }


    /**
     * Check whether update is needed.
     */
    public function needsUpdate()
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        return $ilPluginAdmin->needsUpdate(
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName()
        );
    }


    public function install()
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilCachedComponentData::flush();
        $q = "UPDATE il_plugin SET plugin_id = " . $ilDB->quote($this->getId(), "text") .
            " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
            " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
            " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
            " AND name = " . $ilDB->quote($this->getPluginName(), "text");

        $ilDB->manipulate($q);
        $this->afterInstall();
    }


    /**
     * Activate
     */
    public function activate()
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilCachedComponentData::flush();

        $result = true;

        // check whether update is necessary
        if ($this->needsUpdate()) {
            //$result = $this->isUpdatePossible();

            // do update
            if ($result === true) {
                $result = $this->update();
            }
        }
        if ($result === true) {
            $result = $this->beforeActivation();
            // activate plugin
            if ($result === true) {
                $q = "UPDATE il_plugin SET active = " . $ilDB->quote(1, "integer") .
                    " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
                    " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
                    " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
                    " AND name = " . $ilDB->quote($this->getPluginName(), "text");

                $ilDB->manipulate($q);
                $this->afterActivation();
            }
        }
        ilCachedComponentData::flush();

        return $result;
    }


    /**
     * After install processing
     *
     * @return void
     */
    protected function afterInstall()
    {
    }


    /**
     * Before activation processing
     */
    protected function beforeActivation()
    {
        return true;    // false would indicate that anything went wrong
        // activation would not proceed
        // throw an exception in this case
        //throw new ilPluginException($lng->txt(""));
    }


    /**
     * After activation processing
     */
    protected function afterActivation()
    {
    }


    /**
     * Deactivate
     */
    public function deactivate()
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilCachedComponentData::flush();

        $result = true;

        $q = "UPDATE il_plugin SET active = " . $ilDB->quote(0, "integer") .
            " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
            " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
            " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
            " AND name = " . $ilDB->quote($this->getPluginName(), "text");

        $ilDB->manipulate($q);
        $this->afterDeactivation();

        return $result;
    }


    /**
     * After deactivation processing
     */
    protected function afterDeactivation()
    {
    }


    protected function beforeUninstall()
    {
        // plugin-specific
        // false would indicate that anything went wrong
        return true;
    }


    final public function uninstall()
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($this->beforeUninstall()) {
            // remove all language entries (see ilObjLanguage)
            // see updateLanguages
            $prefix = $this->getPrefix();
            if ($prefix) {
                $ilDB->manipulate(
                    "DELETE FROM lng_data" .
                    " WHERE module = " . $ilDB->quote($prefix, "text")
                );
                $ilDB->manipulate(
                    "DELETE FROM lng_modules" .
                    " WHERE module = " . $ilDB->quote($prefix, "text")
                );
            }

            $this->clearEventListening();

            // db version is kept in il_plugin - will be deleted, too

            $q = "DELETE FROM il_plugin" .
                " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
                " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
                " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
                " AND name = " . $ilDB->quote($this->getPluginName(), "text");
            $ilDB->manipulate($q);
            
            $ilDB->manipulateF('DELETE FROM ctrl_classfile WHERE comp_prefix=%s', [ ilDBConstants::T_TEXT ], [ $this->getPrefix() ]);
            $ilDB->manipulateF('DELETE FROM ctrl_calls WHERE comp_prefix=%s', [ ilDBConstants::T_TEXT ], [ $this->getPrefix() ]);

            $this->afterUninstall();

            ilCachedComponentData::flush();

            return true;
        }

        return false;
    }


    /**
     * This is Plugin-Specific and is triggered after the uninstall command of a plugin
     */
    protected function afterUninstall()
    {
    }


    /**
     * Update plugin
     */
    public function update()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilCtrl = $DIC->ctrl();

        ilGlobalCache::flushAll();

        $result = $this->beforeUpdate();
        if ($result === false) {
            return false;
        }

        // Load language files
        $this->updateLanguages();

        // DB update
        if ($result === true) {
            $result = $this->updateDatabase();
        }

        // load control structure
        include_once("./setup/classes/class.ilCtrlStructureReader.php");
        $structure_reader = new ilCtrlStructureReader();
        $structure_reader->readStructure(
            true,
            "./" . $this->getDirectory(),
            $this->getPrefix(),
            $this->getDirectory()
        );

        // add config gui to the ctrl calls
        $ilCtrl->insertCtrlCalls(
            "ilobjcomponentsettingsgui",
            ilPlugin::getConfigureClassName(["name" => $this->getPluginName()]),
            $this->getPrefix()
        );

        $this->readEventListening();

        // set last update version to current version
        if ($result === true) {
            $q = "UPDATE il_plugin SET last_update_version = " . $ilDB->quote($this->getVersion(), "text") .
                " WHERE component_type = " . $ilDB->quote($this->getComponentType(), "text") .
                " AND component_name = " . $ilDB->quote($this->getComponentName(), "text") .
                " AND slot_id = " . $ilDB->quote($this->getSlotId(), "text") .
                " AND name = " . $ilDB->quote($this->getPluginName(), "text");

            $ilDB->manipulate($q);
            $this->afterUpdate();
        }
        ilGlobalCache::flushAll();

        return $result;
    }


    /**
     * Read the event listening definitions from the plugin.xml (if file exists)
     */
    protected function readEventListening()
    {
        $reader = new ilPluginReader(
            $this->getDirectory() . '/plugin.xml',
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName()
        );
        $reader->clearEvents();
        $reader->startParsing();
    }


    /**
     * Clear the entries of this plugin in the event handling table
     */
    protected function clearEventListening()
    {
        $reader = new ilPluginReader(
            $this->getDirectory() . '/plugin.xml',
            $this->getComponentType(),
            $this->getComponentName(),
            $this->getSlotId(),
            $this->getPluginName()
        );
        $reader->clearEvents();
    }


    /**
     * Before update processing
     */
    protected function beforeUpdate()
    {
        return true;    // false would indicate that anything went wrong
        // update would not proceed
        // throw an exception in this case
        //throw new ilPluginException($lng->txt(""));
    }


    /**
     * After update processing
     */
    protected function afterUpdate()
    {
    }


    /**
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     * @param string $a_pname
     *
     * @return ilPlugin
     */
    public static function getPluginObject(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname)
    {
        $slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);

        $cached_component = ilCachedComponentData::getInstance();
        $rec = $cached_component->lookCompId($a_ctype, $a_cname);
        if (!$rec) {
            return null;
        }

        $file = "./Customizing/global/plugins/" . $a_ctype . "/" .
            $a_cname . "/" . $slot_name . "/" .
            $a_pname . "/classes/class.il" . $a_pname . "Plugin.php";

        if (is_file($file)) {
            include_once($file);
            $class = "il" . $a_pname . "Plugin";
            $plugin = new $class();

            return $plugin;
        }

        return null;
    }


    /**
     * Lookup information data in il_plugin
     *
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     * @param string $a_pname
     *
     * @return string[]
     */
    public static function lookupStoredData(string $a_ctype, string $a_cname, string $a_slot_id, string $a_pname) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_plugin WHERE" .
            " component_type = " . $ilDB->quote($a_ctype, "text") . " AND" .
            " component_name = " . $ilDB->quote($a_cname, "text") . " AND" .
            " slot_id = " . $ilDB->quote($a_slot_id, "text") . " AND" .
            " name = " . $ilDB->quote($a_pname, "text");

        $set = $ilDB->query($q);

        if ($ilDB->numRows($set) == 0) {
            return array();
        }

        return $ilDB->fetchAssoc($set);
    }


    /**
     * @param string $a_ctype
     * @param string $a_cname
     * @param string $a_slot_id
     *
     * @return array
     */
    public static function getActivePluginsForSlot(string $a_ctype, string $a_cname, string $a_slot_id) : array
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $plugins = array();

        $cached_component = ilCachedComponentData::getInstance();

        $lookupActivePluginsBySlotId = $cached_component->lookupActivePluginsBySlotId($a_slot_id);
        foreach ($lookupActivePluginsBySlotId as $rec) {
            if ($ilPluginAdmin->isActive($a_ctype, $a_cname, $a_slot_id, $rec["name"])) {
                $plugins[] = $rec["name"];
            }
        }

        return $plugins;
    }


    /**
     * Get All active plugin ids for a slot.
     *
     * @param $a_ctype
     * @param $a_cname
     * @param $a_slot_id
     *
     * @return array
     */
    public static function getActivePluginIdsForSlot(string $a_ctype, string $a_cname, string $a_slot_id) : array
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $plugins = array();
        $cached_component = ilCachedComponentData::getInstance();
        $lookupActivePluginsBySlotId = $cached_component->lookupActivePluginsBySlotId($a_slot_id);
        foreach ($lookupActivePluginsBySlotId as $rec) {
            if ($ilPluginAdmin->isActive($a_ctype, $a_cname, $a_slot_id, $rec["name"])) {
                $plugins[] = $rec["plugin_id"];
            }
        }

        return $plugins;
    }


    /**
     * @param $a_ctype
     * @param $a_cname
     * @param $a_slot_id
     * @param $a_plugin_id
     *
     * @return string | null
     */
    public static function lookupNameForId(string $a_ctype, string $a_cname, string $a_slot_id, string $a_plugin_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT name FROM il_plugin " .
            " WHERE component_type = " . $ilDB->quote($a_ctype, "text") .
            " AND component_name = " . $ilDB->quote($a_cname, "text") .
            " AND slot_id = " . $ilDB->quote($a_slot_id, "text") .
            " AND plugin_id = " . $ilDB->quote($a_plugin_id, "text");

        $set = $ilDB->query($q);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["name"];
        }
    }


    /**
     * @param $a_ctype
     * @param $a_cname
     * @param $a_slot_id
     * @param $a_plugin_name
     *
     * @return string
     */
    public static function lookupIdForName(string $a_ctype, string $a_cname, string $a_slot_id, string $a_plugin_name) : string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT plugin_id FROM il_plugin " .
            " WHERE component_type = " . $ilDB->quote($a_ctype, "text") .
            " AND component_name = " . $ilDB->quote($a_cname, "text") .
            " AND slot_id = " . $ilDB->quote($a_slot_id, "text") .
            " AND name = " . $ilDB->quote($a_plugin_name, "text");

        $set = $ilDB->query($q);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["plugin_id"];
        }
    }

    /**
     * @param string $id
     * @return string[] | null
     */
    public static function lookupTypeInformationsForId(string $id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT component_type, component_name, slot_id FROM il_plugin "
            . " WHERE plugin_id = " . $ilDB->quote($id, "text")
        ;

        $set = $ilDB->query($q);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return [
                $rec["component_type"],
                $rec["component_name"],
                $rec["slot_id"]
            ];
        }
    }


    /**
     * @return AbstractStaticPluginMainMenuProvider
     */
    public function promoteGlobalScreenProvider() : AbstractStaticPluginMainMenuProvider
    {
        return new ilPluginGlobalScreenNullProvider();
    }
}
