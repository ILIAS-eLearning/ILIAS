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

abstract class ilBaseMailRfc822AddressParser implements ilMailRecipientParser
{
    public function __construct(protected string $addresses, protected string $installation_host = ilMail::ILIAS_HOST)
    {
    }

    public function getAddresses(): string
    {
        return $this->addresses;
    }

    /**
     * @return list<ilMailAddress>
     */
    abstract protected function parseAddressString(string $addresses): array;

    public function parse(): array
    {
        $addresses = preg_replace('/;/', ',', trim($this->addresses));

        return $this->parseAddressString($addresses);
    }
}
