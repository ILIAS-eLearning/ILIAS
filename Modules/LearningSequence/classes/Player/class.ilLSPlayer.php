<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
use ILIAS\KioskMode\ControlBuilder;
use ILIAS\UI\Component\Listing\Workflow\Step;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use ILIAS\UI\Factory;
use ILIAS\Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Implementation of KioskMode Player
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

    protected ilLSLearnerItemsQueries $ls_items;
    protected LSControlBuilder $control_builder;
    protected LSUrlBuilder $url_builder;
    protected ilLSCurriculumBuilder $curriculum_builder;
    protected ilLSViewFactory $view_factory;
    protected ilKioskPageRenderer $page_renderer;
    protected Factory $ui_factory;
    protected ScreenContext $current_context;
    protected Refinery\Factory $refinery;

    public function __construct(
        ilLSLearnerItemsQueries $ls_items,
        LSControlBuilder $control_builder,
        LSUrlBuilder $url_builder,
        ilLSCurriculumBuilder $curriculum_builder,
        ilLSViewFactory $view_factory,
        ilKioskPageRenderer $renderer,
        Factory $ui_factory,
        ScreenContext $current_context,
        Refinery\Factory $refinery
    ) {
        $this->ls_items = $ls_items;
        $this->control_builder = $control_builder;
        $this->url_builder = $url_builder;
        $this->curriculum_builder = $curriculum_builder;
        $this->view_factory = $view_factory;
        $this->page_renderer = $renderer;
        $this->ui_factory = $ui_factory;
        $this->current_context = $current_context;
        $this->refinery = $refinery;
    }

    public function play(RequestWrapper $get) : ?string
    {
        //init state and current item
        $items = $this->ls_items->getItems();
        $current_item = $this->getCurrentItem($items);

        while ($current_item->getAvailability() !== Step::AVAILABLE) {
            $prev_item = $this->getNextItem($items, $current_item, -1);
            if ($prev_item === $current_item) {
                throw new \Exception("Cannot view first LSO-item", 1);
            }
            $current_item = $prev_item;
        }

        $view = $this->view_factory->getViewFor($current_item);
        $state = $this->ls_items->getStateFor($current_item, $view);
        $state = $this->updateViewState($state, $view, $get);
        //reload items after update viewState
        $items = $this->ls_items->getItems();

        $current_item_ref_id = $current_item->getRefId();
        //now, digest parameter:
        $command = null;
        if ($get->has(self::PARAM_LSO_COMMAND)) {
            $command = $get->retrieve(self::PARAM_LSO_COMMAND, $this->refinery->kindlyTo()->string());
        }
        $param = null;
        if ($get->has(self::PARAM_LSO_PARAMETER)) {
            $param = $get->retrieve(self::PARAM_LSO_PARAMETER, $this->refinery->kindlyTo()->int());
        }
        
        switch ($command) {
            case self::LSO_CMD_SUSPEND:
            case self::LSO_CMD_FINISH:
                $this->ls_items->storeState($state, $current_item_ref_id, $current_item_ref_id);
                return 'EXIT::' . $command;
            case self::LSO_CMD_NEXT:
                $next_item = $this->getNextItem($items, $current_item, $param);
                if ($next_item->getAvailability() !== Step::AVAILABLE) {
                    $next_item = $current_item;
                }
                break;
            case self::LSO_CMD_GOTO:
                list(, $next_item) = $this->findItemByRefId($items, $param);
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

        //get position
        list($item_position, $item) = $this->findItemByRefId($items, $next_item->getRefId());

        //have the view build controls
        $control_builder = $this->control_builder;
        $view->buildControls($state, $control_builder);

        //amend controls not set by the view
        $control_builder = $this->buildDefaultControls($control_builder, $item, $item_position, $items);

        //content
        $obj_title = $next_item->getTitle();
        $icon = $this->ui_factory->symbol()->icon()->standard(
            $next_item->getType(),
            $next_item->getType(),
            'medium'
        );

        $content = $this->renderComponentView($state, $view);

        $panel = $this->ui_factory->panel()->standard(
            '',
            $content
        );
        $content = [$panel];

        $rendered_body = $this->page_renderer->render(
            $control_builder,
            $obj_title,
            $icon,
            $content
        );

        $metabar_controls = [
            'exit' => $control_builder->getExitControl()
        ];

        $curriculum_slate = $this->page_renderer->buildCurriculumSlate(
            $this->curriculum_builder
                ->getLearnerCurriculum(true)
                ->withActive($item_position)
        );
        $mainbar_controls = [
            'curriculum' => $curriculum_slate
        ];

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

        return null;
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
                fn ($item) => $item->getRefId(),
                array_values($this->ls_items->getItems())
            );
            if (in_array($current_item_ref_id, $valid_ref_ids)) {
                list(, $current_item) = $this->findItemByRefId($items, $current_item_ref_id);
            }
        }
        return $current_item;
    }

    protected function updateViewState(
        ILIAS\KioskMode\State $state,
        ILIAS\KioskMode\View $view,
        RequestWrapper $get
    ) : ILIAS\KioskMode\State {
        if ($get->has(self::PARAM_LSO_COMMAND) && $get->has(self::PARAM_LSO_PARAMETER)) {
            $command = $get->retrieve(self::PARAM_LSO_COMMAND, $this->refinery->kindlyTo()->string());
            $param = $get->retrieve(self::PARAM_LSO_PARAMETER, $this->refinery->kindlyTo()->int());
            $state = $view->updateGet($state, $command, $param);
        }
        return $state;
    }

    /**
     * $direction is either -1 or 1;
     */
    protected function getNextItem(array $items, LSLearnerItem $current_item, int $direction) : LSLearnerItem
    {
        list($position) = $this->findItemByRefId($items, $current_item->getRefId());
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

    protected function renderComponentView($state, ILIAS\KioskMode\View $view) : Component
    {
        return $view->render(
            $state,
            $this->ui_factory,
            $this->url_builder,
            []
        );
    }


    public function getCurrentItemLearningProgress() : int
    {
        $item = $this->getCurrentItem($this->ls_items->getItems());
        return $item->getLearningProgressStatus();
    }
}
