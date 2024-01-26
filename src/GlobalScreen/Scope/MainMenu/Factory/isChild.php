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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface isChild
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isChild extends isItem
{
    /**
     * As a developer, you provide the standard-parent Item while creating your items.
     * Please note that the effective parent can be changed by configuration.
     * @param IdentificationInterface $identification
     * @return isItem
     */
    public function withParent(IdentificationInterface $identification) : isItem;

    /**
     * @return bool
     */
    public function hasParent() : bool;

    /**
     * @return IdentificationInterface
     */
    public function getParent() : IdentificationInterface;

    /**
     * @param IdentificationInterface $identification
     * @return isChild
     */
    public function overrideParent(IdentificationInterface $identification) : isItem;
}
