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

namespace ILIAS\Data\Text;

class MarkdownFactory
{
    public function __construct(
        protected Shape\Markdown $markdown_shape,
        protected Shape\SimpleDocumentMarkdown $simple_document_markdown_shape,
        protected Shape\WordOnlyMarkdown $word_only_markdown_shape
    ) {
    }

    public function generic(string $markdown): Markdown
    {
        return $this->markdown_shape->fromString($markdown);
    }

    public function simpleDocument(string $markdown): SimpleDocumentMarkdown
    {
        return $this->simple_document_markdown_shape->fromString($markdown);
    }

    public function wordOnly(string $markdown): WordOnlyMarkdown
    {
        return $this->word_only_markdown_shape->fromString($markdown);
    }
}
