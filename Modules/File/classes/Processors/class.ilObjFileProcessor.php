<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjFileProcessor extends ilObjFileAbstractProcessor
{
    /**
     * @inheritDoc
     */
    public function process(ResourceIdentification $rid, int $parent_id, array $options = []) : void
    {
        $this->createFileObj($rid, $parent_id, $options);
    }
}