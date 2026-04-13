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

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilBookingManagerBookingInfoMigration implements Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Migration of post booking info files to the resource storage service.';
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
        $this->helper = new ilResourceStorageMigrationHelper(new ilBookBookingInfoStakeholder(), $environment);
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            'SELECT booking_object_id, pool_id, owner FROM booking_object JOIN object_data ON pool_id = obj_id WHERE book_info_rid IS NULL LIMIT 1'
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $pool_id = (int) $d->pool_id;
        $booking_object_id = (int) $d->booking_object_id;
        $resource_owner_id = (int) $d->owner;
        $base_path = $this->buildAbsolutPath($booking_object_id);
        $pattern = '/[^\.].*/m';
        $rid = is_dir($base_path)
            ? $this->helper->moveFirstFileOfPatternToStorage($base_path, $pattern, $resource_owner_id)
            : '';
        $this->helper->getDatabase()->update(
            'booking_object',
            [
                'book_info_rid' => [ilDBConstants::T_TEXT, (string) $rid]
            ],
            [
                'booking_object_id' => [ilDBConstants::T_INTEGER, $booking_object_id],
                'pool_id' => [ilDBConstants::T_INTEGER, $pool_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            'SELECT count(booking_object_id) amount FROM booking_object JOIN object_data ON pool_id = obj_id WHERE book_info_rid IS NULL'
        );

        return (int) $this->helper->getDatabase()->fetchObject($r)->amount;
    }

    protected function buildAbsolutPath(int $booking_object_id): string
    {
        $path_from_id = ilFileSystemAbstractionStorage::createPathFromId($booking_object_id, 'book');
        return CLIENT_WEB_DIR . "/ilBookingManager/{$path_from_id}/post";
    }
}
