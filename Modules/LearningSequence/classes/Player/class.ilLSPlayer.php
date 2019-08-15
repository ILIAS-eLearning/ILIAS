<?php

declare(strict_types=1);

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\UI\Component\Listing\Workflow\Step;

/**
 * Implementation of KioskMode Player
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSPlayer
{
	const PARAM_LSO_COMMAND = 'lsocmd';
	const PARAM_LSO_PARAMETER = 'lsov';

	const LSO_CMD_NEXT = 'lsonext'; //with param directions
	const LSO_CMD_GOTO = 'lsogoto'; //with param ref_id
	const LSO_CMD_SUSPEND = 'lsosuspend';
	const LSO_CMD_FINISH = 'lsofinish';


	public function __construct(
		string $lso_title,
		ilLSLearnerItemsQueries $ls_items,
		LSControlBuilder $control_builder,
		LSUrlBuilder $url_builder,
		ilLSCurriculumBuilder $curriculum_builder,
		ilLSViewFactory $view_factory,
		ilKioskPageRenderer $renderer,
		ILIAS\UI\Factory $ui_factory
	) {
		$this->lso_title = $lso_title;
		$this->ls_items = $ls_items;
		$this->items = $ls_items->getItems();
		$this->control_builder = $control_builder;
		$this->url_builder = $url_builder;
		$this->curriculum_builder = $curriculum_builder;
		$this->view_factory = $view_factory;
		$this->page_renderer = $renderer;
		$this->ui_factory = $ui_factory;
	}

	public function render(array $get, array $post=null)
	{
		//init state and current item
		$current_item_ref_id = $this->ls_items->getCurrentItemRefId();
		if($current_item_ref_id === 0) {
			$current_item = $this->items[0];
			$current_item_ref_id = $current_item->getRefId();
		} else {
			list($position, $current_item) = $this->findItemByRefId($current_item_ref_id);
		}

		$view = $this->view_factory->getViewFor($current_item);

		$state = $this->ls_items->getStateFor($current_item);
		$state = $this->updateViewState($state, $view, $get, $post);

		//now, digest parameter:
		$command = $_GET[self::PARAM_LSO_COMMAND];
		$param = (int)$_GET[self::PARAM_LSO_PARAMETER];

		switch($command) {
			case self::LSO_CMD_SUSPEND:
			case self::LSO_CMD_FINISH:
				//store state and exit
				$this->ls_items->storeState($state, $current_item_ref_id, $current_item_ref_id);
				return 'EXIT::'. $command;
			case self::LSO_CMD_NEXT:
				$next_item = $this->getNextItem($current_item, $param);
				break;
			case self::LSO_CMD_GOTO:
				list($position, $next_item) = $this->findItemByRefId($param);
				break;
			default: //view-internal / unknown command
				$next_item = $current_item;
		}
		//write State to DB
		$this->ls_items->storeState($state, $current_item_ref_id, $next_item->getRefId());

		//get proper view
		if($next_item !== $current_item) {
			$view = $this->view_factory->getViewFor($next_item);
		}
		//get position
		list($item_position, $item) = $this->findItemByRefId($next_item->getRefId());

		//have the view build controls
		$control_builder = $this->control_builder;
		$view->buildControls($state, $control_builder);
		//amend controls not set by the view
		$this->buildDefaultControls($control_builder, $item, $item_position);

		//content
		$obj_title = $next_item->getTitle();
		$icon = $this->ui_factory->symbol()->icon()
			->standard($next_item->getType(), $next_item->getType(), 'medium');

		$curriculum = $this->curriculum_builder->getLearnerCurriculum(true)
			->withActive($item_position);

		$content = $this->renderComponentView($state, $view);
		$panel = $this->ui_factory->panel()->standard(
			'', //panel_title
			$content
		);
		$content = [$panel];

		return $this->page_renderer->render(
			$this->lso_title,
			$control_builder,
			$obj_title,
			$icon,
			$content,
			$curriculum
		);
	}

	protected function updateViewState(
		ILIAS\KioskMode\State $state,
		ILIAS\KioskMode\View $view,
		array $get,
		array $post=null
	): ILIAS\KioskMode\State {
		//get view internal command
		$command = $_GET[self::PARAM_LSO_COMMAND];
		$param = (int)$_GET[self::PARAM_LSO_PARAMETER];
		if(!is_null($command)) {
			$state = $view->updateGet($state, $command, $param);
		}
		return $state;
	}

	/**
	 * $direction is either -1 or 1;
	 */
	protected function getNextItem(LSLearnerItem $current_item, int $direction): LSLearnerItem
	{
		list($position, $item) = $this->findItemByRefId($current_item->getRefId());
		$next = $position + $direction;
		if($next >= 0 && $next < count($this->items)) {
			return $this->items[$next];
		}
		return $current_item;
	}

	/**
	 * @return array <int, LSLearnerItem> position=>item
	 */
	protected function findItemByRefId(int $ref_id): array
	{
		foreach ($this->items as $index=>$item) {
			if($item->getRefId() === $ref_id) {
				return [$index, $item];
			}
		}
		throw new \Exception("This is not a valid item.", 1);
	}

	protected function buildDefaultControls(
		LSControlBuilder $control_builder,
		LSLearnerItem $item,
		int $item_position
	): ControlBuilder {
		$is_first = $item_position === 0;
		$is_last = $item_position === count($this->items) - 1;

		if(! $control_builder->getExitControl()) {
			$cmd = self::LSO_CMD_SUSPEND;
			if ($is_last) {
				$cmd = self::LSO_CMD_FINISH;
			}
			$control_builder = $control_builder->exit($cmd);
		}

		if(! $control_builder->getPreviousControl()) {
			$direction_prev = -1;
			$cmd = ''; //disables control

			if (!$is_first) {
				$available = $this->getNextItem($item, $direction_prev)
					->getAvailability() === Step::AVAILABLE;

				if ($available) {
					$cmd = self::LSO_CMD_NEXT;
				}
			}

			$control_builder = $control_builder
				->previous($cmd, $direction_prev);
		}

		if(! $control_builder->getNextControl()) {
			$direction_next = 1;
			$cmd = '';
			if (!$is_last) {
				$available = $this->getNextItem($item, $direction_next)
					->getAvailability() === Step::AVAILABLE;

				if ($available) {
					$cmd = self::LSO_CMD_NEXT;
				}
			}

			$control_builder = $control_builder
				->next($cmd, $direction_next);
		}

		return $control_builder;
	}

	protected function renderComponentView(
		$state,
		ILIAS\KioskMode\View $view
	) {
		$component = $view->render(
			$state,
			$this->ui_factory,
			$this->url_builder,
			[]
		);
		return $component;
	}

}
