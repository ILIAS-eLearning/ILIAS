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

class ilDashboardPage extends ilPageObject
{
    public const ID = 1;
    public const PARENT_TYPE = 'dash';

    public string $parent_type = self::PARENT_TYPE;
    public int $parent_id = 1;

    public function getParentType(): string
    {
        return $this->parent_type;
    }
}
