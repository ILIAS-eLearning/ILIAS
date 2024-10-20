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

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface as VocabFactory;

class ilMDControlledVocabsUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a new table to store deactivated standard vocabularies.
     */
    public function step_1(): void
    {
        if (!$this->db->tableExists('il_md_vocab_inactive')) {
            $this->db->createTable(
                'il_md_vocab_inactive',
                [
                    'slot' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ]
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_inactive', ['slot']);
        }
    }

    /**
     * Add a new table to store controlled vocabularies.
     */
    public function step_2(): void
    {
        if (!$this->db->tableExists('il_md_vocab_contr')) {
            $this->db->createTable(
                'il_md_vocab_contr',
                [
                    'id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 4
                    ],
                    'slot' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ],
                    'source' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ],
                    'active' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 1,
                        'default' => 1
                    ],
                    'custom_input' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 1,
                        'default' => 1
                    ],
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_contr', ['id']);
            $this->db->createSequence('il_md_vocab_contr');
        }
    }

    /**
     * Add a new table to store values and labels of controlled vocabularies.
     */
    public function step_3(): void
    {
        if (!$this->db->tableExists('il_md_vocab_contr_vals')) {
            $this->db->createTable(
                'il_md_vocab_contr_vals',
                [
                    'vocab_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 4
                    ],
                    'value' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 255
                    ],
                    'label' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 255
                    ]
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_contr_vals', ['vocab_id', 'value']);
        }
    }

    /**
     * Add columns for the sources of all vocab values.
     */
    public function step_4(): void
    {
        foreach ($this->getAllVocabSlots() as $slot) {
            $table = $this->getTableForVocabSlot($slot);
            $src_column = $this->getSourceColumnNameForVocabSlot($slot);

            if ($this->db->tableColumnExists($table, $src_column)) {
                continue;
            }
            $this->db->addTableColumn(
                $table,
                $src_column,
                [
                    'type' => ilDBConstants::T_TEXT,
                    'notnull' => true,
                    'length' => 64
                ]
            );
        }
    }

    /**
     * Make more space in the columns of vocab values to hold controlled values.
     */
    public function step_5(): void
    {
        foreach ($this->getAllVocabSlots() as $slot) {
            $table = $this->getTableForVocabSlot($slot);
            $value_column = $this->getValueColumnNameForVocabSlot($slot);

            if (!$this->db->tableColumnExists($table, $value_column)) {
                continue;
            }
            $this->db->modifyTableColumn(
                $table,
                $value_column,
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 255
                ]
            );
        }
    }

    /**
     * Normalize existing vocab values.
     */
    public function step_6(): void
    {
        foreach ($this->getAllVocabSlots() as $slot) {
            $table = $this->getTableForVocabSlot($slot);
            $value_column = $this->getValueColumnNameForVocabSlot($slot);

            foreach ($this->getTranslationsForVocabSlot($slot) as $new_value => $old_value) {
                if ($new_value === $old_value) {
                    continue;
                }
                $this->db->update(
                    $table,
                    [$value_column => [ilDBConstants::T_TEXT, $new_value]],
                    [$value_column => [ilDBConstants::T_TEXT, $old_value]]
                );
            }
        }
    }

    /**
     * Fill source columns with the default value.
     */
    public function step_7(): void
    {
        foreach ($this->getAllVocabSlots() as $slot) {
            $table = $this->getTableForVocabSlot($slot);
            $src_column = $this->getSourceColumnNameForVocabSlot($slot);
            $value_column = $this->getValueColumnNameForVocabSlot($slot);
            $standard_values = array_keys($this->getTranslationsForVocabSlot($slot));

            $this->db->manipulate(
                'UPDATE ' . $this->db->quoteIdentifier($table) . ' SET ' .
                $this->db->quoteIdentifier($src_column) . ' = ' .
                $this->db->quote(VocabFactory::STANDARD_SOURCE, ilDBConstants::T_TEXT) .
                ' WHERE ' . $this->db->in($value_column, $standard_values, false, ilDBConstants::T_TEXT)
            );
        }
    }

    public function step_8(): void
    {
        foreach ($this->getAllVocabSlots() as $slot) {
            $table = $this->getTableForVocabSlot($slot);
            $src_column = $this->getSourceColumnNameForVocabSlot($slot);
            if (!$this->db->tableColumnExists($table, $src_column)) {
                continue;
            }
            $this->db->modifyTableColumn(
                $table,
                $src_column,
                [
                    'default' => ""
                ]
            );
        }
    }

    /**
     * @return SlotIdentifier[]
     */
    protected function getAllVocabSlots(): \Generator
    {
        // Some slots are persisted in the same column and table, so only return one of them.
        yield from [
            SlotIdentifier::GENERAL_STRUCTURE,
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL,
            SlotIdentifier::LIFECYCLE_STATUS,
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            //SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE,
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE,
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            //SlotIdentifier::TECHNICAL_REQUIREMENT_OS,
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE,
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL,
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY,
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE,
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE,
            SlotIdentifier::EDUCATIONAL_CONTEXT,
            SlotIdentifier::RIGHTS_COST,
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS,
            SlotIdentifier::RELATION_KIND,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
    }

    protected function getTableForVocabSlot(SlotIdentifier $slot): string
    {
        return match ($slot) {
            SlotIdentifier::GENERAL_STRUCTURE,
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL => 'il_meta_general',
            SlotIdentifier::LIFECYCLE_STATUS => 'il_meta_lifecycle',
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE => 'il_meta_contribute',
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE,
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS => 'il_meta_or_composite',
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE,
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL,
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY,
            SlotIdentifier::EDUCATIONAL_DIFFICULTY => 'il_meta_educational',
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => 'il_meta_lr_type',
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => 'il_meta_end_usr_role',
            SlotIdentifier::EDUCATIONAL_CONTEXT => 'il_meta_context',
            SlotIdentifier::RIGHTS_COST,
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => 'il_meta_rights',
            SlotIdentifier::RELATION_KIND => 'il_meta_relation',
            SlotIdentifier::CLASSIFICATION_PURPOSE => 'il_meta_classification',
            default => ''
        };
    }

    protected function getValueColumnNameForVocabSlot(SlotIdentifier $slot): string
    {
        return match ($slot) {
            SlotIdentifier::GENERAL_STRUCTURE => 'general_structure',
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL => 'general_aggl',
            SlotIdentifier::LIFECYCLE_STATUS => 'lifecycle_status',
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE => 'role',
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE => 'type',
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS => 'name',
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE => 'interactivity_type',
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => 'learning_resource_type',
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL => 'interactivity_level',
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY => 'semantic_density',
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => 'intended_end_user_role',
            SlotIdentifier::EDUCATIONAL_CONTEXT => 'context',
            SlotIdentifier::EDUCATIONAL_DIFFICULTY => 'difficulty',
            SlotIdentifier::RIGHTS_COST => 'costs',
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => 'cpr_and_or',
            SlotIdentifier::RELATION_KIND => 'kind',
            SlotIdentifier::CLASSIFICATION_PURPOSE => 'purpose',
            default => ''
        };
    }

    protected function getSourceColumnNameForVocabSlot(SlotIdentifier $slot): string
    {
        return match ($slot) {
            SlotIdentifier::GENERAL_STRUCTURE => 'general_structure_src',
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL => 'general_aggl_src',
            SlotIdentifier::LIFECYCLE_STATUS => 'lifecycle_status_src',
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE => 'role_src',
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE => 'type_src',
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS => 'name_src',
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE => 'interactivity_type_src',
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => 'learning_resource_type_src',
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL => 'interactivity_level_src',
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY => 'semantic_density_src',
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => 'intended_end_user_role_src',
            SlotIdentifier::EDUCATIONAL_CONTEXT => 'context_src',
            SlotIdentifier::EDUCATIONAL_DIFFICULTY => 'difficulty_src',
            SlotIdentifier::RIGHTS_COST => 'costs_src',
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => 'cpr_and_or_src',
            SlotIdentifier::RELATION_KIND => 'kind_src',
            SlotIdentifier::CLASSIFICATION_PURPOSE => 'purpose_src',
            default => ''
        };
    }

    protected function getTranslationsForVocabSlot(SlotIdentifier $slot): array
    {
        return match ($slot) {
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4'
            ],
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE => [
                'browser' => 'browser',
                'operating system' => 'operating system'
            ],
            SlotIdentifier::GENERAL_STRUCTURE => [
                'atomic' => 'Atomic',
                'collection' => 'Collection',
                'networked' => 'Networked',
                'hierarchical' => 'Hierarchical',
                'linear' => 'Linear'
            ],
            SlotIdentifier::LIFECYCLE_STATUS => [
                'draft' => 'Draft',
                'final' => 'Final',
                'revised' => 'Revised',
                'unavailable' => 'Unavailable'
            ],
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE => [
                'author' => 'Author',
                'publisher' => 'Publisher',
                'unknown' => 'Unknown',
                'initiator' => 'Initiator',
                'terminator' => 'Terminator',
                'editor' => 'Editor',
                'graphical designer' => 'GraphicalDesigner',
                'technical implementer' => 'TechnicalImplementer',
                'content provider' => 'ContentProvider',
                'technical validator' => 'TechnicalValidator',
                'educational validator' => 'EducationalValidator',
                'script writer' => 'ScriptWriter',
                'instructional designer' => 'InstructionalDesigner',
                'subject matter expert' => 'SubjectMatterExpert',
                'creator' => 'Creator',
                'validator' => 'Validator'
            ],
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS => [
                'pc-dos' => 'PC-DOS',
                'ms-windows' => 'MS-Windows',
                'macos' => 'MacOS',
                'unix' => 'Unix',
                'multi-os' => 'Multi-OS',
                'none' => 'None',
                'any' => 'Any',
                'netscape communicator' => 'NetscapeCommunicator',
                'ms-internet explorer' => 'MS-InternetExplorer',
                'opera' => 'Opera',
                'amaya' => 'Amaya'
            ],
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE => [
                'active' => 'Active',
                'expositive' => 'Expositive',
                'mixed' => 'Mixed'
            ],
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => [
                'exercise' => 'Exercise',
                'simulation' => 'Simulation',
                'questionnaire' => 'Questionnaire',
                'diagram' => 'Diagram',
                'figure' => 'Figure',
                'graph' => 'Graph',
                'index' => 'Index',
                'slide' => 'Slide',
                'table' => 'Table',
                'narrative text' => 'NarrativeText',
                'exam' => 'Exam',
                'experiment' => 'Experiment',
                'problem statement' => 'ProblemStatement',
                'self assessment' => 'SelfAssessment',
                'lecture' => 'Lecture'
            ],
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL,
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY => [
                'very low' => 'VeryLow',
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'very high' => 'VeryHigh'
            ],
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => [
                'teacher' => 'Teacher',
                'author' => 'Author',
                'learner' => 'Learner',
                'manager' => 'Manager'
            ],
            SlotIdentifier::EDUCATIONAL_CONTEXT => [
                'school' => 'School',
                'higher education' => 'HigherEducation',
                'training' => 'Training',
                'other' => 'Other'
            ],
            SlotIdentifier::EDUCATIONAL_DIFFICULTY => [
                'very easy' => 'VeryEasy',
                'easy' => 'Easy',
                'medium' => 'Medium',
                'difficult' => 'Difficult',
                'very difficult' => 'VeryDifficult'
            ],
            SlotIdentifier::RIGHTS_COST,
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            SlotIdentifier::RELATION_KIND => [
                'ispartof' => 'IsPartOf',
                'haspart' => 'HasPart',
                'isversionof' => 'IsVersionOf',
                'hasversion' => 'HasVersion',
                'isformatof' => 'IsFormatOf',
                'hasformat' => 'HasFormat',
                'references' => 'References',
                'isreferencedby' => 'IsReferencedBy',
                'isbasedon' => 'IsBasedOn',
                'isbasisfor' => 'IsBasisFor',
                'requires' => 'Requires',
                'isrequiredby' => 'IsRequiredBy'
            ],
            SlotIdentifier::CLASSIFICATION_PURPOSE => [
                'discipline' => 'Discipline',
                'idea' => 'Idea',
                'prerequisite' => 'Prerequisite',
                'educational objective' => 'EducationalObjective',
                'accessibility restrictions' => 'AccessibilityRestrictions',
                'educational level' => 'EducationalLevel',
                'skill level' => 'SkillLevel',
                'security level' => 'SecurityLevel',
                'competency' => 'Competency'
            ],
            default => []
        };
    }
}
