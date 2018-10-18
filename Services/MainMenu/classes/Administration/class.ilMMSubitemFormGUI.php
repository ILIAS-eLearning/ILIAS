<?php

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMMSubitemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubitemFormGUI {

	const F_TITLE = "title";
	const F_TYPE = "type";
	const F_PARENT = "parent";
	const F_ACTIVE = "active";
	/**
	 * @var ilMMItemRepository
	 */
	private $repository;
	/**
	 * @var Standard
	 */
	private $form;
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
	 * @var ilMMItemFacadeInterface
	 */
	private $item_facade;


	/**
	 * ilMMSubitemFormGUI constructor.
	 *
	 * @param ilCtrl                  $ctrl
	 * @param Factory                 $ui_fa
	 * @param Renderer                $ui_re
	 * @param ilLanguage              $lng
	 * @param ilMMItemFacadeInterface $item
	 * @param ilMMItemRepository      $repository
	 */
	public function __construct(ilCtrl $ctrl, Factory $ui_fa, Renderer $ui_re, ilLanguage $lng, ilMMItemFacadeInterface $item, ilMMItemRepository $repository) {
		$this->ctrl = $ctrl;
		$this->ui_fa = $ui_fa;
		$this->ui_re = $ui_re;
		$this->lng = $lng;
		$this->item_facade = $item;
		$this->repository = $repository;
		if (!$this->item_facade->isEmpty()) {
			$this->ctrl->saveParameterByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::IDENTIFIER);
		}

		$this->initForm();
	}


	private function initForm() {
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('sub_title_default'), $this->lng->txt('sub_title_default_byline'));
		if (!$this->item_facade->isEmpty()) {
			$title = $title->withValue($this->item_facade->getDefaultTitle());
		}
		$items[self::F_TITLE] = $title;

		$type = $this->ui_fa->input()
			->field()
			->radio($this->lng->txt('sub_type'), $this->lng->txt('sub_type_byline'))->withRequired(true);
		foreach ($this->repository->getPossibleSubItemTypesForForm() as $class_name => $representation) {
			$type = $type->withOption($class_name, $representation);
		}
		// ->withOption(1, 'Link', [$this->ui_fa->input()->field()->text("URL"), $this->ui_fa->input()->field()->checkbox("Open in new window")])
		// ->withOption(2, 'Repository Link', [$this->ui_fa->input()->field()->text("URL")])
		// ->withOption(3, 'Link List')
		// ->withOption(4, 'Separator');
		// ->withOption(5, 'Custom Entry Type from Plugin XY')->withValue(1);
		if (!$this->item_facade->isEmpty()) {
			$type = $type->withValue($this->item_facade->getType());
		}
		$items[self::F_TYPE] = $type;

		$mm_item = $this->ui_fa->input()->field()->select($this->lng->txt('sub_parent'), $this->repository->getPossibleParentsForFormAndTable())->withRequired(true);
		if (!$this->item_facade->isEmpty()) {
			$mm_item = $mm_item->withValue($this->item_facade->getParentIdentificationString());
		}
		$items[self::F_PARENT] = $mm_item;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('sub_active'), $this->lng->txt('sub_active_byline'));
		if (!$this->item_facade->isEmpty()) {
			$active = $active->withValue($this->item_facade->isAvailable());
		}
		$items[self::F_ACTIVE] = $active;

		// RETURN FORM
		if ($this->item_facade->isEmpty()) {
			$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt(ilMMSubItemGUI::CMD_ADD));
			$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_CREATE), [$section]);
		} else {
			$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt(ilMMSubItemGUI::CMD_EDIT));
			$this->form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_UPDATE), [$section]);
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
		$this->item_facade->setParent((string)$data[0][self::F_PARENT]);
		$this->item_facade->setIsTopItm(false);

		if ($this->item_facade->isEmpty()) {
			// $type = array_search((int)$data[0][self::F_TYPE], self::$type_mapping);
			// if ($type) {
			// 	$this->item_facade->setType((string)$data[0][self::F_TYPE]);
			// }
			$this->item_facade->setType(\ILIAS\GlobalScreen\MainMenu\Item\Link::class);
			$r->createItem($this->item_facade);
		}

		$r->updateItem($this->item_facade);

		return true;
	}


	public function getHTML() {
		return $this->ui_re->render([$this->form]);
	}
}
