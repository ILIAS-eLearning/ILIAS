<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Card\Card;

/**
 * Class ilPDObjectsTileRenderer
 */
class ilPDObjectsTileRenderer extends ilPDBaseObjectsRenderer implements ilPDObjectsRenderer
{
	/**
	 * @inheritDoc
	 */
	public function render(array $groupedItems, bool $showHeader): string
	{
		$tpl = new ilTemplate('tpl.block_tiles.html', true, true, 'Services/PersonalDesktop');

		$itemRendered = false;

		foreach ($groupedItems as $group) {
			$items = $group->getItems();
			if (count($items) > 0) {
				$cards = [];
				foreach ($group->getItems() as $item) {
					$c = $this->getCard($item);
					if ($c !== null) {
						$cards[] = $c;
					}
				}

				$tpl->setCurrentBlock('head');
				$tpl->setVariable('HEAD', $group->getLabel());
				$tpl->parseCurrentBlock();

				$deck = $this->uiFactory
					->deck($cards)
					->withNormalCardsSize();

				$tpl->setCurrentBlock('tiles');
				if ($this->ctrl->isAsynch()) {
					$tpl->setVariable('TILES', $this->uiRenderer->renderAsync($deck));
				} else {
					$tpl->setVariable('TILES', $this->uiRenderer->render($deck));
				}
				$tpl->parseCurrentBlock();

				$tpl->setCurrentBlock('grouped_tiles');
				$tpl->parseCurrentBlock();

				$itemRendered = true;
			}
		}

		if (!$itemRendered) {
			return '';
		}

		$html = $tpl->get();
		if ($this->ctrl->isAsynch()) {
			$html .= $tpl->getOnLoadCodeForAsynch();
		}

		$this->tpl->touchBlock('row_type_1');
		$this->tpl->setCurrentBlock('container_standard_row');
		$this->tpl->setVariable('BLOCK_ROW_CONTENT', $html);
		$this->tpl->parseCurrentBlock();

		$this->tpl->touchBlock('container_row');

		return $this->tpl->get();
	}

	/**
	 * Render card
	 * @param array $item
	 * @return Card
	 * @throws ilException
	 */
	protected function getCard(array $item): ?Card {
		$itemListGui = $this->listItemFactory->byType($item['type']);
		ilObjectActivation::addListGUIActivationProperty($itemListGui, $item);


		return $itemListGui->getAsCard((int) $item['ref_id'],
			(int) $item['obj_id'],
			(string) $item['type'],
			(string) $item['title'],
			(string) $item['description']);
	}
}