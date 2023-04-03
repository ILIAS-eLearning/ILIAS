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

/**
 * Class BaseTypeHandler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class BaseTypeHandler implements TypeHandler
{
    /**
     * @inheritDoc
     */
    public function matchesForType() : string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function enrichItem(isItem $item) : isItem
    {
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function saveFormFields(IdentificationInterface $identification, array $data) : bool
    {
        return true;
    }
}
