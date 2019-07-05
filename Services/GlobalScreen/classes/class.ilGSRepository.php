<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilGSRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSRepository
{

    const PURPOSE_MAIN_MENU = 'mainmenu';
    /**
     * @var StorageFacade
     */
    private $storage;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $global_screen_services;


    /**
     * ilGSRepository constructor.
     *
     * @param StorageFacade $storage
     */
    public function __construct(StorageFacade $storage)
    {
        global $DIC;
        $this->storage = $storage;
        $this->global_screen_services = $DIC->globalScreen();
    }


    /**
     * @param string $purpose
     *
     * @return IdentificationInterface[]
     */
    public function getIdentificationsForPurpose(string $purpose) : array
    {
        /**
         * @var $identification_storage ilGSIdentificationStorage
         */
        $identifications = [];
        /*
         * innerjoinAR(new ilGSProviderStorage(), 'provider_class', 'provider_class', ['purpose'])->where(
                "il_gs_providers.purpose = " . $this->storage->db()->quote($purpose, 'text')
            )->
         */
        foreach (
            ilGSIdentificationStorage::get() as $identification_storage
        ) {
            $identifications[] = $this->getIdentificationFromStorage($identification_storage);
        };

        return $identifications;
    }


    /**
     * @param ilGSIdentificationStorage $storage
     *
     * @return IdentificationInterface
     */
    private function getIdentificationFromStorage(ilGSIdentificationStorage $storage) : IdentificationInterface
    {
        return $this->global_screen_services->identification()->fromSerializedIdentification($storage->getIdentification());
    }
}
