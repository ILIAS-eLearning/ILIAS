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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilMMTypeHandlerSeparator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerSeparator implements TypeHandler
{
    /**
     * @inheritDoc
     */
    public function matchesForType(): string
    {
        return Separator::class;
    }


    /**
     * @inheritDoc
     */
    public function enrichItem(isItem $item): isItem
    {
        if ($item instanceof Separator && $item->getTitle() !== "") {
            return $item->withVisibleTitle(true);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification): array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function saveFormFields(IdentificationInterface $identification, array $data): bool
    {
        return true;
    }
}
