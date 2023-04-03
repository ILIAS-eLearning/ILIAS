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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\isGlobalScreenItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\MetaBarItemRenderer;

/**
 * Class isItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isItem extends isGlobalScreenItem
{
    /**
     * @return MetaBarItemRenderer
     */
    public function getRenderer() : MetaBarItemRenderer;

    /**
     * Pass a callable which can decide whether your element is visible for
     * the current user
     * @param callable $is_visible
     * @return isItem
     */
    public function withVisibilityCallable(callable $is_visible) : isItem;

    /**
     * @return bool
     */
    public function isVisible() : bool;

    /**
     * Pass a callable which can decide whether your element is available in
     * general, e.g. return false for the Badges Item when the Badges-Service
     * is disabled.
     * @param callable $is_avaiable
     * @return isItem
     */
    public function withAvailableCallable(callable $is_available) : isItem;

    /**
     * @return bool
     */
    public function isAvailable() : bool;

    /**
     * Return the default position for installation, this will be overridden by
     * the configuration later
     * @return int
     */
    public function getPosition() : int;

    /**
     * @param int $position
     * @return isItem
     */
    public function withPosition(int $position) : isItem;
}
