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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class TypeRenderer
 * Every Type should have a renderer, if you won't provide on in your
 * TypeInformation, a BaseTypeRenderer is used.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface TypeRenderer
{
    /**
     * @param isItem $item
     * @param bool   $with_content
     * @return Component
     */
    public function getComponentForItem(isItem $item, bool $with_content = true) : Component;

    /**
     * This is called in cases when the Full Item with it's content is needed,
     * e.g. for Items which do not support async loading or for async-items when
     * it's content is relevant during an async call.
     * @param isItem $item
     * @return Component
     */
    public function getComponentWithContent(isItem $item) : Component;

    /**
     * This is called when only the relevant part of the item is needed during
     * the synchronous rendering of the MainBar. Async-Items can therefore render
     * itself without their content
     * @param isItem $item
     * @return Component
     */
    public function getComponentWithoutContent(isItem $item) : Component;
}
