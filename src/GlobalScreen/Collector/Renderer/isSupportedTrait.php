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
namespace ILIAS\GlobalScreen\Collector\Renderer;

use ILIAS\UI\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link\Bulky as BulkyLink;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Trait isSupportedTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait isSupportedTrait
{
    /**
     * @param Component $component
     * @return bool
     */
    protected function isComponentSupportedForCombinedSlate(Component $component) : bool
    {
        return ($component instanceof BulkyButton || $component instanceof Slate || $component instanceof BulkyLink || $component instanceof Horizontal);
    }

    /**
     * @param Component $component
     * @return bool
     */
    protected function isSupportedForMetaBar(Component $component) : bool
    {
        return ($component instanceof BulkyButton || $component instanceof Slate);
    }
}
