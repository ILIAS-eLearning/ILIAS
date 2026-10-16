<?php

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

declare(strict_types=1);

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\UI\Component\Listing\Workflow\Step;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use ILIAS\UI\Factory;
use ILIAS\Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\LearningSequence\Player\LSNavigator;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapPosition;
use ILIAS\LearningSequence\Player\LSChoicePageBuilder;
use ILIAS\LearningSequence\Content\Adaptive\LSOItemPath;
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;

/**
 * Implementation of KioskMode Player
 */
class ilLSPlayer
{
    public const PARAM_LSO_COMMAND = 'lsocmd';
    public const PARAM_LSO_PARAMETER = 'lsov';
    private const string PLAYER_CSS = 'assets/css/lso_player.css';

    public const LSO_CMD_NEXT = 'lsonext'; //with param directions
    public const LSO_CMD_GOTO = 'lsogoto'; //with param ref_id
    public const LSO_CMD_JUMP = 'lsojump'; //jump right to an object, with param obj_id (adaptive) resp. ref_id
    public const LSO_CMD_SUSPEND = 'lsosuspend';
    public const LSO_CMD_FINISH = 'lsofinish';
    public const LSO_CMD_CHOICE = 'lsochoice'; //show the branch/dead-end interstitial page
    public const LSO_CMD_STAY = 'lsostay'; //return from interstitial page back to the object
    public const REDIRECT_PREFIX = 'REDIRECT::';

    public const GS_DATA_LS_KIOSK_MODE = 'ls_kiosk_mode';
    public const GS_DATA_LS_TITLE = 'ls_title';
    public const GS_DATA_LS_CONTENT = 'ls_content';
    public const GS_DATA_LS_MAINBARCONTROLS = 'ls_mainbar_controls';
    public const GS_DATA_LS_METABARCONTROLS = 'ls_metabar_controls';

    public function __construct(
        protected ilLSLearnerItemsQueries $ls_items,
        protected LSControlBuilder $control_builder,
        protected LSUrlBuilder $url_builder,
        protected ilLSViewFactory $view_factory,
        protected ilKioskPageRenderer $page_renderer,
        protected Factory $ui_factory,
        protected ScreenContext $current_context,
        protected Refinery\Factory $refinery,
        protected ilLanguage $lng,
        protected ilCtrlInterface $ctrl,
        protected ?LSNavigator $navigator = null,
        protected int $mode = ilLearningSequenceSettings::MODE_LINEAR,
        protected ?LSOItemPath $item_path = null,
        protected ?LSOAdaptiveBoundaries $boundaries = null,
        protected int $lso_obj_id = 0,
        protected int $usr_id = 0,
        protected ?LSOLearningMapPosition $position = null,
        protected ?LSChoicePageBuilder $choice_page_builder = null,
        protected ?Closure $learning_map = null
    ) {
    }

