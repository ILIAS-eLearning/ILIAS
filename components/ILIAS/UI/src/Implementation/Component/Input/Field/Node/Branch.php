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
 
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field\Node;

use ILIAS\UI\Component as C;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Branch extends Node implements C\Input\Field\Node\Branch
{
    /**
     * @param C\Input\Field\Node\Node[] $children
     */
    public function __construct(
        int|string $id,
        string $name,
        ?C\Symbol\Icon\Icon $icon,
        protected array $children,
    ) {
        parent::__construct($id, $name, $icon);
    }

    /**
     * Returns all sub-nodes of the current node.
     * @return C\Input\Field\Node\Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
