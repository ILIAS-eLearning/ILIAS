<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilPDBaseObjectsRenderer
 */
abstract class ilPDBaseObjectsRenderer implements ilPDObjectsRenderer
{
	/** @var ilPDSelectedItemsBlockViewGUI */
	protected $blockView;
	
	/** @var Factory */
	protected $uiFactory;

	/** @var Renderer */
	protected $uiRenderer;

	/** @var ilObjUser */
	protected $user;

	/** @var ilLanguage */
	protected $lng;

	/** @var \ilObjectService */
	protected $objectService;

	/** @var ilCtrl */
	protected $ctrl;

	/** @var ilPDSelectedItemsBlockListGUIFactory */
	protected $listItemFactory;

	/** @var \ilTemplate $*/
	protected $tpl;

	/** @var string */
	protected $currentRowType = '';

	/**
	 * ilPDSelectedItemsTileRenderer constructor.
	 * @param ilPDSelectedItemsBlockViewGUI $blockView
	 * @param Factory $uiFactory
	 * @param Renderer $uiRenderer
	 * @param ilPDSelectedItemsBlockListGUIFactory $listItemFactory
	 * @param ilObjUser $user
	 * @param ilLanguage $lng
	 * @param ilObjectService $objectService
	 * @param ilCtrl $ctrl
	 */
	public function __construct(
		ilPDSelectedItemsBlockViewGUI $blockView,
		Factory $uiFactory,
		Renderer $uiRenderer,
		ilPDSelectedItemsBlockListGUIFactory $listItemFactory,
		ilObjUser $user,
		ilLanguage $lng,
		ilObjectService $objectService,
		ilCtrl $ctrl
	) {
		$this->blockView = $blockView;
		$this->uiFactory = $uiFactory;
		$this->uiRenderer = $uiRenderer;
		$this->listItemFactory = $listItemFactory;
		$this->user = $user;
		$this->lng = $lng;
		$this->objectService = $objectService;
		$this->ctrl = $ctrl;

		$this->tpl = new ilTemplate('tpl.pd_list_block.html', true, true, 'Services/PersonalDesktop');
	}

	/**
	 *
	 */
	protected function resetRowType()
	{
		$this->currentRowType = "";
	}

	/**
	 * @param ilPDSelectedItemsBlockGroup $group
	 */
	protected function addSectionHeader(ilPDSelectedItemsBlockGroup $group)
	{
		if ($group->hasIcon()) {
			$this->tpl->setCurrentBlock('container_header_row_image');
			$this->tpl->setVariable('HEADER_IMG', $group->getIconPath());
			$this->tpl->setVariable('HEADER_ALT', $group->getLabel());
		} else {
			$this->tpl->setCurrentBlock('container_header_row');
		}

		$this->tpl->setVariable('BLOCK_HEADER_CONTENT', $group->getLabel());
		$this->tpl->setVariable('BLOCK_HEADER_ID', 'th_' . md5($group->getLabel()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->touchBlock('container_row');

		$this->resetRowType();
	}

	/**
	 * @param string $html
	 * @param int $refId
	 * @param int $objId
	 * @param string $imageType
	 * @param string $relatedHeader
	 */
	protected function addStandardRow(
		string $html,
		int $refId = 0,
		int $objId = 0,
		$imageType = '',
		$relatedHeader = ''
	) {
		global $DIC;

		$this->currentRowType = $this->currentRowType === 'row_type_1'
			? 'row_type_2'
			: 'row_type_1';
		$this->tpl->touchBlock($this->currentRowType);

		if ($imageType !== '') {
			if (!is_array($imageType) && !in_array($imageType, ['lm', 'htlm', 'sahs'])) {
				$icon = ilUtil::getImagePath('icon_' . $imageType . '.svg');
				$title = $this->lng->txt('obj_' . $imageType);
			} else {
				$icon = ilUtil::getImagePath('icon_lm.svg');
				$title = $this->lng->txt('learning_module');
			}

			if ($DIC->settings()->get('custom_icons')) {
				
				/** @var \ilObjectCustomIconFactory $customIconFactory */
				$customIconFactory = $DIC['object.customicons.factory'];
				$customIcon = $customIconFactory->getByObjId((int) $objId, $imageType);

				if ($customIcon->exists()) {
					$icon = $customIcon->getFullPath();
				}
			}

			$this->tpl->setCurrentBlock('block_row_image');
			$this->tpl->setVariable('ROW_IMG', $icon);
			$this->tpl->setVariable('ROW_ALT', $title);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setVariable('ROW_NBSP', '&nbsp;');
		}

		$this->tpl->setCurrentBlock('container_standard_row');
		$this->tpl->setVariable('BLOCK_ROW_CONTENT', $html);
		
		if ($relatedHeader !== '') {
			$relatedHeader = 'th_selected_items ' . $relatedHeader;
		} else {
			$relatedHeader = 'th_selected_items';
		}

		$this->tpl->setVariable('BLOCK_ROW_HEADERS', $relatedHeader);

		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock('container_row');
	}
}