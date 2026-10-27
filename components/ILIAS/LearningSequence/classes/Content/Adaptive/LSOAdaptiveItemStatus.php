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

use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\UI\Component\Button\Tag;
use ILIAS\UI\Component\Listing\Property;
use ILIAS\UI\Factory as UIFactory;

/**
 * Describes the status of an item in the adaptive content table: the tags
 * shown below its title and the configuration issues shown in its details.
 */
final readonly class LSOAdaptiveItemStatus
{
    public function __construct(
        private UIFactory $ui_factory,
        private \ilLanguage $lng
    ) {
    }

    /**
     * The status tags of the item. They only label the item and trigger no action.
     *
     * @return Tag[]
     */
    public function getTags(\ilObjLearningSequenceContentData $record): array
    {
        $tags = [
            $record->is_online
                ? $this->buildTag('table_online', Tag::REL_LOW)
                : $this->buildTag('lso_adaptive_offline', Tag::REL_MID)
        ];
        if ($record->start_object !== '') {
            $tags[] = $this->buildTag('lso_adaptive_start', Tag::REL_LOW);
        }
        if ($record->end_object !== '') {
            $tags[] = $this->buildTag('lso_adaptive_end', Tag::REL_LOW);
        }
        if ($record->end_object === '' && !$record->has_structural_successor) {
            $tags[] = $this->buildTag('lso_adaptive_dead_end', Tag::REL_HIGH);
        }
        if ($record->has_conflicting_input_configuration) {
            $tags[] = $this->buildTag('lso_adaptive_misconfigured', Tag::REL_HIGH);
        }
        if ($record->start_object === '' && !$record->has_structural_predecessor) {
            $tags[] = $this->buildTag('lso_adaptive_entry_point', Tag::REL_LOW);
        }

        return $tags;
    }

    /**
     * The configuration issues of a misconfigured item, or null if its
     * configuration is fine.
     */
    public function getConfigurationIssues(\ilObjLearningSequenceContentData $record): Property|string|null
    {
        if (!$record->has_conflicting_input_configuration) {
            return null;
        }
        if ($record->static_input_configuration_issue_details === []) {
            return $this->lng->txt('lso_adaptive_misconfigured_info');
        }

        $listing = $this->ui_factory->listing()->property();
        foreach ($record->static_input_configuration_issue_details as $detail) {
            $title = $this->lng->txt($detail->title_language_var);
            $description = $this->buildIssueDescription($detail);
            $listing = $listing->withProperty(
                $title,
                $description === '' ? $title : $title . ' ' . $description,
                false
            );
        }

        return $listing;
    }

    private function buildTag(string $language_var, string $relevance): Tag
    {
        return $this->ui_factory->button()
            ->tag($this->lng->txt($language_var), '')
            ->withRelevance($relevance);
    }

    private function buildIssueDescription(StaticInputConfigurationIssueDetail $detail): string
    {
        $parts = [];
        if ($detail->description_language_var !== null) {
            $parts[] = $this->lng->txt($detail->description_language_var);
        }
        foreach ($detail->properties_by_language_var as $language_var => $value) {
            $parts[] = sprintf(
                '%s: %s',
                $this->lng->txt($language_var),
                is_array($value) ? $this->buildPropertyList($value) : $value
            );
        }

        return implode(' ', $parts);
    }

    /**
     * @param array<int, int|string> $values ref_ids of objects or plain values
     */
    private function buildPropertyList(array $values): string
    {
        $items = array_map(
            fn(int|string $value): string => is_int($value) ? $this->lookupTitle($value) : $value,
            $values
        );
        sort($items);

        return implode(', ', $items);
    }

    private function lookupTitle(int $ref_id): string
    {
        return \ilObject::_lookupTitle(\ilObject::_lookupObjId($ref_id));
    }
}
