<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Parser/class.ilBaseMailRfc822AddressParser.php';

/**
 * Class ilMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRfc822AddressParser extends ilBaseMailRfc822AddressParser
{
    /**
     * @var ilBaseMailRfc822AddressParser
     */
    protected $aggregated_parser;

    /**
     * @param ilBaseMailRfc822AddressParser $a_addresses
     */
    public function __construct(ilBaseMailRfc822AddressParser $a_addresses)
    {
        parent::__construct($a_addresses->getAddresses());
        $this->aggregated_parser = $a_addresses;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAddressString($a_addresses)
    {
        // @todo: Use an own ILIAS parser instead
        return $this->aggregated_parser->parse();
    }
}
