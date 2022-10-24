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

/**
 * Class ilObjFileUnzipFlatProcessor
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjFileUnzipFlatProcessor extends ilObjFileAbstractZipProcessor
{
    public function process(ResourceIdentification $rid, array $options = []): void
    {
        $this->openZip($rid);

        foreach ($this->getZipFiles() as $file_path) {
            if (substr($file_path, -1) !== DIRECTORY_SEPARATOR) {
                $rid = $this->storeZippedFile($file_path);
                // $options is ignored, as flat-unzip stores the content directly
                // within the provided parent object.
                $this->createFileObj($rid, $this->gui_object->getParentId(), [], true);
            }
        }

        $this->closeZip();
    }
}
