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
	 * Returns you a UX Slate which can be added to the MainMenu. Slates are
	 * always the first level of entries in the MaiMenu and can contain other
	 * entries (e.g. Links).
	 *
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return SlateInterfaceInterface
	 */
	public function slate(IdentificationInterface $identification): SlateInterfaceInterface {
		return new Slate($identification);
	}


	/**
	 * Returns you s UX Link which can be added to Slates.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return LinkInterface
	 */
	public function link(IdentificationInterface $identification): LinkInterface {
		return new Link($identification);
	}


	/**
	 * Returns you a UX Divider which is used to separate to other entries in a
	 * optical way.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return DividerInterface
	 */
	public function divider(IdentificationInterface $identification): DividerInterface {
		return new Divider($identification);
	}
}
