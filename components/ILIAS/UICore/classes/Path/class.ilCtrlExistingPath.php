<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\UICore\Exceptions\ilCtrlPathException;

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
                throw new ilCtrlPathException('ilCtrl: invalid ' . ilCtrlInterface::PARAM_CID_PATH . ' parameter requested.');
            }
        }
    }
}
