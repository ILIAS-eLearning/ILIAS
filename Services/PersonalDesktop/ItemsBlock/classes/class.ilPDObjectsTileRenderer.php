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
					$cards[] = $this->getCard($item);
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
	protected function getCard(array $item): Card {
		$itemListGui = $this->listItemFactory->byType($item['type']);
		ilObjectActivation::addListGUIActivationProperty($itemListGui, $item);

		$itemListGui->initItem(
			$item['ref_id'],
			$item['obj_id'],
			$item['title'],
			$item['description']
		);

		$itemListGui->insertCommands();
		$actions = [];
		foreach ($itemListGui->current_selection_list->getItems() as $action_item) {
			$actions[] = $this->uiFactory
				->button()
				->shy($action_item['title'], $action_item['link']);
		}
		$dropdown = $this->uiFactory
			->dropdown()
			->standard($actions);

		$def_command = $itemListGui->getDefaultCommand();

		$img = $this->objectService->commonSettings()->tileImage()->getByObjId((int) $item['obj_id']);
		if ($img->exists()) {
			$path = $img->getFullPath();
		} else {
			$path = ilUtil::getImagePath('cont_tile/cont_tile_default_' . $item['type'] . '.svg');
			if (!is_file($path)) {
				$path = ilUtil::getImagePath('cont_tile/cont_tile_default.svg');
			}
		}

		$image = $this->uiFactory
			->image()
			->responsive($path, '');
		if ($def_command['link'] != '')    // #24256
		{
			$image = $image->withAction($def_command['link']);
		}

		$title = $item['title'];

		if ($item['type'] == 'sess' && $item['title'] == '') {
			$app_info = ilSessionAppointment::_lookupAppointment($item['obj_id']);
			$title = ilSessionAppointment::_appointmentToString(
				$app_info['start'],
				$app_info['end'],
				$app_info['fullday']
			);
		}

		$icon = $this->uiFactory
			->icon()
			->standard($item['type'], $this->lng->txt('obj_' . $item['type']))
			->withIsOutlined(true);
		$card = $this->uiFactory->card()->repositoryObject(
			$title . '<span data-list-item-id="' . $itemListGui->getUniqueItemId(true) . '"></span>',
			$image
		)->withObjectIcon(
			$icon
		)->withActions(
			$dropdown
		);

		// #24256
		if ($def_command['link']) {
			$card = $card->withTitleAction($def_command['link']);
		}

		$l = [];
		foreach ($itemListGui->determineProperties() as $p) {
			if ($p['property'] !== $this->lng->txt('learning_progress')) {
				$l[(string)$p['property']] = (string)$p['value'];
			}
		}
		if (count($l) > 0) {
			$prop_list = $this->uiFactory
				->listing()
				->descriptive($l);
			$card = $card->withSections([$prop_list]);
		}

		$lp = ilLPStatus::getListGUIStatus($item['obj_id'], false);
		if (is_array($lp) && array_key_exists('status', $lp)) {
			$percentage = (int)ilLPStatus::_lookupPercentage($item['obj_id'], $this->user->getId());
			if ($lp['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
				$percentage = 100;
			}

			$card = $card->withProgress(
				$this->uiFactory
					->chart()
					->progressMeter()
					->mini(100, $percentage)
			);
		}

		return $card;
	}
}