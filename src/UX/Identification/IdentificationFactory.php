<?php namespace ILIAS\UX\Identification;

/**
 * Class IdentificationFactory
 *
 * There will be at least two IdentificationProvider, one cor core components
 * and one for plugins. This factory allows to acces both.
 *
 * Currently Identifications are only used for the UX-MainMenu-Elements.
 * Other like Footer may follow.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationFactory {

	/**
	 * Returns a IndentificationProvider for core components, only a Provider
	 * is needed.
	 *
	 * @param \ILIAS\UX\Provider\Provider $provider
	 *
	 * @return IdentificationProviderInterface
	 */
	public function core(\ILIAS\UX\Provider\Provider $provider): IdentificationProviderInterface {
		return new CoreIdentificationProvider(get_class($provider));
	}


	/**
	 * Returns a IndentificationProvider for ILIAS-Plugins which takes care of
	 * the plugin_id for further identification where a provided UX-element
	 * comes from (e.g. to disable or delete all elements when a plugin is
	 * deleted or deactivated).
	 *
	 * @param \ilPlugin                   $plugin
	 * @param \ILIAS\UX\Provider\Provider $provider
	 *
	 * @return IdentificationProviderInterface
	 */
	public function plugin(\ilPlugin $plugin, \ILIAS\UX\Provider\Provider $provider): IdentificationProviderInterface {
		return new PluginIdentificationProvider(get_class($provider), $plugin->getId());
	}
}

