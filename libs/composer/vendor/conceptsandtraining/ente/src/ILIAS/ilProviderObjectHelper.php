<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

use CaT\Ente;

/**
 * Helper for repository objects that want to provide components.
 */
trait ilProviderObjectHelper {
	use ilObjectHelper;

	/**
	 * Delete all unbound providers of this object.
	 *
	 * @return void
	 */
	protected function deleteUnboundProviders() {
		if (!($this instanceof \ilObject)) {
			throw new \LogicException("ilProviderObjectHelper can only be used with ilObjects.");
		}

		$provider_db = $this->getProviderDB();
		$unbound_providers = $provider_db->unboundProvidersOf($this);
		foreach ($unbound_providers as $unbound_provider) {
			$provider_db->delete($unbound_provider, $this);
		}
	}

	/**
	 * Create an unbound provider for this object.
	 *
	 * @param	string	$object_type	for which the object provides
	 * @param	string	$class_name		of the unbound provider
	 * @param	string	$path			of the include file for the unbound provider class
	 * @return 	void
	 */
	protected function createUnboundProvider($object_type, $class_name, $path) {
		if (!($this instanceof \ilObject)) {
			throw new \LogicException("ilProviderObjectHelper can only be used with ilObjects.");
		}
		if(is_subclass_of($class_name, SeparatedUnboundProvider::class)) {
			$this->getProviderDB()->createSeparatedUnboundProvider($this, $object_type, $class_name, $path);
		}
		else if(is_subclass_of($class_name, SharedUnboundProvider::class)) {
			$this->getProviderDB()->createSharedUnboundProvider($this, $object_type, $class_name, $path);
		}
		else {
			throw new \LogicException(
				"createUnboundProvider can only create providers ".
				"derived from Shared- or SeperatedUnboundProvider.");
		}
	}
}
