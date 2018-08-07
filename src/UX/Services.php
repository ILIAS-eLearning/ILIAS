<?php namespace ILIAS\UX;

use ILIAS\UX\Identification\IdentificationFactory;
use ILIAS\UX\MainMenu\EntryFactory;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services {

	/**
	 * @inheritdoc
	 */
	public function mainmenu(): EntryFactory {
		return new EntryFactory();
	}


	/**
	 * @inheritDoc
	 */
	public function identification(): IdentificationFactory {
		return new IdentificationFactory();
	}
}
