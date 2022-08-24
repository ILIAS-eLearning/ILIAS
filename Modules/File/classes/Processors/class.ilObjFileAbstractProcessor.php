<?php

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
    protected Services $storage;
    protected ResourceStakeholder $stakeholder;
    protected ilObjFileGUI $gui_object;

    public function __construct(
        ResourceStakeholder $stakeholder,
        ilObjFileGUI $gui_object,
        Services $storage
    ) {
        $this->storage = $storage;
        $this->stakeholder = $stakeholder;
        $this->gui_object = $gui_object;
    }

    /**
     * Creates an ilObjFile instance for the provided information.
     * @see ilObjFileAbstractProcessorInterface::OPTIONS
     */
    protected function createFileObj(ResourceIdentification $rid, int $parent_id, array $options = []): ilObjFile
    {
        $revision = $this->storage->manage()->getCurrentRevision($rid);
        $file_obj = new ilObjFile();
        $file_obj->setResourceId($rid);
        $file_obj->setTitle($revision->getInformation()->getTitle());
        $file_obj->setFileName($revision->getInformation()->getTitle());
        $file_obj->setVersion($revision->getVersionNumber());

        if (!empty($options)) {
            $this->applyOptions($file_obj, $options);
        }

        $file_obj->create();
        $file_obj->createReference();

        ilPreview::createPreview($file_obj, true);

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
}
