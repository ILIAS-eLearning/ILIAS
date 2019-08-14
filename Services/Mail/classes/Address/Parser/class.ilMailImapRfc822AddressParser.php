<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailImapRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailImapRfc822AddressParser extends ilBaseMailRfc822AddressParser
{
    /**
     * @inheritdoc
     */
    protected function parseAddressString(string $addresses) : array
    {
        $parsedAddresses = imap_rfc822_parse_adrlist($addresses, $this->installationHost);

        // #18992
        $validParsedAddresses = array_filter($parsedAddresses, function ($address) {
            return '.SYNTAX-ERROR.' != $address->host;
        });

        if ($parsedAddresses != $validParsedAddresses) {
            throw new ilMailException($addresses);
        }

        return array_map(function ($address) {
            return new ilMailAddress($address->mailbox, $address->host);
        }, $validParsedAddresses);
    }
}