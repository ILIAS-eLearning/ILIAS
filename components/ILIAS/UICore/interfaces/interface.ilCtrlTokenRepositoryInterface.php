<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Interface ilCtrlTokenRepositoryInterface describes an ilCtrl token.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTokenRepositoryInterface
{
    /**
     * Returns a temporary ilCtrlToken for the given session (id).
     *
     * @return ilCtrlTokenInterface
     */
    public function getToken(): ilCtrlTokenInterface;
}
