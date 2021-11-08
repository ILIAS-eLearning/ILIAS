<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailPearRfc822WrapperAddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailPearRfc822WrapperAddressParser extends ilBaseMailRfc822AddressParser
{
    protected function parseAddressString(string $addresses) : array
    {
        if ($addresses === '') {
            return [];
        }

        $parser = new Mail_RFC822();
        $parsed_addresses = $parser->parseAddressList(
            $addresses,
            $this->installationHost,
            false,
            true
        );

        return array_map(static function (stdClass $address) : ilMailAddress {
            return new ilMailAddress($address->mailbox, $address->host);
        }, $parsed_addresses);
    }
}
