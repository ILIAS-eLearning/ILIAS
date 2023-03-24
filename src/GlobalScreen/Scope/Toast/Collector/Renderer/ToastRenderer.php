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

namespace ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer;

use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isItem;
use ILIAS\UI\Component\Toast\Toast;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ToastRenderer
{
    /**
     * Returns the UI Component for the past item
     * @param isItem $item
     * @return Toast
     */
    public function getToastComponentForItem(isItem $item): Component;
}
