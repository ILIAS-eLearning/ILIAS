<?php declare(strict_types=1);

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\UI\Component\Listing\Workflow\Step;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

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

    const GS_DATA_LS_KIOSK_MODE = 'ls_kiosk_mode';
    const GS_DATA_LS_CONTENT = 'ls_content';
    const GS_DATA_LS_MAINBARCONTROLS = 'ls_mainbar_controls';
    const GS_DATA_LS_METABARCONTROLS = 'ls_metabar_controls';

    const RET_EXIT = 'EXIT::';
    const RET_NOITEMS = 'NOITEMS';

    public function __construct(
        string $lso_title,
        ilLSLearnerItemsQueries $ls_items,
        LSControlBuilder $control_builder,
        LSUrlBuilder $url_builder,
        ilLSCurriculumBuilder $curriculum_builder,
        ilLSViewFactory $view_factory,
        ilKioskPageRenderer $renderer,
        ILIAS\UI\Factory $ui_factory,
        ScreenContext $current_context
    ) {
        $this->lso_title = $lso_title;
        $this->ls_items = $ls_items;
        $this->control_builder = $control_builder;
        $this->url_builder = $url_builder;
        $this->curriculum_builder = $curriculum_builder;
        $this->view_factory = $view_factory;
        $this->page_renderer = $renderer;
        $this->ui_factory = $ui_factory;
        $this->current_context = $current_context;
    }

    public function play(array $get, array $post = null)
    {
        //init state and current item
        $items = $this->ls_items->getItems();
        if (count($items) === 0) {
            return self::RET_NOITEMS;
        }
        $current_item = $this->getCurrentItem($items);

        while ($current_item->getAvailability() !== \ILIAS\UI\Component\Listing\Workflow\Step::AVAILABLE) {
            $prev_item = $this->getNextItem($items, $current_item, -1);
            if ($prev_item === $current_item) {
                throw new \Exception("Cannot view first LSO-item", 1);
            }
            $current_item = $prev_item;
        }

        $view = $this->view_factory->getViewFor($current_item);
        $state = $this->ls_items->getStateFor($current_item, $view);
        $state = $this->updateViewState($state, $view, $get, $post);
        $items = $this->ls_items->getItems(); //reload items after update viewState

        $current_item_ref_id = $current_item->getRefId();
        //now, digest parameter:
        $command = $_GET[self::PARAM_LSO_COMMAND];
        $param = (int) $_GET[self::PARAM_LSO_PARAMETER];

        switch ($command) {
            case self::LSO_CMD_SUSPEND:
            case self::LSO_CMD_FINISH:
                //store state and exit
                $this->ls_items->storeState($state, $current_item_ref_id, $current_item_ref_id);
                return 'EXIT::' . $command;
            case self::LSO_CMD_NEXT:
                $next_item = $this->getNextItem($items, $current_item, $param);
                if ($next_item->getAvailability() !== \ILIAS\UI\Component\Listing\Workflow\Step::AVAILABLE) {
                    $next_item = $current_item;
                }
                break;
            case self::LSO_CMD_GOTO:
                list($position, $next_item) = $this->findItemByRefId($items, $param);
                break;
            default: //view-internal / unknown command
                $next_item = $current_item;
        }
        //write State to DB
        $this->ls_items->storeState($state, $current_item_ref_id, $next_item->getRefId());

        //get proper view
        if ($next_item !== $current_item) {
            $view = $this->view_factory->getViewFor($next_item);
            $state = $this->ls_items->getStateFor($next_item, $view);
        }

        //content
        $obj_title = $next_item->getTitle();
        $icon = $this->ui_factory->symbol()->icon()
            ->standard($next_item->getType(), $next_item->getType(), 'medium');

        $content = $this->renderComponentView($state, $view);

        $panel = $this->ui_factory->panel()->standard(
            '', //panel_title
            $content
        );
        $content = [$panel];

        $items = $this->ls_items->getItems(); //reload items after renderComponentView content

        //get position
        list($item_position, $item) = $this->findItemByRefId($items, $next_item->getRefId());

        //have the view build controls
        $control_builder = $this->control_builder;
        $view->buildControls($state, $control_builder);

        //amend controls not set by the view
        $control_builder = $this->buildDefaultControls($control_builder, $item, $item_position, $items);

        $rendered_body = $this->page_renderer->render(
            $this->lso_title,
            $control_builder,
            $obj_title,
            $icon,
            $content
        );

        $metabar_controls = [
            'exit' => $control_builder->getExitControl()
        ];

        //curriculum
        $curriculum_slate = $this->page_renderer->buildCurriculumSlate(
            $this->curriculum_builder
                ->getLearnerCurriculum(true)
                ->withActive($item_position)
        );
        $mainbar_controls = [
            'curriculum' => $curriculum_slate
        ];

        //ToC
        $toc = $control_builder->getToc();
        if ($toc) {
            $toc_slate = $this->page_renderer->buildToCSlate($toc, $icon);
            $mainbar_controls['toc'] = $toc_slate;
        }

        $cc = $this->current_context;
        $cc->addAdditionalData(self::GS_DATA_LS_KIOSK_MODE, true);
        $cc->addAdditionalData(self::GS_DATA_LS_METABARCONTROLS, $metabar_controls);
        $cc->addAdditionalData(self::GS_DATA_LS_MAINBARCONTROLS, $mainbar_controls);
        $cc->addAdditionalData(self::GS_DATA_LS_CONTENT, $rendered_body);
        return;
    }

    /**
     * @param array LSLearnerItem[]
     */
    protected function getCurrentItem(array $items) : LSLearnerItem
    {
        $current_item = $items[0];
        $current_item_ref_id = $this->ls_items->getCurrentItemRefId();
        if ($current_item_ref_id !== 0) {
            $valid_ref_ids = array_map(
                function ($item) {
                    return $item->getRefId();
                },
                array_values($this->ls_items->getItems())
            );
            if (in_array($current_item_ref_id, $valid_ref_ids)) {
                list($position, $current_item) = $this->findItemByRefId($items, $current_item_ref_id);
            }
        }
        return $current_item;
    }

    protected function updateViewState(
        ILIAS\KioskMode\State $state,
        ILIAS\KioskMode\View $view,
        array $get,
        array $post = null
    ) : ILIAS\KioskMode\State {
        //get view internal command
        $command = $_GET[self::PARAM_LSO_COMMAND];
        $param = (int) $_GET[self::PARAM_LSO_PARAMETER];
        if (!is_null($command)) {
            $state = $view->updateGet($state, $command, $param);
        }
        return $state;
    }

    /**
     * $direction is either -1 or 1;
     */
    protected function getNextItem(array $items, LSLearnerItem $current_item, int $direction) : LSLearnerItem
    {
        list($position, $item) = $this->findItemByRefId($items, $current_item->getRefId());
        $next = $position + $direction;
        if ($next >= 0 && $next < count($items)) {
            return $items[$next];
        }
        return $current_item;
    }

    /**
     * @return array <int, LSLearnerItem> position=>item
     */
    protected function findItemByRefId(array $items, int $ref_id) : array
    {
        foreach ($items as $index => $item) {
            if ($item->getRefId() === $ref_id) {
                return [$index, $item];
            }
        }
        throw new \Exception("This is not a valid item.", 1);
    }

    protected function buildDefaultControls(
        LSControlBuilder $control_builder,
        LSLearnerItem $item,
        int $item_position,
        array $items
    ) : ControlBuilder {
        $is_first = $item_position === 0;
        $is_last = $item_position === count($items) - 1;

        if (!$control_builder->getExitControl()) {
            $cmd = self::LSO_CMD_SUSPEND;
            if ($is_last) {
                $cmd = self::LSO_CMD_FINISH;
            }
            $control_builder = $control_builder->exit($cmd);
        }

        if (!$control_builder->getPreviousControl()) {
            $direction_prev = -1;
            $cmd = ''; //disables control

            if (!$is_first) {
                $available = $this->getNextItem($items, $item, $direction_prev)
                    ->getAvailability() === Step::AVAILABLE;

                if ($available) {
                    $cmd = self::LSO_CMD_NEXT;
                }
            }

            $control_builder = $control_builder
                ->previous($cmd, $direction_prev);
        }

        if (!$control_builder->getNextControl()) {
            $direction_next = 1;
            $cmd = '';
            if (!$is_last) {
                $available = $this->getNextItem($items, $item, $direction_next)
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


    public function getCurrentItemLearningProgress()
    {
        $item = $this->getCurrentItem($this->ls_items->getItems());
        return $item->getLearningProgressStatus();
    }
}
