<?php

declare(strict_types=1);

/**
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilObjStudyProgrammeAutoCategoriesGUI
{
	const F_TITLE = 'f_t';
	const F_CATEGORY_REF = 'f_cr';

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
		\GuzzleHttp\Psr7\ServerRequest $request
	) {
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
		$this->ui_factory = $ui_factory;
		$this->ui_renderer = $ui_renderer;
		$this->request = $request;
	}

	public function executeCommand()
	{

		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case "view":
				$this->view();
				break;

			case "save":
				$this->save();
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
		$table = new ilStudyProgrammeAutoCategoriesTableGUI($this, "view", "");
		$modal = $this->getModal();
		$this->getToolbar($modal->getShowSignal());

		$this->tpl->setContent(
			$this->ui_renderer->render($modal)
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

		$this->getObject()->storeAutomaticContentCategory(
			(string)$result[self::F_TITLE],
			(int)$result[self::F_CATEGORY_REF]
		);
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
	protected function getModal(): \ILIAS\UI\Component\Modal\Modal
	{
		$submit_action = "";
		$submit = $this->ui_factory->button()->primary(
			$this->lng->txt('modal_categories_submit'),
			$submit_action
		);

		$modal = $this->ui_factory->modal()->roundtrip(
			$this->lng->txt('modal_categories_title'),
			$this->getModalForm()
		)
		//->withActionButtons([$submit])
		;
		return $modal;
	}


	/**
	 * Build the modal's form.
	 */
	protected function getModalForm(): ILIAS\UI\Component\Input\Container\Form\Form
	{
		$factory = $this->ui_factory->input();
		$url = $this->ctrl->getLinkTarget($this, "save", "", false, false);

		$f_title = $factory->field()->text($this->lng->txt('title'));
		$f_cat_ref = $factory->field()->numeric($this->lng->txt('Category'));
		$form = $factory->container()->form()->standard(
			$url,
			[
				self::F_TITLE => $f_title,
				self::F_CATEGORY_REF => $f_cat_ref
			]
		);
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
}
