<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilMMCustomItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomItemFacade extends ilMMAbstractItemFacade {

	/**
	 * @var ilMMCustomItemStorage|null
	 */
	protected $custom_item_storage;
	/**
	 * @var string
	 */
	protected $action = '';
	/**
	 * @var string
	 */
	protected $type = '';
	/**
	 * @var bool
	 */
	protected $top_item = false;


	/**
	 * @inheritDoc
	 */
	public function __construct(IdentificationInterface $identification, Main $collector) {
		parent::__construct($identification, $collector);
		$this->custom_item_storage = $this->getCustomStorage();
		if ($this->custom_item_storage instanceof ilMMCustomItemStorage) {
			if ($this->custom_item_storage->getType()) {
				$this->type = $this->custom_item_storage->getType();
			}
		}
	}


	/**
	 * @inheritDoc
	 */
	public function update() {
		if ($this->isCustom()) {
			$mm = $this->getCustomStorage();
			if ($mm instanceof ilMMCustomItemStorage) {
				$default_title = $this->getDefaultTitle();
				$mm->setDefaultTitle($default_title);
				$mm->setType($this->getType()); // FSX
				$mm->update();
			}
		}
		parent::update();
	}


	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (!$this->isCustom()) {
			throw new LogicException("Non Custom items can't be deleted");
		}

		$cm = $this->getCustomStorage();
		if ($cm instanceof ilMMCustomItemStorage) {
			$cm->delete();
		}
		$gs = ilGSIdentificationStorage::find($this->gs_item->getProviderIdentification()->serialize());
		if ($gs instanceof ilGSIdentificationStorage) {
			$gs->delete();
		}
		$mm = ilMMItemStorage::find($this->gs_item->getProviderIdentification()->serialize());
		if ($mm instanceof ilMMItemStorage) {
			$mm->delete();
		}
	}


	/**
	 * @return ilMMCustomItemStorage|null
	 */
	private function getCustomStorage() {
		$id = $this->gs_item->getProviderIdentification()->getInternalIdentifier();
		$mm = ilMMCustomItemStorage::find($id);

		return $mm;
	}


	/**
	 * @inheritDoc
	 */
	public function isCustom(): bool {
		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function getProviderNameForPresentation(): string {
		return "Custom";
	}


	/**
	 * @return string
	 */
	public function getStatus(): string {
		return "";
	}


	/**
	 * @inheritDoc
	 */
	public function setAction(string $action) {
		$this->action = $action;
	}


	/**
	 * @inheritDoc
	 */
	public function getType(): string {
		return $this->type;
	}


	/**
	 * @inheritDoc
	 */
	public function setType(string $type) {
		$this->type = $type;
	}


	/**
	 * @inheritDoc
	 */
	public function isTopItem(): bool {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isItem) {
			return parent::isTopItem();
		}

		return $this->top_item;
	}


	/**
	 * @inheritDoc
	 */
	public function setIsTopItm(bool $top_item) {
		$this->top_item = $top_item;
	}
}
