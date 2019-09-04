<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailRfc822AddressParser implements ilMailRecipientParser
{
    /** @var string */
    protected $addresses = '';

    /** @var string */
    protected $installationHost = '';

    /**
     * @param string $addresses A comma separated list of email addresses
     * @param string $installationHost
     */
    public function __construct(string $addresses, string $installationHost = ilMail::ILIAS_HOST)
    {
        $this->addresses = $addresses;
        $this->installationHost = $installationHost;
    }

    /**
     * @return string
     */
    public function getAddresses() : string
    {
        return $this->addresses;
    }

    /**
     * A comma separated list of email addresses
     * @param string $addresses
     */
    public function setAddresses(string $addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * @param string $addresses A comma separated list of email addresses
     * @return ilMailAddress[]
     */
    protected abstract function parseAddressString(string $addresses) : array;

    /**
     * @inheritdoc
     */
    public function parse() : array
    {
        $addresses = preg_replace('/;/', ',', trim($this->addresses));

        return $this->parseAddressString($addresses);
    }
}