<?php declare(strict_types=1);

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

/**
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilComponentFactoryImplementation implements ilComponentFactory
{
    protected \ilComponentDataDBWrite $component_data_db;
    protected \ilDBInterface $db;

    protected array $plugins = [];

    public function __construct(
        \ilComponentDataDBWrite $component_data_db,

        // These are only required to pass on to the created objects.
        \ilDBInterface $db
    ) {
        $this->component_data_db = $component_data_db;

        // These are only required to pass on to the created objects.
        $this->db = $db;
    }

    public function getPlugin(string $id) : ilPlugin
    {
        if (!isset($this->plugins[$id])) {
            $plugin_info = $this->component_data_db->getPluginById($id);
            $class_name = $plugin_info->getClassName();
            $plugin = new $class_name($this->db, $this->component_data_db, $id);
            $this->plugins[$id] = $plugin;
        }

        return $this->plugins[$id];
    }

    public function getActivePluginsInSlot(string $slot_id) : Generator
    {
        $ps = $this->component_data_db->getPluginSlotById($slot_id)->getActivePlugins();
        foreach ($ps as $p) {
            yield $this->getPlugin($p->getId());
        }
    }
}
