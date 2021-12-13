<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Code;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DataFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @param string $code
     * @return Code
     */
    public function code(string $code) : Code
    {
        return new Code($code);
    }
}
