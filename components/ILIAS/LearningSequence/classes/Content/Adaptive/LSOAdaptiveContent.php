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

namespace ILIAS\LearningSequence\Content\Adaptive;

use ilCtrl;
use ilDBInterface;
use ilGlobalTemplateInterface;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\ConditionHandler;
use ILIAS\LearningSequence\Content\Condition\ConditionTypes;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationAnalyzer;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\LSOContentController;
use ILIAS\LearningSequence\Content\LSOContentDeletion;
use ILIAS\LearningSequence\Player\AdaptiveNavigator;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilLink;
use ilObject;
use ilObjLearningSequence;
use ilObjLearningSequenceActionData;
use ilObjLearningSequenceConditionData;
use ilObjLearningSequenceConditionsGUI;
use ilObjLearningSequenceContentData;
use ilObjLearningSequenceContentGUI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controls adaptive learning sequence content management.
 */
class LSOAdaptiveContent implements LSOContentController
{
    use LSOContentDeletion;

    /** Parent content GUI. */
    protected ilObjLearningSequenceContentGUI $parent_gui;
    /** UI factory. */
    protected Factory $ui_factory;
    /** UI renderer. */
    protected Renderer $ui_renderer;
    /** Language service. */
    protected ilLanguage $lng;
    /** Controller service. */
    protected ilCtrl $ctrl;
    /** Server request. */
    protected ServerRequestInterface $request;
    /** Global template. */
    protected ilGlobalTemplateInterface $tpl;
    /** Database connection. */
    protected ilDBInterface $db;
    /** Learning sequence repository reference ID. */
    protected int $ref_id;
    /** Learning sequence object ID. */
    protected int $obj_id;

    /**
     * Creates the adaptive content controller.
     */
    public function __construct(
        ilObjLearningSequenceContentGUI $parent_gui,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ServerRequestInterface $request,
        ilGlobalTemplateInterface $tpl,
        int $ref_id,
        int $obj_id
    ) {
        global $DIC;
        $this->parent_gui = $parent_gui;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->request = $request;
        $this->tpl = $tpl;
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->db = $DIC->database();
    }

    /**
     * Gets the commands supported by the controller.
     *
     * @return string[]
     */
    public function getSupportedCommands(): array
    {
        return [
            ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT,
            ilObjLearningSequenceContentGUI::CMD_SET_START_OBJECT,
            ilObjLearningSequenceContentGUI::CMD_UNSET_START_OBJECT,
            ilObjLearningSequenceContentGUI::CMD_SET_END_OBJECT,
            ilObjLearningSequenceContentGUI::CMD_UNSET_END_OBJECT,
            ilObjLearningSequenceContentGUI::CMD_DELETE,
        ];
    }

    /**
     * Renders the adaptive content management view.
     */
    public function manageContent(): void
    {
        // Restore the "add new object" drilldown in the toolbar so that new
        // objects can be created directly from the content management view.
        $this->parent_gui->showPossibleSubObjects();

        /** @var ilObjLearningSequence $lso */
        $lso = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
        $items = $lso->getLSItems();
        $navigator = new AdaptiveNavigator();
        $navigator->preload($items);
        $structural_successors = [];
        foreach ($items as $item) {
            $structural_successors[$item->getRefId()] = array_map(
                static fn(\LSItem $successor): int => $successor->getRefId(),
                $navigator->getStructuralSuccessors($items, $item)
            );
        }
        $filter_gui = new LSOAdaptiveFilter($this->ui_factory, $this->lng, $this->ctrl, $this->parent_gui);
        $filter = $filter_gui->getFilter(
            'manageContent',
            $this->getInputConditionOptions(),
            $this->getOutputConditionOptions()
        )->withRequest($this->request);

        $filter_data = $filter->getData();
        $analyzer = new StaticInputConfigurationAnalyzer();
        $static_input_configuration_issues = $this->getStaticInputConfigurationIssues($items, $navigator);
        $misconfigured_ref_ids = array_fill_keys(
            $analyzer->getAffectedRefIds($static_input_configuration_issues),
            true
        );
        $issue_details_by_ref_id = $analyzer->getIssueDetailsByRefId($static_input_configuration_issues);
        $data = $this->getTableData(
            $items,
            $filter_data,
            $structural_successors,
            $misconfigured_ref_ids,
            $issue_details_by_ref_id
        );

        $boundaries_db = new LSOAdaptiveBoundaries($this->db);
        $boundary_data = $boundaries_db->getBoundariesFor($this->obj_id);

        $missing_hints = [];
        if ((int) $boundary_data['start_ref_id'] === 0) {
            $missing_hints[] = $this->lng->txt('lso_adaptive_missing_start_object');
        }
        if ((int) $boundary_data['end_ref_id'] === 0) {
            $missing_hints[] = $this->lng->txt('lso_adaptive_missing_end_object');
        }
        if ($missing_hints !== []) {
            $this->tpl->setOnScreenMessage('info', implode('<br>', $missing_hints));
        }

        $table = new LSOAdaptiveTable(
            $this->ui_factory,
            $this->ui_renderer,
            $data,
            $filter,
            (int) $boundary_data['start_ref_id'],
            (int) $boundary_data['end_ref_id'],
            $this->parent_gui,
            $this->ref_id,
            $this->lng,
            $this->ctrl,
            $this->request,
            $this->tpl
        );

        $this->parent_gui->setContent(
            $table->render()
        );
    }

