<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjFileProcessor extends ilObjFileAbstractProcessor
{
    public function process(ResourceIdentification $rid, array $options = []): void
    {
        $this->createFileObj($rid, $this->gui_object->getParentId(), $options);
    }
}
