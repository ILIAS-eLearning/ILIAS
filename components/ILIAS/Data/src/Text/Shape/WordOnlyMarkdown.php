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

class WordOnlyMarkdown extends SimpleDocumentMarkdown
{
    /**
     * @return Structure[]
     */
    public function getSupportedStructure(): array
    {
        return [
            Structure::BOLD,
            Structure::ITALIC
        ];
    }

    public function fromString(string $text): Text\WordOnlyMarkdown
    {
        return new Text\WordOnlyMarkdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        $options = mb_regex_set_options();
        try {
            mb_regex_set_options("m");
            return !mb_ereg_match(
                '.*((' . MRE::HEADINGS->value . ')|(' . MRE::UNORDERED_LIST->value . ')|(' . MRE::ORDERED_LIST->value . ')|' .
                '(' . MRE::LINE_BREAK->value . ')|(' . MRE::PARAGRAPH->value . ')|(' . MRE::BLOCKQUOTE->value . ')|' .
                '(' . MRE::CODEBLOCK->value . ')|(' . MRE::REF->value . ')|(' . MRE::LINK_REF_USAGE->value . ')|' .
                '(' . MRE::IMAGE->value . ')|(' . MRE::IMAGE_REF_USAGE->value . '))',
                $text
            );
        } finally {
            mb_regex_set_options($options);
        }
    }
}