    public function play(RequestWrapper $get): ?string
    {
        $ls_ref_id = $this->ls_items->getLearningSequenceRefId();
        $ls_obj_id = ilObject::_lookupObjId($ls_ref_id);
        $ls_title = ilObject::_lookupTitle($ls_obj_id);

        $items = $this->ls_items->getItems();

        if (count($items) === 0) {
            return null;
        }

        if ($this->isAdaptive()) {
            $this->position->prepareForItems($items);
            $this->navigator->preload($items);
            $current_item = $this->getAdaptiveCurrentItem($items);
        } else {
            $current_item = $this->getNextAvailableItem($items, $this->getCurrentItem($items));
        }
        if ($current_item === null) {
            return null;
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

        // When set, the branch selection / dead-end notice is rendered on its
        // own interstitial page instead of being appended below the object.
        $show_choice = ($command === self::LSO_CMD_CHOICE) && $this->isAdaptive();

        if (!$this->isAdaptive() && $this->position !== null) {
            $this->position->recordVisit($current_item->getRefId());
        }

        switch ($command) {
            case self::LSO_CMD_SUSPEND:
            case self::LSO_CMD_FINISH:
                $this->ls_items->storeState($state, $current_item_ref_id, $current_item_ref_id);
                return 'EXIT::' . $command;
            case self::LSO_CMD_NEXT:
                if ($this->isAdaptive()) {
                    $next_item = $this->getAdaptiveNextItem($items, $current_item, $param);
                    break;
                }
                $next_item = $this->getNextItem($items, $current_item, $param);
                if (!$this->isItemAvailableForSequenceNavigation($items, $current_item, $next_item, $param)) {
                    $next_item = $current_item;
                }
                break;
            case self::LSO_CMD_GOTO:
                if ($this->isAdaptive()) {
                    $next_item = $this->gotoAdaptive($items, $current_item, $param);
                    break;
                }
                list(, $next_item) = $this->findItemByRefId($items, $param);
                break;
            case self::LSO_CMD_JUMP:
                // Coming from the learning map: the learner picked one specific
                // object, which is not necessarily a successor of the object
                // worked on last. Without this the player would silently stay
                // where it was.
                if ($this->isAdaptive()) {
                    $next_item = $this->jumpAdaptive($items, $current_item, $param);
                    break;
                }
                list(, $next_item) = $this->findItemByRefId($items, $param);
                break;
            case self::LSO_CMD_CHOICE: //stay on the object but show the interstitial page
            case self::LSO_CMD_STAY:   //return from the interstitial back to the object
                $next_item = $current_item;
                break;
            default: //view-internal / unknown command
                $next_item = $current_item;
        }
        //write State to DB
        $this->ls_items->storeState($state, $current_item_ref_id, $next_item->getRefId());

        // After a navigation step the player continues on a url without the
        // navigation command, so reloading the page does not step on again.
        // A step back onto a branch/dead-end object continues on its
        // interstitial page, as it did before the redirect.
        if (in_array($command, [self::LSO_CMD_NEXT, self::LSO_CMD_GOTO, self::LSO_CMD_JUMP], true)
            && $next_item->getRefId() !== $current_item_ref_id
        ) {
            $continue_with = self::LSO_CMD_STAY;
            $is_back_step = $command === self::LSO_CMD_NEXT && $param !== null && $param < 0;
            if ($this->isAdaptive() && $is_back_step) {
                $reloaded_items = $this->ls_items->getItems();
                list(, $target_item) = $this->findItemByRefId($reloaded_items, $next_item->getRefId());
                if ($this->landsOnInterstitialPage($reloaded_items, $target_item)) {
                    $continue_with = self::LSO_CMD_CHOICE;
                }
            }
            return self::REDIRECT_PREFIX . $this->url_builder->getHref($continue_with);
        }

        //get proper view
        if ($next_item != $current_item) {
            $view = $this->view_factory->getViewFor($next_item);
            $state = $this->ls_items->getStateFor($next_item, $view);
        }

        if (!$this->isAdaptive() && $this->position !== null) {
            $this->position->recordVisit($next_item->getRefId());
        }

        //content
        $obj_title = $next_item->getTitle();
        $obj_description = $next_item->getDescription();
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

        $items = $this->ls_items->getItems(); //reload items after renderComponentView content

        //get position
        list($item_position, $item) = $this->findItemByRefId($items, $next_item->getRefId());

        // When resuming the player (no command, e.g. after leaving and
        // re-entering) onto a branch/dead-end object, land directly on its
        // interstitial page (choice / dead-end notice) instead of the object
        // content. Steps back are redirected to that page above.
        if ($this->isAdaptive() && $command === null) {
            if ($this->landsOnInterstitialPage($items, $item)) {
                $show_choice = true;
            }
        }

        if ($this->isAdaptive() && !$show_choice) {
            $content = $this->amendAdaptiveContent($content, $items, $item);
        }
        if ($this->isAdaptive() && $show_choice) {
            $this->page_renderer->addCss(self::PLAYER_CSS);
            $situation = $this->getAdaptiveSituation($items, $item);
            if ($situation === 'branch') {
                $obj_title = $this->choice_page_builder->getHeadline();
                $obj_description = '';
                $content = $this->buildAdaptiveChoiceContent($items, $item);
            } elseif ($situation === 'deadend') {
                $obj_title = $this->txt('lso_player_dead_end_title');
                $obj_description = '';
                $content = $this->buildAdaptiveDeadEndContent();
            } else {
                $show_choice = false;
                $content = $this->amendAdaptiveContent($content, $items, $item);
            }
        }

        $control_builder = $this->control_builder;
        // On the interstitial pages (branch selection / dead-end notice) the
        // object itself is not shown, so its view-controls (e.g. the
        // "mark as completed"-button of a page) must not be built either.
        if (!($this->isAdaptive() && $show_choice)) {
            $view->buildControls($state, $control_builder);
        }

        if ($this->isAdaptive() && $show_choice) {
            $control_builder = $this->buildAdaptiveChoiceControls($control_builder);
        } elseif ($this->isAdaptive()) {
            $control_builder = $this->buildAdaptiveControls($control_builder, $item, $items);
        } else {
            $control_builder = $this->buildDefaultControls($control_builder, $item, $item_position, $items);
        }

        $mainbar_controls = [];

        // The learning map lives in the main bar and opens in a modal; the
        // modal itself has to be part of the page, so it is appended to the
        // content before the body is rendered.
        $map_html = $this->getLearningMapHtml($ls_ref_id);
        if ($map_html !== '') {
            list($map_button, $map_modal) = $this->page_renderer->buildLearningMapEntry($map_html);
            $mainbar_controls['learning_map'] = $map_button;
            $content[] = $map_modal;
        }

        // Keep a stable header layout: reserve the rating slot even if rating is not available.
        // Otherwise the navigation/buttons would shift vertically.
        $obj_rating_html = '&nbsp;';
        $obj_id = ilObject::_lookupObjId($next_item->getRefId());
        if ($obj_id > 0 && !$show_choice) {
            $obj_type = ilObject::_lookupType($obj_id);
            ilRating::preloadListGUIData([$obj_id]);
            if ($obj_type !== '' && ilRating::hasRatingInListGUI($obj_id, $obj_type)) {
                $parent_ref_id = (int) $ls_ref_id;
                $rating_container_id = 'lg_div_' . $next_item->getRefId() . '_pref_' . $parent_ref_id;

                $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(
                    ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
                    $next_item->getRefId(),
                    $obj_type,
                    $obj_id
                );

                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject($obj_id, $obj_type);
                $rating_gui->setCtrlPath([
                    ilCommonActionDispatcherGUI::class,
                    ilRatingGUI::class
                ]);
                $rating_gui->setYourRatingText($this->lng->txt('rating_your_rating'));

                global $DIC;

                $rating_content = $rating_gui->getListGUIProperty(
                    $next_item->getRefId(),
                    $DIC->access()->checkAccess('read', '', $next_item->getRefId()),
                    $ajax_hash,
                    $parent_ref_id
                );

                $tpl = new ilTemplate(
                    'tpl.lso_kiosk_rating_container.html',
                    true,
                    true,
                    'components/ILIAS/LearningSequence'
                );
                $tpl->setVariable('CONTAINER_ID', $rating_container_id);
                $tpl->setVariable('CHILD_REF_ID', (string) $next_item->getRefId());
                $tpl->setVariable('AJAX_HASH', htmlspecialchars($ajax_hash, ENT_QUOTES));
                $tpl->setVariable('RATING_CONTENT', $rating_content);
                $obj_rating_html = $tpl->get();

                $redraw_url = $this->ctrl->getLinkTargetByClass(
                    ilObjLearningSequenceLearnerGUI::class,
                    ilObjLearningSequenceLearnerGUI::CMD_REDRAW_LIST_ITEM,
                    '',
                    true,
                    false
                );

                $rating_url = $this->ctrl->getLinkTargetByClass(
                    [
                        ilCommonActionDispatcherGUI::class,
                        ilRatingGUI::class
                    ],
                    'saveRating',
                    '',
                    true,
                    false
                );

                $this->page_renderer->addOnLoadCode(
                    "if (window.il && window.il.Object) {" .
                    " if (typeof window.il.Object.setRedrawListItemUrl === 'function') { window.il.Object.setRedrawListItemUrl(" . json_encode($redraw_url, JSON_THROW_ON_ERROR) . "); }" .
                    " if (typeof window.il.Object.setRatingUrl === 'function') { window.il.Object.setRatingUrl(" . json_encode($rating_url, JSON_THROW_ON_ERROR) . "); }" .
                    "}"
                );
            }
        }

        $rendered_body = $this->page_renderer->render(
            $control_builder,
            $obj_title,
            $obj_description,
            $icon,
            $content,
            $obj_rating_html
        );

        $metabar_controls = [
            'exit' => $control_builder->getExitControl()
        ];

        $toc = $control_builder->getToc();
        if ($toc) {
            $toc_slate = $this->page_renderer->buildToCSlate($toc, $icon);
            $mainbar_controls['toc'] = $toc_slate;
        }

        $cc = $this->current_context;
        $cc->addAdditionalData(self::GS_DATA_LS_KIOSK_MODE, true);
        $cc->addAdditionalData(self::GS_DATA_LS_TITLE, $ls_title);
        $cc->addAdditionalData(self::GS_DATA_LS_METABARCONTROLS, $metabar_controls);
        $cc->addAdditionalData(self::GS_DATA_LS_MAINBARCONTROLS, $mainbar_controls);
        $cc->addAdditionalData(self::GS_DATA_LS_CONTENT, $rendered_body);

        return null;
    }

    /**
     * The learning map of the current user, without the panel around it - it
     * is shown inside the modal of the main bar entry. Which map is built
     * (adaptive graph or sequential chain) is decided by the operation mode.
     */
    protected function getLearningMapHtml(int $ls_ref_id): string
    {
        if ($this->learning_map === null) {
            return '';
        }

        $this->page_renderer->addCss(\ILIAS\LearningSequence\LearningMap\LSOLearningMapRenderer::CSS);
        $this->page_renderer->addJs(\ILIAS\LearningSequence\LearningMap\LSOLearningMapRenderer::JS, true);

        return ($this->learning_map)();
    }


    /**
     * @param array LSLearnerItem[]
     */
    protected function getCurrentItem(array $items): LSLearnerItem
    {
        $current_item = $items[0];
        $current_item_ref_id = $this->ls_items->getCurrentItemRefId();
        if ($current_item_ref_id !== 0) {
            $valid_ref_ids = array_map(
                fn($item) => $item->getRefId(),
                array_values($this->ls_items->getItems())
            );
            if (in_array($current_item_ref_id, $valid_ref_ids)) {
                list(, $current_item) = $this->findItemByRefId($items, $current_item_ref_id);
            }
        }
        return $current_item;
    }

    protected function getNextAvailableItem(
        array $items,
        LSLearnerItem $current_item
    ): ?LSLearnerItem {
        if ($current_item->getAvailability() === Step::AVAILABLE) {
            return $current_item;
        }
        $position = $this->getPosition();
        if ($position !== null) {
            $current_obj_id = ilObject::_lookupObjId($current_item->getRefId());
            if ($position->hasVisited($current_obj_id)) {
                return $current_item;
            }
        }

        $new_next_item = null;
        $idx = array_search($current_item, $items);

        for ($i = $idx - 1; $i >= 0; $i--) {
            if ($items[$i]->getAvailability() === Step::AVAILABLE) {
                $new_next_item = $items[$i];
                continue;
            }
        }
        if ($new_next_item === null) {
            for ($i = $idx + 1; $i < count($items); $i++) {
                if ($items[$i]->getAvailability() === Step::AVAILABLE) {
                    $new_next_item = $items[$i];
                    continue;
                }
            }
        }
        return $new_next_item;
    }

    protected function updateViewState(
        ILIAS\KioskMode\State $state,
        ILIAS\KioskMode\View $view,
        RequestWrapper $get
    ): ILIAS\KioskMode\State {
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
    protected function getNextItem(array $items, LSLearnerItem $current_item, int $direction): LSLearnerItem
    {
        list($position) = $this->findItemByRefId($items, $current_item->getRefId());
        $next = $position + $direction;
        if ($next >= 0 && $next < count($items)) {
            return $items[$next];
        }
        return $current_item;
    }

    /**
     * @param LSLearnerItem[] $items
     */
    protected function isItemAvailableForSequenceNavigation(
        array $items,
        LSLearnerItem $current_item,
        LSLearnerItem $target_item,
        int $direction
    ): bool {
        if ($target_item->getAvailability() === Step::AVAILABLE) {
            return true;
        }
        // Only used in the linear mode: the post condition of the current
        // item alone decides whether the learner may proceed.
        if ($direction > 0) {
            return false;
        }
        $position = $this->getPosition();
        if ($position === null) {
            return false;
        }
        if ($target_item->getRefId() === $current_item->getRefId()) {
            return true;
        }
        return $position->hasVisited(ilObject::_lookupObjId($target_item->getRefId()));
    }

    protected function getPosition(): ?LSOLearningMapPosition
    {
        return $this->position ?? null;
    }

    /**
     * @return array <int, LSLearnerItem> position=>item
     */
    protected function findItemByRefId(array $items, int $ref_id): array
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
    ): LSControlBuilder {
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
                $available = $this->isItemAvailableForSequenceNavigation(
                    $items,
                    $item,
                    $this->getNextItem($items, $item, $direction_prev),
                    $direction_prev
                );

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
            $param = $direction_next;

            if ($is_last) {
                $cmd = self::LSO_CMD_FINISH;
                $param = null;
            } else {
                $available = $this->isItemAvailableForSequenceNavigation(
                    $items,
                    $item,
                    $this->getNextItem($items, $item, $direction_next),
                    $direction_next
                );

                if ($available) {
                    $cmd = self::LSO_CMD_NEXT;
                }
            }

            $control_builder = $control_builder
                ->next($cmd, $param);
        }

        return $control_builder;
    }

