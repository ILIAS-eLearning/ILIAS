<?php

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
            $item = $item->withVisibleTitle(true);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification): array
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    public function saveFormFields(IdentificationInterface $identification, array $data): bool
    {
        return true;
    }
}
