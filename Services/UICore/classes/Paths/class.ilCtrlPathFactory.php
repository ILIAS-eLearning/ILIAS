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
     * @param string $cid_path
     * @return ilCtrlPathInterface
     */
    public function byExistingPath(string $cid_path) : ilCtrlPathInterface
    {
        return new ilCtrlExistingPath($this->structure, $cid_path);
    }

    /**
     * @param ilCtrlContextInterface $context
     * @param string                 $target_class
     * @return ilCtrlPathInterface
     */
    public function bySingleClass(ilCtrlContextInterface $context, string $target_class) : ilCtrlPathInterface
    {
        return new ilCtrlSingleClassPath($this->structure, $context, $target_class);
    }

    /**
     * @param array $target_classes
     * @return ilCtrlPathInterface
     */
    public function byArrayClass(array $target_classes) : ilCtrlPathInterface
    {
        return new ilCtrlArrayClassPath($this->structure, $target_classes);
    }
}