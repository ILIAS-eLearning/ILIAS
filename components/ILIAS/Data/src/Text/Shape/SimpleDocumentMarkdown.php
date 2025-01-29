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

namespace ILIAS\Data\Text\Shape;

use ILIAS\Data\Text\Structure;
use ILIAS\Data\Text;
use ILIAS\Data\Text\Markup\MarkdownRegExps as MRE;

class SimpleDocumentMarkdown extends Markdown
{
    /**
     * @return Structure[]
     */
    public function getSupportedStructure(): array
    {
        return [
            Structure::BOLD,
            Structure::ITALIC,
            Structure::HEADING_1,
            Structure::HEADING_2,
            Structure::HEADING_3,
            Structure::HEADING_4,
            Structure::HEADING_5,
            Structure::HEADING_6,
            Structure::UNORDERED_LIST,
            Structure::ORDERED_LIST,
            Structure::PARAGRAPH,
            Structure::LINK,
            Structure::BLOCKQUOTE,
            Structure::CODE
        ];
    }

    public function fromString(string $text): Text\SimpleDocumentMarkdown
    {
        return new Text\SimpleDocumentMarkdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        $options = mb_regex_set_options();
        try {
            mb_regex_set_options("m");
            return !mb_ereg_match(
                '.*((' . MRE::IMAGE->value . ')|(' . MRE::REF->value . ')|(' . MRE::IMAGE_REF_USAGE->value . '))',
                $text
            );
        } finally {
            mb_regex_set_options($options);
        }
    }
}
