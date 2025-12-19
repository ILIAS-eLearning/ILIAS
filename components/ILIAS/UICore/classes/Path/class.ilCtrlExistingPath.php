<?php

declare(strict_types=1);

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
        $this->ensureValidCidPath();
    }

    /**
     * Ensures each consecutive pair of CIDs must have a valid parent–child relationship.
     * CID-paths with <= 1 CID are to be considered valid.
     */
    protected function ensureValidCidPath(): void
    {
        $cid_array = $this->getCidArray(SORT_ASC);
        $cid_count = count($cid_array);
        if ($cid_count <= 1) {
            return;
        }
        for ($current = 0, $max = ($cid_count - 1); $current < $max; $current++) {
            $parent_cid = $cid_array[$current];
            $child_cid = $cid_array[$current + 1];
            $child_class = $this->structure->getClassNameByCid($child_cid);
            $allowed_children = $this->structure->getChildrenByCid($parent_cid) ?? [];
            if (null === $child_class || !in_array($child_class, $allowed_children, true)) {
                throw new RuntimeException('ilCtrl: invalid ' . ilCtrlInterface::PARAM_CID_PATH . ' parameter requested.');
            }
        }
    }
}
