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
	 * @var ilMMItemFacade
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


	public function __construct(ilCtrl $ctrl, \ILIAS\UI\Factory $ui_fa, \ILIAS\UI\Renderer $ui_re, ilLanguage $lng, ilMMItemFacade $item) {
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
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('slate_title_default'), $this->lng->txt('slate_title_default_byline'))->withRequired(true);
		if (!$this->item_facade->isEmpty()) {
			$title = $title->withValue($this->item_facade->getDefaultTitle());
		}
		$items[self::F_TITLE] = $title;

		$type = $this->ui_fa->input()->field()->radio($this->lng->txt('slate_type'), $this->lng->txt('slate_type_byline'))
			->withOption(self::$type_mapping[TopParentItem::class], 'Main Menu Item with Subitems')
			->withOption(self::$type_mapping[TopLinkItem::class], 'Link')
			->withValue(self::$type_mapping[TopParentItem::class])->withRequired(true);
		if (!$this->item_facade->isEmpty()) {
			if (isset(self::$type_mapping[$this->item_facade->getGSItemClassName()])) {
				$type = $type->withValue(self::$type_mapping[$this->item_facade->getGSItemClassName()]);
			}
		}
		$items[self::F_TYPE] = $type;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('slate_active'), $this->lng->txt('slate_active_byline'));
		if (!$this->item_facade->isEmpty()) {
			$active = $active->withValue($this->item_facade->isActive());
		}
		$items[self::F_ACTIVE] = $active;

		// RETURN FORM
		$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt('add_slate'));

		$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_UPDATE), [$section]);
	}


	public function save() {
		global $DIC;
		$r = new ilMMItemRepository($DIC->globalScreen()->storage());
		$form = $this->form->withRequest($DIC->http()->request());
		$data = $form->getData();

		if ($this->item_facade->isEmpty()) {
			// FSX TODO create custon item, set type etc.
			$r->create($this->item_facade);
		}
		$this->item_facade->setDefaultTitle((string)$data[0][self::F_TITLE]);
		$this->item_facade->setActiveStatus((bool)$data[0][self::F_ACTIVE]);

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
