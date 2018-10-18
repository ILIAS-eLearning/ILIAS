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
	protected $default_title;
	/**
	 * @var string
	 */
	protected $action = '';
	/**
	 * @var string
	 */
	protected $type = '';


	/**
	 * @inheritDoc
	 */
	public function __construct(IdentificationInterface $identification, Main $collector) {
		parent::__construct($identification, $collector);
		$this->custom_item_storage = $this->getCustomStorage();
		$this->default_title = "";
		if ($this->custom_item_storage instanceof ilMMCustomItemStorage) {
			$this->default_title = $this->custom_item_storage->getDefaultTitle() ? $this->custom_item_storage->getDefaultTitle() : "";
		}
	}


	/**
	 * @inheritDoc
	 */
	public function update() {
		if ($this->isCustom()) {
			$mm = $this->getCustomStorage();
			if ($mm instanceof ilMMCustomItemStorage) {
				$mm->setDefaultTitle($this->getDefaultTitle());
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


	public function getDefaultTitle(): string {
		return $this->default_title;
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


	public function getStatus(): string {
		return "";
	}


	public function getTypeForPresentation(): string {
		return "Custom";
	}


	public function setDefaultTitle(string $default_title) {
		$this->default_title = $default_title;
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
}
