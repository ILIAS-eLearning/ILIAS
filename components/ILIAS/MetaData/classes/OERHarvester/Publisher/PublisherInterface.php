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

namespace ILIAS\MetaData\OERHarvester\Publisher;

interface PublisherInterface
{
    public function block(int $obj_id): void;

    public function checkPermissionsForBlock(int $ref_id, string $type, int $obj_id): bool;

    public function unblock(int $obj_id): void;

    public function checkPermissionsForUnblock(int $ref_id, string $type, int $obj_id): bool;

    public function publish(int $obj_id, string $type): void;

    public function checkPermissionsForPublish(int $ref_id, string $type, int $obj_id): bool;

    public function withdraw(int $obj_id): void;

    public function checkPermissionsForWithdraw(int $ref_id, string $type, int $obj_id): bool;

    public function submit(int $obj_id): void;

    public function checkPermissionsForSubmit(int $ref_id, string $type, int $obj_id): bool;

    public function accept(int $obj_id, string $type): void;

    public function checkPermissionsForAccept(int $ref_id, string $type, int $obj_id): bool;

    public function reject(int $obj_id): void;

    public function checkPermissionsForReject(int $ref_id, string $type, int $obj_id): bool;
}
