<?php

declare(strict_types = 1);

/**
 * Class ilObjStudyProgrammeAutoMembershipsGUI
 *
 * @author: Nils Haagen  <nils.haagen@concepts-and-training.de>
 *
 */
class ilObjStudyProgrammeAutoMembershipsGUI
{
	const CHECKBOX_SOURCE_IDS = 'c_amsids';

	const F_SOURCE_TYPE = 'f_st';
	const F_SOURCE_ID = 'f_sid';
	const F_ORIGINAL_SOURCE_TYPE = 'f_st_org';
	const F_ORIGINAL_SOURCE_ID = 'f_sid_org';
	const CMD_DELETE_SINGLE = 'deleteSingle';

 	/**
	 * @var ilTemplate
	 */
	public $tpl;
 	/**c
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
			case "delete":
			case "save":
			case self::CMD_DELETE_SINGLE:
				$this->$cmd();
				$this->ctrl->redirect($this, 'view');
				break;
			default:
				throw new ilException("ilObjStudyProgrammeAutoMembershipsGUI: ".
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
 		$data = [];
		foreach($this->getObject()->getAutomaticMembershipSources() as $ams) {

			//$title = $this->getItemPath($ams->getCategoryRefId());
			$title = 'title';

			$usr = $this->getUserRepresentation($ams->getLastEditorId());
			$form = $this->getModalForm($ams->getSourceType(), $ams->getSourceId());
			$modal = $this->getModal($form);
			$collected_modals[] = $modal;
			$signal = $modal->getShowSignal();

			$src_id = $ams->getSourceType() .'-' .$ams->getSourceId();
			$actions = $this->getItemAction($src_id, $modal->getShowSignal());
 			$data[] = [
				$ams,
				//$this->ui_renderer->render($title),
				$title,
				$this->ui_renderer->render($usr),
				$this->ui_renderer->render($actions)
			];
		}
		$table = new ilStudyProgrammeAutoMembershipsTableGUI($this, "view", "");
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

		list($src_type, $sub_values) = array_values($result[self::F_SOURCE_TYPE]);
		$src_id = (int)$sub_values[self::F_SOURCE_ID];

 		if(
			array_key_exists(self::F_ORIGINAL_SOURCE_TYPE, $_GET)
			&& array_key_exists(self::F_ORIGINAL_SOURCE_ID, $_GET)
		) {
			$this->getObject()->deleteAutomaticMembershipSource(
				(string)$_GET[self::F_ORIGINAL_SOURCE_TYPE],
				(int)$_GET[self::F_ORIGINAL_SOURCE_ID]
			);
		}

 		$this->getObject()->storeAutomaticMembershipSource($src_type, $src_id);

	}


 	/**
	 * Delete entries.
	 */
	protected function delete()
	{
		$post = $_POST;
		$field = self::CHECKBOX_SOURCE_IDS;
		if(array_key_exists($field, $post)) {
			foreach ($post[$field] as $src_id) {
				list($type, $id) = explode('-', $src_id);
				$this->getObject()->deleteAutomaticMembershipSource((string)$type, (int)$id);
			}
		}
	}

 	/**
	 * Delete single entry.
	 */
	protected function deleteSingle()
	{
		$get = $_GET;
		$field = self::CHECKBOX_SOURCE_IDS;
		if(array_key_exists($field, $get)) {
			list($type, $id) = explode('-', $get[$field]);
			$this->getObject()->deleteAutomaticMembershipSource((string)$type, (int)$id);
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
	 * Set this GUI's parent gui.
	 * @param ilContainerGUI $a_parent_gui
	 */
	public function setParentGUI(ilContainerGUI $a_parent_gui)
	{
		$this->parent_gui = $a_parent_gui;
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
		$modal = $this->ui_factory->modal()->roundtrip(
			$this->lng->txt('modal_categories_title'),
			$form
		);
 		return $modal;
	}

	protected function addTypeOption(
		ILIAS\UI\Component\Input\Field\Radio $radio,
		ILIAS\UI\Component\Input\Field\Numeric $f_id,
		string $type
	): ILIAS\UI\Component\Input\Field\Radio {

		return $radio->withOption(
			$type,
			$this->lng->txt($type),
			'',
			[self::F_SOURCE_ID => $f_id]
		);
	}

 	/**
	 * Build the modal's form.
	 */
	protected function getModalForm(
		string $source_type = null,
		int $source_id = null
	): ILIAS\UI\Component\Input\Container\Form\Form
	{
		$factory = $this->ui_factory->input();

		$f_id = $factory->field()->numeric($this->lng->txt('membership_source_id'));
 		if(! is_null($source_id)) {
			$f_id = $f_id->withValue($source_id);
 		}

		$f_type = $factory->field()->radio($this->lng->txt('membership_source_type'));
		$f_type = $this->addTypeOption($f_type, $f_id, ilStudyProgrammeAutoMembershipSource::TYPE_ROLE);
		$f_type = $this->addTypeOption($f_type, $f_id, ilStudyProgrammeAutoMembershipSource::TYPE_GROUP);
		$f_type = $this->addTypeOption($f_type, $f_id, ilStudyProgrammeAutoMembershipSource::TYPE_COURSE);
		$f_type = $this->addTypeOption($f_type, $f_id, ilStudyProgrammeAutoMembershipSource::TYPE_ORGU);

 		if(! is_null($source_type)) {
			$f_type = $f_type->withValue($source_type);
		}

 		if(!is_null($source_type) && !is_null($source_id)) {
			$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, $source_id);
			$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, $source_type);
		}

 		$url = $this->ctrl->getLinkTarget($this, "save", "", false, false);
		$form = $factory->container()->form()->standard($url, [self::F_SOURCE_TYPE => $f_type]);
		return $form;
	}

 	/**
	 * Setup toolbar.
	 */
	protected function getToolbar(\ILIAS\UI\Component\Signal $add_cat_signal)
	{
		$btn = $this->ui_factory->button()->primary($this->lng->txt('add_automembership_source'),'')
			->withOnClick($add_cat_signal);
		$this->toolbar->addComponent($btn);
 	}



 	protected function getItemAction(
		string $src_id,
		\ILIAS\UI\Component\Signal $signal
	): \ILIAS\UI\Component\Dropdown\Standard {
 		$items = [];
		$items[] =  $this->ui_factory->button()->shy($this->lng->txt('edit'), '')
			->withOnClick($signal);
 		$this->ctrl->setParameter($this, self::CHECKBOX_SOURCE_IDS, $src_id);
		$items[] =  $this->ui_factory->button()->shy(
			$this->lng->txt('delete'),
			$this->ctrl->getLinkTarget($this, self::CMD_DELETE_SINGLE)
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
		$url = ilLink::_getStaticLink($usr_id, 'usr');
		return $this->ui_factory->button()->shy($editor, $url);
	}

 	/*
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
	*/
}