    protected function renderComponentView($state, ILIAS\KioskMode\View $view): Component
    {
        return $view->render(
            $state,
            $this->ui_factory,
            $this->url_builder,
            []
        );
    }

    /**
     * Whether the player runs in adaptive mode with the required collaborators
     * (navigator, path repository and boundaries) available.
     */
    protected function isAdaptive(): bool
    {
        return $this->mode === ilLearningSequenceSettings::MODE_ADAPTIVE
            && $this->navigator instanceof LSNavigator
            && $this->item_path !== null
            && $this->boundaries !== null
            && $this->position !== null;
    }

    protected function getAdaptiveStartObjId(): int
    {
        return $this->position->getStartObjId();
    }

    protected function getAdaptiveEndObjId(): int
    {
        return $this->position->getEndObjId();
    }

    /**
     * Determines the current object in adaptive mode from the walked path.
     *
     * @param LSLearnerItem[] $items
     */
    protected function getAdaptiveCurrentItem(array $items): ?LSLearnerItem
    {
        return $this->position->getCurrentItem($items);
    }

    /**
     * Handles "next"/"back" in adaptive mode.
     *
     * @param LSLearnerItem[] $items
     */
    protected function getAdaptiveNextItem(array $items, LSLearnerItem $current_item, ?int $direction): LSLearnerItem
    {
        return $this->position->advance($items, $current_item, $direction);
    }

