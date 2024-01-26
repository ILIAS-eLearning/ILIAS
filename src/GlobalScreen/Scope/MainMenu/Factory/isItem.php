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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\isGlobalScreenItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Interface IFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isItem extends isGlobalScreenItem
{
    /**
     * Pass a callable which can decide whether your element is visible for
     * the current user
     * @param callable $is_visible
     * @return isItem|isChild
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
     * @return isItem|isChild
     */
    public function withAvailableCallable(callable $is_available) : isItem;

    /**
     * @return bool
     */
    public function isAvailable() : bool;

    /**
     * If your provider or the service which provides the Item does not allow
     * to activate the item (@param Legacy $element
     * @return isItem
     * @see withAvailableCallable ), please provide the
     *      reason why. You can pass e Legacy Component for the moment, in most cases
     *      this will be something like in
     *      Services/Administration/templates/default/tpl.external_settings.html
     */
    public function withNonAvailableReason(Legacy $element) : isItem;

    /**
     * @return Legacy
     */
    public function getNonAvailableReason() : Legacy;

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

    /**
     * @return bool
     */
    public function isAlwaysAvailable() : bool;

    /**
     * @param bool $always_active
     * @return isItem
     */
    public function withAlwaysAvailable(bool $always_active) : isItem;

    /**
     * @param TypeInformation $information
     * @return isItem
     */
    public function setTypeInformation(TypeInformation $information) : isItem;

    public function getTypeInformation() : ?TypeInformation;

    public function isTop() : bool;
}
