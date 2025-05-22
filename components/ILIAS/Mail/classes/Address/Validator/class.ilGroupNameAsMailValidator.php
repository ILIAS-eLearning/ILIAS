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

class ilGroupNameAsMailValidator
{
    /** @var callable(string): bool */
    protected $group_name_check_callable;

    /**
     * @param callable(string): bool|null $group_name_check_callable
     */
    public function __construct(protected string $host, ?callable $group_name_check_callable = null)
    {
        $this->group_name_check_callable = $group_name_check_callable ?? static fn(string $group_name): bool => ilUtil::groupNameExists($group_name);
    }

    /**
     * Validates if the given address contains a valid group name to send an email
     */
    public function validate(ilMailAddress $address): bool
    {
        $group_name = substr($address->getMailbox(), 1);

        $func = $this->group_name_check_callable;
        return $func($group_name) && $this->isHostValid($address->getHost());
    }

    private function isHostValid(string $host): bool
    {
        return ($host === $this->host || $host === '');
    }
}
