<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMNullItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMNullItemFacade extends ilMMCustomItemFacade implements ilMMItemFacadeInterface {

	/**
	 * @var
	 */
	private $active_status;


	/**
	 * @inheritDoc
	 */
	public function __construct(IdentificationInterface $identification, Main $collector) {
		$this->identification = $identification;
	}


	/**
	 * @inheritDoc
	 */
	public function isEmpty(): bool {
		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function setActiveStatus(bool $status) {
		$this->active_status = $status;
	}


	public function create() {
		$s = new ilMMCustomItemStorage();
		$s->setTopItem(true);
		$s->setIdentifier(uniqid());
		$s->setType($this->type);
		$s->setAction($this->action);
		$s->setDefaultTitle($this->default_title);
		$s->create();

		$this->custom_item_storage = $s;

		global $DIC;
		$provider = new ilMMCustomProvider($DIC);
		$this->gs_item = $provider->getSingleCustomItem($s);

		$this->mm_item = new ilMMItemStorage();
		$this->mm_item->setPosition($this->gs_item->getPosition());
		$this->mm_item->setIdentification($this->gs_item->getProviderIdentification()->serialize());
		$this->mm_item->setActive($this->active_status);
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\isChild) {
			$this->mm_item->setParentIdentification($this->gs_item->getParent()->serialize());
		}

		parent::create();
	}
}

