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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface isParent
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isParent extends isItem
{
    /**
     * @return isItem[]
     */
    public function getChildren(): array;

    /**
     * @param isItem[] $children
     * @return isParent
     */
    public function withChildren(array $children): isParent;

    /**
     * Attention
     *
     * @param isItem $child
     * @return isParent
     */
    public function appendChild(isItem $child): isParent;

    /**
     * @param isItem $child_to_remove
     * @return isParent
     */
    public function removeChild(isItem $child_to_remove): isParent;

    /**
     * @return bool
     */
    public function hasChildren(): bool;
}
