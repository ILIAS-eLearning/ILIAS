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

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class Node implements C\Input\Field\Node\Node
{
    use ComponentHelper;

    protected string|int $id;

    /** @var array<string|int> */
    protected array $parent_ids;

    /** @param array<string|int> $full_node_path */
    public function __construct(
        protected array $full_node_path,
        protected string $name,
        protected ?C\Symbol\Icon\Icon $icon,
    ) {
        if (empty($full_node_path)) {
            throw new \InvalidArgumentException("\$full_node_path MUST contain at least one node-id.");
        }
        $this->checkArgListElements('full_node_path', $full_node_path, ['string', 'int']);
        $this->id = array_pop($full_node_path);
        $this->parent_ids = $full_node_path;
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    /** @return array<string|int> */
    public function getFullPath(): array
    {
        return $this->full_node_path;
    }

    /** @return array<string|int> */
    public function getParentIds(): array
    {
        return $this->parent_ids;
    }

    /**
     * Returns the display value of this node.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns an Icon which visually represents this node. Defaults to an
     * abbreviation using the first letter of the node name.
     */
    public function getIcon(): ?C\Symbol\Icon\Icon
    {
        return $this->icon;
    }
}
