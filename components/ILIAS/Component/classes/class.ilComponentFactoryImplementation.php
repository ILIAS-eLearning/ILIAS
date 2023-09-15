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

declare(strict_types=1);

/**
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilComponentFactoryImplementation implements ilComponentFactory
{
    protected \ilComponentRepositoryWrite $component_repository;
    protected \ilDBInterface $db;

    protected array $plugins = [];

    public function __construct(
        \ilComponentRepositoryWrite $component_repository,

        // These are only required to pass on to the created objects.
        \ilDBInterface $db
    ) {
        $this->component_repository = $component_repository;

        // These are only required to pass on to the created objects.
        $this->db = $db;
    }

    public function getPlugin(string $id): ilPlugin
    {
        if (!isset($this->plugins[$id])) {
            $plugin_info = $this->component_repository->getPluginById($id);
            $class_name = $plugin_info->getClassName();
            $plugin = new $class_name($this->db, $this->component_repository, $id);
            $this->plugins[$id] = $plugin;
        }

        return $this->plugins[$id];
    }

    public function getActivePluginsInSlot(string $slot_id): Generator
    {
        $ps = $this->component_repository->getPluginSlotById($slot_id)->getActivePlugins();
        foreach ($ps as $p) {
            yield $this->getPlugin($p->getId());
        }
    }
}
