<?php

use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * Class ilMMTopItemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemFormGUI {

	protected static $type_mapping
		= [
			TopParentItem::class => 1,
			TopLinkItem::class   => 2,
		];
	/**
	 * @var Standard
	 */
	private $form;
	/**
	 * @var \ILIAS\GlobalScreen\MainMenu\isItem
	 */
	private $gs_item;
	/**
	 * @var ilMainMenuCollector
	 */
	private $collector;
	/**
	 * @var ilMMItemStorage
	 */
	private $mm_item;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ILIAS\UI\Factory
	 */
	protected $ui_fa;
	/**
	 * @var ILIAS\UI\Renderer
	 */
	protected $ui_re;


	/**
	 * ilMMTopItemFormGUI constructor.
	 *
	 * @param ilCtrl             $ctrl
	 * @param \ILIAS\UI\Factory  $ui_fa
	 * @param \ILIAS\UI\Renderer $ui_re
	 */
	public function __construct(ilCtrl $ctrl, \ILIAS\UI\Factory $ui_fa, \ILIAS\UI\Renderer $ui_re, ilLanguage $lng, ilMMItemStorage $item) {
		global $DIC;
		$this->ctrl = $ctrl;
		$this->ui_fa = $ui_fa;
		$this->ui_re = $ui_re;
		$this->lng = $lng;
		$this->collector = new ilMainMenuCollector($DIC->globalScreen()->storage());
		$this->mm_item = $item;
		if (!$this->mm_item->isEmpty()) {
			global $DIC;
			$this->gs_item = $this->collector->getSingleItem($DIC->globalScreen()->identification()->fromSerializedIdentification($item->getIdentification()));
			$this->ctrl->saveParameterByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::IDENTIFIER);
		}

		$this->initForm();
	}



	private function initForm() {
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('slate_title_default'), $this->lng->txt('slate_title_default_byline'))->withRequired(true);
		if (!$this->mm_item->isEmpty()) {
			$title = $title->withValue($this->mm_item->getDefaultTitle());
		}
		$items[] = $title;

		$type = $this->ui_fa->input()->field()->radio($this->lng->txt('slate_type'), $this->lng->txt('slate_type_byline'))
			->withOption(self::$type_mapping[TopParentItem::class], 'Main Menu Item with Subitems')
			->withOption(self::$type_mapping[TopLinkItem::class], 'Link')
			->withValue(self::$type_mapping[TopParentItem::class])->withRequired(true);
		if (!$this->mm_item->isEmpty()) {
			$gs_item_class = get_class($this->gs_item);
			if (isset(self::$type_mapping[$gs_item_class])) {
				$type = $type->withValue(self::$type_mapping[$gs_item_class]);
			}
		}
		$items[] = $type;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('slate_active'), $this->lng->txt('slate_active_byline'));
		if (!$this->mm_item->isEmpty()) {
			$active = $active->withValue($this->mm_item->isActive());
		}
		$items[] = $active;

		// RETURN FORM
		$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt('add_slate'));

		$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_UPDATE), [$section]);
	}


	public function save() {
		global $DIC;
		$form = $this->form->withRequest($DIC->http()->request());
		$data = $form->getData();
		echo '<pre>' . print_r($data, 1) . '</pre>';
		exit;
	}


	/**
	 * @return string
	 */
	public function getHTML(): string {
		return $this->ui_re->render([$this->form]);
	}
}
