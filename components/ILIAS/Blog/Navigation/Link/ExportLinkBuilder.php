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

namespace ILIAS\Blog\Navigation\Link;

/**
 * Link builder for static HTML export.
 */
class ExportLinkBuilder implements LinkBuilder
{
    public function __construct(
        protected string $template,
        protected array $keyword_map = []
    ) {
    }

    protected function build(string $type, string $id): string
    {
        return str_replace(["{TYPE}", "{ID}"], [$type, $id], $this->template);
    }

    public function forMonth(string $month): string
    {
        return $this->build("m", $month);
    }

    public function forPosting(int $posting_id, string $month = ""): string
    {
        return $this->build("p", (string) $posting_id);
    }

    public function forKeyword(string $keyword): string
    {
        $id = (string) ($this->keyword_map[$keyword] ?? "");
        return $this->build("k", $id);
    }

    public function forAuthor(int $user_id): string
    {
        // Currently not supported in HTML export, but could be added if needed.
        return "";
    }

    public function forMainList(): string
    {
        return "index.html";
    }
}
