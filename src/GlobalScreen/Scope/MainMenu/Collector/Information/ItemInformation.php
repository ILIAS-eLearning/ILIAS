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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ItemInformation
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemInformation
{
    /**
     * @param isItem $item
     * @return bool
     */
    public function isItemActive(isItem $item) : bool;

    /**
     * @param isItem $item
     * @return isItem
     */
    public function customPosition(isItem $item) : isItem;

    /**
     * @param hasTitle $item
     * @return hasTitle
     */
    public function customTranslationForUser(hasTitle $item) : hasTitle;

    /**
     * @param isChild $item
     * @return IdentificationInterface
     */
    public function getParent(isItem $item) : IdentificationInterface;

    /**
     * @param hasSymbol $item
     * @return hasSymbol
     */
    public function customSymbol(hasSymbol $item) : hasSymbol;
}
