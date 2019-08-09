<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Tree\Node\Node as INode;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * A very simple Tree-Node
 */
abstract class Node implements INode
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var URI
     */
    protected $link;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $expanded = false;

    /**
     * @var bool
     */
    protected $highlighted = false;

    /**
     * @var Node[]
     */
    protected $subnodes = [];

    public function __construct(string $label, URI $link = null)
    {
        $this->label = $label;
        $this->link  = $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalSubnode(INode $node) : INode
    {
        $this->subnodes[] = $node;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubnodes() : array
    {
        return $this->subnodes;
    }

    /**
     * @inheritdoc
     */
    public function withExpanded(bool $expanded) : INode
    {
        $clone           = clone $this;
        $clone->expanded = $expanded;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isExpanded() : bool
    {
        return $this->expanded;
    }

    /**
     * @inhertidoc
     */
    public function withHighlighted(bool $highlighted) : INode
    {
        $clone              = clone $this;
        $clone->highlighted = $highlighted;
        return $clone;
    }

    /**
     * @inhertidoc
     */
    public function isHighlighted() : bool
    {
        return $this->highlighted;
    }

    /**
     * @inhertidoc
     */
    public function withOnClick(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inhertidoc
     */
    public function appendOnClick(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
     * Get the URI object that is added as link in the UI
     *
     * @return URI
     */
    public function getLink(): ?URI
    {
        return $this->link;
    }
}
