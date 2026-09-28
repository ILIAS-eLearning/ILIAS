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

namespace ILIAS\Tracking\DB\LPSettings\Element;

interface LPSettingsInterface
{
    public function getObjectId(): int;

    public function getUMode(): int;

    public function getVisits(): int;

    public function getObjType(): string;

    public function withObjectId(
        int $obj_id
    ): LPSettingsInterface;

    public function withUMode(
        int $u_mode
    ): LPSettingsInterface;

    public function withVisits(
        int $visits
    ): LPSettingsInterface;

    public function withObjType(
        string $obj_type
    ): LPSettingsInterface;
}
