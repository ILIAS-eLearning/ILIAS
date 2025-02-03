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
 * Class ilCtrlNullPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlNullPath implements ilCtrlPathInterface
{
    /**
     * @inheritDoc
     */
    public function getCidPath(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCid(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNextCid(string $current_class): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCidPaths(int $order = SORT_DESC): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCidArray(int $order = SORT_DESC): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getException(): ?ilCtrlException
    {
        return null;
    }
}
