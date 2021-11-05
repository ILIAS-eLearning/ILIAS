<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureCidGenerator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureCidGenerator
{
    /**
     * @var int
     */
    private int $index;

    /**
     * ilCtrlStructureCidGenerator Constructor
     *
     * @param int $starting_index
     */
    public function __construct(int $starting_index = 0)
    {
        $this->index = $starting_index;
    }

    /**
     * Returns the index of a given cid.
     *
     * @param string $cid
     * @return int
     */
    public function getIndexByCid(string $cid) : int
    {
        return (int) base_convert($cid, 36, 10);
    }

    /**
     * Returns the cid for a given index.
     *
     * @param int $index
     * @return string
     */
    public function getCidByIndex(int $index) : string
    {
        return base_convert((string) $index, 10, 36);
    }

    /**
     * Returns the next available cid.
     *
     * @return string
     */
    public function getCid() : string
    {
        return $this->getCidByIndex($this->index++);
    }
}