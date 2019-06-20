<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\LinkItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\LinkListItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\LostItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TopLinkItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TopParentItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;

/**
 * Class ilMMCustomProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider
{

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;


    /**
     * @return TopParentItem[]
     */
    public function getStaticTopItems() : array
    {
        /**
         * @var $item ilMMCustomItemStorage
         */
        $top_items = [];
        foreach (ilMMCustomItemStorage::where(['top_item' => true])->get() as $item) {
            $top_items[] = $this->getSingleCustomItem($item, true);
        }

        return $top_items;
    }


    /**
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem[]
     */
    public function getStaticSubItems() : array
    {
        /**
         * @var $item ilMMCustomItemStorage
         */
        $items = [];
        foreach (ilMMCustomItemStorage::where(['top_item' => false])->get() as $item) {
            $items[] = $this->getSingleCustomItem($item, true);
        }

        return $items;
    }


    /**
     * @param ilMMCustomItemStorage $storage
     * @param bool                  $register
     *
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
     */
    public function getSingleCustomItem(ilMMCustomItemStorage $storage, $register = false) : \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
    {
        $identification = $this->globalScreen()->identification()->core($this)->identifier($storage->getIdentifier());
        if ($register) {
            ilGSIdentificationStorage::registerIdentification($identification, $this);
        }

        $item = $this->globalScreen()->mainBar()->custom($storage->getType(), $identification);

        if ($item instanceof hasTitle && $storage->getDefaultTitle() !== '') {
            $item = $item->withTitle($storage->getDefaultTitle());
        }
        if ($item instanceof hasAction) {
            $item = $item->withAction("#");
        }
        if ($item instanceof isChild) {
            $mm_item = ilMMItemStorage::find($identification->serialize());
            $parent_identification = "";
            if ($mm_item instanceof ilMMItemStorage) {
                $parent_identification = $mm_item->getParentIdentification();
            }

            if ($parent_identification) {
                $item = $item->withParent(
                    $this->globalScreen()
                        ->identification()
                        ->fromSerializedIdentification($parent_identification)
                );
            }
        }

        if ($register) {
            ilMMItemStorage::register($item);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    public function provideTypeInformation() : TypeInformationCollection
    {
        $c = new TypeInformationCollection();
        // TopParentItem
        $c->add(new TypeInformation(TopParentItem::class, $this->translateType(TopParentItem::class), new TopParentItemRenderer()));
        // TopLinkItem
        $c->add(new TypeInformation(TopLinkItem::class, $this->translateType(TopLinkItem::class), new TopLinkItemRenderer(), new ilMMTypeHandlerTopLink()));
        // Link
        $c->add(new TypeInformation(Link::class, $this->translateType(Link::class), new LinkItemRenderer(), new ilMMTypeHandlerLink()));

        // LinkList
        $link_list = new TypeInformation(LinkList::class, $this->translateType(LinkList::class), new LinkListItemRenderer());
        $link_list->setCreationPrevented(true);
        $c->add($link_list);
        // Separator
        $c->add(new TypeInformation(Separator::class, $this->translateType(Separator::class), null, new ilMMTypeHandlerSeparator(), $this->translateByline(Separator::class)));
        // RepositoryLink
        $c->add(new TypeInformation(RepositoryLink::class, $this->translateType(RepositoryLink::class), null, new ilMMTypeHandlerRepositoryLink()));
        // Lost
        $lost = new TypeInformation(Lost::class, $this->translateType(Lost::class), new LostItemRenderer());
        $lost->setCreationPrevented(true);
        $c->add($lost);

        return $c;
    }


    /**
     * @param string $type
     *
     * @return string
     */
    private function translateType(string $type) : string
    {
        $last_part = substr(strrchr($type, "\\"), 1);
        $last_part = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $last_part));

        return $this->dic->language()->txt("type_" . strtolower($last_part));
    }


    /**
     * @param string $type
     *
     * @return string
     */
    private function translateByline(string $type) : string
    {
        $last_part = substr(strrchr($type, "\\"), 1);
        $last_part = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $last_part));

        return $this->dic->language()->txt("type_" . strtolower($last_part) . "_info");
    }
}
