<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMAbstractItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractItemFacade implements ilMMItemFacadeInterface {

	/**
	 * @var ilMMItemStorage
	 */
	protected $mm_item;
	/**
	 * @var isItem
	 */
	protected $gs_item;
	/**
	 * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
	 */
	protected $identification;
	/**
	 * @var string
	 */
	protected $default_title = "-";


	/**
	 * ilMMAbstractItemFacade constructor.
	 *
	 * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
	 * @param Main                                                       $collector
	 *
	 * @throws Throwable
	 */
	public function __construct(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, Main $collector) {
		$this->identification = $identification;
		$this->gs_item = $collector->getSingleItem($identification);
		$this->mm_item = ilMMItemStorage::register($this->gs_item);
	}


	public function getId(): string {
		return $this->identification->serialize();
	}


	/**
	 * @return bool
	 */
	public function hasStorage(): bool {
		return ilMMItemStorage::find($this->getId()) !== null;
	}


	/**
	 * @return bool
	 */
	public function isEmpty(): bool {
		return $this->mm_item->getIdentification() == '';
	}


	/**
	 * @return ilMMItemStorage
	 */
	public function itemStorage(): ilMMItemStorage {
		return $this->mm_item;
	}


	/**
	 * @return ilGSIdentificationStorage
	 * @throws arException
	 */
	public function identificationStorage(): ilGSIdentificationStorage {
		return ilGSIdentificationStorage::findOrFail($this->identification->serialize());
	}


	/**
	 * @return \ILIAS\GlobalScreen\Identification\IdentificationInterface
	 */
	public function identification(): \ILIAS\GlobalScreen\Identification\IdentificationInterface {
		return $this->identification;
	}


	/**
	 * @return isItem
	 */
	public function item(): isItem {
		return $this->gs_item;
	}


	public function getAmountOfChildren(): int {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isParent) {
			return count($this->gs_item->getChildren());
		}

		return 0;
	}


	public function isAvailable(): bool {
		return (bool)(($this->mm_item->isActive() && $this->gs_item->isAvailable()) || $this->item()->isAlwaysAvailable());
	}


	/**
	 * @return string
	 */
	public function getProviderNameForPresentation(): string {
		return $this->identification->getProviderNameForPresentation();
	}


	/**
	 * @return string
	 */
	public function getDefaultTitle(): string {
		if ($this->default_title == "-" && $this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) {
			$this->default_title = $this->gs_item->getTitle();
		}

		return $this->default_title;
	}


	/**
	 * @param string $default_title
	 */
	public function setDefaultTitle(string $default_title) {
		$this->default_title = $default_title;
	}


	/**
	 * @return string
	 */
	public function getStatus(): string {
		global $DIC;
		if (!$this->gs_item->isAvailable() || $this->gs_item->isAlwaysAvailable()) {
			return $DIC->ui()->renderer()->render($this->gs_item->getNonAvailableReason());
		}

		return "";
	}


	/**
	 * @return string
	 * @throws ReflectionException
	 */
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


	/**
	 * @inheritDoc
	 */
	public function isTopItem(): bool {
		return $this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isTopItem;
	}


	/**
	 * @inheritDoc
	 */
	public function setIsTopItm(bool $top_item) {
		// TODO: Implement setIsTopItm() method.
	}


	/**
	 * FSX check if doublette
	 *
	 * @inheritDoc
	 */
	public function getType(): string {
		return get_class($this->gs_item);
	}


	/**
	 * @param string $parent
	 */
	public function setParent(string $parent) {
		$this->mm_item->setParentIdentification($parent);
	}


	/**
	 * @inheritdoc
	 */
	public function setPosition(int $position) {
		$this->mm_item->setPosition($position);
	}


	/**
	 * @param bool $status
	 */
	public function setActiveStatus(bool $status) {
		$this->mm_item->setActive($status);
	}


	// CRUD

	public function update() {
		ilMMItemTranslationStorage::storeDefaultTranslation($this->identification, $this->default_title);

		$this->mm_item->update();
	}


	public function create() {
		ilMMItemTranslationStorage::storeDefaultTranslation($this->identification, $this->default_title);
		$this->mm_item->create();
		ilMMItemStorage::register($this->gs_item);
	}


	public function delete() {
		throw new Exception();
	}
}