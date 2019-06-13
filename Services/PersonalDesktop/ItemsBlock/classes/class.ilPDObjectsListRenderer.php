<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDSelectedItemsListRenderer
 */
class ilPDObjectsListRenderer extends ilPDBaseObjectsRenderer implements ilPDObjectsRenderer
{
	/**
	 * @inheritDoc
	 */
	public function render(array $groupedItems, bool $showHeader) : string
	{
		foreach ($groupedItems as $group) {
			$itemHtml = [];

			foreach ($group->getItems() as $item) {
				try {
					$itemListGUI = $this->listItemFactory->byType($item['type']);
					ilObjectActivation::addListGUIActivationProperty($itemListGUI, $item);

					// #15232
					$itemListGUI->enableCheckbox(false);
					if ($this->blockView->isInManageMode() && $this->blockView->mayRemoveItem((int) $item['ref_id'])) {
						$itemListGUI->enableCheckbox(true);
					}

					$html = $itemListGUI->getListItemHTML(
						$item['ref_id'],
						$item['obj_id'],
						$item['title'],
						$item['description']
					);

					if ($html !== '') {
						$itemHtml[] = [
							'html' => $html,
							'item_ref_id' => $item['ref_id'],
							'item_obj_id' => $item['obj_id'],
							'parent_ref' => $item['parent_ref'],
							'type' => $item['type'],
							'item_icon_image_type' => $itemListGUI->getIconImageType()
						];
					}
				} catch (ilException $e) {
					continue;
				}
			}

			if (0 == count($itemHtml)) {
				continue;
			}

			if ($showHeader) {
				$this->addSectionHeader($group);
				$this->resetRowType();
			}

			foreach ($itemHtml as $item) {
				$this->addStandardRow(
					$item['html'],
					(int) $item['item_ref_id'],
					(int) $item['item_obj_id'],
					$item['item_icon_image_type'],
					'th_' . md5($group->getLabel())
				);
			}
		}


		if ($this->blockView->isInManageMode() && $this->blockView->supportsSelectAll()) {
			// #11355 - see ContainerContentGUI::renderSelectAllBlock()
			$this->tpl->setCurrentBlock('select_all_row');
			$this->tpl->setVariable('CHECKBOXNAME', 'ilToolbarSelectAll');
			$this->tpl->setVariable('SEL_ALL_PARENT', 'ilToolbar');
			$this->tpl->setVariable('SEL_ALL_CB_NAME', 'id');
			$this->tpl->setVariable('TXT_SELECT_ALL', $this->lng->txt('select_all'));
			$this->tpl->parseCurrentBlock();
		}

		return $this->tpl->get();
	}
}