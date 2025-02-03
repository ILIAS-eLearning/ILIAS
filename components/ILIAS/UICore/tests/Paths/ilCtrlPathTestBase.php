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

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlPathTestBase
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathTestBase extends TestCase
{
    /**
     * @var ilCtrlStructureInterface
     */
    protected ilCtrlStructureInterface $structure;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $structure_artifact = require __DIR__ . '/../Data/Structure/test_ctrl_structure.php';
        $base_class_artifact = require __DIR__ . '/../Data/Structure/test_base_classes.php';

        $this->structure = new ilCtrlStructure(
            $structure_artifact,
            $base_class_artifact,
            []
        );
    }

    /**
     * @param string|null $cid_path
     * @return ilCtrlPathInterface
     */
    protected function getPath(?string $cid_path = null): ilCtrlPathInterface
    {
        return new class ($this->structure, $cid_path) extends ilCtrlAbstractPath {
            public function __construct(ilCtrlStructureInterface $structure, ?string $cid_path = null)
            {
                parent::__construct($structure);
                $this->cid_path = $cid_path;
            }
        };
    }
}