    /**
     * @param \LSItem[] $items Learning sequence items.
     * @return StaticInputConfigurationIssue[]
     */
    protected function getStaticInputConfigurationIssues(array $items, AdaptiveNavigator $navigator): array
    {
        $conditions_by_ref_id = [];
        foreach ($items as $item) {
            $conditions_by_ref_id[$item->getRefId()] = $navigator->getInputConditions($item);
        }

        return (new StaticInputConfigurationAnalyzer())->getIssues($conditions_by_ref_id);
    }

    /**
     * @param array<int, bool> $misconfigured_ref_ids
     */
    protected function isMisconfiguredRefId(int $ref_id, array $misconfigured_ref_ids): bool
    {
        return isset($misconfigured_ref_ids[$ref_id]);
    }

    /**
     * Builds a readable list of object titles for the given ref ids.
     *
     * @param int[] $ref_ids Ref ids of the connected objects.
     */
    protected function getObjectTitleList(array $ref_ids): string
    {
        $titles = [];
        foreach (array_unique($ref_ids) as $ref_id) {
            $titles[] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
        }
        sort($titles);

        if ($titles === []) {
            return $this->lng->txt('no_conditions');
        }

        return implode(', ', $titles);
    }

    /**
     * Gets the filtered content table data.
     *
     * @param \LSItem[] $items Learning sequence items.
     * @param array<string, mixed>|null $filter_data Filter data.
     * @param array<int, int[]> $structural_successors Ref ids of the structural successors per item.
     * @param array<int, bool> $misconfigured_ref_ids
     * @param array<int, StaticInputConfigurationIssueDetail[]> $issue_details_by_ref_id
     * @return ilObjLearningSequenceContentData[]
     */
    protected function getTableData(
        array $items,
        ?array $filter_data,
        array $structural_successors,
        array $misconfigured_ref_ids,
        array $issue_details_by_ref_id
    ): array {
        $boundaries_db = new LSOAdaptiveBoundaries($this->db);
        $boundary_data = $boundaries_db->getBoundariesFor($this->obj_id);
        $start_ref_id = $boundary_data['start_ref_id'];
        $end_ref_id = $boundary_data['end_ref_id'];

        $structural_predecessors = [];
        foreach ($structural_successors as $source_ref_id => $successor_ref_ids) {
            foreach ($successor_ref_ids as $successor_ref_id) {
                $structural_predecessors[$successor_ref_id][] = (int) $source_ref_id;
            }
        }

        $name_filter = $filter_data['name'] ?? '';
        $input_filter = $filter_data['input_conditions'] ?? [];
        $output_filter = $filter_data['output_conditions'] ?? [];
        $online_filter = $filter_data['online_status'] ?? null;
        $position_filter = $filter_data['position'] ?? [];

        usort($items, function ($a, $b) use ($start_ref_id, $end_ref_id) {
            if ($a->getRefId() === $start_ref_id) {
                return -1;
            }
            if ($b->getRefId() === $start_ref_id) {
                return 1;
            }
            if ($a->getRefId() === $end_ref_id) {
                return 1;
            }
            if ($b->getRefId() === $end_ref_id) {
                return -1;
            }
            return $a->getOrderNumber() <=> $b->getOrderNumber();
        });

        $condition_handler = new ConditionHandler();
        $lso_ref_id = $this->ref_id;

        $data = [];
        foreach ($items as $index => $item) {
            $ref_id = $item->getRefId();
            $obj_id = ilObject::_lookupObjId($ref_id);
            $title = ilObject::_lookupTitle($obj_id);

            if ($name_filter !== '' && mb_stripos($title, $name_filter) === false) {
                continue;
            }

            if ($online_filter !== null && $online_filter !== '') {
                $is_online = $item->isOnline();
                if ($online_filter === 'online' && !$is_online) {
                    continue;
                }
                if ($online_filter === 'offline' && $is_online) {
                    continue;
                }
            }

            if (count($position_filter) > 0) {
                $is_start = ($ref_id === $start_ref_id);
                $is_end = ($ref_id === $end_ref_id);
                $matches_position =
                    (in_array('start', $position_filter, true) && $is_start)
                    || (in_array('end', $position_filter, true) && $is_end);
                if (!$matches_position) {
                    continue;
                }
            }

            $type = $item->getType();

            $input_conditions = [];
            $db_input_conditions = $condition_handler->getInputConditionsByRefId($lso_ref_id, $ref_id);
            foreach ($db_input_conditions as $db_cond) {
                $input_conditions[] = new ilObjLearningSequenceConditionData(
                    title: $db_cond['title'],
                    value: $db_cond['value'],
                    internal_name: $db_cond['internal_name']
                );
            }

            $output_conditions = [];
            $db_output_conditions = $condition_handler->getOutputConditionsByRefId($lso_ref_id, $ref_id);
            foreach ($db_output_conditions as $db_cond) {
                $output_conditions[] = new ilObjLearningSequenceConditionData(
                    title: $db_cond['title'],
                    value: $db_cond['value'],
                    internal_name: $db_cond['internal_name']
                );
            }

            if (count($input_filter) > 0) {
                $found = false;
                foreach ($input_conditions as $ic) {
                    if (in_array($ic->internal_name, $input_filter)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    continue;
                }
            }

            if (count($output_filter) > 0) {
                $found = false;
                foreach ($output_conditions as $oc) {
                    if (in_array($oc->internal_name, $output_filter)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    continue;
                }
            }

            // Information
            $prev_title = $this->getObjectTitleList($structural_predecessors[$ref_id] ?? []);
            $next_title = $this->getObjectTitleList($structural_successors[$ref_id] ?? []);

            $icon_path = ilObject::_getIcon($obj_id, "small", $type);
            $actions = $this->parent_gui->getTableActionHandler()->collectActions(
                $ref_id,
                $this->getSpecificActions(
                    $ref_id,
                    $start_ref_id,
                    $end_ref_id
                ),
                $item->isOnline()
            );

            $row = new ilObjLearningSequenceContentData(
                $ref_id,
                $obj_id,
                $title,
                ilObject::_lookupDescription($obj_id),
                $type,
                $icon_path,
                ilLink::_getLink($ref_id, $type),
                $item->isOnline(),
                ($ref_id === $start_ref_id) ? 'Start' : '',
                ($ref_id === $end_ref_id) ? 'End' : '',
                $prev_title,
                $next_title,
                $input_conditions,
                $output_conditions,
                $this->isMisconfiguredRefId($ref_id, $misconfigured_ref_ids),
                $issue_details_by_ref_id[$ref_id] ?? [],
                ($structural_predecessors[$ref_id] ?? []) !== [],
                ($structural_successors[$ref_id] ?? []) !== [],
                $actions
            );

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Gets input condition filter options.
     *
     * @return array<string, string>
     */
    protected function getInputConditionOptions(): array
    {
        return $this->buildConditionOptions(
            $this->getConditionTypes()->getAllInputConditions()
        );
    }

    /**
     * Gets output condition filter options.
     *
     * @return array<string, string>
     */
    protected function getOutputConditionOptions(): array
    {
        return $this->buildConditionOptions(
            $this->getConditionTypes()->getAllOutputConditions()
        );
    }

    private function getConditionTypes(): ConditionTypes
    {
        global $DIC;
        return $DIC['lso.condition_types'];
    }

    /**
     * Builds the multiselect options for the filter from the contributed
     * condition classes. The option key is the condition's internal name (as
     * used when filtering the rows) and the value is its human readable title.
     *
     * @param string[] $condition_classes
     * @return array<string, string>
     */
    private function buildConditionOptions(array $condition_classes): array
    {
        $options = [];
        foreach ($condition_classes as $class) {
            /** @var AbstractCondition $condition */
            $condition = new $class();
            $options[AbstractCondition::getIdentifierForClass($class)] = $this->lng->txt((string) $condition->getName());
        }
        return $options;
    }

    /**
     * Gets actions specific to an adaptive learning sequence item.
     *
     * @return array<string, ilObjLearningSequenceActionData>
     */
    public function getSpecificActions(int $ref_id, int $start_ref_id, int $end_ref_id): array
    {
        $specific_actions = [];

        // 1. Conditions
        $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', $ref_id);
        $link = $this->ctrl->getLinkTargetByClass(ilObjLearningSequenceConditionsGUI::class, 'manageConditions');
        $specific_actions['conditions'] = new ilObjLearningSequenceActionData(
            label: $this->lng->txt('conditions'),
            link: $link
        );

        // 2. Start / End Switcher
        if ($ref_id > 0) {
            if ($ref_id === $start_ref_id) {
                $label = $this->lng->txt('unset_start_object');
                $link = ilObjLearningSequenceContentGUI::CMD_UNSET_START_OBJECT;
                $id = 'unset_start';
            } else {
                $label = $this->lng->txt('set_start_object');
                $link = ilObjLearningSequenceContentGUI::CMD_SET_START_OBJECT;
                $id = 'set_start';
            }
            $this->ctrl->setParameter($this->parent_gui, 'item_ref_id', $ref_id);
            $link = $this->ctrl->getLinkTarget($this->parent_gui, $link);
            $specific_actions[$id] = new ilObjLearningSequenceActionData(label: $label, link: $link);

            if ($ref_id === $end_ref_id) {
                $label = $this->lng->txt('unset_end_object');
                $link = ilObjLearningSequenceContentGUI::CMD_UNSET_END_OBJECT;
                $id = 'unset_end';
            } else {
                $label = $this->lng->txt('set_end_object');
                $link = ilObjLearningSequenceContentGUI::CMD_SET_END_OBJECT;
                $id = 'set_end';
            }
            $this->ctrl->setParameter($this->parent_gui, 'item_ref_id', $ref_id);
            $link = $this->ctrl->getLinkTarget($this->parent_gui, $link);
            $specific_actions[$id] = new ilObjLearningSequenceActionData(label: $label, link: $link);
        } else {
            // Template
            $specific_actions['set_start'] = new ilObjLearningSequenceActionData(
                label: $this->lng->txt('set_start_object'),
                link: ilObjLearningSequenceContentGUI::CMD_SET_START_OBJECT
            );
            $specific_actions['unset_start'] = new ilObjLearningSequenceActionData(
                label: $this->lng->txt('unset_start_object'),
                link: ilObjLearningSequenceContentGUI::CMD_UNSET_START_OBJECT
            );
            $specific_actions['set_end'] = new ilObjLearningSequenceActionData(
                label: $this->lng->txt('set_end_object'),
                link: ilObjLearningSequenceContentGUI::CMD_SET_END_OBJECT
            );
            $specific_actions['unset_end'] = new ilObjLearningSequenceActionData(
                label: $this->lng->txt('unset_end_object'),
                link: ilObjLearningSequenceContentGUI::CMD_UNSET_END_OBJECT
            );
        }

        $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', '');
        return $specific_actions;
    }

    /**
     * Sets the selected item as the start object.
     */
    public function setStartObject(): void
    {
        global $DIC;
        $ref_id = $this->parent_gui->extractItemRefId();
        if ($ref_id > 0) {
            $boundaries = new LSOAdaptiveBoundaries($DIC->database());
            $current = $boundaries->getBoundariesFor($this->obj_id);
            if ((int) $current['end_ref_id'] === $ref_id) {
                // An object must never be start and end object at the same time.
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('lso_adaptive_start_end_not_same'),
                    true
                );
                $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
            }
            $boundaries->setStartRefId($this->obj_id, $ref_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }
        $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
    }

    /**
     * Removes the start object.
     */
    public function unsetStartObject(): void
    {
        global $DIC;
        $boundaries = new LSOAdaptiveBoundaries($DIC->database());
        $boundaries->unsetStartRefId($this->obj_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);

        /** @var ilObjLearningSequence $lso */
        $lso = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
        $this->enforceOfflineForMissingBoundaries($lso);
        $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
    }

    /**
     * Sets the selected item as the end object.
     */
    public function setEndObject(): void
    {
        global $DIC;
        $ref_id = $this->parent_gui->extractItemRefId();
        if ($ref_id > 0) {
            $boundaries = new LSOAdaptiveBoundaries($DIC->database());
            $current = $boundaries->getBoundariesFor($this->obj_id);
            if ((int) $current['start_ref_id'] === $ref_id) {
                // An object must never be start and end object at the same time.
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('lso_adaptive_start_end_not_same'),
                    true
                );
                $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
            }
            $boundaries->setEndRefId($this->obj_id, $ref_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }
        $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
    }

    /**
     * Removes the end object.
     */
    public function unsetEndObject(): void
    {
        global $DIC;
        $boundaries = new LSOAdaptiveBoundaries($DIC->database());
        $boundaries->unsetEndRefId($this->obj_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);

        /** @var ilObjLearningSequence $lso */
        $lso = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
        $this->enforceOfflineForMissingBoundaries($lso);
        $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
    }
}
