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

class ilTest9DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'show_examview_pdf')) {
            $this->db->dropTableColumn('tst_tests', 'show_examview_pdf');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableExists('manscoring_done')) {
            $this->db->createTable('manscoring_done', [
                'active_id' => [
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true
                ],
                'done' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ]);
            $this->db->addPrimaryKey('manscoring_done', ['active_id']);
        }
    }

    /**
     * Drop the test settings for the special character seletor
     */
    public function step_3(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'char_selector_availability')) {
            $this->db->dropTableColumn('tst_tests', 'char_selector_availability');
        }

        if ($this->db->tableColumnExists('tst_tests', 'char_selector_definition')) {
            $this->db->dropTableColumn('tst_tests', 'char_selector_definition');
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'introduction_page_id')) {
            $this->db->addTableColumn(
                'tst_tests',
                'introduction_page_id',
                [
                    'type' => 'integer',
                    'length' => 8
                ]
            );
        }
        if (!$this->db->tableColumnExists('tst_tests', 'concluding_remarks_page_id')) {
            $this->db->addTableColumn(
                'tst_tests',
                'concluding_remarks_page_id',
                [
                    'type' => 'integer',
                    'length' => 8
                ]
            );
        }
    }

    public function step_5(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'show_examview_html')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'show_examview_html'
            );
        }
        if ($this->db->tableColumnExists('tst_tests', 'showinfo')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'showinfo'
            );
        }
        if ($this->db->tableColumnExists('tst_tests', 'forcejs')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'forcejs'
            );
        }
        if ($this->db->tableColumnExists('tst_tests', 'enable_archiving')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'enable_archiving'
            );
        }
        if ($this->db->tableColumnExists('tst_tests', 'customstyle')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'customstyle'
            );
        }
        if ($this->db->tableColumnExists('tst_tests', 'enabled_view_mode')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'enabled_view_mode'
            );
        }
    }

    public function step_6(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'allowedusers')) {
            $this->db->dropTableColumn('tst_tests', 'allowedusers');
        }

        if ($this->db->tableColumnExists('tst_tests', 'alloweduserstimegap')) {
            $this->db->dropTableColumn('tst_tests', 'alloweduserstimegap');
        }

        if ($this->db->tableColumnExists('tst_tests', 'limit_users_enabled')) {
            $this->db->dropTableColumn('tst_tests', 'limit_users_enabled');
        }
    }

    public function step_7(): void
    {
        if ($this->db->tableExists('tst_dyn_quest_set_cfg')) {
            $this->db->dropTable('tst_dyn_quest_set_cfg');
        }
        if ($this->db->tableExists('tst_seq_qst_tracking')) {
            $this->db->dropTable('tst_seq_qst_tracking');
        }
        if ($this->db->tableExists('tst_seq_qst_answstatus')) {
            $this->db->dropTable('tst_seq_qst_answstatus');
        }
        if ($this->db->tableExists('tst_seq_qst_postponed')) {
            $this->db->dropTable('tst_seq_qst_postponed');
        }
    }

    public function step_8(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'redirection_url')) {
            $this->db->modifyTableColumn(
                'tst_tests',
                'redirection_url',
                [
                    'type' => 'text',
                    'length' => 4000,
                    'notnull' => false,
                    'default' => null
                ]
            );
        }
    }

    public function step_9(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'show_questionlist')) {
            $this->db->addTableColumn(
                'tst_tests',
                'show_questionlist',
                [
                    'type' => 'integer',
                    'length' => 1
                ]
            );
        }
    }

    public function step_10(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'sign_submission')) {
            $this->db->dropTableColumn(
                'tst_tests',
                'sign_submission'
            );
        }
    }

    public function step_11(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'show_summary')) {
            $this->db->renameTableColumn(
                'tst_tests',
                'show_summary',
                'usr_pass_overview_mode'
            );
        }
    }

    public function step_12(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'show_questionlist')) {
            $this->db->addTableColumn(
                'tst_tests',
                'show_questionlist',
                [
                    'type' => 'integer',
                    'length' => 1
                ]
            );
        }
    }

    public function step_13(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'hide_info_tab')) {
            $this->db->addTableColumn('tst_tests', 'hide_info_tab', [
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]);
        }

        if (!$this->db->tableColumnExists('tst_tests', 'conditions_checkbox_enabled')) {
            $this->db->addTableColumn('tst_tests', 'conditions_checkbox_enabled', [
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]);
        }
    }

    public function step_14(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'hide_info_tab')) {
            $this->db->modifyTableColumn('tst_tests', 'hide_info_tab', [
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]);
        }

        if ($this->db->tableColumnExists('tst_tests', 'conditions_checkbox_enabled')) {
            $this->db->modifyTableColumn('tst_tests', 'conditions_checkbox_enabled', [
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]);
        }
    }

    public function step_15(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'result_tax_filters')) {
            $this->db->dropTableColumn('tst_tests', 'result_tax_filters');
        }
    }

    public function step_16(): void
    {
        $this->db->modifyTableColumn(
            'tst_tests',
            'show_cancel',
            [
                'type' => 'text',
                'length' => 1,
                'default' => '0'
            ]
        );
    }

    public function step_17(): void
    {
        $this->db->modifyTableColumn(
            'tst_tests',
            'use_previous_answers',
            [
                'type' => 'text',
                'length' => 1,
                'default' => '0'
            ]
        );
    }


    public function step_18(): void
    {
        $this->db->renameTableColumn(
            'tst_tests',
            'show_cancel',
            'suspend_test_allowed'
        );
    }

    public function step_19(): void
    {
        if (!$this->db->tableExists('tst_qst_var_presented')) {
            $this->db->createTable('tst_qst_var_presented', [
                'question_id' => [
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true
                ],
                'active_id' => [
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true
                ],
                'pass' => [
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true
                ],
                'variable' => [
                    'type' => 'text',
                    'length' => 32,
                    'notnull' => true
                ],
                'value' => [
                    'type' => 'text',
                    'length' => 64,
                    'notnull' => true
                ]
            ]);
            $this->db->addPrimaryKey(
                'tst_qst_var_presented',
                ['question_id','active_id','pass','variable']
            );
        }
    }

    public function step_20(): void
    {
        $this->db->modifyTableColumn('ass_log', 'logtext', ['type' => \ilDBConstants::T_CLOB]);
    }
}
