<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\DI\Container;

/**
 * Class ilResourceStorageInfoGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilResourceStorageInfoGUI
{
    protected ResourceIdentification $identification;
    protected \ILIAS\ResourceStorage\Services $storage;
    protected \ILIAS\ResourceStorage\Resource\StorableResource $resource;
    protected ilLanguage $language;
    /**
     * @var false
     */
    protected bool $is_storage = true;

    /**
     * ilResourceStorageInfoGUI constructor.
     * @param ResourceIdentification $identification
     */
    public function __construct(?ResourceIdentification $identification = null)
    {
        if (!$identification instanceof ResourceIdentification) {
            $this->is_storage = false;
        }
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->identification = $identification;
        $this->storage = $DIC->resourceStorage();
        $this->resource = $this->storage->manage()->getResource($this->identification);
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('irss');
    }

    public function append(ilInfoScreenGUI $info) : void
    {
        if ($this->is_storage) {
            $info->addSection($this->language->txt("storage_info"));
            $info->addProperty($this->language->txt("resource_id"), $this->identification->serialize());
            $info->addProperty($this->language->txt("storage_id"), $this->resource->getStorageID());
            $info->addProperty($this->language->txt("max_revision"), $this->resource->getMaxRevision());
            $info->addProperty($this->language->txt("stakeholders"), count($this->resource->getStakeholders()));
        }
    }

}
