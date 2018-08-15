<?php namespace ILIAS\UX\MainMenu;

use ILIAS\UX\MainMenu\Entry\Divider;
use ILIAS\UX\MainMenu\Entry\DividerInterface;
use ILIAS\UX\MainMenu\Entry\Link;
use ILIAS\UX\MainMenu\Entry\LinkInterface;
use ILIAS\UX\MainMenu\Slate\Slate;
use ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface;
use ILIAS\UX\Identification\IdentificationInterface;

/**
 * Class EntryFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class EntryFactory {

	/**
	 * @inheritDoc
	 */
	public function slate(IdentificationInterface $identification): SlateInterfaceInterface {
		return new Slate($identification);
	}


	/**
	 * @inheritdoc
	 */
	public function link(IdentificationInterface $identification): LinkInterface {
		return new Link($identification);
	}


	/**
	 * @inheritdoc
	 */
	public function divider(IdentificationInterface $identification): DividerInterface {
		return new Divider($identification);
	}
}
