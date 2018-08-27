<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\MainMenu\Entry\Divider;
use ILIAS\GlobalScreen\MainMenu\Entry\DividerInterface;
use ILIAS\GlobalScreen\MainMenu\Entry\Link;
use ILIAS\GlobalScreen\MainMenu\Entry\LinkInterface;
use ILIAS\GlobalScreen\MainMenu\Slate\Slate;
use ILIAS\GlobalScreen\MainMenu\Slate\SlateInterfaceInterface;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MainMenuEntryFactory
 *
 * This factory provides you all available types for MainMenu GlobalScreen Elements.
 *
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainMenuEntryFactory {

	/**
	 * Returns you a GlobalScreen Slate which can be added to the MainMenu. Slates are
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
	 * Returns you s GlobalScreen Link which can be added to Slates.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return LinkInterface
	 */
	public function link(IdentificationInterface $identification): LinkInterface {
		return new Link($identification);
	}


	/**
	 * Returns you a GlobalScreen Divider which is used to separate to other entries in a
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
