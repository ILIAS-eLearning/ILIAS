<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Object class for plugins. This one wraps around ilObject
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilObjectPlugin extends ilObject2
{
    protected ilPlugin $plugin;
    protected static array $plugin_by_type = [];

    public function __construct(int $a_ref_id = 0)
    {
        $this->initType();
        parent::__construct($a_ref_id, true);
        $this->plugin = $this->getPlugin();
    }


    /**
     * Return either a repoObject plugin or a orgunit extension plugin or null if the type is not a plugin.
     * @return null | ilRepositoryObjectPlugin | ilOrgUnitExtensionPlugin
     */
    public static function getPluginObjectByType(string $type) : ?ilPlugin
    {
        if (!isset(self::$plugin_by_type[$type]) || !self::$plugin_by_type[$type]) {
            list($component, $component_name) = ilPlugin::lookupTypeInformationsForId($type);
            if (
                $component == IL_COMP_SERVICE &&
                $component_name == "Repository"
            ) {
                self::loadRepoPlugin($type);
            }

            if (
                $component == IL_COMP_MODULE &&
                $component_name == "OrgUnit"
            ) {
                self::loadOrgUnitPlugin($type);
            }
        }

        return self::$plugin_by_type[$type];
    }

    protected static function loadRepoPlugin(string $type_id) : void
    {
        $plugin = null;
        $name = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $type_id);

        if (!is_null($name)) {
            $plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", $name);
        }

        if (is_null($plugin)) {
            ilLoggerFactory::getLogger("obj")->log("Try to get repo plugin obj by type: $type_id. No such type exists for Repository and Org Unit pluginss.");
        }
        self::$plugin_by_type[$type_id] = $plugin;
    }

    protected static function loadOrgUnitPlugin(string $type_id) : void
    {
        $plugin = null;
        $name = ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $type_id);
        if (!is_null($name)) {
            $plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext", $name);
        }

        if (is_null($plugin)) {
            ilLoggerFactory::getLogger("obj")->log("Try to get repo plugin obj by type: $type_id. No such type exists for Repository and Org Unit pluginss.");
        }
        self::$plugin_by_type[$type_id] = $plugin;
    }

    public static function lookupTxtById(
        string $plugin_id,
        string $lang_var
    ) : string {
        $pl = self::getPluginObjectByType($plugin_id);
        return $pl->txt($lang_var);
    }

    /**
     * Get plugin object
     * @throws ilPluginException
     */
    protected function getPlugin() : ilPlugin
    {
        if (!$this->plugin) {
            $this->plugin =
                ilPlugin::getPluginObject(
                    IL_COMP_SERVICE,
                    "Repository",
                    "robj",
                    ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType())
                );
            if (!is_object($this->plugin)) {
                throw new ilPluginException("ilObjectPlugin: Could not instantiate plugin object for type " . $this->getType() . ".");
            }
        }
        return $this->plugin;
    }

    final public function txt(string $a_var) : string
    {
        return $this->getPlugin()->txt($a_var);
    }

    /**
     * returns a list of all repository object types which can be a parent of this type.
     * @return string[]
     */
    public function getParentTypes() : array
    {
        return $this->plugin->getParentTypes();
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

        $pl = self::getPluginObjectByType($pluginId);
        $pl->loadLanguageModule();

        return $lng->exists($pl->getPrefix() . "_" . $langVar);
    }
}
