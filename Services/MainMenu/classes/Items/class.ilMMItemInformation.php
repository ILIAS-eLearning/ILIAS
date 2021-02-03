<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;

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
    public function translateItemForUser(hasTitle $item) : hasTitle
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

        $ar = ilMMTypeActionStorage::where([
            'identification' => $item->getProviderIdentification()->serialize()
        ], '=')->first();

        if (null !== $ar && $item instanceof RepositoryLink && empty($item->getTitle())) {
            $item = $item->withTitle(($ar->getAction() > 0) ?
                ilObject2::_lookupTitle(ilObject2::_lookupObjectId($ar->getAction())) :
                ""
            );
        }
        if (null !== $ar && $item instanceof Link && empty($item->getTitle())) {
            $item = $item->withTitle($ar->getAction());
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
    public function getPositionOfSubItem(isChild $child) : int
    {
        $position = $this->getPosition($child);

        return $position;
    }


    /**
     * @inheritDoc
     */
    public function getPositionOfTopItem(isTopItem $top_item) : int
    {
        return $this->getPosition($top_item);
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
}
