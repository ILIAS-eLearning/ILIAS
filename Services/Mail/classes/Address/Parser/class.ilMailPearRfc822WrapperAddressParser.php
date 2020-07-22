<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailPearRfc822WrapperAddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailPearRfc822WrapperAddressParser extends \ilBaseMailRfc822AddressParser
{
    /**
     * @inheritdoc
     */
    public function parseAddressString(string $a_addresses) : array
    {
        if (strlen($a_addresses) == 0) {
            return [];
        }

        $parser = new \Mail_RFC822();
        $parsed_addresses = $parser->parseAddressList($a_addresses, $this->installationHost, false, true);

        return array_map(function ($address) {
            return new \ilMailAddress($address->mailbox, $address->host);
        }, $parsed_addresses);
    }
}
