<?php

use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\UI\Component\Link\Link;

/**
 * Class ilMMAbstractItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractItemFacade implements ilMMItemFacadeInterface
{

    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation
     */
    protected $type_information;
    /**
     * @var ilMMItemStorage
     */
    protected $mm_item;
    /**
     * @var isItem
     */
    protected $gs_item;
    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
     */
    protected $identification;
    /**
     * @var string
     */
    protected $default_title = "-";


    /**
     * ilMMAbstractItemFacade constructor.
     *
     * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
     * @param Main                                                       $collector
     *
     * @throws Throwable
     */
    public function __construct(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, Main $collector)
    {
        $this->identification = $identification;
        $this->gs_item = $collector->getSingleItem($identification);
        $this->type_information = $collector->getTypeInformationCollection()->get(get_class($this->gs_item));
        $this->mm_item = ilMMItemStorage::register($this->gs_item);
    }


    public function getId() : string
    {
        return $this->identification->serialize();
    }


    /**
     * @return bool
     */
    public function hasStorage() : bool
    {
        return ilMMItemStorage::find($this->getId()) !== null;
    }


    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->mm_item->getIdentification() == '';
    }


    /**
     * @return ilMMItemStorage
     */
    public function itemStorage() : ilMMItemStorage
    {
        return $this->mm_item;
    }


    /**
     * @return \ILIAS\GlobalScreen\Identification\IdentificationInterface
     */
    public function identification() : \ILIAS\GlobalScreen\Identification\IdentificationInterface
    {
        return $this->identification;
    }


    /**
     * @return isItem
     */
    public function item() : isItem
    {
        return $this->gs_item;
    }


    public function getAmountOfChildren() : int
    {
        if ($this->gs_item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent) {
            return count($this->gs_item->getChildren());
        }

        return 0;
    }


    public function isAvailable() : bool
    {
        return (bool) ($this->gs_item->isAvailable() || $this->item()->isAlwaysAvailable());
    }


    /**
     * @inheritDoc
     */
    public function isActivated() : bool
    {
        return (bool) $this->mm_item->isActive() || $this->item()->isAlwaysAvailable();
    }


    /**
     * @inheritDoc
     */
    public function isAlwaysAvailable() : bool
    {
        return $this->item()->isAlwaysAvailable();
    }


    /**
     * @return string
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->identification->getProviderNameForPresentation();
    }


    /**
     * @return string
     */
    public function getDefaultTitle() : string
    {
        $default_translation = ilMMItemTranslationStorage::getDefaultTranslation($this->identification);
        if ($default_translation !== "") {
            return $default_translation;
        }
        if ($this->default_title == "-" && $this->gs_item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle) {
            $this->default_title = $this->gs_item->getTitle();
        }

        return $this->default_title;
    }


    /**
     * @param string $default_title
     */
    public function setDefaultTitle(string $default_title)
    {
        $this->default_title = $default_title;
    }


    /**
     * @return string
     */
    public function getStatus() : string
    {
        global $DIC;
        if (!$this->gs_item->isAvailable() || $this->gs_item->isAlwaysAvailable()) {
            return $DIC->ui()->renderer()->render($this->gs_item->getNonAvailableReason());
        }

        return "";
    }


    /**
     * @return string
     * @throws ReflectionException
     */
    public function getTypeForPresentation() : string
    {
        return $this->type_information->getTypeNameForPresentation();
    }


    public function getParentIdentificationString() : string
    {
        if ($this->gs_item instanceof isChild) {
            $provider_name_for_presentation = $this->gs_item->getParent()->serialize();

            return $provider_name_for_presentation;
        }

        return "";
    }


    /**
     * @return bool
     */
    public function isCustomType() : bool
    {
        $known_core_types = [
            Complex::class,
            Link::class,
            LinkList::class,
            Lost::class,
            RepositoryLink::class,
            Separator::class,
            TopLinkItem::class,
            TopParentItem::class,
        ];
        foreach ($known_core_types as $known_core_type) {
            if (get_class($this->gs_item) === $known_core_type) {
                return false;
            }
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function isTopItem() : bool
    {
        return $this->gs_item instanceof isTopItem;
    }


    /**
     * @inheritDoc
     */
    public function isInLostItem() : bool
    {
        if ($this->gs_item instanceof isChild) {
            return $this->gs_item->getParent() instanceof NullIdentification;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public function setIsTopItm(bool $top_item)
    {
        // TODO: Implement setIsTopItm() method.
    }


    /**
     * FSX check if doublette
     *
     * @inheritDoc
     */
    public function getType() : string
    {
        return $this->type_information->getType();
    }


    /**
     * @param string $parent
     */
    public function setParent(string $parent)
    {
        $this->mm_item->setParentIdentification($parent);
    }


    /**
     * @inheritdoc
     */
    public function setPosition(int $position)
    {
        $this->mm_item->setPosition($position);
    }


    /**
     * @param bool $status
     */
    public function setActiveStatus(bool $status)
    {
        $this->mm_item->setActive($status);
    }


    // CRUD

    public function update()
    {
        ilMMItemTranslationStorage::storeDefaultTranslation($this->identification, $this->default_title);
        $this->mm_item->update();
    }


    public function create()
    {
        ilMMItemTranslationStorage::storeDefaultTranslation($this->identification, $this->default_title);
        $this->mm_item->create();
        ilMMItemStorage::register($this->gs_item);
    }


    /**
     * @inheritDoc
     */
    public function delete()
    {
        if ($this->isDeletable()) {
            $serialize = $this->identification->serialize();
            $gs = ilGSIdentificationStorage::find($serialize);
            if ($gs instanceof ilGSIdentificationStorage) {
                $gs->delete();
            }
            $mm = ilMMItemStorage::find($serialize);
            if ($mm instanceof ilMMItemStorage) {
                $mm->delete();
            }
        }
    }
}
