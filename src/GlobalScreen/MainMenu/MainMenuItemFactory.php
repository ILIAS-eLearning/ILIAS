<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\MainMenu\Item\Complex;
use ILIAS\GlobalScreen\MainMenu\Item\ComplexInterface;
use ILIAS\GlobalScreen\MainMenu\Item\Separator;
use ILIAS\GlobalScreen\MainMenu\Item\DividerInterface;
use ILIAS\GlobalScreen\MainMenu\Item\Link;
use ILIAS\GlobalScreen\MainMenu\Item\LinkInterface;
use ILIAS\GlobalScreen\MainMenu\Item\LinkList;
use ILIAS\GlobalScreen\MainMenu\Item\RepositoryLink;
use ILIAS\GlobalScreen\MainMenu\Item\RepositoryLinkInterface;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\SlateInterfaceInterface;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MainMenuItemFactory
 *
 * This factory provides you all available types for MainMenu GlobalScreen Items.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainMenuItemFactory {

	/**
	 * Returns you a GlobalScreen TopParentItem which can be added to the MainMenu. TopItems are
	 * always at the first level in the MainMenu and can contain other
	 * entries (e.g. Links).
	 *
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return TopParentItem
	 */
	public function topParentItem(IdentificationInterface $identification): TopParentItem {
		return new TopParentItem($identification);
	}


	/**
	 * Returns you a GlobalScreen TopLinkItem which can be added to the MainMenu. TopLinkItem are
	 * always at the first level in the MainMenu and have an action
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return TopLinkItem
	 */
	public function topLinkItem(IdentificationInterface $identification): TopLinkItem {
		return new TopLinkItem($identification);
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
	 * Returns you a GlobalScreen Separator which is used to separate to other entries in a
	 * optical way.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return Separator
	 */
	public function separator(IdentificationInterface $identification): Separator {
		return new Separator($identification);
	}


	/**
	 * Returns you a GlobalScreen Complex Item which is used to generate complex
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
	 * Returns you a GlobalScreen RepositoryLink Item which is used to generate URLs to Ref-IDs
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return RepositoryLink
	 */
	public function repositoryLink(IdentificationInterface $identification): RepositoryLink {
		return new RepositoryLink($identification);
	}


	/**
	 * Returns you a GlobalScreen LinkList Item which is used to group multiple Links
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return LinkList
	 */
	public function linkList(IdentificationInterface $identification): LinkList {
		return new LinkList($identification);
	}


	/**
	 * @param string                  $class_name
	 * @param IdentificationInterface $identification
	 *
	 * @return isItem
	 */
	public function custom(string $class_name, IdentificationInterface $identification): isItem {
		if (!class_exists($class_name)) {
			throw new \InvalidArgumentException("Unknown Classname '$class_name' provided. Cannot provide type.");
		}

		return new $class_name($identification);
	}
}
