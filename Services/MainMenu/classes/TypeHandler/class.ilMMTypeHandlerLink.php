<?php

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;

/**
 * Class ilMMTypeHandlerLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler
{
    public function matchesForType(): string
    {
        return Link::class;
    }


    /**
     * @inheritdoc
     */
    public function enrichItem(isItem $item): isItem
    {
        if ($item instanceof hasAction && isset($this->links[$item->getProviderIdentification()->serialize()])) {
            $action = (string) $this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION];
            $is_external = (bool) $this->links[$item->getProviderIdentification()->serialize()][self::F_EXTERNAL];
            $item = $item->withAction($action)->withIsLinkToExternalAction($is_external);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    protected function getFieldTranslation(): string
    {
        global $DIC;

        return $DIC->language()->txt("field_url");
    }


    /**
     * @inheritDoc
     */
    protected function getFieldInfoTranslation(): string
    {
        global $DIC;

        return $DIC->language()->txt("field_url_info");
    }
}
