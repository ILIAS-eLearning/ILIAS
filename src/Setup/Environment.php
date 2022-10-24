<?php

declare(strict_types=1);

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

namespace ILIAS\Setup;

/**
 * An environment holds resources to be used in the setup process. Objectives might
 * add resources when they have been achieved.
 */
interface Environment
{
    // We define some resources that will definitely be requried. We allow for
    // new identifiers, though, to be open for extensions and the future.
    public const RESOURCE_DATABASE = "resource_database";
    public const RESOURCE_ADMIN_INTERACTION = "resource_admin_interaction";
    public const RESOURCE_ILIAS_INI = "resource_ilias_ini";
    public const RESOURCE_CLIENT_INI = "resource_client_ini";
    public const RESOURCE_SETTINGS_FACTORY = "resource_settings_factory";
    public const RESOURCE_CLIENT_ID = "resource_client_id";
    public const RESOURCE_PLUGIN_ADMIN = "resource_plugin_admin";
    public const RESOURCE_COMPONENT_REPOSITORY = "resource_component_repository";
    public const RESOURCE_COMPONENT_FACTORY = "resource_component_factory";

    /**
     * Consumers of this method should check if the result is what they expect,
     * e.g. implements some known interface.
     *
     * @return mixed|null
     */
    public function getResource(string $id);

    /**
     * @throw \RuntimeException if this resource is already in the environment.
     */
    public function withResource(string $id, $resource): Environment;

    /**
     * Stores a config for some component in the environment.
     *
     * @throw \RuntimeException if this config is already in the environment.
     */
    public function withConfigFor(string $component, $config): Environment;

    /**
     * @throw \RuntimeException if there is no config for the component
     * @return mixed
     */
    public function getConfigFor(string $component);
    public function hasConfigFor(string $component): bool;
}
