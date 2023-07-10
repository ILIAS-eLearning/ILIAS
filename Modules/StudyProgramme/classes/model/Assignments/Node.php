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

namespace ILIAS\StudyProgramme\Assignment;

class Node
{
    protected array $subnodes = [];
    protected ?Node $container = null;
    protected string $id;


    protected int $current_points = 0;

    public function __construct($id) //, array $subnodes = [])
    {
        $this->id = $id;
    }

    public function setSubnodes(array $subnodes)
    {
        if ($this->subnodes !== []) {
            throw new \Exception("Use 'setSubnodes' during construction only", 1);
        }
        foreach ($subnodes as $subnode) {
            $this->subnodes[$subnode->getId()] = $subnode->withContainer($this);
        }
        return $this;
    }

    /**
     * this is only used internally - do not use apart from constructing the tree!
     */
    public function withContainer(Node $node): self
    {
        $this->container = $node;
        return $this;
    }

    protected function getContainer(): ?Node
    {
        return $this->container;
    }

    public function withSubnode($node)
    {
        $clone = clone $this;
        $clone->subnodes[$node->getId()] = $node->withContainer($this);
        return $clone;
    }

    public function getSubnodes(): array
    {
        return array_values($this->subnodes);
    }

    public function getSubnode(string $id): ?Node
    {
        return $this->subnodes[$id];
    }

    public function getPath(): array
    {
        $ret = [$this->getId()];
        $node = $this;
        while ($node = $node->getContainer()) {
            $ret[] = $node->getId();
        }
        return array_reverse($ret);
    }

    public function findSubnodePath(string $id, ?Node $node = null): ?array
    {
        if (!$node) {
            $node = $this;
        }
        if ($node->getId() == $id) {
            return $node->getPath();
        }
        foreach ($node->getSubnodes() as $subnode) {
            $result = $this->findSubnodePath($id, $subnode);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
