<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface Panel extends \ILIAS\UI\Component\Component
{
    /**
     * Gets the title of the panel
     *
     * @return string $title Title of the Panel
     */
    public function getTitle();

    /**
     * Gets the content to be displayed inside the panel
     *
     * @return \ILIAS\UI\Component\Component[]|\ILIAS\UI\Component\Component
     */
    public function getContent();

    /**
     * Sets action Dropdown being displayed beside the title
     * @param \ILIAS\UI\Component\Dropdown\Standard $actions
     * @return Sub
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions);

    /**
     * Gets action Dropdown being displayed beside the title
     * @return \ILIAS\UI\Component\Dropdown\Standard | null
     */
    public function getActions();
}
