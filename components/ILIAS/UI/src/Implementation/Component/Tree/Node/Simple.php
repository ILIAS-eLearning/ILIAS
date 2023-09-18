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
use ILIAS\UI\Component\Tree\Node\Simple as ISimple;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * A very simple Tree-Node
 */
class Simple extends Node implements ISimple
{
    protected string $asynch_url = '';
    protected ?Icon $icon;

    public function __construct(
        string $label,
        Icon $icon = null,
        URI $link = null
    ) {
        parent::__construct($label, $link);
        $this->icon = $icon;
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
    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function getAsyncLoading(): bool
    {
        return $this->getAsyncURL() != '';
    }

    /**
     * @inheritdoc
     */
    public function withAsyncURL(string $url): ISimple
    {
        $clone = clone $this;
        $clone->asynch_url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAsyncURL(): string
    {
        return $this->asynch_url;
    }

    /**
     * Create a new node object with an URI that will be added to the UI
     */
    public function withLink(URI $link): \ILIAS\UI\Component\Tree\Node\Node
    {
        $clone = clone $this;
        $clone->link = $link;
        return $clone;
    }
}
