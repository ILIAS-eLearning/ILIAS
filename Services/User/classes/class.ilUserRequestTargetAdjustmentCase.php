<?php declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilUserRequestTargetAdjustmentCase
 */
abstract class ilUserRequestTargetAdjustmentCase
{
    /** @var ilObjUser */
    protected $user;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ServerRequestInterface */
    protected $request;

    /**
     * @param ilObjUser              $user
     * @param ilCtrl                 $ctrl
     * @param ServerRequestInterface $request
     */
    public function __construct(ilObjUser $user, ilCtrl $ctrl, ServerRequestInterface $request)
    {
        $this->user    = $user;
        $this->ctrl    = $ctrl;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    abstract public function shouldStoreRequestTarget() : bool;

    /**
     * @return bool
     */
    abstract public function shouldAdjustRequest() : bool;

    /**
     * @return bool
     */
    abstract public function isInFulfillment() : bool;

    /**
     * @return void
     */
    abstract public function adjust() : void;
}
