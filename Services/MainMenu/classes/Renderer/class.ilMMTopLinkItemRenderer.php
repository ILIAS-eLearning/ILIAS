<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMTopLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopLinkItemRenderer extends BaseTypeRenderer {

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
	public function getComponentForItem(isItem $item): Component {
		/**
		 * @var $item TopLinkItem
		 */
		$tpl = new ilTemplate("tpl.mm_top_link_item.html", false, false, 'Services/MainMenu');
		$tpl->setVariable("TITLE", $item->getTitle());
		$tpl->setVariable("HREF", $item->getAction());
		$tpl->setVariable("TARGET", $item->isLinkWithExternalAction() ? self::BLANK : self::TOP);

		return $this->ui_factory->legacy($tpl->get());
	}
}
