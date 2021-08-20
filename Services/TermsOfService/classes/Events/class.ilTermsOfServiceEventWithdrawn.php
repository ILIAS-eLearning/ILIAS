<?php declare(strict_types=1);

/**
 * Class ilTermsOfServiceEventWithdrawn
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilTermsOfServiceEventWithdrawn
{
    private ilObjUser $user;

    public function __construct(ilObjUser $user)
    {
        $this->user = $user;
    }

    public function getUser() : ilObjUser
    {
        return $this->user;
    }
}
