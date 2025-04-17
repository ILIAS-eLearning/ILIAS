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

namespace ILIAS\CommonMarkExtension;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;

/**
 * Commonmark Underline Extension
 * Based on https://github.com/benfiratkaya/commonmark-ext-underline
 */
final class UnderlineDelimiterProcessor implements DelimiterProcessorInterface
{
    public function getOpeningCharacter(): string
    {
        return '-';
    }

    public function getClosingCharacter(): string
    {
        return '-';
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        $min = \min($opener->getLength(), $closer->getLength());

        return $min >= 2 ? $min : 0;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $underline = new Underline(\str_repeat('--', $delimiterUse));

        $tmp = $opener->next();
        while ($tmp !== null && $tmp !== $closer) {
            $next = $tmp->next();
            $underline->appendChild($tmp);
            $tmp = $next;
        }

        $opener->insertAfter($underline);
    }
}
