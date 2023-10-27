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

class Document
{
    /**
     * @param list<Criterion> $criteria
     */
    public function __construct(
        private readonly int $id,
        private readonly Meta $meta,
        private readonly DocumentContent $content,
        private readonly array $criteria
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function meta(): Meta
    {
        return $this->meta;
    }

    public function content(): DocumentContent
    {
        return $this->content;
    }

    /**
     * @return list<Criterion>
     */
    public function criteria(): array
    {
        return $this->criteria;
    }
}
