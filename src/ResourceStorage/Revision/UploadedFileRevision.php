<?php declare(strict_types=1);

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

    /**
     * @var UploadResult
     */
    private $upload;


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


    /**
     * @return UploadResult
     */
    public function getUpload() : UploadResult
    {
        return $this->upload;
    }
}
