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
