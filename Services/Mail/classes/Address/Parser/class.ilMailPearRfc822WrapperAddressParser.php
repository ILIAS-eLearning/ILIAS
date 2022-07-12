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
 * Class ilMailPearRfc822WrapperAddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailPearRfc822WrapperAddressParser extends ilBaseMailRfc822AddressParser
{
    protected function parseAddressString(string $addresses) : array
    {
        if ($addresses === '') {
            return [];
        }

        $parser = new Mail_RFC822();
        $parsed_addresses = $parser->parseAddressList(
            $addresses,
            $this->installationHost,
            false,
            true
        );

        return array_map(static function (stdClass $address) : ilMailAddress {
            return new ilMailAddress($address->mailbox, $address->host);
        }, $parsed_addresses);
    }
}
