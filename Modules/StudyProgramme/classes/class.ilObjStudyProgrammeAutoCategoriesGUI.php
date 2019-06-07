<?php

declare(strict_types=1);

/**
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilObjStudyProgrammeAutoCategoriesGUI
{
	const F_CATEGORY_REF = 'f_cr';
	const F_CATEGORY_ORIGINAL_REF = 'f_cr_org';
	const CHECKBOX_CATEGORY_REF_IDS = 'c_catids';

	/**
	 * @var ilTemplate
	 */
	public $tpl;

	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @var ilToolbarGUI
	 */
	public $toolbar;

	/**
	 * @var ilLng
	 */
	public $lng;

	/**
	 * @var int | null
	 */
	public $prg_ref_id;

	/**
	 * @var ilObjStudyProgramme | null
	 */
	public $object;

	/**
	 * @var ILIAS\UI\Factory
	 */
	public $ui_factory;

	/**
	 * @var ILIAS\UI\Renderer
	 */
	public $ui_renderer;

	/**
	 * @var Psr\Http\Message\ServerRequestInterface
	 */
	protected $request;


	public function __construct(
		ilTemplate $tpl,
		ilCtrl $ilCtrl,
		ilToolbarGUI $ilToolbar,
		ilLanguage $lng,
		\ILIAS\UI\Factory $ui_factory,
		\ILIAS\UI\Renderer $ui_renderer,
		\GuzzleHttp\Psr7\ServerRequest $request,
		ilTree $tree
	) {
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
		$this->ui_factory = $ui_factory;
		$this->ui_renderer = $ui_renderer;
		$this->request = $request;
		$this->tree = $tree;
	}

	public function executeCommand()
	{

		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case "view":
				$this->view();
				break;

			case "save":
			case "delete":
			case "delete_single":
				$this->$cmd();
				$this->ctrl->redirect($this, 'view');
				break;

			default:
				throw new ilException("ilObjStudyProgrammeAutoCategoriesGUI: ".
									  "Command not supported: $cmd");
		}
	}

	/**
	 * Render.
	 */
	protected function view()
	{
		$collected_modals = [];

		$form = $this->getModalForm();
		$modal = $this->getModal($form);
		$this->getToolbar($modal->getShowSignal());
		$collected_modals[] = $modal;


		$table = new ilStudyProgrammeAutoCategoriesTableGUI($this, "view", "");
		$data = [];
		foreach($this->getObject()->getAutomaticContentCategories() as $ac) {
			$title = $this->getItemPath($ac->getCategoryRefId());
			$usr = $this->getUserRepresentation($ac->getLastEditorId());
			$form = $this->getModalForm($ac->getCategoryRefId());
			$modal = $this->getModal($form);
			$collected_modals[] = $modal;
			$signal = $modal->getShowSignal();
			$actions = $this->getItemAction(
				$ac->getCategoryRefId(),
				$modal->getShowSignal()
			);

			$data[] = [
				$ac,
				$this->ui_renderer->render($title),
				$this->ui_renderer->render($usr),
				$this->ui_renderer->render($actions)
			];
		}
		$table->setData($data);

		$this->tpl->setContent(
			$this->ui_renderer->render($collected_modals)
			.$table->getHTML()
		);
	}

	/**
	 * Store data from (modal-)form.
	 * @return string
	 */
	protected function save()
	{
		$form = $this->getModalForm()->withRequest($this->request);
		$result = $form->getData();

		if(
			array_key_exists(self::F_CATEGORY_ORIGINAL_REF, $_GET)
			&& $_GET[self::F_CATEGORY_ORIGINAL_REF] !== $result[self::F_CATEGORY_REF]
		) {
			$ids = [(int)$_GET[self::F_CATEGORY_ORIGINAL_REF]];
			$this->getObject()->deleteAutomaticContentCategories($ids);
		}

		$this->getObject()->storeAutomaticContentCategory(
			(int)$result[self::F_CATEGORY_REF]
		);
	}

	/**
	 * Delete entries.
	 */
	protected function delete()
	{
		$post = $_POST;
		$field = self::CHECKBOX_CATEGORY_REF_IDS;
		if(array_key_exists($field, $post)) {
			$ids = array_map('intval', $post[$field]);
			$this->getObject()->deleteAutomaticContentCategories($ids);
		}
	}
	/**
	 * Delete single entry.
	 */
	protected function delete_single()
	{
		$get = $_GET;
		$field = self::CHECKBOX_CATEGORY_REF_IDS;
		if(array_key_exists($field, $get)) {
			$ids = [(int)$get[$field]];
			$this->getObject()->deleteAutomaticContentCategories($ids);
		}
	}


	/**
	 * Set ref-id of StudyProgramme before using this GUI.
	 * @param int $prg_ref_id
	 */
	public function setRefId(int $prg_ref_id)
	{
		$this->prg_ref_id = $prg_ref_id;
	}

	/**
	 * Get current StudyProgramme-object.
	 * @return ilObjStudyProgramme
	 */
	protected function getObject()
	{
		if ($this->object === null ||
			(int)$this->object->getRefId() !== $this->prg_ref_id
		) {
			$this->object = ilObjStudyProgramme::getInstanceByRefId($this->prg_ref_id);
		}
		return $this->object;
	}

	/**
	 * Build a modal to add/edit a category.
	 */
	protected function getModal(
		ILIAS\UI\Component\Input\Container\Form\Form $form
	): \ILIAS\UI\Component\Modal\Modal
	{
		$submit_action = "";
		$submit = $this->ui_factory->button()->primary(
			$this->lng->txt('modal_categories_submit'),
			$submit_action
		);

		$modal = $this->ui_factory->modal()->roundtrip(
			$this->lng->txt('modal_categories_title'),
			$form
		);

		return $modal;
	}


	/**
	 * Build the modal's form.
	 */
	protected function getModalForm(int $category_ref_id = null): ILIAS\UI\Component\Input\Container\Form\Form
	{
		$factory = $this->ui_factory->input();
		$f_cat_ref = $factory->field()->numeric($this->lng->txt('Category'));

		if(! is_null($category_ref_id)) {
			$f_cat_ref = $f_cat_ref->withValue($category_ref_id);
			$this->ctrl->setParameter($this, self::F_CATEGORY_ORIGINAL_REF, $category_ref_id);
		}

		$url = $this->ctrl->getLinkTarget($this, "save", "", false, false);
		$form = $factory->container()->form()->standard($url, [self::F_CATEGORY_REF => $f_cat_ref]);
		return $form;
	}

	/**
	 * Setup toolbar.
	 */
	protected function getToolbar(\ILIAS\UI\Component\Signal $add_cat_signal)
	{
		$btn = $this->ui_factory->button()->primary($this->lng->txt('add_category'),'')
			->withOnClick($add_cat_signal);
		$this->toolbar->addComponent($btn);

	}

	protected function getItemAction(
		int $cat_ref_id,
		\ILIAS\UI\Component\Signal $signal
	): \ILIAS\UI\Component\Dropdown\Standard {

		$items = [];
		$items[] =  $this->ui_factory->button()->shy($this->lng->txt('edit'), '')
			->withOnClick($signal);

		$this->ctrl->setParameter($this, self::CHECKBOX_CATEGORY_REF_IDS, $cat_ref_id);
		$items[] =  $this->ui_factory->button()->shy(
			$this->lng->txt('delete'),
			$this->ctrl->getLinkTarget($this, 'delete_single')
		);

		$dd = $this->ui_factory->dropdown()->standard($items);
		return $dd;
	}

	protected function getUserRepresentation(int $usr_id): \ILIAS\UI\Component\Button\Shy
	{
		$username = ilObjUser::_lookupName($usr_id);
		$editor = implode(' ', [
			$username['firstname'],
			$username['lastname'],
			'('.$username['login'] .')'
		]);
		$url = ''; //ilLink::_getStaticLink($usr_id, 'usrf');
		return $this->ui_factory->button()->shy($editor, $url);
	}

	protected function getItemPath(int $cat_ref_id): \ILIAS\UI\Component\Button\Shy
	{
	$url = ilLink::_getStaticLink($cat_ref_id, 'cat');


	$hops = array_map(function($c) {
			return ilObject::_lookupTitle($c["obj_id"]);
		},
		$this->tree->getPathFull($cat_ref_id)
	);
	$path = implode(' > ', $hops);
	return $this->ui_factory->button()->shy($path, $url);
	}


}