    /**
     * Jumps right to the chosen object in adaptive mode (learning map).
     *
     * @param LSLearnerItem[] $items
     */
    protected function jumpAdaptive(array $items, LSLearnerItem $current_item, ?int $param): LSLearnerItem
    {
        return $this->position->jumpTo($items, $current_item, $param);
    }

    /**
     * @param LSLearnerItem[] $items
     */
    protected function gotoAdaptive(array $items, LSLearnerItem $current_item, ?int $param): LSLearnerItem
    {
        return $this->position->goTo($items, $current_item, $param);
    }

    /**
     * Classifies the situation of the given object in adaptive mode:
     * 'end', 'blocked', 'deadend', 'branch' or 'straight'.
     *
     * @param LSLearnerItem[] $items
     */
    protected function getAdaptiveSituation(array $items, LSLearnerItem $item): string
    {
        return $this->position->getSituation($items, $item);
    }

    /**
     * Whether arriving on the item (by a step back or by resuming) shows its
     * interstitial page instead of the object: on a branch, or on a dead end
     * that is one structurally as well.
     *
     * @param LSLearnerItem[] $items
     */
    protected function landsOnInterstitialPage(array $items, LSLearnerItem $item): bool
    {
        $situation = $this->getAdaptiveSituation($items, $item);
        return $situation === 'branch'
            || ($situation === 'deadend'
                && $this->position->getStructuralSituation($items, $item) === 'deadend');
    }

