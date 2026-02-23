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

namespace ILIAS\StaticURL\Shortlinks\Shortlink\Target;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RepoTypeData extends TypeData
{
    public const string F_TYPE = 'type';
    public const string F_REF_ID = 'ref_id';

    public function __construct(
        string $type,
        int $ref_id
    ) {
        parent::__construct(
            [
                self::F_TYPE => $type,
                self::F_REF_ID => $ref_id
            ]
        );
    }

    public function getRefId(): ?int
    {
        return $this[self::F_REF_ID] ?? null;
    }

    public function setRefId(int $ref_id): void
    {
        $this[self::F_REF_ID] = $ref_id;
    }

    public function getType(): ?string
    {
        return $this[self::F_TYPE] ?? null;
    }

    public function setType(string $type): void
    {
        $this[self::F_TYPE] = $type;
    }
}
