<?php

/**
 * Class ilCtrlPathFactory
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathFactory
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
     * @param ilCtrlContextInterface $context
     * @param string[]|string        $target
     * @return ilCtrlPathInterface
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
     * @param string $cid_path
     * @return ilCtrlPathInterface
     */
    public function existing(string $cid_path) : ilCtrlPathInterface
    {
        return new ilCtrlExistingPath($this->structure, $cid_path);
    }

    /**
     * @return ilCtrlPathInterface
     */
    public function null() : ilCtrlPathInterface
    {
        return new ilCtrlNullPath();
    }
}