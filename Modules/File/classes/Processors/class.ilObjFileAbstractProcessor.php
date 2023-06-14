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
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Services;

/**
 * Class ilObjFileAbstractProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilObjFileAbstractProcessor implements ilObjFileProcessorInterface
{
    protected ilFileServicesPolicy $policy;
    protected ilFileServicesSettings $settings;
    protected ilCountPDFPages $page_counter;
    protected Services $storage;
    protected ResourceStakeholder $stakeholder;
    protected ilObjFileGUI $gui_object;
    protected array $invalid_file_names = [];

    public function __construct(
        ResourceStakeholder $stakeholder,
        ilObjFileGUI $gui_object,
        Services $storage,
        ilFileServicesSettings $settings
    ) {
        $this->storage = $storage;
        $this->stakeholder = $stakeholder;
        $this->gui_object = $gui_object;
        $this->page_counter = new ilCountPDFPages();
        $this->settings = $settings;
        $this->policy = new ilFileServicesPolicy($this->settings);
    }

    /**
     * Creates an ilObjFile instance for the provided information.
     * @see ilObjFileAbstractProcessorInterface::OPTIONS
     */
    protected function createFileObj(
        ResourceIdentification $rid,
        int $parent_id,
        string $title = null,
        string $description = null,
        int $copyright_id = null,
        bool $create_reference = false
    ): ilObjFile {
        $revision = $this->storage->manage()->getCurrentRevision($rid);
        $file_obj = new ilObjFile();
        $file_obj->setResourceId($rid);
        if ($this->page_counter->isAvailable()) {
            $file_obj->setPageCount($this->page_counter->extractAmountOfPagesByRID($rid) ?? 0);
        }
        $revision_title = $revision->getInformation()->getTitle();
        if (!$this->policy->isValidExtension($revision->getInformation()->getSuffix())) {
            $this->invalid_file_names[] = $revision_title;
        }
        $file_obj->setTitle($title ?? $revision_title);
        if ($description !== null) {
            $file_obj->setDescription($description);
        }
        $file_obj->setVersion($revision->getVersionNumber());
        $file_obj->setCopyrightID($copyright_id);

        $file_obj->create();

        if ($create_reference) {
            $file_obj->createReference();
        }

        $file_obj->processAutoRating();
        $this->gui_object->putObjectInTree($file_obj, $parent_id);

        return $file_obj;
    }

    public function getInvalidFileNames(): array
    {
        return $this->invalid_file_names;
    }
}
