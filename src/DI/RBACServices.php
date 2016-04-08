<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class RBACServices {
	/**
	 * Get the interface to the RBAC system.
	 *
	 * @return	ilRbacSystem
	 */
	public function system() {
		
	}

	/**
	 * Get the interface to insert relations into the RBAC system.
	 *
	 * @return	ilRbacAdmin
	 */
	public function admin() {
		
	}

	/**
	 * Get the interface to query the RBAC system.
	 *
	 * @return	ilRbacSystem
	 */
	public function review() {
		
	}

}