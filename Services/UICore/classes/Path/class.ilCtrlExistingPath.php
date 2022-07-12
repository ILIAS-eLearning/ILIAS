<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlExistingPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlExistingPath extends ilCtrlAbstractPath
{
    /**
     * ilCtrlExistingPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param string                   $cid_path
     */
    public function __construct(ilCtrlStructureInterface $structure, string $cid_path)
    {
        parent::__construct($structure);

        $this->cid_path = $cid_path;
    }
}
