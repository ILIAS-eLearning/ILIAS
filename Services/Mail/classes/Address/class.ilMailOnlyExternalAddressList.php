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
 * Class ilMailOnlyExternalAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOnlyExternalAddressList implements ilMailAddressList
{
    protected ilMailAddressList $origin;
    protected string $installationHost;
    /** @var callable */
    protected $getUsrIdByLoginCallable;

    /**
     * @param callable $getUsrIdByLoginCallable A callable which accepts a string as argument
     *                                          and returns an integer >= 0
     */
    public function __construct(
        ilMailAddressList $origin,
        string $installationHost,
        callable $getUsrIdByLoginCallable
    ) {
        $this->origin = $origin;
        $this->installationHost = $installationHost;
        $this->getUsrIdByLoginCallable = $getUsrIdByLoginCallable;
    }

    public function value() : array
    {
        $addresses = $this->origin->value();

        $filteredAddresses = array_filter($addresses, function (ilMailAddress $address) : bool {
            $c = $this->getUsrIdByLoginCallable;
            if ($c((string) $address)) {
                // Fixed mantis bug #5875
                return false;
            }

            if ($address->getHost() === $this->installationHost) {
                return false;
            }

            if (strpos($address->getMailbox(), '#') === 0) {
                return false;
            }

            return true;
        });

        return $filteredAddresses;
    }
}
