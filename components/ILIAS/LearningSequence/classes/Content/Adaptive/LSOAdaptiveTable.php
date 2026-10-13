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

use ILIAS\UI\Component\Button\Tag;

/**
 * Renders the adaptive learning sequence content table.
 */
readonly class LSOAdaptiveTable
{
    /**
     * Creates the adaptive content table.
     *
     * @param \ilObjLearningSequenceContentData[] $objects Content data records.
     */
    public function __construct(
        /** UI factory. */
        private \ILIAS\UI\Factory $ui_factory,
        /** UI renderer. */
        private \ILIAS\UI\Renderer $ui_renderer,
        /** @var \ilObjLearningSequenceContentData[] Content data records. */
        private array $objects,
        /** Content filter. */
        private ?\ILIAS\UI\Component\Input\Container\Filter\Standard $filter = null,
        /** Start object reference ID. */
        private int $start_ref_id = 0,
        /** End object reference ID. */
        private int $end_ref_id = 0,
        /** Parent content GUI. */
        private ?\ilObjLearningSequenceContentGUI $parent_gui = null,
        /** Learning sequence repository reference ID. */
        private int $ref_id = 0,
        /** Language service. */
        private ?\ilLanguage $lng = null,
        /** Controller service. */
        private ?\ilCtrl $ctrl = null,
        /** Server request. */
        private ?\Psr\Http\Message\ServerRequestInterface $request = null,
        /** Global template. */
        private ?\ilGlobalTemplateInterface $tpl = null
    ) {
    }

    /**
     * Renders the content table.
     */
    public function render(): string
    {
        $data = $this->objects;
        $view_controls = [];

        if ($this->filter !== null) {
            $view_controls[] = $this->filter;
        }

        $modals = [];
        $environment = $this->buildEnvironment($modals);

        $mapping = function (
            $row,
            \ilObjLearningSequenceContentData $record,
            $ui_factory,
            array $env
        ) {
            $leading_icon = $ui_factory->symbol()->icon()->custom(
                $record->icon_path,
                $record->type
            );

            $actions = $env['actions']($record);
            $content = $env['content']($record);
            $headline = $env['headline']($record);

            return $row
                ->withHeadline($headline)
                ->withLeadingSymbol($leading_icon)
                ->withSubheadline($record->description)
                ->withContent($content)
                ->withFurtherFieldsHeadline($this->lng->txt('lso_adaptive_information'))
                ->withFurtherFields($env['further_fields']($record))
                ->withAction($actions);
        };

        $table = $this->ui_factory->table()->presentation(
            $this->lng->txt('table_sequence_content'),
            $view_controls,
            $mapping
        )
            ->withEnvironment($environment)
            ->withData($data);

        $html = $this->ui_renderer->render($table);

        if ($modals !== []) {
            $html .= $this->ui_renderer->render($modals);
        }

        return $html;
    }

    /**
     * Builds the presentation table environment.
     *
     * @param array<int, \ILIAS\UI\Component\Modal\Interruptive> $modals
     * @return array<string, callable>
     */
    private function buildEnvironment(array &$modals): array
    {
        $headline = function (\ilObjLearningSequenceContentData $record): string {
            $link_html = $this->ui_renderer->render(
                $this->ui_factory->link()->standard($record->title, $record->href)
            );

            $tags = [];
            if ($record->is_online) {
                $tags[] = $this->buildStatusTag('table_online', 'lso_adaptive_online_info', Tag::REL_LOW);
            } else {
                $tags[] = $this->buildStatusTag('lso_adaptive_offline', 'lso_adaptive_offline_info', Tag::REL_MID);
            }

            if ($record->start_object !== '') {
                $tags[] = $this->buildStatusTag('lso_adaptive_start', 'lso_adaptive_start_info', Tag::REL_LOW);
            }
            if ($record->end_object !== '') {
                $tags[] = $this->buildStatusTag('lso_adaptive_end', 'lso_adaptive_end_info', Tag::REL_LOW);
            }
            if ($record->end_object === '' && !$record->has_structural_successor) {
                $tags[] = $this->buildStatusTag('lso_adaptive_dead_end', 'lso_adaptive_dead_end_info', Tag::REL_HIGH);
            }
            if ($record->has_conflicting_input_configuration) {
                $tags[] = $this->buildMisconfigurationTag($record);
            }
            if ($record->start_object === '' && !$record->has_structural_predecessor) {
                $tags[] = $this->buildStatusTag(
                    'lso_adaptive_entry_point',
                    'lso_adaptive_entry_point_info',
                    Tag::REL_LOW
                );
            }

            // Each entry is a popover together with its triggering tag.
            $tags_html = array_map(
                fn(array $popover_and_tag): string => $this->ui_renderer->render($popover_and_tag),
                $tags
            );

            // Tags carry their own right margin, so no extra whitespace is added between them.
            return $link_html . ' ' . implode('', $tags_html);
        };

        $actions = function (\ilObjLearningSequenceContentData $record) use (&$modals) {
            $dropdown_items = [];
            $lso = \ilObjLearningSequence::getInstanceByRefId($this->ref_id);
            $ref_id = $lso->getRefId();
            $obj_id = $lso->getId();

            $specific_actions = new LSOAdaptiveContent(
                $this->parent_gui,
                $this->ui_factory,
                $this->ui_renderer,
                $this->lng,
                $this->ctrl,
                $this->request,
                $this->tpl,
                $ref_id,
                $obj_id
            )->getSpecificActions(
                $record->ref_id,
                $this->start_ref_id,
                $this->end_ref_id
            );
            $action_data = $this->parent_gui->getTableActionHandler()->collectActions(
                $record->ref_id,
                $specific_actions,
                $record->is_online
            );

            foreach ($action_data as $id => $action) {
                if ($action->is_divider) {
                    $dropdown_items[] = $this->ui_factory->divider()->horizontal();
                    continue;
                }

                $label = $action->label;
                $link = $action->link;
                if (strpos($link, 'http') !== 0 && strpos($link, 'ilias.php') === false) {
                    $this->ctrl->setParameter($this->parent_gui, 'item_ref_id', $record->ref_id);
                    $link = $this->ctrl->getLinkTarget($this->parent_gui, $link);
                }

                $dropdown_items[] = $this->ui_factory->button()->shy($label, $link);
            }

            $delete_modal = $this->buildDeleteModal($record->ref_id, $record->title);
            $modals[$record->ref_id] = $delete_modal;
            $dropdown_items[] = $this->ui_factory->button()->shy(
                $this->lng->txt('delete'),
                ''
            )->withOnClick($delete_modal->getShowSignal());

            return $this->ui_factory->dropdown()->standard($dropdown_items)->withLabel('');
        };

        $content = function (\ilObjLearningSequenceContentData $record) {
            return $this->ui_factory->listing()->descriptive([
                $this->lng->txt('input_conditions') => $this->buildConditionListing($record->input_conditions),
                $this->lng->txt('output_conditions') => $this->buildConditionListing($record->output_conditions),
            ]);
        };

        $further_fields = fn(\ilObjLearningSequenceContentData $record): array => [
            $this->lng->txt('lso_adaptive_previous_object') . ':' => htmlspecialchars(trim($record->previous_objects)),
            $this->lng->txt('lso_adaptive_next_object') . ':' => htmlspecialchars(trim($record->next_objects)),
        ];

        return [
            'headline' => $headline,
            'actions' => $actions,
            'content' => $content,
            'further_fields' => $further_fields,
        ];
    }

    /**
     * Builds a status tag that opens a popover explaining the status.
     *
     * @return \ILIAS\UI\Component\Component[] The popover and its triggering tag.
     */
    private function buildStatusTag(string $language_var, string $info_language_var, string $relevance): array
    {
        $popover = $this->ui_factory->popover()
            ->listing([
                $this->ui_factory->item()
                    ->standard($this->lng->txt($language_var))
                    ->withDescription($this->lng->txt($info_language_var)),
            ])
            ->withVerticalPosition();
        $tag = $this->ui_factory->button()
            ->tag($this->lng->txt($language_var), '')
            ->withRelevance($relevance)
            ->withOnClick($popover->getShowSignal());

        return [$popover, $tag];
    }

    /**
     * Builds the misconfiguration tag. If issue details exist, the tag opens them in a popover.
     *
     * @return \ILIAS\UI\Component\Component[]
     */
    private function buildMisconfigurationTag(\ilObjLearningSequenceContentData $record): array
    {
        if ($record->static_input_configuration_issue_details === []) {
            return $this->buildStatusTag(
                'lso_adaptive_misconfigured',
                'lso_adaptive_misconfigured_info',
                Tag::REL_HIGH
            );
        }

        $items = [];
        $issue_details = $record->static_input_configuration_issue_details;
        foreach ($issue_details as $index => $detail) {
            $item = $this->ui_factory->item()->standard(
                $this->lng->txt($detail->title_language_var)
            );

            $description = $this->buildIssueDetailDescription($detail);
            if ($description !== null && $description !== '') {
                $item = $item->withDescription($description);
            }

            $items[] = $item;

            if ($index < count($issue_details) - 1) {
                $items[] = $this->ui_factory->divider()->horizontal();
            }
        }

        $popover = $this->ui_factory->popover()
            ->listing($items)
            ->withTitle($this->lng->txt('lso_adaptive_misconfigured'))
            ->withVerticalPosition();
        $tag = $this->ui_factory->button()
            ->tag($this->lng->txt('lso_adaptive_misconfigured'), '')
            ->withRelevance(Tag::REL_HIGH)
            ->withOnClick($popover->getShowSignal());

        return [$popover, $tag];
    }

    /**
     * @return string|null
     */
    private function buildIssueDetailDescription(
        \ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail $detail
    ): ?string {
        $parts = [];

        if ($detail->description_language_var !== null) {
            $parts[] = $this->lng->txt($detail->description_language_var);
        }

        foreach ($detail->properties_by_language_var as $language_var => $value) {
            $parts[] = sprintf(
                '%s: %s',
                $this->lng->txt($language_var),
                is_array($value) ? $this->renderIssueDetailPropertyList($value) : $value
            );
        }

        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }

    /**
     * @param array<int, int|string> $values
     */
    private function renderIssueDetailPropertyList(array $values): string
    {
        $items = [];

        foreach ($values as $value) {
            if (is_int($value)) {
                $items[] = \ilObject::_lookupTitle(\ilObject::_lookupObjId($value));
                continue;
            }

            $items[] = $value;
        }

        sort($items);

        return implode(', ', $items);
    }

    /**
     * Builds the delete confirmation modal for an item.
     */
    private function buildDeleteModal(int $ref_id, string $title): \ILIAS\UI\Component\Modal\Interruptive
    {
        $this->ctrl->setParameter($this->parent_gui, 'item_ref_id', '');
        $form_action = $this->ctrl->getFormAction(
            $this->parent_gui,
            \ilObjLearningSequenceContentGUI::CMD_DELETE
        );

        $item = $this->ui_factory->modal()->interruptiveItem()->keyValue(
            (string) $ref_id,
            $this->lng->txt('title'),
            $title
        );

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('info_delete_sure'),
            $form_action
        )
            ->withAffectedItems([$item])
            ->withActionButtonLabel($this->lng->txt('delete'));
    }

    /**
     * Builds a listing of condition titles and values.
     *
     * @param \ilObjLearningSequenceConditionData[] $conditions
     */
    private function buildConditionListing(array $conditions): string|\ILIAS\UI\Component\Listing\Property
    {
        if ($conditions === []) {
            return $this->lng->txt('no_conditions');
        }

        $listing = $this->ui_factory->listing()->property();
        foreach ($conditions as $condition) {
            $listing = $condition->value === ''
                ? $listing->withProperty($condition->title, $condition->title, false)
                : $listing->withProperty($condition->title, $condition->value);
        }

        return $listing;
    }
}
