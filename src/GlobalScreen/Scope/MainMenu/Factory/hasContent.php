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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use Closure;
use ILIAS\UI\Component\Component;

/**
 * Interface hasContent
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasContent
{
    /**
     * @param Closure $content_wrapper a closure which returns a UI-Component
     *                                 This wins over a withContent
     * @return hasContent
     */
    public function withContentWrapper(Closure $content_wrapper) : hasContent;

    /**
     * @param Component $ui_component
     * @return hasContent
     * @deprecated Use withContentWrapper instead
     */
    public function withContent(Component $ui_component) : hasContent;

    /**
     * @return Component
     */
    public function getContent() : Component;
}
