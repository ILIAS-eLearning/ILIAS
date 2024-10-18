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

namespace Data\src\TextHandling\Shape;

use Data\src\TextHandling\Structure;
use Data\src\TextHandling\Text\Text;

class SimpleDocumentMarkdown extends Markdown
{
    /**
     * @return mixed[] consts from Structure
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

    public function fromString(string $text): Text
    {
        return new \Data\src\TextHandling\Text\SimpleDocumentMarkdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        $structure_patterns = [
            '\!\[(.)*\]\((.)+\)',   // images ![](url)
            '\!\[(.)+\]',           // images ![][url]
            '\[(.)*\]\:(.)+'        // images []:url
        ];

        foreach ($structure_patterns as $pattern) {
            if (mb_ereg_match($pattern, $text)) {
                return false;
            }
        }

        return true;
    }
}
