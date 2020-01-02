<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * Build controls for the view.
 */
interface ControlBuilder
{
    /**
     * An exit control allows the user to gracefully leave the object providing
     * the kiosk mode.
     *
     * @throws \LogicException if view wants to introduce a second exit button.
     */
    public function exit(string $command) : ControlBuilder;

    /**
     * A next control allows the user to progress to the next item in the object.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @throws \LogicException if view wants to introduce a second next button.
     */
    public function next(string $command, int $parameter = null) : ControlBuilder;

    /**
     * A previous control allows the user to go back to the previous item in the object.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @throws \LogicException if view wants to introduce a second previous button.
     */
    public function previous(string $command, int $parameter = null) : ControlBuilder;

    /**
     * A done control allows the user to mark the object as done.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @throws \LogicException if view wants to introduce a second done button.
     */
    public function done(string $command, int $parameter = null) : ControlBuilder;

    /**
     * A generic control needs to have a label that tells what it does.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     */
    public function generic(string $label, string $command, int $parameter = null) : ControlBuilder;

    /**
     * A toggle can be used to switch some behaviour in the view on or of.
     */
    public function toggle(string $label, string $on_command, string $off_command) : ControlBuilder;

    /**
     * A mode control can be used to switch between different modes in the view.
     *
     * Uses the indizes of the labels in the array as parameter for the command.
     */
    public function mode(string $command, array $labels) : ControlBuilder;

    /**
     * A locator allows the user to see the path leading to her current location and
     * jump back to previous items on that path.
     *
     * The command will be enhanced with a parameter defined in the locator builder.
     *
     * @throws \LogicException if view wants to introduce a second locator.
     */
    public function locator(string $command) : LocatorBuilder;

    /**
     * A table of content allows the user to get an overview over the generally available
     * content in the object.
     *
     * The command will be enhanced with a parameter defined here on in the locator builder.
     *
     * If a parameter is defined here, the view provides an overview-page.
     *
     * @throws \LogicException if view wants to introduce a second TOC.
     * @param	mixed $state one of the STATE_ constants from TOCBuilder
     */
    public function tableOfContent(string $label, string $command, int $parameter = null, $state = null) : TOCBuilder;
}
