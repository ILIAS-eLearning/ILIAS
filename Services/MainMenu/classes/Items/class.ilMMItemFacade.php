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

		$this->mm_item = ilMMItemStorage::find($identification->serialize());
		if ($this->mm_item === null) {
			$this->mm_item = new ilMMItemStorage();
			$this->mm_item->setPosition($this->gs_item->getPosition());
			$this->mm_item->setIdentification($identification->serialize());
			$this->mm_item->setActive(true);
			if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isChild) {
				$this->mm_item->setParentIdentification($this->gs_item->getParent()->serialize());
			}
			$this->mm_item->create();
		}
	}


	public function getId(): string {
		return $this->identification->serialize();
	}


	public function getAmountOfChildren(): int {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isParent) {
			return count($this->gs_item->getChildren());
		}

		return 0;
	}


	/**
	 * @return bool
	 */
	public function hasStorage(): bool {
		return ilMMItemStorage::find($this->getId()) !== null;
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


	/**
	 * @return isItem
	 */
	public function getGSItem(): isItem {
		return $this->gs_item;
	}


	public function isActive(): bool {
		return (bool)$this->mm_item->isActive();
	}


	public function getTitleForPresentation(): string {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) {
			return $this->gs_item->getTitle();
		}

		return "No Title";
	}


	public function getPosition(): int {
		throw new Exception();
	}


	public function getDefaultTitle(): string {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) { //FSX
			return $this->gs_item->getTitle();
		}

		return "No Title";
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


	public function getStatus(): string {
		global $DIC;

		return $DIC->ui()->renderer()->render($this->gs_item->getNonAvailableReason());
	}


	public function getTypeForPresentation(): string {
		$reflect = new ReflectionClass($this->gs_item);

		return $reflect->getShortName();
	}


	public function getParentIdentificationString(): string {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isChild) {
			$provider_name_for_presentation = $this->gs_item->getParent()->serialize();

			return $provider_name_for_presentation;
		}

		return "";
	}


	// Setter
	public function setActiveStatus(bool $status) {
		$this->mm_item->setActive($status);
	}


	public function setDefaultTitle(string $default_title) {

	}


	public function setPosition(int $position) {
		$this->mm_item->setPosition($position);
	}


	public function setParent(string $parent) {
		$this->mm_item->setParentIdentification($parent);
	}


	public function update() {
		// FSX Translation
		$this->mm_item->update();
	}


	public function create() {
		// FSX Translation and Identification
		$this->mm_item->create();
	}
}
