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

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilOrgUnitRemoveDeletedMDSetsMigration implements Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Deleted meta data set assignments are removed for OrgUnit types';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilOrgUnitTypeStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $this->helper->getDatabase()->setLimit(1);
        $res = $this->helper->getDatabase()->query(
            'SELECT rec_id FROM orgu_types_adv_md_rec' . PHP_EOL
            . 'WHERE rec_id NOT IN (' . PHP_EOL
            . 'SELECT record_id FROM adv_md_record' . PHP_EOL
            . ')'
        );
        $row = $this->helper->getDatabase()->fetchAssoc($res);
        $rec_id = (int) $row['rec_id'];

        $query = 'DELETE FROM orgu_types_adv_md_rec' . PHP_EOL
            . 'WHERE rec_id = ' . $this->helper->getDatabase()->quote($rec_id, 'integer');
        $this->helper->getDatabase()->manipulate($query);

    }

    public function getRemainingAmountOfSteps(): int
    {
        $res = $this->helper->getDatabase()->query(
            'SELECT COUNT(rec_id) as amount FROM orgu_types_adv_md_rec' . PHP_EOL
            . 'WHERE rec_id NOT IN (' . PHP_EOL
            . 'SELECT record_id FROM adv_md_record' . PHP_EOL
            . ')'
        );
        $row = $this->helper->getDatabase()->fetchObject($res);
        return (int) ($row->amount ?? 0);
    }
}
