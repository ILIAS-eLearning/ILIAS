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

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\ViewControl\HasViewControls;

/**
 * Class Secondary
 * @package ILIAS\UI\Implementation\Component\Standard
 */
abstract class Secondary implements C\Panel\Secondary\Secondary
{
    use ComponentHelper;
    use HasViewControls;

    protected string $title;
    protected ?C\Dropdown\Standard $actions = null;
    protected ?C\Button\Shy $footer_component = null;

    /**
     * Gets the secondary panel title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the action drop down to be displayed on the right of the title
     */
    public function withActions(C\Dropdown\Standard $actions): C\Panel\Secondary\Secondary
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * Gets the action drop down to be displayed on the right of the title
     */
    public function getActions(): ?C\Dropdown\Standard
    {
        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function withFooter(C\Button\Shy $component): C\Panel\Secondary\Secondary
    {
        $clone = clone $this;
        $clone->footer_component = $component;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFooter(): ?C\Button\Shy
    {
        return $this->footer_component;
    }
}
