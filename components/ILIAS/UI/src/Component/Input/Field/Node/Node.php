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
 */

namespace ILIAS\UI\Component\Input\Field\Node;

use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Node
{
    /**
     * Returns the unique identifier of this node.
     */
    public function getId(): string|int;

    /**
     * Returns the display value of this node.
     */
    public function getName(): string;

    /**
     * Returns an Icon which visually represents this node. Defaults to an
     * abbreviation using the first letter of the node name.
     */
    public function getIcon(): ?Icon;

    /**
     * Returns all sub-nodes of the current node.
     * @return Node[]
     */
    public function getChildren(): array;
}
