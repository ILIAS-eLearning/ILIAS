<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Information;

use DateTimeImmutable;

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
 * Class Information
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileInformation implements Information
{

    protected string $title = '';
    protected string $suffix = '';
    protected string $mime_type = '';
    protected int $size = 0;
    protected ?\DateTimeImmutable $creation_date = null;

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix) : self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getMimeType() : string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type) : self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function setSize(int $size) : self
    {
        $this->size = $size;

        return $this;
    }

    public function getCreationDate() : DateTimeImmutable
    {
        return $this->creation_date ?? new DateTimeImmutable();
    }

    public function setCreationDate(DateTimeImmutable $creation_date) : void
    {
        $this->creation_date = $creation_date;
    }
}
