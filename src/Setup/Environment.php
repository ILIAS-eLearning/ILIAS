<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * An environment holds resources to be used in the setup process. Objectives might
 * add resources when they have been achieved.
 */
interface Environment {
	// We define some resources that will definitely be requried. We allow for
	// new identifiers, though, to be open for extensions and the future.
	const RESOURCE_DATABASE = "resource_database";
	const RESOURCE_ADMIN_INTERACTION = "resource_admin_interaction";
	const RESOURCE_ACHIEVEMENT_TRACKER = "resource_achievement_tracker";
	const RESOURCE_ILIAS_INI = "resource_ilias_ini";
	const RESOURCE_CLIENT_INI = "resource_client_ini";
	const RESOURCE_SETTINGS_FACTORY = "resource_settings_factory";

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
	public function withResource(string $id, $resource) : Environment;

	 /**
	  * Stores a config for some component in the environment.
	  *
	  * @throw \RuntimeException if this config is already in the environment.
	  */
	public function withConfigFor(string $component, $config) : Environment;

	/**
	 * @throw \RuntimeException if there is no config for the component
	 * @return mixed 
	 */
	public function getConfigFor(string $component);
}
