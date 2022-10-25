<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\DI\Container;

/**
 * Class ilResourceStorageInfoGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilResourceStorageInfoGUI
{
    protected ?ResourceIdentification $identification = null;
    protected \ILIAS\ResourceStorage\Services $storage;
    protected \ILIAS\ResourceStorage\Resource\StorableResource $resource;
    protected ilLanguage $language;
    protected bool $is_storage = true;

    /**
     * ilResourceStorageInfoGUI constructor.
     * @param ResourceIdentification $identification
     */
    public function __construct(?ResourceIdentification $identification = null)
    {
        global $DIC;
        $this->storage = $DIC->resourceStorage();
        /**
         * @var $DIC Container
         */
        if (!$identification instanceof ResourceIdentification) {
            $this->is_storage = false;
        } else {
            $this->is_storage = true;
            $this->identification = $identification;
            $this->resource = $this->storage->manage()->getResource($this->identification);
        }

        $this->language = $DIC->language();
        $this->language->loadLanguageModule('irss');
    }

    public function append(ilInfoScreenGUI $info): void
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
