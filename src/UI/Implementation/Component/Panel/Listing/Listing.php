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

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Item\Group;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Panel
 */
abstract class Listing implements C\Panel\Listing\Listing
{
    use ComponentHelper;

    protected string $title;
    protected ?C\Dropdown\Standard $actions = null;

    /**
     * @var Group[]
     */
    protected array $item_groups = array();

    /**
     * @param \ILIAS\UI\Component\Item\Group[] $item_groups
     */
    public function __construct(string $title, array $item_groups)
    {
        $this->title = $title;
        $this->item_groups = $item_groups;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItemGroups(): array
    {
        return $this->item_groups;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions): C\Panel\Listing\Listing
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions(): ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
