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

class WordOnlyMarkdown extends SimpleDocumentMarkdown
{
    /**
     * @return mixed[] consts from Structure
     */
    public function getSupportedStructure(): array
    {
        return [
            Structure::BOLD,
            Structure::ITALIC
        ];
    }

    public function fromString(string $text): Text
    {
        return new \Data\src\TextHandling\Text\WordOnlyMarkdown($this, $text);
    }

    public function isRawStringCompliant(string $text): bool
    {
        $structure_patterns = [
            '^(\#){1,6}(\ )',       // headings 1 - 6
            '^(\- )',               // unordered list
            '^(\* )',               // unordered list
            '^(\+ )',               // unordered list
            '^([0-9]+)(\.\ )',      // ordered list
            '(\ ){2}',              // paragraph via space
            '(\\\)',                // paragraph
            '\[(.)*\]\((.)+\)',     // link [title](url)
            '\[(.)*\]\([.]+\)',     // link [id][url]
            '\[(.)*\]\:(.)+',       // link [id]:url
            '\!\[(.)*\]\((.)+\)',   // images ![](url)
            '\!\[(.)+\]',           // images ![][url]
            '\[(.)*\]\:(.)+',       // images []:url
            '^(\>)+',               // blockquote
            '(\`)'                  // code only
        ];

        foreach ($structure_patterns as $pattern) {
            if (mb_ereg_match($pattern, $text)) {
                return false;
            }
        }

        return true;
    }
}
