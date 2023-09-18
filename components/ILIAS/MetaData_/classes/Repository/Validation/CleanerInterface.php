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

namespace ILIAS\MetaData\Repository\Validation;

use ILIAS\MetaData\Elements\SetInterface;

interface CleanerInterface
{
    /**
     * Returns a new metadata set, identical to the one given but
     * with all invalid elements (invalid data, multiples of unique
     * elements, ...) removed. Removes markers and scaffolds.
     */
    public function clean(SetInterface $set): SetInterface;

    /**
     * Checks whether the proposed manipulations on the set via markers
     * are valid. Throws an error if not.
     * @throws \ilMDRepositoryException
     */
    public function checkMarkers(SetInterface $set): void;
}
