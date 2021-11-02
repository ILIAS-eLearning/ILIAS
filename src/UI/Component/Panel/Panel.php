<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Dropdown;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface Panel extends Component
{
    /**
     * Gets the title of the panel
     *
     * @return string $title Title of the Panel
     */
    public function getTitle() : string;

    /**
     * Gets the content to be displayed inside the panel
     * @return Component[]|Component
     */
    public function getContent();

    /**
     * Sets action Dropdown being displayed beside the title
     */
    public function withActions(Dropdown\Standard $actions) : Panel;

    /**
     * Gets action Dropdown being displayed beside the title
     */
    public function getActions() : ?Dropdown\Standard;
}
