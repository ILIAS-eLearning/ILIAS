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
 ********************************************************************
 */

use ILIAS\GlobalScreen\Provider\PluginProviderCollection;
use ILIAS\GlobalScreen\Provider\ProviderCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Data\Version;

/**
 * @author   Richard Klees <richard.klees@concepts-and-training.de>
 * @author   Alex Killing <alex.killing@gmx.de>
 * @author   Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilPlugin
{
    protected ilDBInterface $db;
    protected ilComponentRepositoryWrite $component_repository;
    protected string $id;

    /**
     * @var bool
     */
    protected $lang_initialised = false;
    /**
     * @var ProviderCollection
     */
    protected $provider_collection;
    /**
     * @var string
     */
    protected $message;


    public function __construct(
        \ilDBInterface $db,
        \ilComponentRepositoryWrite $component_repository,
        string $id
    )
    {
        if (!$component_repository->hasPluginId($id)) {
            throw new \LogicException(
                "You tried to instantiate a plugin with an inexisting id '$id'." .
                "This is odd... Please use ilComponentFactory to instantiate plugins."
            );
        }

        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->id = $id;

        $this->provider_collection = new PluginProviderCollection();

        // Fix for authentication plugins
        $this->loadLanguageModule();

        // Custom initialisation for plugin.
        $this->init();
    }

    protected function getPluginInfo() : ilPluginInfo
    {
        return $this->component_repository
            ->getPluginById(
                $this->id
            );
    }

    protected function getComponentInfo() : ilComponentInfo
    {
        return $this->getPluginInfo()->getComponent();
    }

    protected function getPluginSlotInfo() : ilPluginSlotInfo
    {
        return $this->getPluginInfo()->getPluginSlot();
    }

    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     */
    public function getPluginName() : string
    {
        return $this->getPluginInfo()->getName();
    }

    public function getId() : string
    {
        return $this->getPluginInfo()->getId();
    }

    /**
     * Only very little classes seem to care about this: Services/COPage/classes/class.ilPCPluggedGUI.php
     *
     * @return string
     */
    public function getVersion() : string
    {
        return (string) $this->getPluginInfo()->getAvailableVersion();
    }

    /**
     * Get Plugin Directory
     *
     * Only very little classes seem to care about this:
     *     - Services/COPage/classes/class.ilPCPlugged.php
     *     - Modules/DataCollection/classes/Fields/class.ilDclFieldFactory.php
     *
     * @return    object    Plugin Slot
     */
    public function getDirectory() : string
    {
        $plugin = $this->getPluginInfo();
        $component = $this->getComponentInfo();
        $slot = $this->getPluginSlotInfo();
        return "Customizing/global/plugins/" . $component->getType() . "/" . $component->getName() . "/" .
            $slot->getName() . "/" . $plugin->getName();
    }

    // ------------------------------------------
    // Language Handling
    // ------------------------------------------

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
                        $langs[] = array(
                            "key" => substr($file, 6, 2),
                            "file" => $file,
                            "path" => $a_lang_directory . "/" . $file,
                        );
                    }
                }
            }
        }

        return $langs;
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

    // ------------------------------------------

    /**
     * Has the plugin a configure class?
     *
     * @param string $a_slot_dir     slot directory
     * @param array  $plugin_data    plugin data
     * @param array  $plugin_db_data plugin db data
     *
     * @return boolean true/false
     */
    public static function hasConfigureClass(\ilPluginInfo $plugin) : bool
    {
        global $DIC;

        // Mantis: 23282: Disable plugin config page for incompatible plugins
        if (!$plugin->isCompliantToILIAS()) {
            return false;
        }

        return is_file($plugin->getPath() . "/classes/" . self::getConfigureClassName($plugin));
    }


    /**
     * Get plugin configure class name
     *
     * @param array $plugin_data
     *
     * @return string
     */
    protected static function getConfigureClassName(\ilPluginInfo $plugin) : string
    {
        return "il" . $plugin->getName() . "ConfigGUI";
    }


    /**
     * Get plugin prefix, used for lang vars
     */
    public function getPrefix() : string
    {
        $plugin = $this->getPluginInfo();
        $component = $this->getComponentInfo();
        $slot = $this->getPluginSlotInfo();

        return $component->getId() . "_" . $slot->getId() . "_" . $plugin->getId();
    }

    /**
     * Update database
     */
    public function updateDatabase()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $dbupdate = new ilPluginDBUpdate(
            $this->db,
            $this->getPluginInfo()
        );

        $dbupdate->applyUpdate();

        return $dbupdate->getCurrentVersion();
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
     * Only very little classes seem to care about this:
     *    - Services/Repository/classes/class.ilRepositoryObjectPlugin.php
     *    - Modules/OrgUnit/classes/Extension/class.ilOrgUnitExtensionPlugin.php
     *
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
        global $DIC;

        $component_repository = $DIC["component.repository"];

        $plugin = $component_repository->getPluginName($a_pname);
        $component = $component_repository->getComponentByTypeAndName($a_ctype, $a_cname);

        $d2 = $component->getId() . "_" . $a_slot_id . "_" . $plugin->getId();

        $img = ilUtil::getImagePath($d2 . "/" . $a_img);
        if (is_int(strpos($img, "Customizing"))) {
            return $img;
        }

        $d = ilPlugin::_getDirectory($a_ctype, $a_cname, $a_slot_id, $a_pname);

        return $d . "/templates/images/" . $a_img;
    }


    /**
     * @param string $a_css_file
     *
     * @return string
     */
    public function getStyleSheetLocation(string $a_css_file) : string
    {
        $d2 = $this->getComponentInfo()->getId() . "_" . $this->getPluginSlotInfo()->getId() . "_" . $this->getPluginInfo()->getId();

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
     * Object initialization. Can be overwritten by plugin class
     * (and should be made protected)
     *
     * TODO: Maybe this should be removed or be documented better. This is called
     * during __construct. If this contains expensive stuff this will be bad for
     * overall system performance, because Plugins tend to be constructed a lot.
     */
    protected function init()
    {
    }


    /**
     * Check whether plugin is active.
     *
     * Only very little classes seem to care about this:
     *    - Services/Component/classes/class.ilObjComponentSettingsGUI.php
     *    - Services/Component/classes/class.ilPluginsOverviewTableGUI.php
     *    - Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php
     */
    public function isActive()
    {
        return $this->getPluginInfo()->isActive();
    }


    /**
     * Check whether update is needed.
     */
    public function needsUpdate()
    {
        return $this->getPluginInfo()->isUpdateRequired();
    }


    public function install()
    {
        $this->afterInstall();
    }


    /**
     * Activate
     */
    public function activate()
    {
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
            $this->component_repository->setActivation($this->getId(), true);
            $this->afterActivation();
        }

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
        $this->component_repository->setActivation($this->getId(), false);
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

            $this->component_repository->removeStateInformationOf($this->getId());

            $this->afterUninstall();

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

        $result = $this->beforeUpdate();
        if ($result === false) {
            return false;
        }

        // Load language files
        $this->updateLanguages();

        // DB update
        if ($result === true) {
            $db_version = $this->updateDatabase();
        }

        $this->readEventListening();

        $this->readEventListening();

        // set last update version to current version
        if ($result === true) {
            $this->component_repository->setCurrentPluginVersion(
                $this->getPluginInfo()->getId(),
                $this->getPluginInfo()->getAvailableVersion(),
                $db_version
            );
            $this->afterUpdate();
        }

        return $result;
    }


    /**
     * Read the event listening definitions from the plugin.xml (if file exists)
     */
    protected function readEventListening()
    {
        $reader = new ilPluginReader(
            $this->getDirectory() . '/plugin.xml',
            $this->getComponentInfo()->getType(),
            $this->getComponentInfo()->getName(),
            $this->getPluginSlotInfo()->getId(),
            $this->getPluginInfo()->getName()
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
            $this->getComponentInfo()->getType(),
            $this->getComponentInfo()->getName(),
            $this->getPluginSlotInfo()->getId(),
            $this->getPluginInfo()->getName()
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
     * @return AbstractStaticPluginMainMenuProvider
     *
     * @deprecated
     * @see getGlobalScreenProviderCollection instead
     */
    public function promoteGlobalScreenProvider() : AbstractStaticPluginMainMenuProvider
    {
        global $DIC;

        return new ilPluginGlobalScreenNullProvider($DIC, $this);
    }


    /**
     * @return PluginProviderCollection
     */
    final public function getGlobalScreenProviderCollection() : PluginProviderCollection
    {
        if (!$this->promoteGlobalScreenProvider() instanceof ilPluginGlobalScreenNullProvider) {
            $this->provider_collection->setMainBarProvider($this->promoteGlobalScreenProvider());
        }

        return $this->provider_collection;
    }


    /**
     * This methods allows to replace the UI Renderer (see src/UI) of ILIAS after initialization
     * by returning a closure returning a custom renderer. E.g:
     *
     * return function(\ILIAS\DI\Container $c){
     *   return new CustomRenderer();
     * };
     *
     * Note: Note that plugins might conflict by replacing the renderer, so only use if you
     * are sure, that no other plugin will do this for a given context.
     *
     * @param \ILIAS\DI\Container $dic
     *
     * @return Closure
     */
    public function exchangeUIRendererAfterInitialization(\ILIAS\DI\Container $dic) : Closure
    {
        //This returns the callable of $c['ui.renderer'] without executing it.
        return $dic->raw('ui.renderer');
    }


    /**
     * This methods allows to replace some factory for UI Components (see src/UI) of ILIAS
     * after initialization by returning a closure returning a custom factory. E.g:
     *
     * if($key == "ui.factory.nameOfFactory"){
     *    return function(\ILIAS\DI\Container  $c){
     *       return new CustomFactory($c['ui.signal_generator'],$c['ui.factory.maincontrols.slate']);
     *    };
     * }
     *
     * Note: Note that plugins might conflict by replacing the same factory, so only use if you
     * are sure, that no other plugin will do this for a given context.
     *
     * @param string              $dic_key
     * @param \ILIAS\DI\Container $dic
     *
     * @return Closure
     */
    public function exchangeUIFactoryAfterInitialization(string $dic_key, \ILIAS\DI\Container $dic) : Closure
    {
        //This returns the callable of $c[$key] without executing it.
        return $dic->raw($dic_key);
    }


    /**
     * @return string
     */
    public function getMessage() : string
    {
        return strval($this->message);
    }
}
