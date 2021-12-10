<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Service
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Internal service, do not use in other components
     * @return InternalService
     */
    public function internal()
    {
        return new InternalService();
    }
}
