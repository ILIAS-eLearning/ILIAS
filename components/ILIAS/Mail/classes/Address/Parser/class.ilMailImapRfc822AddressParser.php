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

class ilMailImapRfc822AddressParser extends ilBaseMailRfc822AddressParser
{
    protected function parseAddressString(string $addresses): array
    {
        $parsed_addresses = imap_rfc822_parse_adrlist($addresses, $this->installation_host);

        // #18992
        $valid_parsed_addresses = array_filter($parsed_addresses, static function (stdClass $address): bool {
            return $address->host !== '.SYNTAX-ERROR.';
        });

        if ($parsed_addresses !== $valid_parsed_addresses) {
            throw new ilMailException($addresses);
        }

        return array_map(static function (stdClass $address): ilMailAddress {
            return new ilMailAddress($address->mailbox, $address->host);
        }, $valid_parsed_addresses);
    }
}
