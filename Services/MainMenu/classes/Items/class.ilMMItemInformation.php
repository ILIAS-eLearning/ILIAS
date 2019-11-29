<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ilMMItemInformation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemInformation implements ItemInformation
{

    /**
     * @var array
     */
    private $translations = [];
    /**
     * @var array
     */
    private $items = [];


    /**
     * ilMMItemInformation constructor.
     *
     * @param StorageFacade $storage
     */
    public function __construct()
    {
        $this->items = ilMMItemStorage::getArray('identification');
        $this->translations = ilMMItemTranslationStorage::getArray('id', 'translation');
    }


    /**
     * @inheritDoc
     */
    public function customTranslationForUser(hasTitle $item) : hasTitle
    {
        /**
         * @var $item isItem
         */
        global $DIC;
        static $usr_language_key;
        static $default_language;

        if (!$usr_language_key) {
            $usr_language_key = $DIC->language()->getUserLanguage() ? $DIC->language()->getUserLanguage() : $DIC->language()->getDefaultLanguage();
        }
        if (!$default_language) {
            $default_language = ilMMItemTranslationStorage::getDefaultLanguage();
        }

        if ($item instanceof hasTitle && isset($this->translations["{$item->getProviderIdentification()->serialize()}|$usr_language_key"])
            && $this->translations["{$item->getProviderIdentification()->serialize()}|$usr_language_key"] !== ''
        ) {
            $item = $item->withTitle((string) $this->translations["{$item->getProviderIdentification()->serialize()}|$usr_language_key"]);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    public function customPosition(isItem $item) : isItem
    {
        return $item->withPosition($this->getPosition($item));
    }


    private function getPosition(isItem $item) : int
    {
        if (isset($this->items[$item->getProviderIdentification()->serialize()]['position'])) {
            return (int) $this->items[$item->getProviderIdentification()->serialize()]['position'];
        }

        return $item->getPosition();
    }


    /**
     * @inheritDoc
     */
    public function isItemActive(isItem $item) : bool
    {
        $serialize = $item->getProviderIdentification()->serialize();
        if (isset($this->items[$serialize]['active'])) {
            return $this->items[$serialize]['active'] === "1";
        }

        return $item->isActive();
    }


    /**
     * @inheritDoc
     */
    public function getParent(isChild $item) : IdentificationInterface
    {
        global $DIC;
        $parent_string = $item->getProviderIdentification()->serialize();
        if (isset($this->items[$parent_string]['parent_identification'])) {
            return $DIC->globalScreen()->identification()->fromSerializedIdentification($this->items[$parent_string]['parent_identification']);
        }

        return $item->getParent();
    }


    /**
     * @inheritDoc
     */
    public function customSymbol(hasSymbol $item) : hasSymbol
    {
        $id = $item->getProviderIdentification()->serialize();
        if (isset($this->items[$id]['icon_id']) && strlen($this->items[$id]['icon_id']) > 1) {
            global $DIC;

            $DIC->ctrl()->setParameterByClass(ilMMIconGUI::class, 'icon_id', $this->items[$id]['icon_id']);
            $link_target_by_class = $DIC->ctrl()->getLinkTargetByClass([ilUIPluginRouterGUI::class, ilMMIconGUI::class]);
            $symbol = $DIC->ui()->factory()->symbol()->icon()->custom($link_target_by_class, 'aria');

            return $item->withSymbol($symbol);
        }

        return $item;
    }
}
