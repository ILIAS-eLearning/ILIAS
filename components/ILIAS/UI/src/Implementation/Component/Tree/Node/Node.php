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

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Tree\Node\Node as INode;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Clickable;

/**
 * A very simple Tree-Node
 */
abstract class Node implements INode
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var Node[]
     */
    protected array $subnodes = [];
    protected ?URI $link = null;
    protected string $label;
    protected bool $expanded = false;
    protected bool $highlighted = false;

    public function __construct(string $label, URI $link = null)
    {
        $this->label = $label;
        $this->link = $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalSubnode(INode $node): INode
    {
        $this->subnodes[] = $node;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubnodes(): array
    {
        return $this->subnodes;
    }

    /**
     * @inheritdoc
     */
    public function withExpanded(bool $expanded): INode
    {
        $clone = clone $this;
        $clone->expanded = $expanded;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * @inhertidoc
     */
    public function withHighlighted(bool $highlighted): INode
    {
        $clone = clone $this;
        $clone->highlighted = $highlighted;
        return $clone;
    }

    /**
     * @inhertidoc
     */
    public function isHighlighted(): bool
    {
        return $this->highlighted;
    }

    /**
     * @inhertidoc
     */
    public function withOnClick(Signal $signal): self
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inhertidoc
     */
    public function appendOnClick(Signal $signal): self
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
     * Get the URI object that is added as link in the UI
     */
    public function getLink(): ?URI
    {
        return $this->link;
    }
}
