<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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
