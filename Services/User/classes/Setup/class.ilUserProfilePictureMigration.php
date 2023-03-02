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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\UI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilUserProfilePictureMigration implements Setup\Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of Profile Pictures to the Resource Storage Service.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new \ilUserProfilePictureStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT
                        usr_id
                    FROM usr_data
                    WHERE usr_data.rid IS NULL OR usr_data.rid = ''
                    LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $user_id = (int)$d->usr_id;

        $base_path = $this->buildBasePath();
        $pattern = '/.*\/(usr|upload)\_' . $user_id . '\..*/m';

        $rid = $this->helper->moveFirstFileOfPatternToStorage(
            $base_path,
            $pattern,
            $user_id
        );

        $save_rid = $rid === null ? '-' : $rid->serialize();
        $this->helper->getDatabase()->update(
            'usr_data',
            ['rid' => ['text', $save_rid]],
            ['usr_id' => ['integer', $user_id],]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT
                        count(usr_data.usr_id) AS amount
                    FROM usr_data
                    WHERE usr_data.rid IS NULL OR usr_data.rid = '';"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int)$d->amount;
    }

    protected function buildBasePath(): string
    {
        return CLIENT_WEB_DIR . '/usr_images/';
    }
}
