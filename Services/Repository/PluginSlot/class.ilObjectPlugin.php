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
 * Object class for plugins. This one wraps around ilObject
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilObjectPlugin extends ilObject2
{
    protected ?ilPlugin $plugin = null;
    protected ilComponentFactory $component_factory;

    public function __construct(int $a_ref_id = 0)
    {
        global $DIC;
        $this->component_factory = $DIC["component.factory"];
        $this->initType();
        parent::__construct($a_ref_id, true);
        $this->plugin = $this->getPlugin();
    }


    /**
     * Return either a repoObject plugin or a orgunit extension plugin or null if the type is not a plugin.
     * @return null | ilRepositoryObjectPlugin | ilOrgUnitExtensionPlugin
     */
    public static function getPluginObjectByType(string $type): ?ilPlugin
    {
        global $DIC;
        $component_factory = $DIC["component.factory"];
        try {
            return $component_factory->getPlugin($type);
        } catch (InvalidArgumentException $e) {
            ilLoggerFactory::getLogger("obj")->log("There was an error while instantiating repo plugin obj of type: $type. Error: $e");
        }
        return null;
    }

    public static function lookupTxtById(
        string $plugin_id,
        string $lang_var
    ): string {
        $pl = self::getPluginObjectByType($plugin_id);
        return $pl->txt($lang_var);
    }

    /**
     * Get plugin object
     * @throws ilPluginException
     */
    protected function getPlugin(): ilPlugin
    {
        if (!$this->plugin) {
            $this->plugin = $this->component_factory->getPlugin($this->getType());
        }
        return $this->plugin;
    }

    final public function txt(string $a_var): string
    {
        return $this->getPlugin()->txt($a_var);
    }

    /**
     * returns a list of all repository object types which can be a parent of this type.
     * @return string[]
     */
    public function getParentTypes(): array
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
    public static function langExitsById(string $pluginId, string $langVar): bool
    {
        global $DIC;
        $lng = $DIC->language();

        $pl = self::getPluginObjectByType($pluginId);
        $pl->loadLanguageModule();

        return $lng->exists($pl->getPrefix() . "_" . $langVar);
    }

    public function getPrefix(): string
    {
        return $this->getPlugin()->getPrefix();
    }
}
