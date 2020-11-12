<?php declare(strict_types=1);

/**
 * Class ilTermsOfServiceEventWithdrawn
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilTermsOfServiceEventWithdrawn
{
    /** @var ilObjUser */
    private $user;

    /**
     * ilTermsOfServiceEventWithdrawn constructor.
     * @param ilObjUser $user
     */
    public function __construct(ilObjUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return ilObjUser
     */
    public function getUser() : ilObjUser
    {
        return $this->user;
    }
}
