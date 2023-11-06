<?php

declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * A password is used as part of credentials for authentication.
 * This is a quite specific kind of data - worth to be protected and
 * not to be confused by mistake.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Password
{
    private string $pass;

    public function __construct(string $pass)
    {
        $this->pass = $pass;
    }

    public function toString(): string
    {
        return $this->pass;
    }
}
