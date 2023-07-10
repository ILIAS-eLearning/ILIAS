<?php

declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * Class ClientId
 * @package ILIAS\Data
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ClientId
{
    private string $clientId;

    /**
     * ClientId constructor.
     * @param string $clientId
     */
    public function __construct(string $clientId)
    {
        if ($clientId === '') {
            throw new \InvalidArgumentException('Empty $clientId');
        }

        if (preg_match('/[^A-Za-z0-9#_\.\-]/', $clientId)) {
            throw new \InvalidArgumentException('Invalid value for $clientId');
        }

        $this->clientId = $clientId;
    }

    public function toString(): string
    {
        return $this->clientId;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
