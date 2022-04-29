<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlPathFactory
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathFactory implements ilCtrlPathFactoryInterface
{
    /**
     * @var ilCtrlStructureInterface
     */
    private ilCtrlStructureInterface $structure;

    /**
     * ilCtrlPathFactory Constructor
     *
     * @param ilCtrlStructureInterface $structure
     */
    public function __construct(ilCtrlStructureInterface $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @inheritDoc
     */
    public function find(ilCtrlContextInterface $context, $target) : ilCtrlPathInterface
    {
        if (is_array($target)) {
            return new ilCtrlArrayClassPath($this->structure, $context, $target);
        }

        if (is_string($target)) {
            return new ilCtrlSingleClassPath($this->structure, $context, $target);
        }

        return $this->null();
    }

    /**
     * @inheritDoc
     */
    public function existing(string $cid_path) : ilCtrlPathInterface
    {
        return new ilCtrlExistingPath($this->structure, $cid_path);
    }

    /**
     * @inheritDoc
     */
    public function null() : ilCtrlPathInterface
    {
        return new ilCtrlNullPath();
    }
}
