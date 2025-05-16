<?php

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

declare(strict_types=1);

class ilMailOnlyExternalAddressList implements ilMailAddressList
{
    /** @var callable(string): int */
    protected $get_usr_id_by_login_callable;

    /**
     * @param callable(string): int $get_usr_id_by_login_callable A callable which accepts a string as argument and returns an integer >= 0
     */
    public function __construct(
        protected ilMailAddressList $origin,
        protected string $installation_host,
        callable $get_usr_id_by_login_callable
    ) {
        $this->get_usr_id_by_login_callable = $get_usr_id_by_login_callable;
    }

    public function value(): array
    {
        $addresses = $this->origin->value();

        return array_filter($addresses, function (ilMailAddress $address): bool {
            if (($this->get_usr_id_by_login_callable)((string) $address)) {
                // Fixed mantis bug #5875
                return false;
            }

            if ($address->getHost() === $this->installation_host) {
                return false;
            }

            return !str_starts_with($address->getMailbox(), '#');
        });
    }
}
