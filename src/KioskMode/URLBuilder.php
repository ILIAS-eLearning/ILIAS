<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\Data;

/**
 * The URLBuilder allows views to get links that are used somewhere inline in
 * the content.
 */
interface URLBuilder
{
    /**
     * Get an URL for the provided command and params.
     */
    public function getURL(string $command, int $param = null) : Data\URI;
}
