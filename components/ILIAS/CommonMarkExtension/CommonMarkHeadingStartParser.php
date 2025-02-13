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

namespace ILIAS\CommonMarkExtension\CommonMarkExtension;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\CommonMark\Util\RegexHelper;
use League\CommonMark\Extension\CommonMark\Parser\Block as PB;

/**
 * CommonMarkHeadingStartParser
 *
 * Only parses ATX headings, ignores setext headings
 */
class CommonMarkHeadingStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented() || !\in_array($cursor->getNextNonSpaceCharacter(), ['#'], true)) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();

        if ($atxHeading = self::getAtxHeader($cursor)) {
            return BlockStart::of($atxHeading)->at($cursor);
        }

        return BlockStart::none();
    }

    private static function getAtxHeader(Cursor $cursor): ?PB\HeadingParser
    {
        $match = RegexHelper::matchFirst('/^#{1,6}(?:[ \t]+|$)/', $cursor->getRemainder());
        if (!$match) {
            return null;
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $cursor->advanceBy(\strlen($match[0]));

        $level = \strlen(\trim($match[0]));
        $str = $cursor->getRemainder();
        $str = \preg_replace('/^[ \t]*#+[ \t]*$/', '', $str);
        \assert(\is_string($str));
        $str = \preg_replace('/[ \t]+#+[ \t]*$/', '', $str);
        \assert(\is_string($str));

        return new PB\HeadingParser($level, $str);
    }
}
