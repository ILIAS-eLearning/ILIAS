<?php

use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMMTopItemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemFormGUI {

	/**
	 * @var \ILIAS\DI\HTTPServices
	 */
	private $http;
	/**
	 * @var ilMMItemRepository
	 */
	private $repository;
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
	 * @param ilCtrl   $ctrl
	 * @param Factory  $ui_fa
	 * @param Renderer $ui_re
	 */
	const F_ACTIVE = 'active';
	const F_TITLE = 'title';
	const F_TYPE = 'type';


	public function __construct(ilCtrl $ctrl, Factory $ui_fa, Renderer $ui_re, ilLanguage $lng, \ILIAS\DI\HTTPServices $http, ilMMItemFacadeInterface $item, ilMMItemRepository $repository) {
		$this->repository = $repository;
		$this->http = $http;
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

		$type = $this->ui_fa->input()->field()->radio($this->lng->txt('topitem_type'), $this->lng->txt('topitem_type_byline'))->withRequired(true);
		$top_item_types_for_form = $this->repository->getPossibleTopItemTypesForForm();

		foreach ($top_item_types_for_form as $classname => $representation) {
			$item = $this->repository->getEmptyItemForTypeString($classname);
			$type_handler = $this->repository->information()->getTypeHandlerForType($item);
			$type = $type->withOption($classname, $representation, $type_handler->getAdditionalFieldsForSubForm());
		}
		$type = $type->withValue(reset(array_keys($top_item_types_for_form)));
		if (!$this->item_facade->isEmpty()) {
			$value = $this->item_facade->getType();
			$type = $type->withValue($value);
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
		$form = $this->form->withRequest($this->http->request());
		$data = $form->getData();

		$type = (string)$data[0][self::F_TYPE]['value'];
		$this->item_facade->setAction((string)$data[0]['action']);
		$this->item_facade->setDefaultTitle((string)$data[0][self::F_TITLE]);
		$this->item_facade->setActiveStatus((bool)$data[0][self::F_ACTIVE]);
		$this->item_facade->setType($type);
		$this->item_facade->setIsTopItm(true);

		if ($this->item_facade->isEmpty()) {
			$this->repository->createItem($this->item_facade);
		}

		$type_specific_data = (array)$data[0][self::F_TYPE]['group_values'];

		$item = $this->repository->getEmptyItemForTypeString($type);
		$type_handler = $this->repository->information()->getTypeHandlerForType($item);
		$type_handler->saveFormFields($this->item_facade->identification(), $type_specific_data);

		$this->repository->updateItem($this->item_facade);

		return true;
	}


	/**
	 * @return string
	 */
	public function getHTML(): string {
		return $this->ui_re->render([$this->form]);
	}
}
