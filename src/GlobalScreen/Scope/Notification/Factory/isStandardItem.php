<?php

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

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

/**
 * Interface isStandardItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isStandardItem extends isItem
{
    /**
     * Set the callable to be executed, when the notification center is opened.
     * @param callable $handle_opened
     * @return isItem
     */
    public function withOpenedCallable(callable $handle_opened) : isItem;

    /**
     * Get the callable to be executed, when the notification center is opened.
     * @return callable
     */
    public function getOpenedCallable() : callable;

    /**
     * Set the callable to be executed, when this specific item is closed.
     * @param callable $handle_closed
     * @return isItem
     */
    public function withClosedCallable(callable $handle_closed) : isItem;

    /**
     * Get the callable to be executed, when this specific item is closed.
     * @return callable|null
     */
    public function getClosedCallable() : ?callable;

    /**
     * Get whether there are any callables to be executed when the notification
     * center is closed.
     * @return bool
     */
    public function hasClosedCallable() : bool;
}
