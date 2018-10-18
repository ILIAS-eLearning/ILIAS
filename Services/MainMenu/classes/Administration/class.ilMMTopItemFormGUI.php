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
	 * @var ilMMItemFacadeInterface
	 */
	private $item_facade;
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
	const F_ACTIVE = 'active';
	const F_TITLE = 'title';
	const F_TYPE = 'type';


	public function __construct(ilCtrl $ctrl, \ILIAS\UI\Factory $ui_fa, \ILIAS\UI\Renderer $ui_re, ilLanguage $lng, ilMMItemFacadeInterface $item) {
		$this->ctrl = $ctrl;
		$this->ui_fa = $ui_fa;
		$this->ui_re = $ui_re;
		$this->lng = $lng;
		$this->item_facade = $item;
		if (!$this->item_facade->isEmpty()) {
			$this->ctrl->saveParameterByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::IDENTIFIER);
		}

		$this->initForm();
	}


	private function initForm() {
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('topitem_title_default'), $this->lng->txt('topitem_title_default_byline'))->withRequired(true);
		if (!$this->item_facade->isEmpty()) {
			$title = $title->withValue($this->item_facade->getDefaultTitle());
		}
		$items[self::F_TITLE] = $title;

		$type = $this->ui_fa->input()->field()->radio($this->lng->txt('topitem_type'), $this->lng->txt('topitem_type_byline'))
			->withOption(self::$type_mapping[TopParentItem::class], $this->lng->txt('topitem_type_parent'))
			// ->withOption(
			// 	self::$type_mapping[TopLinkItem::class], $this->lng->txt('topitem_type_link'),
			// 	["action" => $this->ui_fa->input()->field()->text("URL"), "external" => $this->ui_fa->input()
			// 		->field()
			// 		->checkbox("Open in new window")]
			// )
			->withValue(self::$type_mapping[TopParentItem::class])
			->withRequired(true);
		if (!$this->item_facade->isEmpty()) {
			if (isset(self::$type_mapping[$this->item_facade->getGSItemClassName()])) {
				$type = $type->withValue(self::$type_mapping[$this->item_facade->getGSItemClassName()]);
			}
		}
		$items[self::F_TYPE] = $type;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('topitem_active'), $this->lng->txt('topitem_active_byline'));
		if (!$this->item_facade->isEmpty()) {
			$active = $active->withValue($this->item_facade->isAvailable());
		}
		$items[self::F_ACTIVE] = $active;

		// RETURN FORM
		if ($this->item_facade->isEmpty()) {
			$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt(ilMMTopItemGUI::CMD_ADD));
			$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_CREATE), [$section]);
		} else {
			$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt(ilMMTopItemGUI::CMD_EDIT));
			$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_UPDATE), [$section]);
		}
	}


	public function save() {
		global $DIC;
		$r = new ilMMItemRepository($DIC->globalScreen()->storage());
		$form = $this->form->withRequest($DIC->http()->request());
		$data = $form->getData();

		$this->item_facade->setAction((string)$data[0]['action']);
		$this->item_facade->setDefaultTitle((string)$data[0][self::F_TITLE]);
		$this->item_facade->setActiveStatus((bool)$data[0][self::F_ACTIVE]);

		if ($this->item_facade->isEmpty()) {
			$type = array_search((int)$data[0][self::F_TYPE], self::$type_mapping);
			if ($type) {
				$this->item_facade->setType((string)$data[0][self::F_TYPE]);
			}
			$r->createItem($this->item_facade);
		}

		$r->updateItem($this->item_facade);

		return true;
	}


	/**
	 * @return string
	 */
	public function getHTML(): string {
		return $this->ui_re->render([$this->form]);
	}
}
