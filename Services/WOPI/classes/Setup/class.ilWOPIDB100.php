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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPIDB100 implements \ilDatabaseUpdateSteps
{
    private ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('wopi_action', 'target_text')) {
            $this->db->addTableColumn(
                'wopi_action',
                'target_text',
                [
                    'type' => 'text',
                    'length' => 256,
                    'notnull' => false,
                ],
            );
        }
    }

}
