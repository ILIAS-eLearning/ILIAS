<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailImapRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailImapRfc822AddressParser extends \ilBaseMailRfc822AddressParser
{
    /**
     * @inheritdoc
     */
    protected function parseAddressString(string $a_addresses) : array
    {
        $parsed_addresses = imap_rfc822_parse_adrlist($a_addresses, $this->installationHost);

        // #18992
        $valid_parsed_addresses = array_filter($parsed_addresses, function ($address) {
            return '.SYNTAX-ERROR.' != $address->host;
        });

        if ($parsed_addresses != $valid_parsed_addresses) {
            throw new \ilMailException($a_addresses);
        }

        return array_map(function ($address) {
            return new \ilMailAddress($address->mailbox, $address->host);
        }, $valid_parsed_addresses);
    }
}
