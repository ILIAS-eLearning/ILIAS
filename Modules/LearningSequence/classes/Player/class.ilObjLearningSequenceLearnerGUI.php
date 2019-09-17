<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilObjLearningSequenceLearnerGUI
 */
class ilObjLearningSequenceLearnerGUI
{
	const CMD_STANDARD = 'learnerView';
	const CMD_EXTRO = 'learnerViewFinished';
	const CMD_UNSUBSCRIBE = 'unsubscribe';
	const CMD_VIEW = 'view';
	const CMD_START = 'start';
	const PARAM_LSO_NEXT_ITEM = 'lsoni';
	const LSO_CMD_NEXT = 'lson';
	const LSO_CMD_PREV = 'lsop';

	/**
	 * @var LSLearnerItems[]
	 */
	protected $ls_learner_items;

	public function __construct(
		ilObjLearningSequence $ls_object,
		int $usr_id,
		array $ls_learner_items,
		int $current_item,
		ilCtrl $ctrl,
		ilLanguage $lng,
		ilGlobalTemplateInterface $tpl,
		ilToolbarGUI $toolbar,
		ilKioskModeService $kiosk_mode_service,
		ilAccess $access,
		ilSetting $il_settings,
		ILIAS\UI\Factory $ui_factory,
		ILIAS\UI\Renderer $ui_renderer,
		ILIAS\Data\Factory $data_factory
	) {
		$this->ls_object = $ls_object;
		$this->usr_id = $usr_id;
		$this->ls_learner_items = $ls_learner_items;
		$this->current_item = $current_item;
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->toolbar = $toolbar;
		$this->kiosk_mode_service = $kiosk_mode_service;
		$this->access = $access;
		$this->il_settings = $il_settings;
		$this->ui_factory = $ui_factory;
		$this->renderer = $ui_renderer;
		$this->data_factory = $data_factory;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_EXTRO:
				$this->view($cmd);
				break;
			case self::CMD_START:
				$this->addMember($this->usr_id);
				$this->ctrl->redirect($this, self::CMD_VIEW);
				break;
			case self::CMD_UNSUBSCRIBE:
				if ($this->ls_object->userMayUnparticipate()) {
					$this->removeMember($this->usr_id);
				}
				$this->ctrl->redirect($this, self::CMD_STANDARD);
				break;
			case self::CMD_VIEW:
				$this->play();
				break;
			default:
				throw new ilException(
					"ilObjLearningSequenceLearnerGUI: ".
					"Command not supported: $cmd"
				);
		}
	}

	protected function view(string $cmd)
	{
		$content = $this->getWrappedHTML($this->getMainContent($cmd));
		$curriculum = $this->getWrappedHTML($this->getCurriculum());

		$this->initToolbar($cmd);
		$this->tpl->setContent($content);
		$this->tpl->setRightContent($curriculum);
	}

	protected function addMember(int $usr_id)
	{
		$admins = $this->ls_object->getLearningSequenceAdminIds();
		if(! in_array($usr_id, $admins)) {
			$this->ls_object->join($usr_id);
		}
	}

	protected function removeMember(int $usr_id)
	{
		$this->ls_object->leave($usr_id);
	}

	protected function initToolbar(string $cmd)
	{
		$is_member = $this->ls_object->isMember($this->usr_id);
		$completed = $this->ls_object->isCompletedByUser($this->usr_id);
		$has_items = count($this->ls_learner_items) > 0;

		if (! $is_member) {
			if ($has_items) {
				$may_subscribe = $this->ls_object->userMayJoin();
				if($may_subscribe) {
					$this->toolbar->addButton(
						$this->lng->txt("lso_player_start"),
						$this->ctrl->getLinkTarget($this, self::CMD_START)
					);
				}
			}

		} else {

			if (! $completed) {
				if ($has_items) {
					$state_db =  $this->ls_object->getStateDB();
					$obj_ref_id = (int)$this->ls_object->getRefId();
					$first_access = $state_db->getFirstAccessFor(
						$obj_ref_id,
						array($this->usr_id)
					)[$this->usr_id];

					$label = "lso_player_resume";
					if($first_access === -1) {
						$label = "lso_player_start";
					}

					$this->toolbar->addButton(
						$this->lng->txt($label),
						$this->ctrl->getLinkTarget($this, self::CMD_VIEW)
					);
				}
			} else {
				if ($has_items) {
					$this->toolbar->addButton(
						$this->lng->txt("lso_player_review"),
						$this->ctrl->getLinkTarget($this, self::CMD_VIEW)
					);
				}
				if ($cmd === self::CMD_STANDARD) {
					$this->toolbar->addButton(
						$this->lng->txt("lso_player_extro"),
						$this->ctrl->getLinkTarget($this, self::CMD_EXTRO)
					);
				}
				if ($cmd === self::CMD_EXTRO) {
					$this->toolbar->addButton(
						$this->lng->txt("lso_player_abstract"),
						$this->ctrl->getLinkTarget($this, self::CMD_STANDARD)
					);
				}
			}

			$may_unsubscribe = $this->ls_object->userMayUnparticipate();
			if ($may_unsubscribe) {
				$this->toolbar->addButton(
					$this->lng->txt("unparticipate"),
					$this->ctrl->getLinkTarget($this, self::CMD_UNSUBSCRIBE)
				);
			}

		}
	}

	private function getWrappedHTML(array $components): string
	{
		array_unshift (
			$components,
			$this->ui_factory->legacy('<div class="ilLSOLearnerView">')
		);
		$components[] = $this->ui_factory->legacy('</div>');

		return $this->renderer->render($components);
	}

	private function getCurriculum(): array
	{
		$current_position = 0;
		foreach ($this->ls_learner_items as $index=>$item) {
			if($item->getRefId() === $this->current_item) {
				$current_position = $index;
			}
		}

		$curriculum = $this
			->getCurriculumBuilder($this->ls_learner_items)
			->getLearnerCurriculum();

		if(count($this->ls_learner_items) > 0 ) {
			$curriculum = $curriculum->withActive($current_position);
		}
		return array($curriculum);
	}

	/**
	 * @param LSLearnerItem[] 	$items
	 */
	public function getCurriculumBuilder(array $items, LSUrlBuilder $url_builder=null): ilLSCurriculumBuilder
	{
		return new ilLSCurriculumBuilder(
			$items,
			$this->ui_factory,
			$this->lng,
			ilLSPlayer::LSO_CMD_GOTO,
			$url_builder
		);
	}

	private function getMainContent(string $cmd): array
	{
		$settings = $this->ls_object->getLSSettings();

		if ($cmd === self::CMD_STANDARD) {
			$txt = $settings->getAbstract();
			$img = $settings->getAbstractImage();
		}

		if ($cmd === self::CMD_EXTRO) {
			$txt = $settings->getExtro();
			$img = $settings->getExtroImage();
		}

		$contents = [$this->ui_factory->legacy($txt)];
		if (! is_null($img)) {
			$contents[] = $this->ui_factory->image()->responsive($img, '');
		}

		return $contents;
	}


	protected function play()
	{
		//enforce tree is visible/active for ToC
		//(this does not work for first item, since there is no page-change)
		//how is this done?

		if(!$_SESSION["lso_old_il_rep_mode"]) {
			$_SESSION["lso_old_il_rep_mode"] = $_SESSION["il_rep_mode"];
		}
		$_SESSION["il_rep_mode"] = 'tree';


		$player = $this->getSequencePlayer(
			self::CMD_VIEW, $this->usr_id
		);
		$html = $player->render($_GET, $_POST);

		if($html === 'EXIT::' .$player::LSO_CMD_SUSPEND) {
			$cmd = self::CMD_STANDARD;
		}
		if($html === 'EXIT::' .$player::LSO_CMD_FINISH) {
			$cmd = self::CMD_EXTRO;
		}
		if(is_null($html)) {
			$cmd = self::CMD_STANDARD;
		}

		if(is_null($cmd)){
			print $html;
			exit();
		} else {
			$_SESSION["il_rep_mode"] = $_SESSION["lso_old_il_rep_mode"];

			$href = $this->ctrl->getLinkTarget($this, $cmd, '', false, false);
			\ilUtil::redirect($href);
		}
	}

	/**
	 * factors the player
	 */
	public function getSequencePlayer(string $player_command, int $usr_id): ilLSPlayer
	{
		$lso_ref_id = $this->ls_object->getRefId();
		$lso_title = $this->ls_object->getTitle();

		$player_url = $this->ctrl->getLinkTarget($this, $player_command, '', false, false);
		$items = $this->ls_object->getLSLearnerItems($usr_id);
		$url_builder = $this->getUrlBuilder($player_url);

		$curriculum_builder = $this->getCurriculumBuilder(
			$items,
			$url_builder
		);

		$state_db = $this->ls_object->getStateDB();

		$control_builder = new LSControlBuilder(
			$this->ui_factory,
			$url_builder,
			$this->lng
		);

		$view_factory = new ilLSViewFactory(
			$this->kiosk_mode_service,
			$this->lng,
			$this->access
		);

		$kiosk_renderer = $this->getKioskRenderer($url_builder);

		return new ilLSPlayer(
			$lso_ref_id,
			$lso_title,
			$usr_id,
			$items,
			$state_db,
			$control_builder,
			$url_builder,
			$curriculum_builder,
			$view_factory,
			$kiosk_renderer,
			$this->ui_factory
		);
	}

	public function getUrlBuilder(string $player_url): LSUrlBuilder
	{
		$player_url = $this->data_factory->uri(ILIAS_HTTP_PATH .'/'	.$player_url);
		return new LSUrlBuilder($player_url);
	}

	protected function getKioskRenderer(LSUrlBuilder $url_builder)
	{
		if (!$this->kiosk_renderer) {
			$kiosk_template = new ilTemplate("tpl.kioskpage.html", true, true, 'Modules/LearningSequence');

			$toc_gui = new ilLSTOCGUI($url_builder, $this->tpl, $this->ctrl);
			$loc_gui = new ilLSLocatorGUI($url_builder, $this->ui_factory);

			$window_title = $this->il_settings->get('short_inst_name');
			if($window_title === false) {
				$window_title = 'ILIAS';
			}

			$this->kiosk_renderer = new ilKioskPageRenderer(
				$this->tpl,
				$this->renderer,
				$kiosk_template,
				$toc_gui,
				$loc_gui,
				$window_title
			);
		}

		return $this->kiosk_renderer;
	}

}
