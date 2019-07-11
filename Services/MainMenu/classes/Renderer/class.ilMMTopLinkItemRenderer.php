<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMTopLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopLinkItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    const BLANK = "_blank";
    const TOP = "_top";


    /**
     * @param isItem $item
     *
     * @return Component
     * @throws ilTemplateException
     */
    public function getComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopLinkItem
         */
        $tpl = new ilTemplate("tpl.mm_top_link_item.html", false, false, 'Services/MainMenu');
        $tpl->setVariable("TITLE", $item->getTitle());
        $tpl->setVariable("HREF", $item->getAction());
        $tpl->setVariable("TARGET", $item->isLinkWithExternalAction() ? self::BLANK : self::TOP);
        $tpl->setVariable("ID", "mm_" . $item->getProviderIdentification()->getInternalIdentifier());

        return $this->ui_factory->legacy($tpl->get());
    }
}
