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

namespace ILIAS\MetaData\Vocabularies\Controlled;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValue;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as SlotHandler;
use ILIAS\MetaData\Elements\Data\Type as DataType;
use ILIAS\MetaData\Vocabularies\Controlled\Database\WrapperInterface as DatabaseWrapper;

class Repository implements RepositoryInterface
{
    protected DatabaseWrapper $db;
    protected FactoryInterface $factory;
    protected SlotHandler $slot_handler;

    public function __construct(
        DatabaseWrapper $db,
        FactoryInterface $factory,
        SlotHandler $slot_handler
    ) {
        $this->db = $db;
        $this->factory = $factory;
        $this->slot_handler = $slot_handler;
    }

    /**
     * @throws \ilMDVocabulariesException
     */
    public function create(
        SlotIdentifier $slot,
        string $source
    ): string {
        if ($source === FactoryInterface::STANDARD_SOURCE) {
            throw new \ilMDVocabulariesException(FactoryInterface::STANDARD_SOURCE . ' is reserved as a source.');
        }
        if ($source === '') {
            throw new \ilMDVocabulariesException('Source cannot be empty.');
        }
        if (!$this->isSlotValid($slot)) {
            throw new \ilMDVocabulariesException(
                'Slot ' . $slot->value . ' is not available for controlled vocabularies.'
            );
        }

        $id = $this->db->nextId('il_md_vocab_contr');

        $this->db->insert(
            'il_md_vocab_contr',
            [
                'id' => [\ilDBConstants::T_INTEGER, $id],
                'slot' => [\ilDBConstants::T_TEXT, $slot->value],
                'source' => [\ilDBConstants::T_TEXT, $source],
                'active' => [\ilDBConstants::T_INTEGER, 1],
                'custom_input' => [\ilDBConstants::T_INTEGER, 1]
            ]
        );

        return (string) $id;
    }

    public function addValueToVocabulary(
        string $vocab_id,
        string $value,
        string $label = ''
    ): void {
        if ($value === '') {
            return;
        }
        $this->db->insert(
            'il_md_vocab_contr_vals',
            [
                'vocab_id' => [\ilDBConstants::T_INTEGER, $vocab_id],
                'value' => [\ilDBConstants::T_TEXT, $value],
                'label' => [\ilDBConstants::T_TEXT, $label]
            ]
        );
    }

    /**
     * @return string[]
     */
    public function findAlreadyExistingValues(
        SlotIdentifier $slot,
        string ...$values
    ): \Generator {
        $result = $this->db->query(
            'SELECT value FROM il_md_vocab_contr_vals JOIN il_md_vocab_contr ON vocab_id
            WHERE slot = ' . $this->db->quoteAsString($slot->value) . ' AND ' .
            $this->db->in('value', ...$values)
        );

        foreach ($result as $row) {
            yield (string) $row['value'];
        }
    }

