<?php declare(strict_types=1);

/**
 * Class ilTermsOfServiceEventWithdrawn
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilTermsOfServiceEventWithdrawn
{
    /** @var int */
    private $usrId;

    /**
     * ilTermsOfServiceEventWithdrawn constructor.
     * @param int $usrId
     */
    public function __construct(int $usrId)
    {
        $this->usrId = $usrId;
    }

    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return $this->usrId;
    }
}