    /**
     * Amends the rendered content with adaptive hints or a choice panel.
     *
     * @param Component[] $content
     * @param LSLearnerItem[] $items
     * @return Component[]
     */
    protected function amendAdaptiveContent(array $content, array $items, LSLearnerItem $item): array
    {
        switch ($this->getAdaptiveSituation($items, $item)) {
            case 'blocked':
                $content[] = $this->ui_factory->messageBox()->info(
                    $this->txt('lso_player_next_object_blocked')
                );
                break;
            default:
                // Branch selection and dead-end notice are no longer appended
                // here; they are rendered on their own interstitial page
                // (see LSO_CMD_CHOICE handling in play()).
                break;
        }
        return $content;
    }

    /**
     * Builds the branch-selection content (one card per allowed successor)
     * using the reusable LSChoicePageBuilder template.
     *
     * @param LSLearnerItem[] $items
     * @return Component[]
     */
    protected function buildAdaptiveChoiceContent(array $items, LSLearnerItem $item): array
    {
        $successors = $this->position->getSuccessors($items, $item);
        return $this->choice_page_builder->build($successors);
    }

    /**
     * @return Component[]
     */
    protected function buildAdaptiveDeadEndContent(): array
    {
        return [
            $this->ui_factory->legacy()->content(
                '<div class="lso-player-dead-end">'
                . '<p class="lso-player-dead-end__message">'
                . htmlspecialchars($this->txt('lso_player_dead_end_line_1')) . '<br>'
                . htmlspecialchars($this->txt('lso_player_dead_end_line_2')) . '<br>'
                . htmlspecialchars($this->txt('lso_player_dead_end_line_3'))
                . '</p>'
                . '<div class="lso-player-dead-end__illustration" aria-hidden="true">'
                . $this->getAdaptiveDeadEndSvg()
                . '</div>'
                . '</div>'
            )
        ];
    }

