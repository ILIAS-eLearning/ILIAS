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

use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;

class ilLearningSequenceXMLWriter extends ilXmlWriter
{
    public const string TAG_LSO = 'LearningSequence';
    public const string TAG_LSITEMS = 'LSItems';
    public const string TAG_LSITEM = 'LSItem';
    public const string TAG_MEMBERSGALLERY = 'MembersGallery';
    public const string TAG_CONDITION = 'Condition';
    public const string TAG_ITEM_CONDITIONS = 'ItemConditions';
    public const string TAG_ITEM_CONDITION = 'ItemCondition';
    public const string TAG_LPSETTING = 'LPSetting';
    public const string TAG_LPREFID = 'LPRefId';
    public const string TAG_TITLE = 'title';
    public const string TAG_DESCRIPTION = 'description';
    public const string TAG_CONTAINERSETTING = 'ContainerSetting';

    protected ilLearningSequenceSettings $ls_settings;
    private ilObjLearningSequenceConditionDiscover $discover;
    private ConditionFactory $condition_factory;
    private ilDBInterface $database;

    public function __construct(
        protected ilObjLearningSequence $ls_object,
        protected ilSetting $settings,
        protected ilLPObjSettings $lp_settings
    ) {
        global $DIC;
        parent::__construct();
        $this->database = $DIC->database();
        $this->ls_settings = $ls_object->getLSSettings();
        $this->discover = new ilObjLearningSequenceConditionDiscover();
        $this->condition_factory = new ConditionFactory(
            $this->discover,
            $this->database,
        );
    }

    public function getXml(): string
    {
        return $this->xmlDumpMem(false);
    }

    public function start(): void
    {
        $this->writeHeader();
        $this->writeLearningSequence();
        $this->writeLSItems();
        $this->writeFooter();
    }

    protected function writeHeader(): void
    {
        $this->xmlSetDtdDef(
            "<!DOCTYPE learning sequence PUBLIC \"-//ILIAS//DTD LearningSequence//EN\" \"" .
            ILIAS_HTTP_PATH . "/xml/ilias_lso_11_0.dtd\">"
        );

        $this->xmlSetGenCmt(
            "Export of ILIAS LearningSequence " .
            $this->ls_object->getId() .
            " of installation " .
            $this->settings->get("inst_id") .
            "."
        );
    }

    protected function writeLearningSequence(): void
    {
        $boundaries = new LSOAdaptiveBoundaries($this->database)
            ->getBoundariesFor($this->ls_object->getId());

        $attributes = [
            'ref_id' => $this->ls_object->getRefId(),
            'members_gallery' => $this->ls_settings->getMembersGallery() ? 'true' : 'false',
            'mode' => $this->ls_settings->getMode(),
            'start_ref_id' => $boundaries['start_ref_id'],
            'end_ref_id' => $boundaries['end_ref_id'],
        ];
        $this->xmlStartTag(self::TAG_LSO, $attributes);

        $this->xmlElement(self::TAG_TITLE, null, $this->ls_object->getTitle());
        if ($desc = $this->ls_object->getDescription()) {
            $this->xmlElement(self::TAG_DESCRIPTION, null, $desc);
        }

        $this->writeLPSettings();
        \ilContainer::_exportContainerSettings($this, $this->ls_object->getId());
    }

    protected function writeLPSettings(): void
    {
        $type = $this->lp_settings->getObjType();
        $mode = $this->lp_settings->getMode();
        $this->xmlStartTag(
            self::TAG_LPSETTING,
            [
                'type' => $type,
                'mode' => $mode
            ]
        );
        $collection = ilLPCollection::getInstanceByMode(
            $this->ls_object->getId(),
            $mode
        );
        if (!is_null($collection)) {
            $items = $collection->getItems();
            foreach ($items as $item) {
                $this->xmlElement(self::TAG_LPREFID, null, $item);
            }
        }
        $this->xmlEndTag(self::TAG_LPSETTING);
    }

