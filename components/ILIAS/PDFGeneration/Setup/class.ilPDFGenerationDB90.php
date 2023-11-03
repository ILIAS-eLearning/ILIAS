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

class ilPDFGenerationDB90 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('pdfgen_conf')) {
            $this->db->dropTable('pdfgen_conf');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableExists('pdfgen_map')) {
            $this->db->dropTable('pdfgen_map');
        }
    }
    public function step_3(): void
    {
        if ($this->db->tableExists('pdfgen_purposes')) {
            $this->db->dropTable('pdfgen_purposes');
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableExists('pdfgen_renderer')) {
            $this->db->dropTable('pdfgen_renderer');
        }
    }
    public function step_5(): void
    {
        if ($this->db->tableExists('pdfgen_renderer_avail')) {
            $this->db->dropTable('pdfgen_renderer_avail');
        }
    }
    public function step_6(): void
    {
        if ($this->db->tableExists('pdfgen_conf_seq')) {
            $this->db->dropTable('pdfgen_conf_seq');
        }
    }

    public function step_7(): void
    {
        if ($this->db->tableExists('pdfgen_map_seq')) {
            $this->db->dropTable('pdfgen_map_seq');
        }
    }
    public function step_8(): void
    {
        if ($this->db->tableExists('pdfgen_purposes_seq')) {
            $this->db->dropTable('pdfgen_purposes_seq');
        }
    }

    public function step_9(): void
    {
        if ($this->db->tableExists('pdfgen_renderer_seq')) {
            $this->db->dropTable('pdfgen_renderer_seq');
        }
    }
    public function step_10(): void
    {
        if ($this->db->tableExists('pdfgen_renderer_avail_seq')) {
            $this->db->dropTable('pdfgen_renderer_avail_seq');
        }
    }
}
