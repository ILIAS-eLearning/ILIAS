<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRfc822AddressParser extends ilBaseMailRfc822AddressParser
{
    /**
     * @var ilBaseMailRfc822AddressParser
     */
    protected $aggregatedParser;

    /**
     * @param ilBaseMailRfc822AddressParser $addresses
     */
    public function __construct(ilBaseMailRfc822AddressParser $addresses)
    {
        parent::__construct($addresses->getAddresses());
        $this->aggregatedParser = $addresses;
    }

    /**
     * @inheritdoc
     */
    protected function parseAddressString(string $addresses) : array
    {
        return $this->aggregatedParser->parse();
    }
}