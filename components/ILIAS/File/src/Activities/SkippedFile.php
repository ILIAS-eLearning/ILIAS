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

namespace ILIAS\File\Activities;

/**
 * A file a packing activity has left out, and why - so the user learns that the
 * archive is not complete instead of losing the whole archive over one file.
 */
readonly class SkippedFile
{
    public function __construct(
        public int $ref_id,
        public string $title,
        public string $reason,
    ) {
    }
}
