<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

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
    protected function getPath(string $cid_path = null): ilCtrlPathInterface
    {
        return new class ($this->structure, $cid_path) extends ilCtrlAbstractPath {
            public function __construct(ilCtrlStructureInterface $structure, string $cid_path = null)
            {
                parent::__construct($structure);
                $this->cid_path = $cid_path;
            }
        };
    }
}