    /**
     * Builds the navigation controls for adaptive mode based on the situation
     * and the length of the walked path.
     *
     * @param LSLearnerItem[] $items
     */
    protected function buildAdaptiveControls(
        LSControlBuilder $control_builder,
        LSLearnerItem $item,
        array $items
    ): ControlBuilder {
        $situation = $this->getAdaptiveSituation($items, $item);
        $path_length = $this->position->getPathLength();

        if (!$control_builder->getExitControl()) {
            $cmd = ($situation === 'end') ? self::LSO_CMD_FINISH : self::LSO_CMD_SUSPEND;
            $control_builder = $control_builder->exit($cmd);
        }

        if (!$control_builder->getPreviousControl()) {
            $cmd = ($path_length > 1) ? self::LSO_CMD_NEXT : '';
            $control_builder = $control_builder->previous($cmd, -1);
        }

        if (!$control_builder->getNextControl()) {
            $cmd = '';
            $param = 1;
            if ($situation === 'end') {
                $cmd = self::LSO_CMD_FINISH;
                $param = null;
            } elseif ($situation === 'straight') {
                $cmd = self::LSO_CMD_NEXT;
            } elseif ($situation === 'branch' || $situation === 'deadend') {
                // "Weiter" leads to the dedicated interstitial page (choice or
                // dead-end notice) instead of advancing directly.
                $cmd = self::LSO_CMD_CHOICE;
                $param = null;
            }
            $control_builder = $control_builder->next($cmd, $param);
        }

        return $control_builder;
    }

    /**
     * Builds the navigation controls for the adaptive interstitial page (branch
     * selection / dead-end notice): only a way back to the object is offered;
     * advancing happens via the choice buttons in the content.
     */
    protected function buildAdaptiveChoiceControls(
        LSControlBuilder $control_builder
    ): ControlBuilder {
        if (!$control_builder->getExitControl()) {
            $control_builder = $control_builder->exit(self::LSO_CMD_SUSPEND);
        }
        if (!$control_builder->getPreviousControl()) {
            $control_builder = $control_builder->previous(self::LSO_CMD_STAY, null);
        }
        // No "next" control on the interstitial page: advancing happens via the
        // choice cards; on a dead end there is nothing to advance to at all.
        return $control_builder;
    }

    public function getCurrentItemLearningProgress(): int
    {
        $item = $this->getCurrentItem($this->ls_items->getItems());
        return $item->getLearningProgressStatus();
    }

    protected function txt(string $key): string
    {
        global $DIC;
        return $DIC->language()->txt($key);
    }

    protected function getAdaptiveDeadEndSvg(): string
    {
        $svg = file_get_contents(dirname(__DIR__, 2) . '/resources/images/player/dead_end.svg');
        if ($svg === false) {
            throw new \RuntimeException('Unable to load dead-end SVG asset.');
        }

        return $svg;
    }
}