    protected function writeLSItems(): void
    {
        $this->xmlStartTag(self::TAG_LSITEMS);
        $ls_items = $this->ls_object->getLSItems();
        foreach ($ls_items as $ls_item) {
            $post_condition = $ls_item->getPostCondition();

            $this->xmlStartTag(
                self::TAG_LSITEM,
                [
                    'obj_id' => \ilObject::_lookupObjectId($ls_item->getRefId()),
                    'ref_id' => $ls_item->getRefId(),
                    'position' => $ls_item->getOrderNumber()
                ]
            );

            $this->xmlElement(
                self::TAG_CONDITION,
                ['type' => $post_condition->getConditionOperator()],
                $post_condition->getValue()
            );

            $this->writeConditionsXml($ls_item);

            $this->xmlEndTag(self::TAG_LSITEM);
        }

        $this->xmlEndTag(self::TAG_LSITEMS);
    }

    protected function writeConditionsXml($ls_item): void
    {
        $conditions = $this->getConditionData($ls_item);

        $this->xmlStartTag(self::TAG_ITEM_CONDITIONS);
        foreach ($conditions as $condition) {
            $attrs = [
                'type' => $condition['type']
            ];

            if (isset($condition['subtype'])) {
                $attrs['subtype'] = (string) $condition['subtype'];
            }

            $this->xmlStartTag(self::TAG_ITEM_CONDITION, $attrs);

            $payload = $condition['data'] ?? [];
            $this->writeConditionPayloadXml($payload);

            $this->xmlEndTag(self::TAG_ITEM_CONDITION);
        }
        $this->xmlEndTag(self::TAG_ITEM_CONDITIONS);
    }

    /**
     * @param array $payload Format: [['table' => string, 'fields' => array, 'rows' => array], ...]
     */
    protected function writeConditionPayloadXml(array $payload): void
    {
        $this->xmlStartTag('Data');

        foreach ($payload as $table_data) {
            $table_name = (string) ($table_data['table'] ?? '');
            $this->xmlStartTag('Table', ['name' => $table_name]);

            $fields = is_array($table_data['fields'] ?? null) ? $table_data['fields'] : [];
            $this->xmlStartTag('Fields');
            foreach ($fields as $field_name => $definition) {
                $attrs = ['name' => (string) $field_name];
                if (is_array($definition)) {
                    foreach ($definition as $meta_key => $meta_value) {
                        $attrs[(string) $meta_key] = is_bool($meta_value)
                            ? ($meta_value ? '1' : '0')
                            : (string) $meta_value;
                    }
                }
                $this->xmlElement('Field', $attrs);
            }
            $this->xmlEndTag('Fields');

            $rows = is_array($table_data['rows'] ?? null) ? $table_data['rows'] : [];
            $this->xmlStartTag('Rows');
            foreach ($rows as $row) {
                $this->xmlStartTag('Row');
                if (is_array($row)) {
                    foreach ($row as $col => $value) {
                        $this->xmlElement(
                            'Col',
                            ['name' => (string) $col],
                            $value === null ? '' : (string) $value
                        );
                    }
                }
                $this->xmlEndTag('Row');
            }
            $this->xmlEndTag('Rows');

            $this->xmlEndTag('Table');
        }

        $this->xmlEndTag('Data');
    }

    protected function getConditionData($ls_item): array
    {
        $all_conditions = [];

        $condition_ids = $this->discover->getAllConditionIdsForItem($ls_item->getRefId());
        foreach ($condition_ids as $condition_id) {
            try {
                $condition = $this->condition_factory->getConditionInstanceById($condition_id);
            } catch (\Throwable) {
                continue;
            }

            $entry = [
                'type' => $condition->getIdentifierForClass($condition::class),
                'subtype' => null,
                'data' => [],
            ];

            if ($condition instanceof SubtypeAwareInterface) {
                $entry['subtype'] = $condition->getSubtype();
            }

            $entry['data'] = $condition->export();
            $all_conditions[] = $entry;
        }

        return $all_conditions;
    }

    protected function writeFooter(): void
    {
        $this->xmlEndTag(self::TAG_LSO);
    }
}
