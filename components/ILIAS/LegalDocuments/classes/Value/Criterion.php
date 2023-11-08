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

namespace ILIAS\LegalDocuments\Value;

class Criterion
{
    public function __construct(
        private readonly int $id,
        private readonly CriterionContent $content,
        private readonly Edit $lastModification,
        private readonly Edit $creation
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function content(): CriterionContent
    {
        return $this->content;
    }

    public function lastModification(): Edit
    {
        return $this->lastModification;
    }

    public function creation(): Edit
    {
        return $this->creation;
    }
}
