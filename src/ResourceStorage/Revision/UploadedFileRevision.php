<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class UploadedFileRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UploadedFileRevision extends FileRevision implements Revision
{

    private \ILIAS\FileUpload\DTO\UploadResult $upload;


    /**
     * @inheritDoc
     */
    public function __construct(ResourceIdentification $identification, UploadResult $result)
    {
        $this->upload = $result;
        parent::__construct($identification);
        $information = new FileInformation();
        $information->setTitle($result->getName());
        $information->setMimeType($result->getMimeType());
        $information->setSuffix(pathinfo($result->getName(), PATHINFO_EXTENSION));
        $information->setSize($result->getSize());
        $information->setCreationDate(new \DateTimeImmutable());
        $this->setInformation($information);
    }


    public function getUpload() : UploadResult
    {
        return $this->upload;
    }
}
