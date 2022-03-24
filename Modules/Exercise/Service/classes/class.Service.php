<?php declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Service
{
    public function internal() : InternalService
    {
        return new InternalService();
    }
}
