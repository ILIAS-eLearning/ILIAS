<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Parser/class.ilBaseMailRfc822AddressParser.php';

/**
 * Class ilImapMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailImapRfc822AddressParser extends ilBaseMailRfc822AddressParser
{
    /**
     * {@inheritdoc}
     */
    protected function parseAddressString($a_addresses)
    {
        require_once 'Services/Mail/classes/class.ilMail.php';
        $parsed_addresses = imap_rfc822_parse_adrlist($a_addresses, ilMail::ILIAS_HOST);

        // #18992
        $valid_parsed_addresses = array_filter($parsed_addresses, function ($address) {
            return '.SYNTAX-ERROR.' != $address->host;
        });
        if ($parsed_addresses != $valid_parsed_addresses) {
            throw new ilMailException($a_addresses);
        }

        require_once 'Services/Mail/classes/Address/class.ilMailAddress.php';
        return array_map(function ($address) {
            return new ilMailAddress($address->mailbox, $address->host);
        }, $valid_parsed_addresses);
    }
}
