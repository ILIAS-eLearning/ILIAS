<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;

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
		ilObjLearningSequence $ls_object,
		bool $has_items,
		int $usr_id,
		ilCtrl $ctrl,
		ilLanguage $lng,
		ilGlobalPageTemplate $tpl,
		ilToolbarGUI $toolbar,
		ilAccess $access,
		ILIAS\UI\Factory $ui_factory,
		ILIAS\UI\Renderer $ui_renderer,
		ilLSCurriculumBuilder $curriculum_builder,
		ilLSPlayer $player
	) {
		$this->ls_object = $ls_object;
		$this->has_items = $has_items;
		$this->usr_id = $usr_id;
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->toolbar = $toolbar;
		$this->access = $access;
		$this->ui_factory = $ui_factory;
		$this->renderer = $ui_renderer;
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
		$has_items = $this->has_items;

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
		$curriculum = $this->curriculum_builder->getLearnerCurriculum();
		return array($curriculum);
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
