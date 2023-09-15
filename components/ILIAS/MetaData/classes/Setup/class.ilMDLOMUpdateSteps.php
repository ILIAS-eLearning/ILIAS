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

/**
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilMDLOMUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a column to the il_meta_general table to store the
     * 'Aggregation Level' element.
     */
    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('il_meta_general', 'general_aggl')) {
            $this->db->addTableColumn(
                'il_meta_general',
                'general_aggl',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 16,
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_contribute table to store the
     * descrption of the date and its language.
     */
    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('il_meta_contribute', 'c_date_descr')) {
            $this->db->addTableColumn(
                'il_meta_contribute',
                'c_date_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_contribute', 'descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_contribute',
                'descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_annotation table to store the
     * description of the date and its language.
     */
    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('il_meta_annotation', 'a_date_descr')) {
            $this->db->addTableColumn(
                'il_meta_annotation',
                'a_date_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_annotation', 'date_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_annotation',
                'date_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_educational table to store the
     * description of the typical learning time and its language.
     */
    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('il_meta_educational', 'tlt_descr')) {
            $this->db->addTableColumn(
                'il_meta_educational',
                'tlt_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_educational', 'tlt_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_educational',
                'tlt_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_technical table to store the
     * description of the duration and its language.
     */
    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('il_meta_technical', 'duration_descr')) {
            $this->db->addTableColumn(
                'il_meta_technical',
                'duration_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_technical', 'duration_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_technical',
                'duration_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }
}
