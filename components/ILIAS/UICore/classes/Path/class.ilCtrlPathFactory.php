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
    public function find(ilCtrlContextInterface $context, $target): ilCtrlPathInterface
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
    public function existing(string $cid_path): ilCtrlPathInterface
    {
        return new ilCtrlExistingPath($this->structure, $cid_path);
    }

    /**
     * @inheritDoc
     */
    public function null(): ilCtrlPathInterface
    {
        return new ilCtrlNullPath();
    }
}
