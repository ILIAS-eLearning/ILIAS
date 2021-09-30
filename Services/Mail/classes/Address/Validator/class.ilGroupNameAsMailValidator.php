<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilGroupNameAsMailValidator
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGroupNameAsMailValidator
{
    protected string $host;
    /** @var callable */
    protected $groupNameCheckCallable;

    
    public function __construct(string $host, callable $groupNameCheckCallable = null)
    {
        $this->host = $host;

        if (null === $groupNameCheckCallable) {
            $groupNameCheckCallable = static function (string $groupName) : bool {
                return ilUtil::groupNameExists($groupName);
            };
        }

        $this->groupNameCheckCallable = $groupNameCheckCallable;
    }

    /**
     * Validates if the given address contains a valid group name to send an email
     */
    public function validate(ilMailAddress $address) : bool
    {
        $groupName = substr($address->getMailbox(), 1);

        $func = $this->groupNameCheckCallable;
        return $func($groupName) && $this->isHostValid($address->getHost());
    }

    private function isHostValid(string $host) : bool
    {
        return ($host === $this->host || $host === '');
    }
}
