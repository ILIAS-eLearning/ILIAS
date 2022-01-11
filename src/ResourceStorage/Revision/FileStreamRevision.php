<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\Filesystem\Stream\FileStream;
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
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileStreamRevision extends FileRevision implements Revision
{

    private \ILIAS\Filesystem\Stream\FileStream $stream;
    protected bool $keep_original = true;

    /**
     * @inheritDoc
     */
    public function __construct(ResourceIdentification $identification, FileStream $stream, bool $keep_original = false)
    {
        $this->stream = $stream;
        $this->keep_original = $keep_original;
        parent::__construct($identification);
        $information = new FileInformation();
        $this->setInformation($information);
    }

    public function getStream() : FileStream
    {
        return $this->stream;
    }

    public function keepOriginal() : bool
    {
        return $this->keep_original;
    }

}
