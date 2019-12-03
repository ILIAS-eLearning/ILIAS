<?php

declare(strict_types = 1);

/**
 * Class ilObjStudyProgrammeAutoMembershipsGUI
 *
 * @author: Nils Haagen  <nils.haagen@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAutoMembershipsGUI: ilPropertyFormGUI
 */
class ilObjStudyProgrammeAutoMembershipsGUI
{
	const ROLEFOLDER_REF_ID = 8;
	const CHECKBOX_SOURCE_IDS = 'c_amsids';

	const F_SOURCE_TYPE = 'f_st';
	const F_SOURCE_ID = 'f_sid';
	const F_ORIGINAL_SOURCE_TYPE = 'f_st_org';
	const F_ORIGINAL_SOURCE_ID = 'f_sid_org';
	const CMD_DELETE_SINGLE = 'deleteSingle';
	const CMD_ENABLE = 'enable';
	const CMD_DISABLE = 'disable';

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
		ilGlobalTemplateInterface $tpl,
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

		$tpl->addJavaScript("Services/JavaScript/js/Basic.js");
		$tpl->addJavaScript("Services/Form/js/Form.js");
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
			case self::CMD_DISABLE:
			case self::CMD_ENABLE:
				$this->$cmd();
				$this->ctrl->redirect($this, 'view');
				break;
			case "getAsynchModalOutput":
				$this->getAsynchModalOutput();
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
		$modal = $this->getModal();
		$this->getToolbar($modal->getShowSignal());
		$collected_modals[] = $modal;
		$data = [];
		foreach($this->getObject()->getAutomaticMembershipSources() as $ams) {
			$title = $this->getTitleRepresentation($ams);
			$usr = $this->getUserRepresentation($ams->getLastEditorId());
			$modal = $this->getModal($ams->getSourceType(), $ams->getSourceId());
			$collected_modals[] = $modal;

			$src_id = $ams->getSourceType() .'-' .$ams->getSourceId();
			$actions = $this->getItemAction(
				$src_id,
				$modal->getShowSignal(),
				$ams->isEnabled()
			);

			$data[] = [
				$ams,
				$this->ui_renderer->render($title),
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

	protected function save()
	{
		$form = $this->getForm();
		$form->checkInput();
		$form->setValuesByPost();

		$post = $_POST;
		$src_type = $post[self::F_SOURCE_TYPE];
		$src_id = (int)$post[self::F_SOURCE_ID.$src_type];

		if(
			array_key_exists(self::F_ORIGINAL_SOURCE_TYPE, $post) &&
			array_key_exists(self::F_ORIGINAL_SOURCE_ID, $post)
		) {
			$this->getObject()->deleteAutomaticMembershipSource(
				(string)$post[self::F_ORIGINAL_SOURCE_TYPE],
				(int)$post[self::F_ORIGINAL_SOURCE_ID]
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
	 * Enable single entry.
	 */
	protected function enable()
	{
		$get = $_GET;
		$field = self::CHECKBOX_SOURCE_IDS;
		if(array_key_exists($field, $get)) {
			list($type, $id) = explode('-', $get[$field]);
			$this->getObject()->enableAutomaticMembershipSource((string)$type, (int)$id);
		}
	}

	/**
	 * Disable single entry.
	 */
	protected function disable()
	{
		$get = $_GET;
		$field = self::CHECKBOX_SOURCE_IDS;
		if(array_key_exists($field, $get)) {
			list($type, $id) = explode('-', $get[$field]);
			$this->getObject()->disableAutomaticMembershipSource((string)$type, (int)$id);
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

	protected function getModal(
		string $source_type = null,
		int $source_id = null
	) {
		$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, $source_type);
		$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, $source_id);
		$link = $this->ctrl->getLinkTarget($this, "getAsynchModalOutput", "", true);
		$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_TYPE, null);
		$this->ctrl->setParameter($this, self::F_ORIGINAL_SOURCE_ID, null);

		$modal = $this->ui_factory->modal()->roundtrip(
			'',
			[]
		)->withAsyncRenderUrl(
			$link
		);

		return $modal;
	}

	protected function getAsynchModalOutput()
	{
		$current_src_type = null;
		if(
			array_key_exists(self::F_ORIGINAL_SOURCE_TYPE, $_GET) &&
			! is_null($_GET[self::F_ORIGINAL_SOURCE_TYPE])
		) {
			$current_src_type = $_GET[self::F_ORIGINAL_SOURCE_TYPE];
		}
		$current_src_id = null;
		if(
			array_key_exists(self::F_ORIGINAL_SOURCE_ID, $_GET) &&
			! is_null($_GET[self::F_ORIGINAL_SOURCE_ID])
		) {
			$current_src_id = (int)$_GET[self::F_ORIGINAL_SOURCE_ID];
		}
		$form = $this->getForm($current_src_type, $current_src_id);
		$form_id = "form_".$form->getId();
		$submit = $this->ui_factory->button()->primary($this->txt('search'), "#")->withOnLoadCode(
			function ($id) use ($form_id) {
				return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
			});
		$modal = $this->ui_factory->modal()->roundtrip(
			$this->txt('modal_categories_title'),
			$this->ui_factory->legacy($form->getHtml())
		)->withActionButtons([$submit]);

		echo $this->ui_renderer->renderAsync($modal);
		exit;
	}

	protected function getForm(
		string $source_type = null,
		int $source_id = null
	) : ilPropertyFormGUI {
		$form = new ilPropertyFormGUI();

		if(is_null($source_type)) {
			$source_type = "";
		}
		if(is_null($source_id)) {
			$source_id = "";
		}
		$form->setId(uniqid((string)$source_type.(string)$source_id));
		$form->setFormAction($this->ctrl->getFormAction($this, 'save'));

		$rgroup = new ilRadioGroupInputGUI($this->txt('membership_source_type'), self::F_SOURCE_TYPE);
		$rgroup->setValue($source_type);
		$form->addItem($rgroup);

		$radio_role = new ilRadioOption(
			$this->txt(ilStudyProgrammeAutoMembershipSource::TYPE_ROLE),
			ilStudyProgrammeAutoMembershipSource::TYPE_ROLE
		);

		$ni_role = new ilNumberInputGUI(
			'',
			self::F_SOURCE_ID.ilStudyProgrammeAutoMembershipSource::TYPE_ROLE
		);
		$ni_role->setInfo($this->txt('membership_source_id_byline_objid'));
		$radio_role->addSubItem($ni_role);
		$rgroup->addOption($radio_role);

		$radio_grp = new ilRadioOption(
			$this->txt(ilStudyProgrammeAutoMembershipSource::TYPE_GROUP),
			ilStudyProgrammeAutoMembershipSource::TYPE_GROUP
		);
		$ni_grp = new ilNumberInputGUI(
			'',
			self::F_SOURCE_ID.ilStudyProgrammeAutoMembershipSource::TYPE_GROUP
		);
		$ni_grp->setInfo($this->txt('membership_source_id_byline_refid'));
		$radio_grp->addSubItem($ni_grp);
		$rgroup->addOption($radio_grp);

		$radio_crs = new ilRadioOption(
			$this->txt(ilStudyProgrammeAutoMembershipSource::TYPE_COURSE),
			ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
		);
		$ni_crs = new ilNumberInputGUI(
			'',
			self::F_SOURCE_ID.ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
		);
		$ni_crs->setInfo($this->txt('membership_source_id_byline_refid'));
		$radio_crs->addSubItem($ni_crs);
		$rgroup->addOption($radio_crs);

		$radio_orgu = new ilRadioOption(
			$this->txt(ilStudyProgrammeAutoMembershipSource::TYPE_ORGU),
			ilStudyProgrammeAutoMembershipSource::TYPE_ORGU
		);
		$orgu = new ilRepositorySelector2InputGUI(
			"",
			false,
			self::F_SOURCE_ID.ilStudyProgrammeAutoMembershipSource::TYPE_ORGU
		);
		$orgu->getExplorerGUI()->setSelectableTypes(["orgu"]);
		$orgu->getExplorerGUI()->setTypeWhiteList(["root", "orgu"]);
		if($current_ref_id != "") {
			$orgu->getExplorerGUI()->setPathOpen($current_ref_id);
			$orgu->setValue($current_ref_id);
		}
		$orgu->getExplorerGUI()->setRootId(ilObjOrgUnit::getRootOrgRefId());
		$orgu->getExplorerGUI()->setAjax(false);
		$radio_orgu->addSubItem($orgu);
		$rgroup->addOption($radio_orgu);

		if(
			! is_null($source_type) &&
			! is_null($source_id) &&
			$source_type !== "" &&
			$source_id !== ""
		) {
			switch ($source_type) {
				case ilStudyProgrammeAutoMembershipSource::TYPE_ROLE:
					$ni_role->setValue($source_id);
					break;
				case ilStudyProgrammeAutoMembershipSource::TYPE_GROUP:
					$ni_grp->setValue($source_id);
					break;
				case ilStudyProgrammeAutoMembershipSource::TYPE_COURSE:
					$ni_crs->setValue($source_id);
					break;
				case ilStudyProgrammeAutoMembershipSource::TYPE_ORGU:
					$orgu->setValue($source_id);
					break;
				default:
			}
		}

		$hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_TYPE);
		$hi->setValue($source_type);
		$form->addItem($hi);

		$hi = new ilHiddenInputGUI(self::F_ORIGINAL_SOURCE_ID);
		$hi->setValue($source_id);
		$form->addItem($hi);

		return $form;
	}

	/**
	 * Setup toolbar.
	 */
	protected function getToolbar(\ILIAS\UI\Component\Signal $add_cat_signal)
	{
		$btn = $this->ui_factory->button()->primary($this->txt('add_automembership_source'),'')
			->withOnClick($add_cat_signal);
		$this->toolbar->addComponent($btn);
	}



	protected function getItemAction(
		string $src_id,
		\ILIAS\UI\Component\Signal $signal,
		bool $is_enabled
	): \ILIAS\UI\Component\Dropdown\Standard {
		$items = [];

		$items[] =  $this->ui_factory->button()->shy($this->txt('edit'), '')
			->withOnClick($signal);

		$this->ctrl->setParameter($this, self::CHECKBOX_SOURCE_IDS, $src_id);

		if($is_enabled) {
			$items[] =  $this->ui_factory->button()->shy(
				$this->txt('disable'),
				$this->ctrl->getLinkTarget($this, self::CMD_DISABLE)
			);
		} else {
			$items[] =  $this->ui_factory->button()->shy(
				$this->txt('enable'),
				$this->ctrl->getLinkTarget($this, self::CMD_ENABLE)
			);
		}

		$items[] =  $this->ui_factory->button()->shy(
			$this->txt('delete'),
			$this->ctrl->getLinkTarget($this, self::CMD_DELETE_SINGLE)
		);

		$this->ctrl->clearParameters($this);

		$dd = $this->ui_factory->dropdown()->standard($items);
		return $dd;
	}

	protected function getUserRepresentation(int $usr_id): \ILIAS\UI\Component\Link\Standard
	{
		$username = ilObjUser::_lookupName($usr_id);
		$editor = implode(' ', [
			$username['firstname'],
			$username['lastname'],
			'('.$username['login'] .')'
		]);
		$url = ilLink::_getStaticLink($usr_id, 'usr');
		return $this->ui_factory->link()->standard($editor, $url);
	}


	protected function getTitleRepresentation(
		ilStudyProgrammeAutoMembershipSource $ams
	): \ILIAS\UI\Component\Link\Standard {
		$src_id = $ams->getSourceId();

		$title = "";
		$url = "";
		switch ($ams->getSourceType()) {
			case ilStudyProgrammeAutoMembershipSource::TYPE_ROLE:
				$title = ilObjRole::_lookupTitle($src_id) ?? "-";
				$this->ctrl->setParameterByClass('ilObjRoleGUI', 'obj_id', $src_id);
				$this->ctrl->setParameterByClass('ilObjRoleGUI', 'ref_id', self::ROLEFOLDER_REF_ID);
				$this->ctrl->setParameterByClass('ilObjRoleGUI', 'admin_mode', 'settings');
				$url = $this->ctrl->getLinkTargetByClass(['ilAdministrationGUI', 'ilObjRoleGUI'], 'userassignment' );
				$this->ctrl->clearParametersByClass('ilObjRoleGUI');
				break;

			case ilStudyProgrammeAutoMembershipSource::TYPE_GROUP:
			case ilStudyProgrammeAutoMembershipSource::TYPE_COURSE:
				$hops = array_map(function($c) {
						return ilObject::_lookupTitle($c["obj_id"]);
					},
					$this->tree->getPathFull($src_id)
				);
				$hops = array_slice($hops, 1);
				$title = implode(' > ', $hops) ?? "-";
				break;

			case ilStudyProgrammeAutoMembershipSource::TYPE_ORGU:
				$hops = array_map(function($c) {
						return ilObject::_lookupTitle($c["obj_id"]);
					},
					$this->tree->getPathFull($src_id)
				);
				$hops = array_slice($hops, 3);
				$title = implode(' > ', $hops) ?? "-";
				$url = ilLink::_getStaticLink($src_id, $ams->getSourceType());
				break;
			default:
				throw new \LogicException("This should not happen. Forgot a case in the switch?");
		}

		return $this->ui_factory->link()->standard($title, $url);
	}

	protected function txt(string $code) : string
	{
		return $this->lng->txt($code);
	}
}
