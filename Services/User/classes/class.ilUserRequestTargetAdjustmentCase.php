<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserRequestTargetAdjustmentCase
 */
abstract class ilUserRequestTargetAdjustmentCase
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @param ilObjUser $user
     * @param ilCtrl    $ctrl
     */
    public function __construct(ilObjUser $user, ilCtrl $ctrl)
    {
        $this->user = $user;
        $this->ctrl = $ctrl;
    }
    /**
     * @return boolean
     */
    abstract public function shouldStoreRequestTarget();

    /**
     * @return boolean
     */
    abstract public function shouldAdjustRequest();

    /**
     * @return boolean
     */
    abstract public function isInFulfillment();

    /**
     * @return void
     */
    abstract public function adjust();
}
