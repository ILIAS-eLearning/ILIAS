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

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Tree Control
 */
abstract class Tree implements ITree\Tree
{
    use ComponentHelper;

    /**
     * @var mixed
     */
    protected $environment;

    /**
     * @var mixed
     */
    protected $data;

    protected string $label;
    protected ITree\TreeRecursion $recursion;
    protected bool $highlight_nodes_on_click = false;
    protected bool $is_sub = false;


    public function __construct(string $label, ITree\TreeRecursion $recursion)
    {
        $this->label = $label;
        $this->recursion = $recursion;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withEnvironment($environment): ITree\Tree
    {
        $clone = clone $this;
        $clone->environment = $environment;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withData($data): ITree\Tree
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getRecursion(): ITree\TreeRecursion
    {
        return $this->recursion;
    }

    /**
     * @inheritdoc
     */
    public function withHighlightOnNodeClick(bool $highlight_nodes_on_click): ITree\Tree
    {
        $clone = clone $this;
        $clone->highlight_nodes_on_click = $highlight_nodes_on_click;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getHighlightOnNodeClick(): bool
    {
        return $this->highlight_nodes_on_click;
    }

    /**
     * @inheritdoc
     */
    public function isSubTree(): bool
    {
        return $this->is_sub;
    }

    /**
     * @inheritdoc
     */
    public function withIsSubTree(bool $is_sub): ITree\Tree
    {
        $clone = clone $this;
        $clone->is_sub = $is_sub;
        return $clone;
    }
}
