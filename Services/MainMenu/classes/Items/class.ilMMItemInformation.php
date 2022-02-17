<?php

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\MainMenu\Storage\Services;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;

/**
 * Class ilMMItemInformation
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemInformation implements ItemInformation
{
    private const ICON_ID = 'icon_id';
    /**
     * @var \ILIAS\UI\Factory
     */
    private $ui_factory;
    /**
     * @var Services
     */
    private $storage;
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
     */
    public function __construct()
    {
        $this->items        = ilMMItemStorage::getArray('identification');
        $this->translations = ilMMItemTranslationStorage::getArray('id', 'translation');
        $this->storage      = new Services();
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
        if ($item instanceof RepositoryLink && empty($item->getTitle())) {
            $item = $item->withTitle(($item->getRefId() > 0) ?
                \ilObject2::_lookupTitle(\ilObject2::_lookupObjectId($item->getRefId())) :
                ""
            );
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
            return $this->items[$serialize]['active'] === '1';
        }
        return true;
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
        if (isset($this->items[$id][self::ICON_ID]) && strlen($this->items[$id][self::ICON_ID]) > 1) {
            global $DIC;

            $ri = $this->storage->find($this->items[$id][self::ICON_ID]);
            if (!$ri) {
                return $item;
            }
            $stream     = $this->storage->stream($ri)->getStream();
            $data       = 'data:' . $this->storage->getRevision($ri)->getInformation()->getMimeType() . ';base64,' . base64_encode($stream->getContents());
            $old_symbol = $item->hasSymbol() ? $item->getSymbol() : null;
            if ($old_symbol instanceof Glyph || $old_symbol instanceof Icon) {
                $aria_label = $old_symbol->getAriaLabel();
            } elseif ($item instanceof hasTitle) {
                $aria_label = $item->getTitle();
            } else {
                $aria_label = 'Custom icon';
            }

            $symbol = $DIC->ui()->factory()->symbol()->icon()->custom($data, $aria_label);

            return $item->withSymbol($symbol);
        }

        return $item;
    }
}
