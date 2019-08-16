<?php declare(strict_types=1);

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

	public function __construct(
		int $ls_ref_id,
		bool $has_items,
		$first_access,
		int $usr_id,
		ilAccess $access,
		ilCtrl $ctrl,
		ilLanguage $lng,
		ilGlobalPageTemplate $tpl,
		ilToolbarGUI $toolbar,
		ILIAS\UI\Factory $ui_factory,
		ILIAS\UI\Renderer $ui_renderer,
		ilLearningSequenceRoles $roles,
		ilLearningSequenceSettings $settings,
		ilLSCurriculumBuilder $curriculum_builder,
		ilLSPlayer $player
	) {
		$this->ls_object = $ls_object;
		$this->ls_ref_id = $ls_ref_id;
		$this->has_items = $has_items;
		$this->first_access = $first_access;
		$this->usr_id = $usr_id;
		$this->access = $access;
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->toolbar = $toolbar;
		$this->ui_factory = $ui_factory;
		$this->renderer = $ui_renderer;
		$this->roles = $roles;
		$this->settings = $settings;
		$this->curriculum_builder = $curriculum_builder;
		$this->player = $player;
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
				if ($this->userMayUnparticipate()) {
					$this->roles->leave($this->usr_id);
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
		$admins = $this->roles->getLearningSequenceAdminIds();
		if(! in_array($usr_id, $admins)) {
			$this->roles->join($usr_id);
		}
	}


	protected function userMayUnparticipate(): bool
	{
		return $this->access->checkAccess('unparticipate', '', $this->ls_ref_id);
	}

	protected function userMayJoin(): bool
	{
		return $this->access->checkAccess('participate', '', $this->ls_ref_id);
	}

	protected function initToolbar(string $cmd)
	{
		$is_member = $this->roles->isMember($this->usr_id);
		$completed = $this->roles->isCompletedByUser($this->usr_id);
		$has_items = $this->has_items;

		if (! $is_member) {
			if ($has_items) {
				$may_subscribe = $this->userMayJoin();
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
					$label = "lso_player_resume";
					if($this->first_access === -1) {
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

			$may_unsubscribe = $this->userMayUnparticipate();
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
		$curriculum = $this->curriculum_builder->getLearnerCurriculum();
		return array($curriculum);
	}

	private function getMainContent(string $cmd): array
	{
		if ($cmd === self::CMD_STANDARD) {
			$txt = $this->settings->getAbstract();
			$img = $this->settings->getAbstractImage();
		}

		if ($cmd === self::CMD_EXTRO) {
			$txt = $this->settings->getExtro();
			$img = $this->settings->getExtroImage();
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

		$html = $this->player->render($_GET, $_POST);

		if($html === 'EXIT::' .$this->player::LSO_CMD_SUSPEND) {
			$cmd = self::CMD_STANDARD;
		}
		if($html === 'EXIT::' .$this->player::LSO_CMD_FINISH) {
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
}
