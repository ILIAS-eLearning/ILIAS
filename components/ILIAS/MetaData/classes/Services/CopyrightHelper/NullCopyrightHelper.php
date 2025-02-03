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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface as SearchClause;
use ILIAS\MetaData\Search\Clauses\NullClause;

class NullCopyrightHelper implements CopyrightHelperInterface
{
    public function isCopyrightSelectionActive(): bool
    {
        return false;
    }

    public function hasPresetCopyright(ReaderInterface $reader): bool
    {
        return false;
    }

    public function readPresetCopyright(ReaderInterface $reader): CopyrightInterface
    {
        return new NullCopyright();
    }

    public function readCustomCopyright(ReaderInterface $reader): string
    {
        return '';
    }

    public function prepareCreateOrUpdateOfCopyrightFromPreset(
        ManipulatorInterface $manipulator,
        string $copyright_id
    ): ManipulatorInterface {
        return $manipulator;
    }

    public function prepareCreateOrUpdateOfCustomCopyright(
        ManipulatorInterface $manipulator,
        string $custom_copyright
    ): ManipulatorInterface {
        return $manipulator;
    }

    public function getAllCopyrightPresets(): \Generator
    {
        yield from [];
    }

    public function getNonOutdatedCopyrightPresets(): \Generator
    {
        yield from [];
    }

    public function getCopyrightSearchClause(
        string $first_copyright_id,
        string ...$further_copyright_ids
    ): SearchClause {
        return new NullClause();
    }
}
