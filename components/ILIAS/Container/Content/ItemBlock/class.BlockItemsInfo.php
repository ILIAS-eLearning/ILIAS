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

namespace ILIAS\Container\Content\ItemBlock;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BlockItemsInfo
{
    protected bool $limit_exhausted = false;
    protected array $ref_ids = [];

    public function __construct(
        array $ref_ids,
        bool $limit_exhausted
    ) {
        $this->ref_ids = $ref_ids;
        $this->limit_exhausted = $limit_exhausted;
    }

    public function getRefIds(): array
    {
        return $this->ref_ids;
    }

    public function getLimitExhausted(): bool
    {
        return $this->limit_exhausted;
    }
}
