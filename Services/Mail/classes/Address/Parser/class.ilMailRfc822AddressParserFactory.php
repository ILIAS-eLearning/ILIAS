<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailRfc822AddressParserFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRfc822AddressParserFactory
{
    /**
     * @param string $a_address
     * @return \ilMailRecipientParser
     */
    public function getParser(string $a_address) : \ilMailRecipientParser
    {
        return new \ilMailRfc822AddressParser(new \ilMailPearRfc822WrapperAddressParser($a_address));
    }
}
