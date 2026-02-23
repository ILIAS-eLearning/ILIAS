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

namespace ILIAS\Search\Presentation\Result\UI;

use ILIAS\Refinery\Factory as Refinery;

class SanitizerImpl implements Sanitizer
{
    protected const string HIGHLIGHT_START = '<span class="ilSearchHighlight">';
    protected const string HIGHLIGHT_END = '</span>';
    protected const string PLACEHOLDER_START = '[PLACEHOLDER_START]';
    protected const string PLACEHOLDER_END = '[PLACEHOLDER_END]';

    public function __construct(
        protected Refinery $refinery
    ) {
    }

    public function sanitize(string $text): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($text);
    }

    public function sanitizeAndSetUpPlaceholders(string $text): string
    {
        $text = str_replace(self::PLACEHOLDER_START, '', $text);
        $text = str_replace(self::PLACEHOLDER_END, '', $text);
        $text = $this->replaceInPairs(
            self::HIGHLIGHT_START,
            self::HIGHLIGHT_END,
            self::PLACEHOLDER_START,
            self::PLACEHOLDER_END,
            $text
        );
        return $this->sanitize($text);
    }

    public function replacePlaceholders(string $html): string
    {
        return $this->replaceInPairs(
            self::PLACEHOLDER_START,
            self::PLACEHOLDER_END,
            self::HIGHLIGHT_START,
            self::HIGHLIGHT_END,
            $html
        );
    }

    protected function replaceInPairs(
        string $search_start,
        string $search_end,
        string $replace_start,
        string $replace_end,
        string $text
    ): string {
        $regex = '/' . preg_quote($search_start, '/') . '(.*?)' . preg_quote($search_end, '/') . '/m';
        $replacement = $replace_start . '$1' . $replace_end;
        return preg_replace($regex, $replacement, $text);
    }
}
