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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Class TypeHandler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface TypeHandler
{
    /**
     * @return string Classname of matching Type this TypeHandler can handle
     */
    public function matchesForType() : string;

    /**
     * @param isItem $item
     * @return isItem
     */
    public function enrichItem(isItem $item) : isItem;

    /**
     * @param IdentificationInterface $identification
     * @return Input[]
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array;

    /**
     * @param IdentificationInterface $identification
     * @param array                   $data
     * @return bool
     */
    public function saveFormFields(IdentificationInterface $identification, array $data) : bool;
}
