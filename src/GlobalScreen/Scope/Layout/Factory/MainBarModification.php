<?php

declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use ILIAS\UI\Component\MainControls\MainBar as UIMainBar;

/**
 * Class MainBar
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainBarModification extends AbstractLayoutModification implements LayoutModification
{
    /**
     * @inheritDoc
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentType(): string
    {
        return UIMainBar::class;
    }

    /**
     * @inheritDoc
     */
    public function getClosureReturnType(): string
    {
        return UIMainBar::class;
    }

    /**
     * @inheritDoc
     */
    public function firstArgumentAllowsNull(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function returnTypeAllowsNull(): bool
    {
        return true;
    }
}
