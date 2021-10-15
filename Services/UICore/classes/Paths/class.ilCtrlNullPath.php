<?php

/**
 * Class ilCtrlNullPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlNullPath implements ilCtrlPathInterface
{
    /**
     * @inheritDoc
     */
    public function getCidPath() : ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCid() : ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNextCid(string $current_class) : ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCidPaths(int $order = SORT_DESC) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCidArray(int $order = SORT_DESC) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass() : ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getException() : ?ilCtrlException
    {
        return null;
    }
}