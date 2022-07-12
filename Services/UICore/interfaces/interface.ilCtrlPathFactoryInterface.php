<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Interface ilCtrlPathFactoryInterface describes the ilCtrl
 * Path factory.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlPathFactoryInterface
{
    /**
     * Returns the corresponding ilCtrlPath by the provided target type.
     *
     * @param ilCtrlContextInterface $context
     * @param string[]|string        $target
     * @return ilCtrlPathInterface
     */
    public function find(ilCtrlContextInterface $context, $target) : ilCtrlPathInterface;

    /**
     * Returns an instance of an existing ilCtrlPath.
     *
     * @param string $cid_path
     * @return ilCtrlPathInterface
     */
    public function existing(string $cid_path) : ilCtrlPathInterface;

    /**
     * Returns a pseudo instance of an ilCtrlPath.
     *
     * @return ilCtrlPathInterface
     */
    public function null() : ilCtrlPathInterface;
}
