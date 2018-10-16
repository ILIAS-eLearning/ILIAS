<?php

use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemFacade {

	/**
	 * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
	 */
	protected $identification;


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
	}


	public function getId(): string {
		return $this->identification->serialize();
	}


	public function getAmountOfChildren(): int {
		return 0;
	}


	public function isEmpty(): bool {
		return $this->identification->serialize() == '';
	}


	public function getMMItemStorage(): ilMMItemStorage {
		throw new Exception();
	}


	public function getGSIdentificationStorage(): ilGSIdentificationStorage {
		return ilGSIdentificationStorage::findOrFail($this->identification->serialize());
	}


	public function getGSItem(): isItem {
		throw new Exception();
	}


	public function isActive(): bool {
		throw new Exception();
	}


	public function getTitleForPresentation(): string {
		throw new Exception();
	}


	public function getPosition(): int {
		throw new Exception();
	}


	public function getDefaultTitle(): string {
		throw new Exception();
	}


	public function getGSItemClassName(): string {
		throw new Exception();
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
