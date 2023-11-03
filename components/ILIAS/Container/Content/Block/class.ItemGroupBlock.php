<?php

declare(strict_types=1);

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

namespace ILIAS\Container\Content;

/**
 * A block that holds a single item group
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemGroupBlock implements Block
{
    protected int $ref_id;

    public function __construct(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }
}
