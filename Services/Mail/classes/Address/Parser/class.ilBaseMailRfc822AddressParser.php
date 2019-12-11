<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/interfaces/interface.ilMailRecipientParser.php';

/**
 * Class ilBaseMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailRfc822AddressParser implements ilMailRecipientParser
{
    /**
     * @var string
     */
    protected $addresses = '';

    /**
     * @param string $a_addresses
     */
    public function __construct($a_addresses)
    {
        $this->addresses = $a_addresses;
    }

    /**
     * @return string
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param string $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * @param string $a_addresses
     * @return ilMailAddress[]
     */
    abstract protected function parseAddressString($a_addresses);

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $addresses = preg_replace('/;/', ',', trim($this->addresses));
        return $this->parseAddressString($addresses);
    }
}
