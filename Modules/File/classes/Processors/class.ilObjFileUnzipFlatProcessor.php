<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileUnzipFlatProcessor
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjFileUnzipFlatProcessor extends ilObjFileAbstractZipProcessor
{
    public function process(ResourceIdentification $rid, array $options = []) : void
    {
        $this->openZip($rid);

        foreach ($this->getZipFiles() as $file_path) {
            if (substr($file_path, -1) !== DIRECTORY_SEPARATOR) {
                $rid = $this->storeZippedFile($file_path);
                // $options is ignored, as flat-unzip stores the content directly
                // within the provided parent object.
                $this->createFileObj($rid, $this->gui_object->getParentId());
            }
        }

        $this->closeZip();
    }
}
