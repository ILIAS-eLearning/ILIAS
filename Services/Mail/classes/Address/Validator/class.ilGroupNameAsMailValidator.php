<?php

declare(strict_types=1);

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
 * Class ilGroupNameAsMailValidator
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGroupNameAsMailValidator
{
    /** @var callable */
    protected $groupNameCheckCallable;

    public function __construct(protected string $host, callable $groupNameCheckCallable = null)
    {
        if (null === $groupNameCheckCallable) {
            $groupNameCheckCallable = static function (string $groupName): bool {
                return ilUtil::groupNameExists($groupName);
            };
        }

        $this->groupNameCheckCallable = $groupNameCheckCallable;
    }

    /**
     * Validates if the given address contains a valid group name to send an email
     */
    public function validate(ilMailAddress $address): bool
    {
        $groupName = substr($address->getMailbox(), 1);

        $func = $this->groupNameCheckCallable;
        return $func($groupName) && $this->isHostValid($address->getHost());
    }

    private function isHostValid(string $host): bool
    {
        return ($host === $this->host || $host === '');
    }
}
