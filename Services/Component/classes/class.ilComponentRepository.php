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
 * Writeable part of repository interface to ilComponenDataDB.
 */
interface ilComponentRepository
{
    public const PLUGIN_BASE_PATH = "Customizing/global/plugins";

    /**
     * Check if a component exists.
     *
     * @throws \InvalidArgumentException if $type is not known
     */
    public function hasComponent(string $type, string $name): bool;

    /**
     * Check if a component exists.
     */
    public function hasComponentId(string $id): bool;

    /**
     * Get all components.
     *
     * Keys are the ids.
     *
     * @return Iterator <string, ilComponentInfo>
     */
    public function getComponents(): Iterator;

    /**
     * Get a component by id.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentById(string $id): ilComponentInfo;

    /**
     * Get a component by type and name.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentByTypeAndName(string $type, string $name): ilComponentInfo;

    /**
     * Check if a slot exists.
     */
    public function hasPluginSlotId(string $id): bool;

    /**
     * Get all pluginslots.
     *
     * Keys are the ids.
     *
     * @return Iterator<string, ilPluginSlotInfo>
     */
    public function getPluginSlots(): Iterator;

    /**
     * Get pluginslot by id.
     *
     * @throws \InvalidArgumentException if pluginslot does not exist
     */
    public function getPluginSlotById(string $id): ilPluginSlotInfo;

    /**
     * Check if a plugin exists.
     */
    public function hasPluginId(string $id): bool;

    /**
     * Get all plugins.
     *
     * Keys are the ids.
     *
     * @return Iterator<string, ilPluginInfo>
     */
    public function getPlugins(): Iterator;

    /**
     * Get a plugin by id.
     *
     * @throws \InvalidArgumentException if plugin does not exist
     */
    public function getPluginById(string $id): ilPluginInfo;

    /**
     * Get a plugin by name.
     *
     * @throws \InvalidArgumentException if plugin does not exist.
     */
    public function getPluginByName(string $name): ilPluginInfo;
}
