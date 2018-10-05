<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\MainMenu\Entry\Complex;
use ILIAS\GlobalScreen\MainMenu\Entry\ComplexInterface;
use ILIAS\GlobalScreen\MainMenu\Entry\Divider;
use ILIAS\GlobalScreen\MainMenu\Entry\DividerInterface;
use ILIAS\GlobalScreen\MainMenu\Entry\Link;
use ILIAS\GlobalScreen\MainMenu\Entry\LinkInterface;
use ILIAS\GlobalScreen\MainMenu\Entry\RepositoryLink;
use ILIAS\GlobalScreen\MainMenu\Entry\RepositoryLinkInterface;
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
	 * @return Slate
	 */
	public function slate(IdentificationInterface $identification): Slate {
		return new Slate($identification);
	}


	/**
	 * Returns you s GlobalScreen Link which can be added to Slates.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return Link
	 */
	public function link(IdentificationInterface $identification): Link {
		return new Link($identification);
	}


	/**
	 * Returns you a GlobalScreen Divider which is used to separate to other entries in a
	 * optical way.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return Divider
	 */
	public function divider(IdentificationInterface $identification): Divider {
		return new Divider($identification);
	}


	/**
	 * Returns you a GlobalScreen Complex Entry which is used to generate complex
	 * content from a Async-URL
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return Complex
	 */
	public function complex(IdentificationInterface $identification): Complex {
		return new Complex($identification);
	}


	/**
	 * Returns you a GlobalScreen RepositoryLink Entry which is used to generate URLs to Ref-IDs
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return RepositoryLink
	 */
	public function repositoryLink(IdentificationInterface $identification): RepositoryLink {
		return new RepositoryLink($identification);
	}
}
