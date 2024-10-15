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

namespace Data\src\TextHandling\Text;

use Data\src\TextHandling\Shape\SimpleDocumentMarkdown as SimpleDocumentMarkdownShape;
use ILIAS\Refinery\Factory;
use Data\src\TextHandling\Markup\Markup;

class SimpleDocumentMarkdown extends Markdown
{
    public function __construct(
        protected SimpleDocumentMarkdownShape $simple_document_markdown_shape,
        string $raw
    ) {
        $this->simple_document_markdown_shape = $this->simple_document_markdown_shape;
        parent::__construct($this->simple_document_markdown_shape, $raw);
    }

    public function getSupportedStructure(): array
    {
        return $this->simple_document_markdown_shape->getSupportedStructure();
    }
}
