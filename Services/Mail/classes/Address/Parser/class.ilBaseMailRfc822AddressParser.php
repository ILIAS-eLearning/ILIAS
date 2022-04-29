<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBaseMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailRfc822AddressParser implements ilMailRecipientParser
{
    /**
     * @var string A comma separated list of email addresses
     */
    protected string $addresses = '';
    protected string $installationHost = '';

    /**
     * @param string $addresses A comma separated list of email addresses
     */
    public function __construct(string $addresses, string $installationHost = ilMail::ILIAS_HOST)
    {
        $this->addresses = $addresses;
        $this->installationHost = $installationHost;
    }

    /**
     * @return string A comma separated list of email addresses
     */
    public function getAddresses() : string
    {
        return $this->addresses;
    }

    /**
     * @param string $addresses A comma separated list of email addresses
     * @return ilMailAddress[]
     */
    abstract protected function parseAddressString(string $addresses) : array;

    public function parse() : array
    {
        $addresses = preg_replace('/;/', ',', trim($this->addresses));

        return $this->parseAddressString($addresses);
    }
}
