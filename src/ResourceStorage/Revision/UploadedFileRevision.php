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

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * Class UploadedFileRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UploadedFileRevision extends FileRevision implements Revision
{
    private UploadResult $upload;

    /**
     * @inheritDoc
     */
    public function __construct(ResourceIdentification $identification, UploadResult $upload)
    {
        $this->upload = $upload;
        parent::__construct($identification);
        $information = new FileInformation();
        $information->setTitle($upload->getName());
        $information->setMimeType($upload->getMimeType());
        $information->setSuffix(pathinfo($upload->getName(), PATHINFO_EXTENSION));
        $information->setSize($upload->getSize());
        $information->setCreationDate(new \DateTimeImmutable());
        $this->setInformation($information);
    }


    public function getUpload(): UploadResult
    {
        return $this->upload;
    }
}
