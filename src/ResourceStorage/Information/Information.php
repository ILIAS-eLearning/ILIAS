<?php

namespace ILIAS\ResourceStorage\Information;

use DateTimeImmutable;

/**
 * Class Information
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Information
{

    /**
     * @return string
     */
    public function getTitle() : string;

    /**
     * @return string
     */
    public function getSuffix() : string;

    /**
     * @return string
     */
    public function getMimeType() : string;

    /**
     * @return int
     */
    public function getSize() : int;

    /**
     * @return DateTimeImmutable
     */
    public function getCreationDate() : DateTimeImmutable;
}
