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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\GlobalScreen\isGlobalScreenItem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface isItem extends isGlobalScreenItem
{
    /**
     * Pass a callable which can decide whether your element is visible for
     * the current user
     */
    public function withVisibilityCallable(callable $is_visible): isItem;

    public function isVisible(): bool;

    /**
     * Pass a callable which can decide whether your element is available in
     * general, e.g. return false for the Badges Item when the Badges-Service
     * is disabled.
     */
    public function withAvailableCallable(callable $is_available): isItem;

    public function isAvailable(): bool;

    /**
     * Return the default position for installation, this will be overridden by
     * the configuration later
     */
    public function getPosition(): int;

    public function withPosition(int $position): isItem;

    public function isTop(): bool;

}
