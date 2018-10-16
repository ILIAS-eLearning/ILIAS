<?php

use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemFacade {

	/**
	 * @var ilMMItemStorage
	 */
	private $mm_item;
	/**
	 * @var isItem
	 */
	private $gs_item;
	/**
	 * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
	 */
	private $identification;


	/**
	 * ilMMItemFacade constructor.
	 *
	 * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
	 * @param array                                                      $providers
	 */
	public function __construct(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $providers) {
		global $DIC;
		$this->identification = $identification;
		$this->gs_item = $DIC->globalScreen()->collector()->mainmenu($providers)->getSingleItem($identification);
		$this->mm_item = ilMMItemStorage::findOrFail($identification->serialize());
	}


	public function getId(): string {
		return $this->identification->serialize();
	}


	public function getAmountOfChildren(): int {
		return 0;
	}


	public function isEmpty(): bool {
		return $this->mm_item->getIdentification() == '';
	}


	public function getMMItemStorage(): ilMMItemStorage {
		return $this->mm_item;
	}


	public function getGSIdentificationStorage(): ilGSIdentificationStorage {
		return ilGSIdentificationStorage::findOrFail($this->identification->serialize());
	}


	public function getGSItem(): isItem {
		throw new Exception();
	}


	public function isActive(): bool {
		return (bool)$this->mm_item->isActive();
	}


	public function getTitleForPresentation(): string {
		throw new Exception();
	}


	public function getPosition(): int {
		throw new Exception();
	}


	public function getDefaultTitle(): string {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) {
			return $this->gs_item->getTitle();
		}

		return "No Title";
		throw new Exception();
	}


	/**
	 * @return string
	 */
	public function getGSItemClassName(): string {
		return get_class($this->gs_item);
	}


	public function identification(): \ILIAS\GlobalScreen\Identification\IdentificationInterface {
		throw new Exception();
	}


	/**
	 * @return string
	 */
	public function getProviderNameForPresentation(): string {
		return $this->identification->getProviderNameForPresentation();
	}
}