    public function getVocabulary(string $vocab_id): VocabularyInterface
    {
        $result = $this->db->query(
            'SELECT id, slot, source, active, custom_input FROM il_md_vocab_contr
            WHERE  id = ' . $this->db->quoteAsInteger($vocab_id)
        );

        foreach ($result as $row) {
            return $this->getVocabularyFromRow($row);
        }
        return $this->factory->null();
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
    {
        yield from $this->readVocabularies(false, ...$slots);
    }

    public function countActiveVocabulariesForSlot(SlotIdentifier $slot): int
    {
        $result = $this->db->query(
            'SELECT COUNT(*) AS count FROM il_md_vocab_contr WHERE active = 1 AND slot = ' .
            $this->db->quoteAsString($slot->value)
        );

        foreach ($result as $row) {
            return (int) $row['count'];
        }
        return 0;
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
    {
        yield from $this->readVocabularies(true, ...$slots);
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function getLabelsForValues(
        SlotIdentifier $slot,
        bool $only_active,
        string ...$values
    ): \Generator {
        if (!$this->isSlotValid($slot)) {
            return;
        }

        $active_where = '';
        if ($only_active) {
            $active_where = 'active = 1 AND ';
        }

        $result = $this->db->query(
            'SELECT value, label FROM il_md_vocab_contr_vals JOIN il_md_vocab_contr ON vocab_id
            WHERE slot = ' . $this->db->quoteAsString($slot->value) . ' AND ' . $active_where .
            $this->db->in('value', ...$values)
        );

        foreach ($result as $row) {
            yield new LabelledValue(
                (string) $row['value'],
                (string) $row['label']
            );
        }
    }

    public function setActiveForVocabulary(
        string $vocab_id,
        bool $active
    ): void {
        $this->db->update(
            'il_md_vocab_contr',
            ['active' => [\ilDBConstants::T_INTEGER, (int) $active]],
            ['id' => [\ilDBConstants::T_INTEGER, $vocab_id]]
        );
    }

    public function setCustomInputsAllowedForVocabulary(
        string $vocab_id,
        bool $custom_inputs
    ): void {
        $this->db->update(
            'il_md_vocab_contr',
            ['custom_input' => [\ilDBConstants::T_INTEGER, (int) $custom_inputs]],
            ['id' => [\ilDBConstants::T_INTEGER, $vocab_id]]
        );
    }

    public function deleteVocabulary(string $vocab_id): void
    {
        $this->db->manipulate(
            'DELETE FROM il_md_vocab_contr_vals WHERE vocab_id = ' .
            $this->db->quoteAsString($vocab_id)
        );
        $this->db->manipulate(
            'DELETE FROM il_md_vocab_contr WHERE id = ' .
            $this->db->quoteAsString($vocab_id)
        );
    }

    /**
     * @return VocabularyInterface[]
     */
    protected function readVocabularies(bool $only_active, SlotIdentifier ...$slots): \Generator
    {
        $slot_values = [];
        foreach ($slots as $slot) {
            if (!$this->isSlotValid($slot)) {
                continue;
            }
            $slot_values[] = $slot->value;
        }

        $where_active = '';
        if ($only_active) {
            $where_active = ' AND active = 1';
        }
        $result = $this->db->query(
            'SELECT id, slot, source, active, custom_input FROM il_md_vocab_contr
            WHERE  ' . $this->db->in('slot', ...$slot_values) .
            $where_active
        );

        foreach ($result as $row) {
            yield $this->getVocabularyFromRow($row);
        }
    }

    protected function getVocabularyFromRow(array $row): VocabularyInterface
    {
        $slot = SlotIdentifier::from((string) $row['slot']);
        $id = (string) $row['id'];
        $source = (string) $row['source'];
        $values = $this->readVocabularyValues((string) $row['id']);

        if ($this->slot_handler->dataTypeForSlot($slot) === DataType::VOCAB_VALUE) {
            $builder = $this->factory->controlledVocabValue($slot, $id, $source, ...$values);
        } else {
            $builder = $this->factory->controlledString($slot, $id, $source, ...$values);
        }

        return $builder->withIsDeactivated(!$row['active'])
                       ->withDisallowsCustomInputs(!$row['custom_input'])
                       ->get();
    }

    /**
     * @return string[]
     */
    protected function readVocabularyValues(string $vocab_id): \Generator
    {
        $result = $this->db->query(
            'SELECT value FROM il_md_vocab_contr_vals WHERE vocab_id = ' .
            $this->db->quoteAsInteger($vocab_id)
        );
        foreach ($result as $row) {
            yield (string) $row['value'];
        }
    }

    protected function isSlotValid(SlotIdentifier $slot): bool
    {
        $valid_slots = [
             SlotIdentifier::GENERAL_STRUCTURE,
             SlotIdentifier::GENERAL_AGGREGATION_LEVEL,
             SlotIdentifier::GENERAL_COVERAGE,
             SlotIdentifier::GENERAL_IDENTIFIER_CATALOG,
             SlotIdentifier::LIFECYCLE_STATUS,
             SlotIdentifier::LIFECYCLE_CONTRIBUTE_PUBLISHER,
             SlotIdentifier::METAMETADATA_IDENTIFIER_CATALOG,
             SlotIdentifier::METAMETADATA_SCHEMA,
             SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
             SlotIdentifier::TECHNICAL_REQUIREMENT_OS,
             SlotIdentifier::TECHNICAL_OTHER_PLATFORM_REQUIREMENTS,
             SlotIdentifier::TECHNICAL_FORMAT,
             SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE,
             SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE,
             SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL,
             SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY,
             SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE,
             SlotIdentifier::EDUCATIONAL_CONTEXT,
             SlotIdentifier::EDUCATIONAL_DIFFICULTY,
             SlotIdentifier::EDUCATIONAL_TYPICAL_AGE_RANGE,
             SlotIdentifier::RIGHTS_COST,
             SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS,
             SlotIdentifier::RELATION_KIND,
             SlotIdentifier::RELATION_RESOURCE_IDENTIFIER_CATALOG,
             SlotIdentifier::CLASSIFICATION_PURPOSE,
             SlotIdentifier::CLASSIFICATION_KEYWORD,
             SlotIdentifier::CLASSIFICATION_TAXPATH_SOURCE,
             SlotIdentifier::CLASSIFICATION_TAXON_ENTRY
        ];

        return in_array($slot, $valid_slots, true);
    }
}
