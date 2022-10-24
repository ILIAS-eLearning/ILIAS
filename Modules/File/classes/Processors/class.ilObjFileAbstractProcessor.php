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
        array $options = [],
        bool $create_reference = false
    ): ilObjFile {
        $revision = $this->storage->manage()->getCurrentRevision($rid);
        $file_obj = new ilObjFile();
        $file_obj->setResourceId($rid);
        if ($this->page_counter->isAvailable()) {
            $file_obj->setPageCount($this->page_counter->extractAmountOfPagesByRID($rid) ?? 0);
        }
        $title = $revision->getInformation()->getTitle();
        if (!$this->policy->isValidExtension($revision->getInformation()->getSuffix())) {
            $this->invalid_file_names[] = $title;
        }
        $file_obj->setTitle($title);
        $file_obj->setFileName($title);
        $file_obj->setVersion($revision->getVersionNumber());

        if (!empty($options)) {
            $this->applyOptions($file_obj, $options);
        }

        $file_obj->create();

        ilPreview::createPreview($file_obj, true);
        if ($create_reference) {
            $file_obj->createReference();
        }
        $this->gui_object->putObjectInTree($file_obj, $parent_id);

        return $file_obj;
    }

    /**
     * Apply provided options to the given object.
     */
    protected function applyOptions(ilObject $obj, array $options): void
    {
        foreach ($options as $key => $option) {
            if (in_array($key, self::OPTIONS, true)) {
                if (!empty($option)) {
                    $setter = "set" . ucfirst($key);
                    $obj->{$setter}($option);
                }
            } else {
                throw new LogicException("Option '$key' is not declared in " . static::class . "::OPTIONS.");
            }
        }
    }

    public function getInvalidFileNames(): array
    {
        return $this->invalid_file_names;
    }
}
