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

namespace ILIAS\Tracking\DB\LPCollection;

use ILIAS\Tracking\DB\LPCollection\Element\LPCollectionInterface;

interface RepositoryInterface
{
    public function readLPCollection(
        int $object_id
    ): LPCollectionInterface|null;

    public function readLPCollectionWithReferenceInObjectReference(
        int $object_id
    ): LPCollectionInterface|null;

    public function writeLPCollection(
        LPCollectionInterface $lp_collection
    ): void;

    public function deleteLPCollection(
        int $object_id
    ): void;

    public function deleteLPCollectionEntry(
        int $object_id,
        int $item_id
    ): void;

    public function deleteLPCollectionEntryByGroupingId(
        int $object_id,
        int $item_id,
        int $grouping_id
    ): void;

    public function deleteLPCollectionManual(
        int $object_id,
    ): void;
}
