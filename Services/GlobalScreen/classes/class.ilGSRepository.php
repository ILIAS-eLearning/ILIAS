<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilGSRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSRepository extends ilMMAbstractRepository {

	const PURPOSE_MAIN_MENU = 'mainmenu';
	/**
	 * @var \ILIAS\GlobalScreen\Services
	 */
	private $global_screen_services;


	/**
	 * ilGSRepository constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		global $DIC;
		parent::__construct($storage);
		$this->global_screen_services = $DIC->globalScreen();
	}


	/**
	 * @param string $purpose
	 *
	 * @return IdentificationInterface[]
	 */
	public function getIdentificationsForPurpose(string $purpose): array {
		/**
		 * @var $identification_storage ilGSIdentificationStorage
		 */
		$identifications = [];
		foreach (
			ilGSIdentificationStorage::innerjoinAR(new ilGSProviderStorage(), 'provider_class', 'provider_class', ['purpose'])->where(
				"il_gs_providers.purpose = " . $this->db->quote($purpose, 'text')
			)->get() as $identification_storage
		) {
			$identifications[] = $this->getIdentificationForStorage($identification_storage);
		};

		return $identifications;
	}


	/**
	 * @param ilGSIdentificationStorage $storage
	 *
	 * @return IdentificationInterface
	 */
	private function getIdentificationForStorage(ilGSIdentificationStorage $storage): IdentificationInterface {
		return $this->global_screen_services->identification()->fromSerializedIdentification($storage->getIdentification());
	}
}
